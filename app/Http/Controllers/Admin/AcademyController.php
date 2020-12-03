<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Academy;
use App\Models\Customer;
use App\Models\AcademyPeriod;
use App\Models\AcademyPeriodCustomer;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Mail;
use App\Mails\AcademyMail;

class AcademyController extends Controller
{
     public function index(){
        $data = Academy::with('updater')->paginate(10);
        return response()->json(['data'=>$data]);
     }

     public function list(Request $request){
        $data = Academy::where('id','>',0);
        if($request->has('active')){
            $data->whereHas('academy_period', function($q){
                $q->where('active',1);
            });
        }
        $data = $data->get();
        return response()->json(['data'=>$data]);
     }

     public function customerStore(Request $request){
        $datas = $request->all();

        $validator = Validator::make($datas, rules_lists(__CLASS__, __FUNCTION__));
        if ($validator->fails()) return response()->json(['errors'=>($validator->messages())],422);

        $cus = Customer::updateOrCreate(["email"=>$datas["email"]],$datas);
        if($cus){
            //cek ada akademi X gak
            $aca = Academy::where("name",$datas["ja_name"])->first();
            if(!$aca) return response()->json(["message"=>"Tidak ada Akademi ".$datas["ja_name"]],450);

            //cek akademi X lagi buka ato gak di periode ini
            $aca_per = AcademyPeriod::where("academy_id",$aca->id)->where('active',1)->first();
            if(!$aca_per) return response()->json(["message"=>"Penyelenggaraan Akademi ".$datas["ja_name"]." ditutup"],450);

            //cek customer A udah terdaftar belum di akademi X
            $aca_per_cus = AcademyPeriodCustomer::where(["academy_period_id"=>$aca_per->id, "customer_id"=>$cus->id])->first();
            if($aca_per_cus){
                //jika sudah terdaftar dan pembayaran berhasil atau pending, tidak bisa melanjutkan pembayaran
                if($aca_per_cus->status == 1 || $aca_per_cus->status == 2 )
                    return response()->json(["message"=>"Kamu sudah mendaftar Akademi ".$datas["ja_name"]],450);
            }else{
                //jika belum terdaftar, tambahkan di peserta dengan status belum bayar
                $updater_id = null;
                if(Auth::check()){
                    $updater_id = Auth::user()->id;
                }
                $aca_per_cus = AcademyPeriodCustomer::create([
                    "academy_period_id"=>$aca_per->id, "customer_id"=>$cus->id, "price"=>$aca_per->price, "updater_id"=>$updater_id]);
                if(!$aca_per_cus){
                    return response()->json(["message"=>"Terjadi Kesalahan"],450);
                }
            }

            if(Auth::check()){
                return response()->json(["message"=>"Berhasil tambah peserta ".$datas["ja_name"],"payment"=>false]);
            }

            $amount = $aca_per->price;
            $last_payment_id = Payment::orderBy('id','desc')->value('id');
            $last_payment_id_plus_one = $last_payment_id + 1;
            $code = "ORD-JOBHUNACADEMY-".$aca_per_cus->id."-".$last_payment_id_plus_one;
            $code = md5(env("MIDTRANS_MD5_ADDITIONAL_CODE").$code);

            //===================================
            $payment = new Payment;
            $payment->amount = $amount;
            $payment->code = $code;
            $payment->academy_period_customer_id = $aca_per_cus->id;
            $payment->via = "midtrans";
            $payment->save();
            //===================================

            if(!$payment)
                return response()->json(["message"=>"Terjadi Kesalahan"],450);

            $snap_response = $this->getMidtransToken($code,$amount);
            if($snap_response["status"]){
                return response()->json(["data"=>$snap_response["data"]]);
            }else{
                return response()->json(["message"=>$snap_response["message"]],450);
            }

        }
        return response()->json(["message"=>"Terjadi Kesalahan"],450);
     }

     private function getMidtransToken($code,$amount){
        $serverAuthKey = "Basic ".base64_encode(env("MIDTRANS_SERVER_KEY").":");
        if($serverAuthKey){
            $response = Curl::to('https://app.sandbox.midtrans.com/snap/v1/transactions')
            ->withHeader('Accept: application/json')
            ->withContentType('application/json')
            ->withAuthorization($serverAuthKey)
            ->withData(["transaction_details"=>["order_id"=>$code, "gross_amount"=>$amount],"callbacks"=>["finish"=>"localhost:3000/academy/form"]])
            ->asJson(true)
            ->post();

            if(isset($response["token"])){
                return ["status"=>true,"data"=>$response];
            }else{
                return ["status"=>false,"message"=>$response["error_messages"][0]];
            }
        }
        else
            return ["status"=>false,"message"=>"Gangguan Akses ke Server Pembayaran"];
     }

     public function successPayment(Request $request){
        if($request->has("transaction_status")){
            $tc = $request->transaction_status;
            $payment_code = $request->order_id;
            $payment = Payment::where('code',$payment_code)->firstOrFail();
            $aca_per_cus = AcademyPeriodCustomer::findOrFail($payment->academy_period_customer_id);
            $pending_string = "";
            if(in_array($tc, ["capture","settlement"])){
                $aca_per_cus->status = 1;
                $status_string = "sudah berhasil :)";
            }
            else if($tc == "pending"){
                $aca_per_cus->status = 2;
                $status_string = "masih pending ...";
            }
            else{
                $aca_per_cus->status = 3;
                $status_string = "gagal :'(";
            }
            $aca_per_cus->save();
            $payment->transaction_status = $tc;
            $payment->transaction_id = $request->transaction_id;
            $payment->save();
            
            Mail::to($aca_per_cus->customer)
                ->send(new AcademyMail($aca_per_cus->academy_period->academy->name, $aca_per_cus->customer->name, $status_string));

            return response()->json(["status_code"=>"200"]);
        }
        else
            return response()->json(["status_code"=>"400"]);
     }

     public function customerShow(Request $request){
        $data = DB::table('academy_period_customer as apc')
                    ->join('academy_periods as ap','ap.id','=','apc.academy_period_id')
                    ->join('academies as a','a.id','=','ap.academy_id')
                    ->join('customers as c','c.id','=','apc.customer_id');

        if($request->has('search')){
            if(trim($request->search) != ""){
                $data = $data->where('c.name','like','%'.$request->search.'%');
            }
        }
        if($request->has('ja_id')){
            if(trim($request->ja_id) != ""){
                $data = $data->where('ap.academy_id',$request->ja_id);
            }
        }
        if($request->has('period')){
            if(trim($request->period) != ""){
                $data = $data->where('ap.period',$request->period);
            }
        }   
        if($request->has('status')){
            if(trim($request->status) != ""){
                $data = $data->where('apc.status',$request->status);
            }
        }
        $data = $data->select("apc.*","a.name as academy_name","c.name as customer_name","ap.period")->paginate(10);
        return response()->json($data->appends($request->all()));
     }

     public function paymentStore(Request $request){
        $session_id = Auth::user()->id;
        $datas = $request->all();

        $validator = Validator::make($datas, rules_lists(__CLASS__, __FUNCTION__));
        if ($validator->fails()) return response()->json(['errors'=>($validator->messages())],422);

        $payment = new Payment;
        $payment->transaction_id = $request->transaction_id;
        $payment->amount = $request->amount;
        $payment->academy_period_customer_id = $request->academy_period_customer_id;
        $payment->via = $request->via;
        if($payment->save()){
            $aca_per_cus = AcademyPeriodCustomer::findOrFail($request->academy_period_customer_id);
            $aca_per_cus->price = $payment->amount;
            $aca_per_cus->status = 1;
            $aca_per_cus->updater_id = $session_id;
            if($aca_per_cus->save()){
                return response()->json(["message"=>"Status Pembayaran diubah"]);
            }
        }
        return response()->json(["message"=>"Terjadi Kesalahan"],450);
     }

     public function customerDestroy($id){
        $data = AcademyPeriodCustomer::findOrFail($id);
        if($data->delete()){
            return response()->json(["status"=>"ok"]);
        }
        return response()->json(["message"=>"Terjadi Kesalahan"],450);
     }
}
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
     public function index(Request $request){
        $data = Academy::where('id','>',0);
        if($request->has('search')){
            $data->where('name','like','%'.$request->search.'%');
        }
        $data = Academy::with('updater')->orderBy('id','desc')->paginate(10);
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

     public function store(Request $request){
        $session_id = Auth::user()->id;
        $datas = $request->all();

        $validator = Validator::make($datas, rules_lists(__CLASS__, __FUNCTION__));
        if ($validator->fails()) return response()->json(['errors'=>($validator->messages())],422);

        $datas["updater_id"] = $session_id;
        $data = Academy::create($datas);

        return response()->json(['data'=>$data,'message'=>"Berhasil menambah data Kelas ".$request->name]);
     }

     public function destroy($id){
        $data = Academy::findOrFail($id);
        if($data->delete()){
            return response()->json(["status"=>"ok"]);
        }
        return response()->json(["message"=>"Terjadi Kesalahan"],450);
     }

     public function customerStore(Request $request){
        $datas = $request->all();

        $validator = Validator::make($datas, rules_lists(__CLASS__, __FUNCTION__));
        if ($validator->fails()) return response()->json(['errors'=>($validator->messages())],422);

        $cus = Customer::updateOrCreate(["email"=>$datas["email"]],$datas);
        if($cus){
            //cek ada akademi X gak
            $acas = Academy::whereIn("id",$datas["ja_ids"])->pluck('id')->toArray();
            if(count($acas) != count($datas["ja_ids"])) return response()->json(["message"=>"Periksa pemilihan Kelas kembali"],450);

            //cek akademi X lagi buka ato gak di periode ini
            $aca_pers = AcademyPeriod::whereIn("academy_id",$acas)->where('active',1)->get();
            if(count($aca_pers) != count($datas["ja_ids"])) return response()->json(["message"=>"Periksa pemilihan Kelas kembali"],450);

            //cek customer A udah terdaftar belum di akademi X
            $amount = 0;
            $aca_per_cus_ids = [];
            foreach ($aca_pers as $aca_per) {
                $aca_per_cus = AcademyPeriodCustomer::where(["academy_period_id"=>$aca_per->id, "customer_id"=>$cus->id])->first();
                if($aca_per_cus){
                    //jika sudah terdaftar dan pembayaran berhasil atau pending, tidak bisa melanjutkan pembayaran
                    if($aca_per_cus->status == 1 || $aca_per_cus->status == 2 )
                        return response()->json(["message"=>"Kamu sudah pernah mendaftar Akademi ".$aca_per_cus->academy_period->academy->name.". Pendaftaran dibatalkan."],450);
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
                    array_push($aca_per_cus_ids, $aca_per_cus->id);
                    $amount += $aca_per->price;
                }
            }            

            if(Auth::check()){
                return response()->json(["message"=>"Berhasil tambah peserta","payment"=>false]);
            }

            $last_payment_id = Payment::orderBy('id','desc')->value('id');
            $last_payment_id_plus_one = $last_payment_id + 1;
            $code = "ORD-JOBHUNACADEMY-".$aca_per_cus->id."-".$last_payment_id_plus_one;
            $code = md5(env("MIDTRANS_MD5_ADDITIONAL_CODE").$code);

            //===================================
            $payment = new Payment;
            $payment->amount = $amount;
            $payment->code = $code;
            $payment->via = "midtrans";
            $payment->save();

            if(!$payment)
                return response()->json(["message"=>"Terjadi Kesalahan"],450);

            $res = AcademyPeriodCustomer::whereIn('id',$aca_per_cus_ids)->update(["payment_id"=>$payment->id]);
            if(!$res)
                return response()->json(["message"=>"Terjadi Kesalahan"],450);
            //===================================

            $snap_response = $this->getMidtransToken($code,$amount);
            
            if($snap_response["status"]){
                return response()->json(["data"=>$snap_response["data"],"payment"=>true]);
            }else{
                return response()->json(["message"=>$snap_response["message"],"payment"=>true],450);
            }

        }
        return response()->json(["message"=>"Terjadi Kesalahan"],450);
     }

     private function getMidtransToken($code,$amount){
        //=========================untuk testing aja, bypass data
        return ["status"=>true,"data"=>["redirect_url"=>"https://localhost:3000/academy/customer"]];
        //=======================================================

        $serverAuthKey = "Basic ".base64_encode(env("MIDTRANS_SERVER_KEY").":");
        if($serverAuthKey){
            $response = Curl::to('https://app.sandbox.midtrans.com/snap/v1/transactions')
            ->withHeader('Accept: application/json')
            ->withContentType('application/json')
            ->withAuthorization($serverAuthKey)
            ->withData(["transaction_details"=>["order_id"=>$code, "gross_amount"=>$amount],/*"callbacks"=>["finish"=>"localhost:3000/academy/form"]*/])
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
            $status_string = "";
            if(in_array($tc, ["capture","settlement"])){
                $aca_per_cus_status = 1;
                $status_string = "sudah berhasil :)";
            }
            else if($tc == "pending"){
                $aca_per_cus_status = 2;
                $status_string = "masih pending ...";
            }
            else{
                $aca_per_cus_status = 3;
                $status_string = "gagal :'(";
            }

            $res = AcademyPeriodCustomer::where('payment_id',$payment->id)->update(["status",$aca_per_cus_status]);

            if(!$res){
                return response()->json(["message"=>"Terjadi Kesalahan"],450);
            }
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
        $price = $data->sum('apc.price');
        $data = $data->select("apc.*","a.name as academy_name","c.name as customer_name","ap.period")->paginate(10);
        return response()->json(["data"=>$data->appends($request->all()),"total_price"=>$price]);
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
        if(in_array($data->status,[1,2])){
            return response()->json(["message"=>"Tidak bisa hapus peserta yang telah membayar"],450);
        }
        if($data->delete()){
            return response()->json(["status"=>"ok"]);
        }
        return response()->json(["message"=>"Terjadi Kesalahan"],450);
     }
}
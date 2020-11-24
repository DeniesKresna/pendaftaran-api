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
use Illuminate\Support\Facades\DB;

class AcademyController extends Controller
{
     public function index(){
        $data = Academy::with('updater')->paginate(10);
        return response()->json(['data'=>$data]);
     }

     public function list(){
        $data = Academy::all();
        return response()->json(['data'=>$data]);
     }

     public function customerStore(Request $request){
     	$datas = $request->all();

     	$validator = Validator::make($datas, rules_lists(__CLASS__, __FUNCTION__));
        if ($validator->fails()) return response()->json(['errors'=>($validator->messages())],422);

        $cus = Customer::updateOrCreate(["email"=>$datas["email"]],$datas);
        if($cus){
        	$aca = Academy::where("name",$datas["ja_name"])->first();
        	if(!$aca) return response()->json(["message"=>"Tidak ada Akademi ".$datas["ja_name"]],450);
        	$aca_per = AcademyPeriod::where("academy_id",$aca->id)->where('active',1)->first();
        	if(!$aca_per) return response()->json(["message"=>"Penyelenggaraan Akademi ".$datas["ja_name"]." ditutup"],450);

        	$check_aca_per_cus = AcademyPeriodCustomer::where(["academy_period_id"=>$aca_per->id, "customer_id"=>$cus->id])->first();
        	if($check_aca_per_cus) return response()->json(["message"=>"Kamu sudah mendaftar Akademi ".$datas["ja_name"]],450);

        	$aca_per_cus = AcademyPeriodCustomer::create([
        		"academy_period_id"=>$aca_per->id, "customer_id"=>$cus->id, "price"=>$aca_per->price
        	]);
        	if($aca_per_cus){
        		return response()->json(["message"=>"Anda telah terdaftar di Akademi ".$datas["ja_name"]." periode ".$aca_per->period]);
        	}
        }
        return response()->json(["message"=>"Terjadi Kesalahan"],450);
     }
}
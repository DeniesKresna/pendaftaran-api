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

class AcademyPeriodController extends Controller
{
     public function index(Request $request){
        $data = AcademyPeriod::where('id','>',0);

        if($request->has('ja_id')){
            if(trim($request->ja_id) != ""){
                $data = $data->where('academy_id',$request->ja_id);
            }
        }
        if($request->has('period')){
            if(trim($request->period) != ""){
                $data = $data->where('period',$request->period);
            }
        }   
        $data = $data->with(['academy','updater','mentors'])->paginate(10);
        return response()->json(["data"=>$data->appends($request->all())]);
     }

     public function store(Request $request){
        $session_id = Auth::user()->id;

        //cek academy exist
        $res = AcademyPeriod::where(["academy_id"=>$request->academy_id, "period"=>$request->period])->first();
        if($res)
            return response()->json(["message"=>"Kelas yang kamu maksud sudah pernah dibuat"],450);

        // validation
        $datas = $request->all();
        $validator = Validator::make($datas, rules_lists(__CLASS__, __FUNCTION__));
        if ($validator->fails()) return response()->json(['errors'=>($validator->messages())],422);

        $datas["updater_id"] = $session_id;
        $data = AcademyPeriod::create($datas);
        if($data){
            $data->mentors()->attach($datas["mentor_ids"]);
        }else{
            return response()->json(["message"=>"Terjadi Kesalahan"],450);
        }

        if($request->active == 1){
            $res = AcademyPeriod::where("academy_id",$request->academy_id)->where("id",'!=',$data->id)->update(["active"=>0]);
        }

        return response()->json(['data'=>$data,"message"=>"Berhasil menambahkan periode kelas"]);
     }

     public function update(Request $request, $id){
        $session_id = Auth::user()->id;

        // validation
        $datas = $request->all();
        $validator = Validator::make($datas, rules_lists(__CLASS__, __FUNCTION__));
        if ($validator->fails()) return response()->json(['errors'=>($validator->messages())],422);

        $datas["updater_id"] = $session_id;

        $data = AcademyPeriod::findOrFail($id);
        
        if(!$data->update(exclude_array($datas,["active","description","price","mentor_id","updater_id"]))){
            return response()->json(["message"=>"Terjadi Kesalahan"],450);
        }
        $data->mentors()->sync($datas["mentor_ids"]);
        if($request->active == 1){
            $res = AcademyPeriod::where("academy_id",$request->academy_id)->where("id",'!=',$id)->update(["active"=>0]);
        }

        return response()->json(['data'=>$data,"message"=>"Berhasil mengubah periode kelas"]);
     }
     public function destroy($id){
        $data = AcademyPeriod::findOrFail($id);
        $customers = $data->customers;
        if(count($customers)>0)
            return response()->json(["message"=>"Tidak bisa menghapus periode kelas yang sudah memiliki peserta"],450);

        if($data->delete()){
            return response()->json(["status"=>"ok"]);
        }
        return response()->json(["message"=>"Terjadi Kesalahan"],450);
     }
}
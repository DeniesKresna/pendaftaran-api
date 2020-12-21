<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Mentor;
use App\Models\AcademyPeriod;
use Illuminate\Support\Facades\DB;

class MentorController extends Controller
{
     public function index(Request $request){
        $data = Mentor::where("id",">",0);

        if($request->has('search')){
            if(trim($request->search) != ""){
                $data = $data->where('name','like','%'.$request->search.'%');
            }
        }   
        $data = $data->with('updater')->paginate(10);
        return response()->json(["data"=>$data->appends($request->all())]);
     }

     public function list(Request $request){
        $data = Mentor::where("id",">",0);
        if($request->has('search')){
            if(trim($request->search) != ""){
                $data = $data->where('name',$request->search);
            }
        }   
        $data = $data->orderBy("name")->get();
        return response()->json(["data"=>$data]);
     }

     public function store(Request $request){
        $session_id = Auth::user()->id;

        // validation
        $datas = $request->all();
        $validator = Validator::make($datas, rules_lists(__CLASS__, __FUNCTION__));
        if ($validator->fails()) return response()->json(['errors'=>($validator->messages())],422);

        $datas["updater_id"] = $session_id;
        $data = Mentor::create($datas);

        return response()->json(['data'=>$data,"message"=>"Berhasil tambah Mentor"]);
     }

     public function update(Request $request, $id){
        $session_id = Auth::user()->id;

        // validation
        $datas = $request->all();
        $validator = Validator::make($datas, rules_lists(__CLASS__, __FUNCTION__,["id"=>$id]));
        if ($validator->fails()) return response()->json(['errors'=>($validator->messages())],422);

        $datas["updater_id"] = $session_id;

        $data = Mentor::findOrFail($id);
        $res = $data->update($datas);
        if(!$res){
            return response()->json(["message"=>"Terjadi Kesalahan"],450);
        }

        return response()->json(['data'=>$data,"message"=>"Berhasil ubah Mentor"]);
     }
     public function destroy($id){
        $data = Mentor::findOrFail($id);
        $aca_pers = $data->academy_periods;
        foreach ($aca_pers as $aca_per) {
            if(count($aca_per->customers) > 0){
                return response()->json(["message"=>"Tidak bisa hapus Mentor yang sudah mendampingi pelanggan di JA ataupiun JAC"],450);
            }
        }
        
        if($data->delete()){
            return response()->json(["status"=>"ok"]);
        }
        return response()->json(["message"=>"Terjadi Kesalahan"],450);
     }
}
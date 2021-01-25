<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Expert;
use App\Models\Mentor;
use Illuminate\Support\Facades\DB;

class ExpertController extends Controller
{
     public function index(Request $request){
        $data = DB::table('experts as e')->join('mentors as m','e.mentor_id','=','m.id')
            ->join('users as u','u.id','=','e.updater_id')
            ->select('e.*','u.name as updater_name','m.name as mentor_name');

        if($request->has('search')){
            if(trim($request->search) != ""){
                $data = $data->where('m.name','like','%'.$request->search.'%');
            }
        } 

        if($request->has('active')){
            if(in_array($request->active, [0,1])){
                $data = $data->where('e.active',$request->active);
          }
        }  

        $data = $data->orderBy('e.id','desc')->paginate(10);
        return response()->json(["data"=>$data->appends($request->all())]);
     }

     public function list(Request $request){
        $data = Expert::where("id",">",0);
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

        $expert = Expert::where(['mentor_id'=>$request->mentor_id, 'job'=>$request->job])->first();
        if($expert){
            return response()->json(["message"=>"Gagal menambah expert. Data tersebut telah ada"],450);
        }

        $datas["updater_id"] = $session_id;
        $data = Expert::create($datas);

        return response()->json(['data'=>$data,"message"=>"Berhasil tambah Expert"]);
     }

     public function update(Request $request, $id){
        $session_id = Auth::user()->id;

        // validation
        $datas = $request->all();
        $validator = Validator::make($datas, rules_lists(__CLASS__, __FUNCTION__,["id"=>$id]));
        if ($validator->fails()) return response()->json(['errors'=>($validator->messages())],422);

        $datas["updater_id"] = $session_id;

        $data = Expert::findOrFail($id);
        $data->update($datas);

        return response()->json(['data'=>$data,"message"=>"Berhasil ubah Expert"]);
     }
     public function destroy($id){
        $data = Expert::findOrFail($id);
        /*
        $aca_pers = $data->academy_periods;
        foreach ($aca_pers as $aca_per) {
            if(count($aca_per->customers) > 0){
                return response()->json(["message"=>"Tidak bisa hapus Mentor yang sudah mendampingi pelanggan di JA ataupiun JAC"],450);
            }
        }*/
        
        if($data->delete()){
            return response()->json(["status"=>"ok"]);
        }
        return response()->json(["message"=>"Terjadi Kesalahan"],450);
     }

     public function page_data(Request $request){
        $data = DB::table('experts as e')->join('mentors as mn','mn.id','=','e.mentor_id')->leftJoin('medias as md','md.id','=','mn.media_id')->where('e.active',1)->select('e.*','mn.name','md.url');
        if($request->has('limit')) $data->limit($request->limit);
        return response()->json(["data"=>$data->get()]);
     }
}
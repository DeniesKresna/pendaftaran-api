<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;

class CustomerController extends Controller
{
     public function showByEmail($email){
        $data = Customer::where('email',$email)->first();
        return response()->json(['data'=>$data]);
     }
}
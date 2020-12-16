<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademyPeriodCustomer;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
     public function show($id){
        $data = Payment::findOrFail($id)->load('academy_period_customers','academy_period_customers.academy_period','academy_period_customers.academy_period.academy','academy_period_customers.customer'
    );
        return response()->json(['data'=>$data]);
     }
}
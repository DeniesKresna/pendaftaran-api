<?php
namespace App\Traits;

use App\Models\Coupon;

trait Promo{
    public function actual_price($amount, $code, $ja=[]){
        $code = trim($code);
        if($code == ""){
            return ["amount"=>$amount, "status"=>true];
        }

        $coupon = Coupon::where("code",$code)->first();
        if(!$coupon){
            return ["amount"=>$amount, "status"=>false];
        }
        if($coupon->type == "simple"){
            $amount -= $coupon->cut;
            return ["amount"=>$amount, "status"=>true];
        }
        else if($coupon->type == "pahe"){
            if(count($ja) >= $coupon->extra_variable){
                $amount -= $coupon->cut;
                return ["amount"=>$amount, "status"=>true];
            }
        }
        return ["amount"=>$amount, "status"=>false];
    }
}
<?php
if (! function_exists('rules_lists')) {
    function rules_lists($controller, $method="any", $custom_var = [])
    {
        $path = explode('\\', $controller);
        $controller = array_pop($path);

        //================================Device Line=======================
        if($controller == "DeviceLineController"){
            if($method=="store"){
                return [
                    'car_id' => [new \App\Rules\isUnique("device_lines","car_id"), new \App\Rules\isExists("cars","id")],
                    'driver_id' => [new \App\Rules\isUnique("device_lines","driver_id"), new \App\Rules\isExists("drivers","id")],
                    'box_id' => [new \App\Rules\isUnique("device_lines","box_id"), new \App\Rules\isExists("boxes","id")],
                    'device_id' => [new \App\Rules\isUnique("device_lines","device_id"), new \App\Rules\isExists("devices","id")],
                    "device_type_id"=>[new \App\Rules\isExists("device_types","id")],
                    'device_type' => ['in:led,screen'],
                ];
            } elseif($method=="update"){
                return [
                    'car_id' => [new \App\Rules\isUnique("device_lines","car_id",["id"=>$custom_var["id"]]), new \App\Rules\isExists("cars","id")],
                    'driver_id' => [new \App\Rules\isUnique("device_lines","driver_id",["id"=>$custom_var["id"]]), new \App\Rules\isExists("drivers","id")],
                    'box_id' => [new \App\Rules\isUnique("device_lines","box_id",["id"=>$custom_var["id"]]), new \App\Rules\isExists("boxes","id")],
                    'device_id' => [new \App\Rules\isUnique("device_lines","device_id",["id"=>$custom_var["id"]]), new \App\Rules\isExists("devices","id")],
                    "device_type_id"=>[new \App\Rules\isExists("device_types","id")],
                    'device_type' => ['in:led,screen'],
                ];
            }

        }

        //================================Academy =======================
        if($controller == "AcademyController"){
            if($method=="customerStore"){
                return [
                    'ja_ids' => 'array|required|min:1',
                    'name' => 'required|max:191',
                    'email' => 'required|email|max:191',
                    'phone' => 'required|max:191',
                    'reference' => 'required|max:191',
                    'profession' => 'required|max:191',
                    'domicile' => 'required|max:191'
                ];
            }
            else if($method=="paymentStore"){
                return [
                    'transaction_id' => 'required|max:191',
                    'amount' => 'required|max:191',
                    'academy_period_customer_id' => [new \App\Rules\isExists("academy_period_customer","id"),'required'],
                    'via' => 'required|max:191',
                ];
            }
            else if($method=="store"){
                return [
                    'name' => 'required|max:191|unique:academies',
                ];
            }
        }

        //================================Academy Period=======================
        if($controller == "AcademyPeriodController"){
            if($method=="store"){
                return [
                    'academy_id' => [new \App\Rules\isExists("academies","id"),'required'],
                    'period' => 'required|date|date_format:Y-m-d',
                    'active' => 'required|in:0,1',
                    'description' => 'max:191',
                    'price' => 'required|numeric|min:1'
                ];
            }
            else{
                return [
                    'academy_id' => [new \App\Rules\isExists("academies","id")],
                    'period' => 'date|date_format:Y-m-d',
                    'active' => 'in:0,1',
                    'description' => 'max:191',
                    'price' => 'numeric|min:1'
                ];
            }

        }
        
        //========================Global============================================

        else if($controller == "Global"){
            if($method=="any"){
                return [
                    'start_date' => 'date|date_format:Y-m-d',
                    'end_date' => 'date|date_format:Y-m-d|after_or_equal:start_date',
                    'start_time' => 'date_format:Y-m-d H:i:s',
                    'end_time' => 'date_format:Y-m-d H:i:s'
                ];
            }
        }
        return [];
    }
}
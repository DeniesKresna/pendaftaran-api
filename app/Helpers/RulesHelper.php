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
                    'ja_name' => 'max:191',
                    'name' => 'required|max:191',
                    'email' => 'required|email|max:191',
                    'phone' => 'required|max:191',
                    'reference' => 'required|max:191',
                    'profession' => 'required|max:191',
                    'domicile' => 'required|max:191'
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
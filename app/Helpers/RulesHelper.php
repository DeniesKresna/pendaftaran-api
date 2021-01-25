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
                    'customer_list' => 'array|required|min:1',
                    'via' => 'required|max:191',
                ];
            }
            else if($method=="store"){
                return [
                    'name' => 'required|max:191|unique:academies',
                    'description' => 'max:191',
                    'file' => 'file|max:400|mimes:jpg,jpeg,gif,bmp,png'
                ];
            }
            else if($method=="update"){
                return [
                    'name' => [new \App\Rules\isUnique("academies","name",["id"=>$custom_var["id"]]), 'required', 'max:191'],
                    'description' => 'max:191',
                    'file' => 'file|max:400|mimes:jpg,jpeg,gif,bmp,png'
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

        //================================Mentor=======================
        if($controller == "MentorController"){
            if($method=="store"){
                return [
                    'name' => 'required|max:191',
                    'company_name' => 'required|max:191',
                    'position' => 'required|max:191',
                    'education' => 'required',
                    'experience' => 'required',
                    'linkedin_link' => 'required|max:191',
                    'email' => 'required|max:191|email|unique:mentors',
                    'phone' => 'required|max:191|unique:mentors',
                    'file' => 'file|max:400|mimes:jpg,jpeg,gif,bmp,png'
                ];
            }
            else{
                return [
                    'name' => 'required|max:191',
                    'company_name' => 'required|max:191',
                    'position' => 'required|max:191',
                    'education' => 'required',
                    'experience' => 'required',
                    'linkedin_link' => 'required|max:191',
                    'email' => [new \App\Rules\isUnique("mentors","email",["id"=>$custom_var["id"]]), 'email', 'required', 'max:191'],
                    'phone' => [new \App\Rules\isUnique("mentors","phone",["id"=>$custom_var["id"]]), 'required', 'max:191'],
                    'file' => 'file|max:400|mimes:jpg,jpeg,gif,bmp,png'
                ];
            }

        }

        //================================Expert=======================
        if($controller == "ExpertController"){
            if($method=="store"){
                return [
                    'mentor_id' => [new \App\Rules\isExists("mentors","id"),'required'],
                    'job' => 'required|max:191',
                    'price' => 'required|numeric',
                    'description' => 'max:300',
                    'active' => 'in:1,0',
                ];
            }
            else{
                return [
                    'mentor_id' => [new \App\Rules\isExists("mentors","id"),'required'],
                    'job' => 'required|max:191',
                    'price' => 'required|numeric',
                    'description' => 'max:300',
                    'active' => 'in:1,0',
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
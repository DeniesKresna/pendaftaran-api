<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class isUnique implements Rule
{
    var $table ,$column, $not_in;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($table,$column, $not_in = [])
    {
        $this->not_in = $not_in;
        $this->table = $table;
        $this->column = $column;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(empty($value) || $value == null || $value == "null") return true;
        $result = DB::table($this->table)->where($this->column,$value);
        if (!empty($this->not_in)) {
            foreach(to_object($this->not_in) as $key => $val) {
                $result = $result->where($key , "<>", $val);
            }
        }
        $result = $result->first();
        if (!$result) return true;
        else return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Not unique value for '.$this->column.".";
    }
}



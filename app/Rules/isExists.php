<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class isExists implements Rule
{
    var $table ,$column;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($table,$column)
    {
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
        $result = DB::table($this->table)->where($this->column,$value)->first();
        if ($result) return true;
        else return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}

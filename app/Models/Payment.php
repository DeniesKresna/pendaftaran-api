<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 02 Mar 2020 15:44:02 +0700.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
	protected $fillable = [
		'amount',
		'code',
		'transaction_status',
		'transaction_id',
		'via',
	];

	public function academy_period_customers(){
		return $this->hasMany("App\Models\AcademyPeriodCustomer");
	}
}

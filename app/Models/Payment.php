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
		'academy_period_customer_id',
	];

	public function academy_period_customer(){
		return $this->belongsTo("App\Models\AcademyPeriodCustomer");
	}
}

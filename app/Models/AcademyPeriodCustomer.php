<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 02 Mar 2020 15:44:02 +0700.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyPeriodCustomer extends Model
{
	protected $table = "academy_period_customer";

	protected $fillable = [
		'academy_period_id',
		'customer_id',
		'price',
		'description'
	];

	public function academy_period(){
		return $this->belongsTo("App\Models\AcademyPeriod");
	}

	public function customer(){
		return $this->belongsTo("App\Models\Customer");
	}
}

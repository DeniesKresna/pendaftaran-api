<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 02 Mar 2020 15:44:02 +0700.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
	protected $fillable = [
		'name',
		'email',
		'phone',
		'profession',
		'reference',
		'domicile',
	];

	public function academy_periods(){
		return $this->belongsToMany("App\Models\AcademyPeriod");
	}
}

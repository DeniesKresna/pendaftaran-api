<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 02 Mar 2020 15:44:02 +0700.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Academy extends Model
{
	protected $fillable = [
		'name',
		'updater_id'
	];

	public function updater(){
		return $this->belongsTo("App\User","updater_id");
	}

	public function academy_period(){
		return $this->hasMany("App\Models\AcademyPeriod");
	}
}

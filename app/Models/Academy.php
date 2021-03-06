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
		'description',
		'updater_id'
	];

	public function updater(){
		return $this->belongsTo("App\User","updater_id");
	}

	public function academy_periods(){
		return $this->hasMany("App\Models\AcademyPeriod");
	}

	public function media(){
		return $this->belongsTo("App\Models\Media");
	}
}

<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 02 Mar 2020 15:44:02 +0700.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mentor extends Model
{
	protected $fillable = [
		'name',
		'company_name',
		'position',
		'education',
		'experience',
		'linkedin_link',
		'email',
		'phone',
		'updater_id'
	];

	public function updater(){
		return $this->belongsTo("App\User","updater_id");
	}

	public function academy_periods(){
		return $this->belongsToMany("App\Models\AcademyPeriod");
	}
}
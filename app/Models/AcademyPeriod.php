<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 02 Mar 2020 15:44:02 +0700.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademyPeriod extends Model
{
	protected $fillable = [
		'academy_id',
		'period',
		'active',
		'mentor_id',
		'updater_id'
	];

	public function updater(){
		return $this->belongsTo("App\User","updater_id");
	}

	public function academy(){
		return $this->belongsTo("App\Models\Academy");
	}

	public function mentor(){
		return $this->belongsTo("App\Models\Mentor");
	}

	public function customers(){
		return $this->belongsToMany("App\Models\Customer");
	}
}
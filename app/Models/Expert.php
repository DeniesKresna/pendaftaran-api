<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 02 Mar 2020 15:44:02 +0700.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expert extends Model
{
	protected $fillable = [
		'mentor_id',
		'job',
		'price',
		'description',
		'updater_id',
		'active'
	];

	public function updater(){
		return $this->belongsTo("App\User","updater_id");
	}
	
	public function mentor(){
		return $this->belongsTo("App\Models\Mentor");
	}
}
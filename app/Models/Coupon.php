<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 02 Mar 2020 15:44:02 +0700.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
	protected $fillable = [
		'code',
		'type',
		'cut',
		'extra_variable',
		'description',
		'updater_id',
	];

	public function updater(){
		return $this->belongsTo("App\User");
	}
}

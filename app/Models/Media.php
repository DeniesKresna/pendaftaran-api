<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 02 Mar 2020 15:44:02 +0700.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
	protected $table = 'medias';
	protected $fillable = [
		'name',
		'type',
		'url',
		'path',
		'updater_id',
	];

	public function updater(){
		return $this->hasMany("App\User");
	}
}

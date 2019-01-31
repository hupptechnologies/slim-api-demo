<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Login_token extends Model {
	protected $table = 'sl_login_token';
	protected $primaryKey= 'id';
	protected $fillable = ['user_id','token','push_token','device'];
}
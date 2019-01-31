<?php  
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
	protected $table = 'sl_users';
	protected $primaryKey= 'id';
	protected $fillable = ['first_name','last_name','email','password','forgot_password_token','created_at','updated_at'];
}
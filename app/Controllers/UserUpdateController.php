<?php
/* Copyright (C) Hupp Technologies, Inc - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Hupp Technologies <hello@hupp.in>, September 2018
 */
namespace App\Controllers;

use App\Models\User;
use App\Models\Login_token;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Validator;

class UserUpdateController {

	public function update_profile(Request $request, Response $response){
		$token = $request->getHeader('authorization');
		$t = explode(" ", $token[0]);
		$user = Login_token::where('token',$t[1])->first();
		// print_r($request->getAttribute('decoded_token_data');
		if($user != ''){
			$validation = Validator::make($request, [
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required|email',
			'password' => 'required',
			]);
			if ($validation->fails()) {

				$errors = $validation->errors();

				$caught_errors = array();
				foreach($errors->all() as $message){
					array_push($caught_errors,$message);
					// $error_array = $message;
				}

				$result = array('success'=> false,'error' => true,'response'=> array('message'=> implode(",", implode(",", $caught_errors)),'result'=> array()),'mdf_dt_time'=> strtotime(date('Y-m-d H:i:s')));

				return $response->withJson($result);

			} elseif (User::where('email', $request->getParam('email'))->where('id','!=',$user->user_id)->first()) {
				$caught_errors = array(
					'email already exist!'
				);
				$result = array('success'=> false,'error' => true,'response'=> array('message'=> implode(",", $caught_errors),'result'=> array()),'mdf_dt_time'=> strtotime(date('Y-m-d H:i:s')));

				return $response->withJson($result);
			}
			else{
				$first_name = $request->getParam('first_name');
				$last_name = $request->getParam('last_name');
				$email = $request->getParam('email');
				$password = md5($request->getParam('password'));
				$user_id = $request->getParam('id');

				$user_data = User::find($user_id);
				$user_data->first_name = $first_name;
				$user_data->last_name = $last_name;
				$user_data->email = $email;
				$user_data->password = $password;
				$user_data->save();

				$result = array('success'=> true,'error' => false,'response'=> array('message'=> 'Data updated.','result'=> array()),'mdf_dt_time'=> strtotime(date('Y-m-d H:i:s')));

				return $response->withJson($result);
			}
		}
		else{
			$result = array(
	                'success' => false,
	                'error' => true,
	                'response' => array('message' => 'Token Expired or not found ,Please login again.'),
	                'mdf_dt_time' => strtotime(date('Y-m-d H:i:s'))
	            );
	    	return $response->withJson($result);
		}
		return $response->withJson($user->user_id);
	}

	public function update_profile_photo(Request $request,Response $response){
		$validation = Validator::make($request, [
			// 'profile_image' => 'required',
		]);

		// When Validator Fails Return Response
		if ($validation->fails()) {

			$errors = $validation->errors();

			$caught_errors = array();
			foreach($errors->all() as $message){
				array_push($caught_errors,$message);
				// $error_array = $message;
			}

			$result = array('success'=> false,'error' => true,'response'=> array('message'=> implode(",", implode(",", $caught_errors)),'result'=> array()),'mdf_dt_time'=> strtotime(date('Y-m-d H:i:s')));

			return $response->withJson($result);

		}
		else{
			$user_id = $request->getParam('id');
			$image = time().'.'.end(explode(".", $_FILES['profile_image']['name']));
			$ext = $_FILES['profile_image']['type'];
			$array = [
				'image/jpeg','image/jpg','image/png','image/gif'
			];
			if (in_array($ext, $array)) {
				move_uploaded_file($_FILES['profile_image']['tmp_name'], __DIR__ . '/../storage/profile_images/'.$image);
				$user_data = User::find($user_id);
				$user_data->profile_image = $image;
				$user_data->save();

				$result = array('success'=> true,'error' => false,'response'=> array('message'=> 'Image Uploaded.','result'=> array()),'mdf_dt_time'=> strtotime(date('Y-m-d H:i:s')));

				return $response->withJson($result);
			}
			else{
				$result = array('success'=> false,'error' => true,'response'=> array('message'=> 'Image format must be jpg,png,jpeg or gif.','result'=> array()),'mdf_dt_time'=> strtotime(date('Y-m-d H:i:s')));

				return $response->withJson($result);
			}
		}
	}
	public function push_token(Request $request, Response $response){

		// Validate request
		$validation = Validator::make($request, [
			'push_token' => 'required|string',
			'device' => 'required|string'
		]);

		// When Validator Fails Return Response
		if ($validation->fails()) {

			$errors = $validation->errors();

			$caught_errors = array();
			foreach($errors->all() as $message){
				array_push($caught_errors,$message);
				// $error_array = $message;
			}

			$result = array('success'=> false,'error' => true,'response'=> array('message'=> implode(",", $caught_errors),'result'=> array()),'mdf_dt_time'=> strtotime(date('Y-m-d H:i:s')));

			// return responce
			return $response->withJson($result);
		} else {
			
			// post parameter
			$push_token = $request->getParam('push_token');
			$device = $request->getParam('device');

			// get header
			$auth_token_array = $request->getHeader('authorization');
			$auth_token = explode(" ", $auth_token_array[0]);
			$auth_token_string = $auth_token[1];

			$collection = [
				'push_token' => $push_token,
				'device' => $device,
			];

			// reset password
			if (Login_token::where('token', $auth_token_string)->update($collection)) {

				// Responce result
				$result = array(
					'success' => true,
					'error' => false,
					'response' => array(
						'message' => 'Successfully added push token',
						'result'=> array()
					),
					'mdf_dt_time' => strtotime(date('Y-m-d H:i:s'))
				);

				// return responce
				return $response->withJson($result);

			} else {

				// Responce result
				$result = array(
					'success' => false,
					'error' => true,
					'response' => array(
						'message' => 'Somenthing went wrong!',
						'result'=> array()
					),
					'mdf_dt_time' => strtotime(date('Y-m-d H:i:s'))
				);

				// return responce
				return $response->withJson($result);

			}
		}
	}
}
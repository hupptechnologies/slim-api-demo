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
use \Firebase\JWT\JWT;
use App\Validator;
use \Slim\Http\Cookies;
use App\Mail;

class AuthController {

	public function signup(Request $request,Response $response,$args){

		// Validate request
		$validation = Validator::make($request, [
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required|email',
			'password' => 'required',
		]);

		// When Validator Fails Return Response
		if ($validation->fails()) {

			$errors = $validation->errors();

			$caught_errors = array();
			foreach($errors->all() as $message){
				array_push($caught_errors,$message);
			}

			$result = array('success'=> false,'error' => true,'response'=> array('message'=> implode(",", $caught_errors),'result'=> array()),'mdf_dt_time'=> strtotime(date('Y-m-d H:i:s')));

			return $response->withJson($result);

		} elseif (User::where('email', $request->getParam('email'))->first()) {
			$caught_errors = array(
				'email already exist!'
			);
			$result = array('success'=> false,'error' => true,'response'=> array('message'=> implode(",", $caught_errors),'result'=> array()),'mdf_dt_time'=> strtotime(date('Y-m-d H:i:s')));

			// return responce
			return $response->withJson($result);

		} else {

			// Store Input Fields
			$first_name = $request->getParam('first_name');
			$last_name = $request->getParam('last_name');
			$email = $request->getParam('email');
			$password = $request->getParam('password');

			// Create object of User Model
			$user = new User;

			$user->first_name = $first_name;
			$user->last_name = $last_name;
			$user->email = $email;
			$user->password = md5($password);

			// insert user database
			$user->save();

			// Responce reseult
			$result = array(
				'success' => true,
				'error' => false,
				'response' => array('message' => 'Sign Up Successfully','result' => $user->id),
				'mdf_dt_time' => strtotime(date('Y-m-d H:i:s'))
			);

			// return responce
			return $response->withJson($result);
		}
	}

	public function login(Request $request, Response $response, array $args){
	$input = $request->getParsedBody();

	$validation = Validator::make($request, [
		'email' => 'required|email',
		'password' => 'required',
	]);

	// When Validator Fails Return Response
	if ($validation->fails()) {

		$errors = $validation->errors();

		$caught_errors = array();
		foreach($errors->all() as $message){
			array_push($caught_errors,$message);
			// $error_array = $message;
		}

		$result = array('success'=> false,'error' => true,'response'=> array('message'=> $caught_errors,'result'=> array()),'mdf_dt_time'=> strtotime(date('Y-m-d H:i:s')));

		return $response->withJson($result);

	}
	else{

		$user = User::where('email',$input['email'])->first();
	 
		// verify email address.
		if(!$user) {
			$result = array(
	                'success' => false,
	                'error' => true,
	                'response' => array('message' => 'These credentials do not match our records.'),
	                'mdf_dt_time' => strtotime(date('Y-m-d H:i:s'))
	            );
			return $response->withJson($result);  
		}
	 

	    // verify password.
	    $pass = md5($request->getParam('password'));
	    
	    if ($pass != $user->password) {
	    	$result = array(
	                'success' => false,
	                'error' => true,
	                'response' => array('message' => 'These credentials do not match our records.'),
	                'mdf_dt_time' => strtotime(date('Y-m-d H:i:s'))
	            );
	        return $response->withJson($result);  
	    }
	    $settings = require __DIR__ . '/../../src/settings.php';
	    $app = new \Slim\App($settings);
	    $container = $app->getContainer();
	    $setting = $container->get('settings'); // get settings array.
	    $token = JWT::encode(['id' => $user->id, 'email' => $user->email,'time' => time()], $setting['jwt']['secret'], "HS256");
	    $login_token = new Login_token;
	    $login_token->user_id = $user->id;
	    $login_token->token = $token;
	    $login_token->save();

	    $result = array(
	                'success' => true,
	                'error' => false,
	                'response' => array('message' => 'Logged in Successfully','token' => $token),
	                'mdf_dt_time' => strtotime(date('Y-m-d H:i:s'))
	            );
	    return $response->withJson($result);
	}
	}

	public function logout(Request $request, Response $response){
		
		$token = $request->getHeader('authorization');
		$t = explode(" ", $token[0]);
        Login_token::where('token',$t[1])->delete();
        $result = array(
	                'success' => true,
	                'error' => false,
	                'response' => array('message' => 'Logged out Successfully.'),
	                'mdf_dt_time' => strtotime(date('Y-m-d H:i:s'))
	            );
		
        return $response->withJson($result);
	}

	public function password_reset_intial_request(Request $request, Response $response, array $args){

		// Validate request
		$validation = Validator::make($request, [
			'email' => 'required|email',
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

			return $response->withJson($result);
		} elseif (!User::where('email', $request->getParam('email'))->first()) {
			$caught_errors = array(
				'sorry no records found!'
			);
			$result = array('success'=> false,'error' => true,'response'=> array('message'=> implode(",", $caught_errors),'result'=> array()),'mdf_dt_time'=> strtotime(date('Y-m-d H:i:s')));

			return $response->withJson($result);
		} else {

			$to = $request->getParam('email');
			$from = 'andrew@hupp.in';
			$reset_key = mt_rand(100000, 999999);
			$body = $reset_key;

			// Send mail to verify user
			$mail = Mail::send($to,$from,$body);

			// update password_reset_token in database
			if ($mail['success'] === true) {
				$field_reset = [
					'forgot_password_token' => $reset_key
				];
				User::where('email', $to)->update($field_reset);
			}

			// Responce reseult
			$result = array(
				'success' => $mail['success'],
				'error' => $mail['error'],
				'response' => array(
					'message' => $mail['message'],
					'result'=> array()
				),
				'mdf_dt_time' => strtotime(date('Y-m-d H:i:s'))
			);

			// return responce
			return $response->withJson($result);
		}
	}

	public function password_reset_token_validation(Request $request, Response $response, array $args){

		// Validate request
		$validation = Validator::make($request, [
			'email' => 'required|email',
			'reset_token' => 'required|numeric'
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

			// find email and reset_token in database
			$user = User::where('email', $request->getParam('email'))->where('forgot_password_token', $request->getParam('reset_token'))->first();

			if (isset($user) && !empty($user) && !empty($user->id)) {
				$email = $request->getParam('email');
				$reset_token = $request->getParam('reset_token');

				// Responce result
				$result = array(
					'success' => true,
					'error' => false,
					'response' => array(
						'message' => 'Token validate',
						'result'=> array(
							'user_id' => $user->id,
						)
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
						'message' => 'Ivalid email or token',
						'result'=> array()
					),
					'mdf_dt_time' => strtotime(date('Y-m-d H:i:s'))
				);

				// return responce
				return $response->withJson($result);
			}
		}
	}

	public function password_reset(Request $request, Response $response, array $args){

		// Validate request
		$validation = Validator::make($request, [
			'email' => 'required|email',
			'reset_token' => 'required|numeric',
			'password' => 'required|string'
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

			return $response->withJson($result);
		} else {

			// find email and reset_token in database
			$user = User::where('email', $request->getParam('email'))->where('forgot_password_token', $request->getParam('reset_token'))->first();

			if (isset($user) && !empty($user)) {

				// Store parameters
				$email = $request->getParam('email');
				$reset_token = $request->getParam('reset_token');
				$password = md5(base64_decode($request->getParam('password')));

				// create collection for reset password
				$collection = array(
					'password' => $password,
					'forgot_password_token' => null
				);

				// reset password
				if (User::where('email', $email)->where('forgot_password_token', $reset_token)->update($collection)) {

					// Responce result
					$result = array(
						'success' => true,
						'error' => false,
						'response' => array(
							'message' => 'Successfully password reseted',
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
			} else {
				// Responce result
				$result = array(
					'success' => false,
					'error' => true,
					'response' => array(
						'message' => 'Ivalid email or token',
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
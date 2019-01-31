<?php
namespace App\Middleware;
use App\Models\Login_token;

/**
 * Authnetication for token
 */
class AuthMiddleware extends Middleware
{
	public function __invoke($request, $response, $next)
	{
		$auth_token_array = $request->getHeader('authorization');
		$auth_token = explode(" ", $auth_token_array[0]);
		$auth_token_string = $auth_token[1];

		if (Login_token::where('token', $auth_token_string)->first()) {
			$response = $next($request, $response);
			return $response;
		} else {
			$result = array('success'=> true,'error' => false,'response'=> array('message'=> 'Token expire','result'=> array()),'mdf_dt_time'=> strtotime(date('Y-m-d H:i:s')));

			return $response->withJson($result);
		}
	}
}
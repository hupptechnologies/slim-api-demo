<?php

$app->add(new \Tuupola\Middleware\JwtAuthentication([
    "path" => "/user", /* or ["/api", "/admin"] */
    "attribute" => "decoded_token_data",
    "secret" => "creatingslimsecureapi",
    "algorithm" => ["HS256"],
    "secure" => false,
    "error" => function ($response, $arguments) {
        $data = array(
            'success' => false,
            'error' => true,
            'test' => $dd,
            'response' => array('message' => $arguments["message"]),
            'mdf_dt_time' => strtotime(date('Y-m-d H:i:s'))
        );
        return $response
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));
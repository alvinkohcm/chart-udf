<?php

include(__DIR__."/../includes/functions.php");

use \Firebase\JWT\JWT;

//iss: The issuer of the token
//sub: The subject of the token
//aud: The audience of the token
//exp: This will probably be the registered claim most often used.
//     This will define the expiration in NumericDate value.
//     The expiration MUST be after the current date/time.
//nbf: Defines the time before which the JWT MUST NOT be accepted for processing
//iat: The time the JWT was issued. Can be used to determine the age of the JWT
//jti: Unique identifier for the JWT.
//     Can be used to prevent the JWT from being replayed.
//     This is helpful for a one time use token.

$key = "trAdingv1ew";
$token = array(
    "iss" => "upload.sgberjangka.com",
    "aud" => "charts.tradeprofx.com",
    "iat" => time(),
    "nbf" => time(),
	"exp" => (time()+(5*60))
);

 $jwt = JWT::encode($token, $key);	


try
{
 $jwt = JWT::encode($token, $key);	
}
catch (Exception $e)
{
 echo "FAILED";
}

$decoded = JWT::decode($jwt, $key, array('HS256'));

print_r($jwt);


?>
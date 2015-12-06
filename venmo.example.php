<?php
/*
 * Server side integration for Venmo API
 *
 * Please make sure to define client_id, client_secret and scopes before getting started
 *
 * For full list of scopes, please see Venmo API documentation. By default, this class implements the bare
 * minimum set of scopes to send money from one user to another.
 *
 * 1) Call getAuthURL from app and direct user to that URL in app
 * 2) Call exchangeToken and pass query param "code" returned from first request
 * 3) On success, store the entire object so that you have access to the access and refresh tokens
 * 4) Call getUser or Payment request as needed
 *
 * See https://developer.venmo.com for more info
 *
 * To Do
 * 1) Error Handling - https://developer.venmo.com/docs/errors
 * 2) Yii wrapper
 */ 

include 'Venmo.php';
    
$v = new Venmo;

$user_id = 145434160922624933;
$email = 'venmo@venmo.com';
$phone = 15555555555;
        
echo $v->getAuthURL();
echo "\r\n -----Done----- \r\n";

echo $v->exchangeToken();
echo $v->getUser();


echo $v->sendPayment();

echo $v->refreshAccessToken();

?>
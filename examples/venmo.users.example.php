<?php

// Venmo Users Example

include '../venmo.class.php';
include 'venmo.config.php';

try 
{   
    $v = new Venmo();
    $v->setAccessToken(EXAMPLE_USERS_ACCESS_TOKEN);
    $v->setEnviroment("SANDBOX");

    echo "Venmo Users Example\r\n";
    echo "-------------------\r\n\r\n";

    // getCurrentUserInfo
    echo "getCurrentUserInfo Test\r\nResponse: ";
    print_r($v->getCurrentUserInfo());
    echo "-------------------\r\n\r\n";

    // getUserFriends
    echo "getUserFriends Test - Current User\r\nResponse: ";
    print_r($v->getUserFriends("me"));
    
    // getUserInfo
    echo "getCurrentUserInfo Test - " . EXAMPLE_USERS_USER_ID . "\r\nResponse: ";
    print_r($v->getUserInfo(EXAMPLE_USERS_USER_ID));
    echo "-------------------\r\n\r\n";

    // getUserFriends
    echo "getUserFriends Test - " . EXAMPLE_USERS_USER_ID . "\r\nResponse: ";
    print_r($v->getUserFriends(EXAMPLE_USERS_USER_ID));
} 
catch (Exception $e) 
{
    echo "ERROR:venmo.users.example.php";
    print_r($e);   
}

?>
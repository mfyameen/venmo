<?php

// Venmo Payments Example

include '../venmo.class.php';
include 'venmo.config.php';

try 
{   
    $v = new Venmo();
    $v->setAccessToken(EXAMPLE_USERS_ACCESS_TOKEN);
    $v->setEnviroment("SANDBOX");

    echo "Venmo Payments Example\r\n";
    echo "-------------------\r\n\r\n";

    // sendPaymentCharge
    echo "sendPaymentCharge Test - User ID\r\nResponse: ";
    $to = array("user_id"=>"145434160922624933");   // Sandbox user
    print_r($v->sendPaymentCharge($to,'0.10','sendPaymentCharge Test - User ID','private'));
    echo "-------------------\r\n\r\n";

    // sendPaymentCharge
    echo "sendPaymentCharge Test - Email\r\nResponse: ";
    $to = array("email"=>"venmo@venmo.com");   // Sandbox user
    print_r($v->sendPaymentCharge($to,'0.20','sendPaymentCharge Test - Email','friends'));
    echo "-------------------\r\n\r\n";

    // sendPaymentCharge
    echo "sendPaymentCharge Test - Phone\r\nResponse: ";
    $to = array("phone"=>"15555555555");   // Sandbox user
    print_r($v->sendPaymentCharge($to,'0.30','sendPaymentCharge Test - Phone','public'));
    echo "-------------------\r\n\r\n";

    // getRecentPayments
    echo "getRecentPayments Test\r\nResponse: ";
    print_r($v->getRecentPayments());
    echo "-------------------\r\n\r\n";

    // sendPaymentCharge
    echo "getPaymentInformation Test\r\nResponse: ";
    print_r($v->getPaymentInformation(EXAMPLE_PAYMENTS_PAYMENT_ID));
    echo "-------------------\r\n\r\n";
    


} 
catch (Exception $e) 
{
    echo "ERROR:venmo.users.example.php";
    print_r($e);   
}

?>
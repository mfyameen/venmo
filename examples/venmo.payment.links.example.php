<?php

// Venmo Payment Links Example

include '../venmo.class.php';
include 'venmo.config.php';

try 
{   
    $v = new Venmo();
    $v->setAccessToken(EXAMPLE_USERS_ACCESS_TOKEN);
    $v->setEnviroment("SANDBOX");

    echo "Venmo Payment Link Example\r\n";
    echo "-------------------\r\n\r\n";

    // generatePaymentLink
    $type = "pay";
    $recipients = array("hamilton@venmo.com","646.863.9557","john");
    $amount = "1.25";
    $note = "Test Note";
    $audience = "private";
    $sharing = "f";

    echo $v->generatePaymentLink($type, $recipients, $amount, $note, $audience, $sharing);
    
} 
catch (Exception $e) 
{
    echo "ERROR:venmo.payment.links.example.php";
    print_r($e);   
}

?>
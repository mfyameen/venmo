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
 * 3) On success, store the entire response so that you have access to the access and refresh tokens
 * 4) Call getUser or Payment request as needed
 *
 * See https://developer.venmo.com for more info
 *
 * To Do
 * 1) Error Handling - https://developer.venmo.com/docs/errors
 * 2) Yii wrapper
 */

require_once('config.php');

class Venmo{
    function __construct(){
        if(ENVIRONMENT=='PRODUCTION'){
            define('API_URL','https://api.venmo.com/v1');
        }else{
            define('API_URL','https://sandbox-api.venmo.com/v1');
        }
    }
    
    /*
    *
    */    
    public function getAuthURL(){
        $csrf_token = md5(uniqid(rand(), true)); //CSRF style token should be saved to implement CSRF check
        
        $fields = array(
            'client_id' => CLIENT_ID,
            'scope' => SCOPES,
            'response_type' => 'code', //required for server side token exchange
            'state' => $csrf_token
        );
        
        return API_URL.'/oauth/authorize?'.http_build_query($fields);
    }

    /*
    *
    */
    public function exchangeToken($authorization_code){
        //make sure client send authorization code, something like
        //$authorization_code = $_GET["code"];
        
        $url = API_URL.'/oauth/access_token';

        $fields = array(
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'code' => $authorization_code
        ); 

        $response = $this->curlMethod($url,$fields);
        return $response;
    }
    
    /*
    * Access Tokens eventually expire, but can be renewed easily using this method
    */
    public function refreshAccessToken($access_object){
        $url = API_URL.'/oauth/access_token';

        $fields = array(
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'refresh_token' => getRefreshToken($access_object)
        ); 

        $response = $this->curlMethod($url,$fields);
        return $response;
    }
    
    /*
    *
    */
    private function getAccessToken($access_object){
        return json_decode($access_object)->access_token;
    }
    
    /*
    *
    */
    private function getRefreshToken($access_object){
        return json_decode($access_object)->refresh_token;
    }    
    
    /*
    *
    * @param string $access_token authentication token assigned to user, required for making all requests
    */    
    public function getUser($access_object){
        $url = API_URL.'/me?access_token=';
        
        $response = file_get_contents($url.getAccessToken($access_object));        
        return $response;
    }
    
    /*
    *
    */    
    public function sendPayment($access_object,$pay_to_user_id,$amount,$note){
        $url = API_URL.'/payments';

        $fields = array(
            'access_token' => getAccessToken($access_object),
            'user_id' => $pay_to_user_id,
            'amount' => $amount,
            'note' => $note.' via CabEasy'
        );
        
        $response = $this->curlMethod($url,$fields);
        
        return $response;
    }
    
    /*
    *
    */    
    private function curlMethod($url,$fields){
        // Open connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$fields);
        
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
        return $result;
    }
}
?>
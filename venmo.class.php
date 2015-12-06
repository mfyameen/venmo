<?php
/*
 * phpVenmo
 *
 * Server side integration for Venmo API
 *
 */

define('VENMO_ENVIRONMENT_SANDBOX_API','https://sandbox-api.venmo.com/v1/');
define('VENMO_ENVIRONMENT_PRODUCTION_API', 'https://api.venmo.com/v1/');
define('VENMO_ENVIRONMENT','PRODUCTION');

class Venmo{

    var $apiURL = '';
    var $timeout = 1;

    var $currentUserID = '';

    var $accessToken = '';

    var $debug = false;

    function __construct()
    {
        if(VENMO_ENVIRONMENT=='PRODUCTION')
        {
            $this->apiURL = VENMO_ENVIRONMENT_PRODUCTION_API;
        }
        else
        {
            $this->apiURL = VENMO_ENVIRONMENT_SANDBOX_API;
        }
    }    

    /* =============================================================================
    *
    *  Authentication
    *
    *  =============================================================================
    */

    /*
    * Get the authentication URL using a given CLIENT_ID and SCOPES
    * @param    string  client_id    The client_id to use
    * @param    array   scopes      The scopes to request
    */    
    public function getAuthURL($client_id, $scopes){
        $csrf_token = md5(uniqid(rand(), true)); //CSRF style token should be saved to implement CSRF check

        $fields = array(
            'client_id' => $client_id,
            'scope' => $scopes,
            'response_type' => 'code', //required for server side token exchange
            'state' => $csrf_token
        );

        return $this->apiURL . 'oauth/authorize?' . http_build_query($fields);
    }

    /*
    * Exchanges the authorization code for an access token
    * @param    string  client_id    The client_id to use
    * @param    array   scopes      The scopes to request
    * @param    string  authorization_code The authorization code provided once a user logs into Venmo and authorizes the application
    */
    public function exchangeToken($client_id, $scopes, $authorization_code){
        //make sure client send authorization code, something like
        //$authorization_code = $_GET["code"];
        
        $url = $this->apiURL . 'oauth/access_token';

        $fields = array(
            'client_id' => $client_id,
            'client_secret' => $scopes,
            'code' => $authorization_code
        ); 

        return $this->curlMethod($url,$fields);
    }
    
    /*
    * Access Tokens eventually expire, but can be renewed easily using this method
    * @param    string  client_id          The client_id to use
    * @param    array   client_secret      The client_secret to use
    * @param    string  refresh_token      The refresh token to use to grab a new access token
    */
    public function refreshAccessToken($client_id, $client_secret, $refresh_token){
        $url = $this->apiURL . 'oauth/access_token';

        $fields = array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token
        ); 

        return $this->curlMethod($url,$fields);
    }
    
    /* =============================================================================
    *
    *  Payments
    *
    *  =============================================================================
    */

    /*
    * Pay or a charge an email, phone number or user.
    * @param    array   to          Provide a valid US phone, email or Venmo User ID.
    * @param    string  amount      The amount you want to pay. To create a charge, use a negative amount.
    * @param    string  note        A message to accompany the payment.
    * @param    string  audient     The sharing setting for this payment. Possible values are 'public', 'friends' or 'private'.
    */    
    public function sendPaymentCharge($to,$amount,$note,$audience='private')
    {
        if(empty($this->accessToken))
        {
            throw new Exception("ERROR::sendPaymentCharge::Access token missing");
        }
        $fields = array_merge($to, array(
            'access_token' => $this->accessToken,
            'amount' => $amount,
            'note' => $note,
            'audience' => $audience
        ));
        
        return $this->curlMethod('payments',$fields);
    }

    /*
    * Get a list of the current user's most current payment and charges
    * @param array filters See https://developer.venmo.com/docs/payments
    */
    public function getRecentPayments($filters)
    {
        if(empty($this->accessToken))
        {
            throw new Exception("ERROR::getRecentPayments::Access token missing");
        }

        if($filters)
            $fields = array_merge($filters, array('access_token' => $this->accessToken));
        else
            $fields = array('access_token' => $this->accessToken);



        return $this->curlMethod('payments',$fields,"GET");
    }

    /*
    * Get payment information
    * @param string paymentID The payment ID of the desired payment
    */
    public function getPaymentInformation($paymentID)
    {
        if(empty($this->accessToken))
        {
            throw new Exception("ERROR::getPaymentInformation::Access token missing");
        }

        $fields = array('access_token' => $this->accessToken);

        return $this->curlMethod('payments/' . $paymentID,$fields,"GET");
    }

    /*
    * Approve, deny, or cancel a payment request.
    * @param string paymentID The payment ID of the desired payment to approve, deny, or cancel
    * @param string action Provide a value of 'approve', 'deny' if access token owner is the user who received the request, or 'cancel' if access token owner is the user who made the request.
    */
    public function updatePaymentRequest($paymentID, $action)
    {
        if(empty($this->accessToken))
        {
            throw new Exception("ERROR::updatePaymentRequest::Access token missing");
        }

        $fields = array('access_token' => $this->accessToken, 'action' => $action);

        return $this->curlMethod('payments/' . $paymentID,null,"GET");
    }

    /* 
    * Generate a payment link
    * @param string type The type of transaction (pay or charge)
    * @param array recipient(s) The person or people to send the transaction to
    * @param string amount The amount of money to send or charge
    * @param string note The note to add to the transaction
    * @param string sharing Make transaction private, public, or friends only
    * @param string external Share the transaction externally to facebook
    */
    public function generatePaymentLink($type, $recipients, $amount, $note, $audience, $sharing)
    {
        $fields = array();

        if(($type != "pay") && ($type != "charge"))
            throw new Exception("ERROR::generatePaymentLink::Bad transaction type '" . $type . "'");
        else
            $fields['txn'] = $type;

        if($amount)
            $fields['amount'] = $amount;

        if($note)
            $fields['note'] = $note;

        if($sharing)
            $fields['share'] = $sharing;

        if($audience)
            $fields['audience'] = $audience;

        if($recipients)
            $fields['recipients'] = join(',', $recipients);

        return "https://venmo.com/?" . http_build_query($fields);
    }



    /* =============================================================================
    *
    *  Users
    *
    *  =============================================================================
    */

    /*
    * Get current user information. Email, phone and balance will return non-null if the access token has proper permissions.
    */
    public function getCurrentUserInfo()
    {
        if(empty($this->accessToken))
        {
            throw new Exception("ERROR::getCurrentUserInfo::token missing");
        }

        $fields = array('access_token' => $this->accessToken);

        $response = $this->curlMethod('me',$fields,"GET");

        $responseJSON = json_decode($response);

        $this->currentUserID = $responseJSON->data->user->id;

        return $response;
    }

    /*
    * Get a single user's information
    * @param    string  user_id  The user ID of the desired user.
    */
    public function getUserInfo($user_id)
    {
        if(empty($this->accessToken))
        {
            throw new Exception("ERROR::getUserInfo::Access token missing");
        }

        $fields = array('access_token' => $this->accessToken);

        return $this->curlMethod('users/' . $user_id,$fields,"GET");
    }

    /*
    * Get a single user's friends
    * @param    string  user_id  The user ID of the desired user (can also use "me" to get the current user's friends)
    * @param    string  before  Returns user ids less than this id.
    * @param    string  limit   Limits the number of friends returned.

    */
    public function getUserFriends($user_id = "me", $before = null, $limit = null)
    {
        if(empty($this->accessToken))
        {
            throw new Exception("ERROR::getUserFriends::Access token missing");
        }

        if($user_id == "me")
        {
            if(empty($this->currentUserID))
            {
                throw new Exception("ERROR::getUserFriends::Current user_id not set. Call getCurrentUserInfo first");
            }
            else
            {
                $user_id = $this->currentUserID;
            }
        }

        $fields = array('access_token' => $this->accessToken);

        if($before)
            $fields['before'] = $before;

        if($limit)
            $fields['limit'] = $limit;

        return $this->curlMethod('users/' . $user_id . '/friends',$fields,"GET");
    }
    
    /* =============================================================================
    *
    *  Helper Methods
    *
    *  =============================================================================
    */

    /*
    * Set the object's access token. 
    * @param    string  accessToken     The access token to set
    */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /*
    * Set the object's environment 
    * @param    string  enviroment      The enviroment (PRODUCTION or SANDBOX)
    */
    public function setEnviroment($enviroment)
    {
        if($enviroment=='PRODUCTION')
        {
            $this->debug = false;
            $this->setAPIUrl(VENMO_ENVIRONMENT_PRODUCTION_API);
        }
        else
        {
            echo "INFO::setEnviroment::Sandbox environment set";
            $this->debug = true;
            $this->setAPIUrl(VENMO_ENVIRONMENT_SANDBOX_API);
        }
    }

    /*
    * Set the object's api URL 
    * @param    string  url      The URL to use
    */
    public function setAPIUrl($url)
    {
        $this->apiURL = $url;
    }

    /*
    * Execute a cURL request
    * @param    string  url     The url to request
    * @param    array   fields  The fields to add to the request
    * @param    string  method  The HTTP method type (GET, POST, PUT)
    */    
    private function curlMethod($url,$fields,$method="POST"){
        // Open connection
        $ch = curl_init();

        switch ($method) {
            case 'GET':
                // No additional actions needed for GET requests
                $url = $url . "?" . http_build_query($fields);
                break;
            case 'POST':
                // Enable POST method and set the post fields
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($fields));
                break;    
            case 'PUT':
                // Enable PUT method and set the fields to be sent
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($fields));  
            default:
                throw new Exception("Unsupported cURL Method: '" + $method + "'");
                break;
        }

        // Set the url
        curl_setopt($ch, CURLOPT_URL, $this->apiURL . $url);

        // Request for the server's response to be returned to this function
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Set the timeout
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        
        // Throw out some debug information
        if($this->debug)
        {
            echo "INFO::cURL URL::" . $this->apiURL . $url . "\r\n";
        }

        // Execute the cURL request and grab the response
        $result = curl_exec($ch);
        if ($result === FALSE) {
            throw new Exception("cURL Error: '" + curl_error($ch) + "'");
        }

        // Close connection
        curl_close($ch);
        return $result;
    }
}
?>
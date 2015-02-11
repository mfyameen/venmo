<?php
/*
 * Server side integration for Venmo API
 *
 * 1) Select the API environment you wish to talk to. Default is sandbox.
 * 2) Define client_id, client_secret and scopes before getting started
 *
 * For full list of scopes, please see Venmo API documentation. By default, this class implements the bare
 * minimum set of scopes to send money from one user to another.
 */

//Choose environment, stick with sandbox until ready to go
define('ENVIRONMENT','SANDBOX');
//define('ENVIRONMENT','PRODUCTION');

//Enter venmo developer app credentials below
define('CLIENT_ID','please replace me');
define('CLIENT_SECRET','please replace me');
define('SCOPES','access_email access_phone make_payments');

?>
# phpVenmo

PHP library for interacting with the Venmo API. Provide methods for the full OAuth authentication, payments, and user information.

## Authentication

### Requires

* client_id
* client_secret
* scopes. For full list of scopes, please see Venmo API documentation. By default, this class implements the bare minimum set of scopes to send money from one user to another.

### Usage

1. Call getAuthURL from app and direct user to that URL in app
1. Call exchangeToken and pass query param "code" returned from first request
1. On success, store the entire response so that you have access to the access and refresh tokens
1. Call getUser or Payment request as needed

### ToDo

1. Save access token on refresh of token
1. Save refresh token, client_id, & client_secret internally to make class more "friendly"

## Payments

### Requires

* access_token. Use setAccessToken function

### Supported functions

* sendPaymentCharge - Send a payment or a charge
* getRecentPayments - Grab recent payments
* getPaymentInformation - Grab a single payment's information
* updatePaymentRequest - Accept, Deny, or Cancel a payment request
* generatePaymentLink - Generate a payment link complaint with https://developer.venmo.com/paymentlinks

## Users 

### Requires

* access_token. Use setAccessToken function

### Supported functions

* getCurrentUserInfo - Get the current user's information
* getUserInfo - Get a specific user's information
* getUserFriends - Get a specific user's friends

## Helper Functions

Supported functions

* setAccessToken - Set a specific access token
* setEnvironment - Switch between PRODUCTION and SANDBOX environments (essentially a shortcut for setAPIUrl)
* setAPIUrl - Set a specific Venmo API URL
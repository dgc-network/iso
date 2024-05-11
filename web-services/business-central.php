<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// OAuth 2.0 parameters
$tenant_id = 'YOUR_TENANT_ID';
$client_id = 'YOUR_CLIENT_ID';
$client_secret = 'YOUR_CLIENT_SECRET';
$redirect_uri = 'YOUR_REDIRECT_URI';
$scope = 'https://YOUR_DYNAMICS365_URL/.default';

// Authorization endpoint
$authorize_url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/authorize";

// Redirect the user to the authorization endpoint
$authorization_params = array(
    'client_id' => $client_id,
    'response_type' => 'code',
    'redirect_uri' => $redirect_uri,
    'scope' => $scope,
);
$authorization_url = add_query_arg($authorization_params, $authorize_url);

// Redirect the user to the authorization URL
wp_redirect($authorization_url);
exit;

// After user authentication and authorization, handle the redirect URI to obtain the access token
// Exchange authorization code for access token
$token_endpoint = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";
$response = wp_remote_post($token_endpoint, array(
    'body' => array(
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => $redirect_uri,
        'scope' => $scope,
    ),
));

if (!is_wp_error($response)) {
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    // Access token
    $access_token = $data->access_token;

    // Use the access token to make requests to Dynamics 365 Online API endpoints
    // Example request to retrieve data from Dynamics 365 Online
    $endpoint_url = 'https://YOUR_DYNAMICS365_URL/api/data/v9.0/accounts';
    $response = wp_remote_get($endpoint_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
        ),
    ));

    if (!is_wp_error($response)) {
        // Handle successful response
        $body = wp_remote_retrieve_body($response);
        $accounts = json_decode($body);

        // Process retrieved accounts data
        // ...
    } else {
        // Handle error
        $error_message = $response->get_error_message();
        // ...
    }
} else {
    // Handle authentication error
    $error_message = $response->get_error_message();
    // ...
}

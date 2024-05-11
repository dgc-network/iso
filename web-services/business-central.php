<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


function retrieve_chart_of_account() {
    // OAuth 2.0 parameters
    $tenant_id = 'YOUR_TENANT_ID';
    $client_id = 'YOUR_CLIENT_ID';
    $client_secret = 'YOUR_CLIENT_SECRET';
    $redirect_uri = 'YOUR_REDIRECT_URI';
    $scope = 'https://YOUR_DYNAMICS365_URL/.default';

    $tenant_id = '8fd48cfd-1156-4b3a-bc21-32e0e891eda9';
    $client_id = '915093ab-a735-44e3-8d84-849d07590b0f';
    $client_secret = '';
    $redirect_uri = 'https://businesscentral.dynamics.com/OauthLanding.htm';
    //$redirect_uri = 'https://iso-helper.com';

    $company = 'CRONUS%20USA%2C%20Inc.';
    $service = 'Chart_of_Accounts';
    $dynamics_url = "api.businesscentral.dynamics.com/v2.0/$tenant_id/Production/ODataV4/Company('".$company."')/$service";
    $scope = 'https://YOUR_DYNAMICS365_URL/.default';
    $scope = "https://$dynamics_url/.default";
    $scope = 'https://api.businesscentral.dynamics.com/.default';
    //$scope = 'https://api.businesscentral.dynamics.com/Financials.ReadWrite.All';


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
    
        // Use the access token to make requests to Dynamics 365 Business Central API endpoints
        // Example request to retrieve Chart of Accounts
        $endpoint_url = 'https://api.businesscentral.dynamics.com/v2.0/8fd48cfd-1156-4b3a-bc21-32e0e891eda9/Production/ODataV4/Company(\'CRONUS%20USA%2C%20Inc.\')/Chart_of_Accounts';
        $response = wp_remote_get($endpoint_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        ));
/*        
        // Use the access token to make requests to Dynamics 365 Online API endpoints
        // Example request to retrieve Chart of Accounts
        $endpoint_url = 'https://YOUR_DYNAMICS365_URL/api/data/v9.0/chart_of_accounts';
        $response = wp_remote_get($endpoint_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        ));
*/    
        if (!is_wp_error($response)) {
            // Handle successful response
            $body = wp_remote_retrieve_body($response);
            $chart_of_accounts = json_decode($body);
    
            // Check if decoding was successful
            if ($chart_of_accounts !== null) {
                // Loop through each property of the object
                foreach ($chart_of_accounts as $property => $value) {
                    // Output the property name and its value
                    echo $property . ': ' . $value . '<br>';
                }
            } else {
                // Handle JSON decoding error
                echo 'Error decoding JSON';
            }
            
            // Process retrieved Chart of Accounts data
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
    
}

function first_example() {
    // OAuth 2.0 parameters
    $tenant_id = '8fd48cfd-1156-4b3a-bc21-32e0e891eda9';
    $client_id = '33611e2a-c08a-4849-b9a3-edf1dc4255e5';
    $client_secret = 'e7c9cbfd-4be0-4db4-a281-eebe5a79623d';
    $redirect_uri = 'YOUR_REDIRECT_URI';
    $redirect_uri = 'https://iso-helper.com';
    
    $company = 'CRONUS%20USA%2C%20Inc.';
    $service = 'Chart_of_Accounts';
    $dynamics_url = "api.businesscentral.dynamics.com/v2.0/$tenant_id/Production/ODataV4/Company('".$company."')/$service";
    $scope = 'https://YOUR_DYNAMICS365_URL/.default';
    $scope = "https://$dynamics_url/.default";
    
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
    
}


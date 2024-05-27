<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_bc_access_token() {
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $client_secret = get_option('client_secret');
    $scope = 'https://api.businesscentral.dynamics.com/.default';

    $token_endpoint = "https://login.microsoftonline.com/{$tenant_id}/oauth2/v2.0/token";
    $response = wp_remote_post($token_endpoint, array(
        'body' => array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => 'client_credentials',
            'scope' => $scope,
        ),
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    if (isset($data->access_token)) {
        return $data->access_token;
    }

    return false;
}

function get_bc_companies($access_token) {
    $tenant_id = get_option('tenant_id');
    $companies_endpoint = "https://api.businesscentral.dynamics.com/v2.0/{$tenant_id}/Production/ODataV4/companies";
    $response = wp_remote_get($companies_endpoint, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
        ),
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    return $data->value ?? false;
}

function display_bc_companies() {
    $access_token = get_bc_access_token();

    if (!$access_token) {
        return 'Failed to retrieve access token.';
    }

    $companies = get_bc_companies($access_token);

    if (!$companies) {
        return 'Failed to retrieve companies.';
    }

    $output = '<ul>';
    foreach ($companies as $company) {
        $output .= '<li>Company Name: ' . esc_html($company->displayName) . ', GUID: ' . esc_html($company->id) . '</li>';
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('bc_companies', 'display_bc_companies');


function register_oauth_callback_endpoint() {
    add_rewrite_rule('^oauth-callback/?', 'index.php?oauth_callback=1', 'top');
}
add_action('init', 'register_oauth_callback_endpoint');

function add_oauth_callback_query_var($vars) {
    $vars[] = 'oauth_callback';
    return $vars;
}
add_filter('query_vars', 'add_oauth_callback_query_var');

function handle_oauth_callback() {
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $client_secret = get_option('client_secret');
    $redirect_uri = get_option('redirect_uri');
    $scope = get_option('bc_scope');

    global $wp_query;
    if (isset($wp_query->query_vars['oauth_callback'])) {
        $code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : '';
        if ($code) {
            // Process the code as needed, e.g., exchange it for an access token
            //echo 'Authorization code: ' . esc_html($code);

            // After user authentication and authorization, handle the redirect URI to obtain the access token
            // Exchange authorization code for access token
            $token_endpoint = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";
            $response = wp_remote_post($token_endpoint, array(
                'body' => array(
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'grant_type' => 'authorization_code',
                    //'code' => $_GET['code'],
                    'code' => $code,
                    'redirect_uri' => $redirect_uri,
                    'scope' => $scope,
                ),
            ));
            
            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body);
            
                // Access token
                $access_token = $data->access_token;
                //echo '<br>access token: ' . esc_html($access_token);
            
                // Use the access token to make requests to Dynamics 365 Business Central API endpoints
                // Example request to retrieve Chart of Accounts
                $endpoint_url = 'https://api.businesscentral.dynamics.com/v2.0/8fd48cfd-1156-4b3a-bc21-32e0e891eda9/Production/ODataV4/Company(\'CRONUS%20USA%2C%20Inc.\')/Chart_of_Accounts';
                $api_endpoint = 'https://api.businesscentral.dynamics.com/v2.0/';
                $company = '';
                $service = 'Chart_of_Accounts'; 
                $endpoint_url = $api_endpoint . $tenant_id. '/Production/ODataV4/Company(\'CRONUS%20USA%2C%20Inc.\')/' . $service;

                $response = wp_remote_get($endpoint_url, array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $access_token,
                    ),
                ));

                if (!is_wp_error($response)) {
                    // Handle successful response
                    $body = wp_remote_retrieve_body($response);
                    $chart_of_accounts = json_decode($body);
            
                    // Check if decoding was successful
                    if ($chart_of_accounts !== null) {
                        foreach ($chart_of_accounts as $property => $value) {
                            // Check if the value is an array
                            if (is_array($value)) {
                                // Output the property name
                                echo $property . ': <br>';
                                // Loop through the array and output its contents
                                foreach ($value as $sub_property => $sub_value) {
                                    // Check if the sub-value is an object
                                    if (is_object($sub_value)) {
                                        echo '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . json_encode($sub_value) . '<br>';
                                    } else {
                                        echo '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . $sub_value . '<br>';
                                    }
                                }
                            } elseif (is_object($value)) {
                                // Output the property name and the JSON-encoded object
                                echo $property . ': ' . json_encode($value) . '<br>';
                            } else {
                                // Output the property name and its value
                                echo $property . ': ' . $value . '<br>';
                            }
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
            exit;
        } else {
            echo 'Authorization code not found.';
            exit;
        }
    }
}
add_action('template_redirect', 'handle_oauth_callback');

function flush_rewrite_rules_once() {
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'flush_rewrite_rules_once');

function retrieve_chart_of_account() {
    // OAuth 2.0 parameters
    $company = 'CRONUS%20USA%2C%20Inc.';
    $service = 'Chart_of_Accounts';
    $dynamics_url = "api.businesscentral.dynamics.com/v2.0/$tenant_id/Production/ODataV4/Company('".$company."')/$service";
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $client_secret = get_option('client_secret');
    $redirect_uri = get_option('redirect_uri');
    $scope = get_option('bc_scope');

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
}

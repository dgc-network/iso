<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// handle the array parameter
function redirect_to_authorization_url($params) {
    // Extract parameters from the array
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $redirect_uri = get_option('redirect_uri');
    $scope = array('https://api.businesscentral.dynamics.com/.default');

    $company = $params['company'];
    $service = $params['service'];
    $index_key = $params['index_key'];
    
    $authorize_url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/authorize";
    
    // Include the parameters in the state
    $state = array(
        'company' => $company,
        'service' => $service,
        'index_key' => $index_key,
    );
    
    $authorization_params = array(
        'client_id' => $client_id,
        'response_type' => 'code',
        'redirect_uri' => $redirect_uri,
        'scope' => implode(' ', $scope),
        //'state' => base64_encode(json_encode($state)),  // Encode state as base64 to pass it safely
        'state' => base64_encode(json_encode($params)),  // Encode state as base64 to pass it safely
    );
    
    $authorization_url = $authorize_url . '?' . http_build_query($authorization_params);
    
    wp_redirect($authorization_url);
    exit;
}

function handle_oauth_callback() {
    if (isset($_GET['code'])) {
        $code = sanitize_text_field($_GET['code']);
        $state = isset($_GET['state']) ? json_decode(base64_decode(sanitize_text_field($_GET['state'])), true) : array();

        // Retrieve OAuth 2.0 settings
        $tenant_id = get_option('tenant_id');
        $client_id = get_option('client_id');
        $client_secret = get_option('client_secret');
        $redirect_uri = get_option('redirect_uri');
        $scope = 'https://api.businesscentral.dynamics.com/.default';
        
        // Token endpoint
        $token_endpoint = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";
        $response = wp_remote_post($token_endpoint, array(
            'body' => array(
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirect_uri,
                'scope' => $scope,
            ),
        ));
        
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body);
            
            if (isset($data->access_token)) {
                $access_token = $data->access_token;
                
                // Extract parameters from state
                $company = isset($state['company']) ? $state['company'] : 'CRONUS USA, Inc.';
                $service = isset($state['service']) ? $state['service'] : 'dgCompanies';
                $index_key = isset($state['index_key']) ? $state['index_key'] : '';

                // Make the request to Business Central API
                $endpoint_url = 'https://api.businesscentral.dynamics.com/v2.0/' . $tenant_id . '/Production/ODataV4/Company(\'' . $company . '\')/' . $service;
                $response = wp_remote_get($endpoint_url, array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $access_token,
                    ),
                ));

                if (!is_wp_error($response)) {
                    $body = wp_remote_retrieve_body($response);
                    $propertys = json_decode($body);
            
                    if ($propertys !== null) {
                        foreach ($propertys as $property => $value) {
                            if (is_array($value)) {
                                return $value;

                                echo $property . ': <br>';
                                foreach ($value as $sub_property => $sub_value) {
                                    if (is_object($sub_value)) {
                                        echo '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . json_encode($sub_value) . '<br>';
                                    } else {
                                        echo '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . $sub_value . '<br>';
                                    }
                                }
                            } elseif (is_object($value)) {
                                return $property . ': ' . json_encode($value) . '<br>';
                                echo $property . ': ' . json_encode($value) . '<br>';
                            } else {
                                return $property . ': ' . $value . '<br>';
                                echo $property . ': ' . $value . '<br>';
                            }
                        }
                    } else {
                        return 'Error decoding JSON';
                        echo 'Error decoding JSON';
                    }
                } else {
                    $error_message = $response->get_error_message();
                    return 'Error: ' . $error_message;
                    echo 'Error: ' . $error_message;
                }
            } else {
                return 'Failed to get access token';
                echo 'Failed to get access token';
            }
        } else {
            $error_message = $response->get_error_message();
            return 'Error: ' . $error_message;
            echo 'Error: ' . $error_message;
        }
    } else {
        return 'Authorization code not found.';
        echo 'Authorization code not found.';
    }
}

function handle_oauth_callback_redirect() {
    global $wp_query;
    if (isset($wp_query->query_vars['oauth_callback'])) {
        return handle_oauth_callback();
        exit;
    }
}
add_action('template_redirect', 'handle_oauth_callback_redirect');

function flush_rewrite_rules_once() {
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'flush_rewrite_rules_once');

function register_oauth_callback_endpoint() {
    add_rewrite_rule('^oauth-callback/?', 'index.php?oauth_callback=1', 'top');
}
add_action('init', 'register_oauth_callback_endpoint');

function add_oauth_callback_query_var($vars) {
    $vars[] = 'oauth_callback';
    return $vars;
}
add_filter('query_vars', 'add_oauth_callback_query_var');

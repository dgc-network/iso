<?php
if ( ! defined( 'ABSPATH' ) ) {
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

                // Decode the original URL
                $original_url = isset($state['original_url']) ? urldecode($state['original_url']) : home_url();

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
                        ob_start();
                        foreach ($propertys as $property => $value) {
                            if (is_array($value)) {
                                echo $property . ': <br>';
                                foreach ($value as $sub_property => $sub_value) {
                                    if (is_object($sub_value)) {
                                        echo '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . json_encode($sub_value) . '<br>';
                                    } else {
                                        echo '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . $sub_value . '<br>';
                                    }
                                }
                            } elseif (is_object($value)) {
                                echo $property . ': ' . json_encode($value) . '<br>';
                            } else {
                                echo $property . ': ' . $value . '<br>';
                            }
                        }
                        $result = ob_get_clean();
                        set_transient('oauth_callback_result', $result, 60);

                        // Redirect back to the original URL with a query parameter
                        wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
                        exit;
                    } else {
                        set_transient('oauth_callback_result', 'Error decoding JSON', 60);
                        wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
                        exit;
                    }
                } else {
                    $error_message = $response->get_error_message();
                    set_transient('oauth_callback_result', 'Error: ' . $error_message, 60);
                    wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
                    exit;
                }
            } else {
                set_transient('oauth_callback_result', 'Failed to get access token', 60);
                wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
                exit;
            }
        } else {
            $error_message = $response->get_error_message();
            set_transient('oauth_callback_result', 'Error: ' . $error_message, 60);
            wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
            exit;
        }
    } else {
        set_transient('oauth_callback_result', 'Authorization code not found.', 60);
        wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
        exit;
    }
}

function redirect_to_authorization_url($params) {
    // Extract parameters from the array
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $redirect_uri = get_option('redirect_uri');
    $scope = array('https://api.businesscentral.dynamics.com/.default');

    // Get the current URL
    $original_url = (is_ssl() ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // Encode the original URL
    $encoded_original_url = urlencode($original_url);

    // Add the encoded original URL to the parameters
    $params['original_url'] = $encoded_original_url;

    // Add the original URL to the parameters
    //$params['original_url'] = $original_url;

    // Encode the parameters as a state parameter
    $state = base64_encode(json_encode($params));

    $authorization_params = array(
        'client_id' => $client_id,
        'response_type' => 'code',
        'redirect_uri' => $redirect_uri,
        'scope' => implode(' ', $scope),
        'state' => $state,
    );

    $authorize_url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/authorize";
    $authorization_url = $authorize_url . '?' . http_build_query($authorization_params);

    wp_redirect($authorization_url);
    exit;
}

function handle_oauth_callback_05() {
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
                //$original_url = isset($state['original_url']) ? $state['original_url'] : home_url();
                // Decode the original URL
                $original_url = isset($state['original_url']) ? urldecode($state['original_url']) : home_url();

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
                        ob_start();
                        foreach ($propertys as $property => $value) {
                            if (is_array($value)) {
                                echo $property . ': <br>';
                                foreach ($value as $sub_property => $sub_value) {
                                    if (is_object($sub_value)) {
                                        echo '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . json_encode($sub_value) . '<br>';
                                    } else {
                                        echo '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . $sub_value . '<br>';
                                    }
                                }
                            } elseif (is_object($value)) {
                                echo $property . ': ' . json_encode($value) . '<br>';
                            } else {
                                echo $property . ': ' . $value . '<br>';
                            }
                        }
                        $result = ob_get_clean();
                        set_transient('oauth_callback_result', $result, 60);

                        // Redirect back to the original URL with a query parameter
                        wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
                        exit;
                    } else {
                        set_transient('oauth_callback_result', 'Error decoding JSON', 60);
                        wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
                        exit;
                    }
                } else {
                    $error_message = $response->get_error_message();
                    set_transient('oauth_callback_result', 'Error: ' . $error_message, 60);
                    wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
                    exit;
                }
            } else {
                set_transient('oauth_callback_result', 'Failed to get access token', 60);
                wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
                exit;
            }
        } else {
            $error_message = $response->get_error_message();
            set_transient('oauth_callback_result', 'Error: ' . $error_message, 60);
            wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
            exit;
        }
    } else {
        set_transient('oauth_callback_result', 'Authorization code not found.', 60);
        wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
        exit;
    }
}


// Register the OAuth callback endpoint
function register_oauth_callback_endpoint() {
    add_rewrite_rule('^oauth-callback/?', 'index.php?oauth_callback=1', 'top');
}
add_action('init', 'register_oauth_callback_endpoint');

function add_oauth_callback_query_var($vars) {
    $vars[] = 'oauth_callback';
    return $vars;
}
add_filter('query_vars', 'add_oauth_callback_query_var');

// Flush rewrite rules once after switching themes
function flush_rewrite_rules_once() {
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'flush_rewrite_rules_once');

function handle_oauth_callback_redirect() {
    global $wp_query;
    if (isset($wp_query->query_vars['oauth_callback'])) {
        handle_oauth_callback();
        exit;
    }
}
add_action('template_redirect', 'handle_oauth_callback_redirect');

// Redirect function
function redirect_to_authorization_url_4($params) {
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $redirect_uri = get_option('redirect_uri');
    $scope = array('https://api.businesscentral.dynamics.com/.default');

    $authorize_url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/authorize";

    // Include the parameters in the state
    $state = base64_encode(json_encode($params));

    $authorization_params = array(
        'client_id' => $client_id,
        'response_type' => 'code',
        'redirect_uri' => $redirect_uri,
        'scope' => implode(' ', $scope),
        'state' => $state,
    );

    $authorization_url = $authorize_url . '?' . http_build_query($authorization_params);

    wp_redirect($authorization_url);
    exit;
}

function handle_oauth_callback_04() {
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
                        ob_start(); // Start output buffering
                        foreach ($propertys as $property => $value) {
                            if (is_array($value)) {
                                echo $property . ': <br>';
                                foreach ($value as $sub_property => $sub_value) {
                                    if (is_object($sub_value)) {
                                        echo '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . json_encode($sub_value) . '<br>';
                                    } else {
                                        echo '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . $sub_value . '<br>';
                                    }
                                }
                            } elseif (is_object($value)) {
                                echo $property . ': ' . json_encode($value) . '<br>';
                            } else {
                                echo $property . ': ' . $value . '<br>';
                            }
                        }
                        $result = ob_get_clean(); // Get the buffered content and clean the buffer
                        set_transient('oauth_callback_result', $result, 60); // Store the result in a transient
                        
                        // Redirect back to the original page with a query parameter
                        wp_redirect(add_query_arg('oauth_result_ready', '1', home_url()));
                        exit;
                    } else {
                        set_transient('oauth_callback_result', 'Error decoding JSON', 60);
                        wp_redirect(add_query_arg('oauth_result_ready', '1', home_url()));
                        exit;
                    }
                } else {
                    $error_message = $response->get_error_message();
                    set_transient('oauth_callback_result', 'Error: ' . $error_message, 60);
                    wp_redirect(add_query_arg('oauth_result_ready', '1', home_url()));
                    exit;
                }
            } else {
                set_transient('oauth_callback_result', 'Failed to get access token', 60);
                wp_redirect(add_query_arg('oauth_result_ready', '1', home_url()));
                exit;
            }
        } else {
            $error_message = $response->get_error_message();
            set_transient('oauth_callback_result', 'Error: ' . $error_message, 60);
            wp_redirect(add_query_arg('oauth_result_ready', '1', home_url()));
            exit;
        }
    } else {
        set_transient('oauth_callback_result', 'Authorization code not found.', 60);
        wp_redirect(add_query_arg('oauth_result_ready', '1', home_url()));
        exit;
    }
}






/*
// Global variable to store the callback result
global $oauth_callback_result;
$oauth_callback_result = '';

// Handle OAuth callback
function handle_oauth_callback_3() {
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
                        ob_start(); // Start output buffering
                        foreach ($propertys as $property => $value) {
                            if (is_array($value)) {
                                echo $property . ': <br>';
                                foreach ($value as $sub_property => $sub_value) {
                                    if (is_object($sub_value)) {
                                        echo '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . json_encode($sub_value) . '<br>';
                                    } else {
                                        echo '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . $sub_value . '<br>';
                                    }
                                }
                            } elseif (is_object($value)) {
                                echo $property . ': ' . json_encode($value) . '<br>';
                            } else {
                                echo $property . ': ' . $value . '<br>';
                            }
                        }
                        $result = ob_get_clean(); // Get the buffered content and clean the buffer
                        return $result;
                    } else {
                        return 'Error decoding JSON';
                    }
                } else {
                    $error_message = $response->get_error_message();
                    return 'Error: ' . $error_message;
                }
            } else {
                return 'Failed to get access token';
            }
        } else {
            $error_message = $response->get_error_message();
            return 'Error: ' . $error_message;
        }
    } else {
        return 'Authorization code not found.';
    }
}

// Handle the callback and store the result
function handle_oauth_callback_redirect_3() {
    global $wp_query, $oauth_callback_result;
    if (isset($wp_query->query_vars['oauth_callback'])) {
        $oauth_callback_result = handle_oauth_callback_3();
        echo $oauth_callback_result; // Display the result for demonstration purposes
        exit;
    }
}
add_action('template_redirect', 'handle_oauth_callback_redirect_3');


/*
function handle_oauth_callback_2() {
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
                        $output = '';
                        foreach ($propertys as $property => $value) {
                            if (is_array($value)) {
                                $output .= $property . ': <br>';
                                foreach ($value as $sub_property => $sub_value) {
                                    if (is_object($sub_value)) {
                                        $output .= '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . json_encode($sub_value) . '<br>';
                                    } else {
                                        $output .= '&nbsp;&nbsp;&nbsp;' . $sub_property . ': ' . $sub_value . '<br>';
                                    }
                                }
                            } elseif (is_object($value)) {
                                $output .= $property . ': ' . json_encode($value) . '<br>';
                            } else {
                                $output .= $property . ': ' . $value . '<br>';
                            }
                        }
                        return $output;
                    } else {
                        return 'Error decoding JSON';
                    }
                } else {
                    $error_message = $response->get_error_message();
                    return 'Error: ' . $error_message;
                }
            } else {
                return 'Failed to get access token';
            }
        } else {
            $error_message = $response->get_error_message();
            return 'Error: ' . $error_message;
        }
    } else {
        return 'Authorization code not found.';
    }
}

function handle_oauth_callback_redirect_2() {
    global $wp_query;
    if (isset($wp_query->query_vars['oauth_callback'])) {
        $result = handle_oauth_callback_2();
        echo $result; // For demonstration purposes, you can echo the result here, or handle it as needed.
        exit;
    }
}
add_action('template_redirect', 'handle_oauth_callback_redirect_2');



// handle the array parameter
function redirect_to_authorization_url_2($params) {
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

function handle_oauth_callback_1() {
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

function handle_oauth_callback_redirect_1() {
    global $wp_query;
    if (isset($wp_query->query_vars['oauth_callback'])) {
        return handle_oauth_callback_1();
        exit;
    }
}
add_action('template_redirect', 'handle_oauth_callback_redirect_1');

function flush_rewrite_rules_once_2() {
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'flush_rewrite_rules_once_2');

function register_oauth_callback_endpoint_2() {
    add_rewrite_rule('^oauth-callback/?', 'index.php?oauth_callback=1', 'top');
}
add_action('init', 'register_oauth_callback_endpoint_2');

function add_oauth_callback_query_var_2($vars) {
    $vars[] = 'oauth_callback';
    return $vars;
}
add_filter('query_vars', 'add_oauth_callback_query_var_2');
*/
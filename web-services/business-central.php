<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Redirect function
function redirect_to_authorization_url($params) {
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $redirect_uri = get_option('redirect_uri');
    $scope = array('https://api.businesscentral.dynamics.com/.default');

    // Get the current URL
    $original_url = (is_ssl() ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // Encode the original URL
    $encoded_original_url = urlencode($original_url);

    // Add the encoded original URL to the parameters
    $params['encoded_original_url'] = $encoded_original_url;

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

function handle_oauth_callback_redirect() {
    global $wp_query;
    if (isset($wp_query->query_vars['oauth_callback'])) {
        handle_oauth_callback();
        exit;
    }
}
add_action('template_redirect', 'handle_oauth_callback_redirect');

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
            $data = json_decode($body, true);

            if (isset($data['access_token'])) {
                $access_token = $data['access_token'];

                // Extract parameters from state
                $company = isset($state['company']) ? $state['company'] : 'CRONUS USA, Inc.';
                $service = isset($state['service']) ? $state['service'] : 'dgCompanies';
                $post_type = isset($state['post_type']) ? $state['post_type'] : 'GET';
                $body_data = isset($state['body_data']) ? $state['body_data'] : array();
                $original_url = isset($state['original_url']) ? urldecode($state['original_url']) : home_url() . '/display-profiles/';

                // Construct the endpoint URL
                $endpoint_url = "https://api.businesscentral.dynamics.com/v2.0/$tenant_id/Production/ODataV4/Company('$company')/$service";

                // Add body_data as $filter query parameters if not empty
                if (!empty($body_data) && $post_type == 'GET') {
                    $filters = [];
                    foreach ($body_data as $key => $value) {
                        if (is_string($value)) {
                            $filters[] = "$key eq '" . esc_attr($value) . "'";
                        } elseif (is_numeric($value)) {
                            $filters[] = "$key gt " . esc_attr($value);
                        }
                    }
                    if (!empty($filters)) {
                        $endpoint_url = add_query_arg('$filter', implode(' and ', $filters), $endpoint_url);
                    }
                }

                if ($post_type == 'GET') {
                    $response = wp_remote_get($endpoint_url, array(
                        'headers' => array(
                            'Authorization' => 'Bearer ' . $access_token,
                        ),
                    ));
                } else if ($post_type == 'POST') {
                    $response = wp_remote_post($endpoint_url, array(
                        'headers' => array(
                            'Authorization' => 'Bearer ' . $access_token,
                            'Content-Type' => 'application/json', // Set content type to JSON
                        ),
                        'body' => json_encode($body_data),
                    ));
                } else if ($post_type == 'PUT') {
                    $response = wp_remote_request($endpoint_url, array(
                        'method' => 'PUT',
                        'headers' => array(
                            'Authorization' => 'Bearer ' . $access_token,
                            'Content-Type' => 'application/json', // Set content type to JSON
                        ),
                        'body' => json_encode($body_data),
                    ));
                } else {
                    // Handle unsupported operations
                    set_transient('oauth_callback_result', 'Error: Operation not supported', 60);
                    wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
                    exit;
                }

                if (!is_wp_error($response)) {
                    $body = wp_remote_retrieve_body($response);
                    $properties = json_decode($body, true);

                    if ($properties !== null) {
                        set_transient('oauth_callback_result', $properties, 60); // Store the result in a transient
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


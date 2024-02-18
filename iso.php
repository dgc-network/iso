<?php
/**
 * Plugin Name: iso
 * Plugin URI: https://wordpress.org/plugins/iso/
 * Description: The leading documents management plugin for iso system by shortcode
 * Author: dgc.network
 * Author URI: https://dgc.network/
 * Version: 1.0.2
 * Requires at least: 6.0
 * Tested up to: 6.4.1
 *
 * Text Domain: iso
 * Domain Path: /languages/
 */

if (!defined('ABSPATH')) {
    exit;
}
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function custom_date_format() {
    $date_format = get_option('date_format');
    if (empty($date_format)) {
        $new_date_format = 'Y-m-d'; // Set your desired date format    
        update_option('date_format', $new_date_format);
    }
}
add_action( 'init', 'custom_date_format' );
*/
function register_session() {
    if ( ! session_id() ) {
        session_start();
    }
}
add_action( 'init', 'register_session' );

function remove_admin_bar() {
  if (!current_user_can('administrator') && !is_admin()) {
    show_admin_bar(false);
  }
}
add_action('after_setup_theme', 'remove_admin_bar');

function admin_enqueue_scripts_and_styles() {
    $version = '1.0.0.'.time(); // Update this version number when you make changes
    wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
    wp_enqueue_script('jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), '1.13.2', true);
    wp_enqueue_style('admin-enqueue-css', plugins_url('assets/css/admin-enqueue.css', __FILE__), '', $version);
    wp_enqueue_script('admin-enqueue-js', plugins_url('assets/js/admin-enqueue.js', __FILE__), array('jquery', 'jquery-ui-js'), $version, true);
    wp_localize_script('admin-enqueue-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('iso_documents_nonce'), // Generate nonce
    ));
}
add_action('admin_enqueue_scripts', 'admin_enqueue_scripts_and_styles');

function wp_enqueue_scripts_and_styles() {
    wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
    wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
    $version = '1.0.0.'.time(); // Update this version number when you make changes
    wp_enqueue_style('wp-enqueue-css', plugins_url('assets/css/wp-enqueue.css', __FILE__), '', $version);
    $version = '1.0.0.'.time(); // Update this version number when you make changes
    wp_enqueue_script('wp-enqueue-js', plugins_url('assets/js/wp-enqueue.js', __FILE__), array('jquery'), $version);
    wp_localize_script('wp-enqueue-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('iso_documents_nonce'), // Generate nonce
    ));
    $version = '1.0.1.'.time(); // Update this version number when you make changes
    wp_enqueue_script('my-jobs-js', plugins_url('assets/js/my-jobs.js', __FILE__), array('jquery'), $version);
    wp_localize_script('my-jobs-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('iso_documents_nonce'), // Generate nonce
    ));
    $version = '1.0.1.'.time(); // Update this version number when you make changes
    wp_enqueue_script('display-documents-js', plugins_url('assets/js/display-documents.js', __FILE__), array('jquery'), $version);
    wp_localize_script('display-documents-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('iso_documents_nonce'), // Generate nonce
    ));
    $version = '1.0.1.'.time(); // Update this version number when you make changes
    wp_enqueue_script('to-do-list-js', plugins_url('assets/js/to-do-list.js', __FILE__), array('jquery'), $version);
    wp_localize_script('to-do-list-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('iso_documents_nonce'), // Generate nonce
    ));
}
add_action('wp_enqueue_scripts', 'wp_enqueue_scripts_and_styles');

require_once plugin_dir_path( __FILE__ ) . 'web-services/line-bot-api.php';
require_once plugin_dir_path( __FILE__ ) . 'web-services/open-ai-api.php';
require_once plugin_dir_path( __FILE__ ) . 'web-services/options-setting.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/user-custom.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/edit-site.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/my-jobs.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/display-documents.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/to-do-list.php';

add_option('_line_account', 'https://line.me/ti/p/@804poufw');
add_option('_operation_fee_rate', 0.005);
add_option('_operation_wallet_address', 'DKVr5kVFcDDREPeLSDvUcNbXAffdYuPQCd');

function display_custom_admin_notice($message, $type = 'success') {
    ?>
    <div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <?php
}

// Hook the function to the 'admin_notices' action

function display_custom_error_notice($error_message) {
    ?>
    <div class="error notice">
        <p><?php echo esc_html__('Error: ', 'your-text-domain') . esc_html($error_message); ?></p>
    </div>
    <?php
}

// Example usage:
/*
add_action('admin_notices', 'display_admin_notice');
function display_admin_notice($message) {
    ?>
    <div class="notice notice-error">
        <p><?php echo esc_html__('This is an important message for administrators!', 'your-text-domain'); ?></p>
    </div>
    <?php
}
*/
//add_action('parse_request', 'handle_line_webhook');
//add_action('wp_loaded', 'handle_line_webhook');
//add_action('init', 'handle_line_webhook');
function handle_line_webhook() {
    $content_type = isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : $_SERVER['CONTENT_TYPE'];
    $request_method = $_SERVER['REQUEST_METHOD'];
    //if (($content_type === 'application/json') && ($request_method === 'POST')) {
    if ($request_method === 'POST') {
            add_action('admin_notices', function () {
            display_custom_admin_notice('This is a normal notice');
        });
        process_line_webhook();
    } else {
        add_action('admin_notices', function () {
            display_custom_admin_notice('This is an error notice but do not panic!', 'error');
        });        
    }
/*
    // Check if this is a Line webhook request
    //if (isset($_SERVER['HTTP_X_LINE_SIGNATURE'])) {
        

        // Retrieve the request method
        $request_method = $_SERVER['REQUEST_METHOD'];
        // Log details for debugging
        error_log('Received webhook request. Method: ' . $request_method);
        error_log('Headers: ' . json_encode(getallheaders()));
        error_log('Payload: ' . file_get_contents('php://input'));
        $error_message = 'HTTP_X_LINE_SIGNATURE';
        add_action('admin_notices', function () use ($error_message) {
            display_custom_error_notice($error_message);
            display_custom_error_notice('Headers: ' . json_encode(getallheaders()));
            display_custom_error_notice('Payload: ' . file_get_contents('php://input'));
        });

        // Check if the request method is POST
        if ($request_method === 'POST') {
            // Process Line webhook data
            process_line_webhook();
        } else {
            $error_message = 'REQUEST_METHOD is not the POST!';
            add_action('admin_notices', function () use ($error_message) {
                display_custom_error_notice($error_message);
                display_custom_error_notice('Received webhook request. Method: ' . $request_method);
            });
            
        }
/*        
    } else {
        $error_message = 'HTTP_X_LINE_SIGNATURE is not present';
        add_action('admin_notices', function () use ($error_message) {
            display_custom_error_notice($error_message);
        });
        
    }
*/    
}
/*
function handle_line_webhook() {
    // Retrieve the request method
    $request_method = $_SERVER['REQUEST_METHOD'];

    // Check if the request method is POST
    if ($request_method === 'POST' && isset($_SERVER['HTTP_X_LINE_SIGNATURE'])) {
        // Process Line webhook data
        process_line_webhook();
    } else {
        echo '$_SERVER error!';
    }
}
*/
//add_action('parse_request', 'process_line_webhook');
//add_action('wp_loaded', 'process_line_webhook');
//add_action('init', 'process_line_webhook');
function process_line_webhook() {
    // Retrieve the request body
    $entityBody = file_get_contents('php://input');

    // Verify that the request body is not empty
    if ($entityBody === false || strlen($entityBody) === 0) {
        http_response_code(400);
        error_log('Missing request body');
        exit;
    }

    // Decode the JSON payload
    $data = json_decode($entityBody, true);

    // Verify that the JSON payload can be decoded
    if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        error_log('Invalid JSON payload: ' . json_last_error_msg());
        exit;
    }

    // Implement your logic here based on the $data array
    foreach ((array)$data['events'] as $event) {

        // Start the User Login/Registration process if got the one time password
        if (esc_attr((int)$event['message']['text'])==esc_attr((int)get_option('_one_time_password'))) {
            $profile = $line_bot_api->getProfile($event['source']['userId']);
            $display_name = str_replace(' ', '', $profile['displayName']);
            // Encode the Chinese characters for inclusion in the URL
            $link_uri = home_url().'/my-jobs/?_id='.$event['source']['userId'].'&_name='.urlencode($display_name);
            // Flex Message JSON structure with a button
            $flexMessage = [
                'type' => 'flex',
                'altText' => 'This is a Flex Message with a Button',
                'contents' => [
                    'type' => 'bubble',
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'Hello, '.$display_name,
                                'size' => 'lg',
                                'weight' => 'bold',
                            ],
                            [
                                'type' => 'text',
                                'text' => 'You have not logged in yet. Please click the button below to go to the Login/Registration system.',
                                'wrap' => true,
                            ],
                        ],
                    ],
                    'footer' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'button',
                                'action' => [
                                    'type' => 'uri',
                                    'label' => 'Click me!',
                                    'uri' => $link_uri, // Replace with your desired URI
                                ],
                            ],
                        ],
                    ],
                ],
            ];
            
            $line_bot_api->replyMessage([
                'replyToken' => $event['replyToken'], // Make sure $event['replyToken'] is valid and present
                'messages' => [$flexMessage],
            ]);            
        }

        // Regular webhook response
        switch ($event['type']) {
            case 'message':
                $message = $event['message'];
                switch ($message['type']) {
                    case 'text':
                        // Open-AI auto reply
                        $response = $open_ai_api->createChatCompletion($message['text']);
                        $line_bot_api->replyMessage([
                            'replyToken' => $event['replyToken'],
                            'messages' => [
                                [
                                    'type' => 'text',
                                    'text' => $response
                                ]                                                                    
                            ]
                        ]);
                        break;
                    default:
                        error_log('Unsupported message type: ' . $message['type']);
                        break;
                }
                break;
            default:
                error_log('Unsupported event type: ' . $event['type']);
                break;
        }
    }

    // Send a response (optional)
    http_response_code(200);
    echo 'Webhook received successfully';
}

function listen_webhook() {
    // Retrieve the request body
    $entityBody = file_get_contents('php://input');
    
    // Verify that the request body is not empty
    if ($entityBody === false || strlen($entityBody) === 0) {
        http_response_code(400);
        error_log('Missing request body');
        exit;
    }
    
    // Decode the JSON payload
    $data = json_decode($entityBody, true);
    
    // Verify that the JSON payload can be decoded
    if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        error_log('Invalid JSON payload');
        exit;
    }
    
    // Process the Line webhook data
    // Implement your logic here based on the $data array
    
    // Send a response (optional)
    //http_response_code(200);
    //echo 'Webhook received successfully';
    return $data;
}


function init_webhook_events() {

    $line_bot_api = new line_bot_api();
    $open_ai_api = new open_ai_api();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        error_log('Method not allowed');
        exit;
    }

    $entityBody = file_get_contents('php://input');

    if ($entityBody === false || strlen($entityBody) === 0) {
        http_response_code(400);
        error_log('Missing request body');
        exit;
    }

    $data = json_decode($entityBody, true);
    
    // Verify that the JSON payload can be decoded
    if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        error_log('Invalid JSON payload');
        exit;
    }
    
    //$data = listen_webhook();
    foreach ((array)$data['events'] as $event) {

    //$events = $line_bot_api->parseEvents();
    //foreach ((array)$events as $event) {

        // Start the User Login/Registration process if got the one time password
        if (esc_attr((int)$event['message']['text'])==esc_attr((int)get_option('_one_time_password'))) {
            $profile = $line_bot_api->getProfile($event['source']['userId']);
            $display_name = str_replace(' ', '', $profile['displayName']);
            // Encode the Chinese characters for inclusion in the URL
            $link_uri = home_url().'/my-jobs/?_id='.$event['source']['userId'].'&_name='.urlencode($display_name);
            // Flex Message JSON structure with a button
            $flexMessage = [
                'type' => 'flex',
                'altText' => 'This is a Flex Message with a Button',
                'contents' => [
                    'type' => 'bubble',
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'Hello, '.$display_name,
                                'size' => 'lg',
                                'weight' => 'bold',
                            ],
                            [
                                'type' => 'text',
                                'text' => 'You have not logged in yet. Please click the button below to go to the Login/Registration system.',
                                'wrap' => true,
                            ],
                        ],
                    ],
                    'footer' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'button',
                                'action' => [
                                    'type' => 'uri',
                                    'label' => 'Click me!',
                                    'uri' => $link_uri, // Replace with your desired URI
                                ],
                            ],
                        ],
                    ],
                ],
            ];
            
            $line_bot_api->replyMessage([
                'replyToken' => $event['replyToken'], // Make sure $event['replyToken'] is valid and present
                'messages' => [$flexMessage],
            ]);            
        }

        // Regular webhook response
        switch ($event['type']) {
            case 'message':
                $message = $event['message'];
                switch ($message['type']) {
                    case 'text':
                        // Open-AI auto reply
                        $response = $open_ai_api->createChatCompletion($message['text']);
                        $line_bot_api->replyMessage([
                            'replyToken' => $event['replyToken'],
                            'messages' => [
                                [
                                    'type' => 'text',
                                    'text' => $response
                                ]                                                                    
                            ]
                        ]);
                        break;
                    default:
                        error_log('Unsupported message type: ' . $message['type']);
                        break;
                }
                break;
            default:
                error_log('Unsupported event type: ' . $event['type']);
                break;
        }
    }

}
add_action( 'parse_request', 'init_webhook_events' );
//add_action( 'init', 'init_webhook_events' );

// User did not login system yet
function user_did_not_login_yet() {
    if( isset($_GET['_id']) && isset($_GET['_name']) ) {
        // Using Line User ID to register and login into the system
        $array = get_users( array( 'meta_value' => $_GET['_id'] ));
        if (empty($array)) {
            $user_id = wp_insert_user( array(
                'user_login' => $_GET['_id'],
                'user_pass' => $_GET['_id'],
            ));
            $user = get_user_by( 'ID', $user_id );
            add_user_meta( $user_id, 'line_user_id', $_GET['_id']);
            // To-Do: add_user_meta( $user_id, 'wallet_address', $_GET['_wallet_address']);
        }
        ?>
        <div class="ui-widget">
            <h2>User profile</h2>
            <form method="post" action="<?php echo esc_url(site_url('wp-login.php', 'login_post'));?>">
            <fieldset>
                <label for="display-name">Name:</label>
                <input type="text" id="display-name" name="_display_name" value="<?php echo esc_attr($_GET['_name']); ?>" class="text ui-widget-content ui-corner-all" />
                <label for="site-id">Site:</label>
                <input type="text" id="site-title" class="text ui-widget-content ui-corner-all" />
                <div id="site-hint" style="display:none; color:#999;"></div>
                <input type="hidden" id="site-id" name="_site_id" />
                <input type="hidden" name="log" value="<?php echo esc_attr($_GET['_id']);?>" />
                <input type="hidden" name="pwd" value="<?php echo esc_attr($_GET['_id']);?>" />
                <input type="hidden" name="rememberme" value="foreverchecked" />
                <input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url());?>" />
                <hr>
                <input type="submit" name="wp-submit" class="button button-primary" value="Submit" />
            </fieldset>
            </form>
        </div>
        <?php        

    } else {
        // Display a message or redirect to the login/registration page
        $one_time_password = random_int(100000, 999999);
        update_option('_one_time_password', $one_time_password);
        ?>
        <div class="ui-widget" style="text-align:center;">
            <p>感謝您使用我們的系統</p>
            <p>請加入我們的Line官方帳號,</p>
            <p>利用手機按或掃描下方QR code</p>
            <a href="<?php echo get_option('line_official_account');?>">
            <img src="<?php echo get_option('line_official_qr_code');?>">
            </a>
            <p>並請在聊天室中, 輸入六位數字:</p>
            <h3><?php echo get_option('_one_time_password');?></h3>
            <p>完成註冊/登入作業</p>
        </div>
        <?php
    }
}

function custom_login_process($user, $password) {
    // Check if the login was successful
    if (is_a($user, 'WP_User')) {
        $user_data = wp_update_user( array( 
            'ID' => $user->ID, 
            'display_name' => $_POST['_display_name'], 
        ) );
        // Add/update user metadata
        update_post_meta( $user->ID, 'site_id', sanitize_text_field($_POST['_site_id']));
    }
    return $user;
}
add_filter('wp_authenticate_user', 'custom_login_process', 10, 2);

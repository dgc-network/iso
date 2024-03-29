<?php
/**
 * Plugin Name: iso
 * Plugin URI: https://wordpress.org/plugins/iso/
 * Description: The leading documents management plugin for iso system by shortcode
 * Author: dgc.network
 * Author URI: https://dgc.network/
 * Version: 1.0.3
 * Requires at least: 6.0
 * Tested up to: 6.4.3
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
    wp_enqueue_script('display-profiles-js', plugins_url('assets/js/display-profiles.js', __FILE__), array('jquery'), $version);
    wp_localize_script('display-profiles-js', 'ajax_object', array(
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
//require_once plugin_dir_path( __FILE__ ) . 'includes/user-custom.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/edit-site.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/display-profiles.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/display-documents.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/to-do-list.php';

function set_flex_message($display_name, $link_uri, $text_message) {
    // Flex Message JSON structure with a button
    return $flexMessage = [
        'type' => 'flex',
        'altText' => $text_message,
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
                        'text' => $text_message,
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

}

function init_webhook_events() {

    $line_bot_api = new line_bot_api();
    $open_ai_api = new open_ai_api();

    $entityBody = file_get_contents('php://input');
    $data = json_decode($entityBody, true);
    $events = $data['events'] ?? [];

    foreach ((array)$events as $event) {

        $line_user_id = $event['source']['userId'];
        $profile = $line_bot_api->getProfile($line_user_id);
        $display_name = str_replace(' ', '', $profile['displayName']);
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            $text_message = 'You have not logged in yet. Please click the button below to go to the Login/Registration system.';
            $text_message = '您尚未登入系統！請點擊下方按鍵登入或註冊本系統。';
            // Encode the Chinese characters for inclusion in the URL
            $link_uri = home_url().'/display-profiles/?_id='.$line_user_id.'&_name='.urlencode($display_name);
            $flexMessage = set_flex_message($display_name, $link_uri, $text_message);
            $line_bot_api->replyMessage([
                'replyToken' => $event['replyToken'],
                'messages' => [$flexMessage],
            ]);
            exit;
            //continue; // Skip further processing for this event
        }

        // Regular expression to detect URLs
        $urlRegex = '/\bhttps?:\/\/\S+\b/';

        // Match URLs in the text
        if (preg_match_all($urlRegex, $event['message']['text'], $matches)) {
            // Extract the matched URLs
            $urls = $matches[0];
            
            // Output the detected URLs
            foreach ($urls as $url) {
                // Parse the URL
                $parsed_url = parse_url($url);
                
                // Check if the URL contains a query string
                if (isset($parsed_url['query'])) {
                    // Parse the query string
                    parse_str($parsed_url['query'], $query_params);
                    
                    // Check if the 'doc_id' parameter exists in the query parameters
                    if (isset($query_params['_get_shared_doc_id'])) {
                        // Retrieve the value of the 'doc_id' parameter
                        $doc_id = $query_params['_get_shared_doc_id'];
                        $doc_title = get_post_meta($doc_id, 'doc_title', true);
                        $text_message = '您可以點擊下方按鍵將文件「'.$doc_title.'」加入您的文件匣中。';
                    }
                }
                
                $flexMessage = set_flex_message($display_name, $url, $text_message);
                $line_bot_api->replyMessage([
                    'replyToken' => $event['replyToken'],
                    'messages' => [$flexMessage],
                ]);            
            }
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
/*
function init_webhook_events() {

    $line_bot_api = new line_bot_api();
    $open_ai_api = new open_ai_api();

    $entityBody = file_get_contents('php://input');
    $data = json_decode($entityBody, true);
    $events = $data['events'] ?? [];

    foreach ((array)$events as $event) {

        $line_user_id = $event['source']['userId'];
        $profile = $line_bot_api->getProfile($line_user_id);
        $display_name = str_replace(' ', '', $profile['displayName']);
/*        
        // Start the session to access stored OTP and expiration
        session_start();
        // Get stored OTP and expiration timestamp from session
        $one_time_password = isset($_SESSION['one_time_password']) ? intval($_SESSION['one_time_password']) : 0;
    
        // Start the User Login/Registration process if got the one time password
        if ((int)$event['message']['text']===$one_time_password) {
        //}
        //if ((int)$event['message']['text']==(int)get_option('_one_time_password')) {
            $text_message = 'You have not logged in yet. Please click the button below to go to the Login/Registration system.';
            $text_message = '您尚未登入系統！請點擊下方按鍵登入或註冊本系統。';
            // Encode the Chinese characters for inclusion in the URL
            $link_uri = home_url().'/display-profiles/?_id='.$event['source']['userId'].'&_name='.urlencode($display_name);
            $flexMessage = set_flex_message($display_name, $link_uri, $text_message);
            $line_bot_api->replyMessage([
                'replyToken' => $event['replyToken'],
                'messages' => [$flexMessage],
            ]);            
        }

        // Regular expression to detect URLs
        $urlRegex = '/\bhttps?:\/\/\S+\b/';

        // Match URLs in the text
        if (preg_match_all($urlRegex, $event['message']['text'], $matches)) {
            // Extract the matched URLs
            $urls = $matches[0];
            
            // Output the detected URLs
            foreach ($urls as $url) {
                // Parse the URL
                $parsed_url = parse_url($url);
                
                // Check if the URL contains a query string
                if (isset($parsed_url['query'])) {
                    // Parse the query string
                    parse_str($parsed_url['query'], $query_params);
                    
                    // Check if the 'doc_id' parameter exists in the query parameters
                    if (isset($query_params['_get_shared_doc_id'])) {
                        // Retrieve the value of the 'doc_id' parameter
                        $doc_id = $query_params['_get_shared_doc_id'];
                        $doc_title = get_post_meta($doc_id, 'doc_title', true);
                        $text_message = '您可以點擊下方按鍵將文件「'.$doc_title.'」加入您的文件匣中。';
                    }
                }
                
                $flexMessage = set_flex_message($display_name, $url, $text_message);
                $line_bot_api->replyMessage([
                    'replyToken' => $event['replyToken'],
                    'messages' => [$flexMessage],
                ]);            
            }
        }
        
        // Regular webhook response
        switch ($event['type']) {
            case 'message':
                if (!is_user_logged_in()) {
                    $text_message = 'You have not logged in yet. Please click the button below to go to the Login/Registration system.';
                    $text_message = '您尚未登入系統！請點擊下方按鍵登入或註冊本系統。';
                    // Encode the Chinese characters for inclusion in the URL
                    $link_uri = home_url().'/display-profiles/?_id='.$event['source']['userId'].'&_name='.urlencode($display_name);
                    $flexMessage = set_flex_message($display_name, $link_uri, $text_message);
                    $line_bot_api->replyMessage([
                        'replyToken' => $event['replyToken'],
                        'messages' => [$flexMessage],
                    ]);            
                }
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
*/
function proceed_to_registration_login($line_user_id, $display_name) {
    // Using Line User ID to register and login into the system
    $users = get_users( array( 'meta_value' => $line_user_id ));
    if (empty($users)) {
        $user_id = wp_insert_user( array(
            'user_login' => $line_user_id,
            'user_pass' => $line_user_id,
        ));
        add_user_meta( $user_id, 'line_user_id', $line_user_id);
    } else {
        // Get user by 'line_user_id' meta
        global $wpdb;
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'line_user_id' AND meta_value = %s",
            $line_user_id
        ));
        $site_id = get_user_meta( $user_id, 'site_id', true);
        $site_title = get_the_title($site_id);
    }
    $user_data = get_userdata( $user_id );
    ob_start();
    ?>
    <div class="ui-widget">
        <h2>User registration/login</h2>
        <fieldset>
            <label for="display-name">Name:</label>
            <input type="text" id="display-name" value="<?php echo esc_attr($display_name);?>" class="text ui-widget-content ui-corner-all" />
            <label for="user-email">Email:</label>
            <input type="text" id="user-email" value="<?php echo esc_attr($user_data->user_email);?>" class="text ui-widget-content ui-corner-all" />
            <label for="site-id">Site:</label>
            <input type="text" id="site-title" value="<?php echo esc_attr($site_title);?>" class="text ui-widget-content ui-corner-all" />
            <div id="site-hint" style="display:none; color:#999;"></div>
            <input type="hidden" id="site-id" value="<?php echo esc_attr($site_id);?>" />
            <input type="hidden" id="log" value="<?php echo esc_attr($line_user_id);?>" />
            <input type="hidden" id="pwd" value="<?php echo esc_attr($line_user_id);?>" />
            <hr>
            <input type="submit" id="wp-login-submit" class="button button-primary" value="Submit" />
        </fieldset>
    </div>
    <?php        
    $html = ob_get_clean();
    return $html;

}

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
            add_user_meta( $user_id, 'line_user_id', $_GET['_id']);
        } else {
            // Get user by 'line_user_id' meta
            global $wpdb;
            $user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'line_user_id' AND meta_value = %s",
                $_GET['_id']
            ));
            $site_id = get_user_meta( $user_id, 'site_id', true);
            $site_title = get_the_title($site_id);
        }
        //$user = get_user_by( 'ID', $user_id );
        $user_data = get_userdata( $user_id );
        ?>
        <div class="ui-widget">
            <h2>User registration/login</h2>
            <fieldset>
                <label for="display-name">Name:</label>
                <input type="text" id="display-name" value="<?php echo esc_attr($_GET['_name']);?>" class="text ui-widget-content ui-corner-all" />
                <label for="user-email">Email:</label>
                <input type="text" id="user-email" value="<?php echo esc_attr($user_data->user_email);?>" class="text ui-widget-content ui-corner-all" />
                <label for="site-id">Site:</label>
                <input type="text" id="site-title" value="<?php echo esc_attr($site_title);?>" class="text ui-widget-content ui-corner-all" />
                <div id="site-hint" style="display:none; color:#999;"></div>
                <input type="hidden" id="site-id" value="<?php echo esc_attr($site_id);?>" />
                <input type="hidden" id="log" value="<?php echo esc_attr($_GET['_id']);?>" />
                <input type="hidden" id="pwd" value="<?php echo esc_attr($_GET['_id']);?>" />
                <hr>
                <input type="submit" id="wp-login-submit" class="button button-primary" value="Submit" />
            </fieldset>
        </div>
        <?php        
    } else {
        // Display a message or redirect to the login/registration page
        $one_time_password = random_int(100000, 999999);
        update_option('_one_time_password', $one_time_password);
        ?>
        <div class="desktop-content ui-widget" style="text-align:center; display:none;">
            <!-- Content for desktop users -->
            <p>感謝您使用我們的系統</p>
            <p>請輸入您的 Email 帳號</p>
            <input type="text" id="user-email-input" />
            <div id="otp-input-div" style="display:none;">
            <p>請輸入傳送到您 Line 上的六位數字密碼</p>
            <input type="text" id="one-time-password-desktop-input" />
            <input type="hidden" id="line-user-id-input" />
            </div>
        </div>

        <div class="mobile-content ui-widget" style="text-align:center; display:none;">
            <!-- Content for mobile users -->
            <p>感謝您使用我們的系統</p>
            <p>請加入我們的Line官方帳號,</p>
            <p>利用手機按或掃描下方QR code</p>
            <a href="<?php echo get_option('line_official_account');?>">
            <img src="<?php echo get_option('line_official_qr_code');?>">
            </a>
            <p>並請在聊天室中, 輸入六位數字:</p>
            <h3><?php echo get_option('_one_time_password');?></h3>
            <h3><?php echo $otp;?></h3>
            <p>完成註冊/登入作業</p>
        </div>
        <?php
    }
}

function send_one_time_password() {
    $response = array('success' => false, 'error' => 'Invalid data format', 'line_user_id' => false);
    
    if (isset($_POST['_user_email'])) {
        $user_email = sanitize_text_field($_POST['_user_email']);
        // Get user by email
        $user = get_user_by('email', $user_email);

        if ($user) {
            // Get user meta "line_user_id"
            $line_user_id = get_user_meta($user->ID, 'line_user_id', true);
        
            if ($line_user_id) {
                // Generate a one-time password
                $one_time_password = random_int(100000, 999999);
                update_option('_one_time_password', $one_time_password);
                
                // Send the one-time password to Line user
                $access_token = get_option('line_bot_token_option');

                $message = [
                    'to' => $line_user_id,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => 'Your one-time password is: ' . $one_time_password,
                        ],
                    ],
                ];

                $ch = curl_init('https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $response_line = curl_exec($ch);
                curl_close($ch);

                // Handle the Line response as needed
                if ($response_line === false) {
                    $response = array('error' => 'Error sending Line message: ' . curl_error($ch));
                } else {
                    $response = array('success' => true, 'line_user_id' => $line_user_id);
                }
            } else {
                $response = array('error' => "User meta 'line_user_id' not found for the user with email: " . $user_email);
            }
        } else {
            $response = array('error' => "User not found with email: " . $user_email);
        }        
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_send_one_time_password', 'send_one_time_password' );
add_action( 'wp_ajax_nopriv_send_one_time_password', 'send_one_time_password' );

function one_time_password_desktop_submit() {
    $response = array('success' => false, 'error' => 'Invalid data format');

    if (isset($_POST['_one_time_password'])) {
        $one_time_password = sanitize_text_field($_POST['_one_time_password']);
        $line_user_id = sanitize_text_field($_POST['_line_user_id']);

        if ((int)$one_time_password == (int)get_option('_one_time_password')) {
            // Get user by 'line_user_id' meta
            global $wpdb;
            $user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'line_user_id' AND meta_value = %s",
                $line_user_id
            ));

            if ($user_id) {
                // Do something with $user_id
                //$user = get_user_by('ID', $user_id);
                $user_data = get_userdata( $user_id );

                $credentials = array(
                    'user_login'    => $user_data->user_login,
                    'user_password' => $line_user_id,
                    'remember'      => true,
                );

                $user_signon = wp_signon($credentials, false);

                if (!is_wp_error($user_signon)) {
                    // Login successful
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                    do_action('wp_login', $user_data->user_login);

                    $response = array('success' => true);
                } else {
                    // Login failed
                    $response = array('error' => $user_signon->get_error_message());
                }
            } else {
                $response = array('error' => $line_user_id . "Wrong line_user_id meta key");
            }
        } else {
            $response = array('error' => "Wrong one-time password");
        }
    }
    wp_send_json($response);
}
add_action('wp_ajax_one_time_password_desktop_submit', 'one_time_password_desktop_submit');
add_action('wp_ajax_nopriv_one_time_password_desktop_submit', 'one_time_password_desktop_submit');

function wp_login_submit() {
    $response = array('success' => false, 'error' => 'Invalid data format');

    if (isset($_POST['_site_id'])) {
        $user_login = sanitize_text_field($_POST['_log']);
        $user_password = sanitize_text_field($_POST['_pwd']);

        $credentials = array(
            'user_login'    => $user_login,
            'user_password' => $user_password,
            'remember'      => true,
        );

        $user = wp_signon($credentials, false);

        if (!is_wp_error($user)) {
            // Login successful
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            do_action('wp_login', $user->user_login);

            $user_data = wp_update_user( array( 
                'ID' => $user->ID, 
                'display_name' => sanitize_text_field($_POST['_display_name']),
                'user_email' => sanitize_text_field($_POST['_user_email']),
            ) );
            // Add/update user metadata
            update_user_meta( $user->ID, 'site_id', sanitize_text_field($_POST['_site_id']));

            $response = array('success' => true);
        } else {
            // Login failed
            $response = array('error' => $user->get_error_message());
        }
    }
    wp_send_json($response);
}
add_action('wp_ajax_wp_login_submit', 'wp_login_submit');
add_action('wp_ajax_nopriv_wp_login_submit', 'wp_login_submit');
/*
// Function to send Line user ID to WordPress
function send_line_user_id_to_wordpress($line_user_id, $line_display_name) {
    if (!is_user_logged_in()) {

        // Retrieve user by meta field and meta value
        $users = get_users(array(
            'meta_key'    => 'line_user_id',
            'meta_value'  => $user_identifier,
            'number'      => 1, // Limit to 1 user
            'count_total' => false // Improve performance
        ));
        
        // Check if user is found
        if (!empty($users)) {
            $user = reset($users); // Get the first user
            $user_id = $user->ID;
            $site_id = get_user_meta($user_id, 'site_id', true);
            $site_title = get_the_title($site_id);
            $user_email = $user->user_email; // Retrieve user email
        } else {
            // User not found by Line user ID, initialize variables
            $user_email = ''; 
            $site_title = '';
        }
        
        ?>
        <div class="ui-widget">
            <h2>User registration/login</h2>
            <fieldset>
                <label for="display-name">Name:</label>
                <input type="text" id="display-name" value="<?php echo esc_attr($line_display_name);?>" class="text ui-widget-content ui-corner-all" />
                <label for="user-email">Email:</label>
                <input type="text" id="user-email" value="<?php echo esc_attr($user_email);?>" class="text ui-widget-content ui-corner-all" />
                <label for="site-id">Site:</label>
                <input type="text" id="site-title" value="<?php echo esc_attr($site_title);?>" class="text ui-widget-content ui-corner-all" />
                <div id="site-hint" style="display:none; color:#999;"></div>
                <input type="hidden" id="site-id" value="<?php echo esc_attr($site_id);?>" />
                <input type="hidden" id="log" value="<?php echo esc_attr($user->user_login);?>" />
                <input type="hidden" id="pwd" value="<?php echo esc_attr($user->user_pass);?>" />
                <hr>
                <input type="submit" id="wp-login-submit" class="button button-primary" value="Submit" />
            </fieldset>
        </div>
        <?php
    }
}

// Function to generate a random OTP
function generate_otp() {
    // Generate a random 6-digit OTP
    return rand(100000, 999999);
}

// Function to verify OTP during login
function verify_otp_login($user_login, $user) {
    // Start the session to access stored OTP and expiration
    session_start();

    // Get submitted OTP
    $submitted_otp = isset($_POST['otp']) ? intval($_POST['otp']) : 0;

    // Get stored OTP and expiration timestamp from session
    $stored_otp = isset($_SESSION['otp']) ? intval($_SESSION['otp']) : 0;
    $expiration = isset($_SESSION['otp_expiration']) ? intval($_SESSION['otp_expiration']) : 0;

    // Check if submitted OTP matches stored OTP and is within expiration time
    if ($submitted_otp === $stored_otp && $expiration > time()) {
        // OTP is valid, log the user in
        wp_set_auth_cookie($user->ID, true);
        // Clear OTP and expiration from session
        unset($_SESSION['otp']);
        unset($_SESSION['otp_expiration']);
        // Redirect to home page or dashboard
        wp_redirect(home_url());
        exit;
    } else {
        // Invalid OTP, display error message
        $error = new WP_Error('invalid_otp', 'Invalid one-time password.');
        return $error;
    }
}
*/


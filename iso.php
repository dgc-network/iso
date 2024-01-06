<?php
/**
 * Plugin Name: iso
 * Plugin URI: https://wordpress.org/plugins/iso/
 * Description: The leading documents management plugin for iso system by shortcode
 * Author: dgc.network
 * Author URI: https://dgc.network/
 * Version: 1.0.0
 * Requires at least: 6.0
 * Tested up to: 6.4.1
 * 
 * Text Domain: iso
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
// Function to enqueue scripts for the custom chat_gpt admin
function admin_enqueue() {
    wp_enqueue_style( 'jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css' );
    wp_enqueue_script( 'jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
    //wp_enqueue_script( 'jquery-ui-datepicker' );
    //wp_enqueue_script( 'jquery-ui-dialog' );
    wp_enqueue_style( 'admin-enqueue-css', plugins_url( 'assets/css/admin-enqueue.css' , __FILE__ ), '', time() );
    wp_enqueue_script( 'admin-enqueue-js', plugins_url( 'assets/js/admin-enqueue.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_localize_script( 'admin-enqueue-js', 'ajax_object', array( 
        'ajax_url' => admin_url( 'admin-ajax.php' ), 
        'nonce' => wp_create_nonce('iso_documents_nonce'), // Generate nonce
    ) );
}
add_action('admin_enqueue_scripts', 'admin_enqueue');

function wp_enqueue() {
    wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css' );
    wp_enqueue_script( 'jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);

    //wp_enqueue_script( 'jquery-ui-sortable' );
    //wp_enqueue_script( 'jquery-ui-datepicker' );
    //wp_enqueue_script( 'jquery-ui-dialog' );
    
    wp_enqueue_style( 'wp-enqueue-css', plugins_url( 'assets/css/wp-enqueue.css' , __FILE__ ), '', time() );
    wp_enqueue_script( 'wp-enqueue-js', plugins_url( 'assets/js/wp-enqueue.js' , __FILE__ ), array( 'jquery' ), time() );
    wp_localize_script( 'wp-enqueue-js', 'ajax_object', array( 
        'ajax_url' => admin_url( 'admin-ajax.php' ), 
        'nonce' => wp_create_nonce('iso_documents_nonce'), // Generate nonce
    ) );
}
add_action( 'wp_enqueue_scripts', 'wp_enqueue' );

/**
 * 1. 時間到了會透過Line通知使用者填寫資料
 * 2. 有些客戶需要協助填寫資料, 完成驗證
 */
require_once plugin_dir_path( __FILE__ ) . 'web-services/line-bot-api.php';
require_once plugin_dir_path( __FILE__ ) . 'web-services/open-ai-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/user-profile.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-edit-site.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-edit-document.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/display-documents.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/to-do-list.php';
//require_once plugin_dir_path( __FILE__ ) . 'redirect-line-login.php';

add_option('_line_account', 'https://line.me/ti/p/@804poufw');
add_option('_operation_fee_rate', 0.005);
add_option('_operation_wallet_address', 'DKVr5kVFcDDREPeLSDvUcNbXAffdYuPQCd');

function register_session() {
    if ( ! session_id() ) {
        session_start();
    }
}
add_action( 'init', 'register_session' );

// Function to check if the string is a valid URL
function isURL($str) {
    $pattern = '/^(http|https):\/\/[^ "]+$/';
    return preg_match($pattern, $str) === 1;
}

function init_webhook_events() {
    global $wpdb;
    $line_bot_api = new line_bot_api();
    $open_ai_api = new open_ai_api();

    foreach ((array)$line_bot_api->parseEvents() as $event) {
        // Start the User Login/Registration process if got the one time password
        if ($event['message']['text']==get_option('_one_time_password')) {
            $link_uri = home_url().'/?_id='.$event['source']['userId'];

            if (file_exists(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json')) {
                $see_more = file_get_contents(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json');
                $see_more = json_decode($see_more, true);
                $see_more["body"]["contents"][0]["action"]["label"] = 'User Login/Registration';
                $see_more["body"]["contents"][0]["action"]["uri"] = $link_uri;
                $line_bot_api->replyMessage([
                    'replyToken' => $event['replyToken'],
                    'messages' => [
                        [
                            "type" => "flex",
                            "altText" => 'Welcome message',
                            'contents' => $see_more
                        ]
                    ]
                ]);
            } else {
                $line_bot_api->replyMessage([
                    'replyToken' => $event['replyToken'],
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $link_uri
                        ]                                                                    
                    ]
                ]);    
            }
        }

        switch ($event['type']) {
            case 'message':
                $message = $event['message'];
                switch ($message['type']) {
                    case 'text':
                        // Open-AI auto reply
                        $param=array();
                        $param["messages"][0]["content"]=$message['text'];
                        $response = $open_ai_api->createChatCompletion($param);
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
add_action( 'init', 'init_webhook_events' );

function user_did_not_login() {
    // User did not login system yet
    if( isset($_GET['_id']) && isset($_GET['_opt']) && isset($_GET['_name']) ) {
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

        $link_uri = home_url().'/?_id='.$_GET['_id'].'&_otp='.$_GET['_one_time_password'];

        echo '<div class="ui-widget" style="text-align:center;">';
        echo '<p>This is an automated process that helps you register for the system.</p>';
        echo '<p>Please click the Submit button below to complete your registration.</p>';
        echo '<form action="'.esc_url( site_url( 'wp-login.php', 'login_post' ) ).'" method="post" style="display:inline-block;">';
        echo '<fieldset>';
        echo '<label for="display-name">';
        echo '<input type="text" id="display-name" name="display_name" value="'. $_GET['_name'] .'" class="text ui-widget-content ui-corner-all" />';
        echo '<input type="hidden" name="log" value="'. $_GET['_id'] .'" />';
        echo '<input type="hidden" name="pwd" value="'. $_GET['_id'] .'" />';
        echo '<input type="hidden" name="rememberme" value="foreverchecked" />';
        //echo '<input type="hidden" name="redirect_to" value="'.esc_url( $link_uri ).'" />';
        echo '<input type="hidden" name="redirect_to" value="'.home_url().'" />';
        echo '<input type="submit" name="wp-submit" class="button button-primary" value="Submit" />';
        echo '</fieldset>';
        echo '</form>';
        echo '</div>';

    } else {
        // Display a message or redirect to the login/registration page
        $one_time_password = random_int(100000, 999999);
        update_option('_one_time_password', $one_time_password);

        echo '<div class="ui-widget" style="text-align:center;">';
        echo '感謝您使用我們的系統<br>';
        echo '請利用手機<span class="dashicons dashicons-smartphone"></span>按'.'<a href="'.get_option('_line_account').'">這裡</a><br>';
        echo '加入我們的Line官方帳號,<br>';
        echo '並請在聊天室中, 輸入六位數字:';
        echo '<h4>'.get_option('_one_time_password').'</h4>';
        echo '完成註冊/登入作業<br>';
        echo '</div>';

    }
}



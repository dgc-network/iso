<?php
/**
 * Plugin Name: iso
 * Plugin URI: https://wordpress.org/plugins/iso/
 * Description: The leading documents management plugin for iso system by shortcode
 * Author: dgc.network
 * Author URI: https://dgc.network/
 * Version: 1.0.5
 * Requires at least: 6.0
 * Tested up to: 6.5.3
 *
 * Text Domain: iso
 * Domain Path: /languages/
 */

if (!defined('ABSPATH')) {
    exit;
}

if ( headers_sent( $file, $line ) ) {
    error_log( "Headers already sent in $file on line $line" );
}
/*
function register_session() {
    if ( ! session_id() && ! is_rest_request() ) {
        session_start();
    }
}

function is_rest_request() {
    return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

add_action( 'init', 'register_session', 1 );
*/

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
add_action( 'init', 'register_session', 1 );

function wp_enqueue_scripts_and_styles() {
    $version = '1.0.5.'.time(); // Update this version number when you make changes
    wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
    wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
    wp_enqueue_style('wp-enqueue-css', plugins_url('/assets/css/wp-enqueue.css', __DIR__), '', time());

    wp_enqueue_script('iso-helper', plugins_url('/assets/js/iso-helper.js', __DIR__), array('jquery'), time());
    wp_localize_script('iso-helper', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('iso-helper-nonce'), // Generate nonce
    ));

    wp_enqueue_script('display-documents', plugins_url('/assets/js/display-documents.js', __DIR__), array('jquery'), time());
    wp_localize_script('display-documents', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('display-documents-nonce'), // Generate nonce
    ));                

    wp_enqueue_script('to-do-list', plugins_url('/assets/js/to-do-list.js', __DIR__), array('jquery'), time());
    wp_localize_script('to-do-list', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('to-do-list-nonce'), // Generate nonce
    ));                

    wp_enqueue_script('display-profiles', plugins_url('/assets/js/display-profiles.js', __DIR__), array('jquery'), time());
    wp_localize_script('display-profiles', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('display-profiles-nonce'), // Generate nonce
    ));                

    wp_enqueue_script('erp-cards', plugins_url('/assets/js/erp-cards.js', __DIR__), array('jquery'), time());
    wp_localize_script('erp-cards', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('erp-cards-nonce'), // Generate nonce
    ));                

    wp_enqueue_script('subforms', plugins_url('/assets/js/subforms.js', __DIR__), array('jquery'), time());
    wp_localize_script('subforms', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('subforms-nonce'), // Generate nonce
    ));                

}
add_action('wp_enqueue_scripts', 'wp_enqueue_scripts_and_styles');

function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
      show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'remove_admin_bar');  

function allow_subscribers_to_view_users($allcaps, $caps, $args) {
    // Check if the user is trying to view other users
    if (isset($args[0]) && $args[0] === 'list_users') {
        // Check if the user has the "subscriber" role
        $user = wp_get_current_user();
        if (in_array('subscriber', $user->roles)) {
            // Allow subscribers to view users
            $allcaps['list_users'] = true;
        }
    }
    return $allcaps;
}
add_filter('user_has_cap', 'allow_subscribers_to_view_users', 10, 3);

function get_post_type_meta_keys($post_type) {
    global $wpdb;
    $query = $wpdb->prepare("
        SELECT DISTINCT(meta_key)
        FROM $wpdb->postmeta
        INNER JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
        WHERE $wpdb->posts.post_type = %s
    ", $post_type);
    return $wpdb->get_col($query);
}

function isURL($str) {
    $pattern = '/^(http|https):\/\/[^ "]+$/';
    return preg_match($pattern, $str) === 1;
}

//require_once plugin_dir_path( __FILE__ ) . 'erp/erp-cards.php';
//require_once plugin_dir_path( __FILE__ ) . 'erp/subforms.php';
require_once plugin_dir_path( __FILE__ ) . 'services/services.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/iso-helper.php';

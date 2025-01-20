<?php
/**
 * Plugin Name: iso
 * Plugin URI: https://wordpress.org/plugins/iso/
 * Description: The leading documents management plugin for iso system by shortcode
 * Author: dgc.network
 * Author URI: https://dgc.network/
 * Version: 1.0.6
 * Requires at least: 6.0
 * Tested up to: 6.5.3
 *
 * Text Domain: textdomain
 * Domain Path: /languages/
 */

if (!defined('ABSPATH')) {
    exit;
}
/*
if ( headers_sent( $file, $line ) ) {
    error_log( "Headers already sent in $file on line $line" );
}
*/
/*
function is_rest_request() {
    return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

function register_session() {
    if ( ! session_id() && ! is_rest_request() ) {
        //session_start();
    }
}
add_action( 'init', 'register_session', 1 );
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
function plugin_load_textdomain() {
    load_plugin_textdomain( 'textdomain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'plugin_load_textdomain' );

function admin_enqueue_scripts_and_styles() {
    wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
    wp_enqueue_script('jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), '1.13.2', true);

    wp_enqueue_style('admin-enqueue-css', plugins_url('assets/css/admin-enqueue.css', __FILE__), '', time());
    wp_enqueue_script('admin-enqueue-js', plugins_url('assets/js/admin-enqueue.js', __FILE__), array('jquery'), time());
    wp_localize_script('admin-enqueue-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('iso_documents_nonce'), // Generate nonce
    ));
}
//add_action('admin_enqueue_scripts', 'admin_enqueue_scripts_and_styles');

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

require_once plugin_dir_path( __FILE__ ) . 'services/services.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/iso-helper.php';

function remove_system_doc_meta_once() {
    if (!get_option('removed_system_doc_meta')) {
        $args = array(
            'post_type'      => 'document',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        );
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            foreach ($query->posts as $post_id) {
                delete_post_meta($post_id, 'system_doc');
            }
        }

        wp_reset_postdata();

        // Mark as done
        update_option('removed_system_doc_meta', true);
    }
}
add_action('init', 'remove_system_doc_meta_once');

function set_language_based_on_browser() {
    // Check if the user is logged in or not
    if (!is_admin()) {
        // Get the browser's language setting from the HTTP_ACCEPT_LANGUAGE header
        $browser_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2); // Get the first two letters (language code)

        // Check if the detected language is supported by WordPress
        $supported_languages = ['en', 'fr', 'de', 'es', 'zh']; // Add the languages you support

        // If the detected language is supported, set it as the site language
        if (in_array($browser_language, $supported_languages)) {
            // Set the language based on the browser setting
            switch_to_locale($browser_language);
        }
    }
}
add_action('init', 'set_language_based_on_browser');

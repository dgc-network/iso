<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//require_once plugin_dir_path( __FILE__ ) . 'iot-messages.php';
require_once plugin_dir_path( __FILE__ ) . 'business-central.php';
require_once plugin_dir_path( __FILE__ ) . 'line-bot-api.php';
require_once plugin_dir_path( __FILE__ ) . 'open-ai-api.php';
require_once plugin_dir_path( __FILE__ ) . 'line-login-api.php';

function web_service_menu() {
    add_options_page(
        'Web Service Settings',
        'Web Service',
        'manage_options',
        'web-service-settings',
        'web_service_settings_page'
    );
}
add_action('admin_menu', 'web_service_menu');

function web_service_settings_page() {
    ?>
    <div class="wrap">
        <h2>Web Service Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('web-service-settings');
            do_settings_sections('web-service-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function web_service_register_settings() {
    // Register Operation section
    add_settings_section(
        'operation-section-settings',
        'Operation Settings',
        'operation_section_settings_callback',
        'web-service-settings'
    );

    // Register fields for Operation section
    add_settings_field(
        'default_video_url',
        'Default video URL',
        'default_video_url_callback',
        'web-service-settings',
        'operation-section-settings',
    );
    register_setting('web-service-settings', 'default_video_url');
    
    add_settings_field(
        'default_image_url',
        'Default image URL',
        'default_image_url_callback',
        'web-service-settings',
        'operation-section-settings',
    );
    register_setting('web-service-settings', 'default_image_url');
    
    add_settings_field(
        'operation_row_counts',
        'Row counts',
        'operation_row_counts_callback',
        'web-service-settings',
        'operation-section-settings',
    );
    register_setting('web-service-settings', 'operation_row_counts');
    
    add_settings_field(
        'operation_fee_rate',
        'Operation fee rate',
        'operation_fee_rate_callback',
        'web-service-settings',
        'operation-section-settings',
    );
    register_setting('web-service-settings', 'operation_fee_rate');
    
    add_settings_field(
        'operation_wallet_address',
        'Wallet address',
        'operation_wallet_address_callback',
        'web-service-settings',
        'operation-section-settings',
    );
    register_setting('web-service-settings', 'operation_wallet_address');
    
}
add_action('admin_init', 'web_service_register_settings');

function operation_section_settings_callback() {
    echo '<p>Settings for operation.</p>';
}

function default_video_url_callback() {
    $value = get_option('default_video_url');
    echo '<input type="text" name="default_video_url" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function default_image_url_callback() {
    $value = get_option('default_image_url');
    echo '<input type="text" name="default_image_url" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_row_counts_callback() {
    $value = get_option('operation_row_counts');
    echo '<input type="text" name="operation_row_counts" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_fee_rate_callback() {
    $value = get_option('operation_fee_rate');
    echo '<input type="text" name="operation_fee_rate" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_wallet_address_callback() {
    $value = get_option('operation_wallet_address');
    echo '<input type="text" name="operation_wallet_address" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function add_custom_permalink_rule() {
    add_rewrite_rule(
        '^landing-page/?$', // Match the custom page slug
        'index.php?custom_page=1', // Redirect to a query variable
        'top'
    );
}
add_action('init', 'add_custom_permalink_rule');

function register_custom_query_vars($vars) {
    $vars[] = 'custom_page'; // Add the custom query variable
    return $vars;
}
add_filter('query_vars', 'register_custom_query_vars');

function load_custom_template($template) {
    if (get_query_var('custom_page')) {
        // Check for a specific template file in your theme
        $custom_template = locate_template('custom-page-template.php');
        if ($custom_template) {
            return $custom_template; // Use your theme's custom template file
        } else {
            // Fallback: Output content programmatically
            header('Content-Type: text/html; charset=' . get_option('blog_charset'));
            echo '<!DOCTYPE html>';
            echo '<html>';
            echo '<head>';
            echo '<title>Custom Page</title>';
            echo '</head>';
            echo '<body>';
            echo '<h1>Welcome to the Custom Page!</h1>';
            echo '<p>This is dynamically generated content.</p>';
            echo '</body>';
            echo '</html>';
            exit; // Prevent WordPress from continuing to load
        }
    }
    return $template;
}
add_filter('template_include', 'load_custom_template');

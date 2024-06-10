<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function web_service_register_settings() {
/*    
    // Register Line bot section
    add_settings_section(
        'line_bot_settings_section',
        'Line bot Settings',
        'line_bot_settings_section_callback',
        'web-service-settings'
    );

    // Register fields for Line bot section
    add_settings_field(
        'line_bot_token_option',
        'Line bot Token',
        'line_bot_token_option_callback',
        'web-service-settings',
        'line_bot_settings_section'
    );
    register_setting('web-service-settings', 'line_bot_token_option');

    add_settings_field(
        'line_official_account',
        'Line official account',
        'line_official_account_callback',
        'web-service-settings',
        'line_bot_settings_section'
    );
    register_setting('web-service-settings', 'line_official_account');

    add_settings_field(
        'line_official_qr_code',
        'Line official qr-code',
        'line_official_qr_code_callback',
        'web-service-settings',
        'line_bot_settings_section'
    );
    register_setting('web-service-settings', 'line_official_qr_code');
/*
    // Register AI section
    add_settings_section(
        'open_ai_settings_section',
        'Open AI Settings',
        'open_ai_settings_section_callback',
        'web-service-settings'
    );

    // Register fields for AI section
    add_settings_field(
        'open_ai_api_key',
        'API_KEY',
        'open_ai_api_key_callback',
        'web-service-settings',
        'open_ai_settings_section'
    );
    register_setting('web-service-settings', 'open_ai_api_key');
*/
    // Register Business Central section
    add_settings_section(
        'business_central_settings_section',
        'Business Central Settings',
        'business_central_settings_section_callback',
        'web-service-settings'
    );

    // Register fields for Business Central section
    add_settings_field(
        'tenant_id',
        'Tenant ID',
        'tenant_id_callback',
        'web-service-settings',
        'business_central_settings_section'
    );
    register_setting('web-service-settings', 'tenant_id');

    add_settings_field(
        'client_id',
        'Client ID',
        'client_id_callback',
        'web-service-settings',
        'business_central_settings_section'
    );
    register_setting('web-service-settings', 'client_id');

    add_settings_field(
        'client_secret',
        'Client Secret',
        'client_secret_callback',
        'web-service-settings',
        'business_central_settings_section'
    );
    register_setting('web-service-settings', 'client_secret');

    add_settings_field(
        'redirect_uri',
        'Redirect URI',
        'redirect_uri_callback',
        'web-service-settings',
        'business_central_settings_section'
    );
    register_setting('web-service-settings', 'redirect_uri');

    add_settings_field(
        'bc_scope',
        'Scope',
        'bc_scope_callback',
        'web-service-settings',
        'business_central_settings_section'
    );
    register_setting('web-service-settings', 'bc_scope');

    // Register Operation section
    add_settings_section(
        'operation_settings_section',
        'Operation Settings',
        'operation_settings_section_callback',
        'web-service-settings'
    );

    // Register fields for Operation section
    add_settings_field(
        'default_video_url',
        'Default video URL',
        'default_video_url_callback',
        'web-service-settings',
        'operation_settings_section'
    );
    register_setting('web-service-settings', 'default_video_url');
    
    add_settings_field(
        'default_image_url',
        'Default image URL',
        'default_image_url_callback',
        'web-service-settings',
        'operation_settings_section'
    );
    register_setting('web-service-settings', 'default_image_url');
    
    add_settings_field(
        'operation_row_counts',
        'Row counts',
        'operation_row_counts_callback',
        'web-service-settings',
        'operation_settings_section'
    );
    register_setting('web-service-settings', 'operation_row_counts');
    
    add_settings_field(
        'operation_fee_rate',
        'Operation fee rate',
        'operation_fee_rate_callback',
        'web-service-settings',
        'operation_settings_section'
    );
    register_setting('web-service-settings', 'operation_fee_rate');
    
    add_settings_field(
        'operation_wallet_address',
        'Wallet address',
        'operation_wallet_address_callback',
        'web-service-settings',
        'operation_settings_section'
    );
    register_setting('web-service-settings', 'operation_wallet_address');
    
}
add_action('admin_init', 'web_service_register_settings');

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

function line_bot_settings_section_callback() {
    echo '<p>Settings for Line bot.</p>';
}

function line_bot_token_option_callback() {
    $value = get_option('line_bot_token_option');
    echo '<input type="text" id="line_bot_token_option" name="line_bot_token_option" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function line_official_account_callback() {
    $value = get_option('line_official_account');
    echo '<input type="text" id="line_official_account" name="line_official_account" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function line_official_qr_code_callback() {
    $value = get_option('line_official_qr_code');
    echo '<input type="text" id="line_official_qr_code" name="line_official_qr_code" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function open_ai_settings_section_callback() {
    echo '<p>Settings for Open AI.</p>';
}

function open_ai_api_key_callback() {
    $value = get_option('open_ai_api_key');
    echo '<input type="text" id="open_ai_api_key" name="open_ai_api_key" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function business_central_settings_section_callback() {
    echo '<p>Settings for Business Central.</p>';
}

function tenant_id_callback() {
    $value = get_option('tenant_id');
    echo '<input type="text" id="tenant_id" name="tenant_id" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function client_id_callback() {
    $value = get_option('client_id');
    echo '<input type="text" id="client_id" name="client_id" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function client_secret_callback() {
    $value = get_option('client_secret');
    echo '<input type="text" id="client_secret" name="client_secret" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function redirect_uri_callback() {
    $value = get_option('redirect_uri');
    echo '<input type="text" id="redirect_uri" name="redirect_uri" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function bc_scope_callback() {
    $value = get_option('bc_scope');
    echo '<input type="text" id="bc_scope" name="bc_scope" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_settings_section_callback() {
    echo '<p>Settings for operation.</p>';
}

function default_video_url_callback() {
    $value = get_option('default_video_url');
    echo '<input type="text" id="default_video_url" name="default_video_url" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function default_image_url_callback() {
    $value = get_option('default_image_url');
    echo '<input type="text" id="default_image_url" name="default_image_url" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_row_counts_callback() {
    $value = get_option('operation_row_counts');
    echo '<input type="text" id="operation_row_counts" name="operation_row_counts" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_fee_rate_callback() {
    $value = get_option('operation_fee_rate');
    echo '<input type="text" id="operation_fee_rate" name="operation_fee_rate" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_wallet_address_callback() {
    $value = get_option('operation_wallet_address');
    echo '<input type="text" id="operation_wallet_address" name="operation_wallet_address" style="width:100%;" value="' . esc_attr($value) . '" />';
}


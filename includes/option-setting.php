<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function my_plugin_register_settings() {
    // Register a section
    add_settings_section(
        'line_bot_settings_section',
        'Line bot Settings',
        'line_bot_settings_section_callback',
        'general'
    );

    // Register a field
    add_settings_field(
        'line_bot_token_option',
        'Token',
        'line_bot_token_option_callback',
        'general',
        'line_bot_settings_section'
    );
    register_setting('general', 'line_bot_token_option');

    add_settings_field(
        'line_official_account',
        'Line official account',
        'line_official_account_callback',
        'general',
        'line_bot_settings_section'
    );
    register_setting('general', 'line_official_account');

    // Register a section
    add_settings_section(
        'open_ai_settings_section',
        'Open AI Settings',
        'open_ai_settings_section_callback',
        'general'
    );

    // Register a field
    add_settings_field(
        'open_ai_token_api_key',
        'API_KEY',
        'open_ai_token_api_key_callback',
        'general',
        'open_ai_settings_section'
    );
    register_setting('general', 'open_ai_token_api_key');
    
}
add_action('admin_init', 'my_plugin_register_settings');

function line_bot_settings_section_callback() {
    echo '<p>Settings for Line bot.</p>';
}

function iso_plugin_menu() {
    add_options_page(
        'iso Plugin Settings',
        'iso Plugin',
        'manage_options',
        'iso-plugin-settings',
        'iso_plugin_settings_page'
    );
}
add_action('admin_menu', 'iso_plugin_menu');

function iso_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h2>iso Plugin Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('general');
            do_settings_sections('general');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function line_bot_token_option_callback() {
    $value = get_option('line_bot_token_option');
    echo '<input type="text" id="line_bot_token_option" name="line_bot_token_option" class="text ui-widget-content ui-corner-all" value="' . esc_attr($value) . '" />';
}

function line_official_account_callback() {
    $value = get_option('line_official_account');
    echo '<input type="text" id="line_official_account" name="line_official_account" class="text ui-widget-content ui-corner-all" value="' . esc_attr($value) . '" />';
}

function open_ai_token_api_key_callback() {
    $value = get_option('open_ai_token_api_key');
    echo '<input type="text" id="open_ai_token_api_key" name="open_ai_token_api_key" class="text ui-widget-content ui-corner-all" value="' . esc_attr($value) . '" />';
}


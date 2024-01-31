<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function my_plugin_register_settings() {
    // Register a section
    add_settings_section(
        'my_plugin_settings_section',
        'My Plugin Settings',
        'my_plugin_settings_section_callback',
        'general'
    );

    // Register a field
    add_settings_field(
        'my_plugin_option',
        'My Option',
        'my_plugin_option_callback',
        'general',
        'my_plugin_settings_section'
    );

    // Register the setting
    register_setting('general', 'my_plugin_option');
}
add_action('admin_init', 'my_plugin_register_settings');

function my_plugin_settings_section_callback() {
    echo '<p>Settings for My Plugin.</p>';
}

function my_plugin_option_callback() {
    $value = get_option('my_plugin_option');
    echo '<input type="text" id="my_plugin_option" name="my_plugin_option" value="' . esc_attr($value) . '" />';
}

function my_plugin_menu() {
    add_options_page(
        'My Plugin Settings',
        'My Plugin',
        'manage_options',
        'my-plugin-settings',
        'my_plugin_settings_page'
    );
}
add_action('admin_menu', 'my_plugin_menu');

function my_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h2>My Plugin Settings</h2>
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

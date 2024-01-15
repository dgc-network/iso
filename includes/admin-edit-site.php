<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register site post type
function register_site_post_type() {
    $labels = array(
        'name'               => _x( 'Sites', 'post type general name', 'your-text-domain' ),
        'singular_name'      => _x( 'Site', 'post type singular name', 'your-text-domain' ),
        'add_new'            => _x( 'Add New Site', 'book', 'your-text-domain' ),
        'add_new_item'       => __( 'Add New Site', 'your-text-domain' ),
        'edit_item'          => __( 'Edit Site', 'your-text-domain' ),
        'new_item'           => __( 'New Site', 'your-text-domain' ),
        'all_items'          => __( 'All Sites', 'your-text-domain' ),
        'view_item'          => __( 'View Site', 'your-text-domain' ),
        'search_items'       => __( 'Search Sites', 'your-text-domain' ),
        'not_found'          => __( 'No sites found', 'your-text-domain' ),
        'not_found_in_trash' => __( 'No sites found in the Trash', 'your-text-domain' ),
        'parent_item_colon'  => '',
        'menu_name'          => 'Sites'
    );

    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'rewrite'       => array('slug' => 'sites'),
        'supports'      => array( 'title', 'editor', 'custom-fields' ),
        'has_archive'   => true,
        'menu_icon'     => 'dashicons-admin-multisite',
    );

    register_post_type( 'site', $args );
}
add_action('init', 'register_site_post_type');

// Custom columns
function add_site_custom_field_column($columns) {
    // Insert the custom field column after the 'title' column
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            // Add the custom field column after the 'title' column
            $new_columns['site_url_column'] = __('Site URL', 'your-text-domain');
        }
    }
    return $new_columns;
}
add_filter('manage_site_posts_columns', 'add_site_custom_field_column');

function add_sortable_site_custom_field_column($sortable_columns) {
    $sortable_columns['site_url_column'] = 'site_url';
    return $sortable_columns;
}
add_filter('manage_edit-site_sortable_columns', 'add_sortable_site_custom_field_column');

function display_site_custom_field_in_admin_list($column, $post_id) {
    if ($column === 'site_url_column') {
        echo esc_html(get_post_meta($post_id, 'site_url', true));
    }
}
add_action('manage_site_posts_custom_column', 'display_site_custom_field_in_admin_list', 10, 2);

// Meta boxes
function add_site_image_metabox() {
    add_meta_box(
        'site_image_id',
        'Site Image',
        'site_image_content',
        'site',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'add_site_image_metabox');

function site_image_content($post) {
    wp_nonce_field('site_image_nonce', 'site_image_nonce');
    $image_url = esc_attr(get_post_meta($post->ID, 'image_url', true));
    ?>
    <div id="custom-image-container">
        <?php echo (isURL($image_url)) ? '<img src="' . $image_url . '" style="object-fit:cover; width:250px; height:250px;">' : '<a href="#" id="custom-image-href">Set image URL</a>'; ?>
    </div>
    <div id="image-url-dialog" style="display:none;">
        <fieldset>
            <label for="image-url-input">Image URL:</label>
            <textarea id="image-url-input" name="image_url" rows="3" style="width:99%;"><?php echo $image_url; ?></textarea>
            <button id="set-image-url">Set</button>
        </fieldset>
    </div>
    <?php
}

function save_site_image_content($post_id) {
    if (!isset($_POST['site_image_nonce']) || !wp_verify_nonce($_POST['site_image_nonce'], 'site_image_nonce') || !current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if (isset($_POST['image_url'])) {
        update_post_meta($post_id, 'image_url', sanitize_text_field($_POST['image_url']));
    }
}
add_action('save_post', 'save_site_image_content');

function add_site_settings_metabox() {
    add_meta_box(
        'site_settings_id',
        'Site Settings',
        'site_settings_content',
        'site',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_site_settings_metabox');

function site_settings_content($post) {
    wp_nonce_field('site_settings_nonce', 'site_settings_nonce');
    $cust_no = esc_attr(get_post_meta($post->ID, 'cust_no', true));
    $country = esc_attr(get_post_meta($post->ID, 'country', true));
    $site_url = esc_attr(get_post_meta($post->ID, 'site_url', true));
    ?>
    <label for="cust-no"> Site URL: </label>
    <input type="text" id="cust-no" name="cust_no" value="<?php echo $cust_no; ?>" class="text ui-widget-content ui-corner-all" >
    <label for="country"> Site URL: </label>
    <input type="text" id="country" name="country" value="<?php echo $country; ?>" class="text ui-widget-content ui-corner-all" >
    <label for="site-url"> Site URL: </label>
    <input type="text" id="site-url" name="site_url" value="<?php echo $site_url; ?>" class="text ui-widget-content ui-corner-all" >
    <?php
    // Call the function with the CSV file name
    processCsvFromMediaLibrary('customer.csv');

}

function save_site_settings_content($post_id) {
    if (!isset($_POST['site_settings_nonce']) || !wp_verify_nonce($_POST['site_settings_nonce'], 'site_settings_nonce') || !current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if (isset($_POST['cust_no'])) {
        update_post_meta($post_id, 'cust_no', sanitize_text_field($_POST['cust_no']));
    }
    if (isset($_POST['country'])) {
        update_post_meta($post_id, 'country', sanitize_text_field($_POST['country']));
    }
    if (isset($_POST['site_url'])) {
        update_post_meta($post_id, 'site_url', sanitize_text_field($_POST['site_url']));
    }
}
add_action('save_post', 'save_site_settings_content');

// Include WordPress functions
require_once('wp-load.php');

// Function to download and process CSV
function processCsvFromMediaLibrary($filename) {
    // Get the file URL from the Media Library
    $file_url = wp_get_attachment_url(get_page_by_title($filename, OBJECT, 'attachment')->ID);

    // Check if the file URL is valid
    if ($file_url) {
        // Download the CSV file
        $csv_data = file_get_contents($file_url);

        // Process the CSV data
        if ($csv_data !== false) {
            $lines = explode("\n", $csv_data);

            // Iterate through each CSV row
            foreach ($lines as $line) {
                $data = str_getcsv($line);

                // Process each column data
                foreach ($data as $column) {
                    // Your processing logic here
                    echo $column . ' ';
                }

                echo "<br>";
            }
        } else {
            echo 'Error downloading CSV file.';
        }
    } else {
        echo 'File not found in the Media Library.';
    }
}


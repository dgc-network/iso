<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register custom post type
function register_document_post_type() {
    $labels = array(
        'name'               => _x( 'Documents', 'post type general name', 'your-text-domain' ),
        'singular_name'      => _x( 'Document', 'post type singular name', 'your-text-domain' ),
        'add_new'            => _x( 'Add New Document', 'book', 'your-text-domain' ),
        'add_new_item'       => __( 'Add New Document', 'your-text-domain' ),
        'edit_item'          => __( 'Edit Document', 'your-text-domain' ),
        'new_item'           => __( 'New Document', 'your-text-domain' ),
        'all_items'          => __( 'All Documents', 'your-text-domain' ),
        'view_item'          => __( 'View Document', 'your-text-domain' ),
        'search_items'       => __( 'Search Documents', 'your-text-domain' ),
        'not_found'          => __( 'No documents found', 'your-text-domain' ),
        'not_found_in_trash' => __( 'No documents found in the Trash', 'your-text-domain' ),
        'parent_item_colon'  => '',
        'menu_name'          => 'Documents'
    );

    $args = array(
        'labels'        => $labels,
        'public'        => true,
        //'supports'      => array( 'title', 'editor', 'custom-fields' ),
        'supports'      => array( 'title', 'custom-fields' ),
        'taxonomies'    => array( 'category', 'post_tag' ),
        'has_archive'   => true,
        'rewrite'       => array('slug' => 'documents'),
        'menu_icon'     => 'dashicons-media-document',
    );
    register_post_type( 'document', $args );
}
add_action('init', 'register_document_post_type');

// Custom columns
function add_custom_document_field_column($columns) {
    // Insert the custom field column after the 'title' column
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            // Add the custom field column after the 'title' column
            $new_columns['number_column'] = __('Doc.#', 'your-text-domain');
            $new_columns['revision_column'] = __('Rev.', 'your-text-domain');
            $new_columns['site_column'] = __('Site', 'your-text-domain');
        }
    }
    return $new_columns;
}
add_filter('manage_document_posts_columns', 'add_custom_document_field_column');

function add_sortable_custom_document_field_column($sortable_columns) {
    // Add the custom field columns as sortable
    $sortable_columns['number_column'] = 'document_number';
    $sortable_columns['revision_column'] = 'document_revision';
    $sortable_columns['site_column'] = 'document_site';
    return $sortable_columns;
}
add_filter('manage_edit-document_sortable_columns', 'add_sortable_custom_document_field_column');

// Display custom field value in the custom column
function display_custom_document_field_in_admin_list($column, $post_id) {
    switch ($column) {
        case 'number_column':
            echo esc_html(get_post_meta($post_id, 'document_number', true));
            break;
        case 'revision_column':
            echo esc_html(get_post_meta($post_id, 'document_revision', true));
            break;
        case 'site_column':
            $site_id = get_post_meta($post_id, 'document_site', true);
            $site_title = get_the_title($site_id);
            echo esc_html($site_title);
            break;
    }
}
add_action('manage_document_posts_custom_column', 'display_custom_document_field_in_admin_list', 10, 2);

// Meta boxes
function add_document_settings_metabox() {
    add_meta_box(
        'document_settings_metabox',
        'Document settings',
        'document_settings_content',
        'document',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_document_settings_metabox');

// Callback function to display the content of the meta box
function document_settings_content($post) {
    wp_nonce_field('document_settings_nonce', 'document_settings_nonce');
    // Retrieve the meta value
    $document_date = get_post_meta($post->ID, 'document_date', true);
    $document_number = get_post_meta($post->ID, 'document_number', true);
    $document_revision = get_post_meta($post->ID, 'document_revision', true);
    $document_url = get_post_meta($post->ID, 'document_url', true);
    $document_site = get_post_meta($post->ID, 'document_site', true);
    $in_charge_department = get_post_meta($post->ID, 'in_charge_department', true);
    
    // Output the HTML for the meta box
    ?>
        <label for="document-number"> 文件編號: </label>
        <input type="text" id="document-number" name="document_number" value="<?php echo esc_attr($document_number);?>" >
        <label for="document-revision"> Revision: </label>
        <input type="text" id="document-revision" name="document_revision" value="<?php echo esc_attr($document_revision);?>" >
        <label for="document-date"> 發行日期: </label>
        <input type="text" id="document-date" name="document_date" value="<?php echo esc_attr($document_date);?>" ><br>
        <label for="document-url"> URL: </label>
        <input type="text" id="document-url" name="document_url" value="<?php echo esc_url($document_url);?>" style="width:80%;"><br>
        <label for="document-site"> Site: </label>
        <select id="document-site" name="document_site">
            <?php
            $site_id = esc_attr($document_site);
            echo '<option value="">Select Site</option>';
            $site_args = array(
                'post_type'      => 'site',
                'posts_per_page' => -1,
            );
            $sites = get_posts($site_args);    
            foreach ($sites as $site) {
                $selected = ($site_id == $site->ID) ? 'selected' : '';
                echo '<option value="' . esc_attr($site->ID) . '" ' . $selected . '>' . esc_html($site->post_title) . '</option>';
            }
            ?>
        </select>
        <label for="in-charge-department"> In charge department: </label>
        <input type="text" id="in-charge-department" name="in_charge_department" value="<?php echo esc_attr($in_charge_department); ?>" ><br>
    </div>
    <?php

}

// Function to save the meta box data
function save_document_settings_content($post_id) {
    // Check if nonce is set
    if (!isset($_POST['document_settings_nonce']) || !wp_verify_nonce($_POST['document_settings_nonce'], 'document_settings_nonce')) {
        return $post_id;
    }

    // Check if the user has permissions to save data
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    // Fields array for better maintenance
    $fields = array('document_url', 'document_site', 'document_date', 'document_number', 'document_revision', 'in_charge_department');

    // Save custom field data
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post', 'save_document_settings_content');

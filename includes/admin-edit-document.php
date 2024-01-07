<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
    $sortable_columns['site_column'] = 'site_id';
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
            $site_id = get_post_meta($post_id, 'site_id', true);
            $site_title = get_the_title($site_id);
            echo esc_html($site_title);
            break;
    }
}
add_action('manage_document_posts_custom_column', 'display_custom_document_field_in_admin_list', 10, 2);
/*
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
*/
// Callback function to display the content of the meta box
function document_settings_content($post) {
    wp_nonce_field('document_settings_nonce', 'document_settings_nonce');
    // Retrieve the meta value
    $document_date = get_post_meta($post->ID, 'document_date', true);
    $document_number = get_post_meta($post->ID, 'document_number', true);
    $document_revision = get_post_meta($post->ID, 'document_revision', true);
    $document_url = get_post_meta($post->ID, 'document_url', true);
    $site_id = get_post_meta($post->ID, 'site_id', true);
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
        <label for="site-id"> Site: </label>
        <select id="site-id" name="site_id">
            <?php
            $site_id = esc_attr($site_id);
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
    $fields = array('document_url', 'site_id', 'document_date', 'document_number', 'document_revision', 'in_charge_department');

    // Save custom field data
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post', 'save_document_settings_content');

// Add a custom metabox
function add_doc_actions_metabox() {
    add_meta_box(
        'doc_actions_metabox',
        'Document settings',
        'doc_actions_content',
        'document',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_doc_actions_metabox');

function doc_actions_content($post) {
    document_settings_content($post);

    // Retrieve the value
    $query = retrieve_doc_actions_data($post->ID);
    // Action List inside Site actions metabox
    echo '<div class="ui-widget">';
    echo '<table class="ui-widget ui-widget-content" style="width:100%;">';
    echo '<thead><tr class="ui-widget-header ">';
    echo '<th></th>';
    echo '<th>Action</th>';
    echo '<th>Description</th>';
    echo '<th>Next</th>';
    echo '<th>Leadtime</th>';
    echo '<th></th>';
    echo '</tr></thead>';
    echo '<tbody>';
    $x = 0;
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            echo '<tr id="doc-action-list-'.$x.'">';
            echo '<td style="text-align:center;"><span id="btn-edit-action-'.get_the_ID().'" class="dashicons dashicons-edit"></span></td>';
            echo '<td><a href="'.get_permalink(get_the_ID()).'">'.get_the_title().'</a></td>';
            echo '<td>'.get_the_content().'</td>';
            echo '<td style="text-align:center;">'.get_post_meta(get_the_ID(), 'next_action', true).'</td>';
            echo '<td style="text-align:center;">'.get_post_meta(get_the_ID(), 'next_action_leadtime', true).'</td>';
            echo '<td style="text-align:center;"><span id="btn-del-action-'.get_the_ID().'" class="dashicons dashicons-trash"></span></td>';
            echo '</tr>';
            $x += 1;
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    while ($x<50) {
        echo '<tr id="doc-action-list-'.$x.'" style="display:none;"></tr>';
        $x += 1;
    }
    echo '</tbody>';
    // Button to add a new action
    echo '<tr>';
    echo '<td colspan="6"><div id="btn-new-action" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td>';
    echo '</tr>';
    echo '</table>';
    echo '</div>';
    // Embedded the $post->ID
    echo '<input type="hidden" id="doc-id" value="'.$post->ID.'" />';
}

function get_doc_action_list() {
    // Retrieve the value
    $query = retrieve_doc_actions_data($_POST['_doc_id']);

    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $_list = array();
            $_list["action_id"] = get_the_ID();
            $_list["action_title"] = '<a href="'.get_permalink(get_the_ID()).'">'.get_the_title().'</a>';
            $_list["action_description"] = get_the_content();
            $next_action_id = esc_html(get_post_meta(get_the_ID(), 'next_action', true));
            $_list["next_action_title"] = get_the_title($next_action_id);
            $_list["next_action_leadtime"] = esc_html(get_post_meta(get_the_ID(), 'next_action_leadtime', true));
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_doc_action_list', 'get_doc_action_list' );
add_action( 'wp_ajax_nopriv_get_doc_action_list', 'get_doc_action_list' );

function new_doc_action_data() {
    $current_user = wp_get_current_user();
    // Set up the post data
    $new_post = array(
        'post_title'    => 'New action',
        'post_content'  => 'Your post content goes here.',
        'post_status'   => 'publish', // Publish the post immediately
        'post_author'   => $current_user->ID, // Use the user ID of the author
        'post_type'     => 'action', // Change to your custom post type if needed
    );    
    // Insert the post into the database
    $post_id = wp_insert_post($new_post);
    
    // Check if the post was successfully inserted
    if ($post_id) {
        // Add metadata to the post
        update_post_meta($post_id, 'doc_id', $_POST['_doc_id']);
        update_post_meta($post_id, 'next_action_leadtime', 86400); // Assume the default is 1 day
    }
    wp_send_json($post_id);
}
add_action( 'wp_ajax_new_doc_action_data', 'new_doc_action_data' );
add_action( 'wp_ajax_nopriv_new_doc_action_data', 'new_doc_action_data' );

function del_doc_action_data() {
    // Delete the post
    $result = wp_delete_post($_POST['_action_id'], true); // Set the second parameter to true to force delete
    wp_send_json($result);
}
add_action( 'wp_ajax_del_doc_action_data', 'del_doc_action_data' );
add_action( 'wp_ajax_nopriv_del_doc_action_data', 'del_doc_action_data' );

function retrieve_doc_actions_data($_id=0) {
    // Retrieve the value
    $args = array(
        'post_type'      => 'action',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'doc_id',
                'value' => $_id,
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query;
}

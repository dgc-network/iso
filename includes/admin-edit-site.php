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
function add_custom_site_field_column($columns) {
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
add_filter('manage_site_posts_columns', 'add_custom_site_field_column');

function add_sortable_custom_site_field_column($sortable_columns) {
    $sortable_columns['site_url_column'] = 'site_url';
    return $sortable_columns;
}
add_filter('manage_edit-site_sortable_columns', 'add_sortable_custom_site_field_column');

function display_custom_site_field_in_admin_list($column, $post_id) {
    if ($column === 'site_url_column') {
        echo esc_html(get_post_meta($post_id, 'site_url', true));
    }
}
add_action('manage_site_posts_custom_column', 'display_custom_site_field_in_admin_list', 10, 2);

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
    $site_url = esc_attr(get_post_meta($post->ID, 'site_url', true));
    ?>
    <label for="site-url"> Site URL: </label>
    <input type="text" id="site-url" name="site_url" value="<?php echo $site_url; ?>" style="width:100%;">
    <?php
}

function save_site_settings_content($post_id) {
    if (!isset($_POST['site_settings_nonce']) || !wp_verify_nonce($_POST['site_settings_nonce'], 'site_settings_nonce') || !current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if (isset($_POST['site_url'])) {
        update_post_meta($post_id, 'site_url', sanitize_text_field($_POST['site_url']));
    }
}
add_action('save_post', 'save_site_settings_content');

// Register action post type
function register_action_post_type() {
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
        //'labels'        => $labels,
        'public'        => true,
        'rewrite'       => array('slug' => 'actions'),
        'supports'      => array( 'title', 'editor', 'custom-fields' ),
        'has_archive'   => true,
        'menu_icon'     => 'dashicons-admin-multisite',
    );
    register_post_type( 'action', $args );
}
add_action('init', 'register_action_post_type');

// Add a custom metabox for course sessions
function add_site_actions_metabox() {
    add_meta_box(
        'site_actions_metabox',
        'Site settings',
        'site_actions_content',
        'site',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_site_actions_metabox');

function site_actions_content($post) {
    site_settings_content($post);

    // Retrieve the value
    $query = retrieve_site_actions_data($post->ID);
    // Action List inside Site actions metabox
    echo '<div class="ui-widget">';
    echo '<table class="ui-widget ui-widget-content" style="width:100%;">';
    echo '<thead><tr class="ui-widget-header ">';
    echo '<th></th>';
    echo '<th>Action</th>';
    echo '<th>Description</th>';
    echo '<th>Leadtime</th>';
    echo '<th></th>';
    echo '</tr></thead>';
    echo '<tbody>';
    $x = 0;
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo '<tr id="site-action-list-'.$x.'">';
            echo '<td style="text-align:center;"><span id="btn-edit-action-'.get_the_ID().'" class="dashicons dashicons-edit"></span></td>';
            echo '<td><a href="'.get_permalink(get_the_ID()).'">'.get_the_title().'</a></td>';
            echo '<td>'.get_the_content().'</td>';
            echo '<td style="text-align:center;">'.get_post_meta(get_the_ID(), 'action_leadtime', true).'</td>';
            echo '<td style="text-align:center;"><span id="btn-del-action-'.get_the_ID().'" class="dashicons dashicons-trash"></span></td>';
            echo '</tr>';
            $x += 1;
        }    
        wp_reset_postdata(); // Reset post data to the main loop
    }

    //while ($x<(intval(get_post_meta( $this->courses_page_id, '_records_per_page', true ))-1)) {
    while ($x<50) {
        echo '<tr id="site-action-list-'.$x.'" style="display:none;"></tr>';
        $x += 1;
    }
    echo '</tbody>';
    // Button to add a new action
    echo '<tr>';
    echo '<td colspan="5"><div id="btn-new-action" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td>';
    echo '</tr>';
    echo '</table>';
    echo '</div>';

    // Action Dialog
    echo '<div id="site-action-dialog" title="Action dialog" style="display:none;">';
    echo '<fieldset>';
    echo '<input type="hidden" id="site-id" value="'.$post->ID.'" />';
    echo '<input type="hidden" id="action-id" />';
    echo '<label for="action-title">Action Title</label>';
    echo '<input type="text" id="action-title" class="text ui-widget-content ui-corner-all" />';

    echo '<div>';
    echo '<div style="display:inline-block; width:48%; margin-right:5px;">';
    echo '<label for="session-price">Session Price</label>';
    echo '<input type="text" id="session-price" class="text ui-widget-content ui-corner-all" />';
    echo '</div>';
    echo '<div style="display:inline-block; width:48%; ">';
    echo '<label for="session-period">Time Period(minutes)</label>';
    echo '<input type="text" id="session-period" class="text ui-widget-content ui-corner-all" />';
    echo '</div>';
    echo '</div>';

    echo '<div>';
    echo '<div style="display:inline-block; width:48%; margin-right:5px;">';
    echo '<label for="session-visibility">Session Visibility</label>';
    echo '<input type="checkbox" id="session-visibility" style="display:inline-block; width:5%; " /> hide from learners.';
    echo '</div>';
    echo '<div style="display:inline-block; width:48%; ">';
    echo '<label for="is-exam-session">Exam Session</label>';
    echo '<input type="checkbox" id="is-exam-session" style="display:inline-block; width:5%; " /> is exam session.';
    echo '</div>';
    echo '</div>';
    echo '</fieldset>';
    echo '</div>';
}

function get_site_action_list() {
    //calcuate_course_sessions_data($_POST['_course_id']);
    // Retrieve the value
    $query = retrieve_site_actions_data($_POST['_site_id']);

    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $_list = array();
            $_list["action_id"] = get_the_ID();
            $_list["action_title"] = '<a href="'.get_permalink(get_the_ID()).'">'.get_the_title().'</a>';
            $_list["action_description"] = get_the_content();
            $_list["action_leadtime"] = esc_html(get_post_meta(get_the_ID(), 'action_leadtime', true));
            array_push($_array, $_list);
        }    
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_site_action_list', 'get_site_action_list' );
add_action( 'wp_ajax_nopriv_get_site_action_list', 'get_site_action_list' );

function new_site_action_data() {
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
        update_post_meta($post_id, 'site_id', $_POST['_site_id']);
        update_post_meta($post_id, 'action_leadtime', 86400); // Assume the default is 1 day
    }
    wp_send_json($post_id);
}
add_action( 'wp_ajax_new_site_action_data', 'new_site_action_data' );
add_action( 'wp_ajax_nopriv_new_site_action_data', 'new_site_action_data' );

function del_site_action_data() {
    // Delete the post
    $result = wp_delete_post($_POST['_action_id'], true); // Set the second parameter to true to force delete
    wp_send_json($result);
}
add_action( 'wp_ajax_del_site_action_data', 'del_site_action_data' );
add_action( 'wp_ajax_nopriv_del_site_action_data', 'del_site_action_data' );

function retrieve_site_actions_data($_id=0) {
    // Retrieve the value
    $args = array(
        'post_type'      => 'action',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'site_id',
                'value' => $_id,
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query;
}

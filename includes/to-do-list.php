<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Create a Custom Post Type
function register_todo_post_type() {
    $labels = array(
        'name'               => _x('To-Do Items', 'post type general name', 'textdomain'),
        'singular_name'      => _x('To-Do Item', 'post type singular name', 'textdomain'),
        'menu_name'          => _x('To-Do Items', 'admin menu', 'textdomain'),
        'add_new'            => _x('Add New', 'to-do item', 'textdomain'),
        'add_new_item'       => __('Add New To-Do Item', 'textdomain'),
        'edit_item'          => __('Edit To-Do Item', 'textdomain'),
        'new_item'           => __('New To-Do Item', 'textdomain'),
        'view_item'          => __('View To-Do Item', 'textdomain'),
        'search_items'       => __('Search To-Do Items', 'textdomain'),
        'not_found'          => __('No to-do items found', 'textdomain'),
        'not_found_in_trash' => __('No to-do items found in the Trash', 'textdomain'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'todo'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
    );

    register_post_type('todo', $args);
}
add_action('init', 'register_todo_post_type');

// Add Custom Fields for To-Do Details
function add_todo_custom_fields() {
    add_meta_box(
        'todo_custom_fields',
        'To-Do Details',
        'display_todo_custom_fields',
        'todo',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'add_todo_custom_fields');

function display_todo_custom_fields($post) {
    $due_date = get_post_meta($post->ID, '_todo_due_date', true);
    ?>
    <label for="todo_due_date">Due Date:</label>
    <input type="text" id="todo_due_date" name="todo_due_date" value="<?php echo esc_attr($due_date); ?>" placeholder="YYYY-MM-DD">
    <?php
}

function save_todo_custom_fields($post_id) {
    if (isset($_POST['todo_due_date'])) {
        update_post_meta($post_id, '_todo_due_date', sanitize_text_field($_POST['todo_due_date']));
    }
    if (isset($_POST['todo_site_action'])) {
        update_post_meta($post_id, '_todo_site_action', sanitize_text_field($_POST['todo_site_action']));
    }
    if (isset($_POST['todo_doc_title'])) {
        update_post_meta($post_id, '_todo_doc_title', sanitize_text_field($_POST['todo_doc_title']));
    }

    // Assign the to-do item to the current user
    //update_post_meta($post_id, '_todo_assigned_user', get_current_user_id());
}
add_action('save_post', 'save_todo_custom_fields');

// Shortcode to display To-do list on frontend
function to_do_list_shortcode() {
    //ob_start(); // Start output buffering

    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();    
        $site_id = esc_attr(get_post_meta($current_user_id, 'site_id', true));
        $user_data = get_userdata( $current_user_id );
        ?>
        <h2><?php echo __( 'To-do list', 'your-text-domain' );?></h2>
        <div class="ui-widget">
        <fieldset>
            <label for="display-name">Name : </label>
            <input type="text" id="display-name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" disabled />
            <label for="site-title"> Site: </label>
            <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
        
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( 'Job', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Document', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Due date', 'your-text-domain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = retrieve_todo_list_data($site_id);
                if ($query->have_posts()) :
                    $x = 0;
                    while ($query->have_posts()) : $query->the_post();
                        $doc_id = esc_attr(get_post_meta(get_the_ID(), 'doc_id', true));
                        $job_id = esc_attr(get_post_meta(get_the_ID(), 'job_id', true));
                        $job_due = esc_attr(get_post_meta(get_the_ID(), 'job_due', true));
                        $due_date = wp_date( get_option('date_format'), $job_due );
                        ?>
                        <tr id="edit-todo-<?php the_ID();?>" class="todo-list-<?php echo $x;?>">
                            <td style="text-align:center;"><?php echo get_the_title($job_id);?></td>
                            <td><?php echo get_the_title($doc_id);?></td>
                            <td style="text-align:center;"><?php echo $due_date;?></td>
                        </tr>
                        <?php 
                        $x += 1;
                    endwhile;
                    wp_reset_postdata();
                    while ($x<50) {
                        echo '<tr class="todo-list-'.$x.'" style="display:none;"></tr>';
                        $x += 1;
                    }    
                endif;
                ?>
                </tbody>
            </table>
            <?php display_todo_dialog();?>
            <?php display_job_action_list_dialog();?>
        </fieldset>
        </div>

        <?php

    } else {
        user_did_not_login_yet();
    }
    
    //return ob_get_clean(); // Return the buffered content
}
add_shortcode('to-do-list', 'to_do_list_shortcode');

function display_todo_dialog() {
?>
    <div id="todo-dialog" title="To-do dialog" style="display:none;">
        <fieldset>
            <input type="hidden" id="todo-id" />
            <input type="hidden" id="job-id" />
            <input type="hidden" id="action-id" />
            <label for="action-title">Title:</label>
            <input type="text" id="action-title" class="text ui-widget-content ui-corner-all" />
            <label for="action-content">Content:</label>
            <input type="text" id="action-content" class="text ui-widget-content ui-corner-all" />
            <label for="next-job">Next job:</label>
            <select id="next-job" class="text ui-widget-content ui-corner-all" ></select>
            <label for="next-leadtime">Next leadtime:</label>
            <input type="text" id="next-leadtime" class="text ui-widget-content ui-corner-all" />
        </fieldset>
    </div>
<?php
}
    
function retrieve_todo_list_data($job_id=0){
    $args = array(
        'post_type'      => 'todo',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND', // Use 'AND' for an AND relationship between conditions
            array(
                'key'     => 'submit_user',
                'compare' => 'NOT EXISTS', // Exclude posts where submit_user meta key does not exist
            ),
            array(
                'key'     => 'job_id',
                'value'   => '', // You can set a specific value if needed
                'compare' => '=', // Adjust the comparison based on your requirements
            ),
        ),
    );
    
    $args = array(
        'post_type'      => 'todo',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'submit_user',
                'compare' => 'NOT EXISTS', // Exclude posts where submit_user meta key does not exist
            ),
        ),
    );    
    $query = new WP_Query($args);
    return $query;
}

function get_todo_list_data() {
    // Retrieve the data
    $query = retrieve_todo_list_data($_POST['_job_id']);
    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $post_id = (int) get_the_ID();
            $job_id = esc_attr(get_post_meta($post_id, 'job_id', true));
            $job_due = esc_attr(get_post_meta($post_id, 'job_due', true));
            $doc_id = esc_attr(get_post_meta($post_id, 'doc_id', true));
            $_list = array();
            $_list["todo_id"] = $post_id;
            $_list["due_date"] = wp_date( get_option('date_format'), $job_due );
            $_list["job_title"] = get_the_title($job_id);
            $_list["doc_title"] = get_the_title($doc_id);
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_todo_list_data', 'get_todo_list_data' );
add_action( 'wp_ajax_nopriv_get_todo_list_data', 'get_todo_list_data' );

function get_todo_action_list_data() {
    // Retrieve the data
    $todo_id = esc_attr($_POST['_todo_id']);
    $job_id = get_post_meta($todo_id, 'job_id', true);
    $query = retrieve_job_action_list_data($job_id);
    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $next_job_id = esc_attr(get_post_meta(get_the_ID(), 'next_job', true));
            $_list = array();
            $_list["action_id"] = get_the_ID();
            $_list["action_title"] = get_the_title();
            $_list["action_content"] = get_post_field('post_content', get_the_ID());
            $_list["next_job"] = get_the_title($next_job_id);
            $_list["next_leadtime"] = esc_html(get_post_meta(get_the_ID(), 'next_leadtime', true));
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_todo_action_list_data', 'get_todo_action_list_data' );
add_action( 'wp_ajax_nopriv_get_todo_action_list_data', 'get_todo_action_list_data' );

function set_todo_action_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_action_id']) ) {
        // Update To-do
        $todo_id = esc_attr($_POST['_todo_id']);
        $doc_id = get_post_meta($todo_id, 'doc_id', true);
        update_post_meta( $todo_id, 'submit_user', $current_user_id);
        update_post_meta( $todo_id, 'submit_time', time());
        // Insert the To-do list
        $action_id = esc_attr($_POST['_action_id']); // Doc-Actions->ID, Metadata: job_id, action_id
        $job_id = get_post_meta($action_id, 'job_id', true); // Doc-jobs->ID, Metadata: doc_id, job_id
        $next_job = get_post_meta($action_id, 'next_job', true);
        $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
        // Insert the post into the database
        $new_post = array(
            'post_title'    => 'Your post title goes here.',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish', // Publish the post immediately
            'post_author'   => $current_user_id, // Use the user ID of the author
            'post_type'     => 'todo', // Change to your custom post type if needed
        );    
        $post_id = wp_insert_post($new_post);
        update_post_meta( $post_id, 'job_id', esc_attr($next_job));
        update_post_meta( $post_id, 'job_due', time()+esc_attr($next_leadtime));
        update_post_meta( $post_id, 'doc_id', esc_attr($doc_id));

    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_todo_action_dialog_data', 'set_todo_action_dialog_data' );
add_action( 'wp_ajax_nopriv_set_todo_action_dialog_data', 'set_todo_action_dialog_data' );

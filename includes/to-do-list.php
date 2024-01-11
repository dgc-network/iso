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
                        <th></th>
                        <th><?php echo __( 'Due date', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Job', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Document', 'your-text-domain' );?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = retrieve_todo_list_data($site_id);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $due_date = esc_attr(get_post_meta(get_the_ID(), 'job_due', true));
                        $job_id = esc_attr(get_post_meta(get_the_ID(), 'job_id', true));
                        ?>
                        <tr class="todo-item">
                            <td></td>
                            <td style="text-align:center;"><?php echo $due_date;?></td>
                            <td style="text-align:center;"><?php echo get_the_title($job_id);?></td>
                            <td><?php the_title();?></td>
                            <td></td>
                        </tr>
                        <?php 
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                </tbody>
            </table>
        </fieldset>
        </div>

        <?php

    } else {
        user_did_not_login_yet();
    }
    
    //return ob_get_clean(); // Return the buffered content
}
add_shortcode('to-do-list', 'to_do_list_shortcode');

function retrieve_todo_list_data($site_id=0){
    $args = array(
        'post_type'      => 'todo',
        'posts_per_page' => -1,
/*            
        'meta_query'     => array(
            array(
                'key'   => '_todo_assigned_user',
                'value' => $current_user_id,
            ),
        ),
*/            
    );    
    $query = new WP_Query($args);
    return $query;
}
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
        'show_in_menu'       => false, // Set this to false to hide from the admin menu
    );
    register_post_type('todo', $args);
}
add_action('init', 'register_todo_post_type');

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
        
            <table class="ui-widget" style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( 'Todo', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Document', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Due date', 'your-text-domain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = retrieve_todo_list_data();                
                if ($query->have_posts()) :
                    $x = 0;
                    while ($query->have_posts()) : $query->the_post();
                        $doc_id = esc_attr(get_post_meta(get_the_ID(), 'doc_id', true));
                        $job_id = esc_attr(get_post_meta(get_the_ID(), 'job_id', true));
                        $job_due = esc_attr(get_post_meta(get_the_ID(), 'job_due', true));
                        $due_date = wp_date( get_option('date_format'), $job_due );
                        if (is_my_job($job_id)) {
                            ?>
                            <tr class="todo-list-<?php echo $x;?>" id="edit-todo-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo get_the_title($job_id);?></td>
                                <td><?php echo get_the_title($doc_id);?></td>
                                <td style="text-align:center;"><?php echo $due_date;?></td>
                            </tr>
                            <?php 
                            $x += 1;
                        }
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
        </fieldset>
        </div>
        <?php
    } else {
        user_did_not_login_yet();
    }
    
    //return ob_get_clean(); // Return the buffered content
}
add_shortcode('to-do-list', 'to_do_list_shortcode');

function retrieve_todo_list_data(){
    $args = array(
        'post_type'      => 'todo',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND', // Use 'AND' for an AND relationship between conditions
            array(
                'key'     => 'job_due',
                'compare' => 'EXISTS', // Include posts where job_due meta key exists
            ),
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
    $query = retrieve_todo_list_data();
    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $post_id = (int) get_the_ID();
            $job_id = esc_attr(get_post_meta($post_id, 'job_id', true));
            $job_due = esc_attr(get_post_meta($post_id, 'job_due', true));
            $doc_id = esc_attr(get_post_meta($post_id, 'doc_id', true));
            if (is_my_job($job_id)) {
                $_list = array();
                $_list["todo_id"] = $post_id;
                $_list["job_title"] = get_the_title($job_id);
                $_list["doc_title"] = get_the_title($doc_id);
                $_list["due_date"] = wp_date( get_option('date_format'), $job_due );
                array_push($_array, $_list);
            }
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_todo_list_data', 'get_todo_list_data' );
add_action( 'wp_ajax_nopriv_get_todo_list_data', 'get_todo_list_data' );

function display_todo_dialog() {
    ?>
    <div id="todo-dialog" title="To-do dialog" style="display:none;">
        <fieldset>
            <input type="hidden" id="todo-id" />
            <label for="doc-title">Title:</label>
            <input type="text" id="doc-title" class="text ui-widget-content ui-corner-all" disabled />
            <div>
                <div style="display:inline-block;">
                    <label for="doc-number">Doc.#:</label>
                    <input type="text" id="doc-number" class="text ui-widget-content ui-corner-all" disabled />
                </div>
                <div style="display:inline-block; width:25%;">
                    <label for="doc-revision">Revision:</label>
                    <input type="text" id="doc-revision" class="text ui-widget-content ui-corner-all" disabled />
                </div>
            </div>
            <label for="doc-url">URL:</label>
            <a id="doc-url-href">
                <textarea id="doc-url" rows="3" class="text ui-widget-content ui-corner-all" disabled ></textarea>
            </a>
            <label for="doc-url">Workflow:</label>
            <div class="btn-workflow" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;"><span class="dashicons dashicons-networking"></span>Workflow</div>

        </fieldset>
    </div>
    <?php
}
        
function get_todo_dialog_data() {
    $response = array();
    if( isset($_POST['_todo_id']) ) {
        $todo_id = (int)sanitize_text_field($_POST['_todo_id']);
        $doc_id = get_post_meta($todo_id, 'doc_id', true);
        $response["doc_title"] = get_the_title($doc_id);
        $response["doc_number"] = esc_html(get_post_meta($doc_id, 'doc_number', true));
        $response["doc_revision"] = esc_html(get_post_meta($doc_id, 'doc_revision', true));
        $response["doc_url"] = esc_html(get_post_meta($doc_id, 'doc_url', true));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_todo_dialog_data', 'get_todo_dialog_data' );
add_action( 'wp_ajax_nopriv_get_todo_dialog_data', 'get_todo_dialog_data' );

function set_todo_action_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_action_id']) ) {
        // Update To-do
        $todo_id = esc_attr($_POST['_todo_id']);
        update_post_meta( $todo_id, 'submit_user', $current_user_id);
        update_post_meta( $todo_id, 'submit_time', time());

        $action_id = esc_attr($_POST['_action_id']);
        $next_job = get_post_meta($action_id, 'next_job', true);
        $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
        update_post_meta( $next_job, 'job_due', time()+$next_leadtime);
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_todo_action_dialog_data', 'set_todo_action_dialog_data' );
add_action( 'wp_ajax_nopriv_set_todo_action_dialog_data', 'set_todo_action_dialog_data' );

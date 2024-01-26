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
        //'show_in_menu'       => false, // Set this to false to hide from the admin menu
    );
    register_post_type('todo', $args);
}
add_action('init', 'register_todo_post_type');

// Shortcode to display To-do list on frontend
function to_do_list_shortcode() {
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
                        $job_id = esc_attr(get_post_meta(get_the_ID(), 'job_id', true));
                        $doc_id = esc_attr(get_post_meta(get_the_ID(), 'doc_id', true));
                        $todo_due = esc_attr(get_post_meta(get_the_ID(), 'todo_due', true));
                        $due_date = wp_date( get_option('date_format'), $todo_due );
                        if (is_my_job($job_id)) {
                            ?>
                            <tr class="todo-list-<?php echo $x;?>" id="edit-todo-<?php the_ID();?>">
                                <td style="text-align:center;"><?php the_title();?></td>
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
}
add_shortcode('to-do-list', 'to_do_list_shortcode');

function retrieve_todo_list_data(){
    $args = array(
        'post_type'      => 'todo',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND', // Use 'AND' for an AND relationship between conditions
            array(
                'key'     => 'todo_due',
                'compare' => 'EXISTS', // Include posts where todo_due meta key exists
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
            $todo_id = (int) get_the_ID();
            $job_id = esc_attr(get_post_meta($todo_id, 'job_id', true));
            $doc_id = esc_attr(get_post_meta($todo_id, 'doc_id', true));
            $todo_due = esc_attr(get_post_meta($todo_id, 'todo_due', true));
            if (is_my_job($job_id)) { // Another condition to grab the data
                $_list = array();
                $_list["todo_id"] = $todo_id;
                $_list["todo_title"] = get_the_title();
                $_list["doc_title"] = get_the_title($doc_id);
                $_list["due_date"] = wp_date( get_option('date_format'), $todo_due );
                array_push($_array, $_list);
            }
        endwhile;
        wp_reset_postdata();
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
            <input type="hidden" id="job-id" />
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
            <textarea id="btn-doc-url" rows="3" class="text ui-widget-content ui-corner-all" ></textarea>
            <label for="btn-workflow">Workflow:</label>
            <div id="btn-workflow" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;"><span class="dashicons dashicons-networking"></span> Action list</div>
        </fieldset>
    </div>
    <?php display_todo_action_list();?>
    <?php
}
        
function get_todo_dialog_data() {
    $response = array();
    if( isset($_POST['_todo_id']) ) {
        $todo_id = (int)sanitize_text_field($_POST['_todo_id']);
        $doc_id = esc_attr(get_post_meta($todo_id, 'doc_id', true));
        $response["doc_title"] = get_the_title($doc_id);
        $response["doc_number"] = esc_html(get_post_meta($doc_id, 'doc_number', true));
        $response["doc_revision"] = esc_html(get_post_meta($doc_id, 'doc_revision', true));
        $response["doc_url"] = esc_html(get_post_meta($doc_id, 'doc_url', true));
        $response["job_id"] = esc_attr(get_post_meta($todo_id, 'job_id', true));
        $response["site_id"] = esc_attr(get_post_meta($doc_id, 'site_id', true));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_todo_dialog_data', 'get_todo_dialog_data' );
add_action( 'wp_ajax_nopriv_get_todo_dialog_data', 'get_todo_dialog_data' );

function get_todo_dialog_buttons_data() {
    // Retrieve the data
    $todo_id = esc_attr($_POST['_todo_id']);
    $query = retrieve_todo_action_list_data($todo_id);
    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $next_job = esc_attr(get_post_meta(get_the_ID(), 'next_job', true));
            if (($next_job!=0)||($next_job!='')){
                $_list = array();
                $_list["action_id"] = get_the_ID();
                $_list["action_title"] = get_the_title();
                $_list["action_content"] = get_post_field('post_content', get_the_ID());
                $_list["next_job"] = get_the_title($next_job);
                $_list["next_leadtime"] = esc_attr(get_post_meta(get_the_ID(), 'next_leadtime', true));
                array_push($_array, $_list);    
            }
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_todo_dialog_buttons_data', 'get_todo_dialog_buttons_data' );
add_action( 'wp_ajax_nopriv_get_todo_dialog_buttons_data', 'get_todo_dialog_buttons_data' );

function set_todo_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_action_id']) ) {
        // Update To-do
        $todo_id = sanitize_text_field($_POST['_todo_id']);
        $doc_id = esc_attr(get_post_meta($todo_id, 'doc_id', true));
        $start_job = esc_attr(get_post_meta($doc_id, 'start_job', true));
        $action_id = sanitize_text_field($_POST['_action_id']);
        set_next_job_and_actions($start_job, $action_id);
        update_post_meta( $todo_id, 'submit_user', $current_user_id);
        update_post_meta( $todo_id, 'submit_time', time());
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_todo_dialog_data', 'set_todo_dialog_data' );
add_action( 'wp_ajax_nopriv_set_todo_dialog_data', 'set_todo_dialog_data' );

function get_users_by_job_id($job_id) {
    // Define the meta query
    $meta_query = array(
        'relation' => 'AND', // Ensure both conditions are met
        array(
            'key'     => 'my_job_ids',
            'value'   => $job_id,
            'compare' => 'LIKE', // Check if $job_id exists in the array
        ),
    );

    // Set up the user query arguments
    $args = array(
        'meta_query' => $meta_query,
    );

    // Create a new WP_User_Query
    $user_query = new WP_User_Query($args);

    // Get the results
    $users = $user_query->get_results();

    // Return the list of users
    return $users;
}

function send_flex_message_with_button($user, $message_text='', $link_uri='') {
    // Flex Message JSON structure with a button
    $line_bot_api = new line_bot_api();
    $flexMessage = [
        'type' => 'flex',
        //'altText' => 'This is a Flex Message with a Button',
        'altText' => $message_text,
        'contents' => [
            'type' => 'bubble',
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => 'Hello, '.$user->display_name,
                        'size' => 'lg',
                        'weight' => 'bold',
                    ],
                    [
                        'type' => 'text',
                        'text' => $message_text.' Please click the button below to proceed the process.',
                        'wrap' => true,
                    ],
                ],
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'button',
                        'action' => [
                            'type' => 'uri',
                            'label' => 'Click me!',
                            'uri' => $link_uri, // Replace with your desired URI
                        ],
                    ],
                ],
            ],
        ],
    ];
    
    $line_bot_api->pushMessage([
        'to' => get_user_meta($user->ID, 'line_user_id', TRUE),
        'messages' => [$flexMessage],
    ]);
}

function notice_the_persons_in_charge($todo_id=0) {
    // Notice the persons in charge the job
    $job_title = get_the_title($todo_id);
    $doc_title = get_post_field('post_content', $todo_id);
    $message_text='You have a new todo. '.$job_title.':'.$doc_title.'.';
    $link_uri = home_url().'/to-do-list/?_id='.$todo_id;
    $job_id = esc_attr(get_post_meta($todo_id, 'job_id', true));
    $users = get_users_by_job_id($job_id);
    foreach ($users as $user) {
        // Flex Message JSON structure with a button
        send_flex_message_with_button($user, $message_text, $link_uri);
    }    
}

function get_users_in_site($site_id) {
    // Define the meta query
    $meta_query = array(
        'relation' => 'AND', // Ensure both conditions are met
        array(
            'key'     => 'my_job_ids',
            'value'   => $job_id,
            'compare' => 'LIKE', // Check if $job_id exists in the array
        ),
    );

    // Set up the user query arguments
    $args = array(
        //'meta_query' => $meta_query,
        'meta_query'     => array(
            array(
                'key'   => 'site_id',
                'value' => $site_id,
            ),
        ),

    );

    // Create a new WP_User_Query
    $user_query = new WP_User_Query($args);

    // Get the results
    $users = $user_query->get_results();

    // Return the list of users
    return $users;
}

function notice_the_persons_in_site($doc_id=0) {
    // Notice the persons in site
    $doc_title = get_the_title($doc_id);
    $doc_date = esc_attr(get_post_meta($doc_id, 'doc_date', true));
    $doc_url = esc_html(get_post_meta($doc_id, 'doc_url', true));
    $site_id = esc_attr(get_post_meta($doc_id, 'site_id', true));
    $message_text=$doc_title.' has been published on '.wp_date( get_option('date_format'), $doc_date ).'.';
    //$link_uri = home_url().'/to-do-list/?_id='.$todo_id;
    $users = get_users_in_site($site_id);
    foreach ($users as $user) {
        // Flex Message JSON structure with a button
        send_flex_message_with_button($user, $message_text, $doc_url);
    }    
}

function set_next_job_and_actions($next_job=0, $action_id=0, $doc_id=0, $next_leadtime=0) {
    if ($next_job==0) return;
    if ($action_id>0){
        $todo_id = esc_attr(get_post_meta($action_id, 'todo_id', true));
        $doc_id = esc_attr(get_post_meta($todo_id, 'doc_id', true));
        $next_job = esc_attr(get_post_meta($action_id, 'next_job', true));
        $next_leadtime = esc_attr(get_post_meta($action_id, 'next_leadtime', true));
    }
    if ($next_job==-1) {
        $data = array(
            'ID'         => $doc_id,
            'meta_input' => array(
                'doc_date'   => time()+$next_leadtime,
            )
        );
        wp_update_post( $data );
        
        // Notice the persons in charge the job
        notice_the_persons_in_site($doc_id);

    } else {
        $todo_title = get_the_title($next_job);
        $doc_title = get_the_title($doc_id);
        // Insert the To-do list for next_job
        $current_user_id = get_current_user_id();
        $new_post = array(
            'post_title'    => $todo_title,
            'post_content'  => $doc_title,
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'todo',
        );    
        $new_todo_id = wp_insert_post($new_post);
        update_post_meta( $new_todo_id, 'job_id', $next_job);
        update_post_meta( $new_todo_id, 'doc_id', $doc_id);
        update_post_meta( $new_todo_id, 'todo_due', time()+$next_leadtime);

        // Notice the persons in charge the job
        notice_the_persons_in_charge($new_todo_id);

        // Insert the Action list for next_job
        $query = retrieve_job_action_list_data($next_job);
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $new_post = array(
                    'post_title'    => get_the_title(),
                    'post_content'  => get_post_field('post_content', get_the_ID()),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'action',
                );    
                $new_action_id = wp_insert_post($new_post);
                update_post_meta( $new_action_id, 'todo_id', $new_todo_id);
                $new_next_job = esc_attr(get_post_meta(get_the_ID(), 'next_job', true));
                update_post_meta( $new_action_id, 'next_job', $new_next_job);
                $new_next_leadtime = esc_attr(get_post_meta(get_the_ID(), 'next_leadtime', true));
                update_post_meta( $new_action_id, 'next_leadtime', $new_next_leadtime);
            endwhile;
            wp_reset_postdata();
        }
    }
}

function display_todo_action_list() {
    ?>
    <div id="todo-action-list-dialog" title="Action list" style="display:none;">
        <table style="width:100%;">
            <thead>
                <tr>
                    <th><?php echo __( 'Action', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Next job', 'your-text-domain' );?></th>
                    <th><?php echo __( 'LeadTime', 'your-text-domain' );?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $x = 0;
                while ($x<50) {
                    echo '<tr class="todo-action-list-'.$x.'" style="display:none;"></tr>';
                    $x += 1;
                }
                ?>
            </tbody>
        </table>
        <div id="btn-new-todo-action" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
    </div>
    <?php display_todo_action_dialog();?>
    <?php
}

function retrieve_todo_action_list_data($_id=0) {
    $args = array(
        'post_type'      => 'action',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'todo_id',
                'value' => $_id,
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query;
}

function get_todo_action_list_data() {
    // Retrieve the documents data
    $query = retrieve_todo_action_list_data($_POST['_todo_id']);
    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $next_job = esc_attr(get_post_meta(get_the_ID(), 'next_job', true));
            $_list = array();
            $_list["action_id"] = get_the_ID();
            $_list["action_title"] = get_the_title();
            $_list["action_content"] = get_post_field('post_content', get_the_ID());
            if ($next_job==-1){
                $_list["next_job"] = __( '發行', 'your-text-domain' );
            } else {
                $_list["next_job"] = get_the_title($next_job);
            }
            $_list["next_leadtime"] = esc_html(get_post_meta(get_the_ID(), 'next_leadtime', true));
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_todo_action_list_data', 'get_todo_action_list_data' );
add_action( 'wp_ajax_nopriv_get_todo_action_list_data', 'get_todo_action_list_data' );

function display_todo_action_dialog(){
    ?>
    <div id="todo-action-dialog" title="Action dialog" style="display:none;">
        <fieldset>
            <input type="hidden" id="site-id" />
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

function set_todo_action_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_action_id']) ) {
        // Update the post into the database
        $data = array(
            'ID'         => $_POST['_action_id'],
            'meta_input' => array(
                'action_title'   => $_POST['_action_title'],
                'action_content' => $_POST['_action_content'],
                'next_job'       => $_POST['_next_job'],
                'next_leadtime'  => $_POST['_next_leadtime'],
            )
        );
        wp_update_post( $data );
    } else {
        // Insert the post into the database
        $new_post = array(
            'post_title'    => 'New action',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'action',
        );    
        $post_id = wp_insert_post($new_post);
        update_post_meta( $post_id, 'todo_id', sanitize_text_field($_POST['_todo_id']));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_todo_action_dialog_data', 'set_todo_action_dialog_data' );
add_action( 'wp_ajax_nopriv_set_todo_action_dialog_data', 'set_todo_action_dialog_data' );

function del_todo_action_dialog_data() {
    // Delete the post
    $result = wp_delete_post($_POST['_action_id'], true);
    wp_send_json($result);
}
add_action( 'wp_ajax_del_todo_action_dialog_data', 'del_todo_action_dialog_data' );
add_action( 'wp_ajax_nopriv_del_todo_action_dialog_data', 'del_todo_action_dialog_data' );


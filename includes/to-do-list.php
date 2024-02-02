<?php
if (!defined('ABSPATH')) {
    exit;
}

class CustomToDoList
{
    public function __construct()
    {
        add_action('init', array($this, 'registerTodoPostType'));
        add_action('init', array($this, 'registerActionPostType'));
        add_shortcode('to-do-list', array($this, 'toDoListShortcode'));
        add_action('wp_ajax_get_todo_list_data', array($this, 'getToDoListData'));
        add_action('wp_ajax_nopriv_get_todo_list_data', array($this, 'getToDoListData'));
        // Add other actions and hooks here...
    }

    public function registerTodoPostType()
    {
        // Todo post type registration code...
    }

    public function registerActionPostType()
    {
        // Action post type registration code...
    }

    public function toDoListShortcode()
    {
        // Shortcode implementation code...
    }

    public function retrieveToDoListData()
    {
        // Retrieve Todo list data...
    }

    public function getToDoListData()
    {
        // Get Todo list data...
    }

    // Add other methods and functions as needed...
}

// Instantiate the class
$customToDoList = new CustomToDoList();
?>
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

// Register action post type
function register_action_post_type() {
    $args = array(
        'public'        => true,
        'rewrite'       => array('slug' => 'actions'),
        'supports'      => array( 'title', 'editor', 'custom-fields' ),
        'has_archive'   => true,
        'show_in_menu'  => false, // Set this to false to hide from the admin menu
    );
    register_post_type( 'action', $args );
}
add_action('init', 'register_action_post_type');

// Shortcode to display To-do list on frontend
function to_do_list_shortcode() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();    
        $site_id = esc_attr(get_post_meta($current_user_id, 'site_id', true));
        $user_data = get_userdata( $current_user_id );
        ?>
        <div class="ui-widget" id="result-container">
        <h2><?php echo __( 'To-do list', 'your-text-domain' );?></h2>
        <fieldset>
            <div id="todo-setting-div" style="display:none">
            <fieldset>
                <label for="display-name">Name : </label>
                <input type="text" id="display-name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="site-title"> Site: </label>
                <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
            </fieldset>
            </div>
        
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <select id="select-site-job"><?php echo select_site_job_option_data($_GET['_job'],$site_id);?></select>
                </div>
                <div style="text-align: right">
                    <input type="text" id="search-todo" style="display:inline" placeholder="Search..." />
                    <span id="btn-todo-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic"></span>
                </div>
            </div>

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
                    $todo_due = get_post_meta(get_the_ID(), 'todo_due', true);
                    $due_date = wp_date(get_option('date_format'), $todo_due);

                    if (is_my_job($job_id)) { // Another condition to filter the data
                        ?>
                        <tr class="todo-list-<?php echo $x; ?>" id="edit-todo-<?php the_ID(); ?>">
                            <td style="text-align:center;"><?php the_title(); ?></td>
                            <td><?php echo get_the_title($doc_id); ?></td>
                            <?php if ($todo_due < time()) { ?>
                                <td style="text-align:center; color:red;">
                            <?php } else { ?>
                                <td style="text-align:center;"><?php } ?>
                            <?php echo $due_date.$todo_due;?></td>
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
        </fieldset>
        <?php //display_doc_todo_dialog();?>
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
        'posts_per_page' => 30,
        'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
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
            if (is_my_job($job_id)) { // Another condition to filter the data
                $_list = array();
                $_list["todo_id"] = $todo_id;
                $_list["todo_title"] = get_the_title();
                $_list["doc_title"] = get_the_title($doc_id);
                $_list["due_date"] = wp_date( get_option('date_format'), $todo_due );
                $_list["due_color"] = (($todo_due<time()) ? 1 : 0);
                array_push($_array, $_list);
            }
        endwhile;
        wp_reset_postdata();
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_todo_list_data', 'get_todo_list_data' );
add_action( 'wp_ajax_nopriv_get_todo_list_data', 'get_todo_list_data' );

function open_todo_dialog_and_buttons() {
    // Check if the action has been set
    if (isset($_POST['action']) && $_POST['action'] === 'open_todo_dialog_and_buttons') {
        $todo_id = (int)sanitize_text_field($_POST['_todo_id']);
        $doc_id = esc_attr(get_post_meta($todo_id, 'doc_id', true));
        $doc_shortcode = esc_attr(get_post_meta($doc_id, 'doc_shortcode', true));
        $params = array();
        if ($doc_shortcode) {
            $result = call_user_func_array($doc_shortcode, $params);
        } else {
            array_push($params,$todo_id,$doc_id);
            $result = call_user_func_array('display_doc_todo_dialog', $params);
        }
        echo $result;
        wp_die();
    } else {
        // Handle invalid AJAX request
        echo 'Invalid AJAX request!';
        wp_die();
    }
}
add_action('wp_ajax_open_todo_dialog_and_buttons', 'open_todo_dialog_and_buttons');
add_action('wp_ajax_nopriv_open_todo_dialog_and_buttons', 'open_todo_dialog_and_buttons');

function translate_custom_strings($original_string) {
    // Define translations for specific strings
    $translations = array(
        'doc-status' => '文件狀態',
        'doc-title' => '文件名稱',
        'doc_number' => '文件編號',
        'doc_revision' => '文件版本',
        'doc_url' => '文件網址',
        'start_job' => '起始職務',
        'start_leadtime' => '前置時間',
        'doc_category' => '文件類別',
        'site_id' => '單位',
        // Add more translations as needed
    );
    // Check if there's a translation for the given string
    if (isset($translations[$original_string])) {
        return $translations[$original_string];
    }
    // If no translation is found, return the original string
    return $original_string;
}

function display_doc_todo_dialog($todo_id, $post_id) {
    // Get all existing meta data for the specified post ID
    $all_meta = get_post_meta($post_id);
    // Output or manipulate the meta data as needed
    echo '<h2>To-do</h2>';
    echo '<fieldset>';
    echo '<label for="doc-title">'.translate_custom_strings("doc-title").'</label>';
    echo '<input type="text" id="doc-title" value="'.get_the_title($post_id).'" class="text ui-widget-content ui-corner-all" disabled />';

    foreach ($all_meta as $key => $values) {
        if ($key!='site_id') 
        if ($key!='start_job') 
        if ($key!='start_leadtime') 
        if ($key!='todo_status') 
        foreach ($values as $value) {
            echo '<label for="'.$key.'">'.translate_custom_strings($key).'</label>';
            switch (true) {
                case strpos($key, 'url'):
                    echo '<a href="' . $value . '"><textarea id="' . $key . '" class="button" rows="3" style="width:100%;">' . $value . '</textarea></a>';
                    break;
        
                case strpos($key, 'category'):
                    echo '<select id="' . $key . '" class="text ui-widget-content ui-corner-all" disabled>' . select_doc_category_option_data($value) . '</select>';
                    break;
        
                default:
                    echo '<input type="text" id="' . $key . '" value="' . $value . '" class="text ui-widget-content ui-corner-all" disabled />';
                    break;
            }
        }
    }
    echo '<label for="btn-action-list">'.translate_custom_strings("doc-status").'</label>';
    echo '<input type="button" id="btn-action-list" value="'.get_the_title($todo_id).'" style="text-align:center; background:antiquewhite; color:blue; font-size:smaller;" class="text ui-widget-content ui-corner-all" />';
    //display_todo_action_buttons($todo_id);
    echo '<hr>';
    $query = retrieve_todo_action_list_data($todo_id);
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            echo '<input type="button" id="todo-dialog-button-'.get_the_ID().'" value="'.get_the_title().'" style="margin:5px;" />';
        endwhile;
        wp_reset_postdata();
    }
    echo '</fieldset>';
    display_todo_action_list();
}
/*        
function display_todo_action_buttons($todo_id) {
}

function get_todo_dialog_data() {
    $response = array();
    $todo_id = (int)sanitize_text_field($_POST['_todo_id']);
    $doc_id = esc_attr(get_post_meta($todo_id, 'doc_id', true));
    $response["doc_title"] = esc_html(get_the_title($doc_id));
    $response["doc_number"] = esc_html(get_post_meta($doc_id, 'doc_number', true));
    $response["doc_revision"] = esc_html(get_post_meta($doc_id, 'doc_revision', true));
    $response["doc_url"] = esc_html(get_post_meta($doc_id, 'doc_url', true));
    $response["job_id"] = esc_attr(get_post_meta($todo_id, 'job_id', true));
    $response["site_id"] = esc_attr(get_post_meta($doc_id, 'site_id', true));
    wp_send_json($response);
}
add_action( 'wp_ajax_get_todo_dialog_data', 'get_todo_dialog_data' );
add_action( 'wp_ajax_nopriv_get_todo_dialog_data', 'get_todo_dialog_data' );
*/
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
        wp_reset_postdata();
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_todo_dialog_buttons_data', 'get_todo_dialog_buttons_data' );
add_action( 'wp_ajax_nopriv_get_todo_dialog_buttons_data', 'get_todo_dialog_buttons_data' );

function set_todo_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_action_id']) ) {
        // action button is clicked, current todo update
        $action_id = sanitize_text_field($_POST['_action_id']);
        $todo_id = esc_attr(get_post_meta($action_id, 'todo_id', true));
        $doc_id = esc_attr(get_post_meta($todo_id, 'doc_id', true));
        $start_job = esc_attr(get_post_meta($doc_id, 'start_job', true));
        set_next_job_and_actions($start_job, $action_id);
        update_post_meta( $todo_id, 'submit_user', $current_user_id);
        update_post_meta( $todo_id, 'submit_action', $action_id);
        update_post_meta( $todo_id, 'submit_time', time());
    } else {
        // start_job on change, todo insert or update
        $job_id = sanitize_text_field($_POST['_job_id']);
        $doc_id = sanitize_text_field($_POST['_doc_id']);
        if ($job_id==0) wp_send_json($response);

        if( isset($_POST['_todo_id']) ) {
            // Insert To-do
            $todo_id = sanitize_text_field($_POST['_todo_id']);        
            update_post_meta( $todo_id, 'job_id', $job_id);
            update_post_meta( $doc_id, 'start_job', $job_id);
            $response = $todo_id;
        } else {
            $todo_title = get_the_title($job_id);
            $todo_content = get_the_title($doc_id);
            // Insert To-do
            $new_post = array(
                'post_title'    => $todo_title,
                'post_content'  => $todo_content,
                'post_status'   => 'publish',
                'post_author'   => $current_user_id,
                'post_type'     => 'todo',
            );    
            $new_todo_id = wp_insert_post($new_post);
            update_post_meta( $new_todo_id, 'job_id', $job_id);
            update_post_meta( $new_todo_id, 'doc_id', $doc_id);
            update_post_meta( $doc_id, 'start_job', $job_id);
            update_post_meta( $doc_id, 'todo_status', $new_todo_id);

            // Insert the Action list for start_job
            $query = retrieve_job_action_list_data($job_id);
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

            $response = $new_todo_id;
        }

    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_todo_dialog_data', 'set_todo_dialog_data' );
add_action( 'wp_ajax_nopriv_set_todo_dialog_data', 'set_todo_dialog_data' );

function set_next_job_and_actions($next_job=0, $action_id=0, $doc_id=0, $next_leadtime=0) {
    if ($next_job==0) return;
    if ($action_id>0){
        $todo_id = esc_attr(get_post_meta($action_id, 'todo_id', true));
        $doc_id = esc_attr(get_post_meta($todo_id, 'doc_id', true));
        $next_job = esc_attr(get_post_meta($action_id, 'next_job', true));
        $next_leadtime = esc_attr(get_post_meta($action_id, 'next_leadtime', true));
    }
    $todo_title = get_the_title($next_job);
    $doc_title = get_the_title($doc_id);

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
        $todo_title = __( '發行', 'your-text-domain' );
    }

    if ($next_job==-2) {
        $data = array(
            'ID'         => $doc_id,
            'meta_input' => array(
                'doc_date'   => time()+$next_leadtime,
            )
        );
        wp_update_post( $data );
        
        // Notice the persons in charge the job
        notice_the_persons_in_site($doc_id);
        $todo_title = __( '廢止', 'your-text-domain' );
    }

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

    if ($next_job==-1) {
        update_post_meta( $new_todo_id, 'submit_user', $current_user_id);
        update_post_meta( $new_todo_id, 'submit_time', time());
    }

    if ($next_job>0) {
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

// Flex Message JSON structure with a button
function send_flex_message_with_button_link($user, $message_text='', $link_uri='') {
    $line_bot_api = new line_bot_api();
    $flexMessage = [
        'type' => 'flex',
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
                            'uri' => $link_uri,
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

function get_users_by_job_id($job_id=0) {
    // Set up the user query arguments
    $args = array(
        'meta_query'     => array(
            array(
                'key'     => 'my_job_ids',
                'value'   => $job_id,
                'compare' => 'LIKE', // Check if $job_id exists in the array
            ),
        ),
    );
    $query = new WP_User_Query($args);
    $users = $query->get_results();
    return $users;
}

// Notice the persons in charge the job
function notice_the_persons_in_charge($todo_id=0) {
    $job_title = get_the_title($todo_id);
    $doc_title = get_post_field('post_content', $todo_id);
    $todo_due = esc_attr(get_post_meta($todo_id, 'todo_due', true));
    $due_date = wp_date( get_option('date_format'), $todo_due );
    $message_text='You have to work on the '.$job_title.':'.$doc_title.' before '.$due_date.'.';
    $link_uri = home_url().'/to-do-list/?_id='.$todo_id;
    $job_id = esc_attr(get_post_meta($todo_id, 'job_id', true));
    $users = get_users_by_job_id($job_id);
    foreach ($users as $user) {
        send_flex_message_with_button_link($user, $message_text, $link_uri);
    }    
}

function get_users_in_site($site_id=0) {
    // Set up the user query arguments
    $args = array(
        'meta_query'     => array(
            array(
                'key'   => 'site_id',
                'value' => $site_id,
            ),
        ),
    );
    $query = new WP_User_Query($args);
    $users = $query->get_results();
    return $users;
}

// Notice the persons in site
function notice_the_persons_in_site($doc_id=0) {
    $doc_title = get_the_title($doc_id);
    $doc_date = esc_attr(get_post_meta($doc_id, 'doc_date', true));
    $doc_url = esc_html(get_post_meta($doc_id, 'doc_url', true));
    $site_id = esc_attr(get_post_meta($doc_id, 'site_id', true));
    $message_text=$doc_title.' has been published on '.wp_date( get_option('date_format'), $doc_date ).'.';
    //$link_uri = home_url().'/to-do-list/?_id='.$todo_id;
    $users = get_users_in_site($site_id);
    foreach ($users as $user) {
        send_flex_message_with_button_link($user, $message_text, $doc_url);
    }    
}

function display_todo_action_list() {
    ?>
    <div id="todo-action-list-dialog" title="Action list" style="display:none;">
    <fieldset>
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
    </fieldset>
    </div>
    <?php display_todo_action_dialog();?>
    <?php
}

function retrieve_todo_action_list_data($todo_id=0) {
    $args = array(
        'post_type'      => 'action',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'todo_id',
                'value' => $todo_id,
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
            $_list["next_job"] = get_the_title($next_job);
            if ($next_job==-1) $_list["next_job"] = __( '發行', 'your-text-domain' );
            if ($next_job==-2) $_list["next_job"] = __( '廢止', 'your-text-domain' );
            $_list["next_leadtime"] = esc_html(get_post_meta(get_the_ID(), 'next_leadtime', true));
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata();
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

function get_todo_action_dialog_data() {
    $response = array();
    if( isset($_POST['_action_id']) ) {
        $action_id = (int)sanitize_text_field($_POST['_action_id']);
        $todo_id = esc_attr(get_post_meta($action_id, 'todo_id', true));
        $doc_id = esc_attr(get_post_meta($todo_id, 'doc_id', true));
        $site_id = esc_attr(get_post_meta($doc_id, 'site_id', true));
        $next_job = esc_attr(get_post_meta($action_id, 'next_job', true));
        $response["action_title"] = get_the_title($action_id);
        $response["action_content"] = get_post_field('post_content', $action_id);
        $response["next_job"] = select_site_job_option_data($next_job, $site_id);
        $response["next_leadtime"] = esc_html(get_post_meta($action_id, 'next_leadtime', true));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_todo_action_dialog_data', 'get_todo_action_dialog_data' );
add_action( 'wp_ajax_nopriv_get_todo_action_dialog_data', 'get_todo_action_dialog_data' );

function set_todo_action_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_action_id']) ) {
        // Update the post into the database
        $data = array(
            'ID'         => $_POST['_action_id'],
            'post_title' => $_POST['_action_title'],
            'post_content' => $_POST['_action_content'],
            'meta_input' => array(
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


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

    // Assign the to-do item to the current user
    update_post_meta($post_id, '_todo_assigned_user', get_current_user_id());
}
add_action('save_post', 'save_todo_custom_fields');

// Shortcode to display To-do list on frontend
function to_do_list_shortcode() {
    ob_start(); // Start output buffering

    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();
    
        $args = array(
            'post_type'      => 'todo',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'   => '_todo_assigned_user',
                    'value' => $current_user_id,
                ),
            ),
        );
    
        $todos = new WP_Query($args);
    
        if ($todos->have_posts()) :
            while ($todos->have_posts()) : $todos->the_post();
                $due_date = get_post_meta(get_the_ID(), '_todo_due_date', true);
                ?>
                <div class="todo-item">
                    <h2><?php the_title(); ?></h2>
                    <p>Due Date: <?php echo esc_html($due_date); ?></p>
                    <div class="todo-content">
                        <?php the_content(); ?>
                    </div>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo 'No to-do items found.';
        endif;
    } else {
        // Display a message or redirect to the login/registration page
        $one_time_password = random_int(100000, 999999);
        update_option('_one_time_password', $one_time_password);

        echo '<div style="text-align:center;">';
        echo '感謝您使用我們的系統<br>';
        echo 'Please log in or register to view your to-do list.';
        echo '請利用手機<i class="fa-solid fa-mobile-screen"></i>按'.'<a href="'.get_option('_line_account').'">這裡</a>, 加入我們的Line官方帳號,<br>';
        echo '並請在聊天室中, 輸入六位數字:<h4>'.get_option('_one_time_password').'</h4>完成註冊/登入作業<br>';
        echo '</div>';

    }
    
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('to-do-list', 'to_do_list_shortcode');

function init_webhook_events() {
    global $wpdb;
    $line_bot_api = new line_bot_api();
    $open_ai_api = new open_ai_api();
    //$curtain_agents = new curtain_agents();

    foreach ((array)$line_bot_api->parseEvents() as $event) {

        // Start the User Login/Registration process if got the one time password
        if ($event['message']['text']==get_option('_one_time_password')) {
            $link_uri = home_url().'/?_id='.$event['source']['userId'];

            if (file_exists(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json')) {
                $see_more = file_get_contents(plugin_dir_path( __DIR__ ).'assets/templates/see_more.json');
                $see_more = json_decode($see_more, true);
                $see_more["body"]["contents"][0]["action"]["label"] = 'User Login/Registration';
                $see_more["body"]["contents"][0]["action"]["uri"] = $link_uri;
                $line_bot_api->replyMessage([
                    'replyToken' => $event['replyToken'],
                    'messages' => [
                        [
                            "type" => "flex",
                            "altText" => 'Welcome message',
                            'contents' => $see_more
                        ]
                    ]
                ]);
            } else {
                $line_bot_api->replyMessage([
                    'replyToken' => $event['replyToken'],
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $link_uri
                        ]                                                                    
                    ]
                ]);    
            }
        }

        switch ($event['type']) {
            case 'message':
                $message = $event['message'];
                switch ($message['type']) {
                    case 'text':
                        // Open-AI auto reply
                        $param=array();
                        $param["messages"][0]["content"]=$message['text'];
                        $response = $open_ai_api->createChatCompletion($param);
                        $line_bot_api->replyMessage([
                            'replyToken' => $event['replyToken'],
                            'messages' => [
                                [
                                    'type' => 'text',
                                    'text' => $response
                                ]                                                                    
                            ]
                        ]);
                        break;
                    default:
                        error_log('Unsupported message type: ' . $message['type']);
                        break;
                }
                break;
            default:
                error_log('Unsupported event type: ' . $event['type']);
                break;
        }
    }
}
add_action( 'init', 'init_webhook_events' );


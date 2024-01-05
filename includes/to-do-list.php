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
        $query = new WP_Query($args);
    
        if ($query->have_posts()) :?>
            <h2><?php echo __( 'To-do list', 'your-text-domain' );?></h2>
            <table class="to-do-list" style="width:100%;">
                <tbody>
            <?php
            while ($query->have_posts()) : $query->the_post();
                $due_date = get_post_meta(get_the_ID(), '_todo_due_date', true);
                $todo_action = get_post_meta(get_the_ID(), '_todo_site_action', true);
                $doc_title = get_post_meta(get_the_ID(), '_todo_doc_title', true);
                ?>
                    <tr class="todo-item">
                        <td style="text-align:center;"><?php echo esc_html($due_date);?></td>
                        <td style="text-align:center;"><?php echo esc_html($todo_action);?></td>
                        <td><?php echo esc_html($doc_title);?></td>
                    </tr>
                <?php 
            endwhile;
            ?>
                </tbody>
            </table>
            <?php
            wp_reset_postdata();
        else :
            echo '<h2>'.__( 'No to-do items found', 'your-text-domain' ).'</h2>';
        endif;
    } else {
        // Did not login system yet
        if( isset($_GET['_id']) ) {
            // Using Line User ID to register and login into the system
            $array = get_users( array( 'meta_value' => $_GET['_id'] ));
            if (empty($array)) {
                $user_id = wp_insert_user( array(
                    'user_login' => $_GET['_id'],
                    'user_pass' => $_GET['_id'],
                ));
                $user = get_user_by( 'ID', $user_id );
                add_user_meta( $user_id, 'line_user_id', $_GET['_id']);
                // To-Do: add_user_meta( $user_id, 'wallet_address', $_GET['_wallet_address']);
            }

            $link_uri = home_url().'/?_id='.$_GET['_id'].'&_agent_no='.$_GET['_agent_no'];

            echo '<div style="text-align:center;">';
            echo '<p>This is an automated process that helps you register for the system. ';
            echo 'Please click the Submit button below to complete your registration.</p>';
            echo '<form action="'.esc_url( site_url( 'wp-login.php', 'login_post' ) ).'" method="post" style="display:inline-block;">';
            echo '<fieldset>';
            echo '<input type="hidden" name="log" value="'. $_GET['_id'] .'" />';
            echo '<input type="hidden" name="pwd" value="'. $_GET['_id'] .'" />';
            echo '<input type="hidden" name="rememberme" value="foreverchecked" />';
            echo '<input type="hidden" name="redirect_to" value="'.esc_url( $link_uri ).'" />';
            echo '<input type="submit" name="wp-submit" class="button button-primary" value="Submit" />';
            echo '</fieldset>';
            echo '</form>';
            echo '</div>';

        } else {
            // Display a message or redirect to the login/registration page
            $one_time_password = random_int(100000, 999999);
            update_option('_one_time_password', $one_time_password);
    
            echo '<div style="text-align:center;">';
            echo '感謝您使用我們的系統<br>';
            echo 'Please log in or register to view your to-do list.<br>';
            echo '請利用手機<span class="dashicons dashicons-smartphone"></span>按'.'<a href="'.get_option('_line_account').'">這裡</a>, 加入我們的Line官方帳號,<br>';
            echo '並請在聊天室中, 輸入六位數字:<h4>'.get_option('_one_time_password').'</h4>完成註冊/登入作業<br>';
            echo '</div>';
    
        }


    }
    
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('to-do-list', 'to_do_list_shortcode');


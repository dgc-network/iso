<?php
if (!defined('ABSPATH')) {
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
    $labels = array(
        'menu_name'          => _x('Actions', 'admin menu', 'textdomain'),
    );
    $args = array(
        'labels'             => $labels,
        'public'        => true,
        'rewrite'       => array('slug' => 'actions'),
        'supports'      => array( 'title', 'editor', 'custom-fields' ),
        'has_archive'   => true,
        'show_in_menu'  => false, // Set this to false to hide from the admin menu
    );
    register_post_type( 'action', $args );
}
add_action('init', 'register_action_post_type');

function retrieve_signature_record_data(){
    $args = array(
        'post_type'      => 'todo',
        'posts_per_page' => 30,
        'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => 'submit_action',
                'compare' => 'EXISTS',
            ),
            array(
                'key'     => 'submit_user',
                'compare' => 'EXISTS',
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query;
}

function display_signature_record() {
    $current_user_id = get_current_user_id();
    $site_id = get_post_meta($current_user_id, 'site_id', true);
    ?>
    <div class="ui-widget" id="result-container">
    <h2><?php echo __( 'Signature record', 'your-text-domain' );?></h2>
    <fieldset>
        <div id="todo-setting-div" style="display:none">
        <fieldset>
            <label for="display-name">Name : </label>
            <input type="text" id="display-name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" disabled />
            <label for="site-title"> Site: </label>
            <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
            <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
        </fieldset>
        </div>
    
        <div style="display:flex; justify-content:space-between; margin:5px;">
            <div>
                <select id="select-todo">
                    <option value="0">To-do list</option>
                    <option value="1">Signature record</option>
                    <option value="2">...</option>
                </select>
            </div>
            <div style="text-align: right">
                <input type="text" id="search-todo" style="display:inline" placeholder="Search..." />
                <span id="todo-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
            </div>
        </div>

        <table class="ui-widget" style="width:100%;">
            <thead>
                <tr>
                    <th><?php echo __( 'Todo', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Document', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Submit', 'your-text-domain' );?></th>
                    <th><?php echo __( 'User', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Time', 'your-text-domain' );?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $query = retrieve_signature_record_data();
            if ($query->have_posts()) :
                //$x = 0;
                while ($query->have_posts()) : $query->the_post();
                    $job_id = get_post_meta(get_the_ID(), 'job_id', true);
                    $doc_id = get_post_meta(get_the_ID(), 'doc_id', true);
                    $report_id = get_post_meta(get_the_ID(), 'report_id', true);
                    if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
                    $todo_site = get_post_meta($doc_id, 'site_id', true);
                    $doc_title = get_post_meta($doc_id, 'doc_title', true);
                    if ($report_id) $doc_title .= '(Report#'.$report_id.')';
                    $submit_action = get_post_meta(get_the_ID(), 'submit_action', true);
                    $submit_user = get_post_meta(get_the_ID(), 'submit_user', true);
                    $submit_time = get_post_meta(get_the_ID(), 'submit_time', true);
    
                    if ($todo_site==$_site_id) { // Aditional condition to filter the data
                        $user_data = get_userdata( $submit_user );
                        ?>
                        <tr id="edit-todo-<?php esc_attr(the_ID()); ?>">
                            <td style="text-align:center;"><?php esc_html(the_title()); ?></td>
                            <td><?php echo esc_html($doc_title); ?></td>
                            <td style="text-align:center;"><?php esc_html(the_title($submit_action)); ?></td>
                            <td style="text-align:center;"><?php esc_html($user_data->display_name); ?></td>
                            <td style="text-align:center;"><?php wp_date(get_option('date_format'), $submit_time); ?></td>

                        </tr>
                        <?php
                        //$x += 1;
                    }
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
            </tbody>
        </table>
    </fieldset>
    </div>
    <?php
}

// Shortcode to display To-do list on frontend
function to_do_list_shortcode() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        if ($_GET['_select_todo']=='1') display_signature_record();

        $current_user_id = get_current_user_id();
        $site_id = get_post_meta($current_user_id, 'site_id', true);
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
                <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
            </fieldset>
            </div>
        
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <select id="select-todo">
                        <option value="0">To-do list</option>
                        <option value="1">Signature record</option>
                        <option value="2">...</option>
                    </select>
                </div>
                <div style="text-align: right">
                    <input type="text" id="search-todo" style="display:inline" placeholder="Search..." />
                    <span id="todo-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
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
                    $job_id = get_post_meta(get_the_ID(), 'job_id', true);
                    $doc_id = get_post_meta(get_the_ID(), 'doc_id', true);
                    $report_id = get_post_meta(get_the_ID(), 'report_id', true);
                    if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
                    $doc_title = get_post_meta($doc_id, 'doc_title', true);
                    if ($report_id) $doc_title .= '(Report#'.$report_id.')';
                    $todo_due = get_post_meta(get_the_ID(), 'todo_due', true);

                    if (is_my_job($job_id)) { // Aditional condition to filter the data
                        ?>
                        <tr id="edit-todo-<?php esc_attr(the_ID()); ?>">
                            <td style="text-align:center;"><?php esc_html(the_title()); ?></td>
                            <td><?php echo esc_html($doc_title); ?></td>
                            <?php if ($todo_due < time()) { ?>
                                <td style="text-align:center; color:red;">
                            <?php } else { ?>
                                <td style="text-align:center;"><?php } ?>
                            <?php echo wp_date(get_option('date_format'), $todo_due);?></td>
                        </tr>
                        <?php
                        $x += 1;
                    }
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                </tbody>
            </table>
        </fieldset>
        </div>
        <?php
        display_todo_action_list();

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
            'relation' => 'AND',
            array(
                'key'     => 'todo_due',
                'compare' => 'EXISTS',
            ),
            array(
                'key'     => 'submit_user',
                'compare' => 'NOT EXISTS',
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query;
}

function get_todo_dialog_data() {
    $result = array();
    if (isset($_POST['action']) && $_POST['action'] === 'get_todo_dialog_data') {
        $todo_id = (int)sanitize_text_field($_POST['_todo_id']);
        $result['html_contain'] = display_todo_dialog($todo_id);
    } else {
        $result['html_contain'] = 'Invalid AJAX request!';
    }
    wp_send_json($result);
}
add_action('wp_ajax_get_todo_dialog_data', 'get_todo_dialog_data');
add_action('wp_ajax_nopriv_get_todo_dialog_data', 'get_todo_dialog_data');

function display_todo_dialog($todo_id) {
    $report_id = get_post_meta($todo_id, 'report_id', true);
    $doc_id = get_post_meta($todo_id, 'doc_id', true);

    $is_doc = false;
    if ($doc_id) {
        $start_job = get_post_meta($doc_id, 'start_job', true);
        $start_leadtime = get_post_meta($doc_id, 'start_leadtime', true);
        $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
        $doc_category = get_post_meta($doc_id, 'doc_category', true);
        $doc_url = get_post_meta($doc_id, 'doc_url', true);
        $site_id = get_post_meta($doc_id, 'site_id', true);
        $query = retrieve_doc_field_data(false, $site_id, false, true);
        $is_doc = true;
    } else {
        $start_job = get_post_meta($report_id, 'start_job', true);
        $start_leadtime = get_post_meta($report_id, 'start_leadtime', true);
        $doc_id = get_post_meta($report_id, 'doc_id', true);
        $site_id = get_post_meta($doc_id, 'site_id', true);
        $query = retrieve_doc_field_data($doc_id, false, false, true);
    }
    $doc_title = get_post_meta($doc_id, 'doc_title', true);

    ob_start();
    ?>
    <h2 style="margin-left:10px;"><?php echo esc_html($doc_title);?></h2>
    <input type="hidden" id="report-id" value="<?php echo $report_id;?>" />
    <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
    <fieldset>
    <?php
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $field_name = get_post_meta(get_the_ID(), 'field_name', true);
            $field_title = get_post_meta(get_the_ID(), 'field_title', true);
            $field_type = get_post_meta(get_the_ID(), 'editing_type', true);
            if ($is_doc) {
                $field_value = get_post_meta($doc_id, $field_name, true);
            } else {
                $field_value = get_post_meta($report_id, $field_name, true);
            }
            switch (true) {
                case ($field_type=='textarea'):
                    ?>
                    <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                    <textarea id="<?php echo esc_attr($field_name);?>" rows="3" style="width:100%;" disabled><?php echo esc_html($field_value);?></textarea>
                    <?php    
                    break;

                case ($field_type=='checkbox'):
                    $is_checked = ($field_value==1) ? 'checked' : '';
                    ?>
                    <input type="checkbox" id="<?php echo esc_attr($field_name);?>" <?php echo $is_checked;?> />
                    <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label><br>
                    <?php
                    break;
    
                default:
                    ?>
                    <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                    <input type="text" id="<?php echo esc_attr($field_name);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all" disabled />
                    <?php
                    break;
            }
        endwhile;
        wp_reset_postdata();
    }
    if ($is_doc) {
        if ($is_doc_report==1) {
            echo '<label id="doc-field-setting" class="button" for="doc-url">'.__( '欄位設定', 'your-text-domain' ).'</label>';
            echo '<span id="doc-report-preview" <span class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>';
            echo '<textarea id="doc-url" rows="3" style="width:100%; display:none;" disabled>' . $doc_url . '</textarea>';
            echo '<div id="doc-field-list-dialog">';
            echo display_doc_field_list($doc_id);
            echo '</div>';
        } else {
            echo '<label id="doc-field-setting" class="button" for="doc-url">'.__( '文件地址', 'your-text-domain' ).'</label>';
            echo '<span id="doc-url-preview" <span class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>';
            echo '<textarea id="doc-url" rows="3" style="width:100%;" disabled>' . $doc_url . '</textarea>';
            echo '<div id="doc-field-list-dialog" style="display:none;">';
            echo display_doc_field_list($doc_id);
            echo '</div>';
        }
        echo '<input type="hidden" id="is-doc-report" value="'.$is_doc_report.'" />';
        ?>
            <label for="doc-category"><?php echo __( '文件類別', 'your-text-domain' );?></label><br>
            <select id="doc-category" class="text ui-widget-content ui-corner-all" disabled><?php echo select_doc_category_option_data($doc_category);?></select>
        <?php
    }
    ?>
        <label for="action-list-button"><?php echo __( '文件狀態', 'your-text-domain' );?></label><br>
        <input type="button" id="action-list-button" value="<?php echo esc_html(get_the_title($todo_id));?>" style="width:100%; margin:3px; border-radius:5px; font-size:small;" />
        <hr>
    <?php
    $query = retrieve_todo_action_list_data($todo_id);
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            echo '<input type="button" id="todo-dialog-button-'.get_the_ID().'" value="'.get_the_title().'" style="margin:5px;" />';
        endwhile;
        wp_reset_postdata();
    }
    ?>
    </fieldset>
    <?php
    $html = ob_get_clean();
    return $html;
}

function set_todo_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_action_id']) ) {
        // action button is clicked, current todo update
        $action_id = sanitize_text_field($_POST['_action_id']);
        $todo_id = esc_attr(get_post_meta($action_id, 'todo_id', true));
        $doc_id = esc_attr(get_post_meta($todo_id, 'doc_id', true));
        $start_job = esc_attr(get_post_meta($doc_id, 'start_job', true));
        update_post_meta( $todo_id, 'submit_user', $current_user_id);
        update_post_meta( $todo_id, 'submit_action', $action_id);
        update_post_meta( $todo_id, 'submit_time', time());
        $params = array(
            'next_job'      => $start_job,
            'action_id'     => $action_id,
        );        
        set_next_job_and_actions($params);
/*        
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
            $response = get_the_title($todo_id);
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
            $response = get_the_title($new_todo_id);
        }
*/
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_todo_dialog_data', 'set_todo_dialog_data' );
add_action( 'wp_ajax_nopriv_set_todo_dialog_data', 'set_todo_dialog_data' );

function set_next_job_and_actions($args = array()) {
    $next_job      = isset($args['next_job']) ? $args['next_job'] : 0;
    $action_id     = isset($args['action_id']) ? $args['action_id'] : 0;

    if ($next_job == 0) return;

    if ($action_id > 0) {
        $next_job      = get_post_meta($action_id, 'next_job', true);
        $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
        $todo_id       = get_post_meta($action_id, 'todo_id', true);
        $doc_id        = get_post_meta($todo_id, 'doc_id', true);
        $report_id     = get_post_meta($todo_id, 'report_id', true);
    } else {
        $doc_id        = isset($args['doc_id']) ? $args['doc_id'] : 0;
        $report_id     = isset($args['report_id']) ? $args['report_id'] : 0;
        $next_leadtime = isset($args['next_leadtime']) ? $args['next_leadtime'] : 0;    
    }
    $todo_title = get_the_title($next_job);
    if ($next_job==-1) $todo_title = __( '發行', 'your-text-domain' );
    if ($next_job==-2) $todo_title = __( '廢止', 'your-text-domain' );
    
    // Insert the To-do list for next_job
    $current_user_id = get_current_user_id();
    $new_post = array(
        'post_title'    => $todo_title,
        'post_status'   => 'publish',
        'post_author'   => $current_user_id,
        'post_type'     => 'todo',
    );    
    $new_todo_id = wp_insert_post($new_post);
    update_post_meta( $new_todo_id, 'job_id', $next_job);
    if ($doc_id) update_post_meta( $new_todo_id, 'doc_id', $doc_id);
    if ($report_id) update_post_meta( $new_todo_id, 'report_id', $report_id);
    update_post_meta( $new_todo_id, 'todo_due', time()+$next_leadtime);
    if ($doc_id) update_post_meta( $doc_id, 'todo_status', $new_todo_id);
    if ($report_id) update_post_meta( $report_id, 'todo_status', $new_todo_id);

    if ($next_job==-1 || $next_job==-2) {
        update_post_meta( $new_todo_id, 'submit_user', $current_user_id);
        update_post_meta( $new_todo_id, 'submit_time', time());
        notice_the_persons_in_site($new_todo_id);
        if ($doc_id) update_post_meta( $doc_id, 'todo_status', $next_job);
        if ($report_id) update_post_meta( $report_id, 'todo_status', $next_job);
    }

    if ($next_job>0) {
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
                $new_next_job = get_post_meta(get_the_ID(), 'next_job', true);
                $new_next_leadtime = get_post_meta(get_the_ID(), 'next_leadtime', true);
                update_post_meta( $new_action_id, 'todo_id', $new_todo_id);
                update_post_meta( $new_action_id, 'next_job', $new_next_job);
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
    $doc_id = get_post_meta($todo_id, 'doc_id', true);
    $report_id = get_post_meta($todo_id, 'report_id', true);
    if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
    $doc_title = get_post_meta($doc_id, 'doc_title', true);
    $todo_due = get_post_meta($todo_id, 'todo_due', true);
    $due_date = wp_date( get_option('date_format'), $todo_due );
    $message_text='You have to work on the '.$job_title.':'.$doc_title.' before '.$due_date.'.';
    $link_uri = home_url().'/to-do-list/?_id='.$todo_id;
    $job_id = get_post_meta($todo_id, 'job_id', true);
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
function notice_the_persons_in_site($todo_id=0) {
    $doc_id = get_post_meta($todo_id, 'doc_id', true);
    $report_id = get_post_meta($todo_id, 'report_id', true);
    if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
    $site_id = get_post_meta($doc_id, 'site_id', true);
    $doc_url = get_post_meta($doc_id, 'doc_url', true);
    $doc_title = get_post_meta($doc_id, 'doc_title', true);
    if ($report_id) $doc_title .= '(Report#'.$report_id.')'; 
    $todo_submit = get_post_meta($todo_id, 'submit_date', true);
    $submit_date = wp_date( get_option('date_format'), $todo_submit );
    
    $message_text=$doc_title.' has been published on '.wp_date( get_option('date_format'), $submit_date ).'.';

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
        <div id="new-todo-action" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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


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

// Shortcode to display To-do list on frontend
function to_do_list_shortcode() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        if (isset($_GET['_search'])) display_to_do_list();

        if (isset($_GET['_id'])) {
            echo '<div class="ui-widget" id="result-container">';
            echo display_todo_dialog($todo_id);
            echo '</div>';
        }

        if ($_GET['_select_todo']=='1') display_signature_record();

        if ($_GET['_select_todo']!='1' && !isset($_GET['_search']) && !isset($_GET['_id'])) display_to_do_list();

    } else {
        user_did_not_login_yet();
    }
}
add_shortcode('to-do-list', 'to_do_list_shortcode');

function display_to_do_list() {
    $current_user_id = get_current_user_id();
    $site_id = get_user_meta($current_user_id, 'site_id', true);
    $image_url = get_post_meta($site_id, 'image_url', true);
    $user_data = get_userdata( $current_user_id );
    ?>
    <div class="ui-widget" id="result-container">
    <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
    <h2 style="display:inline;"><?php echo __( '待辦事項', 'your-text-domain' );?></h2>
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
                while ($query->have_posts()) : $query->the_post();

                    //$job_id = get_post_meta(get_the_ID(), 'job_id', true);
                    $todo_id = get_the_ID();
                    $todo_title = get_the_title();
                    $todo_due = get_post_meta(get_the_ID(), 'todo_due', true);
                    $doc_id = get_post_meta(get_the_ID(), 'doc_id', true);
                    $report_id = get_post_meta(get_the_ID(), 'report_id', true);
                    
                    if (!empty($report_id)) {
                        $doc_id = get_post_meta($report_id, 'doc_id', true);
                    }
                    
                    if (empty($doc_id)) {
                        $doc_id = get_the_ID();
                        $job_id = get_post_meta(get_the_ID(), 'start_job', true);
                        $todo_title = get_the_title($job_id);
                        $todo_due = get_post_meta(get_the_ID(), 'todo_status', true);
                        $todo_id = get_the_ID();
                    }

                    $doc_number = get_post_meta($doc_id, 'doc_number', true);
                    $doc_title = get_post_meta($doc_id, 'doc_title', true);
                    $doc_title .= '('.$doc_number.')';
                    
                    if (!empty($report_id)) {
                        $doc_title .= '(Report#' . $report_id . ')';
                    }
                    
                    ?>
                        <tr id="edit-todo-<?php echo esc_attr($todo_id); ?>">
                            <td style="text-align:center;"><?php echo esc_html($todo_title); ?></td>
                            <td><?php echo esc_html($doc_title); ?></td>
                            <?php if ($todo_due < time()) { ?>
                                <td style="text-align:center; color:red;">
                            <?php } else { ?>
                                <td style="text-align:center;"><?php } ?>
                            <?php echo wp_date(get_option('date_format'), $todo_due);?></td>
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
}

function get_post_type_meta_keys($post_type) {
    global $wpdb;
    $query = $wpdb->prepare("
        SELECT DISTINCT(meta_key)
        FROM $wpdb->postmeta
        INNER JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
        WHERE $wpdb->posts.post_type = %s
    ", $post_type);

    return $wpdb->get_col($query);
}

function retrieve_todo_list_data(){

    $current_user_id = get_current_user_id();
    $site_id = get_user_meta($current_user_id, 'site_id', true);
    $user_job_ids = get_user_meta($current_user_id, 'user_job_ids', true);
    if (!is_array($user_job_ids)) {
        $user_job_ids = array();
    }        
    $search_query = sanitize_text_field($_GET['_search']);

    if ($search_query) {
        $args = array(
            'post_type'      => 'document',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => 'site_id',
                    'value'   => $site_id,
                    'compare' => '=',
                ),
                array(
                    'key'     => 'start_job',
                    'compare' => 'EXISTS', // Check if the meta key exists
                ),
                array(
                    'key'     => 'start_job',
                    'value'   => $user_job_ids, // User's job IDs
                    'compare' => 'IN',
                ),
                array(
                    'key'     => 'todo_status',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );

        // Add meta query for searching across all meta keys
        $document_meta_keys = get_post_type_meta_keys('document');
        $meta_query_all_keys = array('relation' => 'OR');
        foreach ($document_meta_keys as $meta_key) {
            $meta_query_all_keys[] = array(
                'key'     => $meta_key,
                'value'   => $search_query,
                'compare' => 'LIKE',
            );
        }
        
        $args['meta_query'][] = $meta_query_all_keys;
                
    } else {
        // Define the WP_Query arguments
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
        
        // Add a new meta query to filter by job_id
        $args['meta_query'][] = array(
            'key'     => 'job_id',
            'value'   => $user_job_ids, // Value is the array of user job IDs
            'compare' => 'IN',
        );        
    }

    $query = new WP_Query($args);
    return $query;
}

function display_todo_dialog($todo_id) {

    // Get the post type of the post with the given ID
    $post_type = get_post_type( $todo_id );

    // Check if the post type is 'todo'
    if ( ($post_type != 'todo') && ($post_type != 'document') ) {
        return 'post type is '.$post_type.'. Wrong type!';
    }
    
    if ( $post_type === 'todo' ) {
        $report_id = get_post_meta($todo_id, 'report_id', true);
        $doc_id = get_post_meta($todo_id, 'doc_id', true);
    }
    
    if ( $post_type === 'document' ) {
        $doc_id = $todo_id;
        $todo_id = get_post_meta($doc_id, 'start_job', true);
    }
    
    $is_doc = false;
    if ($doc_id) {
        $doc_number = get_post_meta($doc_id, 'doc_number', true);
        $doc_title = get_post_meta($doc_id, 'doc_title', true);
        $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
        $doc_category = get_post_meta($doc_id, 'doc_category', true);
        $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
        $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
        $is_doc = true;
    } else {
        $doc_id = get_post_meta($report_id, 'doc_id', true);
    }

    if (!$doc_id) return 'post type is '.$post_type.'. doc_id is empty!';

    $current_user_id = get_current_user_id();
    $is_site_admin = get_user_meta($current_user_id, 'is_site_admin', true);
    $site_id = get_user_meta($current_user_id, 'site_id', true);
    $image_url = get_post_meta($site_id, 'image_url', true);
    $doc_title = get_post_meta($doc_id, 'doc_title', true);

    ob_start();
    ?>
    <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
    <h2 style="display:inline;"><?php echo esc_html('Todo: '.get_the_title($todo_id));?></h2>
    <input type="hidden" id="report-id" value="<?php echo $report_id;?>" />
    <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
    <fieldset>
    <?php
    if ($is_doc) {
        // document_dialog data
        ?>
        <label for="doc-number"><?php echo __( '文件編號', 'your-text-domain' );?></label>
        <input type="text" id="doc-number" value="<?php echo esc_html($doc_number);?>" class="text ui-widget-content ui-corner-all" disabled />
        <label for="doc-title"><?php echo __( '文件名稱', 'your-text-domain' );?></label>
        <input type="text" id="doc-title" value="<?php echo esc_html($doc_title);?>" class="text ui-widget-content ui-corner-all" disabled />
        <label for="doc-revision"><?php echo __( '文件版本', 'your-text-domain' );?></label>
        <input type="text" id="doc-revision" value="<?php echo esc_html($doc_revision);?>" class="text ui-widget-content ui-corner-all" disabled />
        <label for="doc-category"><?php echo __( '文件類別', 'your-text-domain' );?></label><br>
        <select id="doc-category" class="text ui-widget-content ui-corner-all" disabled><?php echo select_doc_category_option_data($doc_category);?></select>
        <?php
        if ($is_doc_report==1) {
            ?>
            <label for="doc-report"><?php echo __( '欄位設定', 'your-text-domain' );?></label>
            <span id="doc-report-preview" class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>
            <div id="doc-field-list-div"><?php echo display_doc_field_list($doc_id);?></div>
            <?php
        } else {
            ?>
            <label for="doc-frame"><?php echo __( '文件地址', 'your-text-domain' );?></label>
            <span id="doc-frame-preview" class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>
            <textarea id="doc-frame" rows="3" style="width:100%;" disabled><?php echo $doc_frame;?></textarea>
            <?php
        }
        ?>
        <input type="hidden" id="is-doc-report" value="<?php echo $is_doc_report;?>" />
        <?php

    } else {
        // doc_report_dialog data
        $params = array(
            'doc_id'     => $doc_id,
            'is_editing'  => true,
        );                
        $query = retrieve_doc_field_data($params);

        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                $field_title = get_post_meta(get_the_ID(), 'field_title', true);
                $field_type = get_post_meta(get_the_ID(), 'field_type', true);
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
    }
    ?>
    <?php if ($is_site_admin){?>
        <label for="todo-action-list"><?php echo '<b>'.get_the_title($todo_id).'</b>'.__( '待辦', 'your-text-domain' );?></label><br>
        <fieldset>
        <table style="width:100%;">
            <thead>
                <tr>
                    <th><?php echo __( 'Action', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Next', 'your-text-domain' );?></th>
                    <th><?php echo __( 'LeadTime', 'your-text-domain' );?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = retrieve_todo_action_list_data($todo_id);
                if ( $post_type === 'document' ) {
                    $query = retrieve_job_action_list_data($todo_id);
                }                
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $next_job = get_post_meta(get_the_ID(), 'next_job', true);
                        if ($is_site_admin) $edit_action = 'edit-action-';
                        echo '<tr id="'.$edit_action.esc_attr(get_the_ID()).'">';
                        echo '<td style="text-align:center;">'.get_the_title().'</td>';
                        echo '<td>'.get_post_field('post_content', get_the_ID()).'</td>';
                        if ($next_job>0) echo '<td style="text-align:center;">'.get_the_title($next_job).'</td>';
                        if ($next_job==-1) echo '<td style="text-align:center;">'.__( '文件發行', 'your-text-domain' ).'</td>';
                        if ($next_job==-2) echo '<td style="text-align:center;">'.__( '文件廢止', 'your-text-domain' ).'</td>';
                        echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'next_leadtime', true)).'</td>';
                        echo '</tr>';
                    endwhile;
                    wp_reset_postdata();
                }
                ?>
            </tbody>
        </table>
        <div id="new-action" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
        </fieldset>
    <?php }?>
    <?php display_todo_action_dialog();?>
    <hr>
    <?php
    $query = retrieve_todo_action_list_data($todo_id);
    if ( $post_type === 'document' ) {
        $query = retrieve_job_action_list_data($todo_id);
    }
    
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

function get_todo_dialog_data() {
    $result = array();
    if (isset($_POST['_todo_id'])) {
        $todo_id = sanitize_text_field($_POST['_todo_id']);
        $result['html_contain'] = display_todo_dialog($todo_id);
    } else {
        $result['html_contain'] = 'Invalid AJAX request!';
    }
    wp_send_json($result);
}
add_action('wp_ajax_get_todo_dialog_data', 'get_todo_dialog_data');
add_action('wp_ajax_nopriv_get_todo_dialog_data', 'get_todo_dialog_data');

function set_todo_dialog_data() {
    if( isset($_POST['_action_id']) ) {
        // action button is clicked, current todo update
        $current_user_id = get_current_user_id();
        $action_id = sanitize_text_field($_POST['_action_id']);
        $doc_id = sanitize_text_field($_POST['_doc_id']);
        $report_id = sanitize_text_field($_POST['_report_id']);
        $todo_id = get_post_meta($action_id, 'todo_id', true);
        // Check if the meta key exists
        if ( empty( $todo_id ) ) {
            $job_id = get_post_meta($action_id, 'job_id', true);
            // Insert the To-do list for signature
            $new_post = array(
                'post_title'    => get_the_title($job_id),
                'post_status'   => 'publish',
                'post_author'   => $current_user_id,
                'post_type'     => 'todo',
            );    
            $todo_id = wp_insert_post($new_post);
            update_post_meta( $todo_id, 'job_id', $job_id);
            if ($doc_id) update_post_meta( $todo_id, 'doc_id', $doc_id);
            if ($report_id) update_post_meta( $todo_id, 'report_id', $report_id);
        }
        update_post_meta( $todo_id, 'submit_user', $current_user_id);
        update_post_meta( $todo_id, 'submit_action', $action_id);
        update_post_meta( $todo_id, 'submit_time', time());
        $params = array(
            'action_id' => $action_id,
            'doc_id'    => $doc_id,
            'report_id' => $report_id,
        );        
        set_next_todo_and_actions($params);
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_todo_dialog_data', 'set_todo_dialog_data' );
add_action( 'wp_ajax_nopriv_set_todo_dialog_data', 'set_todo_dialog_data' );

function set_next_todo_and_actions($args = array()) {

    $action_id = isset($args['action_id']) ? $args['action_id'] : 0;

    if ($action_id > 0) {
        $next_job      = get_post_meta($action_id, 'next_job', true);
        $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
        $todo_id       = get_post_meta($action_id, 'todo_id', true);
        $doc_id        = get_post_meta($todo_id, 'doc_id', true);
        $report_id     = get_post_meta($todo_id, 'report_id', true);
        if (!$todo_id) {
            $todo_id = get_post_meta($action_id, 'job_id', true);
            $doc_id = isset($args['doc_id']) ? $args['doc_id'] : 0;
            $report_id = isset($args['report_id']) ? $args['report_id'] : 0;
        }
    }
    $todo_title = get_the_title($next_job);

    if ($next_job==-1) $todo_title = __( '文件發行', 'your-text-domain' );
    if ($next_job==-2) $todo_title = __( '文件廢止', 'your-text-domain' );
    
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
        notice_the_persons_in_site($new_todo_id,$next_job);
        if ($doc_id) update_post_meta( $doc_id, 'todo_status', $next_job);
        if ($report_id) update_post_meta( $report_id, 'todo_status', $next_job);
    }

    if ($next_job>0) {
        notice_the_responsible_persons($new_todo_id);
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

function get_users_by_job_id($job_id=0) {
    // Set up the user query arguments
    $args = array(
        'meta_query'     => array(
            array(
                'key'     => 'user_job_ids',
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
function notice_the_responsible_persons($todo_id=0) {
    $line_bot_api = new line_bot_api();
    $todo_title = get_the_title($todo_id);
    $doc_id = get_post_meta($todo_id, 'doc_id', true);
    $report_id = get_post_meta($todo_id, 'report_id', true);
    if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
    $doc_title = get_post_meta($doc_id, 'doc_title', true);
    $job_id = get_post_meta($todo_id, 'job_id', true);
    $todo_due = get_post_meta($todo_id, 'todo_due', true);
    $due_date = wp_date( get_option('date_format'), $todo_due );
    $text_message='You are in '.$todo_title.' position. You have to sign off the '.$doc_title.' before '.$due_date.'.';
    $text_message = '你在「'.$todo_title.'」的職務有一份文件「'.$doc_title.'」需要在'.$due_date.'前簽核完成，你可以點擊下方連結查看該文件。';
    $link_uri = home_url().'/to-do-list/?_id='.$todo_id;
    $post_type = get_post_type( $todo_id );
    //if ($post_type=='job') $job_id = $todo_id;
    $users = get_users_by_job_id($job_id);
    foreach ($users as $user) {
        $params = [
            'display_name' => $user->display_name,
            'link_uri' => $link_uri,
            'text_message' => $text_message,
        ];        
        $flexMessage = set_flex_message($params);
        $line_bot_api->pushMessage([
            'to' => get_user_meta($user->ID, 'line_user_id', TRUE),
            'messages' => [$flexMessage],
        ]);
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
function notice_the_persons_in_site($todo_id=0,$job_id=0) {
    $line_bot_api = new line_bot_api();
    $doc_id = get_post_meta($todo_id, 'doc_id', true);
    $report_id = get_post_meta($todo_id, 'report_id', true);
    if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
    $site_id = get_post_meta($doc_id, 'site_id', true);
    $doc_title = get_post_meta($doc_id, 'doc_title', true);
    if ($report_id) $doc_title .= '(Report#'.$report_id.')'; 
    $todo_submit = get_post_meta($todo_id, 'submit_date', true);
    $submit_date = wp_date( get_option('date_format'), $todo_submit );    
    $text_message=$doc_title.' has been published on '.wp_date( get_option('date_format'), $submit_date ).'.';
    $text_message = '文件「'.$doc_title.'」已經在'.wp_date( get_option('date_format'), $submit_date );
    if ($job_id==-1) $text_message .= '發行，你可以點擊下方連結查看該文件。';
    if ($job_id==-2) $text_message .= '廢止，你可以點擊下方連結查看該文件。';
    $link_uri = home_url().'/display-documents/?_id='.$doc_id;
    $users = get_users_in_site($site_id);
    foreach ($users as $user) {
        $params = [
            'display_name' => $user->display_name,
            'link_uri' => $link_uri,
            'text_message' => $text_message,
        ];        
        $flexMessage = set_flex_message($params);
        $line_bot_api->pushMessage([
            'to' => get_user_meta($user->ID, 'line_user_id', TRUE),
            'messages' => [$flexMessage],
        ]);            
    }    
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
        $action_id = sanitize_text_field($_POST['_action_id']);
        $todo_id = get_post_meta($action_id, 'todo_id', true);
        $doc_id = get_post_meta($todo_id, 'doc_id', true);
        $next_job = get_post_meta($action_id, 'next_job', true);
        $response["action_title"] = get_the_title($action_id);
        $response["action_content"] = get_post_field('post_content', $action_id);
        $response["next_job"] = select_next_job_option_data($next_job);
        $response["next_leadtime"] = esc_html(get_post_meta($action_id, 'next_leadtime', true));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_todo_action_dialog_data', 'get_todo_action_dialog_data' );
add_action( 'wp_ajax_nopriv_get_todo_action_dialog_data', 'get_todo_action_dialog_data' );

function set_todo_action_dialog_data() {
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
        $current_user_id = get_current_user_id();
        $new_post = array(
            'post_title'    => 'New action',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'action',
        );    
        $post_id = wp_insert_post($new_post);
        update_post_meta( $post_id, 'todo_id', sanitize_text_field($_POST['_todo_id']));
        update_post_meta( $post_id, 'next_leadtime', 86400);
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

// signature_record
function display_signature_record() {
    $current_user_id = get_current_user_id();
    $site_id = get_user_meta($current_user_id, 'site_id', true);
    $image_url = get_post_meta($site_id, 'image_url', true);
    $user_data = get_userdata( $current_user_id );
    $signature_record_list = get_signature_record_list($site_id);
    $$html_contain = $signature_record_list['html'];
    $x_value = $signature_record_list['x'];
    ?>
    <div class="ui-widget" id="result-container">
    <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
    <h2 style="display:inline;"><?php echo __( '簽核記錄', 'your-text-domain' );?></h2>
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
                    <option value="1" selected>Signature record</option>
                    <option value="2">...</option>
                </select>
            </div>
            <div style="text-align: right">
                <input type="text" id="search-todo" style="display:inline" placeholder="Search..." />
                <span id="todo-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
            </div>
        </div>
        <?php echo $$html_contain;?>
        <p style="background-color:lightblue;">Total Submissions: <?php echo $x_value;?></p>
    </fieldset>
    </div>
    <?php
}

function get_signature_record_list($site_id=false, $doc=false, $report=false ) {
    ob_start();
    ?>
        <table class="ui-widget" style="width:100%;">
            <thead>
                <tr>
                    <th><?php echo __( 'Time', 'your-text-domain' );?></th>
                    <?php if(!$doc) {;?>
                    <th><?php echo __( 'Document', 'your-text-domain' );?></th>
                    <?php };?>
                    <th><?php echo __( 'Todo', 'your-text-domain' );?></th>
                    <th><?php echo __( 'User', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Action', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Next', 'your-text-domain' );?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $query = retrieve_signature_record_data($doc, $report);
            $x = 0;
            if ($query->have_posts()) :
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
                    $next_job = get_post_meta($submit_action, 'next_job', true);
                    $job_title = ($next_job==-1) ? __( '文件發行', 'your-text-domain' ) : get_the_title($next_job);
                    $job_title = ($next_job==-2) ? __( '文件廢止', 'your-text-domain' ) : $job_title;

                    if ($todo_site==$site_id) { // Aditional condition to filter the data
                        $user_data = get_userdata( $submit_user );
                        ?>
                        <tr id="view-todo-<?php esc_attr(the_ID()); ?>">
                            <td style="text-align:center;"><?php echo wp_date(get_option('date_format'), $submit_time).' '.wp_date(get_option('time_format'), $submit_time);?></td>
                            <?php if(!$doc) {;?>
                            <td><?php echo esc_html($doc_title);?></td>
                            <?php };?>
                            <td style="text-align:center;"><?php esc_html(the_title());?></td>
                            <td style="text-align:center;"><?php echo esc_html($user_data->display_name);?></td>
                            <td style="text-align:center;"><?php echo esc_html(get_the_title($submit_action));?></td>
                            <td style="text-align:center;"><?php echo esc_html($job_title);?></td>
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
    <?php
    $html = ob_get_clean();
    // Return an array containing both HTML content and $x
    return array(
        'html' => $html,
        'x'    => $x,
    );
}

function retrieve_signature_record_data($doc_id=false, $report_id=false){
    $args = array(
        'post_type'      => 'todo',
        'posts_per_page' => -1,
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

    if ($doc_id) {
        $args['meta_query'][] = array(
            'key'   => 'doc_id',
            'value' => $doc_id,
        );
    }

    if ($report_id) {
        $args['meta_query'][] = array(
            'key'   => 'report_id',
            'value' => $report_id,
        );
    }

    $query = new WP_Query($args);
    return $query;
}


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
        'labels'        => $labels,
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
            $todo_id = sanitize_text_field($_GET['_id']);
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
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');
            $current_page = max(1, get_query_var('paged')); // Get the current page number
            $query = retrieve_todo_list_data($current_page);
            $total_posts = $query->found_posts;
            $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages

            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $todo_id = get_the_ID();
                    $todo_title = get_the_title();
                    $todo_due = get_post_meta(get_the_ID(), 'todo_due', true);
                    if ($todo_due < time()) $todo_due_color='color:red;';
                    $todo_due = wp_date(get_option('date_format'), $todo_due);
                    $doc_id = get_post_meta(get_the_ID(), 'doc_id', true);
                    $report_id = get_post_meta(get_the_ID(), 'report_id', true);                    
                    if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
                    
                    if (empty($doc_id)) {
                        $doc_id = get_the_ID();
                        $job_id = get_post_meta(get_the_ID(), 'start_job', true);
                        $todo_title = get_the_title($job_id);
                        if ($job_id==-1) $todo_title='文件發行';
                        $todo_due = get_post_meta(get_the_ID(), 'todo_status', true);
                        if ($todo_due==-1) $todo_due='發行';
                    }

                    $doc_number = get_post_meta($doc_id, 'doc_number', true);
                    $doc_title = get_post_meta($doc_id, 'doc_title', true);
                    $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                    if ($is_doc_report==1) {
                        $doc_title .= '(#New report)';
                    } else {
                        $doc_title .= '('.$doc_number.')';
                    }
                    if ($report_id) $doc_title .= '(Report#' . $report_id . ')';
                    
                    ?>
                    <tr id="edit-todo-<?php echo esc_attr($todo_id); ?>">
                        <td style="text-align:center;"><?php echo esc_html($todo_title); ?></td>
                        <td><?php echo esc_html($doc_title); ?></td>
                        <td style="text-align:center; <?php echo $todo_due_color?>"><?php echo esc_html($todo_due);?></td>
                    </tr>
                    <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
            </tbody>
        </table>
        <?php
        // Display pagination links
        echo '<div class="pagination">';
        if ($current_page > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($current_page - 1)) . '"> < </a></span>';
        echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $current_page, $total_pages) . '</span>';
        if ($current_page < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($current_page + 1)) . '"> > </a></span>';
        echo '</div>';
         ?>
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

function retrieve_todo_list_data($current_page = 1){
    // Define the custom pagination parameters
    $posts_per_page = get_option('operation_row_counts');
    // Calculate the offset to retrieve the posts for the current page
    $offset = ($current_page - 1) * $posts_per_page;

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
            'posts_per_page' => $posts_per_page,
            'paged'          => $current_page,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => 'site_id',
                    'value'   => $site_id,
                    'compare' => '=',
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'start_job',
                        'value'   => $user_job_ids, // User's job IDs
                        'compare' => 'IN',
                    ),
                    array(
                        'key'     => 'start_job',
                        'value'   => -1,
                        'compare' => '=',
                    ),
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'todo_status',
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'todo_status',
                            'value'   => -1,
                            'compare' => '=',
                        ),
                        array(
                            'key'     => 'is_doc_report',
                            'value'   => 1,
                            'compare' => '=',
                        ),
                    ),
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
            'posts_per_page' => $posts_per_page,
            'paged'          => $current_page,
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
        //if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
    }
    
    if ( $post_type === 'document' ) {
        $doc_id = $todo_id;
        $todo_id = get_post_meta($doc_id, 'start_job', true);
    }
    
    if (!$doc_id) return 'post type is '.$post_type.'. doc_id is empty!';

    $doc_number = get_post_meta($doc_id, 'doc_number', true);
    $doc_title = get_post_meta($doc_id, 'doc_title', true);
    $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
    $doc_category = get_post_meta($doc_id, 'doc_category', true);
    $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
    $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
    if ($is_doc_report) $report_id = get_post_meta($todo_id, 'prev_report_id', true);

    $current_user_id = get_current_user_id();
    $is_site_admin = get_user_meta($current_user_id, 'is_site_admin', true);
    $site_id = get_user_meta($current_user_id, 'site_id', true);
    $image_url = get_post_meta($site_id, 'image_url', true);

    ob_start();
    ?>
    <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
    <h2 style="display:inline;"><?php echo esc_html('Todo: '.get_the_title($todo_id));?></h2>
    <input type="hidden" id="report-id" value="<?php echo $report_id;?>" />
    <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
    <fieldset>
    <?php
    if ($is_doc_report) {
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
                $default_value = get_post_meta(get_the_ID(), 'default_value', true);
                if ($report_id) {
                    $field_value = get_post_meta($report_id, $field_name, true);
                } else {
                    $field_value = get_post_meta(get_the_ID(), 'default_value', true);
                }
                switch (true) {
                    case ($field_type=='textarea'):
                        ?>
                        <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                        <textarea id="<?php echo esc_attr($field_name);?>" rows="3" style="width:100%;" disable><?php echo esc_html($field_value);?></textarea>
                        <?php    
                        break;
    
                    case ($field_type=='checkbox'):
                        $is_checked = ($field_value==1) ? 'checked' : '';
                        ?>
                        <input type="checkbox" id="<?php echo esc_attr($field_name);?>" <?php echo $is_checked;?> />
                        <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label><br>
                        <?php
                        break;

                    case ($field_type=='radio'):
                        if ($prev_field_name!=substr($field_name, 0, 5)) $x = 0;
                        if ($x==0) echo '<label>'.esc_html($field_title).'</label><br>';
                        $field_value = get_post_meta($report_id, $field_name, true);
                        $is_checked = ($field_value==1) ? 'checked' : '';
                        ?>                    
                        <input type="radio" id="<?php echo esc_attr($field_name);?>" name="<?php echo esc_attr(substr($field_name, 0, 5));?>" <?php echo $is_checked;?> />
                        <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($default_value);?></label><br>
                        <?php
                        $prev_field_name=substr($field_name, 0, 5);
                        $x += 1;
                        break;
        
                    case ($field_type=='date'):
                        ?>
                        <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                        <input type="text" id="<?php echo esc_attr($field_name);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all datepicker" />
                        <?php
                        break;
        
                    case ($field_type=='number'):
                        ?>
                        <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                        <input type="number" id="<?php echo esc_attr($field_name);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all" />
                        <?php
                        break;
        
                    default:
                        ?>
                        <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                        <input type="text" id="<?php echo esc_attr($field_name);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all"  />
                        <?php
                        break;
                }
            endwhile;
            wp_reset_postdata();
        }    
    } else {
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
    }
    ?>
    <hr>
    <?php
    if ( $post_type === 'document' ) {
        $query = retrieve_job_action_list_data($todo_id);
    } else {
        $query = retrieve_todo_action_list_data($todo_id);
    }
    
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            echo '<input type="button" id="todo-dialog-button-'.get_the_ID().'" value="'.get_the_title().'" style="margin:5px;" />';
        endwhile;
        wp_reset_postdata();
    }

    if ($todo_id==-1) echo '<input type="button" id="todo-dialog-button--1" value="OK" style="margin:5px;" />';
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
        $doc_id = get_post_meta($todo_id, 'doc_id', true);
        $result['doc_fields'] = display_doc_field_keys($doc_id);
    } else {
        $result['html_contain'] = 'Invalid AJAX request!';
    }
    wp_send_json($result);
}
add_action('wp_ajax_get_todo_dialog_data', 'get_todo_dialog_data');
add_action('wp_ajax_nopriv_get_todo_dialog_data', 'get_todo_dialog_data');

function set_todo_dialog_data() {
    if( isset($_POST['_action_id']) ) {
        // action button is clicked
        $current_user_id = get_current_user_id();
        $action_id = sanitize_text_field($_POST['_action_id']);
        $todo_id = get_post_meta($action_id, 'todo_id', true);
        // Create new todo if the meta key 'todo_id' does not exist
        if ( empty( $todo_id ) ) {
            $job_id = get_post_meta($action_id, 'job_id', true);
            $todo_title = get_the_title($job_id);
            $next_job = get_post_meta($action_id, 'next_job', true);
            $doc_id = sanitize_text_field($_POST['_doc_id']);
            $report_id = sanitize_text_field($_POST['_report_id']);
            if ($report_id) $todo_title = '(Report#'.$report_id.')'; 
            if ($action_id==-1) $todo_title = '文件發行';
            $new_post = array(
                'post_title'    => $todo_title,
                'post_status'   => 'publish',
                'post_author'   => $current_user_id,
                'post_type'     => 'todo',
            );    
            $todo_id = wp_insert_post($new_post);
            update_post_meta( $todo_id, 'job_id', $job_id);
            if ($doc_id) update_post_meta( $todo_id, 'doc_id', $doc_id);
            if ($report_id) update_post_meta( $todo_id, 'report_id', $report_id);
        }
        // Update current todo
        update_post_meta( $todo_id, 'submit_user', $current_user_id);
        update_post_meta( $todo_id, 'submit_action', $action_id);
        update_post_meta( $todo_id, 'submit_time', time());
        $doc_id = get_post_meta($todo_id, 'doc_id', true);
        //$report_id = get_post_meta($todo_id, 'report_id', true);
        if ($doc_id) update_post_meta( $doc_id, 'todo_status', $todo_id);

        // Create a new doc-report if is_doc_report==1
        $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
        if ($is_doc_report==1){
            $new_post = array(
                'post_title'    => 'New doc-report',
                'post_status'   => 'publish',
                'post_author'   => $current_user_id,
                'post_type'     => 'doc-report',
            );    
            $new_report_id = wp_insert_post($new_post);
            update_post_meta( $new_report_id, 'doc_id', $doc_id);
            update_post_meta( $new_report_id, 'todo_status', $next_job);
            update_post_meta( $doc_id, 'todo_status', -1);
            // Update the post
            $params = array(
                'doc_id'     => $doc_id,
            );                
            $query = retrieve_doc_field_data($params);
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                    $field_value = sanitize_text_field($_POST[$field_name]);
                    update_post_meta( $new_report_id, $field_name, $field_value);
                endwhile;
                wp_reset_postdata();
            }            
        }

        // set next todo and actions
        $params = array(
            'action_id' => $action_id,
            'todo_id' => $todo_id,
            'prev_report_id' => $new_report_id,
        );        
        set_next_todo_and_actions($params);
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_todo_dialog_data', 'set_todo_dialog_data' );
add_action( 'wp_ajax_nopriv_set_todo_dialog_data', 'set_todo_dialog_data' );

function set_next_todo_and_actions($args = array()) {
    // 1. come from set_todo_dialog_data(), create a next_todo base on the $args['action_id'], $args['to_id'] and $args['prev_report_id']
    // 2. come from set_todo_for_doc_report(), create a next_todo base on the $args['action_id'] and $args['prev_report_id']
    // 3. come from set_document_dialog_data(), create a next_todo base on the $args['start_job'] and $args['doc_id']

    $action_id = isset($args['action_id']) ? $args['action_id'] : 0;
    $current_user_id = get_current_user_id();

    if ($action_id > 0) {
        $next_job      = get_post_meta($action_id, 'next_job', true);
        $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
        $todo_id       = get_post_meta($action_id, 'todo_id', true);
        if (!$todo_id) $todo_id = isset($args['todo_id']) ? $args['todo_id'] : 0;
        $todo_title    = get_the_title($next_job);
        $report_id     = get_post_meta($todo_id, 'report_id', true);    
        $prev_report_id = isset($args['prev_report_id']) ? $args['prev_report_id'] : 0;
        $doc_ids       = get_document_for_job($next_job);
        if (is_array($doc_ids) && !empty($doc_ids)) {
            $doc_id = $doc_ids[0];
        } else {
            $doc_id = get_post_meta($todo_id, 'doc_id', true);
        }
    }

    if ($next_job==-1) $todo_title = __( '文件發行', 'your-text-domain' );
    if ($next_job==-2) $todo_title = __( '文件廢止', 'your-text-domain' );

    if ($action_id==0) {
        $next_job = isset($args['start_job']) ? $args['start_job'] : 0;
        $todo_title = get_the_title($next_job);
        $doc_id = isset($args['doc_id']) ? $args['doc_id'] : 0;
        $next_leadtime = 0;
        $current_user_id = 1;
    }
    
    // Create a new To-do for next_job
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
    if ($prev_report_id) update_post_meta( $new_todo_id, 'prev_report_id', $prev_report_id);
    update_post_meta( $new_todo_id, 'todo_due', time()+$next_leadtime);

    if ($next_job==-1 || $next_job==-2) {
        update_post_meta( $new_todo_id, 'submit_user', $current_user_id);
        update_post_meta( $new_todo_id, 'submit_time', time());
        notice_the_persons_in_site($new_todo_id,$next_job);
        if ($doc_id) update_post_meta( $doc_id, 'todo_status', $next_job);
        if ($report_id) update_post_meta( $report_id, 'todo_status', $next_job);
    }

    if ($next_job>0) {
        notice_the_responsible_persons($new_todo_id);
        // Create the Action list for next_job
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

function get_document_for_job($job_id) {
    $doc_ids = array();
    $args = array(
        'post_type'      => 'document',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'start_job',
                'value'   => $job_id,
                'compare' => '=',
            ),
        ),
    );                
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $doc_ids[] = get_the_ID();

        }
        wp_reset_postdata();
    }
    return $doc_ids;
}

// Notice the persons in charge the job
function notice_the_responsible_persons($todo_id=0) {
    $todo_title = get_the_title($todo_id);
    $doc_id = get_post_meta($todo_id, 'doc_id', true);
    $report_id = get_post_meta($todo_id, 'report_id', true);
    if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
    $doc_title = get_post_meta($doc_id, 'doc_title', true);
    $todo_due = get_post_meta($todo_id, 'todo_due', true);
    $due_date = wp_date( get_option('date_format'), $todo_due );
    $text_message='You are in '.$todo_title.' position. You have to sign off the '.$doc_title.' before '.$due_date.'.';
    $text_message = '你在「'.$todo_title.'」的職務有一份文件「'.$doc_title.'」需要在'.$due_date.'前簽核完成，你可以點擊下方連結查看該文件。';
    $link_uri = home_url().'/to-do-list/?_id='.$todo_id;

    $line_bot_api = new line_bot_api();
    $args = array(
        'meta_query'     => array(
            array(
                'key'     => 'user_job_ids',
                'value'   => $job_id,
                'compare' => 'LIKE',
            ),
        ),
    );
    $query = new WP_User_Query($args);
    $users = $query->get_results();
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

// Notice the persons in site
function notice_the_persons_in_site($todo_id=0,$job_id=0) {
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

    $line_bot_api = new line_bot_api();
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
/*
// Get Unix timestamp for tomorrow at 2:00 PM
$start_time = strtotime('tomorrow 14:00');

// Schedule event to run weekly starting from tomorrow at 2:00 PM
if (!wp_next_scheduled('my_weekly_process_hook')) {
    wp_schedule_event($start_time, 'weekly', 'my_weekly_process_hook');
}

function add_post_record_twice_per_day() {
    // Create a new post
    $post_data = array(
        'post_title'    => 'New Post Title',
        'post_content'  => 'Post content goes here.',
        'post_status'   => 'publish',
        'post_author'   => 1, // Change this to the desired author ID
        'post_type'     => 'post', // Change this to the desired post type
    );

    // Insert the post into the database
    $post_id = wp_insert_post($post_data);

    // You can perform additional actions if needed

    // Log the action to a file or database
    error_log("New post added with ID: $post_id");
}

// Schedule the task to run at 9 AM
if ( ! wp_next_scheduled( 'add_post_record_twice_per_day_9am' ) ) {
    wp_schedule_event( strtotime( 'today 9:00' ), 'daily', 'add_post_record_twice_per_day_9am' );
}
add_action( 'add_post_record_twice_per_day_9am', 'add_post_record_twice_per_day' );

// Schedule the task to run at 3 PM
if ( ! wp_next_scheduled( 'add_post_record_twice_per_day_3pm' ) ) {
    wp_schedule_event( strtotime( 'today 15:00' ), 'daily', 'add_post_record_twice_per_day_3pm' );
}
add_action( 'add_post_record_twice_per_day_3pm', 'add_post_record_twice_per_day' );

// Hook into WordPress initialization to schedule the event
add_action('wp', 'schedule_biweekly_event');

function schedule_biweekly_event() {
    // Check if the event is already scheduled
    if (!wp_next_scheduled('biweekly_post_event')) {
        // Schedule the event to run every two weeks
        wp_schedule_event(time(), 'biweekly', 'biweekly_post_event');
    }
}

// Hook into the biweekly_post_event action
add_action('biweekly_post_event', 'add_biweekly_post');

function add_biweekly_post() {
    // Create the post data
    $post_data = array(
        'post_title'   => 'Bi-Weekly Post', // Set your post title here
        'post_content' => 'This is a bi-weekly post.', // Set your post content here
        'post_status'  => 'publish',
        'post_author'  => 1, // Set the author ID here
        'post_type'    => 'post', // Set the post type here
    );

    // Insert the post into the database
    $post_id = wp_insert_post($post_data);

    // Optionally, you can perform additional actions after inserting the post
    if ($post_id) {
        // Post inserted successfully
        // Perform additional actions if needed
    } else {
        // Error occurred while inserting the post
        // Handle the error
    }
}

// Add this code to your theme's functions.php file or custom plugin

add_action('init', 'schedule_post_by_frequency');

function schedule_post_by_frequency() {
    if (isset($_POST['frequency'])) {
        // Get the selected frequency from the form
        $frequency = sanitize_text_field($_POST['frequency']);
        
        // Schedule the post creation based on the selected frequency
        switch ($frequency) {
            case 'twice_daily':
                schedule_twice_daily_post();
                break;
            case 'daily':
                schedule_daily_post();
                break;
            case 'weekly':
                schedule_weekly_post();
                break;
            case 'biweekly':
                schedule_biweekly_post();
                break;
            case 'monthly':
                schedule_monthly_post();
                break;
            case 'yearly':
                schedule_yearly_post();
                break;
            default:
                // Handle invalid frequency
                break;
        }
    }
}

// Schedule functions for different frequencies
function schedule_twice_daily_post() {
    // Schedule the first post for 9:00 AM
    wp_schedule_single_event(strtotime('09:00:00'), 'twice_daily_post_event_1');

    // Schedule the second post for 3:00 PM
    wp_schedule_single_event(strtotime('15:00:00'), 'twice_daily_post_event_2');
}

function schedule_daily_post() {
    // Schedule the post for 9:00 AM
    wp_schedule_single_event(strtotime('09:00:00'), 'daily_post_event');
}

function schedule_weekly_post() {
    // Schedule the post for every Monday at 9:00 AM
    wp_schedule_single_event(strtotime('next monday 09:00:00'), 'weekly_post_event');
}

function schedule_biweekly_post() {
    // Schedule the post for every other Monday at 9:00 AM
    wp_schedule_single_event(strtotime('next monday 09:00:00'), 'biweekly_post_event');
}

function schedule_monthly_post() {
    // Schedule the post for the first day of each month at 9:00 AM
    wp_schedule_single_event(strtotime('first day of next month 09:00:00'), 'monthly_post_event');
}

function schedule_yearly_post() {
    // Schedule the post for January 1st of each year at 9:00 AM
    wp_schedule_single_event(strtotime('January 1st next year 09:00:00'), 'yearly_post_event');
}

// Add action hooks for scheduled events
add_action('twice_daily_post_event_1', 'create_twice_daily_post_1');
add_action('twice_daily_post_event_2', 'create_twice_daily_post_2');
add_action('daily_post_event', 'create_daily_post');
add_action('weekly_post_event', 'create_weekly_post');
add_action('biweekly_post_event', 'create_biweekly_post');
add_action('monthly_post_event', 'create_monthly_post');
add_action('yearly_post_event', 'create_yearly_post');

// Functions to create posts based on frequency
function create_twice_daily_post_1() {
    // Create post for the first time slot
}

function create_twice_daily_post_2() {
    // Create post for the second time slot
}

function create_daily_post() {
    // Create daily post here
}

function create_weekly_post() {
    // Create weekly post here
}

function create_biweekly_post() {
    // Create biweekly post here
}

function create_monthly_post() {
    // Create monthly post here
}

function create_yearly_post() {
    // Create yearly post here
}
*/
function select_doc_report_frequence_setting_option($selected_option=0) {
    $options = '<option value="">'.__( 'None', 'your-text-domain' ).'</option>';
    $selected = ($selected_option == "yearly") ? 'selected' : '';
    $options .= '<option value="yearly" '.$selected.' />' . __( '每年', 'your-text-domain' ) . '</option>';
    $selected = ($selected_option == "monthly") ? 'selected' : '';
    $options .= '<option value="monthly" '.$selected.' />' . __( '每月', 'your-text-domain' ) . '</option>';
    $selected = ($selected_option == "weekly") ? 'selected' : '';
    $options .= '<option value="weekly" '.$selected.' />' . __( '每週', 'your-text-domain' ) . '</option>';
    $selected = ($selected_option == "daily") ? 'selected' : '';
    $options .= '<option value="daily" '.$selected.' />' . __( '每日', 'your-text-domain' ) . '</option>';
    return $options;
}

// Define a global variable to hold the hook name
$hook_name = '';

function schedule_post_event_callback($args) {
    global $hook_name;

    $interval = $args['interval'];
    $start_time = $args['start_time'];

    // Define the prefix for the hook name
    $hook_prefix = 'my_custom_post_event_';

    // Concatenate the prefix with the start time
    $hook_name = $hook_prefix . $start_time;
    
    // To remove the scheduled event, use the same unique hook name
    //wp_clear_scheduled_hook('my_custom_post_event');
    //wp_clear_scheduled_hook($hook_name);

    // Schedule the event based on the selected interval
    switch ($interval) {
        case 'twice_daily':
            wp_schedule_event($start_time, 'twice_daily', $hook_name, array($args));
            break;
        case 'daily':
            wp_schedule_event($start_time, 'daily', $hook_name, array($args));
            break;
        case 'weekly':
            wp_schedule_event($start_time, 'weekly', $hook_name, array($args));
            break;
        case 'biweekly':
            // Calculate interval for every 2 weeks (14 days)
            wp_schedule_event($start_time, 'biweekly', $hook_name, array($args));
            break;
        case 'monthly':
            wp_schedule_event($start_time, 'monthly', $hook_name, array($args));
            break;
        case 'yearly':
            wp_schedule_event($start_time, 'yearly', $hook_name, array($args));
            break;
        default:
    }
}

// Callback function to add post when scheduled event is triggered
function my_custom_post_event_callback($params) {
    // Add your code to programmatically add a post here
    set_next_todo_and_actions($params);
}
add_action($hook_name, 'my_custom_post_event_callback');
/*
//add_action('wp_ajax_schedule_post_event', 'schedule_post_event_callback');
function schedule_post_event_callback($args) {

    $interval = $args['interval'];
    $start_time = $args['start_time'];

    // Define the prefix for the hook name
    $hook_prefix = 'my_custom_post_event_';

    // Concatenate the prefix with the start time
    $hook_name = $hook_prefix . $start_time;
    
    // To remove the scheduled event, use the same unique hook name
    wp_clear_scheduled_hook('my_custom_post_event');
    wp_clear_scheduled_hook($hook_name);

    // Schedule the event based on the selected interval
    switch ($interval) {
        case 'twice_daily':
            wp_schedule_event($start_time, 'twice_daily', $hook_name, array($args));
            break;
        case 'daily':
            wp_schedule_event($start_time, 'daily', $hook_name, array($args));
            break;
        case 'weekly':
            wp_schedule_event($start_time, 'weekly', $hook_name, array($args));
            break;
        case 'biweekly':
            // Calculate interval for every 2 weeks (14 days)
            wp_schedule_event($start_time, 'biweekly', $hook_name, array($args));
            break;
        case 'monthly':
            wp_schedule_event($start_time, 'monthly', $hook_name, array($args));
            break;
        case 'yearly':
            wp_schedule_event($start_time, 'yearly', $hook_name, array($args));
            break;
        default:
            //wp_send_json_error('Invalid interval');
    }

    //wp_send_json_success('Post scheduled successfully');
}

// Callback function to add post when scheduled event is triggered
function my_custom_post_event_callback($params) {
    // Add your code to programmatically add a post here
    set_next_todo_and_actions($params);

}
//add_action('my_custom_post_event', 'my_custom_post_event_callback');
add_action($hook_name, 'my_custom_post_event_callback');
*/
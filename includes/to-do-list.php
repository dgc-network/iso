<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('to_do_list')) {
    class to_do_list {
        // Class constructor
        public function __construct() {
            add_shortcode( 'to-do-list', array( $this, 'display_shortcode' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_to_do_list_scripts' ) );
            add_action( 'init', array( $this, 'register_todo_post_type' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_todo_settings_metabox' ) );
            //add_action( 'init', array( $this, 'register_action_post_type' ) );

            add_action( 'wp_ajax_get_todo_dialog_data', array( $this, 'get_todo_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_todo_dialog_data', array( $this, 'get_todo_dialog_data' ) );
            add_action( 'wp_ajax_set_todo_dialog_data', array( $this, 'set_todo_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_todo_dialog_data', array( $this, 'set_todo_dialog_data' ) );

            add_action( 'wp_ajax_get_start_job_dialog_data', array( $this, 'get_start_job_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_start_job_dialog_data', array( $this, 'get_start_job_dialog_data' ) );
            add_action( 'wp_ajax_set_start_job_dialog_data', array( $this, 'set_start_job_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_start_job_dialog_data', array( $this, 'set_start_job_dialog_data' ) );

            add_action( 'wp_ajax_del_transaction_log_dialog_data', array( $this, 'del_transaction_log_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_transaction_log_dialog_data', array( $this, 'del_transaction_log_dialog_data' ) );

            add_action( 'init', array( $this, 'schedule_event_and_action' ) );

            // Schedule the cron job if it's not already scheduled
            if (!wp_next_scheduled('iso_helper_daily_action_process_event')) {
                wp_schedule_event(time(), 'daily', 'iso_helper_daily_action_process_event');
            }    
            // Hook the function to the scheduled cron job
            add_action( 'iso_helper_daily_action_process_event', [$this, 'process_authorized_action_posts_daily' ] );
        }

        function enqueue_to_do_list_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);        

            wp_enqueue_script('to-do-list', plugins_url('js/to-do-list.js', __FILE__), array('jquery'), time());
            wp_localize_script('to-do-list', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('to-do-list-nonce'), // Generate nonce
            ));                
        }        

        // Select profile
        function display_select_todo($select_option=false) {
            $iot_messages = new iot_messages();
            ?>
            <select id="select-todo">
                <option value="todo-list" <?php echo ($select_option=="todo-list") ? 'selected' : ''?>><?php echo __( 'Todo List', 'textdomain' );?></option>
                <option value="start-job" <?php echo ($select_option=="start-job") ? 'selected' : ''?>><?php echo __( '啟動表單', 'textdomain' );?></option>
                <option value="action-log" <?php echo ($select_option=="action-log") ? 'selected' : ''?>><?php echo __( 'Sign-off Record', 'textdomain' );?></option>
                <?php if (current_user_can('administrator') || $iot_messages->is_site_with_iot_device()) {?>
                    <option value="iot-devices" <?php echo ($select_option=="iot-devices") ? 'selected' : ''?>><?php echo __( 'IoT devices', 'textdomain' );?></option>
                <?php }?>
                <?php if (current_user_can('administrator')) {?>
                    <option value="cron-events" <?php echo ($select_option=="cron-events") ? 'selected' : ''?>><?php echo __( 'Cron events', 'textdomain' );?></option>
                <?php }?>
            </select>
            <?php
        }

        // Shortcode to display
        function display_shortcode() {
            // Check if the user is logged in
            if (!is_user_logged_in()) user_is_not_logged_in();                
            elseif (is_site_not_configured()) get_NDA_assignment();
            else {
                if (!isset($_GET['_select_todo'])) $_GET['_select_todo'] = 'start-job';

                if ($_GET['_select_todo']=='todo-list') {
                    if (isset($_GET['_todo_id'])) echo $this->display_todo_dialog($_GET['_todo_id']);
                    else echo $this->display_todo_list();
                }
                

                if ($_GET['_select_todo']=='start-job') {
                    if (isset($_GET['_job_id'])) echo $this->display_start_job_dialog($_GET['_job_id']);
                    else echo $this->display_start_job_list();
                }
                
                if ($_GET['_select_todo']=='action-log') {
                    if (isset($_GET['_log_id'])) echo $this->display_transaction_log_dialog($_GET['_log_id']);
                    else echo $this->display_transaction_log_list();                    
                }

                if ($_GET['_select_todo']=='cron-events') {
                    ?><script>window.location.replace("/wp-admin/tools.php?page=wp-crontrol");</script><?php
                }

                $iot_messages = new iot_messages();
                if ($_GET['_select_todo']=='iot-devices') {
                    if (isset($_GET['_device_id'])) echo $iot_messages->display_iot_device_dialog($_GET['_device_id']);
                    else echo $iot_messages->display_iot_device_list();
                }                
            }
        }

        // Create a todo Post Type
        function register_todo_post_type() {
            $labels = array(
                'menu_name'          => _x('Todo-list', 'admin menu', 'textdomain'),
            );        
            $args = array(
                'labels'             => $labels,
                'public'             => true,
            );
            register_post_type('todo', $args);
        }
        
        function add_todo_settings_metabox() {
            add_meta_box(
                'todo_settings_id',
                'Todo Settings',
                array($this, 'todo_settings_content'),
                'todo',
                'normal',
                'high'
            );
        }
        
        function todo_settings_content($post) {
            $site_id = esc_attr(get_post_meta($post->ID, 'site_id', true));
            $doc_id = esc_attr(get_post_meta($post->ID, 'doc_id', true));
            $prev_report_id = esc_attr(get_post_meta($post->ID, 'prev_report_id', true));
            $todo_due = esc_attr(get_post_meta($post->ID, 'todo_due', true));
            $submit_user = esc_attr(get_post_meta($post->ID, 'submit_user', true));
            $submit_action = esc_attr(get_post_meta($post->ID, 'submit_action', true));
            $submit_time = esc_attr(get_post_meta($post->ID, 'submit_time', true));
            $next_job = esc_attr(get_post_meta($post->ID, 'next_job', true));
            ?>
            <label for="site_id"> site_id: </label>
            <input type="text" id="site_id" name="site_id" value="<?php echo $site_id;?>" style="width:100%" >
            <label for="doc_id"> doc_id: </label>
            <input type="text" id="doc_id" name="doc_id" value="<?php echo $doc_id;?>" style="width:100%" >
            <label for="prev_report_id"> prev_report_id: </label>
            <input type="text" id="prev_report_id" name="prev_report_id" value="<?php echo $prev_report_id;?>" style="width:100%" >
            <label for="todo_due"> todo_due: </label>
            <input type="text" id="todo_due" name="todo_due" value="<?php echo $todo_due;?>" style="width:100%" >
            <label for="submit_user"> submit_user: </label>
            <input type="text" id="submit_user" name="submit_user" value="<?php echo $submit_user;?>" style="width:100%" >
            <label for="submit_action"> submit_action: </label>
            <input type="text" id="submit_action" name="submit_action" value="<?php echo $submit_action;?>" style="width:100%" >
            <label for="submit_time"> submit_time: </label>
            <input type="text" id="submit_time" name="submit_time" value="<?php echo $submit_time;?>" style="width:100%" >
            <label for="next_job"> next_job: </label>
            <input type="text" id="next_job" name="next_job" value="<?php echo $next_job;?>" style="width:100%" >
            <?php
        }
        
        function display_todo_list() {
            ?>
            <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( 'Todo List', 'textdomain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $this->display_select_todo('todo-list');?></div>
                    <div style="text-align: right">
                        <input type="text" id="search-todo" style="display:inline" placeholder="<?php echo __( 'Search...', 'textdomain' );?>" />
                    </div>
                </div>

                <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'No.', 'textdomain' );?></th>
                            <th><?php echo __( 'Document', 'textdomain' );?></th>
                            <th><?php echo __( 'Due date', 'textdomain' );?></th>
                            <th><?php echo __( 'Actions', 'textdomain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_todo_list_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages

                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $todo_id = get_the_ID();
                            $todo_title = get_the_title();
                            $todo_due = get_post_meta($todo_id, 'todo_due', true);
                            if ($todo_due < time()) $todo_due_color='color:red;';
                            $todo_due = wp_date(get_option('date_format'), $todo_due);
                            $doc_id = get_post_meta($todo_id, 'doc_id', true);
                            $doc_title = get_the_title($doc_id);

                            //if ($report_id) $doc_title .= '(#'.$report_id.')';
                            //if ($todo_id) $doc_title .= '(#'.$todo_id.')';
                            $action_titles = $this->get_action_titles_by_doc_id($doc_id);
                            ?>
                            <tr id="edit-todo-<?php echo esc_attr($todo_id);?>">
                                <td style="text-align:center;"><?php echo esc_html($todo_id);?></td>
                                <td><?php echo esc_html($doc_title);?></td>
                                <td style="text-align:center; <?php echo $todo_due_color?>"><?php echo esc_html($todo_due);?></td>
                                <td style="text-align:center;"><?php echo esc_html($action_titles);?></td>
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
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    echo '</div>';
                ?>
                </fieldset>
            </div>
            <?php
        }

        function retrieve_todo_list_data($paged = 1){
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            $user_action_ids = get_user_meta($current_user_id, 'user_action_ids', true);
            if (!is_array($user_action_ids)) $user_action_ids = array();
            $user_doc_ids = []; // Array to store collected doc_id values
            if (!empty($user_action_ids) && is_array($user_action_ids)) {
                foreach ($user_action_ids as $action_id) {
                    $doc_id = get_post_meta($action_id, 'doc_id', true);
                    if (!empty($doc_id)) {
                        $user_doc_ids[] = $doc_id;
                    }
                }
            }

            // Define the WP_Query arguments
            $args = array(
                'post_type'      => 'todo',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
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
                'orderby' => 'date',
                'order' => 'DESC',
            );

            if (!is_site_admin()||current_user_can('administrator')) {
                // Initialize the meta_query array
                $meta_query = array('relation' => 'OR');

                // Check if $user_doc_ids is not an empty array and add it to the meta_query
                if (!empty($user_doc_ids)) {
                    $meta_query[] = array(
                        'key'     => 'doc_id',
                        'value'   => $user_doc_ids,
                        'compare' => 'IN',
                    );
                }

                // If $meta_query has more than just the relation, add it to $args
                if (count($meta_query) > 1) {
                    $args['meta_query'][] = $meta_query;
                }
            }

            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['s'] = $search_query;
            }
            $query = new WP_Query($args);

            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Remove the initial search query
                unset($args['s']);
                // Add meta query for searching across all meta keys
                $meta_query_all_keys = array('relation' => 'OR');
                $meta_keys = get_post_type_meta_keys('todo');
                foreach ($meta_keys as $meta_key) {
                    $meta_query_all_keys[] = array(
                        'key'     => $meta_key,
                        'value'   => $search_query,
                        'compare' => 'LIKE',
                    );
                }
                $args['meta_query'][] = $meta_query_all_keys;
                $query = new WP_Query($args);
            }
            return $query;
        }

        function get_previous_todo_id($current_todo_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            $user_action_ids = get_user_meta($current_user_id, 'user_action_ids', true);
            if (!is_array($user_action_ids)) $user_action_ids = array();
            $user_doc_ids = []; // Array to store collected doc_id values
            if (!empty($user_action_ids) && is_array($user_action_ids)) {
                foreach ($user_action_ids as $action_id) {
                    $doc_id = get_post_meta($action_id, 'doc_id', true);
                    if (!empty($doc_id)) {
                        $user_doc_ids[] = $doc_id;
                    }
                }
            }

            // Initialize the meta_query
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key'     => 'todo_due',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key'     => 'submit_user',
                    'compare' => 'NOT EXISTS',
                ),
            );
        
            // Add additional conditions if the user is not a site admin or administrator
            if (!is_site_admin() || current_user_can('administrator')) {
                if (!empty($user_doc_ids)) {
                    $meta_query[] = array(
                        'key'     => 'doc_id',
                        'value'   => $user_doc_ids,
                        'compare' => 'IN',
                    );
                }
            }
        
            $args = array(
                'post_type'      => 'todo',
                'posts_per_page' => 1,
                'meta_query'     => $meta_query,     // Apply the meta query
                'orderby'        => 'date',          // Order by post date
                'order'          => 'ASC',           // Ascending order
                'date_query'     => array(
                    array(
                        'after' => get_post_field('post_date', $current_todo_id), // Get posts after the current report's date
                        'inclusive' => false,
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }
        
        function get_next_todo_id($current_todo_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            $user_action_ids = get_user_meta($current_user_id, 'user_action_ids', true);
            if (!is_array($user_action_ids)) $user_action_ids = array();
            $user_doc_ids = []; // Array to store collected doc_id values
            if (!empty($user_action_ids) && is_array($user_action_ids)) {
                foreach ($user_action_ids as $action_id) {
                    $doc_id = get_post_meta($action_id, 'doc_id', true);
                    if (!empty($doc_id)) {
                        $user_doc_ids[] = $doc_id;
                    }
                }
            }
        
            // Initialize the meta_query
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key'     => 'todo_due',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key'     => 'submit_user',
                    'compare' => 'NOT EXISTS',
                ),
            );
        
            // Add additional conditions if the user is not a site admin or administrator
            if (!is_site_admin() || current_user_can('administrator')) {
                if (!empty($user_doc_ids)) {
                    $meta_query[] = array(
                        'key'     => 'doc_id',
                        'value'   => $user_doc_ids,
                        'compare' => 'IN',
                    );
                }
            }
        
            $args = array(
                'post_type'      => 'todo',
                'posts_per_page' => 1,
                'meta_query'     => $meta_query,     // Apply the meta query
                'orderby'        => 'date',          // Order by post date
                'order'          => 'DESC',          // Descending order
                'date_query'     => array(
                    array(
                        'before' => get_post_field('post_date', $current_todo_id), // Get posts before the current report's date
                        'inclusive' => false,
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function display_todo_dialog($todo_id=false) {
            ob_start();
            $documents_class = new display_documents();
            $prev_todo_id = $this->get_previous_todo_id($todo_id); // Fetch the previous ID
            $next_todo_id = $this->get_next_todo_id($todo_id);     // Fetch the next ID
            $doc_id = get_post_meta($todo_id, 'doc_id', true);
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo esc_html(__( 'Todo: ', 'textdomain' ).get_the_title($doc_id));?></h2>
            <input type="hidden" id="todo-id" value="<?php echo $todo_id;?>" />
            <input type="hidden" id="prev-todo-id" value="<?php echo esc_attr($prev_todo_id); ?>" />
            <input type="hidden" id="next-todo-id" value="<?php echo esc_attr($next_todo_id); ?>" />
            <fieldset>
            <?php
                $prev_todo_id = get_post_meta($todo_id, 'prev_todo_id', true);
                $summary_todos = get_post_meta($todo_id, 'summary_todos', true);
                // Figure out the summary-job
                if (!empty($summary_todos) && is_array($summary_todos)) {
                    //if ($prev_todo_id) $doc_id = get_post_meta($prev_todo_id, 'doc_id', true);
                    $params = array(
                        'doc_id'         => $doc_id,
                        'summary_todos'  => $summary_todos,
                    );
                    $documents_class->get_doc_report_inner_list($params);
                } else {
                    if ($prev_todo_id) {
                        $documents_class->get_doc_field_contains(array('todo_id' => $prev_todo_id));
                    } else {
                        $documents_class->get_doc_field_contains(array('todo_id' => $todo_id));
                    }
                }
            ?>
            <hr>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php
                        $query = $this->retrieve_todo_action_list_data($todo_id);
                        if ($query->have_posts()) {
                            while ($query->have_posts()) : $query->the_post();
                                $action_id = get_the_ID();
                                $action_title = get_the_title();
                                echo '<input type="button" id="todo-dialog-button-'.$action_id.'" value="'.$action_title.'" style="margin:5px;" />';
                            endwhile;
                            wp_reset_postdata();
                        }    
                    ?>
                </div>
                <div style="text-align: right">
                    <input type="button" id="todo-dialog-exit" value="Exit" style="margin:5px;" />
                </div>
            </div>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }
        
        function get_todo_dialog_data() {
            $response = array();
            if (isset($_POST['_todo_id'])) {
                $todo_id = sanitize_text_field($_POST['_todo_id']);
                $doc_id = get_post_meta($todo_id, 'doc_id', true);
                $documents_class = new display_documents();
                $response['doc_field_keys'] = $documents_class->get_doc_field_keys($doc_id);
                $items_class = new embedded_items();
                $response['embedded_item_keys'] = $items_class->get_embedded_item_keys($doc_id);
            }
            wp_send_json($response);
        }
        
        function set_todo_dialog_data() {
            $response = array();
            if( isset($_POST['_action_id']) ) {
                $action_id = sanitize_text_field($_POST['_action_id']);
                $this->set_todo_data_and_go_next($action_id);
            }
            wp_send_json($response);
        }
        
        function set_todo_data_and_go_next($action_id=false, $user_id=false, $is_default=false) {
            // action button is clicked
            if (!$user_id) $user_id = get_current_user_id();
            $todo_id = get_post_meta($action_id, 'todo_id', true);
            $next_job = get_post_meta($action_id, 'next_job', true);

            // Update the post meta
            $documents_class = new display_documents();
            $documents_class->update_doc_field_contains(
                array('report_id' => $todo_id, 'user_id' => $user_id, 'is_default' => $is_default)
            );

            // Delete the summary_to_id if it exists
            $doc_id = get_post_meta($todo_id, 'doc_id', true);
            $is_summary_report = get_post_meta($doc_id, 'is_summary_report', true);
            if ($is_summary_report) delete_post_meta($todo_id, 'summary_to_id');

            // Update current todo
            update_post_meta($todo_id, 'submit_user', $user_id );
            update_post_meta($todo_id, 'submit_action', $action_id );
            update_post_meta($todo_id, 'submit_time', time() );
            update_post_meta($todo_id, 'next_job', $next_job );
            update_post_meta($todo_id, 'todo_status', $next_job );

            // set next todo and actions
            $params = array(
                'user_id' => $user_id,
                'action_id' => $action_id,
                'prev_todo_id' => $todo_id,
            );

            if ($next_job>0) $this->proceed_to_next_job($params);
        }
        
        // start-job
        function display_start_job_list() {
            ?>
            <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( 'Start Job', 'textdomain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $this->display_select_todo('start-job');?></div>
                    <div style="text-align: right">
                        <input type="text" id="search-start-job" style="display:inline" placeholder="<?php echo __( 'Search...', 'textdomain' );?>" />
                    </div>
                </div>

                <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'No.', 'textdomain' );?></th>
                            <th><?php echo __( 'Document', 'textdomain' );?></th>
                            <th><?php echo __( 'Action', 'textdomain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_start_job_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages

                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $doc_id = get_the_ID();
                            $doc_title = get_the_title();
                            $doc_number = get_post_meta($doc_id, 'doc_number', true);
                            $action_titles = $this->get_action_titles_by_doc_id($doc_id);
                            ?>
                            <tr id="edit-start-job-<?php echo $doc_id;?>">
                                <td style="text-align:center;"><?php echo esc_html($doc_number);?></td>
                                <td><?php echo esc_html($doc_title); ?></td>
                                <td style="text-align:center;"><?php echo esc_html($action_titles);?></td>
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
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    echo '</div>';
                ?>
                </fieldset>
            </div>
            <?php
        }

        function get_action_titles_by_doc_id($doc_id) {
            $titles = [];
        
            $query = new WP_Query([
                'post_type'      => 'action',
                'posts_per_page' => -1,
                'meta_query'     => [
                    [
                        'key'   => 'doc_id',
                        'value' => $doc_id,
                    ],
                ],
                'fields'         => 'ids', // Only retrieve post IDs for performance
            ]);
        
            if ($query->have_posts()) {
                foreach ($query->posts as $post_id) {
                    $titles[] = get_the_title($post_id);
                }
            }
        
            wp_reset_postdata();
            // Return titles as a comma-separated string
            return implode(', ', $titles);
        }

        function retrieve_start_job_data($paged = 1, $current_user_id = false, $search_query = false) {
            if (!$current_user_id) $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
        
            $user_action_ids = get_user_meta($current_user_id, 'user_action_ids', true);
            if (!is_array($user_action_ids)) $user_action_ids = array();
            $user_doc_ids = []; // Array to store collected doc_id values
            if (!empty($user_action_ids) && is_array($user_action_ids)) {
                foreach ($user_action_ids as $action_id) {
                    $doc_id = get_post_meta($action_id, 'doc_id', true);
                    if (!empty($doc_id)) {
                        $user_doc_ids[] = $doc_id;
                    }
                }
            }

            // Prepare WP_Query args for "document" post type
            $args = [
                'post_type'      => 'document',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => [
                    'relation' => 'AND',
                    [
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    ],
                    [
                        'key'     => 'is_doc_report',
                        'value'   => 1,
                    ],
                    array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'not_start_job',
                            'compare' => 'NOT EXISTS',
                        ),
                        array(
                            'key'     => 'not_start_job',
                            'value'   => 0,
                        )
                    ),
                ],
                'orderby' => 'date',
                'order' => 'DESC',
            ];
        
            // Filter only documents that match doc_id from actions
            if (empty($user_doc_ids)) {
                return new WP_Query(['post__in' => [0]]); // Ensures an empty query
            } else {
                $args['post__in'] = $user_doc_ids;
            }
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1;
            }
        
            if (!$search_query && isset($_GET['_search'])) {
                $search_query = sanitize_text_field($_GET['_search']);
            }
        
            $query = new WP_Query($args);
        
            return $query;
        }

        function get_previous_job_id($current_job_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            $user_action_ids = get_user_meta($current_user_id, 'user_action_ids', true);
            if (!is_array($user_action_ids)) $user_action_ids = array();
            $user_doc_ids = []; // Array to store collected doc_id values
            if (!empty($user_action_ids) && is_array($user_action_ids)) {
                foreach ($user_action_ids as $action_id) {
                    $doc_id = get_post_meta($action_id, 'doc_id', true);
                    if (!empty($doc_id)) {
                        $user_doc_ids[] = $doc_id;
                    }
                }
            }

            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => 1,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    ),
                    array(
                        'key'     => 'is_doc_report',
                        'value'   => 1,
                    ),
                ),
                'orderby'        => 'date',          // Order by post date
                'order'          => 'ASC',           // Ascending order
                'date_query'     => array(
                    array(
                        'after' => get_post_field('post_date', $current_job_id), // Get posts after the current report's date
                        'inclusive' => false,
                    ),
                ),

            );
            if (!is_site_admin()||current_user_can('administrator')) {
                $args['post__in'] = $user_doc_ids; // Array of document post IDs
            }

            $query = new WP_Query($args);
        
            // Return the previous document ID or null if no previous document is found
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function get_next_job_id($current_job_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            $user_action_ids = get_user_meta($current_user_id, 'user_action_ids', true);
            if (!is_array($user_action_ids)) $user_action_ids = array();
            $user_doc_ids = []; // Array to store collected doc_id values
            if (!empty($user_action_ids) && is_array($user_action_ids)) {
                foreach ($user_action_ids as $action_id) {
                    $doc_id = get_post_meta($action_id, 'doc_id', true);
                    if (!empty($doc_id)) {
                        $user_doc_ids[] = $doc_id;
                    }
                }
            }

            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => 1,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    ),
                    array(
                        'key'     => 'is_doc_report',
                        'value'   => 1,
                    ),
                ),
                'orderby'        => 'date',          // Order by post date
                'order'          => 'DESC',          // Descending order
                'date_query'     => array(
                    array(
                        'before' => get_post_field('post_date', $current_job_id), // Get posts before the current report's date
                        'inclusive' => false,
                    ),
                ),
            );
            if (!is_site_admin()||current_user_can('administrator')) {
                $args['post__in'] = $user_doc_ids; // Array of document post IDs
            }

            $query = new WP_Query($args);
        
            // Return the next document ID or null if no next document is found
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function display_start_job_dialog($doc_id) {
            ob_start();
            $prev_job_id = $this->get_previous_job_id($doc_id); // Fetch the previous ID
            $next_job_id = $this->get_next_job_id($doc_id);     // Fetch the next ID
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo esc_html(__( 'Start Job: ', 'textdomain' ).get_the_title($doc_id));?></h2>
            <input type="hidden" id="job-id" value="<?php echo $doc_id;?>" />
            <input type="hidden" id="prev-job-id" value="<?php echo esc_attr($prev_job_id); ?>" />
            <input type="hidden" id="next-job-id" value="<?php echo esc_attr($next_job_id); ?>" />
            <fieldset>
                <?php
                $documents_class = new display_documents();
                $documents_class->get_doc_field_contains(array('doc_id' => $doc_id));
                $doc_title = get_the_title($doc_id);
                $content = (isset($_GET['_prompt'])) ? generate_content($doc_title.' '.$_GET['_prompt']) : '';
                ?>
                <br><br>
                <div class="content">
                    <?php echo $content;?>
                    <div style="margin:1em; padding:10px; border:solid; border-radius:1.5rem;">
                        <input type="text" id="ask-gemini" placeholder="<?php echo __( 'Ask Gemini', 'textdomain' );?>" class="text ui-widget-content ui-corner-all" />
                    </div>
                </div>            
            <hr>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                <?php
                    $profiles_class = new display_profiles();
                    $query = $profiles_class->retrieve_site_action_data(0, $doc_id);
                    if ($query->have_posts()) {
                        while ($query->have_posts()) : $query->the_post();
                            $action_id = get_the_ID();
                            $action_title = get_the_title();
                            echo '<input type="button" id="start-job-dialog-button-'.$action_id.'" value="'.$action_title.'" style="margin:5px;" />';
                        endwhile;
                        wp_reset_postdata();
                    }
                ?>
                </div>
                <div style="text-align: right">
                    <input type="button" id="exit-start-job" value="<?php echo __( 'Exit', 'textdomain' );?>" style="margin:5px;" />
                </div>
            </div>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }
        
        function get_start_job_dialog_data() {
            $response = array();
            if (isset($_POST['_job_id'])) {
                $job_id = sanitize_text_field($_POST['_job_id']);
                $documents_class = new display_documents();
                $response['doc_field_keys'] = $documents_class->get_doc_field_keys($job_id);
                $items_class = new embedded_items();
                $response['embedded_item_keys'] = $items_class->get_embedded_item_keys($job_id);
            }
            wp_send_json($response);
        }
        
        function set_start_job_dialog_data() {
            $response = array();
            if( isset($_POST['_action_id']) ) {
                $action_id = sanitize_text_field($_POST['_action_id']);
                $this->set_start_job_and_go_next($action_id);
            }
            wp_send_json($response);
        }
        
        function set_start_job_and_go_next($action_id=false, $user_id=false, $is_default=false) {
            // Run a set_start_job_and_go_next() from schedule_event_callback($params).
            // Action button is clicked
            if (!$user_id) $user_id = get_current_user_id();
            $site_id = get_user_meta($user_id, 'site_id', true);
            $doc_id = get_post_meta($action_id, 'doc_id', true);
            $next_job = get_post_meta($action_id, 'next_job', true);
            $post_title = isset($_POST['_post_title']) ? sanitize_text_field($_POST['_post_title']) : '';
            $post_content = isset($_POST['_post_content']) ? sanitize_text_field($_POST['_post_content']) : '';
            $post_number = isset($_POST['_post_number']) ? sanitize_text_field($_POST['_post_number']) : '';

            // Create a new todo for current action
            $new_post = array(
                'post_type'     => 'todo',
                'post_title'    => $post_title,
                'post_content'  => $post_content,
                'post_status'   => 'publish',
                'post_author'   => $user_id,
            );    
            $new_todo_id = wp_insert_post($new_post);
            update_post_meta($new_todo_id, 'site_id', $site_id );
            update_post_meta($new_todo_id, 'doc_id', $doc_id);
            update_post_meta($new_todo_id, 'submit_user', $user_id );
            update_post_meta($new_todo_id, 'submit_action', $action_id );
            update_post_meta($new_todo_id, 'submit_time', time() );
            update_post_meta($new_todo_id, 'next_job', $next_job );
            update_post_meta($new_todo_id, 'todo_status', $next_job);

            // Update the doc-field meta for new doc-report
            $documents_class = new display_documents();
            $documents_class->update_doc_field_contains(
                array('report_id' => $new_todo_id, 'user_id' => $user_id, 'is_default' => $is_default)
            );

            $is_embedded_doc = get_post_meta($doc_id, 'is_embedded_doc', true);
            if ($is_embedded_doc) {
                $embedded_doc_title = get_the_title($doc_id);
                if (stripos($embedded_doc_title, 'customer') !== false || 
                    stripos($embedded_doc_title, '客戶') !== false || 
                    stripos($embedded_doc_title, '顧客') !== false || 
                    stripos($embedded_doc_title, 'vendor') !== false || 
                    stripos($embedded_doc_title, '供應商') !== false || 
                    stripos($embedded_doc_title, '廠商') !== false) {
                    // Code to execute if $embedded_doc_title includes 'customer' or 'vendor', case-insensitive
                    $documents_class->update_site_profile($new_todo_id);
                }
            }

            // set next todo and actions
            $params = array(
                'user_id' => $user_id,
                'action_id' => $action_id,
                'prev_todo_id' => $new_todo_id,
            );

            if ($next_job>0) $this->proceed_to_next_job($params);
        }
        
        // proceed-to-next-job
        function proceed_to_next_job($params=array()) {
            // 1. From set_todo_data_and_go_next(), create a next_todo based on the $args['action_id'], $args['user_id'] and $args['prev_report_id']
            // 2. From set_start_job_and_go_next(), create a next_todo based on the $args['action_id'], $args['user_id'] and $args['prev_report_id']
            $user_id = isset($params['user_id']) ? $params['user_id'] : get_current_user_id();
            $user_id = ($user_id) ? $user_id : 1;
            $params['user_id'] = $user_id;
            $action_id = isset($params['action_id']) ? $params['action_id'] : 0;

            // Find the next_job, next_leadtime
            if ($action_id > 0) {
                $next_job      = get_post_meta($action_id, 'next_job', true);
                $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
                if (empty($next_leadtime)) $next_leadtime=86400;
                $params['next_job'] = $next_job;
                $params['next_leadtime'] = $next_leadtime;
            }
        
            if ($next_job>0) {
                // Get the API endpoint
                $api_endpoint = get_post_meta($next_job, 'api_endpoint', true);
                if ($api_endpoint) {
                    if (!preg_match('/^https?:\/\//', $api_endpoint)) {
                        $api_endpoint = home_url($api_endpoint);
                    }
                    error_log('API endpoint: ' . $api_endpoint);
    
                    // Get a valid JWT token
                    $doc_category = get_post_meta($next_job, 'doc_category', true);
                    $jwt_token = get_valid_jwt_token($doc_category);
                    if (!$jwt_token) {
                        error_log('No valid JWT token available.');
                        return;
                    }

                    // Make the API request
                    $response = wp_remote_post($api_endpoint, [
                        'method'    => 'POST',
                        'headers'   => [
                            'Content-Type'  => 'application/json',
                            'Authorization' => 'Bearer ' . get_option('jwt_token', ''),
                        ],
                        'body'      => wp_json_encode($params),
                        'data_format' => 'body',
                    ]);

                    // Handle response
                    if (is_wp_error($response)) {
                        error_log('API Error: ' . $response->get_error_message());
                    } else {
                        $response_code = wp_remote_retrieve_response_code($response);
                        $response_body = wp_remote_retrieve_body($response);
                        
                        if ($response_code === 200) {
                            error_log('Message Sent Successfully: ' . $response_body);
                        } else {
                            error_log('API Response Error: ' . $response_body);
                        }
                    }
                } else {
                    $next_todo_id = $this->create_next_todo_and_actions($params);
                }
            }
        }

        function create_next_todo_and_actions($params=array()) {
            
            $user_id = isset($params['user_id']) ? $params['user_id'] : get_current_user_id();
            $action_id = isset($params['action_id']) ? $params['action_id'] : 0;
            $prev_todo_id = isset($params['prev_todo_id']) ? $params['prev_todo_id'] : 0;
            $next_job = isset($params['next_job']) ? $params['next_job'] : 0;
            $next_leadtime = isset($params['next_leadtime']) ? $params['next_leadtime'] : 0;
            $site_id = get_user_meta($user_id, 'site_id', true);

            // Update the summary-job
            $new_todo_id = get_post_meta($next_job, 'summary_todo_id', true);
            if (empty($new_todo_id)) {
                // Create a new Todo for next_job
                $new_post = array(
                    'post_type'     => 'todo',
                    'post_status'   => 'publish',
                    'post_author'   => $user_id,
                );    
                $new_todo_id = wp_insert_post($new_post);
                //$summary_todo_id = $new_todo_id;
                update_post_meta($new_todo_id, 'todo_due', time()+$next_leadtime );
                update_post_meta($new_todo_id, 'site_id', $site_id );
                update_post_meta($new_todo_id, 'doc_id', $next_job );
    
                // Update the post meta
                $not_start_job = get_post_meta($next_job, 'not_start_job', true);
                if ($not_start_job) {
                    update_post_meta($new_todo_id, 'prev_todo_id', $prev_todo_id );
                }

                // Create the new Actions for new Todo
                $profiles_class = new display_profiles();
                $query = $profiles_class->retrieve_site_action_data(0, $next_job);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $action_id = get_the_ID();
                        $new_post = array(
                            'post_type'     => 'action',
                            'post_title'    => get_the_title(),
                            'post_content'  => get_the_content(),
                            'post_status'   => 'publish',
                            'post_author'   => $user_id,
                        );    
                        $new_action_id = wp_insert_post($new_post);
                        $new_next_job = get_post_meta($action_id, 'next_job', true);
                        $new_next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
                        update_post_meta($new_action_id, 'todo_id', $new_todo_id);
                        update_post_meta($new_action_id, 'next_job', $new_next_job);
                        update_post_meta($new_action_id, 'next_leadtime', $new_next_leadtime);
                        
                        //Update the action_authorized_ids
                        $action_authorized_ids = $profiles_class->is_action_authorized($action_id);
                        if ($action_authorized_ids){
                            update_post_meta($new_action_id, 'action_authorized_ids', $action_authorized_ids);
                        }

                        // Notice the persons in charge the action
                        $this->notice_the_responsible_persons($action_id);

                    endwhile;
                    wp_reset_postdata();
                }
            }

            $is_summary_report = get_post_meta($next_job, 'is_summary_report', true);
            if ($is_summary_report) {
                $summary_todos = get_post_meta($new_todo_id, 'summary_todos', true);
                if (!empty($summary_todos) && is_array($summary_todos)) {
                    $summary_todos[] = $prev_todo_id;
                    update_post_meta($new_todo_id, 'summary_todos', $summary_todos);
                } else {
                    update_post_meta($new_todo_id, 'summary_todos', array($prev_todo_id));
                }
                update_post_meta($next_job, 'summary_todo_id', $new_todo_id);
            }    

            return $new_todo_id;

        }

        // Notice the persons in charge the job
        function notice_the_responsible_persons($action_id=0) {
            $todo_id = get_post_meta($action_id, 'todo_id', true);
            $doc_id = get_post_meta($todo_id, 'doc_id', true);
            $next_leadtime = get_post_meta($todo_id, 'next_leadtime', true);
            if (empty($next_leadtime)) $next_leadtime=86400;
            $due_date = time() + $next_leadtime;
            $text_message = sprintf(
                __('The document %s has a job that needs to be signed-off before %s. You can click the link below to view the document.', 'textdomain'),
                get_the_title($doc_id),
                wp_date('Y-m-d', $due_date)
            );            
            $link_uri = home_url().'/to-do-list/?_select_todo=todo-list&_todo_id='.$todo_id;

            $args = array(
                'meta_query'     => array(
                    array(
                        'key'     => 'user_action_ids',
                        'value'   => $action_id,
                        'compare' => 'LIKE',
                    ),
                ),
            );
            $user_query = new WP_User_Query($args);
            $users = $user_query->get_results();
            foreach ($users as $user) {
                $header_contents = array(
                    array(
                        'type' => 'text',
                        'text' => 'Hello, ' . $user->display_name,
                        'size' => 'lg',
                        'weight' => 'bold',
                    ),
                );

                $body_contents = array(
                    array(
                        'type' => 'text',
                        'text' => $text_message,
                        'wrap' => true,
                    ),
                );

                $footer_contents = array(
                    array(
                        'type' => 'button',
                        'action' => array(
                            'type' => 'uri',
                            'label' => get_the_title($doc_id),
                            'uri' => $link_uri, // Use the desired URI
                        ),
                        'style' => 'primary',
                        'margin' => 'sm',
                    ),
                );

                $line_bot_api = new line_bot_api();
                $line_bot_api->send_flex_message([
                    'to' => get_user_meta($user->ID, 'line_user_id', TRUE),
                    'header_contents' => $header_contents,
                    'body_contents' => $body_contents,
                    'footer_contents' => $footer_contents,
                ]);
            }
        }

        // Notice the persons in site
        function notice_the_persons_in_site($site_id=0, $text_message='', $link_uri='') {
            $args = array(
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
            );
            $user_query = new WP_User_Query($args);
            $users = $user_query->get_results();
            foreach ($users as $user) {

                $header_contents = array(
                    array(
                        'type' => 'text',
                        'text' => 'Hello, ' . $user->display_name,
                        'size' => 'lg',
                        'weight' => 'bold',
                    ),
                );

                $body_contents = array(
                    array(
                        'type' => 'text',
                        'text' => $text_message,
                        'wrap' => true,
                    ),
                );

                $footer_contents = array(
                    array(
                        'type' => 'button',
                        'action' => array(
                            'type' => 'uri',
                            'label' => __( 'Click me!', 'textdomain' ),
                            'uri' => $link_uri, // Use the desired URI
                        ),
                        'style' => 'primary',
                        'margin' => 'sm',
                    ),
                );

                $line_bot_api = new line_bot_api();
                $line_bot_api->send_flex_message([
                    'to' => get_user_meta($user->ID, 'line_user_id', TRUE),
                    'header_contents' => $header_contents,
                    'body_contents' => $body_contents,
                    'footer_contents' => $footer_contents,
                ]);
            }
        }

        // Register action post type
        function register_action_post_type() {
            $labels = array(
                'menu_name'     => _x('Actions', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'action', $args );
        }

        function retrieve_todo_action_list_data($todo_id=false) {
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
        
        // transaction_log
        function display_transaction_log_list() {
            ob_start();
            $query = $this->retrieve_transaction_log_data(0);
            $total_posts = $query->found_posts;
            ?>
            <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( 'Sign-off Record', 'textdomain' );?></h2>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $this->display_select_todo('action-log');?></div>
                    <div style="text-align: right">
                        <input type="text" id="search-log" style="display:inline" placeholder="<?php echo __( 'Search...', 'textdomain' );?>" />
                    </div>
                </div>
                <?php echo $this->get_transaction_log_inner_list();?>
                <div style="background-color:lightblue; text-align:center;">
                    <?php echo __( 'Total Submissions: ', 'textdomain' );?> <?php echo $total_posts;?>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        function get_transaction_log_inner_list($todo_ids=array()) {
            ob_start();
            ?>
            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Time', 'textdomain' );?></th>
                            <th><?php echo __( 'Document', 'textdomain' );?></th>
                            <th><?php echo __( 'Action', 'textdomain' );?></th>
                            <th><?php echo __( 'User', 'textdomain' );?></th>
                            <th><?php echo __( 'Status', 'textdomain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_transaction_log_data($paged, $todo_ids);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $todo_id = get_the_ID();
                            $log_title = get_the_title();

                            $submit_action = get_post_meta($todo_id, 'submit_action', true);
                            if ($submit_action) {
                                $doc_id = get_post_meta($todo_id, 'doc_id', true);
                                $log_title = get_the_title($doc_id).'(#'.$todo_id.$log_title.')';
                                $action_title = get_the_title($submit_action);
                                $next_job = get_post_meta($submit_action, 'next_job', true);
                                $next_job_title = get_the_title($next_job);
                            } else {
                                $action_title = get_post_meta($todo_id, 'action_title', true);
                                $next_job_title = '';
                            }
                            $submit_time = get_post_meta($todo_id, 'submit_time', true);
                            $submit_user = get_post_meta($todo_id, 'submit_user', true);
                            $user_data = get_userdata( $submit_user );
                            ?>
                            <tr id="edit-action-log<?php esc_attr(the_ID()); ?>">
                                <td style="text-align:center;"><?php echo wp_date(get_option('date_format'), $submit_time).' '.wp_date(get_option('time_format'), $submit_time);?></td>
                                <td><?php echo esc_html($log_title);?></td>
                                <td style="text-align:center;"><?php echo esc_html($action_title);?></td>
                                <td style="text-align:center;"><?php echo esc_html($user_data->display_name);?></td>
                                <td style="text-align:center;"><?php echo esc_html($next_job_title);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function retrieve_transaction_log_data($paged=1, $todo_ids=array()) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true); // Get current user's site_id

            $args = array(
                'post_type'      => 'todo',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
/*
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'submit_user',
                        'compare' => 'EXISTS',
                    ),
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    ),
                ),
*/                
                'orderby'        => 'meta_value',
                'meta_key'       => 'submit_time',
                'order'          => 'DESC',
            );

            // If paged is 0, retrieve all matching posts
            if ($paged == 0) {
                $args['posts_per_page'] = -1;
            }

            // Ensure $todo_ids is valid before applying post__in
            if (!empty($todo_ids) && is_array($todo_ids)) {
                $todo_ids = array_map('absint', $todo_ids); // Ensure IDs are integers
                $args['post__in'] = $todo_ids;
            }

            // Check if meta_query is causing issues
            if (empty($todo_ids)) {
                $args['meta_query'] = array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'submit_user',
                        'compare' => 'EXISTS',
                    ),
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    ),
                );
            }

            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['s'] = $search_query;
            }
            $query = new WP_Query($args);

            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Remove the initial search query
                unset($args['s']);
                // Add meta query for searching across all meta keys
                $meta_query_all_keys = array('relation' => 'OR');
                $meta_keys = get_post_type_meta_keys('todo');
                foreach ($meta_keys as $meta_key) {
                    $meta_query_all_keys[] = array(
                        'key'     => $meta_key,
                        'value'   => $search_query,
                        'compare' => 'LIKE',
                    );
                }
                $args['meta_query'][] = $meta_query_all_keys;
                $query = new WP_Query($args);
            }

            return $query;
        }

        function get_previous_log_id($current_log_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            // Get the submit time of the current log
            $current_submit_time = get_post_meta($current_log_id, 'submit_time', true);
        
            $args = array(
                'post_type'      => 'todo',
                'posts_per_page' => 1,
                'orderby'        => 'meta_value_num', // Order by numeric meta value
                'meta_key'       => 'submit_time',    // Key for sorting by submit time
                'order'          => 'ASC',           // Get the earliest log after the current one
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'submit_user',
                        'compare' => 'EXISTS',       // Ensure the meta key exists
                    ),
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    ),
                    array(
                        'key'     => 'submit_time',
                        'value'   => $current_submit_time,
                        'compare' => '>',            // Find logs submitted before the current one
                        'type'    => 'NUMERIC',      // Proper comparison for timestamp
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }
        
        function get_next_log_id($current_log_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            // Get the submit time of the current log
            $current_submit_time = get_post_meta($current_log_id, 'submit_time', true);
        
            $args = array(
                'post_type'      => 'todo',
                'posts_per_page' => 1,
                'orderby'        => 'meta_value_num', // Order by numeric meta value
                'meta_key'       => 'submit_time',    // Key for sorting by submit time
                'order'          => 'DESC',          // Get the most recent log before the current one
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'submit_user',
                        'compare' => 'EXISTS',       // Ensure the meta key exists
                    ),
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    ),
                    array(
                        'key'     => 'submit_time',
                        'value'   => $current_submit_time,
                        'compare' => '<',            // Find logs submitted after the current one
                        'type'    => 'NUMERIC',      // Proper comparison for timestamp
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function display_transaction_log_dialog($log_id=false) {
            ob_start();
            $documents_class = new display_documents();
            $prev_log_id = $this->get_previous_log_id($log_id); // Fetch the previous ID
            $next_log_id = $this->get_next_log_id($log_id);     // Fetch the next ID
            $doc_id = get_post_meta($log_id, 'doc_id', true);
            if ($doc_id) $log_title = get_the_title($doc_id);
            $category_id = get_post_meta($log_id, 'category_id', true);
            if ($category_id) $log_title = get_the_title($category_id);
            $department_id = get_post_meta($log_id, 'department_id', true);
            if ($department_id) $log_title = get_the_title($department_id);
            $device_id = get_post_meta($log_id, 'device_id', true);
            if ($device_id) $log_title = get_the_title($device_id);
            $log_title = isset($log_title) ? $log_title : __( 'System log', 'textdomain' );
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo esc_html($log_title);?></h2>
            <input type="hidden" id="log-id" value="<?php echo esc_attr($log_id); ?>" />            
            <input type="hidden" id="prev-log-id" value="<?php echo esc_attr($prev_log_id); ?>" />
            <input type="hidden" id="next-log-id" value="<?php echo esc_attr($next_log_id); ?>" />
            <fieldset>
            <?php
                $summary_todos = get_post_meta($log_id, 'summary_todos', true);

                $prev_report_id = get_post_meta($log_id, 'prev_report_id', true);
                $report_doc_id = get_post_meta($prev_report_id, 'doc_id', true);

                $submit_time = get_post_meta($log_id, 'submit_time', true);
                $submit_action = get_post_meta($log_id, 'submit_action', true);
                if (!$submit_action) {
                    //echo get_post_field('post_content', $log_id);
                    echo get_the_title($log_id);
                    $user_id = get_post_meta($log_id, 'user_id', true);
                    if ($user_id) {
                        $nda_content = get_user_meta($user_id, 'nda_content', true);
                        echo $nda_content;
                    }
                }
                // Figure out the summary-job Step 3
                else if (!empty($summary_todos) && is_array($summary_todos)) {
                    $doc_id = get_post_meta($log_id, 'doc_id', true);
                    $params = array(
                        'doc_id'         => $doc_id,
                        'summary_todos'  => $summary_todos,
                    );
                    $documents_class->get_doc_report_inner_list($params);
                } else {
                    $doc_id = get_post_meta($log_id, 'doc_id', true);
                    $prev_report_id = get_post_meta($log_id, 'prev_report_id', true);
                    $params = array(
                        //'is_todo'         => true,
                        'todo_id'         => $log_id,
                        //'doc_id'          => $doc_id,
                        //'prev_report_id'  => $prev_report_id,
                    );
                    //$documents_class->get_doc_field_contains($params);
                    $documents_class->get_doc_field_contains(array('todo_id' => $log_id));
                }
            ?>
            <hr>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php echo __( 'Log time: ', 'textdomain' ).wp_date(get_option('date_format'), $submit_time).' '.wp_date(get_option('time_format'), $submit_time);?>
                </div>
                <div style="text-align: right">
                    <?php if (is_site_admin()) {?>
                        <input type="button" id="del-action-log" value="<?php echo __( 'Delete', 'textdomain' );?>" style="margin:5px;" />
                    <?php }?>
                    <input type="button" id="exit-action-log" value="<?php echo __( 'Exit', 'textdomain' );?>" style="margin:5px;" />
                </div>
            </div>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }
        
        function set_transaction_log($params=array()) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            $log_title = isset($params['log_message']) ? $params['log_message'] : __( 'No messages.', 'textdomain' ); 
            // Create a new To-do for the current action
            $new_post = array(
                'post_type'     => 'todo',
                'post_title'    => $log_title,
                //'post_content'  => $log_content,
                'post_status'   => 'publish',
                'post_author'   => $current_user_id,
            );    
            $new_todo_id = wp_insert_post($new_post);    

            update_post_meta($new_todo_id, 'site_id', $site_id );
            //update_post_meta($new_todo_id, 'prev_report_id', $report_id);
            update_post_meta($new_todo_id, 'submit_user', $current_user_id);
            //update_post_meta($new_todo_id, 'submit_action', $action_id);
            update_post_meta($new_todo_id, 'submit_time', time());
            //update_post_meta($new_todo_id, 'next_job', $next_job);

            $action_title = isset($params['action_title']) ? $params['action_title'] : '';
            if ($action_title) update_post_meta($new_todo_id, 'action_title', $action_title);

            $doc_id = isset($params['doc_id']) ? $params['doc_id'] : 0;
            if ($doc_id) update_post_meta($new_todo_id, 'doc_id', $doc_id);
            $user_id = isset($params['user_id']) ? $params['user_id'] : 0;
            if ($user_id) update_post_meta($new_todo_id, 'user_id', $user_id);
            $category_id = isset($params['category_id']) ? $params['category_id'] : 0;
            if ($category_id) update_post_meta($new_todo_id, 'category_id', $category_id);
            $department_id = isset($params['department_id']) ? $params['department_id'] : 0;
            if ($department_id) update_post_meta($new_todo_id, 'department_id', $department_id);
            $device_id = isset($params['device_id']) ? $params['device_id'] : 0;
            if ($device_id) update_post_meta($new_todo_id, 'device_id', $device_id);
        }

        function del_transaction_log_dialog_data() {
            $response = array();
            if (isset($_POST['_log_id'])) {
                wp_delete_post($_POST['_log_id'], true);
                $response['html_contain'] = $this->display_transaction_log_list();
            } else {
                $response['error'] = 'Invalid request!';
            }
            wp_send_json($response);
        }

        // doc-report recurrence setting
        function schedule_event_callback($params) {
            $action_id = $params['action_id'];
            $user_id = $params['user_id'];
            $this->set_start_job_and_go_next($action_id, $user_id, true);
        }
        
        function weekday_event_callback($params) {
            // Check if today is a weekday (1 = Monday, 5 = Friday)
            $day_of_week = date('N');
            
            if ($day_of_week >= 1 && $day_of_week <= 5) {
                // Your weekday-specific code here, e.g., send_email_reminder(), update_daily_task(), etc.
                $action_id = $params['action_id'];
                $user_id = $params['user_id'];
                $this->set_start_job_and_go_next($action_id, $user_id, true);
            }
        }
        
        // Method to schedule the event and add the action
        function schedule_event_and_action() {
            // Retrieve the hook name from options
            $hook_name = get_option('schedule_event_hook_name');
            // Add the action with the dynamic hook name
            if ($hook_name=='weekday_daily_post_event') {
                add_action($hook_name, array($this, 'weekday_event_callback'));
            } else {
                add_action($hook_name, array($this, 'schedule_event_callback'));
            }
        }

        public function process_authorized_action_posts_daily() {
            $profiles_class = new display_profiles();
            // process the todo-list
            $args = array(
                'post_type'      => 'todo',
                'posts_per_page' => -1,
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

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $todo_id = get_the_ID();
                    $action_query = $this->retrieve_todo_action_list_data($todo_id);
                    if ($action_query->have_posts()) :
                        while ($action_query->have_posts()) : $action_query->the_post();
                            $action_id = get_the_ID();
                            $action_authorized_ids = $profiles_class->is_action_authorized($action_id);
                            if ($action_authorized_ids) {
                                foreach ($action_authorized_ids as $user_id) {
                                    $this->set_todo_data_and_go_next($action_id, $user_id, true);
                                }
                            }
                        endwhile;
                        wp_reset_postdata();
                    endif;
              }    
                wp_reset_postdata();
            }
        }
    }
    $todo_class = new to_do_list();
}

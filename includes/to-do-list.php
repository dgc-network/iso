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
                <option value="todo-list" <?php echo ($select_option=="todo-list") ? 'selected' : ''?>><?php echo __( '待辦事項', 'your-text-domain' );?></option>
                <option value="start-job" <?php echo ($select_option=="start-job") ? 'selected' : ''?>><?php echo __( '啟動表單', 'your-text-domain' );?></option>
                <option value="action-log" <?php echo ($select_option=="action-log") ? 'selected' : ''?>><?php echo __( '簽核記錄', 'your-text-domain' );?></option>
                <?php if (current_user_can('administrator') || $iot_messages->is_site_with_iot_device()) {?>
                    <option value="iot-devices" <?php echo ($select_option=="iot-devices") ? 'selected' : ''?>><?php echo __( 'IoT devices', 'your-text-domain' );?></option>
                <?php }?>
                <?php if (current_user_can('administrator')) {?>
                    <option value="cron-events" <?php echo ($select_option=="cron-events") ? 'selected' : ''?>><?php echo __( 'Cron events', 'your-text-domain' );?></option>
                <?php }?>
            </select>
            <?php
        }

        // Shortcode to display
        function display_shortcode() {
            // Check if the user is logged in
            if (!is_user_logged_in()) user_is_not_logged_in();                
            elseif (is_site_not_configured()) display_NDA_assignment();
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
                    if (isset($_GET['_log_id'])) echo $this->display_action_log_dialog($_GET['_log_id']);
                    else echo $this->display_action_log_list();                    
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
                <h2 style="display:inline;"><?php echo __( '待辦事項', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $this->display_select_todo('todo-list');?></div>
                    <div style="text-align: right">
                        <input type="text" id="search-todo" style="display:inline" placeholder="Search..." />
                    </div>
                </div>

                <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Todo', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Document', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Due date', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Authorized', 'your-text-domain' );?></th>
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
                            $doc_title = get_post_meta($doc_id, 'doc_title', true);
                            $report_id = get_post_meta($todo_id, 'prev_report_id', true);
                            $doc_title .= '(#'.$report_id.')';
                            $is_checked = $this->is_todo_authorized($todo_id) ? 'checked' : '';
                            ?>
                            <tr id="edit-todo-<?php echo esc_attr($todo_id);?>">
                                <td style="text-align:center;"><?php echo esc_html($todo_title);?></td>
                                <td><?php echo esc_html($doc_title);?></td>
                                <td style="text-align:center; <?php echo $todo_due_color?>"><?php echo esc_html($todo_due);?></td>
                                <td style="text-align:center;"><input type="radio" <?php echo $is_checked;?> /></td>
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
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
            if (!is_array($user_doc_ids)) $user_doc_ids = array();

            $search_query = (isset($_GET['_search'])) ? sanitize_text_field($_GET['_search']) : false;

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

            // Add meta query for searching across all meta keys
            $meta_keys = get_post_type_meta_keys('todo');
            $meta_query_all_keys = array('relation' => 'OR');
            foreach ($meta_keys as $meta_key) {
                $meta_query_all_keys[] = array(
                    'key'     => $meta_key,
                    'value'   => $search_query,
                    'compare' => 'LIKE',
                );
            }
            
            $args['meta_query'][] = $meta_query_all_keys;
            
            $query = new WP_Query($args);
            return $query;
        }

        function get_previous_todo_id($current_todo_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
        
            // Ensure $user_doc_ids is an array
            if (!is_array($user_doc_ids)) {
                $user_doc_ids = array();
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
                'orderby'        => 'date',          // Order by post date
                'order'          => 'ASC',           // Ascending order
                'meta_query'     => $meta_query,     // Apply the meta query
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
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
        
            // Ensure $user_doc_ids is an array
            if (!is_array($user_doc_ids)) {
                $user_doc_ids = array();
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
                'orderby'        => 'date',          // Order by post date
                'order'          => 'DESC',          // Descending order
                'meta_query'     => $meta_query,     // Apply the meta query
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
            $prev_todo_id = $this->get_previous_todo_id($todo_id); // Fetch the previous ID
            $next_todo_id = $this->get_next_todo_id($todo_id);     // Fetch the next ID
            ?>
            <input type="hidden" id="prev-todo-id" value="<?php echo esc_attr($prev_todo_id); ?>" />
            <input type="hidden" id="next-todo-id" value="<?php echo esc_attr($next_todo_id); ?>" />
            <?php
            $documents_class = new display_documents();
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo esc_html('Todo: '.get_the_title($todo_id));?></h2>
            <input type="hidden" id="todo-id" value="<?php echo $todo_id;?>" />
            <fieldset>
            <?php
                $todo_in_summary = get_post_meta($todo_id, 'todo_in_summary', true);
                // Figure out the summary-job Step 3
                if (!empty($todo_in_summary) && is_array($todo_in_summary)) {
                    $doc_id = get_post_meta($todo_id, 'doc_id', true);
                    $params = array(
                        'doc_id'           => $doc_id,
                        'todo_in_summary'  => $todo_in_summary,
                    );
                    $documents_class->get_doc_report_contain_list($params);
                } else {
                    $doc_id = get_post_meta($todo_id, 'doc_id', true);
                    $prev_report_id = get_post_meta($todo_id, 'prev_report_id', true);
                    $params = array(
                        'is_todo'         => true,
                        'todo_id'         => $todo_id,
                        'doc_id'          => $doc_id,
                        'prev_report_id'  => $prev_report_id,
                    );
                    $documents_class->get_doc_field_contains($params);
                }
            ?>
            <hr>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php
                        $query = $this->retrieve_todo_action_list_data($todo_id);
                        if ($query->have_posts()) {
                            while ($query->have_posts()) : $query->the_post();
                                echo '<input type="button" id="todo-dialog-button-'.get_the_ID().'" value="'.get_the_title().'" style="margin:5px;" />';
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
                //$result['html_contain'] = $this->display_todo_dialog($todo_id);
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
                $this->create_todo_dialog_and_go_next($action_id);
            }
            wp_send_json($response);
        }
        
        // start-job
        function display_start_job_list() {
            ?>
            <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '啟動表單', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $this->display_select_todo('start-job');?></div>
                    <div style="text-align: right">
                        <input type="text" id="search-start-job" style="display:inline" placeholder="Search..." />
                    </div>
                </div>

                <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Job', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Document', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Authorized', 'your-text-domain' );?></th>
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
                            $job_title = get_the_title();
                            $job_number = get_post_meta($doc_id, 'job_number', true);
                            if ($job_number) $job_title .= '('.$job_number.')';

                            $doc_title = get_post_meta($doc_id, 'doc_title', true);
                            $doc_number = get_post_meta($doc_id, 'doc_number', true);
                            if ($doc_number) $doc_title .= '('.$doc_number.')';

                            $profiles_class = new display_profiles();
                            $is_checked = $profiles_class->is_doc_authorized($doc_id) ? 'checked' : '';
                            ?>
                            <tr id="edit-start-job-<?php echo $doc_id;?>">
                                <td style="text-align:center;"><?php echo esc_html($job_title);?></td>
                                <td><?php echo esc_html($doc_title); ?></td>
                                <td style="text-align:center;"><input type="radio" <?php echo $is_checked;?> /></td>
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

        function retrieve_start_job_data($paged=1, $current_user_id=false, $search_query=false){
            if (!$current_user_id) $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
            if (!is_array($user_doc_ids)) $user_doc_ids = array();

            if (!$search_query && isset($_GET['_search'])) $search_query = sanitize_text_field($_GET['_search']);
            if ($search_query) $paged = 1;

            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_key'       => 'job_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                        'compare' => '=',
                    ),
                    array(
                        'key'     => 'is_doc_report',
                        'value'   => 1,
                        'compare' => '=',
                    ),
                ),
            );

            if ($paged==0) $args['posts_per_page'] = -1;

            if (!is_site_admin()||current_user_can('administrator')) {
                $args['post__in'] = $user_doc_ids; // Array of document post IDs
            }

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

            $query = new WP_Query($args);

            return $query;
        }
        
        function get_previous_job_id($current_job_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
            if (!is_array($user_doc_ids)) $user_doc_ids = array();

            // Get the current document's `job_number`
            $current_job_number = get_post_meta($current_job_id, 'job_number', true);
        
            if (!$current_job_number) {
                return null; // Return null if the current job_number is not set
            }
        
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => 1,
                'meta_key'       => 'job_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value as a string
                'order'          => 'DESC', // Descending order to get the previous document
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                        'compare' => '=',    
                    ),
                    array(
                        'key'     => 'is_doc_report',
                        'value'   => 1,
                        'compare' => '=',
                    ),
                    array(
                        'key'     => 'job_number',
                        'value'   => $current_job_number,
                        'compare' => '<', // Find `job_number` less than the current one
                        'type'    => 'CHAR', // Treat `job_number` as a string
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
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
            if (!is_array($user_doc_ids)) $user_doc_ids = array();

            // Get the current document's `job_number`
            $current_job_number = get_post_meta($current_job_id, 'job_number', true);
        
            if (!$current_job_number) {
                return null; // Return null if the current job_number is not set
            }
        
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => 1,
                'meta_key'       => 'job_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value as a string
                'order'          => 'ASC', // Ascending order to get the next document
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                        'compare' => '=',    
                    ),
                    array(
                        'key'     => 'is_doc_report',
                        'value'   => 1,
                        'compare' => '=',
                    ),
                    array(
                        'key'     => 'job_number',
                        'value'   => $current_job_number,
                        'compare' => '>', // Find `job_number` greater than the current one
                        'type'    => 'CHAR', // Treat `job_number` as a string
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
            <input type="hidden" id="prev-job-id" value="<?php echo esc_attr($prev_job_id); ?>" />
            <input type="hidden" id="next-job-id" value="<?php echo esc_attr($next_job_id); ?>" />
            <?php
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo esc_html('Start job: '.get_the_title($doc_id));?></h2>
            <input type="hidden" id="job-id" value="<?php echo $doc_id;?>" />
            <fieldset>
                <?php
                $documents_class = new display_documents();
                $documents_class->get_doc_field_contains(array('doc_id' => $doc_id));
                $doc_title = get_post_meta($doc_id, 'doc_title', true);
                $content = (isset($_GET['_prompt'])) ? generate_content($doc_title.' '.$_GET['_prompt']) : '';
                ?>
                <div class="content">
                    <?php echo $content;?>
                    <div style="margin:1em; padding:10px; border:solid; border-radius:1.5rem;">
                        <input type="text" id="ask-gemini" placeholder="問問 Gemini" class="text ui-widget-content ui-corner-all" />
                    </div>
                </div>            
            <hr>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                <?php
                    $profiles_class = new display_profiles();
                    $query = $profiles_class->retrieve_doc_action_data($doc_id);
                    if ($query->have_posts()) {
                        while ($query->have_posts()) : $query->the_post();
                            echo '<input type="button" id="start-job-dialog-button-'.get_the_ID().'" value="'.get_the_title().'" style="margin:5px;" />';
                        endwhile;
                        wp_reset_postdata();
                    }
                ?>
                </div>
                <div style="text-align: right">
                    <input type="button" id="exit-start-job" value="Exit" style="margin:5px;" />
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
                $this->create_start_job_and_go_next($action_id);
            }
            wp_send_json($response);
        }
        
        // to-do-list misc
        function create_todo_dialog_and_go_next($action_id=false, $user_id=false, $is_default=false) {
            // action button is clicked
            if (!$user_id) $user_id = get_current_user_id();
            $next_job = get_post_meta($action_id, 'next_job', true);
            $is_doc_report = get_post_meta($next_job, 'is_doc_report', true);
            $todo_id = get_post_meta($action_id, 'todo_id', true);
            $doc_id = get_post_meta($todo_id, 'doc_id', true);
            $prev_report_id = get_post_meta($todo_id, 'prev_report_id', true);
            $summary_todos = get_post_meta($todo_id, 'summary_todos', true);

            // 如果是審核、核准、彙整之類的工作，就不需要新增一個doc-report了
            if ($is_doc_report==1) {
                // Add a new doc-report
                $new_post = array(
                    'post_type'     => 'doc-report',
                    'post_title'    => get_the_title($doc_id),
                    'post_status'   => 'publish',
                    'post_author'   => $user_id,
                );    
                $prev_report_id = wp_insert_post($new_post);    
            }

            if (!empty($summary_todos) && is_array($summary_todos)) {
                foreach ($summary_todos as $todo_id) {
                    $report_id = get_post_meta($todo_id, 'prev_report_id', true);
                    update_post_meta($report_id, 'todo_status', $next_job);
                }
            } else {
                update_post_meta($prev_report_id, 'doc_id', $doc_id);
                update_post_meta($prev_report_id, 'todo_status', $next_job);    
            }

            // Update the post meta
            $documents_class = new display_documents();
            $query = $documents_class->retrieve_doc_field_data(array('doc_id' => $doc_id));
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $documents_class->update_doc_field_contains($prev_report_id, get_the_ID(), $is_default, $user_id);
                endwhile;
                wp_reset_postdata();
            }            

            // Update current todo
            update_post_meta($todo_id, 'prev_report_id', $prev_report_id);
            update_post_meta($todo_id, 'submit_user', $user_id );
            update_post_meta($todo_id, 'submit_action', $action_id );
            update_post_meta($todo_id, 'submit_time', time() );
            update_post_meta($todo_id, 'next_job', $next_job );

            // set next todo and actions
            $params = array(
                'user_id' => $user_id,
                'action_id' => $action_id,
                'prev_todo_id' => $todo_id,
            );
            if (!empty($summary_todos) && is_array($summary_todos)) {
                $params['doc_id'] = $doc_id;
            } else {
                $params['prev_report_id'] = $prev_report_id;
            }
            if ($next_job>0) $this->initial_next_todo_and_actions($params);
        }
        
        function create_start_job_and_go_next($action_id=false, $user_id=false, $is_default=false) {
            // action button is clicked
            if (!$user_id) $user_id = get_current_user_id();
            $site_id = get_user_meta($user_id, 'site_id', true);
            $doc_id = get_post_meta($action_id, 'doc_id', true);
            $next_job = get_post_meta($action_id, 'next_job', true);
            $is_summary_job = get_post_meta($next_job, 'is_summary_job', true);

            // Add a new doc-report for current action
            $new_post = array(
                'post_type'     => 'doc-report',
                'post_title'    => get_the_title($doc_id),
                'post_status'   => 'publish',
                'post_author'   => $user_id,
            );    
            $new_report_id = wp_insert_post($new_post);
            update_post_meta($new_report_id, 'doc_id', $doc_id);
            update_post_meta($new_report_id, 'todo_status', $next_job);

            // update system_doc
            $documents_class = new display_documents();
            $system_doc = get_post_meta($doc_id, 'system_doc', true);
            if ($system_doc) {
                // Update the post
                $post_data = array(
                    'ID'           => $new_report_id,
                    'post_title'   => $_POST['_post_title'],
                    'post_content' => $_POST['_post_content'],
                );        
                wp_update_post($post_data);
                update_post_meta($new_report_id, '_post_number', $_POST['_post_number']);

                if (stripos($system_doc, 'customer') !== false || stripos($system_doc, 'vendor') !== false) {
                    // Code to execute if $system_doc includes 'customer' or 'vendor', case-insensitive
                    $documents_class->upsert_site_profile($new_report_id);
                }
            }

            // Update the doc-field meta for new doc-report
            $query = $documents_class->retrieve_doc_field_data(array('doc_id' => $doc_id));
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $documents_class->update_doc_field_contains($new_report_id, get_the_ID(), $is_default, $user_id);
                endwhile;
                wp_reset_postdata();
            }            

            // Add a new todo for current action
            $new_post = array(
                'post_type'     => 'todo',
                'post_title'    => get_the_title($doc_id),
                'post_status'   => 'publish',
                'post_author'   => $user_id,
            );    
            $new_todo_id = wp_insert_post($new_post);
            update_post_meta($new_todo_id, 'site_id', $site_id );
            update_post_meta($new_todo_id, 'doc_id', $doc_id);
            update_post_meta($new_todo_id, 'prev_report_id', $new_report_id);
            update_post_meta($new_todo_id, 'submit_user', $user_id );
            update_post_meta($new_todo_id, 'submit_action', $action_id );
            update_post_meta($new_todo_id, 'submit_time', time() );
            update_post_meta($new_todo_id, 'next_job', $next_job );

            // set next todo and actions
            $params = array(
                'user_id' => $user_id,
                'action_id' => $action_id,
                'prev_todo_id' => $new_todo_id,
            );
            if ($is_summary_job) {
                $params['doc_id'] = $doc_id;
            } else {
                $params['prev_report_id'] = $new_report_id;
            }
            if ($next_job>0) $this->initial_next_todo_and_actions($params);
        }
        
        function create_action_log_and_go_next($params=array()) {
            // Create the new To-do
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            $action_id = isset($params['action_id']) ? $params['action_id'] : 0;
            $report_id = isset($params['report_id']) ? $params['report_id'] : 0;

            if ($report_id) {
                $doc_id = get_post_meta($report_id, 'doc_id', true);
                $todo_title = get_the_title($doc_id);
            } else {
                $doc_id = 0;
                $todo_title = isset($params['log_message']) ? $params['log_message'] : 'No message.'; 
            }

            $next_job = get_post_meta($action_id, 'next_job', true);
            $new_post = array(
                'post_type'     => 'todo',
                'post_title'    => $todo_title,
                'post_status'   => 'publish',
                'post_author'   => $current_user_id,
            );    
            $todo_id = wp_insert_post($new_post);    

            update_post_meta($todo_id, 'site_id', $site_id );
            update_post_meta($todo_id, 'doc_id', $doc_id);
            update_post_meta($todo_id, 'prev_report_id', $report_id);
            update_post_meta($todo_id, 'submit_user', $current_user_id);
            update_post_meta($todo_id, 'submit_action', $action_id);
            update_post_meta($todo_id, 'submit_time', time());
            update_post_meta($todo_id, 'next_job', $next_job);

            update_post_meta($report_id, 'todo_status', $next_job);

            // set next todo and actions
            $params = array(
                'next_job' => $next_job,
                'prev_report_id' => $report_id,
                'prev_todo_id' => $todo_id,
            );        
            if ($next_job>0) $this->initial_next_todo_and_actions($params);
        }

        function initial_next_todo_and_actions($params=array()) {
            // 1. From create_todo_dialog_and_go_next(), create a next_todo based on the $args['action_id'], $args['user_id'] and $args['prev_report_id']
            // 2. From create_action_log_and_go_next(), create a next_todo based on the $args['next_job'] and $args['prev_report_id']
            // 3. From create_start_job_and_go_next(), create a next_todo based on the $args['action_id'], $args['user_id'] and $args['prev_report_id']
            // 4. From schedule_event_callback($params), create a create_start_job_and_go_next() then go item 3

            $user_id = isset($params['user_id']) ? $params['user_id'] : get_current_user_id();
            $user_id = ($user_id) ? $user_id : 1;
            $action_id = isset($params['action_id']) ? $params['action_id'] : 0;
            $prev_report_id = isset($params['prev_report_id']) ? $params['prev_report_id'] : 0;

            // Find the doc_id
            if ($prev_report_id) {
                $doc_id = get_post_meta($prev_report_id, 'doc_id', true);
            } else {
                $doc_id = isset($params['doc_id']) ? $params['doc_id'] : 0;
            }

            // Find the next_job, next_leadtime
            if ($action_id > 0) {
                $next_job      = get_post_meta($action_id, 'next_job', true);
                $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
            } else {
                // create_action_log_and_go_next() and frquence doc_report
                $next_job = isset($params['next_job']) ? $params['next_job'] : 0;
                if ($next_job==0) $next_job = $doc_id; // frquence doc_report                    
            }
            if (empty($next_leadtime)) $next_leadtime=86400;
        
            if ($next_job>0)   $todo_title = get_the_title($next_job);
            if ($next_job==-1) $todo_title = __( '發行', 'your-text-domain' );
            if ($next_job==-2) $todo_title = __( '廢止', 'your-text-domain' );

            $params['todo_title'] = $todo_title;
            $params['user_id'] = $user_id;
            $params['doc_id'] = $doc_id;
            $params['next_job'] = $next_job;
            $params['next_leadtime'] = $next_leadtime;

            $is_updated = false;
            // Figure out the summary-job Step 1
            if ($next_job>0) $is_summary_job = get_post_meta($next_job, 'is_summary_job', true);
            if ($is_summary_job) {
                $prev_todo_id = isset($params['prev_todo_id']) ? $params['prev_todo_id'] : 0;
                $summary_todos = get_post_meta($next_job, 'summary_todos', true);

                if (!empty($summary_todos) && is_array($summary_todos)) {
                    // Query for all 'todo' posts in $summary_todos with meta query conditions
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
                
                    $query_args = array(
                        'post_type'  => 'todo',
                        'post__in'   => $summary_todos, // Use the array of IDs directly
                        'meta_query' => $meta_query,
                    );
                    $query = new WP_Query($query_args);
                
                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();
                            // Perform actions for each matching post
                            $todo_in_summary = get_post_meta(get_the_ID(), 'todo_in_summary', true);
                            $todo_in_summary[] = $prev_todo_id;
                            update_post_meta(get_the_ID(), 'todo_in_summary', $todo_in_summary);
                        }
                    } else {
                        //echo "No posts match the meta query conditions.";
                        if (!$is_updated) $this->create_new_todo_for_next_job($params);
                    }
                    wp_reset_postdata(); // Reset query
                } else {
                    if (!$is_updated) $this->create_new_todo_for_next_job($params);
                }
                $is_updated = true;
            }
            if (!$is_updated) $this->create_new_todo_for_next_job($params);
        }

        function create_new_todo_for_next_job($params=array()) {
            $todo_title = isset($params['todo_title']) ? $params['todo_title'] : 0;
            $user_id = isset($params['user_id']) ? $params['user_id'] : get_current_user_id();
            $action_id = isset($params['action_id']) ? $params['action_id'] : 0;
            $doc_id = isset($params['doc_id']) ? $params['doc_id'] : 0;
            $prev_report_id = isset($params['prev_report_id']) ? $params['prev_report_id'] : 0;
            $next_job = isset($params['next_job']) ? $params['next_job'] : 0;
            $next_leadtime = isset($params['next_leadtime']) ? $params['next_leadtime'] : 0;
            $site_id = get_user_meta($user_id, 'site_id', true);

            // Create a new To-do for next_job
            $new_post = array(
                'post_type'     => 'todo',
                'post_title'    => $todo_title,
                'post_status'   => 'publish',
                'post_author'   => $user_id,
            );    
            $new_todo_id = wp_insert_post($new_post);
            
            update_post_meta($new_todo_id, 'todo_due', time()+$next_leadtime );
            update_post_meta($new_todo_id, 'site_id', $site_id );

            if ($prev_report_id) update_post_meta($new_todo_id, 'prev_report_id', $prev_report_id );

            if ($next_job>0) {
                update_post_meta($new_todo_id, 'doc_id', $next_job );
                $doc_number = get_post_meta($next_job, 'doc_number', true);
                // if the meta "doc_number" of $next_job from set_todo_dialog_data() is not presented
                if (empty($doc_number)) {
                    update_post_meta($new_todo_id, 'doc_id', $doc_id );
                }
                // Figure out the summary-job Step 2
                if ($next_job>0) $is_summary_job = get_post_meta($next_job, 'is_summary_job', true);
                if ($is_summary_job) {
                    $prev_todo_id = isset($params['prev_todo_id']) ? $params['prev_todo_id'] : 0;
                    update_post_meta($new_todo_id, 'todo_in_summary', array($prev_todo_id));
                    update_post_meta($next_job, 'summary_todos', array($new_todo_id));
                }    
            }

            if ($next_job==-1 || $next_job==-2) {
                update_post_meta($new_todo_id, 'submit_user', $user_id);
                update_post_meta($new_todo_id, 'submit_action', $action_id);
                update_post_meta($new_todo_id, 'submit_time', time());
                update_post_meta($new_todo_id, 'next_job', $next_job);
                if ($prev_report_id) update_post_meta($prev_report_id, 'todo_status', $next_job );
                // Notice the persons in site
                $this->notice_the_persons_in_site($new_todo_id, $next_job);
            }

            if ($next_job>0) {
                // Create the new Action list for next_job 
                $profiles_class = new display_profiles();
                $query = $profiles_class->retrieve_doc_action_data($next_job);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $new_post = array(
                            'post_type'     => 'action',
                            'post_title'    => get_the_title(),
                            'post_content'  => get_the_content(),
                            'post_status'   => 'publish',
                            'post_author'   => $user_id,
                        );    
                        $new_action_id = wp_insert_post($new_post);
                        $new_next_job = get_post_meta(get_the_ID(), 'next_job', true);
                        $new_next_leadtime = get_post_meta(get_the_ID(), 'next_leadtime', true);
                        update_post_meta($new_action_id, 'todo_id', $new_todo_id);
                        update_post_meta($new_action_id, 'next_job', $new_next_job);
                        update_post_meta($new_action_id, 'next_leadtime', $new_next_leadtime);
                        
                        //Update the action_authorized_ids
                        $action_authorized_ids = $profiles_class->is_action_authorized(get_the_ID());
                        if ($action_authorized_ids){
                            update_post_meta($new_action_id, 'action_authorized_ids', $action_authorized_ids);
                        }
                    endwhile;
                    wp_reset_postdata();
                }
                // Notice the persons in charge the job
                $this->notice_the_responsible_persons($new_todo_id);
            }
        }

        // Notice the persons in charge the job
        function notice_the_responsible_persons($todo_id=0) {
            $line_bot_api = new line_bot_api();
            $todo_title = get_the_title($todo_id);
            $doc_id = get_post_meta($todo_id, 'doc_id', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $todo_due = get_post_meta($todo_id, 'todo_due', true);
            $due_date = wp_date( get_option('date_format'), $todo_due );
            $text_message = '你在「'.$todo_title.'」的職務有一份文件需要在'.$due_date.'前簽核完成，你可以點擊下方連結查看該文件。';
            $link_uri = home_url().'/to-do-list/?_select_todo=todo-list&_todo_id='.$todo_id;
        
            $args = array(
                'meta_query'     => array(
                    array(
                        'key'     => 'user_doc_ids',
                        'value'   => $doc_id,
                        'compare' => 'LIKE',
                    ),
                ),
            );
            $query = new WP_User_Query($args);
            $users = $query->get_results();
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
                            'label' => $todo_title,
                            'uri' => $link_uri, // Use the desired URI
                        ),
                        'style' => 'primary',
                        'margin' => 'sm',
                    ),
                );

                $line_bot_api->send_flex_message([
                    'to' => get_user_meta($user->ID, 'line_user_id', TRUE),
                    'header_contents' => $header_contents,
                    'body_contents' => $body_contents,
                    'footer_contents' => $footer_contents,
                ]);
            }

            // Check if Employees or Department in report
            $report_id = get_post_meta($todo_id, 'prev_report_id', true);
            if ($report_id) {
                $department_id = get_post_meta($report_id, '_department', true);
                $user_ids = get_post_meta($department_id, 'user_ids', true);
                if (is_array($user_ids)) {
                    foreach ($user_ids as $user_id) {
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
                                    'label' => $todo_title,
                                    'uri' => $link_uri, // Use the desired URI
                                ),
                                'style' => 'primary',
                                'margin' => 'sm',
                            ),
                        );
        
                        $line_bot_api->send_flex_message([
                            'to' => get_user_meta($user->ID, 'line_user_id', TRUE),
                            'header_contents' => $header_contents,
                            'body_contents' => $body_contents,
                            'footer_contents' => $footer_contents,
                        ]);
                    }    
                }

                $user_ids = get_post_meta($report_id, '_employees', true);
                if (is_array($user_ids)) {
                    foreach ($user_ids as $user_id) {
                        $user = get_userdata($user_id);

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
                                    'label' => $todo_title,
                                    'uri' => $link_uri, // Use the desired URI
                                ),
                                'style' => 'primary',
                                'margin' => 'sm',
                            ),
                        );
        
                        $line_bot_api->send_flex_message([
                            'to' => get_user_meta($user->ID, 'line_user_id', TRUE),
                            'header_contents' => $header_contents,
                            'body_contents' => $body_contents,
                            'footer_contents' => $footer_contents,
                        ]);
                    }    
                }

            }

        }

        // Notice the persons in site
        function notice_the_persons_in_site($todo_id=0, $next_job=0) {
            $doc_id = get_post_meta($todo_id, 'doc_id', true);
            $report_id = get_post_meta($todo_id, 'report_id', true);
            if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
            $site_id = get_post_meta($doc_id, 'site_id', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            $doc_title .= '('.$doc_number.')';
            $submit_time = get_post_meta($todo_id, 'submit_time', true);
            $text_message=$doc_title.' has been published on '.wp_date( get_option('date_format'), $submit_time ).'.';

            $text_message = '文件「'.$doc_title.'」已經在'.wp_date( get_option('date_format'), $submit_time );
            if ($next_job==-1) $text_message .= '發行，你可以點擊下方連結查看該文件。';
            if ($next_job==-2) $text_message .= '廢止，你可以點擊下方連結查看該文件。';
            if ($report_id) {
                $link_uri = home_url().'/display-documents/?_doc_id='.$doc_id.'&_report_id='.$report_id;
            } else {
                $link_uri = home_url().'/display-documents/?_doc_id='.$doc_id;
            }
        
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
                            'label' => 'Click me!',
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

        function is_todo_authorized($todo_id=false) {
            $query = $this->retrieve_todo_action_list_data($todo_id);
            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $profiles_class = new display_profiles();
                    if ($profiles_class->is_action_authorized(get_the_ID())) return true;
                endwhile;
                wp_reset_postdata();
            endif;
            return false;
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
        
        // action_log
        function display_action_log_list() {
            $query = $this->retrieve_action_log_data(0);
            $total_posts = $query->found_posts;
            ?>
            <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '簽核記錄', 'your-text-domain' );?></h2>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $this->display_select_todo('action-log');?></div>
                    <div style="text-align: right">
                        <input type="text" id="search-todo" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
                <?php echo $this->get_action_log();?>
                <div style="background-color:lightblue; text-align:center;">
                    <?php echo __( 'Total Submissions:', 'your-text-domain' );?> <?php echo $total_posts;?>
                </div>
            </div>
            <?php
        }
        
        function retrieve_action_log_data($paged=1, $report_id=false) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true); // Get current user's site_id

            $args = array(
                'post_type'      => 'todo',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'submit_user',
                        'compare' => 'EXISTS',
                    ),
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                        'compare' => '=',
                    ),
                ),
                'orderby'        => 'meta_value',
                'meta_key'       => 'submit_time',
                'order'          => 'DESC',
            );

            // If paged is 0, retrieve all matching posts
            if ($paged == 0) {
                $args['posts_per_page'] = -1;
            }

            // If $report_id is provided, filter by prev_report_id
            if ($report_id) {
                $args['meta_query'][] = array(
                    'key'   => 'prev_report_id',
                    'value' => $report_id,
                    'compare' => '='
                );
            }

            // Query to get matching posts
            $query = new WP_Query($args);
            return $query;
        }

        function get_previous_log_id($current_log_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
        
            // Ensure $user_doc_ids is an array
            if (!is_array($user_doc_ids)) {
                $user_doc_ids = array();
            }
        
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
                        'compare' => '=',            // Match the site ID
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
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
        
            // Ensure $user_doc_ids is an array
            if (!is_array($user_doc_ids)) {
                $user_doc_ids = array();
            }
        
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
                        'compare' => '=',            // Match the site ID
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

        function display_action_log_dialog($log_id=false) {
            ob_start();
            $prev_log_id = $this->get_previous_log_id($log_id); // Fetch the previous ID
            $next_log_id = $this->get_next_log_id($log_id);     // Fetch the next ID
            ?>
            <input type="hidden" id="prev-log-id" value="<?php echo esc_attr($prev_log_id); ?>" />
            <input type="hidden" id="next-log-id" value="<?php echo esc_attr($next_log_id); ?>" />
            <?php
            $documents_class = new display_documents();
            $submit_time = get_post_meta($log_id, 'submit_time', true);
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo esc_html(get_the_title($log_id));?></h2>
            
            <fieldset>
            <?php
                $todo_in_summary = get_post_meta($log_id, 'todo_in_summary', true);
                $submit_action = get_post_meta($log_id, 'submit_action', true);
                if (!$submit_action) echo 'system log!';
                // Figure out the summary-job Step 3
                else if (!empty($todo_in_summary) && is_array($todo_in_summary)) {
                    $doc_id = get_post_meta($log_id, 'doc_id', true);
                    $params = array(
                        'doc_id'           => $doc_id,
                        'todo_in_summary'  => $todo_in_summary,
                    );
                    $documents_class->get_doc_report_contain_list($params);
                } else {
                    $doc_id = get_post_meta($log_id, 'doc_id', true);
                    $prev_report_id = get_post_meta($log_id, 'prev_report_id', true);
                    $params = array(
                        'is_todo'         => true,
                        'todo_id'         => $log_id,
                        'doc_id'          => $doc_id,
                        'prev_report_id'  => $prev_report_id,
                    );
                    $documents_class->get_doc_field_contains($params);
                }
            ?>
            <hr>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php echo 'Log time: '.wp_date(get_option('date_format'), $submit_time).' '.wp_date(get_option('time_format'), $submit_time);?>
                </div>
                <div style="text-align: right">
                    <input type="button" id="action-log-exit" value="Exit" style="margin:5px;" />
                </div>
            </div>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }
        
        function get_action_log($report_id=false) {
            ob_start();
            ?>
            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Time', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Todo', 'your-text-domain' );?></th>
                            <th><?php echo __( 'User', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Action', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Next', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_action_log_data($paged, $report_id);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $doc_id = get_post_meta(get_the_ID(), 'doc_id', true);
                            //$site_id = get_post_meta($doc_id, 'site_id', true);
                            $doc_title = get_post_meta($doc_id, 'doc_title', true);
                            $todo_title = get_the_title();
                            $report_id = get_post_meta(get_the_ID(), 'prev_report_id', true);
                            if ($report_id) {
                                $doc_title .= '(#'.$report_id.')';
                            }
                            else {
                                $doc_title = get_the_title();
                                $todo_title = 'system';
                            }
                            $submit_action = get_post_meta(get_the_ID(), 'submit_action', true);
                            $submit_user = get_post_meta(get_the_ID(), 'submit_user', true);
                            $submit_time = get_post_meta(get_the_ID(), 'submit_time', true);
                            $next_job = get_post_meta(get_the_ID(), 'next_job', true);
                            if (!$next_job) $next_job = get_post_meta($submit_action, 'next_job', true);
                            $job_title = ($next_job==-1) ? __( '發行', 'your-text-domain' ) : get_the_title($next_job);
                            $job_title = ($next_job==-2) ? __( '廢止', 'your-text-domain' ) : $job_title;
                            if ($submit_action) $submit_title = get_the_title($submit_action);
                            else {
                                $submit_title = '';
                                $job_title = '';
                            } 
                            $user_data = get_userdata( $submit_user );
                            ?>
                            <tr id="edit-action-log<?php esc_attr(the_ID()); ?>">
                                <td style="text-align:center;"><?php echo wp_date(get_option('date_format'), $submit_time).' '.wp_date(get_option('time_format'), $submit_time);?></td>
                                <td><?php echo esc_html($doc_title);?></td>
                                <td style="text-align:center;"><?php echo esc_html($todo_title);?></td>
                                <td style="text-align:center;"><?php echo esc_html($user_data->display_name);?></td>
                                <td style="text-align:center;"><?php echo esc_html($submit_title);?></td>
                                <td style="text-align:center;"><?php echo esc_html($job_title);?></td>
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
/*        
        function count_action_logs(){
            $current_user_id = get_current_user_id();
            $current_site = get_user_meta($current_user_id, 'site_id', true);
            $x = 0;
            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $doc_id = get_post_meta(get_the_ID(), 'doc_id', true);
                    $site_id = get_post_meta($doc_id, 'site_id', true);
                    if ($current_site==$site_id) { // Aditional condition to filter the data
                        $x += 1;
                    }
                endwhile;
                wp_reset_postdata();
            endif;
            return $x;
        }
*/        
        // doc-report frequence setting
        function schedule_event_callback($params) {
            $action_id = $params['action_id'];
            $user_id = $params['user_id'];
            $this->create_start_job_and_go_next($action_id, $user_id, true);
        }
        
        function weekday_event_callback($params) {
            // Check if today is a weekday (1 = Monday, 5 = Friday)
            $day_of_week = date('N');
            
            if ($day_of_week >= 1 && $day_of_week <= 5) {
                // Your weekday-specific code here, e.g., send_email_reminder(), update_daily_task(), etc.
                $action_id = $params['action_id'];
                $user_id = $params['user_id'];
                $this->create_start_job_and_go_next($action_id, $user_id, true);
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
                    $profiles_class = new display_profiles();
                    $action_query = $this->retrieve_todo_action_list_data($todo_id);
                    if ($action_query->have_posts()) :
                        while ($action_query->have_posts()) : $action_query->the_post();
                            $action_id = get_the_ID();
                            $action_authorized_ids = $profiles_class->is_action_authorized($action_id);
                            if ($action_authorized_ids) {
                                foreach ($action_authorized_ids as $user_id) {
                                    $this->create_todo_dialog_and_go_next($action_id, $user_id, true);
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


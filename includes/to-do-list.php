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
            //add_action( 'init', array( $this, 'register_todo_post_type' ) );
            //add_action( 'add_meta_boxes', array( $this, 'add_todo_settings_metabox' ) );
            //add_action( 'init', array( $this, 'register_action_post_type' ) );

            add_action( 'wp_ajax_get_todo_dialog_data', array( $this, 'get_todo_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_todo_dialog_data', array( $this, 'get_todo_dialog_data' ) );
            add_action( 'wp_ajax_set_todo_dialog_data', array( $this, 'set_todo_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_todo_dialog_data', array( $this, 'set_todo_dialog_data' ) );

            add_action( 'wp_ajax_get_job_dialog_data', array( $this, 'get_job_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_job_dialog_data', array( $this, 'get_job_dialog_data' ) );
            add_action( 'wp_ajax_set_job_dialog_data', array( $this, 'set_job_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_job_dialog_data', array( $this, 'set_job_dialog_data' ) );

            // Schedule the cron job if it's not already scheduled
            if (!wp_next_scheduled('daily_action_process_event')) {
                wp_schedule_event(time(), 'daily', 'daily_action_process_event');
            }    
            // Hook the function to the scheduled cron job
            add_action( 'daily_action_process_event', [$this, 'process_authorized_action_posts_daily' ] );
            add_filter( 'cron_schedules', array( $this, 'iso_helper_cron_schedules' ) );
            add_action( 'init', array( $this, 'schedule_event_and_action' ) );
        }

        function enqueue_to_do_list_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);        
            $version = time(); // Update this version number when you make changes
            wp_enqueue_script('to-do-list', plugins_url('to-do-list.js', __FILE__), array('jquery'), $version);
            wp_localize_script('to-do-list', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('to-do-list-nonce'), // Generate nonce
            ));                
        }        

        // Select profile
        function display_select_todo($select_option=false) {
            ?>
            <select id="select-todo">
                <option value="0" <?php echo ($select_option==0) ? 'selected' : ''?>><?php echo __( '待辦事項', 'your-text-domain' );?></option>
                <option value="1" <?php echo ($select_option==1) ? 'selected' : ''?>><?php echo __( '啟動&授權', 'your-text-domain' );?></option>
                <option value="2" <?php echo ($select_option==2) ? 'selected' : ''?>><?php echo __( '簽核記錄', 'your-text-domain' );?></option>
                <option value="5" <?php echo ($select_option==5) ? 'selected' : ''?>><?php echo __( 'IoT Messages', 'your-text-domain' );?></option>
                <option value="3" <?php echo ($select_option==3) ? 'selected' : ''?>><?php echo __( 'Cron events', 'your-text-domain' );?></option>
                </select>
            <?php
        }

        // Shortcode to display
        function display_shortcode() {
            // Check if the user is logged in
            if (!is_user_logged_in()) user_did_not_login_yet();                
            elseif (is_user_not_in_site()) display_site_profile_NDA();
            else {

                if (isset($_GET['_id'])) {
                    $todo_id = sanitize_text_field($_GET['_id']);
                    $submit_user = get_post_meta($todo_id, 'submit_user', true);
                    $user = get_userdata($submit_user);
                    $submit_time = get_post_meta($todo_id, 'submit_time', true);
                    if ($submit_time) {
                        echo 'Todo #'.$todo_id.' has been submitted by '.$user->display_name.' on '.wp_date(get_option('date_format'), $submit_time).' '.wp_date(get_option('time_format'), $submit_time);
                    } else {
                        echo '<div class="ui-widget" id="result-container">';
                        echo $this->display_todo_dialog($todo_id);
                        echo '</div>';
                    }
                }

                if (!isset($_GET['_select_todo']) && !isset($_GET['_id'])) $_GET['_select_todo'] = '0';
                if ($_GET['_select_todo']=='0') echo $this->display_todo_list();
                if ($_GET['_select_todo']=='1') echo $this->display_job_authorization_list();
                if ($_GET['_select_todo']=='2') $this->display_signature_record();
                if ($_GET['_select_todo']=='3') {
                    ?><script>window.location.replace("/wp-admin/tools.php?page=crontrol_admin_manage_page");</script><?php
                }

                $iot_messages = new iot_messages();
                if ($_GET['_select_todo']=='5') echo $iot_messages->display_iot_message_list();

                if (isset($_GET['_remove_iso_helper_scheduled_events'])) {
                    $this->remove_iso_helper_scheduled_events($_GET['_remove_iso_helper_scheduled_events']);
                    $this->list_all_scheduled_events();
                    exit;
                }

            }
        }

        // Create a todo Post Type
        function register_todo_post_type() {
            $labels = array(
                'menu_name'          => _x('To-Do Items', 'admin menu', 'textdomain'),
            );        
            $args = array(
                'labels'             => $labels,
                'public'             => true,
                'show_in_menu'       => false, // Set this to false to hide from the admin menu
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
            $doc_id = esc_attr(get_post_meta($post->ID, 'doc_id', true));
            $report_id = esc_attr(get_post_meta($post->ID, 'report_id', true));
            $todo_due = esc_attr(get_post_meta($post->ID, 'todo_due', true));
            $submit_user = esc_attr(get_post_meta($post->ID, 'submit_user', true));
            $submit_action = esc_attr(get_post_meta($post->ID, 'submit_action', true));
            $submit_time = esc_attr(get_post_meta($post->ID, 'submit_time', true));
            ?>
            <label for="doc_id"> doc_id: </label>
            <input type="text" id="doc_id" name="doc_id" value="<?php echo $doc_id;?>" style="width:100%" >
            <label for="report_id"> report_id: </label>
            <input type="text" id="report_id" name="report_id" value="<?php echo $report_id;?>" style="width:100%" >
            <label for="todo_due"> todo_due: </label>
            <input type="text" id="todo_due" name="todo_due" value="<?php echo $todo_due;?>" style="width:100%" >
            <label for="submit_user"> submit_user: </label>
            <input type="text" id="submit_user" name="submit_user" value="<?php echo $submit_user;?>" style="width:100%" >
            <label for="submit_action"> submit_action: </label>
            <input type="text" id="submit_action" name="submit_action" value="<?php echo $submit_action;?>" style="width:100%" >
            <label for="submit_time"> submit_time: </label>
            <input type="text" id="submit_time" name="submit_time" value="<?php echo $submit_time;?>" style="width:100%" >
            <?php
        }
        
        function display_todo_list() {
            ?>
            <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '待辦事項', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $this->display_select_todo(0);?></div>
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
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();

            $search_query = sanitize_text_field($_GET['_search']);

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
            );

            $document_ids = $this->get_document_ids();

            if (!$is_site_admin) {
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

                // Check if $document_ids is not an empty array and add it to the meta_query
                if (!empty($document_ids)) {
                    $meta_query[] = array(
                        'key'     => 'doc_id',
                        'value'   => $document_ids,
                        'compare' => 'IN',
                    );
                }

                // If $meta_query has more than just the relation, add it to $args
                if (count($meta_query) > 1) {
                    $args['meta_query'][] = $meta_query;
                }
            }

            // Add meta query for searching across all meta keys
            $document_meta_keys = get_post_type_meta_keys('todo');
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

        function display_todo_dialog($todo_id) {
            // Get the post type of the post with the given ID
            $post_type = get_post_type( $todo_id );
        
            // Check if the post type is 'todo'
            if ( ($post_type != 'todo') && ($post_type != 'document') ) {
                return 'post type is '.$post_type.'. Wrong type!';
            }
            
            $params = array();
            if ( $post_type === 'todo' ) {
                $doc_id = get_post_meta($todo_id, 'doc_id', true);
                $prev_report_id = get_post_meta($todo_id, 'prev_report_id', true);
                //$report_id = get_post_meta($todo_id, 'report_id', true);
                //if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
                $params = array(
                    'doc_id'          => $doc_id,
                    'prev_report_id'  => $prev_report_id,
                    'todo_id'  => $todo_id,
                );                

            }
            
            if ( $post_type === 'document' ) {
                $doc_id = $todo_id;
                $params = array(
                    'doc_id'          => $doc_id,
                );                
            }

            if (empty($doc_id)) return 'post type is '.$post_type.'. doc_id is empty!';
                
            //$profiles_class = new display_profiles();
            //$is_site_admin = $profiles_class->is_site_admin();
    
            ob_start();
            ?>
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo esc_html('Todo: '.get_the_title($todo_id));?></h2>
            <?php /*
            <input type="hidden" id="report-id-backup" value="<?php echo $report_id;?>" />
            <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
            <input type="hidden" id="is-doc-report" value="<?php echo $is_doc_report;?>" />
            */?>
            <fieldset>
            <?php
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            if ($is_doc_report) {
                // doc_report_dialog data

                $params = array(
                    'doc_id'     => $doc_id,
                    'prev_report_id'  => get_post_meta($todo_id, 'prev_report_id', true),
                );                

                $documents_class = new display_documents();
                $documents_class->get_doc_field_contains($params);
            } else {
                // document_dialog data
                $doc_number = get_post_meta($doc_id, 'doc_number', true);
                $doc_title = get_post_meta($doc_id, 'doc_title', true);
                $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
                $doc_category = get_post_meta($doc_id, 'doc_category', true);
                $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
                $profiles_class = new display_profiles();
                ?>
                <label for="doc-number"><?php echo __( '文件編號', 'your-text-domain' );?></label>
                <input type="text" id="doc-number" value="<?php echo esc_html($doc_number);?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-title"><?php echo __( '文件名稱', 'your-text-domain' );?></label>
                <input type="text" id="doc-title" value="<?php echo esc_html($doc_title);?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-revision"><?php echo __( '文件版本', 'your-text-domain' );?></label>
                <input type="text" id="doc-revision" value="<?php echo esc_html($doc_revision);?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-category"><?php echo __( '文件類別', 'your-text-domain' );?></label><br>
                <select id="doc-category" class="text ui-widget-content ui-corner-all" disabled><?php echo $profiles_class->select_doc_category_options($doc_category);?></select>
                <label for="doc-frame"><?php echo __( '文件地址', 'your-text-domain' );?></label>
                <span id="doc-frame-preview" class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>
                <textarea id="doc-frame" rows="3" style="width:100%;" disabled><?php echo $doc_frame;?></textarea>
                <?php
            }
            ?>
            <hr>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                <?php
                    if ( $post_type === 'todo' ) {
                        $query = $this->retrieve_todo_action_list_data($todo_id);
                    }
                    if ( $post_type === 'document' ) {
                        $profiles_class = new display_profiles();
                        $query = $profiles_class->retrieve_doc_action_list_data($todo_id);
                    }                    
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
            <?php
            return ob_get_clean();
        }
        
        function get_todo_dialog_data() {
            $result = array();
            if (isset($_POST['_todo_id'])) {
                $todo_id = sanitize_text_field($_POST['_todo_id']);
                $result['html_contain'] = $this->display_todo_dialog($todo_id);

                $doc_id = get_post_meta($todo_id, 'doc_id', true);
                $post_type = get_post_type( $todo_id );
                if ( $post_type === 'document' ) {
                    $doc_id = $todo_id;
                }

                $documents_class = new display_documents();
                $result['doc_fields'] = $documents_class->get_doc_field_keys($doc_id);
            }
            wp_send_json($result);
        }
        
        function set_todo_dialog_data() {
            if( isset($_POST['_action_id']) ) {
                // action button is clicked
                $action_id = sanitize_text_field($_POST['_action_id']);
                $this->update_todo_dialog_data($action_id);
            }
            wp_send_json($response);
        }
        
        // job-authorization
        function display_job_authorization_list() {
            ?>
            <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '啟動&授權', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $this->display_select_todo(1);?></div>
                    <div style="text-align: right">
                        <input type="text" id="search-job" style="display:inline" placeholder="Search..." />
                    </div>
                </div>

                <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Todo', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Document', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Authorized', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_job_authorization_data($paged);
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

                            //$doc_report_frequence_setting = get_post_meta($doc_id, 'doc_report_frequence_setting', true);
                            //$doc_report_frequence_start_time = get_post_meta($doc_id, 'doc_report_frequence_start_time', true);
                            //if ($doc_report_frequence_setting) $doc_report_frequence_setting .= '('.wp_date(get_option('date_format'), $doc_report_frequence_start_time).' '.wp_date(get_option('time_format'), $doc_report_frequence_start_time).')';
                            ?>
                            <tr id="edit-job-<?php echo $doc_id;?>">
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

        function retrieve_job_authorization_data($paged = 1){
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
            if (!is_array($user_doc_ids)) $user_doc_ids = array();
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();

            $search_query = sanitize_text_field($_GET['_search']);        
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
                        'key'     => 'doc_number',
                        'compare' => 'EXISTS',
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'todo_status',
                            'compare' => 'NOT EXISTS',
                        ),
                        array(
                            'key'     => 'todo_status',
                            'value'   => -2,
                            'compare' => '=',
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

            if (!$is_site_admin) {
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
        
        function display_job_dialog($doc_id) {
/*            
            $params = array();
            if ( $post_type === 'todo' ) {
                $doc_id = get_post_meta($todo_id, 'doc_id', true);
                $prev_report_id = get_post_meta($todo_id, 'prev_report_id', true);
                //$report_id = get_post_meta($todo_id, 'report_id', true);
                //if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
                $params = array(
                    'doc_id'          => $doc_id,
                    'prev_report_id'  => $prev_report_id,
                    'todo_id'  => $todo_id,
                );                

            }
            
            if ( $post_type === 'document' ) {
                $doc_id = $todo_id;
                $params = array(
                    'doc_id'          => $doc_id,
                );                
            }

            if (empty($doc_id)) return 'post type is '.$post_type.'. doc_id is empty!';
                
            //$profiles_class = new display_profiles();
            //$is_site_admin = $profiles_class->is_site_admin();
*/    
            ob_start();
            ?>
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo esc_html('Todo: '.get_the_title($doc_id));?></h2>
            <?php /*
            <input type="hidden" id="report-id-backup" value="<?php echo $report_id;?>" />
            <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
            <input type="hidden" id="is-doc-report" value="<?php echo $is_doc_report;?>" />
            */?>
            <fieldset>
            <?php
            //$is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            //if ($is_doc_report) {
                // doc_report_dialog data

                $params = array(
                    'doc_id'     => $doc_id,
                    //'prev_report_id'  => get_post_meta($todo_id, 'prev_report_id', true),
                );                

                $documents_class = new display_documents();
                $documents_class->get_doc_field_contains($params);
/*                
            } else {
                // document_dialog data
                $doc_number = get_post_meta($doc_id, 'doc_number', true);
                $doc_title = get_post_meta($doc_id, 'doc_title', true);
                $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
                $doc_category = get_post_meta($doc_id, 'doc_category', true);
                $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
                $profiles_class = new display_profiles();
                ?>
                <label for="doc-number"><?php echo __( '文件編號', 'your-text-domain' );?></label>
                <input type="text" id="doc-number" value="<?php echo esc_html($doc_number);?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-title"><?php echo __( '文件名稱', 'your-text-domain' );?></label>
                <input type="text" id="doc-title" value="<?php echo esc_html($doc_title);?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-revision"><?php echo __( '文件版本', 'your-text-domain' );?></label>
                <input type="text" id="doc-revision" value="<?php echo esc_html($doc_revision);?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-category"><?php echo __( '文件類別', 'your-text-domain' );?></label><br>
                <select id="doc-category" class="text ui-widget-content ui-corner-all" disabled><?php echo $profiles_class->select_doc_category_options($doc_category);?></select>
                <label for="doc-frame"><?php echo __( '文件地址', 'your-text-domain' );?></label>
                <span id="doc-frame-preview" class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>
                <textarea id="doc-frame" rows="3" style="width:100%;" disabled><?php echo $doc_frame;?></textarea>
                <?php
            }
*/                
            ?>
            <hr>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                <?php
/*                
                    if ( $post_type === 'todo' ) {
                        $query = $this->retrieve_todo_action_list_data($todo_id);
                    }
                    if ( $post_type === 'document' ) {
                        $profiles_class = new display_profiles();
                        $query = $profiles_class->retrieve_doc_action_list_data($todo_id);
                    }                    
*/                        
                    $profiles_class = new display_profiles();
                    $query = $profiles_class->retrieve_doc_action_list_data($doc_id);

                    if ($query->have_posts()) {
                        while ($query->have_posts()) : $query->the_post();
                            echo '<input type="button" id="job-dialog-button-'.get_the_ID().'" value="'.get_the_title().'" style="margin:5px;" />';
                        endwhile;
                        wp_reset_postdata();
                    }
                ?>
                </div>
                <div style="text-align: right">
                    <input type="button" id="job-dialog-exit" value="Exit" style="margin:5px;" />
                </div>
            </div>
            </fieldset>
            <?php
            return ob_get_clean();
        }
        
        function get_job_dialog_data() {
            $result = array();
            if (isset($_POST['_job_id'])) {
                $job_id = sanitize_text_field($_POST['_job_id']);
                $result['html_contain'] = $this->display_job_dialog($job_id);
/*
                $doc_id = get_post_meta($todo_id, 'doc_id', true);
                $post_type = get_post_type( $todo_id );
                if ( $post_type === 'document' ) {
                    $doc_id = $todo_id;
                }
*/
                $documents_class = new display_documents();
                $result['doc_fields'] = $documents_class->get_doc_field_keys($job_id);
            }
            wp_send_json($result);
        }
        
        function set_job_dialog_data() {
            if( isset($_POST['_action_id']) ) {
                // action button is clicked
                $action_id = sanitize_text_field($_POST['_action_id']);
                $this->update_job_dialog_data($action_id);
            }
            wp_send_json($response);
        }
        
        // to-do-list misc
        function update_todo_dialog_data($action_id=false, $user_id=false) {
            // action button is clicked
            if (!$user_id) $user_id = get_current_user_id();
            $next_job = get_post_meta($action_id, 'next_job', true);
            $todo_id = get_post_meta($action_id, 'todo_id', true);
            $doc_id = get_post_meta($todo_id, 'doc_id', true);

            // Update current todo
            update_post_meta($todo_id, 'submit_user', $user_id );
            update_post_meta($todo_id, 'submit_action', $action_id );
            update_post_meta($todo_id, 'submit_time', time() );

            $prev_report_id = get_post_meta($todo_id, 'prev_report_id', true);
            if ($prev_report_id) update_post_meta($prev_report_id, 'todo_status', $next_job );

            $new_post = array(
                'post_title'    => 'New doc-report',
                'post_status'   => 'publish',
                'post_author'   => $user_id,
                'post_type'     => 'doc-report',
            );    
            $new_report_id = wp_insert_post($new_post);
            update_post_meta($new_report_id, 'doc_id', $doc_id);
            update_post_meta($new_report_id, 'todo_status', $next_job);
            // Update the post meta
            $params = array(
                'doc_id'     => $doc_id,
            );                
            $documents_class = new display_documents();
            $query = $documents_class->retrieve_doc_field_data($params);
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $documents_class->update_doc_field_contains($new_report_id, get_the_ID());
                endwhile;
                wp_reset_postdata();
            }            

            // set next todo and actions
            $params = array(
                'user_id' => $user_id,
                'action_id' => $action_id,
                'prev_report_id' => $new_report_id,
            );        
            if ($next_job>0) $this->update_next_todo_and_actions($params);
        }
        
        function update_job_dialog_data($action_id=false, $user_id=false) {
            // action button is clicked
            if (!$user_id) $user_id = get_current_user_id();
            $next_job = get_post_meta($action_id, 'next_job', true);
            $doc_id = get_post_meta($action_id, 'doc_id', true);

            // set current doc-report
            $new_post = array(
                'post_title'    => 'New doc-report',
                'post_status'   => 'publish',
                'post_author'   => $user_id,
                'post_type'     => 'doc-report',
            );    
            $new_report_id = wp_insert_post($new_post);
            update_post_meta($new_report_id, 'doc_id', $doc_id);
            update_post_meta($new_report_id, 'todo_status', $next_job);

            $params = array(
                'doc_id'     => $doc_id,
            );                
            $documents_class = new display_documents();
            $query = $documents_class->retrieve_doc_field_data($params);
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $documents_class->update_doc_field_contains($new_report_id, get_the_ID());
                endwhile;
                wp_reset_postdata();
            }            

            // set current todo
            $todo_title = get_the_title($doc_id);
            $new_post = array(
                'post_title'    => $todo_title,
                'post_status'   => 'publish',
                'post_author'   => $user_id,
                'post_type'     => 'todo',
            );    
            $new_todo_id = wp_insert_post($new_post);
            update_post_meta($new_todo_id, 'doc_id', $doc_id);
            update_post_meta($new_todo_id, 'prev_report_id', $new_report_id);
            update_post_meta($new_todo_id, 'submit_user', $user_id );
            update_post_meta($new_todo_id, 'submit_action', $action_id );
            update_post_meta($new_todo_id, 'submit_time', time() );

            // set next todo and actions
            $params = array(
                'user_id' => $user_id,
                'action_id' => $action_id,
                'prev_report_id' => $new_report_id,
            );        
            if ($next_job>0) $this->update_next_todo_and_actions($params);
        }
        
        function update_next_todo_and_actions($args = array()) {
            // 1. From update_todo_dialog_data(), create a next_todo based on the $args['action_id'], $args['user_id'] and $args['prev_report_id']
            // 2. From set_todo_from_doc_report(), create a next_todo based on the $args['next_job'] and $args['prev_report_id']
            // 3. From schedule_event_callback($params), create a next_todo based on the $args['doc_id']

            $user_id = isset($args['user_id']) ? $args['user_id'] : get_current_user_id();
            $user_id = ($user_id) ? $user_id : 1;
            $action_id = isset($args['action_id']) ? $args['action_id'] : 0;
            $prev_report_id = isset($args['prev_report_id']) ? $args['prev_report_id'] : 0;

            // Find the next_job, next_leadtime, and 
            if ($action_id > 0) {
                $doc_id = get_post_meta($prev_report_id, 'doc_id', true);
                $next_job      = get_post_meta($action_id, 'next_job', true);
                $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
                if (empty($next_leadtime)) $next_leadtime=86400;
            } else {
                // set_todo_from_doc_report() and frquence doc_report
                $next_job = isset($args['next_job']) ? $args['next_job'] : 0;
                if ($next_job==0) {
                    // frquence doc_report
                    $doc_id = isset($args['doc_id']) ? $args['doc_id'] : 0;
                    $next_job = $doc_id;
                }
                $next_leadtime = 86400;
            }
        
            if ($next_job>0) $todo_title = get_the_title($next_job);
            if ($next_job==-1) $todo_title = __( '發行', 'your-text-domain' );
            if ($next_job==-2) $todo_title = __( '廢止', 'your-text-domain' );
        
            $params = array(
                'todo_title' => $todo_title,
                'user_id' => $user_id,
                'action_id' => $action_id,
                'doc_id' => $doc_id,
                'prev_report_id' => $prev_report_id,
                'next_job' => $next_job,
                'next_leadtime' => $next_leadtime,
            );

            // Try to!! Create the new To-do with audit-items If meta "_audit_plan" of $prev_report_id is present
            if ($prev_report_id) {
                $audit_ids = get_post_meta($prev_report_id, '_audit_plan', true);
                $department_id = get_post_meta($prev_report_id, '_audited_department', true);
                $category_id = get_post_meta($prev_report_id, '_iso_category', true);
                $filtered_audit_ids = $this->filtered_audit_ids_by_department($audit_ids, $department_id, $category_id);                    
            }
            if ($filtered_audit_ids) {
                if (is_array($filtered_audit_ids)) {
                    foreach ($filtered_audit_ids as $audit_id) {
                        $params['audit_id'] = $audit_id;
                        $this->create_new_todo_for_next_job($params);
                    }
                }    
            } else {
                if (!is_array($filtered_audit_ids)) {
                    $this->create_new_todo_for_next_job($params);
                }
            }
        }

        function create_new_todo_for_next_job($args = array()) {
            $todo_title = isset($args['todo_title']) ? $args['todo_title'] : 0;
            $user_id = isset($args['user_id']) ? $args['user_id'] : get_current_user_id();
            $action_id = isset($args['action_id']) ? $args['action_id'] : 0;
            $doc_id = isset($args['doc_id']) ? $args['doc_id'] : 0;
            $prev_report_id = isset($args['prev_report_id']) ? $args['prev_report_id'] : 0;
            $next_job = isset($args['next_job']) ? $args['next_job'] : 0;
            $next_leadtime = isset($args['next_leadtime']) ? $args['next_leadtime'] : 0;
            $audit_id = isset($args['audit_id']) ? $args['audit_id'] : 0;

            // Create a new To-do for next_job
            $new_post = array(
                'post_title'    => $todo_title,
                'post_status'   => 'publish',
                'post_author'   => $user_id,
                'post_type'     => 'todo',
            );    
            $new_todo_id = wp_insert_post($new_post);
            update_post_meta($new_todo_id, 'todo_due', time()+$next_leadtime );
            if ($prev_report_id) update_post_meta($new_todo_id, 'prev_report_id', $prev_report_id );

            if ($audit_id) update_post_meta($new_todo_id, 'audit_item', $audit_id );

            if ($next_job>0) {
                update_post_meta($new_todo_id, 'doc_id', $next_job );
                $doc_number = get_post_meta($next_job, 'doc_number', true);
                // if the meta "doc_number" of $next_job from set_todo_dialog_data() is not presented
                if (empty($doc_number)) {
                    $doc_id = get_post_meta($todo_id, 'doc_id', true);
                    update_post_meta($new_todo_id, 'doc_id', $doc_id );
                }
            }

            if ($next_job==-1 || $next_job==-2) {
                update_post_meta($new_todo_id, 'submit_user', $user_id);
                update_post_meta($new_todo_id, 'submit_action', $action_id);
                update_post_meta($new_todo_id, 'submit_time', time());
                if ($prev_report_id) update_post_meta($prev_report_id, 'todo_status', $next_job );
                // Notice the persons in site
                $this->notice_the_persons_in_site($new_todo_id, $next_job);
            }

            if ($next_job>0) {
                // Create the new Action list for next_job 
                $profiles_class = new display_profiles();
                $query = $profiles_class->retrieve_doc_action_list_data($next_job);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $new_post = array(
                            'post_title'    => get_the_title(),
                            //'post_content'  => get_post_field('post_content', get_the_ID()),
                            'post_content'  => get_the_content(),
                            'post_status'   => 'publish',
                            'post_author'   => $user_id,
                            'post_type'     => 'action',
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
            $todo_title = get_the_title($todo_id);
            $doc_id = get_post_meta($todo_id, 'doc_id', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_title .= '('.$doc_number.')';
            $todo_due = get_post_meta($todo_id, 'todo_due', true);
            $due_date = wp_date( get_option('date_format'), $todo_due );
            $text_message='You are in '.$todo_title.' position. You have to sign off the '.$doc_title.' before '.$due_date.'.';
            $text_message = '你在「'.$todo_title.'」的職務有一份文件「'.$doc_title.'」需要在'.$due_date.'前簽核完成，你可以點擊下方連結查看該文件。';
            $link_uri = home_url().'/to-do-list/?_id='.$todo_id;
        
            $line_bot_api = new line_bot_api();
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

            // Check if Employees or Department in report
            $report_id = get_post_meta($todo_id, 'prev_report_id', true);
            if ($report_id) {
                $department_id = get_post_meta($report_id, '_department', true);
                $user_ids = get_post_meta($department_id, 'user_ids', true);
                if (is_array($user_ids)) {
                    foreach ($user_ids as $user_id) {
                        $user = get_userdata($user_id);
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

                $user_ids = get_post_meta($report_id, '_employees', true);
                if (is_array($user_ids)) {
                    foreach ($user_ids as $user_id) {
                        $user = get_userdata($user_id);
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
            //if ($is_doc_report) $doc_title .= '(電子表單)';
            $doc_title .= '('.$doc_number.')';
            $submit_time = get_post_meta($todo_id, 'submit_time', true);
            $text_message=$doc_title.' has been published on '.wp_date( get_option('date_format'), $submit_time ).'.';

            $text_message = '文件「'.$doc_title.'」已經在'.wp_date( get_option('date_format'), $submit_time );
            if ($next_job==-1) $text_message .= '發行，你可以點擊下方連結查看該文件。';
            if ($next_job==-2) $text_message .= '廢止，你可以點擊下方連結查看該文件。';
            $link_uri = home_url().'/display-documents/?_id='.$doc_id;
            if ($report_id) $link_uri = home_url().'/display-documents/?_id='.$report_id;
        
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

        function filtered_audit_ids_by_department($audit_ids=array(), $department_id=false, $category_id=false) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $filtered_audit_ids = array();
            if (is_array($audit_ids)) {
                $filtered_audit_ids = array();
                foreach ($audit_ids as $audit_id) {
                    $clause_no = get_post_meta($audit_id, 'clause_no', true);
                    $sorting_key = get_post_meta($audit_id, 'sorting_key', true);
                    $field_type = get_post_meta($audit_id, 'field_type', true);
                    $field_key = preg_replace('/[^a-zA-Z0-9_]/', '', 'department'.$site_id.$category_id.$clause_no.$sorting_key);
                    $field_value = get_post_meta($site_id, $field_key, true);
                
                    if ($department_id == $field_value) {
                        $filtered_audit_ids[] = $audit_id;
                    }
                }
                return $filtered_audit_ids;
            }
            // Now $filtered_audit_ids contains the filtered audit IDs
            return false;
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

        function get_document_ids() {
            $current_user_id = get_current_user_id();
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
        
            if (!is_array($user_doc_ids)) {
                $user_doc_ids = array(); // Ensure $user_doc_ids is an array                
            }
        
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'     => 'todo_status',
                        'value'   => $user_doc_ids,
                        'compare' => 'IN',
                    ),
                ),
                'fields' => 'ids', // Only get post IDs
            );
            // Perform the second query to get documents filtered by todo_status
            $query = new WP_Query($args);
        
            // Fetch the final set of document IDs
            $document_ids = $query->posts;
        
            // Return the array of filtered document IDs
            return $document_ids;
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
        
        // signature_record
        function display_signature_record() {
            ?>
            <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '簽核記錄', 'your-text-domain' );?></h2>
            
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $this->display_select_todo(2);?></div>
                    <div style="text-align: right">
                        <input type="text" id="search-todo" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
                <?php echo $this->get_signature_record_list();?>
                <p style="background-color:lightblue;"><?php echo __( 'Total Submissions:', 'your-text-domain' );?> <?php echo $this->count_signature_records();?></p>
            </div>
            <?php
        }
        
        function get_signature_record_list($doc=false, $report=false ) {
            ob_start();
            $current_user_id = get_current_user_id();
            $current_site = get_user_meta($current_user_id, 'site_id', true);
            ?>
            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Time', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Document', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Todo', 'your-text-domain' );?></th>
                            <th><?php echo __( 'User', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Action', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Next', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_signature_record_data($doc, $report, $paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $doc_id = get_post_meta(get_the_ID(), 'doc_id', true);
                            $site_id = get_post_meta($doc_id, 'site_id', true);
                            $doc_title = get_post_meta($doc_id, 'doc_title', true);
                            $report_id = get_post_meta(get_the_ID(), 'prev_report_id', true);
                            if ($report_id) $doc_title .= '(#'.$report_id.')';
                            $submit_action = get_post_meta(get_the_ID(), 'submit_action', true);
                            $submit_user = get_post_meta(get_the_ID(), 'submit_user', true);
                            $submit_time = get_post_meta(get_the_ID(), 'submit_time', true);
                            $next_job = get_post_meta($submit_action, 'next_job', true);
                            $job_title = ($next_job==-1) ? __( '發行', 'your-text-domain' ) : get_the_title($next_job);
                            $job_title = ($next_job==-2) ? __( '廢止', 'your-text-domain' ) : $job_title;
        
                            if ($current_site==$site_id) { // Aditional condition to filter the data
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
                            }
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
        
        function count_signature_records(){
            $current_user_id = get_current_user_id();
            $current_site = get_user_meta($current_user_id, 'site_id', true);
            $x = 0;
            $query = $this->retrieve_signature_record_data(false, false, 0);
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
        
        function retrieve_signature_record_data($doc_id=false, $report_id=false, $paged=1){
            $args = array(
                'post_type'      => 'todo',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
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
                    array(
                        'key'     => 'prev_report_id',
                        'compare' => 'EXISTS',
                    ),
                ),
                'orderby'        => 'meta_value',
                'meta_key'       => 'submit_time',
                'order'          => 'DESC',
            );

            if ($paged==0) $args['posts_per_page'] = -1;

            if ($doc_id) {
                $args['meta_query'][] = array(
                    'key'   => 'doc_id',
                    'value' => $doc_id,
                );
            }
        
            if ($report_id) {
                $args['meta_query'][] = array(
                    'key'   => 'prev_report_id',
                    'value' => $report_id,
                );
            }
        
            $query = new WP_Query($args);
            return $query;
        }

        // doc-report frequence setting
        function select_doc_report_frequence_setting_option($selected_option = false) {
            $options = '<option value="">'.__( 'None', 'your-text-domain' ).'</option>';
            $selected = ($selected_option === "yearly") ? 'selected' : '';
            $options .= '<option value="yearly" '.$selected.'>' . __( '每年', 'your-text-domain' ) . '</option>';
            $selected = ($selected_option === "half-yearly") ? 'selected' : '';
            $options .= '<option value="half-yearly" '.$selected.'>' . __( '每半年', 'your-text-domain' ) . '</option>';
            $selected = ($selected_option === "bimonthly") ? 'selected' : '';
            $options .= '<option value="bimonthly" '.$selected.'>' . __( '每二月', 'your-text-domain' ) . '</option>';
            $selected = ($selected_option === "weekly") ? 'selected' : '';
            $options .= '<option value="monthly" '.$selected.'>' . __( '每月', 'your-text-domain' ) . '</option>';
            $selected = ($selected_option === "weekly") ? 'selected' : '';
            $options .= '<option value="biweekly" '.$selected.'>' . __( '每二週', 'your-text-domain' ) . '</option>';
            $selected = ($selected_option === "biweekly") ? 'selected' : '';
            $options .= '<option value="weekly" '.$selected.'>' . __( '每週', 'your-text-domain' ) . '</option>';
            $selected = ($selected_option === "daily") ? 'selected' : '';
            $options .= '<option value="daily" '.$selected.'>' . __( '每日', 'your-text-domain' ) . '</option>';
            return $options;
        }
        
        function schedule_post_event_callback($args) {
            $interval = $args['interval'];
            $start_time = $args['start_time'];
            $prev_start_time = isset($args['prev_start_time']) ? $args['prev_start_time'] : null;
        
            // Clear the previous scheduled event if it exists
            if ($prev_start_time) {
                $prev_hook_name = 'iso_helper_post_event_' . $prev_start_time;
                //$this->remove_iso_helper_scheduled_events($prev_hook_name);
            }
        
            $hook_name = 'iso_helper_post_event_' . $start_time;

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
                    // Use a custom interval for monthly scheduling
                    wp_schedule_event($start_time, 'monthly', $hook_name, array($args));
                    break;
                case 'bimonthly':
                    // Calculate timestamp for next occurrence (every 2 months)
                    wp_schedule_event($start_time, 'bimonthly', $hook_name, array($args));
                    break;
                case 'half-yearly':
                    // Calculate timestamp for next occurrence (every 6 months)
                    wp_schedule_event($start_time, 'half_yearly', $hook_name, array($args));
                    break;
                case 'yearly':
                    // Use a custom interval for yearly scheduling
                    wp_schedule_event($start_time, 'yearly', $hook_name, array($args));
                    break;
                default:
                    // Handle invalid interval
                    return new WP_Error('invalid_interval', 'The specified interval is invalid.');
            }

            // Store the hook name in options (outside switch statement)
            update_option('schedule_event_hook_name', $hook_name);
        
            // Return the hook name for later use
            return $hook_name;
        }

        function iso_helper_cron_schedules($schedules) {
            $schedules['biweekly'] = array(
                'interval' => 2 * WEEK_IN_SECONDS, // 2 weeks in seconds
                'display'  => __('Every Two Weeks'),
            );
            $schedules['monthly'] = array(
                'interval' => 30 * DAY_IN_SECONDS, // Approximate monthly interval
                'display'  => __('Monthly'),
            );
            $schedules['bimonthly'] = array(
                'interval' => 60.5 * DAY_IN_SECONDS, // Approximate monthly interval
                'display'  => __('Every Two Months'),
            );
            $schedules['half_yearly'] = array(
                'interval' => 182.5 * DAY_IN_SECONDS, // Approximate half-year interval
                'display'  => __('Every Six Months'),
            );
            $schedules['yearly'] = array(
                'interval' => 365 * DAY_IN_SECONDS, // Approximate yearly interval
                'display'  => __('Yearly'),
            );
            return $schedules;
        }

        // Method for the callback function
        public function schedule_event_callback($params) {
            $this->update_next_todo_and_actions($params);
        }
        
        // Method to schedule the event and add the action
        public function schedule_event_and_action() {
            // Retrieve the hook name from options
            $hook_name = get_option('schedule_event_hook_name');
            // Add the action with the dynamic hook name
            add_action($hook_name, array($this, 'schedule_event_callback'));
        }
            
        function remove_iso_helper_scheduled_events($remove_name='iso_') {
            if (current_user_can('administrator')) {
                // Get all scheduled events
                $cron_array = _get_cron_array();
        
                // Check if there are any scheduled events
                if (empty($cron_array)) {
                    echo 'No scheduled events found.';
                    return;
                }
        
                // Loop through the scheduled events
                foreach ($cron_array as $timestamp => $cron) {
                    foreach ($cron as $hook_name => $events) {
                        if (empty($hook_name) || strpos($hook_name, $remove_name) === 0) {
                            foreach ($events as $event) {
                                // Unschedule the event
                                wp_unschedule_event($timestamp, $hook_name, $event['args']);
                            }
                        }
                    }
                }
        
                echo 'Removed all scheduled events with hook names starting with '.$remove_name;
            } else {
                echo 'You do not have enough permission to perform this action.';
            }
        }

        function list_all_scheduled_events() {
            if (current_user_can('administrator')) {
                // Get all scheduled events
                $cron_array = _get_cron_array();
        
                // Check if there are any scheduled events
                if (empty($cron_array)) {
                    echo 'No scheduled events found.';
                    return;
                }
        
                echo '<h3>Scheduled Events</h3>';
                echo '<table border="1" cellpadding="10" cellspacing="0">';
                echo '<tr><th>Hook Name</th><th>Next Run (UTC)</th><th>Arguments</th></tr>';
        
                // Loop through the scheduled events
                foreach ($cron_array as $timestamp => $cron) {
                    foreach ($cron as $hook_name => $events) {
                        foreach ($events as $event) {
                            echo '<tr>';
                            echo '<td>' . esc_html($hook_name) . '</td>';
                            echo '<td>' . date('Y-m-d H:i:s', $timestamp) . '</td>';
                            echo '<td>' . json_encode($event['args']) . '</td>';
                            echo '</tr>';
                        }
                    }
                }
        
                echo '</table>';
            } else {
                echo 'You do not have enough permission to display this.';
            }
        }

        public function process_authorized_action_posts_daily() {
            // process the todo-list first
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
                                    $this->update_job_dialog_data($action_id, $user_id);
                                }
                            }
                        endwhile;
                        wp_reset_postdata();
                    endif;
              }    
                wp_reset_postdata();
            }
/*
            // process the doc-job-list after
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'doc_number',
                        'compare' => 'EXISTS',
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'todo_status',
                            'compare' => 'NOT EXISTS',
                        ),
                        array(
                            'key'     => 'todo_status',
                            'value'   => -2,
                            'compare' => '=',
                        ),
                    ),
                ),
            );
            $query = new WP_Query($args);

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $doc_id = get_the_ID();

                    $profiles_class = new display_profiles();
                    $action_query = $profiles_class->retrieve_doc_action_list_data($doc_id);
                    if ($action_query->have_posts()) :
                        while ($action_query->have_posts()) : $action_query->the_post();
                            $action_id = get_the_ID();
                            $action_authorized_ids = $profiles_class->is_action_authorized($action_id);
                            if ($action_authorized_ids) {
                                foreach ($action_authorized_ids as $user_id) {
                                    $this->update_todo_dialog_data($action_id, $user_id);
                                }    
                            }
                        endwhile;
                        wp_reset_postdata();
                    endif;
                }    
                wp_reset_postdata();
            }
*/                
        }
    }
    $todo_class = new to_do_list();
}


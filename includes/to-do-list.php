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
            add_action( 'init', array( $this, 'register_action_post_type' ) );
            add_filter( 'cron_schedules', array( $this, 'iso_helper_cron_schedules' ) );

            add_action( 'wp_ajax_get_todo_dialog_data', array( $this, 'get_todo_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_todo_dialog_data', array( $this, 'get_todo_dialog_data' ) );
            add_action( 'wp_ajax_set_todo_dialog_data', array( $this, 'set_todo_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_todo_dialog_data', array( $this, 'set_todo_dialog_data' ) );    
        }

        function enqueue_to_do_list_scripts() {
            $version = time(); // Update this version number when you make changes
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
        
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
                <option value="1" <?php echo ($select_option==1) ? 'selected' : ''?>><?php echo __( '啟動授權', 'your-text-domain' );?></option>
                <option value="2" <?php echo ($select_option==2) ? 'selected' : ''?>><?php echo __( '簽核記錄', 'your-text-domain' );?></option>
                <option value="3" <?php echo ($select_option==3) ? 'selected' : ''?>><?php echo __( 'Scheduled list', 'your-text-domain' );?></option>
                <option value="4" <?php echo ($select_option==4) ? 'selected' : ''?>><?php echo __( 'HTTP Clients', 'your-text-domain' );?></option>
                <option value="5" <?php echo ($select_option==5) ? 'selected' : ''?>><?php echo __( 'IoT Messages', 'your-text-domain' );?></option>
                </select>
            <?php
        }

        // Shortcode to display
        function display_shortcode() {
            // Check if the user is logged in
            if (is_user_logged_in()) {
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

                if ($_GET['_select_todo']=='1') echo $this->display_job_authorization();
                if ($_GET['_select_todo']=='2') $this->display_signature_record();
                if ($_GET['_select_todo']=='3') {
                    ?><script>window.location.replace("/wp-admin/tools.php?page=crontrol_admin_manage_page");</script><?php
                }
                //$this->list_all_scheduled_events();
                $http_client = new http_client();
                if ($_GET['_select_todo']=='4') echo $http_client->display_http_client_list();
                if ($_GET['_select_todo']=='5') echo $http_client->display_iot_message_list();

                if (isset($_GET['_remove_iso_helper_scheduled_events'])) {
                    $this->remove_iso_helper_scheduled_events($_GET['_remove_iso_helper_scheduled_events']);
                    $this->list_all_scheduled_events();
                    exit;
                }

                //if ($_GET['_select_todo']!='1' && $_GET['_select_todo']!='2' && !isset($_GET['_id'])) $this->display_todo_list();
                if (!isset($_GET['_select_todo']) || $_GET['_select_todo']=='0') echo $this->display_todo_list();

            } else {
                user_did_not_login_yet();
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
        
        // Register action post type
        function register_action_post_type() {
            $labels = array(
                'menu_name'          => _x('Actions', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'action', $args );
        }
        
        function display_job_authorization() {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $current_user = get_userdata( $current_user_id );
            ?>
            <div class="ui-widget" id="result-container">
            <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
            <h2 style="display:inline;"><?php echo __( '啟動授權', 'your-text-domain' );?></h2>

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
                            <th><?php echo __( 'Authorize', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $current_page = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_job_authorization_data($current_page);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages

                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $doc_id = get_the_ID();
                            $job_title = get_the_title();
                            $job_number = get_post_meta(get_the_ID(), 'job_number', true);
                            if ($job_number) $job_title .= '('.$job_number.')';
                            $doc_title = get_post_meta($doc_id, 'doc_title', true);
                            $doc_number = get_post_meta($doc_id, 'doc_number', true);
                            if ($doc_number) $doc_title .= '('.$doc_number.')';
                            $doc_report_frequence_setting = get_post_meta($doc_id, 'doc_report_frequence_setting', true);
                            $doc_report_frequence_start_time = get_post_meta($doc_id, 'doc_report_frequence_start_time', true);
                            if ($doc_report_frequence_setting) $doc_report_frequence_setting .= '('.wp_date(get_option('date_format'), $doc_report_frequence_start_time).' '.wp_date(get_option('time_format'), $doc_report_frequence_start_time).')';
                            ?>
                            <tr id="edit-job-authorization-<?php the_ID(); ?>">
                                <td style="text-align:center;"><?php echo esc_html($job_title); ?></td>
                                <td><?php echo esc_html($doc_title); ?></td>
                                <td style="text-align:center;"><?php echo esc_html($doc_report_frequence_setting); ?></td>
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

        function retrieve_job_authorization_data($current_page = 1){
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');

            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
            if (!is_array($user_doc_ids)) $user_doc_ids = array();
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();

            $search_query = sanitize_text_field($_GET['_search']);        
            //if ($search_query) {
                $args = array(
                    'post_type'      => 'document',
                    'posts_per_page' => $posts_per_page,
                    'paged'          => $current_page,
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

        function display_job_authorization_dialog($todo_id) {
            // Get the post type of the post with the given ID
            $post_type = get_post_type( $todo_id );
        
            // Check if the post type is 'todo'
            if ( ($post_type != 'todo') && ($post_type != 'document') ) {
                return 'post type is '.$post_type.'. Wrong type!';
            }
            
            if ( $post_type === 'todo' ) {
                $report_id = get_post_meta($todo_id, 'report_id', true);
                $doc_id = get_post_meta($todo_id, 'doc_id', true);
                if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
            }
            
            if ( $post_type === 'document' ) {
                $doc_id = $todo_id;
            }
            
            if (empty($doc_id)) return 'post type is '.$post_type.'. doc_id is empty!';
        
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
            $doc_category = get_post_meta($doc_id, 'doc_category', true);
            $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            //if ($is_doc_report) $report_id = get_post_meta($todo_id, 'prev_report_id', true);
        
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();
    
            ob_start();
            ?>
            <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
            <h2 style="display:inline;"><?php echo esc_html('Todo: '.get_the_title($todo_id));?></h2>
            <input type="hidden" id="report-id-backup" value="<?php echo $report_id;?>" />
            <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
            <input type="hidden" id="is-doc-report" value="<?php echo $is_doc_report;?>" />
            <fieldset>
            <?php
            if ($is_doc_report) {
                // doc_report_dialog data
                $params = array(
                    'doc_id'     => $doc_id,
                    //'report_id'     => $report_id,
                    'report_id'  => get_post_meta($todo_id, 'prev_report_id', true),
                );                
                $documents_class = new display_documents();
                $documents_class->display_doc_field_result($params);
            } else {
                // document_dialog data
                $profiles_class = new display_profiles();
                ?>
                <label for="doc-number"><?php echo __( '文件編號', 'your-text-domain' );?></label>
                <input type="text" id="doc-number" value="<?php echo esc_html($doc_number);?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-title"><?php echo __( '文件名稱', 'your-text-domain' );?></label>
                <input type="text" id="doc-title" value="<?php echo esc_html($doc_title);?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-revision"><?php echo __( '文件版本', 'your-text-domain' );?></label>
                <input type="text" id="doc-revision" value="<?php echo esc_html($doc_revision);?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-category"><?php echo __( '文件類別', 'your-text-domain' );?></label><br>
                <select id="doc-category" class="text ui-widget-content ui-corner-all" disabled><?php echo $profiles_class->select_doc_category_option_data($doc_category);?></select>
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
        


        function display_todo_list() {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $current_user = get_userdata( $current_user_id );
            ?>
            <div class="ui-widget" id="result-container">
            <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
            <h2 style="display:inline;"><?php echo __( '待辦事項', 'your-text-domain' );?></h2>
                <div id="todo-setting-div" style="display:none">
                <fieldset>
                    <label for="display-name">Name : </label>
                    <input type="text" id="display-name" value="<?php echo $current_user->display_name;?>" class="text ui-widget-content ui-corner-all" disabled />
                    <label for="site-title"> Site: </label>
                    <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
                    <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
                </fieldset>
                </div>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $this->display_select_todo(0);?></div>
                    <div style="text-align: right">
                        <input type="text" id="search-todo" style="display:inline" placeholder="Search..." />
                        <span id="todo-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
                    </div>
                </div>

            <fieldset>
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
                    $query = $this->retrieve_todo_list_data($current_page);
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
                                $todo_title = get_the_title($doc_id);
                                $todo_due = get_post_meta(get_the_ID(), 'todo_status', true);
                                if ($todo_due==-1) $todo_due='發行';
                            }

                            $doc_number = get_post_meta($doc_id, 'doc_number', true);
                            $doc_title = get_post_meta($doc_id, 'doc_title', true);
                            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                            if ($is_doc_report) $doc_title .= '(電子表單)';
                            if (!$is_doc_report) $doc_title .= '('.$doc_number.')';
                            //if ($report_id) $doc_title .= '(Report#' . $report_id . ')';                            
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

        function retrieve_todo_list_data($current_page = 1){
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');

            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
            if (!is_array($user_doc_ids)) $user_doc_ids = array();
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();

            $search_query = sanitize_text_field($_GET['_search']);
/*                    
            if ($search_query) {
                $args = array(
                    'post_type'      => 'document',
                    'posts_per_page' => $posts_per_page,
                    'paged'          => $current_page,
                    //'post__in'       => $user_doc_ids, // Array of document post IDs
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

            } else {
*/             
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

                if (!$is_site_admin) {
                    // Add a new meta query
                    $args['meta_query'][] = array(
                        'key'     => 'doc_id',
                        'value'   => $user_doc_ids, // Value is the array of user doc IDs
                        'compare' => 'IN',
                    );    
                }
            //}

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
                if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
            }
            
            if ( $post_type === 'document' ) {
                $doc_id = $todo_id;
            }
            
            if (empty($doc_id)) return 'post type is '.$post_type.'. doc_id is empty!';
        
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
            $doc_category = get_post_meta($doc_id, 'doc_category', true);
            $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            //if ($is_doc_report) $report_id = get_post_meta($todo_id, 'prev_report_id', true);
        
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();
    
            ob_start();
            ?>
            <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
            <h2 style="display:inline;"><?php echo esc_html('Todo: '.get_the_title($todo_id));?></h2>
            <input type="hidden" id="report-id-backup" value="<?php echo $report_id;?>" />
            <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
            <input type="hidden" id="is-doc-report" value="<?php echo $is_doc_report;?>" />
            <fieldset>
            <?php
            if ($is_doc_report) {
                // doc_report_dialog data
                $params = array(
                    'doc_id'     => $doc_id,
                    //'report_id'     => $report_id,
                    'report_id'  => get_post_meta($todo_id, 'prev_report_id', true),
                );                
                $documents_class = new display_documents();
                $documents_class->display_doc_field_result($params);
            } else {
                // document_dialog data
                $profiles_class = new display_profiles();
                ?>
                <label for="doc-number"><?php echo __( '文件編號', 'your-text-domain' );?></label>
                <input type="text" id="doc-number" value="<?php echo esc_html($doc_number);?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-title"><?php echo __( '文件名稱', 'your-text-domain' );?></label>
                <input type="text" id="doc-title" value="<?php echo esc_html($doc_title);?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-revision"><?php echo __( '文件版本', 'your-text-domain' );?></label>
                <input type="text" id="doc-revision" value="<?php echo esc_html($doc_revision);?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-category"><?php echo __( '文件類別', 'your-text-domain' );?></label><br>
                <select id="doc-category" class="text ui-widget-content ui-corner-all" disabled><?php echo $profiles_class->select_doc_category_option_data($doc_category);?></select>
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
                $documents_class = new display_documents();
                $result['doc_fields'] = $documents_class->display_doc_field_keys($doc_id);
            }
            wp_send_json($result);
        }
        
        function set_todo_dialog_data() {
            if( isset($_POST['_action_id']) ) {
                // action button is clicked
                $current_user_id = get_current_user_id();
                $action_id = sanitize_text_field($_POST['_action_id']);
                $next_job = get_post_meta($action_id, 'next_job', true);
                $todo_id = get_post_meta($action_id, 'todo_id', true);
        
                // Create new todo if the meta key 'todo_id' does not exist
                if ( empty( $todo_id ) ) {
                    $doc_id = get_post_meta($action_id, 'doc_id', true);
                    $todo_title = get_the_title($doc_id);
                    $report_id = sanitize_text_field($_POST['_report_id']);
                    //if ($report_id) $todo_title = '(Report#'.$report_id.')'; 
                    $new_post = array(
                        'post_title'    => $todo_title,
                        'post_status'   => 'publish',
                        'post_author'   => $current_user_id,
                        'post_type'     => 'todo',
                    );    
                    $todo_id = wp_insert_post($new_post);
                    update_post_meta( $todo_id, 'doc_id', $doc_id);
                    //if ($doc_id) update_post_meta( $todo_id, 'doc_id', $doc_id);
                    //if ($report_id) update_post_meta( $todo_id, 'report_id', $report_id);
                }
        
                // Update current todo
                update_post_meta( $todo_id, 'submit_user', $current_user_id );
                update_post_meta( $todo_id, 'submit_action', $action_id );
                update_post_meta( $todo_id, 'submit_time', time() );

                $doc_id = get_post_meta($todo_id, 'doc_id', true);
                if ($doc_id) update_post_meta( $doc_id, 'todo_status', $next_job );

                $prev_report_id = get_post_meta($todo_id, 'prev_report_id', true);
                if ($prev_report_id) update_post_meta( $prev_report_id, 'todo_status', $next_job );

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
                    $documents_class = new display_documents();
                    $query = $documents_class->retrieve_doc_field_data($params);
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
                if ($next_job>0) $this->set_next_todo_and_actions($params);
            }
            wp_send_json($response);
        }
        
        // to-do-list misc
        function set_next_todo_and_actions($args = array()) {
            // 1. From set_todo_dialog_data(), create a next_todo based on the $args['action_id'], $args['todo_id'] and $args['prev_report_id']
            // 2. From set_todo_from_doc_report(), create a next_todo based on the $args['next_job'] and $args['prev_report_id']
            // 3. From iso_helper_post_event_callback($params), create a next_todo based on the $args['doc_id']
        
            $current_user_id = get_current_user_id();
            $action_id = isset($args['action_id']) ? $args['action_id'] : 0;
            $prev_report_id = isset($args['prev_report_id']) ? $args['prev_report_id'] : 0;
        
            // Find the next_job, next_leadtime, and 
            if ($action_id > 0) {
                $next_job      = get_post_meta($action_id, 'next_job', true);
                $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
                if (empty($next_leadtime)) $next_leadtime=86400;
            }
        
            // for set_todo_from_doc_report() and frquence doc_report to generate a new todo
            if ($action_id==0) {  
                $next_job = isset($args['next_job']) ? $args['next_job'] : 0;
                if (!$next_job) $doc_id = isset($args['doc_id']) ? $args['doc_id'] : 0;
                if (!$next_job) $next_job = $doc_id;
                $todo_title = get_the_title($next_job);
                $next_leadtime = 86400;
                $current_user_id = 1;
            }
            
            if ($next_job>0) $todo_title = get_the_title($next_job);
            if ($next_job==-1) $todo_title = __( '發行', 'your-text-domain' );
            if ($next_job==-2) $todo_title = __( '廢止', 'your-text-domain' );
        
            // Create a new To-do for next_job
            $new_post = array(
                'post_title'    => $todo_title,
                'post_status'   => 'publish',
                'post_author'   => $current_user_id,
                'post_type'     => 'todo',
            );    
            $new_todo_id = wp_insert_post($new_post);
            if ($prev_report_id) update_post_meta( $new_todo_id, 'prev_report_id', $prev_report_id );
            update_post_meta( $new_todo_id, 'todo_due', time()+$next_leadtime );
        
            if ($next_job>0) {
                update_post_meta( $new_todo_id, 'doc_id', $next_job );
                // if the meta "doc_number" of $next_job from set_todo_dialog_data() is not presented
                $todo_id = isset($args['todo_id']) ? $args['todo_id'] : 0;
                if ($todo_id) {
                    $doc_number = get_post_meta($next_job, 'doc_number', true);
                    if (empty($doc_number)) {
                        $doc_id = get_post_meta($todo_id, 'doc_id', true);
                        update_post_meta( $new_todo_id, 'doc_id', $doc_id );
                    }
                }
            }
            if ($next_job==-1 || $next_job==-2) {
                $this->notice_the_persons_in_site($new_todo_id, $next_job);
                update_post_meta( $new_todo_id, 'submit_user', $current_user_id);
                update_post_meta( $new_todo_id, 'submit_action', $action_id);
                update_post_meta( $new_todo_id, 'submit_time', time());
                //if ($report_id) update_post_meta( $report_id, 'todo_status', $next_job);
                if ($prev_report_id) update_post_meta( $prev_report_id, 'todo_status', $next_job );
                if ($prev_report_id) $doc_id = get_post_meta( $prev_report_id, 'doc_id', true );
                if ($doc_id) update_post_meta( $doc_id, 'todo_status', $next_job );
            }
        
            if ($next_job>0) {
                $this->notice_the_responsible_persons($new_todo_id);
                // Create the Action list for next_job
                $profiles_class = new display_profiles();
                $query = $profiles_class->retrieve_doc_action_list_data($next_job);
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

        // Notice the persons in charge the job
        function notice_the_responsible_persons($todo_id=0) {
            $todo_title = get_the_title($todo_id);
            $doc_id = get_post_meta($todo_id, 'doc_id', true);
            $report_id = get_post_meta($todo_id, 'report_id', true);
            if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            if ($is_doc_report) $doc_title .= '(電子表單)';
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
        }

        // Notice the persons in site
        function notice_the_persons_in_site($todo_id=0,$next_job=0) {
            $doc_id = get_post_meta($todo_id, 'doc_id', true);
            $report_id = get_post_meta($todo_id, 'report_id', true);
            if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
            $site_id = get_post_meta($doc_id, 'site_id', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            if ($is_doc_report) $doc_title .= '(電子表單)';
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
            $current_user = get_userdata( $current_user_id );
            $signature_record_list = $this->get_signature_record_list();
            $html_contain = $signature_record_list['html'];
            $x_value = $signature_record_list['x'];
            ?>
            <div class="ui-widget" id="result-container">
            <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
            <h2 style="display:inline;"><?php echo __( '簽核記錄', 'your-text-domain' );?></h2>
                <div id="todo-setting-div" style="display:none">
                <fieldset>
                    <label for="display-name">Name : </label>
                    <input type="text" id="display-name" value="<?php echo $current_user->display_name;?>" class="text ui-widget-content ui-corner-all" disabled />
                    <label for="site-title"> Site: </label>
                    <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
                    <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
                </fieldset>
                </div>
            
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $this->display_select_todo(2);?></div>
                    <div style="text-align: right">
                        <input type="text" id="search-todo" style="display:inline" placeholder="Search..." />
                        <span id="todo-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
                    </div>
                </div>
                <?php echo $html_contain;?>
                <p style="background-color:lightblue;">Total Submissions: <?php echo $x_value;?></p>
            </div>
            <?php
        }
        
        function get_signature_record_list($doc=false, $report=false ) {
            $current_user_id = get_current_user_id();
            $current_site = get_user_meta($current_user_id, 'site_id', true);
            ob_start();
            ?>
            <fieldset>
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
                    // Define the custom pagination parameters
                    $posts_per_page = get_option('operation_row_counts');
                    $current_page = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_signature_record_data($doc, $report, $current_page);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $doc_id = get_post_meta(get_the_ID(), 'doc_id', true);
                            $report_id = get_post_meta(get_the_ID(), 'report_id', true);
                            if ($report_id) $doc_id = get_post_meta($report_id, 'doc_id', true);
                            $site_id = get_post_meta($doc_id, 'site_id', true);
                            $doc_title = get_post_meta($doc_id, 'doc_title', true);
                            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                            if ($is_doc_report) $doc_title .= '(電子表單)';
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
                    if ($current_page > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($current_page - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $current_page, $total_pages) . '</span>';
                    if ($current_page < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($current_page + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>
            <?php
            //$html = ob_get_clean();
            // Return an array containing both HTML content and $x
            return array(
                'html' => ob_get_clean(),
                'x'    => $total_posts,                
            );
        }
        
        function retrieve_signature_record_data($doc_id=false, $report_id=false, $current_page=1){
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');
            $args = array(
                'post_type'      => 'todo',
                'posts_per_page' => $posts_per_page,
                'paged'          => $current_page,
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
                'orderby'        => 'meta_value',
                'meta_key'       => 'submit_time',
                'order'          => 'DESC',
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
                $this->remove_iso_helper_scheduled_events($prev_hook_name);
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
                    $next_occurrence = strtotime('+2 months', $start_time);
                    wp_schedule_single_event($next_occurrence, $hook_name, array($args));
                    break;
                case 'half-yearly':
                    // Calculate timestamp for next occurrence (every 6 months)
                    $next_occurrence = strtotime('+6 months', $start_time);
                    wp_schedule_single_event($next_occurrence, $hook_name, array($args));
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
            update_option('iso_helper_post_event_hook_name', $hook_name);
        
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
            $schedules['yearly'] = array(
                'interval' => 365 * DAY_IN_SECONDS, // Approximate yearly interval
                'display'  => __('Yearly'),
            );
            return $schedules;
        }

        // Method for the callback function
        public function iso_helper_post_event_callback($params) {
            // Add your code to programmatically add a post here
            $this->set_next_todo_and_actions($params);
        }
        
        // Method to schedule the event and add the action
        public function schedule_event_and_action() {
            // Retrieve the hook name from options
            $hook_name = get_option('iso_helper_post_event_hook_name');
            // Add the action with the dynamic hook name
            add_action($hook_name, array($this, 'iso_helper_post_event_callback'));
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

    }
    $todo_class = new to_do_list();
    // Call the method to schedule the event and add the action
    $todo_class->schedule_event_and_action();

}


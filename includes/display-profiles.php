<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('display_profiles')) {
    class display_profiles {
        // Class constructor
        public function __construct() {
            add_shortcode( 'display-profiles', array( $this, 'display_profiles' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_display_profile_scripts' ) );
            //add_action( 'init', array( $this, 'register_site_profile_post_type' ) );

            add_action( 'wp_ajax_set_my_profile_data', array( $this, 'set_my_profile_data' ) );
            add_action( 'wp_ajax_nopriv_set_my_profile_data', array( $this, 'set_my_profile_data' ) );

            add_action( 'wp_ajax_get_my_job_list_data', array( $this, 'get_my_job_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_my_job_list_data', array( $this, 'get_my_job_list_data' ) );
            add_action( 'wp_ajax_get_my_job_dialog_data', array( $this, 'get_my_job_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_my_job_dialog_data', array( $this, 'get_my_job_dialog_data' ) );
            add_action( 'wp_ajax_set_my_job_dialog_data', array( $this, 'set_my_job_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_my_job_dialog_data', array( $this, 'set_my_job_dialog_data' ) );

            add_action( 'wp_ajax_get_site_profile_data', array( $this, 'get_site_profile_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_profile_data', array( $this, 'get_site_profile_data' ) );
            add_action( 'wp_ajax_set_site_profile_data', array( $this, 'set_site_profile_data' ) );
            add_action( 'wp_ajax_nopriv_set_site_profile_data', array( $this, 'set_site_profile_data' ) );

            add_action( 'wp_ajax_set_site_user_action_data', array( $this, 'set_site_user_action_data' ) );
            add_action( 'wp_ajax_nopriv_set_site_user_action_data', array( $this, 'set_site_user_action_data' ) );

            add_action( 'wp_ajax_get_site_NDA_content', array( $this, 'get_site_NDA_content' ) );
            add_action( 'wp_ajax_nopriv_get_site_NDA_content', array( $this, 'get_site_NDA_content' ) );
            add_action( 'wp_ajax_set_NDA_assignment', array( $this, 'set_NDA_assignment' ) );
            add_action( 'wp_ajax_nopriv_set_NDA_assignment', array( $this, 'set_NDA_assignment' ) );

            add_action( 'wp_ajax_get_site_user_dialog_data', array( $this, 'get_site_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_user_dialog_data', array( $this, 'get_site_user_dialog_data' ) );
            add_action( 'wp_ajax_set_site_user_dialog_data', array( $this, 'set_site_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_site_user_dialog_data', array( $this, 'set_site_user_dialog_data' ) );
            add_action( 'wp_ajax_del_site_user_dialog_data', array( $this, 'del_site_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_site_user_dialog_data', array( $this, 'del_site_user_dialog_data' ) );

            add_action( 'wp_ajax_get_site_action_dialog_data', array( $this, 'get_site_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_action_dialog_data', array( $this, 'get_site_action_dialog_data' ) );
            add_action( 'wp_ajax_set_site_action_dialog_data', array( $this, 'set_site_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_site_action_dialog_data', array( $this, 'set_site_action_dialog_data' ) );
            add_action( 'wp_ajax_del_site_action_dialog_data', array( $this, 'del_site_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_site_action_dialog_data', array( $this, 'del_site_action_dialog_data' ) );

            add_action( 'wp_ajax_get_new_action_user', array( $this, 'get_new_action_user' ) );
            add_action( 'wp_ajax_nopriv_get_new_action_user', array( $this, 'get_new_action_user' ) );                                                                    
            add_action( 'wp_ajax_set_action_user_data', array( $this, 'set_action_user_data' ) );
            add_action( 'wp_ajax_nopriv_set_action_user_data', array( $this, 'set_action_user_data' ) );                                                                    
            add_action( 'wp_ajax_del_action_user_data', array( $this, 'del_action_user_data' ) );
            add_action( 'wp_ajax_nopriv_del_action_user_data', array( $this, 'del_action_user_data' ) );                                                                    

            add_action( 'wp_ajax_get_site_list_data', array( $this, 'get_site_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_list_data', array( $this, 'get_site_list_data' ) );
            add_action( 'wp_ajax_get_site_dialog_data', array( $this, 'get_site_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_dialog_data', array( $this, 'get_site_dialog_data' ) );

        }

        function enqueue_display_profile_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);        

            wp_enqueue_script('display-profiles', plugins_url('js/display-profiles.js', __FILE__), array('jquery'), time());
            wp_localize_script('display-profiles', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('display-profiles-nonce'), // Generate nonce
            ));                
        }        

        // Select profile
        function display_select_profile($select_option=false) {
            ?>
            <select id="select-profile">
                <option value="my-profile" <?php echo ($select_option=="my-profile") ? 'selected' : ''?>><?php echo __( 'My Account', 'textdomain' );?></option>
                <option value="site-profile" <?php echo ($select_option=="site-profile") ? 'selected' : ''?>><?php echo __( 'Site Configuration', 'textdomain' );?></option>
                <option value="doc-category" <?php echo ($select_option=="doc-category") ? 'selected' : ''?>><?php echo __( 'Categories', 'textdomain' );?></option>
                <?php if (current_user_can('administrator')) {?>                
                    <option value="user-list" <?php echo ($select_option=="user-list") ? 'selected' : ''?>><?php echo __( 'User List', 'textdomain' );?></option>
                    <option value="site-list" <?php echo ($select_option=="site-list") ? 'selected' : ''?>><?php echo __( 'Site List', 'textdomain' );?></option>
                <?php }?>
            </select>
            <?php
        }

        // Shortcode to display
        function display_profiles() {
            $documents_class = new display_documents();
            // Check if the user is logged in
            if (!is_user_logged_in()) user_is_not_logged_in();                
            elseif (is_site_not_configured()) get_NDA_assignment();
            elseif (isset($_GET['_nda_user_id'])) echo $this->approve_NDA_assignment($_GET['_nda_user_id']);
            elseif (isset($_GET['_report_id'])) echo $documents_class->display_doc_report_dialog($_GET['_report_id']);
            elseif (isset($_GET['_select_profile'])) {
                echo '<div class="ui-widget" id="result-container">';
                if ($_GET['_select_profile']=='my-profile') echo $this->display_my_profile();
                if ($_GET['_select_profile']=='site-profile') echo $this->display_site_profile();
                if ($_GET['_select_profile']=='site-job') echo $this->display_site_job_list();
                if ($_GET['_select_profile']=='user-list') echo $this->display_site_user_list(-1);
                if ($_GET['_select_profile']=='site-list') echo $this->display_site_list();
                $items_class = new embedded_items();
                if ($_GET['_select_profile']=='doc-category') echo $items_class->display_doc_category_list();
                if ($_GET['_select_profile']=='iso-standard') echo $items_class->display_iso_standard_list();
                if ($_GET['_select_profile']=='department-card') echo $items_class->display_department_card_list();
                echo '</div>';

                if ($_GET['_select_profile']=='rename_iso_category_to_iso_standard') echo $this->rename_iso_category_to_iso_standard();

            } else {
                echo '<div class="ui-widget" id="result-container">';
                if (!isset($_GET['_select_profile'])) echo $this->display_my_profile();
                echo '</div>';
            }
        }

        function rename_iso_category_to_iso_standard() {
            global $wpdb;
            $wpdb->query("UPDATE $wpdb->posts SET post_type = 'iso-standard' WHERE post_type = 'iso-category'");
        }

        // my-profile
        function display_my_profile() {
            ob_start();
            $current_user_id = get_current_user_id();
            $current_user = get_userdata( $current_user_id );
            $phone_number = get_user_meta($current_user_id, 'phone_number', true);
            $gemini_api_key = get_user_meta($current_user_id, 'gemini_api_key', true);
            ?>
            <div class="ui-widget" id="result-container">'
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( 'My Account', 'textdomain' );?></h2>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $this->display_select_profile('my-profile');?></div>
                </div>    
                <fieldset>
                    <label for="display-name"><?php echo __( 'Name', 'textdomain' );?></label>
                    <input type="text" id="display-name" value="<?php echo $current_user->display_name;?>" class="text ui-widget-content ui-corner-all" />
                    <label for="user-email"><?php echo __( 'Email', 'textdomain' );?></label>
                    <input type="text" id="user-email" value="<?php echo $current_user->user_email;?>" class="text ui-widget-content ui-corner-all" />
                    <label for="my-job-list"><?php echo __( 'Jobs & Authorizations', 'textdomain' );?></label>
                    <div id="my-job-list"><?php echo $this->display_my_job_list();?></div>
                    <label for="gemini-api-key"><?php echo __( 'Gemini API key', 'textdomain' );?></label>
                    <input type="password" id="gemini-api-key" value="<?php echo $gemini_api_key;?>" class="text ui-widget-content ui-corner-all" />
                </fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <input type="button" id="my-transaction-button" value="<?php echo __( 'Transactions', 'textdomain' );?>" style="margin:3px;" />
                    </div>
                    <div style="text-align: right">
                        <input type="button" id="my-profile-submit" value="<?php echo __( 'Submit', 'textdomain' );?>" style="margin:3px;" />
                    </div>
                </div>
            </div>

            <div id="transaction-data" style="display:none;">
                <?php
                // transaction data vs key/value
                $documents_class = new display_documents();
                $documents_class->display_transaction_report_for_master(['_employee' => get_current_user_id()]);
                ?>
            </div>
            <?php
            return ob_get_clean();
        }

        function set_my_profile_data() {
            $response = array();
            $current_user_id = get_current_user_id();
            wp_update_user(array('ID' => $current_user_id, 'display_name' => $_POST['_display_name']));
            wp_update_user(array('ID' => $current_user_id, 'user_email' => $_POST['_user_email']));
            update_user_meta( $current_user_id, 'gemini_api_key', $_POST['_gemini_api_key']);
            $response = array('success' => true);
            wp_send_json($response);
        }

        // my-jobs
        function display_my_job_list() {
            ob_start();
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            ?>
            <fieldset style="margin-top:5px;">
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'No.', 'textdomain' );?></th>
                        <th><?php echo __( 'Document', 'textdomain' );?></th>
                        <th><?php echo __( 'Authorized Action', 'textdomain' );?></th>
                        <th><?php echo __( 'Recurrence Job', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php    
                    // Accessing elements of the array
                    $user_action_ids = get_user_meta($current_user_id, 'user_action_ids', true);
                    $user_doc_ids = array();
                    if (is_array($user_action_ids)) {
                        foreach ($user_action_ids as $action_id) {
                            $doc_id = get_post_meta($action_id, 'doc_id', true);
                            $doc_site = get_post_meta($doc_id, 'site_id', true);
                            if ($doc_id && !in_array($doc_id, $user_doc_ids) && $doc_site == $site_id) {
                                $user_doc_ids[] = $doc_id;
                            }
                        }
                    }

                    if (is_array($user_doc_ids)) {
                        foreach ($user_doc_ids as $doc_id) {
                            $doc_title = get_the_title($doc_id);
                            $doc_number = get_post_meta($doc_id, 'doc_number', true);
                            $action_titles = array();
                            $query = $this->retrieve_site_action_data(false, $doc_id);
                            if ($query->have_posts()) {
                                while ($query->have_posts()) : $query->the_post();
                                    $action_id = get_the_ID();
                                    $is_action_authorized = $this->is_action_authorized($action_id);
                                    if ($is_action_authorized) {
                                        $action_title = '<span style="color:blue;">'.get_the_title().'</span>';
                                    } else {
                                        $action_title = get_the_title();
                                    }
                                    $action_titles[] = $action_title;
                                endwhile;
                                wp_reset_postdata();                                    
                            }
                            $interval_setting = get_user_meta($current_user_id, 'interval_setting_' . $doc_id, true);
                            ?>
                            <tr id="edit-my-job-<?php echo $doc_id; ?>">
                                <td style="text-align:center;"><?php echo $doc_number;?></td>
                                <td><?php echo $doc_title;?></td>
                                <td style="text-align:center;"><?php echo implode(', ', $action_titles);?></td>
                                <td style="text-align:center;"><?php echo $interval_setting;?></td>
                            </tr>
                            <?php    
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <div id="my-job-dialog" title="Action authorization"></div>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_my_job_dialog_data() {
            if (isset($_POST['_job_id'])) {
                $job_id = sanitize_text_field($_POST['_job_id']);
                $response = array('html_contain' => $this->display_my_job_dialog($job_id));    
            }
            wp_send_json($response);
        }

        function display_my_job_dialog($job_id=false) {
            ob_start();
            $documents_class = new display_documents();
            $documents_class->get_doc_field_contains(array('doc_id' => $job_id));
            $current_user_id = get_current_user_id(); 
            $interval_setting = get_user_meta($current_user_id, 'interval_setting_' . $job_id, true);
            ?>
            <div id="authorization-settings" style="display:none;">
                <hr>
                <label for="is-action-authorized"><?php echo __( 'Action Authorization Settings for Todo list', 'textdomain' );?></label><br>
                <div>
                <?php
                    $query = $this->retrieve_site_action_data(0, $job_id);
                    if ($query->have_posts()) {
                        while ($query->have_posts()) : $query->the_post();
                            $action_id = get_the_ID();
                            $action_title = get_the_title();
                            $is_action_authorized = $this->is_action_authorized($action_id);
                            $is_action_authorized_checked = $is_action_authorized ? 'checked' : '';
                            echo '<input type="radio" name="start-job-dialog-button" id="start-job-dialog-button-'.$action_id.'" value="'.$action_id.'" style="margin:5px;" '.$is_action_authorized_checked.' />';
                            echo '<input type="button" value="'.$action_title.'" style="margin:5px;" />';
                        endwhile;
                        wp_reset_postdata();
                    }
                ?>
                </div>
            </div>
            <div id="recurrence-settings" style="display:none;">
                <input type="hidden" id="job-id" value="<?php echo $job_id;?>" />
                <hr>
                <label for="interval-setting"><?php echo __( 'Default Recurrence Settings for Start job', 'textdomain' );?></label>
                <select id="interval-setting" class="select ui-widget-content ui-corner-all"><?php echo select_cron_schedules_option($interval_setting);?></select>
            </div>
            <?php            
            return ob_get_clean();
        }

        function set_my_job_dialog_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');

            if (isset($_POST['_context']) && $_POST['_mode']=='set' && $_POST['_context']=='authorization') {
                $user_id = get_current_user_id();
                $action_id = sanitize_text_field($_POST['_action_id']);
                $doc_id = get_post_meta($action_id, 'doc_id', true);
                $query = $this->retrieve_site_action_data(0, $doc_id);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $action_authorized_ids = get_post_meta(get_the_ID(), 'action_authorized_ids', true);
                        if (!is_array($action_authorized_ids)) $action_authorized_ids = array();
                        $authorize_exists = in_array($user_id, $action_authorized_ids);
                        if ($action_id == get_the_ID() && !$authorize_exists) {
                            // Add $user_id to 'action_authorized_ids'
                            $action_authorized_ids[] = $user_id;
                        } else {
                            // Remove $user_id from 'action_authorized_ids'
                            $action_authorized_ids = array_diff($action_authorized_ids, array($user_id));
                        }
                        // Update 'action_authorized_ids' meta value
                        update_post_meta(get_the_ID(), 'action_authorized_ids', $action_authorized_ids);
                    endwhile;
                    wp_reset_postdata();
                }
            }

            if (isset($_POST['_context']) && $_POST['_mode']=='unset' && $_POST['_context']=='authorization') {
                $user_id = get_current_user_id();
                $action_id = sanitize_text_field($_POST['_action_id']);
                $action_authorized_ids = get_post_meta($action_id, 'action_authorized_ids', true);
                if (!is_array($action_authorized_ids)) $action_authorized_ids = array();
                $authorize_exists = in_array($user_id, $action_authorized_ids);
                if ($authorize_exists) {
                    // Remove $user_id from 'action_authorized_ids'
                    $action_authorized_ids = array_diff($action_authorized_ids, array($user_id));
                }
                // Update 'action_authorized_ids' meta value
                update_post_meta($action_id, 'action_authorized_ids', $action_authorized_ids);
            }

            if (isset($_POST['_context']) && $_POST['_mode']=='set' && $_POST['_context']=='recurrence') {
                $user_id = get_current_user_id();
                $job_id = sanitize_text_field($_POST['_job_id']);
                $hook_name = 'iso_helper_post_event';
                $interval = sanitize_text_field($_POST['_interval_setting']);
                $args = array(
                    'job_id' => $job_id,
                    'user_id' => $user_id,
                );

                if ($interval) {
                    $start_time = time();
                    // Frequency Report Setting
                    $meta_key = 'interval_setting_' . $job_id;
                    update_user_meta($user_id, $meta_key, $interval);

                    // Prepare iCalendar URL with job ID and interval
                    $ical_url = add_query_arg([
                        'generate_ics' => 1,
                        'job_id' => $job_id,
                        'interval' => $interval, // Pass interval setting to .ics file
                    ], home_url('/generate-icalendar'));

                    // Check if an event with the same hook and args is already scheduled
                    if (!wp_next_scheduled($hook_name, array($args))) {
                        switch ($interval) {
                            case 'hourly':
                                wp_schedule_event($start_time, 'hourly', $hook_name, array($args));
                                break;
                            case 'twicedaily':
                                wp_schedule_event($start_time, 'twicedaily', $hook_name, array($args));
                                break;
                            case 'weekday_daily':
                                $hook_name = 'weekday_daily_post_event';
                                wp_schedule_event($start_time, 'weekday_daily', $hook_name, array($args));
                                break;
                            case 'daily':
                                wp_schedule_event($start_time, 'daily', $hook_name, array($args));
                                break;
                            case 'weekly':
                                wp_schedule_event($start_time, 'weekly', $hook_name, array($args));
                                break;
                            case 'biweekly':
                                wp_schedule_event($start_time, 'biweekly', $hook_name, array($args));
                                break;
                            case 'monthly':
                                wp_schedule_event($start_time, 'monthly', $hook_name, array($args));
                                break;
                            case 'bimonthly':
                                wp_schedule_event($start_time, 'bimonthly', $hook_name, array($args));
                                break;
                            case 'half_yearly':
                                wp_schedule_event($start_time, 'half_yearly', $hook_name, array($args));
                                break;
                            case 'yearly':
                                wp_schedule_event($start_time, 'yearly', $hook_name, array($args));
                                break;
                            default:
                                return new WP_Error('invalid_interval', 'The specified interval is invalid.');
                        }
                    }
                    // Store the hook name in options for later use
                    update_option('schedule_event_hook_name', $hook_name);                

                } else {
                    $meta_key = 'interval_setting_' . $job_id;
                    delete_user_meta($user_id, $meta_key);

                    if ($interval=='weekday_daily') {
                        $hook_name = 'weekday_daily_post_event';
                    }
                    $cron_jobs = _get_cron_array(); // Fetch all cron jobs
                    if ($cron_jobs) {
                        foreach ($cron_jobs as $timestamp => $scheduled_hooks) {
                            if (isset($scheduled_hooks[$hook_name])) {
                                foreach ($scheduled_hooks[$hook_name] as $event) {
                                    // Check if event args match the specified args
                                    if (isset($event['args'][0]) && $event['args'][0] == $args) {
                                        wp_unschedule_event($timestamp, $hook_name, $event['args']);
                                    }
                                }
                            }
                        }
                    }
                }

                $response = array(
                    'success' => true, 
                    'job_id' => $job_id,
                    'ical_url' => $ical_url,
                );
            }

            if (isset($_POST['_context']) && $_POST['_mode']=='unset' && $_POST['_context']=='recurrence') {
                $user_id = get_current_user_id();
                $job_id = sanitize_text_field($_POST['_job_id']);
                $hook_name = 'iso_helper_post_event';
                $interval = sanitize_text_field($_POST['_interval_setting']);
                $args = array(
                    'job_id' => $job_id,
                    'user_id' => $user_id,
                );
                $meta_key = 'interval_setting_' . $job_id;
                delete_user_meta($user_id, $meta_key);

                if ($interval=='weekday_daily') {
                    $hook_name = 'weekday_daily_post_event';
                }
                $cron_jobs = _get_cron_array(); // Fetch all cron jobs
                if ($cron_jobs) {
                    foreach ($cron_jobs as $timestamp => $scheduled_hooks) {
                        if (isset($scheduled_hooks[$hook_name])) {
                            foreach ($scheduled_hooks[$hook_name] as $event) {
                                // Check if event args match the specified args
                                if (isset($event['args'][0]) && $event['args'][0] == $args) {
                                    wp_unschedule_event($timestamp, $hook_name, $event['args']);
                                }
                            }
                        }
                    }
                }
            }

            wp_send_json($response);
        }

        function is_action_authorized($action_id=false, $user_id=false) {
            if (!$action_id) return false;
            if (!$user_id) $user_id = get_current_user_id();
            $action_authorized_ids = get_post_meta($action_id, 'action_authorized_ids', true);
            if (!is_array($action_authorized_ids)) $action_authorized_ids = array();

            if ($user_id) {
                return in_array($user_id, $action_authorized_ids) ? $action_authorized_ids : false;    
            } else {
                return $action_authorized_ids;
            }
        }

        // site-profile
        function register_site_profile_post_type() {
            $labels = array(
                'menu_name'     => _x('Site', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'site-profile', $args );
        }

        function display_site_profile() {
            ob_start();
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $site_content = get_post_field('post_content', $site_id);
            ?>
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( 'Site Configuration', 'textdomain' );?></h2>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $this->display_select_profile('site-profile');?></div>
                <div style="text-align: right">
                </div>
            </div>        

            <fieldset>
                <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
                <label for="site-title"><?php echo __( 'Site Name', 'textdomain' );?></label>
                <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" />
                <div id="site-hint" style="display:none; color:#999;"></div>

                <label for="site-logo"><?php echo __( 'LOGO', 'textdomain' );?></label>
                <div id="site-image-container">
                    <?php echo (isURL($image_url)) ? '<img src="' . esc_attr($image_url) . '" style="object-fit:cover; width:250px; height:250px;" class="button">' : '<a href="#" id="custom-image-href">Set image URL</a>'; ?>
                </div>
                <div id="site-image-url" style="display:none;">
                <fieldset>
                    <label for="image-url"><?php echo __( 'Image URL', 'textdomain' );?></label>
                    <textarea id="image-url" rows="3" style="width:99%;"><?php echo $image_url;?></textarea>
                    <button id="set-image-url" class="button"><?php echo __( 'Set', 'textdomain' );?></button>
                </fieldset>
                </div>

                <?php if (is_site_admin()) {?>
                    <label for="site-members"><?php echo __( 'Site Members', 'textdomain' );?></label>
                    <?php echo $this->display_site_user_list();?>
                <?php }?>

                <label for="site-content"><?php echo __( 'Non-Disclosure Agreement', 'textdomain' );?></label>
                <textarea id="site-content" rows="5" style="width:100%;"><?php echo esc_html($site_content);?></textarea>

                <?php if (is_site_admin()) {?>
                    <div style="display:flex; justify-content:space-between; margin:5px;">
                        <div><label for="site-jobs"><?php echo __( 'Job List', 'textdomain' );?></label></div>
                        <div style="text-align: right">
                            <input type="text" id="site-action-search" style="display:inline" placeholder="<?php echo __( 'Search...', 'textdomain' );?>" />
                        </div>
                    </div>
                    <div id="site-action-list">
                        <?php echo $this->display_site_action_list();?>
                    </div>
                <?php }?>

            </fieldset>
            <?php if (is_site_admin()) {?>
                <button type="submit" id="site-profile-submit" style="margin:3px;"><?php echo __( 'Submit', 'textdomain' );?></button>
            <?php }?>
            <?php
            return ob_get_clean();
        }

        function get_site_profile_data() {
            $response = array('html_contain' => $this->display_site_profile());
            wp_send_json($response);
        }

        function set_site_profile_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');
            if( isset($_POST['_site_id']) ) {
                $site_id = isset($_POST['_site_id']) ? sanitize_text_field($_POST['_site_id']) : 0;
                $site_title = isset($_POST['_site_title']) ? sanitize_text_field($_POST['_site_title']) : '';
                // Update the post
                $post_data = array(
                    'ID'           => $site_id,
                    'post_title'   => $site_title,
                    'post_content' => $_POST['_site_content'],
                );        
                wp_update_post($post_data);
                update_post_meta($site_id, 'image_url', $_POST['_image_url'] );
                $response = array('success' => true);

                if (isset($_POST['_keyValuePairs']) && is_array($_POST['_keyValuePairs'])) {
                    $keyValuePairs = $_POST['_keyValuePairs'];
                    $processedKeyValuePairs = [];
                    foreach ($keyValuePairs as $pair) {
                        foreach ($pair as $field_key => $field_value) {
                            // Sanitize the key and value
                            $field_key = sanitize_text_field($field_key);
                            $field_value = sanitize_text_field($field_value);
                            // Update post meta
                            update_post_meta($site_id, $field_key, $field_value);
                            // Add the sanitized pair to the processed array
                            $processedKeyValuePairs[$field_key] = $field_value;
                        }
                    }
                    // Prepare the response
                    $response = array('success' => true, 'data' => $processedKeyValuePairs);
                } else {
                    // Handle the error case
                    $response = array('success' => false, 'message' => 'No key-value pairs found or invalid format');
                }
            }
            wp_send_json($response);
        }

        // site-users
        function display_site_user_list($paged=1) {
            ob_start();
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            ?>
            <fieldset style="margin-top:5px;">
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Name', 'textdomain' );?></th>
                        <th><?php echo __( 'Email', 'textdomain' );?></th>
                        <th><?php echo __( 'Admin', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php        
                    $users = get_users(); // Initialize with all users

                    if ($paged==1) {
                        $meta_query_args = array(
                            array(
                                'key'     => 'site_id',
                                'value'   => $site_id,
                            ),
                        );
                        $users = get_users(array('meta_query' => $meta_query_args));
                    }
                    // Loop through the users
                    foreach ($users as $user) {
                        $user_site = get_user_meta($user->ID, 'site_id', true);
                        if ($user_site) $display_name = ($user_site == $site_id) ? '<span style="color:blue;">'.$user->display_name.'</span>' : $user->display_name.'('.get_the_title($user_site).')';
                        else $display_name = ($user_site == $site_id) ? $user->display_name : $user->display_name.'<span style="color:red;">***</span>';
                        $is_admin_checked = (is_site_admin($user->ID, $site_id)) ? 'checked' : '';
                        ?>
                        <tr id="edit-site-user-<?php echo $user->ID; ?>">
                            <td style="text-align:center;"><?php echo $display_name; ?></td>
                            <td style="text-align:center;"><?php echo $user->user_email; ?></td>
                            <td style="text-align:center;"><input type="checkbox" <?php echo $is_admin_checked; ?>/></td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </fieldset>
            <div id="site-user-dialog" title="User dialog"></div>
            <?php
            return ob_get_clean();
        }

        function display_site_user_dialog($user_id=false) {
            ob_start();
            $user_data = get_userdata($user_id);
            $user_site = get_user_meta($user_id, 'site_id', true);
            $is_admin_checked = (is_site_admin($user_id)) ? 'checked' : '';
            ?>
            <fieldset>
                <input type="hidden" id="user-id" value="<?php echo $user_id;?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="display-name"><?php echo __( 'Name', 'textdomain' );?></label>
                <input type="text" id="display-name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" />
                <label for="user-email"><?php echo __( 'Email', 'textdomain' );?></label>
                <input type="text" id="user-email" value="<?php echo $user_data->user_email;?>" class="text ui-widget-content ui-corner-all" />
                <?php if (!current_user_can('administrator')) {?>                
                <label for="job-list"><?php echo __( 'Job List', 'textdomain' );?></label>
                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th>#</th>
                            <th><?php echo __( 'Action', 'textdomain' );?></th>
                            <th><?php echo __( 'Document', 'textdomain' );?></th>
                        </thead>
                        <tbody>
                            <?php
                            $query = $this->retrieve_site_action_data(0);
                            if ($query->have_posts()) {
                                while ($query->have_posts()) : $query->the_post();
                                    $action_id = get_the_ID();
                                    $action_title = get_the_title();
                                    $doc_id = get_post_meta($action_id, 'doc_id', true);
                                    $user_action_checked = $this->is_user_action($action_id, $user_id, true) ? 'checked' : '';
                                    echo '<tr id="check-user-action-' . $action_id . '">';
                                    echo '<td style="text-align:center;"><input type="checkbox" id="is-user-action-'.$action_id.'" ' . $user_action_checked . ' /></td>';
                                    echo '<td style="text-align:center;">' . get_the_title() . '</td>';
                                    echo '<td>' . get_the_title($doc_id) . '</td>';
                                    echo '</tr>';
                                endwhile;
                                wp_reset_postdata();                                    
                            }
                            ?>
                        </tbody>
                    </table>
                </fieldset>
                <?php }?>
                <?php
                $current_site_id = get_user_meta($user_id, 'site_id', true);
                if (current_user_can('administrator')) {
                    ?>
                    <label for="select-site"><?php echo __( 'Site', 'textdomain' );?></label>
                    <select id="select-site" class="select ui-widget-content ui-corner-all" ><?php echo $this->select_site_profile_options($current_site_id);?></select>
                    <div>
                    <input type="checkbox" id="is-site-admin-setting" <?php echo $is_admin_checked;?> />
                    <label for="is-site-admin-setting"><?php echo __( 'Is site admin?', 'textdomain' );?></label>
                    </div>
                    <a href="<?php echo home_url('/display-profiles/?_nda_user_id=' . $user_id);?>">NDA approval</a>
                    <?php
                } else {
                    $site_ids = get_user_meta($user_id, 'site_admin_ids', true);
                    if (!empty($site_ids)) {
                        echo '<select id="select-site" class="select ui-widget-content ui-corner-all" >';
                        foreach ($site_ids as $site_id) {
                            $selected = ($site_id == $current_site_id) ? 'selected' : '';
                            echo '<option value="' . esc_attr($site_id) . '" ' . $selected . '>' . esc_html(get_the_title($site_id)) . '</option>';
                        }
                        echo '</select>';
                    }
                    ?>
                    <div>
                    <input type="checkbox" id="is-site-admin-setting" <?php echo $is_admin_checked;?> disabled />
                    <label for="is-site-admin-setting"><?php echo __( 'Is site admin?', 'textdomain' );?></label>
                    </div>
                    <?php
                }
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_site_user_dialog_data() {
            $response = array();
            if (isset($_POST['_user_id'])) {
                $user_id = (int)$_POST['_user_id'];
                $response = array('html_contain' => $this->display_site_user_dialog($user_id));
            }
            wp_send_json($response);
        }

        function set_site_user_dialog_data() {
            $response = array();            
            if (isset($_POST['_user_id'])) {
                $user_id = absint($_POST['_user_id']);
                $current_user = array(
                    'ID'           => $user_id,
                    'display_name' => sanitize_text_field($_POST['_display_name']),
                    'user_email'   => sanitize_email($_POST['_user_email']),
                );        
                // Update user data
                $result = wp_update_user($current_user);

                if (is_wp_error($result)) {
                    $response['error'] = $result->get_error_message();
                } else {
                    // Update user meta
                    $is_site_admin = sanitize_text_field($_POST['_is_site_admin']);
                    update_user_meta($user_id, 'site_id', sanitize_text_field($_POST['_select_site']));
                    $this->set_site_admin_data($user_id, $is_site_admin);
                    $response = array('success' => true);
                }
            }            
            wp_send_json($response);
        }

        function del_site_user_dialog_data() {
            $response = array();
            if (isset($_POST['_user_id'])) {
                $user_id = absint($_POST['_user_id']);
                // Check if the user ID is valid
                if ($user_id > 0) {
                    // Attempt to delete the user
                    $result = wp_delete_user($user_id, true);

                    if (is_wp_error($result)) {
                        // If an error occurs while deleting the user, set the error message in the response
                        $response['error'] = $result->get_error_message();
                    } else {
                        // If the user is successfully deleted, set success to true in the response
                        $response = array('success' => true);
                    }
                } else {
                    // If the provided user ID is invalid, set an error message in the response
                    $response['error'] = 'Invalid user ID provided.';
                }
            } else {
                // If user_id is not provided in the POST request, set an error message in the response
                $response['error'] = 'User ID is missing in the request.';
            }
            wp_send_json($response);
        }

        function select_site_user_options($selected_option=false) {
            if (!$selected_option) $selected_option = get_current_user_id();
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
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
                $selected = ($selected_option == $user->ID) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($user->ID) . '" '.$selected.' >' . esc_html($user->display_name) . '</option>';
            }
            return $options;
        }

        function set_site_admin_data($user_id=false, $is_site_admin=false) {
            if (!$user_id) $user_id = get_current_user_id();
            $site_id = get_user_meta($user_id, 'site_id', true);
            $site_admin_ids = get_user_meta($user_id, 'site_admin_ids', true);
            if (!is_array($site_admin_ids)) $site_admin_ids = array();
            $site_exists = in_array($site_id, $site_admin_ids);

            // Check the condition and update 'site_admin_ids' accordingly
            if ($is_site_admin && !$site_exists) {
                // Add $site_id to 'site_admin_ids'
                $site_admin_ids[] = $site_id;
            } elseif (!$is_site_admin && $site_exists) {
                // Remove $site_id from 'site_admin_ids'
                $site_admin_ids = array_diff($site_admin_ids, array($site_id));
            }        
            // Update 'site_admin_ids' meta value
            update_user_meta( $user_id, 'site_admin_ids', $site_admin_ids);
        }

        function select_site_profile_options($selected_option=0) {
            $args = array(
                'post_type'      => 'site-profile',
                'posts_per_page' => -1,
            );
            $query = new WP_Query($args);
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $site_id = get_the_ID();
                $site_title = get_the_title();
                $selected = ($selected_option == $site_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($site_id) . '" '.$selected.' >' . esc_html($site_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // Site actions
        function display_site_action_list($paged=false, $root_doc_id=false) {
            ob_start();
            ?>
            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'No.', 'textdomain' );?></th>
                        <th><?php echo __( 'Document', 'textdomain' );?></th>
                        <th><?php echo __( 'Action', 'textdomain' );?></th>
                        <th><?php echo __( 'Connector', 'textdomain' );?></th>
                        <th><?php echo __( 'Next', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = empty($paged) ? max(1, get_query_var('paged')) : $paged; // Get the current page number
                    $query = $this->retrieve_site_action_data($paged, $root_doc_id);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages

                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $action_id = get_the_ID();
                            $action_title = get_the_title();
                            $action_content = get_the_content();
                            $action_connector = get_post_meta($action_id, 'action_connector', true);
                            $connector_title = ($action_connector=='embedded') ? __( 'Embedded Items', 'textdomain' ) : get_the_title($action_connector);
                            $next_job = get_post_meta($action_id, 'next_job', true);
                            $doc_id = get_post_meta($action_id, 'doc_id', true);
                            $doc_number = get_post_meta($doc_id, 'doc_number', true);
                            $doc_title = get_the_title($doc_id);
                            ?>
                            <tr id="edit-site-action-<?php echo $action_id;?>">
                                <td style="text-align:center;"><?php echo $doc_number;?></td>
                                <td><?php echo $doc_title;?></td>
                                <td style="text-align:center;"><?php echo '<span style="color:blue;">'.$action_title.'</span>';?></td>
                                <td style="text-align:center;"><?php echo $connector_title;?></td>
                                <td><?php echo get_the_title($next_job);?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-site-action" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
                <?php if ($root_doc_id==false) {?>
                    <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                    </div>
                <?php }?>
            </fieldset>
            <div id="site-action-dialog" title="Action dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_site_action_data($paged=1, $doc_id=false, $is_nest=false) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
        
            $args = array(
                'post_type'      => 'action',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
            );
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1;
            }
        
            if ($doc_id) {
                $args['posts_per_page'] = -1;
                $args['meta_query'][] = array(
                    'key'     => 'doc_id',
                    'value'   => $doc_id,
                );
            }
        
            // 🔹 Full-text search
            $search_query = isset($_GET['_action_search']) ? sanitize_text_field($_GET['_action_search']) : '';
            if (!empty($search_query)) {
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // 🔹 If no results found, search by metadata
            if (!$query->have_posts() && !empty($search_query)) {
                unset($args['s']); // Remove full-text search to avoid conflicts
        
                // 🔹 Search within meta keys of "action"
                $meta_query_all_keys = array('relation' => 'OR');
                $meta_keys = get_post_type_meta_keys('action');
                foreach ($meta_keys as $meta_key) {
                    $meta_query_all_keys[] = array(
                        'key'     => $meta_key,
                        'value'   => $search_query,
                        'compare' => 'LIKE',
                    );
                }
                $args['meta_query'][] = $meta_query_all_keys;
                $query = new WP_Query($args);
        
                // 🔹 If still no results, search for documents and get actions linking to them
                if (!$query->have_posts()) {
                    $document_query = new WP_Query([
                        'post_type'  => 'document',
                        'posts_per_page' => -1,
                        'fields'     => 'ids',
                        's'          => $search_query,
                    ]);
                    $document_ids = $document_query->posts;
                    error_log('Document IDs: ' . print_r($document_ids, true));

                    if (!empty($document_ids)) {
                        // 🔹 REDEFINE META QUERY ONLY FOR DOCUMENT SEARCH
                        $args['meta_query'] = array(
                            'relation' => 'AND',
                            array(
                                'key'   => 'site_id',
                                'value' => $site_id,
                            ),
                            array(
                                'key'     => 'doc_id',
                                'value'   => $document_ids,
                                'compare' => 'IN',
                            ),
                        );
                        error_log('Args: ' . print_r($args, true));
                        $query = new WP_Query($args);
                    }
                }
            }
        
            if ($is_nest) {
                $query = $this->find_more_query_posts($query);
            }
        
            return $query;
        }

        function display_site_action_dialog($action_id=false) {
            ob_start();
            $items_class = new embedded_items();
            $action_title = get_the_title($action_id);
            $action_content = get_post_field('post_content', $action_id);
            $action_connector = get_post_meta($action_id, 'action_connector', true); // doc_category
            $next_job = get_post_meta($action_id, 'next_job', true);
            $doc_id = get_post_meta($action_id, 'doc_id', true);
            $doc_title = get_the_title($doc_id);
            ?>
            <fieldset>
                <input type="hidden" id="action-id" value="<?php echo esc_attr($action_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="action-title"><?php echo __( 'Title', 'textdomain' );?></label><br>
                <input type="text" id="action-title" value="<?php echo esc_attr($action_title);?>" />
                <?php echo $doc_title;?><br>
                <label for="action-content"><?php echo __( 'Content', 'textdomain' );?></label>
                <input type="text" id="action-content" value="<?php echo esc_attr($action_content);?>" class="text ui-widget-content ui-corner-all" />
                <label for="action-connector"><?php echo __( 'Connector', 'textdomain' );?></label>
                <select id="action-connector" class="select ui-widget-content ui-corner-all" >
                    <?php echo $items_class->select_doc_category_options($action_connector, true);?>
                </select>
                <label for="next-job"><?php echo __( 'Action', 'textdomain' );?></label>
                <select id="next-job" class="select ui-widget-content ui-corner-all" ><?php echo $this->select_site_job_options($next_job, $action_connector);?></select>
                <label for="user-list"><?php echo __( 'User List', 'textdomain' );?></label>
                <?php echo $this->display_action_user_list($action_id);?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_site_action_dialog_data() {
            $response = array();
            if (isset($_POST['_action_id'])) {
                $action_id = sanitize_text_field($_POST['_action_id']);
                if (isset($_POST['_action_connector'])) {
                    $action_connector = sanitize_text_field($_POST['_action_connector']);
                    update_post_meta($action_id, 'action_connector', $action_connector);
                    $next_job = isset($_POST['_next_job']) ? sanitize_text_field($_POST['_next_job']) : 0;
                    $response['next_job'] = $this->select_site_job_options($next_job, $action_connector);
                }
                $response['html_contain'] = $this->display_site_action_dialog($action_id);
            }
            wp_send_json($response);
        }

        function set_site_action_dialog_data() {
            $response = array();
            $paged = isset($_POST['_paged']) ? sanitize_text_field($_POST['_paged']) : 1;
            $doc_id = isset($_POST['_doc_id']) ? sanitize_text_field($_POST['_doc_id']) : 0;
            if( isset($_POST['_action_id']) ) {
                $action_id = isset($_POST['_action_id']) ? sanitize_text_field($_POST['_action_id']) : 0;
                $action_title = isset($_POST['_action_title']) ? sanitize_text_field($_POST['_action_title']) : '';
                $action_connector = isset($_POST['_action_connector']) ? sanitize_text_field($_POST['_action_connector']) : 0;
                $next_job = isset($_POST['_next_job']) ? sanitize_text_field($_POST['_next_job']) : 0;
                $data = array(
                    'ID'           => $action_id,
                    'post_title'   => $action_title,
                    'post_content' => $_POST['_action_content'],
                );
                wp_update_post( $data );
                update_post_meta($action_id, 'action_connector', $action_connector);
                update_post_meta($action_id, 'next_job', $next_job);

                // Check if action_number is null
                if ($action_number == null || $action_number === '') {
                    // If null or empty, delete the meta key
                    delete_post_meta($action_id, 'action_number');
                }

            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                // new action
                $new_post = array(
                    'post_type'     => 'action',
                    'post_title'    => __( 'OK', 'textdomain' ),
                    'post_content'  => __( 'Your post content goes here.', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $new_action_id = wp_insert_post($new_post);
                update_post_meta($new_action_id, 'site_id', $site_id);
                update_post_meta($new_action_id, 'doc_id', $doc_id);
            }
            $response['html_contain'] = $this->display_site_action_list($paged, $doc_id);
            wp_send_json($response);
        }

        function del_site_action_dialog_data() {
            $response = array();
            $action_id = isset($_POST['_action_id']) ? sanitize_text_field($_POST['_action_id']) : 0;
            $paged = isset($_POST['_paged']) ? sanitize_text_field($_POST['_paged']) : 1;
            $doc_id = isset($_POST['_doc_id']) ? sanitize_text_field($_POST['_doc_id']) : 0;
            wp_delete_post($action_id, true);
            $response['html_contain'] = $this->display_site_action_list($paged, $doc_id);
            wp_send_json($response);
        }
        
        // action-user
        function display_action_user_list($action_id=false) {
            ob_start();
            ?>
            <div id="action-user-list">
            <fieldset>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( 'Name', 'textdomain' );?></th>
                        <th><?php echo __( 'Email', 'textdomain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $users = $this->retrieve_users_by_action_id($action_id);
                foreach ($users as $user) {
                    ?>
                    <tr id="del-action-user-<?php echo $user->ID;?>">
                        <td style="text-align:center;"><?php echo esc_html($user->display_name);?></td>
                        <td style="text-align:center;"><?php echo esc_html($user->user_email);?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <?php if (is_site_admin()) {?>
                <div id="new-action-user" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            <?php }?>
            </fieldset>
            </div>
            <div id="new-action-users-dialog" title="<?php echo __( 'Add User', 'textdomain' );?>"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_users_by_action_id($action_id) {
            $args = array(
                'meta_query' => array(
                    array(
                        'key'     => 'user_action_ids',
                        'value'   => $action_id,
                        'compare' => 'LIKE'
                    )
                )
            );
            $user_query = new WP_User_Query($args);
            // Get the results
            $users = $user_query->get_results();
            return $users;
        }

        function display_new_action_user_list() {
            ob_start();
            ?>
            <fieldset>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( 'Name', 'textdomain' );?></th>
                        <th><?php echo __( 'Email', 'textdomain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $users = $this->retrieve_users_by_site_id();
                foreach ($users as $user) {
                    ?>
                    <tr id="add-action-user-<?php echo $user->ID;?>">
                        <td style="text-align:center;"><?php echo esc_html($user->display_name);?></td>
                        <td style="text-align:center;"><?php echo esc_html($user->user_email);?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_new_action_user() {
            $response = array();
            $response['html_contain'] = $this->display_new_action_user_list();
            wp_send_json($response);
        }

        function set_action_user_data() {
            $response = array();
            $action_id = sanitize_text_field($_POST['_action_id']);
            $user_id = sanitize_text_field($_POST['_user_id']);

            // Check if user exists
            if (get_userdata($user_id) === false) {
                $response['status'] = 'error';
                $response['message'] = 'Invalid user ID.';
                wp_send_json($response);
            }
        
            // Retrieve current user_action_ids
            $user_action_ids = get_user_meta($user_id, 'user_action_ids', true);
        
            if (empty($user_action_ids)) {
                $user_action_ids = array();
            } elseif (is_string($user_action_ids)) {
                // Handle if user_action_ids is a serialized array or a comma-separated list
                $user_action_ids_array = maybe_unserialize($user_action_ids);
                if (is_array($user_action_ids_array)) {
                    $user_action_ids = $user_action_ids_array;
                } else {
                    $user_action_ids = explode(',', $user_action_ids);
                }
            }
        
            // Add the new action_id if it doesn't already exist
            if (!in_array($action_id, $user_action_ids)) {
                $user_action_ids[] = $action_id;
                update_user_meta($user_id, 'user_action_ids', $user_action_ids);
        
                $response['status'] = 'success';
                $response['message'] = 'ID added successfully.';
            } else {
                $response['status'] = 'info';
                $response['message'] = 'ID already exists for this user.';
            }

            $action_id = sanitize_text_field($_POST['_action_id']);
            $response['html_contain'] = $this->display_action_user_list($action_id);
            wp_send_json($response);
        }

        function del_action_user_data() {
            $response = array();
            $action_id = sanitize_text_field($_POST['_action_id']);
            $user_id = sanitize_text_field($_POST['_user_id']);

            // Check if user exists
            if (get_userdata($user_id) === false) {
                $response['status'] = 'error';
                $response['message'] = 'Invalid user ID.';
                wp_send_json($response);
            }
        
            // Retrieve current user_action_ids
            $user_action_ids = get_user_meta($user_id, 'user_action_ids', true);
        
            if (empty($user_action_ids)) {
                $user_action_ids = array();
            } elseif (is_string($user_action_ids)) {
                // Handle if user_action_ids is a serialized array or a comma-separated list
                $user_action_ids_array = maybe_unserialize($user_action_ids);
                if (is_array($user_action_ids_array)) {
                    $user_action_ids = $user_action_ids_array;
                } else {
                    $user_action_ids = explode(',', $user_action_ids);
                }
            }
        
            // Remove the action_id if it exists
            if (in_array($action_id, $user_action_ids)) {
                $user_action_ids = array_diff($user_action_ids, array($action_id));
                update_user_meta($user_id, 'user_action_ids', $user_action_ids);
        
                $response['status'] = 'success';
                $response['message'] = 'ID deleted successfully.';
            } else {
                $response['status'] = 'info';
                $response['message'] = 'ID does not exist for this user.';
            }

            $action_id = sanitize_text_field($_POST['_action_id']);
            $response['html_contain'] = $this->display_action_user_list($action_id);
            wp_send_json($response);
        }

        function set_site_user_action_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');
            if (isset($_POST['_action_id'])) {
                $action_id = isset($_POST['_action_id']) ? sanitize_text_field($_POST['_action_id']) : 0;
                $user_id = isset($_POST['_user_id']) ? sanitize_text_field($_POST['_user_id']) : get_current_user_id();
                $is_user_action = isset($_POST['_is_user_action']) ? sanitize_text_field($_POST['_is_user_action']) : 0;

                $user_action_ids = get_user_meta($user_id, 'user_action_ids', true);
                if (!is_array($user_action_ids)) $user_action_ids = array();
                $action_exists = in_array($action_id, $user_action_ids);

                // Check the condition and update 'user_action_ids' accordingly
                if ($is_user_action == 1 && !$action_exists) {
                    // Add $action_id to 'user_action_ids'
                    $user_action_ids[] = $action_id;
                } elseif ($is_user_action != 1 && $action_exists) {
                    // Remove $action_id from 'user_action_ids'
                    $user_action_ids = array_diff($user_action_ids, array($action_id));
                }        
                // Update 'user_action_ids' meta value
                update_user_meta( $user_id, 'user_action_ids', $user_action_ids);
                $response = array('success' => true);
            }
            wp_send_json($response);
        }

        function is_user_action($action_id=false, $user_id=false, $user_action_checked=false) {
            // Get the current user ID
            if (!$user_id) $user_id = get_current_user_id();    
            if (is_site_admin($user_id) && !$user_action_checked) return true;
            // Get the user's action IDs as an array
            $user_action_ids = get_user_meta($user_id, 'user_action_ids', true);
            // If $user_action_ids is not an array, convert it to an array
            if (!is_array($user_action_ids)) $user_action_ids = array();
            // Check if the current user has the specified doc ID in their metadata
            return in_array($action_id, $user_action_ids);
        }

        function retrieve_users_by_site_id() {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $args = array(
                'meta_query' => array(
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    )
                )
            );
            $user_query = new WP_User_Query($args);
            // Get the results
            $users = $user_query->get_results();
            return $users;
        }

        function find_more_query_posts($query=false) {
            if (!$query) return false;
        
            // Retrieve the current total posts count
            $current_total_posts = $query->found_posts;
        
            // Retrieve the IDs of the posts from the initial query
            $initial_ids = wp_list_pluck($query->posts, 'ID');
        
            // Retrieve the meta values of "next_job" for the posts from the initial query
            $next_jobs = array();
            foreach ($initial_ids as $post_id) {
                $next_job = get_post_meta($post_id, 'next_job', true);
                if (!empty($next_job)) {
                    $next_jobs[] = $next_job;
                }
            }
        
            // If there are no next jobs, return the original query
            if (empty($next_jobs)) {
                return $query;
            }
        
            // Additional query arguments to find posts with doc_id equal to next_job of initial results
            $additional_args = array(
                'post_type'      => 'action',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'     => 'doc_id',
                        'value'   => $next_jobs,
                        'compare' => 'IN',
                    ),
                ),
            );
        
            // Perform the additional query
            $additional_query = new WP_Query($additional_args);
        
            // Combine the results
            $combined_posts = array_merge($query->posts, $additional_query->posts);
        
            // Create a new WP_Query object with the combined results
            $query = new WP_Query(array(
                'post__in' => wp_list_pluck($combined_posts, 'ID'),
                'post_type' => 'action',
                'posts_per_page' => -1
            ));
        
            // Retrieve the next total posts count
            $next_total_posts = $query->found_posts;
        
            // If new posts are found, perform the recursive call
            if ($next_total_posts > $current_total_posts) {
                return $this->find_more_query_posts($query);
            }
        
            // Return the final query
            return $query;
        }

        function select_site_job_options($selected_option=false, $action_connector=false) {
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => -1,
                'meta_query'     => array(),
            );

            if ($action_connector) {
                if ($action_connector == 'embedded') {
                    $args['meta_query'][] = array(
                        'relation' => 'AND',
                        array(
                            'key'   => 'is_embedded_doc',
                            'value' => 1,
                            'compare' => '='
                        ),
                        array(
                            'relation' => 'OR',
                            // Case 1: in_public exists and is 0
                            array(
                                'relation' => 'AND',
                                array(
                                    'key'   => 'in_public',
                                    'value' => 0,
                                    'compare' => '='
                                ),
                                array(
                                    'key'   => 'site_id',
                                    'value' => $site_id,
                                    'compare' => '='
                                )
                            ),
                            // Case 2: in_public does not exist
                            array(
                                'relation' => 'AND',
                                array(
                                    'key'     => 'in_public',
                                    'compare' => 'NOT EXISTS'
                                ),
                                array(
                                    'key'   => 'site_id',
                                    'value' => $site_id,
                                    'compare' => '='
                                )
                            ),
                            // Case 3: is_public == 1 (should always be included)
                            array(
                                'key'   => 'is_public',
                                'value' => 1,
                                'compare' => '='
                            )
                        )
                    );
                } else {
                    $args['meta_query'][] = array(
                        'key'   => 'doc_category',
                        'value' => $action_connector,
                    );    
                }
            } else {
                $args['meta_query'][] = array(
                    'relation' => 'OR',
                    array(
                        'key'   => 'is_doc_report',
                        'value' => 1,
                    ),
                    array(
                        'key'   => 'is_doc_report',
                        'compare' => 'NOT EXISTS',
                    ),    
                );
            }
            $query = new WP_Query($args);

            while ($query->have_posts()) : $query->the_post();
                $doc_id = get_the_ID();
                $doc_title = get_the_title();
                $doc_number = get_post_meta($doc_id, 'doc_number', true);
                if ($doc_number) $doc_title .= '('.$doc_number.')';

                $selected = ($selected_option == $doc_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($doc_id) . '" '.$selected.' >' . esc_html($doc_title) . '</option>';
            endwhile;
            wp_reset_postdata();

            return $options;
        }

        // site-profile
        function display_site_list() {
            ob_start();
            ?>
            <fieldset style="margin-top:5px;">
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Title', 'textdomain' );?></th>
                        <th><?php echo __( 'Content', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php        
                    $args = array(
                        'post_type'      => 'site-profile',
                        'posts_per_page' => -1,
                    );
                    $query = new WP_Query($args);
                    if ($query->have_posts()) {
                        while ($query->have_posts()) : $query->the_post();
                            $site_id = get_the_ID();
                            $site_title = get_the_title();
                            $site_content = get_the_content();
                            ?>
                            <tr id="edit-site-<?php echo $site_id; ?>">
                                <td style="text-align:center;"><?php echo $site_title; ?></td>
                                <td style="text-align:center;"><?php echo $site_content; ?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    }
                    ?>
                    </tbody>
                </table>
            </fieldset>
            <div id="site-dialog" title="Site dialog"></div>
            <?php
            return ob_get_clean();
        }

        function get_site_list_data() {
            $search_query = sanitize_text_field($_POST['_site_title']);
            $args = array(
                'post_type'      => 'site-profile',
                'posts_per_page' => -1,
                's'              => $search_query,
            );
            $query = new WP_Query($args);
        
            $_array = array();
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $site_id = get_the_ID();
                    $site_title = get_the_title();
                    $_list = array();
                    $_list["site_id"] = $site_id;
                    $_list["site_title"] = $site_title;
                    array_push($_array, $_list);
                endwhile;
                wp_reset_postdata();
            }
            wp_send_json($_array);
        }
        
        function get_site_dialog_data() {
            $response = array();
            if( isset($_POST['_site_id']) ) {
                $site_id = sanitize_text_field($_POST['_site_id']);
                $response["site_title"] = get_the_title($site_id);
            }
            wp_send_json($response);
        }

        function get_site_NDA_content() {
            // Check if the site_id is passed
            if(isset($_POST['site_id'])) {
                $site_id = intval($_POST['site_id']);
        
                // Retrieve the post content
                $post = get_post($site_id);
        
                if($post && $post->post_type == 'site-profile') {
                    wp_send_json_success(array(
                        'content' => apply_filters('the_content', $post->post_content),
                        //'unified_number' => get_post_meta($site_id, 'unified_number', true),
                    ));
                } else {
                    wp_send_json_error(array('message' => 'Invalid site ID or post type.'));
                }
            } else {
                wp_send_json_error(array('message' => 'No site ID provided.'));
            }
        }
        
        function approve_NDA_assignment($user_id=false) {
            if (empty($user_id)) return;
            if (!is_site_admin()) return;
            $site_id = get_user_meta($user_id, 'site_id', true);
            $site_title = get_the_title($site_id);
            $user = get_userdata($user_id);
            $display_name = $user->display_name;
            $nda_content = get_user_meta($user_id, 'nda_content', true);
            $nda_signature = get_user_meta($user_id, 'nda_signature', true);
            $submit_date = get_user_meta($user_id, 'submit_date', true);
            ?>
            <div class="ui-widget" id="result-container">
                <h2 style="display:inline; text-align:center;"><?php echo __( 'Non-Disclosure Agreement', 'textdomain' );?></h2>
                <div>
                    <label for="site-title"><b><?php echo __( 'Party A', 'textdomain' );?></b></label>
                    <input type="text" id="site-title" value="<?php echo $site_title;?>" class="text ui-widget-content ui-corner-all" disabled />
                    <input type="hidden" id="site-id" value="<?php echo $site_id;?>"/>
                </div>
                <div>
                    <label for="display-name"><b><?php echo __( 'Party B', 'textdomain' );?></b></label>
                    <input type="text" id="display-name" value="<?php echo $display_name;?>" class="text ui-widget-content ui-corner-all" disabled />
                    <input type="hidden" id="user-id" value="<?php echo $user_id;?>"/>
                </div>
                <div id="nda-content"><?php echo $nda_content;?></div>
                <div style="display:flex;">
                    <?php echo __( 'Sign-off Date', 'textdomain' );?>
                    <input type="text" id="submit-date" value="<?php echo $submit_date;?>" disabled />
                </div>
                <div>
                    <label for="signature-pad"><?php echo __( 'Review Signature', 'textdomain' );?></label>
                    <div id="signature-pad-div">
                        <div>
                            <canvas id="signature-pad" width="500" height="200" style="border:1px solid #000;"></canvas>
                        </div>
                        <button id="clear-signature" style="margin:3px;"><?php echo __( 'Clear Signature', 'textdomain' );?></button>
                    </div>
                </div>
                <div style="display:flex;">
                    <?php echo __( 'Approval Date', 'textdomain' );?>
                    <input type="date" id="nda-date" value="<?php echo wp_date('Y-m-d', time())?>"/>
                </div>
                <hr>
                <button type="submit" id="nda-approve"><?php echo __( 'Approve', 'textdomain' );?></button>
                <button type="submit" id="nda-reject"><?php echo __( 'Reject', 'textdomain' );?></button>
            </div>
            <?php
        }
        
        function display_NDA_assignment($user_id=false) {
            $user = get_userdata($user_id);
            ?>
            <div class="ui-widget" id="result-container">
                <h2 style="display:inline; text-align:center;"><?php echo __( 'Non-Disclosure Agreement', 'textdomain' );?></h2>
                <div>
                    <label for="select-nda-site"><b><?php echo __( 'Party A', 'textdomain' );?></b></label>
                    <select id="select-nda-site" class="select ui-widget-content ui-corner-all" >
                        <option value=""><?php echo __( 'Select Option', 'textdomain' );?></option>
                        <?php
                            $site_args = array(
                                'post_type'      => 'site-profile',
                                'posts_per_page' => -1,
                            );
                            $sites = get_posts($site_args);
        
                            // Check if "site-profile" posts are empty
                            if (empty($sites)) {
                                // Insert a new "site-profile" post with the title "iso-helper.com"
                                $new_site_id = wp_insert_post(array(
                                    'post_title'  => 'iso-helper.com',
                                    'post_type'   => 'site-profile',
                                    'post_status' => 'publish',
                                ));
                                // Retrieve the updated list of "site-profile" posts
                                $sites = get_posts($site_args);
                            }
        
                            // Display the options in a dropdown
                            foreach ($sites as $site) {
                                echo '<option value="' . esc_attr($site->ID) . '">' . esc_html($site->post_title) . '</option>';
                            }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="display-name"><b><?php echo __( 'Party B', 'textdomain' );?></b></label>
                    <input type="text" id="display-name" value="<?php echo $user->display_name;?>" class="text ui-widget-content ui-corner-all" />
                    <input type="hidden" id="user-id" value="<?php echo $user_id;?>"/>
                </div>
                <div id="site-content">
                    <!-- The site content will be displayed here -->
                </div>
                <div>
                    <label for="signature-pad"><?php echo __( 'Canvas', 'textdomain' );?></label>
                    <div id="signature-pad-div">
                        <div>
                            <canvas id="signature-pad" width="500" height="200" style="border:1px solid #000;"></canvas>
                        </div>
                        <button id="clear-signature" style="margin:3px;"><?php echo __( 'Clear Signature', 'textdomain' );?></button>
                    </div>
                </div>
                <div style="display:flex;">
                    <?php echo __( 'Approval Date', 'textdomain' );?>
                    <input type="date" id="submit-date" value="<?php echo wp_date('Y-m-d', time())?>"/>
                </div>
                <hr>
                <button type="submit" id="nda-submit"><?php echo __( 'Submit', 'textdomain' );?></button>
                <button type="submit" id="nda-exit"><?php echo __( 'Exit', 'textdomain' );?></button>
            </div>
            <?php
        }
        
        function set_NDA_assignment() {
            $response = array();
            $line_bot_api = new line_bot_api();
            if(isset($_POST['_user_id']) && isset($_POST['_site_id']) && isset($_POST['_submit_date'])) {
                $user_id = intval($_POST['_user_id']);
                $user = get_userdata($user_id);
                $site_id = intval($_POST['_site_id']);
                update_user_meta( $user_id, 'site_id', $site_id);
                update_user_meta( $user_id, 'display_name', $_POST['_display_name']);
                update_user_meta( $user_id, 'nda_content', $_POST['_nda_content']);
                update_user_meta( $user_id, 'nda_signature', $_POST['_nda_signature']);
                update_user_meta( $user_id, 'submit_date', $_POST['_submit_date']);
        
                $site_admin_ids = get_site_admin_ids_for_site($site_id);
                if (empty($site_admin_ids)) {
                    $activated_site_users = get_post_meta($site_id, 'activated_site_users', true);
                    if (!is_array($activated_site_users)) $activated_site_users = array();
                    $activated_site_users[] = $user_id;
                    update_user_meta( $user_id, 'approve_id', 1);
                    update_user_meta( $user_id, 'approve_date', $_POST['_approve_date']);
                    update_post_meta( $site_id, 'activated_site_users', $activated_site_users);

                    $site_admin_ids = get_user_meta($user_id, 'site_admin_ids', true);
                    if (empty($site_admin_ids)) {
                        $site_admin_ids = array($site_id);
                    } else {
                        if (!in_array($site_id, $site_admin_ids)) {
                            $site_admin_ids[] = $site_id;
                        }
                    }                    
                    update_user_meta($user_id, 'site_admin_ids', $site_admin_ids);

                } else {
                    foreach ($site_admin_ids as $site_admin_id) {
                        $line_user_id = get_user_meta($site_admin_id, 'line_user_id', true);
                        $line_bot_api->send_flex_message([
                            'to' => $line_user_id,
                            'header_contents' => [['type' => 'text', 'text' => __( 'Notification', 'textdomain' ), 'weight' => 'bold']],
                            'body_contents' => [
                                [
                                    'type' => 'text',
                                    'text' => sprintf(
                                        __('%s has signed the NDA of %s.', 'textdomain'),
                                        $user->display_name,
                                        get_the_title($site_id)
                                    ),
                                    'wrap' => true
                                ]
                            ],                        
                            'footer_contents' => [
                                [
                                    'type' => 'button', 
                                    'action' => [
                                        'type' => 'uri', 
                                        'label' =>  __( 'View Details', 'textdomain' ), 
                                        'uri' => esc_url_raw(home_url("/display-profiles/?_nda_user_id=$user_id"))
                                    ], 
                                    'style' => 'primary'
                                ]
                            ],
                        ]);
                    }    
                }

                // Retrieve all administrators
                $admins = get_users(['role' => 'administrator']);
                $admin_emails = [];
                
                foreach ($admins as $admin) {
                    $admin_emails[] = $admin->user_email;
                }
                
                if (!empty($admin_emails)) {
                    $subject = __('NDA Signed Notification', 'textdomain');
                    $message = sprintf(
                        __("Hello Admin,\n\n%s has signed the NDA of %s.\n\nYou can view the details here:\n%s", 'textdomain'),
                        $user->display_name,
                        get_the_title($site_id),
                        home_url("/display-profiles/?_nda_user_id=$user_id")
                    );
                
                    $headers = ['Content-Type: text/plain; charset=UTF-8'];
                
                    // Send email to all administrators
                    wp_mail($admin_emails, $subject, $message, $headers);
                }

                $response = array('nda'=>'submitted');
            }

            if(isset($_POST['_user_id']) && isset($_POST['_site_id']) && isset($_POST['_approve_date'])) {
                $user_id = intval($_POST['_user_id']);
                $user = get_userdata($user_id);
                $site_id = intval($_POST['_site_id']);
                $activated_site_users = get_post_meta($site_id, 'activated_site_users', true);
                if (!is_array($activated_site_users)) $activated_site_users = array();
                $activated_site_users[] = $user_id;
                update_user_meta( $user_id, 'approve_id', get_current_user_id());
                update_user_meta( $user_id, 'approve_date', $_POST['_approve_date']);
                update_post_meta( $site_id, 'activated_site_users', $activated_site_users);

                $line_user_id = get_user_meta($user_id, 'line_user_id', true);
                $line_bot_api->send_flex_message([
                    'to' => $line_user_id,
                    'header_contents' => [['type' => 'text', 'text' => __( 'Notification', 'textdomain' ), 'weight' => 'bold']],
                    'body_contents' => [
                        [
                            'type' => 'text',
                            'text' => sprintf(
                                __('The NDA between %s and %s has been approved by %s.', 'textdomain') . __('Go to your profile.', 'textdomain'),
                                $user->display_name,
                                get_the_title($site_id),
                                wp_get_current_user()->display_name
                            ),
                            'wrap' => true
                        ]
                    ],                    
                    'footer_contents' => [
                        [
                            'type' => 'button', 
                            'action' => [
                                'type' => 'uri', 
                                'label' => __( 'View Details', 'textdomain' ), 
                                'uri' => esc_url_raw(home_url("/display-profiles/?_select_profile=my-profile"))
                            ], 
                            'style' => 'primary'
                        ]
                    ],
                ]);

                $params = array(
                    'log_message' => sprintf(
                        __('The NDA between %s and %s has been approved by %s.', 'textdomain'),
                        $user->display_name,
                        get_the_title($site_id),
                        wp_get_current_user()->display_name
                    ),                    
                    'action_title' => 'Approve',
                    'user_id' => $user_id,
                );
                $todo_class = new to_do_list();
                $todo_class->set_transaction_log($params);    

                $response = array('nda'=>'approved', 'user_id'=>$user_id, 'activated_site_users'=>$activated_site_users);
            }

            if(isset($_POST['_user_id']) && isset($_POST['_site_id']) && isset($_POST['_reject_date'])) {
                $user_id = intval($_POST['_user_id']);
                $user = get_userdata($user_id);
                $site_id = intval($_POST['_site_id']);
                $activated_site_users = get_post_meta($site_id, 'activated_site_users', true);
                if (!is_array($activated_site_users)) $activated_site_users = array();
                $activated_site_users[] = $user_id;
                update_user_meta( $user_id, 'reject_id', get_current_user_id());
                update_user_meta( $user_id, 'reject_date', $_POST['_reject_date']);

                $line_user_id = get_user_meta($user_id, 'line_user_id', true);
                $line_bot_api->send_flex_message([
                    'to' => $line_user_id,
                    'header_contents' => [['type' => 'text', 'text' => __( 'Notification', 'textdomain' ), 'weight' => 'bold']],
                    'body_contents' => [
                        [
                            'type' => 'text',
                            'text' => sprintf(
                                __( 'The NDA between %s and %s has been rejected by %s.', 'textdomain' ) . __( 'Check with the administrator.', 'textdomain' ),
                                $user->display_name,
                                get_the_title($site_id),
                                wp_get_current_user()->display_name
                            ),
                            'wrap' => true,
                        ],
                    ],                    
                    'footer_contents' => [
                        [
                            'type' => 'button', 
                            'action' => [
                                'type' => 'uri', 
                                'label' => __( 'View Details', 'textdomain' ), 
                                'uri' => esc_url_raw(home_url("/display-profiles/?_select_profile=my-profile"))
                            ], 
                            'style' => 'primary'
                        ]
                    ],
                ]);

                $params = array(
                    'log_message' => sprintf(
                        __('The NDA between %s and %s has been rejected by %s.', 'textdomain'),
                        $user->display_name,
                        get_the_title($site_id),
                        wp_get_current_user()->display_name
                    ),     
                    'action_title' => 'Reject',
                    'user_id' => $user_id,
                );
                $todo_class = new to_do_list();
                $todo_class->set_transaction_log($params);    

                $response = array('nda'=>'rejected', 'user_id'=>$user_id, 'activated_site_users'=>$activated_site_users);
            }

            wp_send_json($response);
        }
    }
    $profiles_class = new display_profiles();
}
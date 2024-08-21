<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!class_exists('http_client')) {
    class iot_messages {

        public function __construct() {

            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_http_client_scripts' ) );
            //add_action( 'init', array( $this, 'register_http_client_post_type' ) );
            add_action( 'init', array( $this, 'register_iot_message_meta' ) );
            add_action( 'init', array( $this, 'register_iot_message_post_type' ) );
            //add_action( 'init', array( $this, 'register_exception_notification_post_type' ) );

            //add_action( 'wp_ajax_get_http_client_list_data', array( $this, 'get_http_client_list_data' ) );
            //add_action( 'wp_ajax_nopriv_get_http_client_list_data', array( $this, 'get_http_client_list_data' ) );
            add_action( 'wp_ajax_get_http_client_dialog_data', array( $this, 'get_http_client_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_http_client_dialog_data', array( $this, 'get_http_client_dialog_data' ) );
            add_action( 'wp_ajax_set_http_client_dialog_data', array( $this, 'set_http_client_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_http_client_dialog_data', array( $this, 'set_http_client_dialog_data' ) );
            add_action( 'wp_ajax_del_http_client_dialog_data', array( $this, 'del_http_client_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_http_client_dialog_data', array( $this, 'del_http_client_dialog_data' ) );

            add_action( 'wp_ajax_get_notification_list_data', array( $this, 'get_notification_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_notification_list_data', array( $this, 'get_notification_list_data' ) );
            add_action( 'wp_ajax_get_notification_dialog_data', array( $this, 'get_notification_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_notification_dialog_data', array( $this, 'get_notification_dialog_data' ) );
            add_action( 'wp_ajax_set_notification_dialog_data', array( $this, 'set_notification_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_notification_dialog_data', array( $this, 'set_notification_dialog_data' ) );
            add_action( 'wp_ajax_del_notification_dialog_data', array( $this, 'del_notification_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_notification_dialog_data', array( $this, 'del_notification_dialog_data' ) );

            //add_action( 'wp_ajax_set_geolocation_message_data', array( $this, 'set_geolocation_message_data' ) );
            //add_action( 'wp_ajax_nopriv_set_geolocation_message_data', array( $this, 'set_geolocation_message_data' ) );
            //add_action( 'wp_ajax_get_geolocation_message_data', array( $this, 'get_geolocation_message_data' ) );
            //add_action( 'wp_ajax_nopriv_get_geolocation_message_data', array( $this, 'get_geolocation_message_data' ) );

            add_filter('cron_schedules', array( $this, 'custom_cron_schedules'));
            if (!wp_next_scheduled('five_minutes_action_process_event')) {
                wp_schedule_event(time(), 'every_five_minutes', 'five_minutes_action_process_event');
            }
            add_action('five_minutes_action_process_event', array( $this, 'update_iot_message_meta'));
            register_deactivation_hook(__FILE__, array( $this, 'custom_cron_deactivation'));
            
        }

        function enqueue_http_client_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
            wp_enqueue_script('leaflet-script', "https://unpkg.com/leaflet/dist/leaflet.js");
            wp_enqueue_style('leaflet-style', "https://unpkg.com/leaflet@1.7.1/dist/leaflet.css");

            wp_enqueue_script('iot-messages', plugins_url('iot-messages.js', __FILE__), array('jquery'), time());
            wp_localize_script('iot-messages', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('iot-messages-nonce'), // Generate nonce
            ));                
        }        

        // iot-message operation
        function register_iot_message_post_type() {
            register_post_type('iot-message', array(
                'labels' => array(
                    'name' => 'IoT Messages',
                    'singular_name' => 'IoT Message',
                ),
                'public' => true,
                'show_in_rest' => true,
                'supports' => array('title', 'editor', 'custom-fields'),
                'capability_type' => 'post',
            ));
        }
        
        function register_iot_message_meta() {
            register_post_meta('iot-message', 'deviceID', array(
                'show_in_rest' => true,
                'single' => true,
                'type' => 'string',
            ));
            register_post_meta('iot-message', 'temperature', array(
                'show_in_rest' => true,
                'single' => true,
                'type' => 'number',
            ));
            register_post_meta('iot-message', 'humidity', array(
                'show_in_rest' => true,
                'single' => true,
                'type' => 'number',
            ));
        }
        
        function update_iot_message_meta() {
            // Retrieve all 'iot-message' posts from the last 5 minutes that haven't been processed
            $args = array(
                'post_type' => 'iot-message',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'processed',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
                'date_query' => array(
                    array(
                        'after' => '5 minutes ago',
                        'inclusive' => true,
                    ),
                ),
            );
            $iot_query = new WP_Query($args);
        
            if ($iot_query->have_posts()) {
                while ($iot_query->have_posts()) {
                    $iot_query->the_post();
                    $equipment_code = get_post_meta(get_the_ID(), 'deviceID', true);
                    $temperature = get_post_meta(get_the_ID(), 'temperature', true);
                    $humidity = get_post_meta(get_the_ID(), 'humidity', true);
        
                    // Find 'http-client' post with the same deviceID
                    // Find 'equipment-card' post with the same deviceID
                    $http_args = array(
                        //'post_type' => 'http-client',
                        'post_type' => 'equipment-card',
                        'meta_query' => array(
                            array(
                                //'key' => 'deviceID',
                                'key'     => 'equipment_code',
                                'value'   => $equipment_code,
                                'compare' => '='
                            )
                        )
                    );
                    $http_query = new WP_Query($http_args);
        
                    if ($http_query->have_posts()) {
                        while ($http_query->have_posts()) {
                            $http_query->the_post();
                            $http_post_id = get_the_ID();
        
                            // Update 'temperature' and 'humidity' metadata
                            if ($temperature) {
                                update_post_meta(get_the_ID(), 'temperature', $temperature);
                                $this->create_exception_notification_events(get_the_ID(), 'temperature', $temperature);
                            }
                            if ($humidity) {
                                update_post_meta(get_the_ID(), 'humidity', $humidity);
                                $this->create_exception_notification_events(get_the_ID(), 'humidity', $humidity);
                            }
                        }
                        wp_reset_postdata();
                    }
        
                    // Mark the 'iot-message' post as processed
                    update_post_meta(get_the_ID(), 'processed', 1);
                }
                wp_reset_postdata();
            }
        }

        function create_exception_notification_events($equipment_id=false, $iot_sensor=false, $sensor_value=false) {
            $key_value_pair = array(
                '_equipment'   => $equipment_id,
            );
            foreach ($key_value_pair as $key => $value) {
                $args = array(
                    'post_type'  => 'doc-field',
                    'posts_per_page' => -1, // Retrieve all posts
                    'meta_query' => array(
                        array(
                            'key'   => 'field_type',
                            'value' => $key,
                            'compare' => '='
                        )
                    ),
                    'fields' => 'ids' // Only return post IDs
                );
            
                // Execute the query
                $query = new WP_Query($args);

                $doc_ids = array();
                if ($query->have_posts()) {
                    foreach ($query->posts as $field_id) {
                        $doc_id = get_post_meta($field_id, 'doc_id', true);

                        $doc_title = get_post_meta($doc_id, 'doc_title', true);
                        // Ensure the doc ID is unique
                        if (!isset($doc_ids[$doc_id])) {                                
                            $doc_ids[$doc_id] = $doc_title; // Use doc_id as key to ensure uniqueness
                            $documents_class = new display_documents();
                            $params = array(
                                'doc_id'         => $doc_id,
                                'key_value_pair' => $key_value_pair,
                            );
                            $doc_report = $documents_class->retrieve_doc_report_list_data($params);
                            if ($doc_report->have_posts()) {

                                $employee_ids = get_post_meta(get_the_ID(), 'employee_ids', true);
                                foreach ($employee_ids as $employee_id) {
                                    $max_temperature = get_post_meta(get_the_ID(), 'max_temperature', true);
                                    $max_humidity = get_post_meta(get_the_ID(), 'max_humidity', true);
                                    if ($iot_sensor=='temperature' && $sensor_value>$max_temperature) $this->prepare_exception_notification_event(get_the_ID(), $employee_id, $iot_sensor, $sensor_value, $max_temperature);
                                    if ($iot_sensor=='humidity' && $sensor_value>$max_humidity) $this->prepare_exception_notification_event(get_the_ID(), $employee_id, $iot_sensor, $sensor_value, $max_humidity);
    
                                }
            
                            }        
                        }
                    }
                    return $query->posts; // Return the array of post IDs
                }

            }
/*
            $query = $this->retrieve_notification_data($http_client_id);
            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $user_id = get_post_meta(get_the_ID(), 'user_id', true);
                    $max_temperature = (float) get_post_meta(get_the_ID(), 'max_temperature', true);
                    $max_humidity = (float) get_post_meta(get_the_ID(), 'max_humidity', true);
                    if ($key=='temperature' && $value>$max_temperature) $this->prepare_exception_notification_event(get_the_ID(), $user_id, $key, $value, $max_temperature);
                    if ($key=='humidity' && $value>$max_humidity) $this->prepare_exception_notification_event(get_the_ID(), $user_id, $key, $value, $max_humidity);
                endwhile;
                wp_reset_postdata();
            endif;
*/            
        }

        function prepare_exception_notification_event($http_client_id=false, $user_id=false, $key=false, $value=false, $max_value=false) {
            $content = get_post_field('post_content', $http_client_id);
            $deviceID = get_post_meta($http_client_id, 'deviceID', true);
            $equipment_code = get_post_meta($http_client_id, 'equipment_code', true);
            
            // Prepare the notification message
            $five_minutes_ago = time() - (5 * 60);
            $five_minutes_ago_formatted = wp_date(get_option('date_format'), $five_minutes_ago) . ' ' . wp_date(get_option('time_format'), $five_minutes_ago);
        
            if ($key=='temperature') {
                $text_message = '#'.$deviceID.' '.$content.'在'.$five_minutes_ago_formatted.'的溫度是'.$value.'°C，已經超過設定的'.$max_value.'°C了。';
            }
            if ($key=='humidity') {
                $text_message = '#'.$deviceID.' '.$content.'在'.$five_minutes_ago_formatted.'的濕度是'.$value.'%，已經超過設定的'.$max_value.'%了。';
            }
        
            // Check if a notification has been sent today
            $last_notification_time = get_user_meta($user_id, 'last_notification_time_' . $deviceID, true);
            $today = wp_date('Y-m-d');
        
            if ($last_notification_time && wp_date('Y-m-d', $last_notification_time) === $today) {
                // Notification already sent today, do not send again
                return;
            }
        
            // Parameters to pass to the notification function
            $params = [
                'user_id' => $user_id,
                'text_message' => $text_message,
            ];
        
            // Schedule the event to run after 5 minutes (300 seconds)
            wp_schedule_single_event(time() + 300, 'send_delayed_notification', [$params]);
        
            // Update the last notification time
            update_user_meta($user_id, 'last_notification_time_' . $deviceID, time());
        }
        
        function send_delayed_notification_handler($params) {
            $user_id = $params['user_id'];
            $text_message = $params['text_message'];
            
            $user_data = get_userdata($user_id);
            $line_user_id = get_user_meta($user_id, 'line_user_id', true);
        
            // Prepare the flex message
            $flexMessage = set_flex_message([
                'display_name' => $user_data->display_name,
                'link_uri' => home_url() . '/display-profiles/?_id=' . $user_id,
                'text_message' => $text_message,
            ]);
        
            // Send the message via the LINE API
            $line_bot_api = new line_bot_api();
            $line_bot_api->pushMessage([
                'to' => $line_user_id,
                'messages' => [$flexMessage],
            ]);
        }

        function select_user_id_option_data($selected_option = 0) {
            $profiles_class = new display_profiles();
            $users = $profiles_class->retrieve_users_by_site_id();
            $options = '<option value="">Select user</option>';            
            foreach ($users as $user) {
                $selected = ($selected_option == $user->ID) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
            }            
            return $options;
        }

        function custom_cron_schedules($schedules) {
            if (!isset($schedules['every_five_minutes'])) {
                $schedules['every_five_minutes'] = array(
                    'interval' => 300, // 300 seconds = 5 minutes
                    'display' => __('Every Five Minutes')
                );
            }
            return $schedules;
        }

        function custom_cron_deactivation() {
            $timestamp = wp_next_scheduled('five_minutes_action_process_event');
            if ($timestamp) {
                wp_unschedule_event($timestamp, 'five_minutes_action_process_event');
            }
        }        

        // iot message
        function display_iot_message_list() {
            ob_start();
            $profiles_class = new display_profiles();
            $todo_class = new to_do_list();
            $current_user_id = get_current_user_id();
            $current_user = get_userdata($current_user_id);
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $is_site_admin = $profiles_class->is_site_admin();
    
            // Check if the user is administrator
            if ($is_site_admin || current_user_can('administrator')) {
                ?>
                <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( 'IoT Messages', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $todo_class->display_select_todo(5);?></div>
                    <div style="text-align: right"></div>                        
                </div>
        
                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Time', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Device', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Tc', 'your-text-domain' );?></th>
                            <th><?php echo __( 'H', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Latitude', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Longitude', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $paged = max(1, get_query_var('paged')); // Get the current page number
                        $query = $this->retrieve_iot_message_data($paged);
                        $total_posts = $query->found_posts;
                        $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages

                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                // Get post creation time
                                $post_time = get_post_time('Y-m-d H:i:s', false, get_the_ID());
                                $topic = get_the_title();
                                $message = get_the_content();
                                $deviceID = get_post_meta(get_the_ID(), 'deviceID', true);
                                $temperature = get_post_meta(get_the_ID(), 'temperature', true);
                                $humidity = get_post_meta(get_the_ID(), 'humidity', true);
                                $latitude = get_post_meta(get_the_ID(), 'latitude', true);
                                $longitude = get_post_meta(get_the_ID(), 'longitude', true);
                                ?>
                                <tr id="edit-iot-message-<?php the_ID();?>">
                                    <td style="text-align:center;"><?php echo esc_html($post_time);?></td>
                                    <td style="text-align:center;"><?php echo esc_html($deviceID);?></td>
                                    <td style="text-align:center;"><?php echo esc_html($temperature);?></td>
                                    <td style="text-align:center;"><?php echo esc_html($humidity);?><span style="font-size:small">%</span></td>
                                    <td style="text-align:center;"><?php echo esc_html($latitude);?></td>
                                    <td style="text-align:center;"><?php echo esc_html($longitude);?></td>
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
                </div>
                <div id="geolocation-dialog" title="Geolocation map">
                    <input type="hidden" id="latitude" />
                    <input type="hidden" id="longitude" />
                    <div id="map" style="height:400px;"></div>
                    <div id="message" style="margin-top: 20px;"></div>
                </div>
                <?php
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
            return ob_get_clean();
        }
        
        function retrieve_iot_message_data($paged = 1) {
            $args = array(
                'post_type'      => 'iot-message',
                'posts_per_page' => get_option('operation_row_counts'), // Show 20 records per page
                'orderby'        => 'date',
                'order'          => 'DESC',
                'paged'          => $paged,
            );
            $query = new WP_Query($args);
            return $query;
        }

        function get_iot_message_data() {
            $response = array();
            $iot_message_id = sanitize_text_field($_POST['_iot_message_id']);
            $response['latitude'] = get_post_meta($geolocation_message_id, 'latitude', true);
            $response['longitude'] = get_post_meta($geolocation_message_id, 'longitude', true);
            $response['message'] = get_post_field('post_content', $geolocation_message_id);
            wp_send_json($response);
        }

        function set_iot_message_data() {
            $receiver = sanitize_text_field($_POST['receiver']);
            $message = sanitize_text_field($_POST['message']);
            $latitude = sanitize_text_field($_POST['latitude']);
            $longitude = sanitize_text_field($_POST['longitude']);
        
            // Create a new post
            $post_data = array(
                'post_title'    => $receiver, // Using receiver as the title
                'post_content'  => $message,
                'post_status'   => 'publish',
                'post_type'     => 'iot-message',
            );
        
            $new_post_id = wp_insert_post($post_data);
        
            if (!is_wp_error($new_post_id)) {
                // Add custom meta data
                update_post_meta($new_post_id, 'latitude', $latitude);
                update_post_meta($new_post_id, 'longitude', $longitude);
                wp_send_json_success('Post created successfully.');
            } else {
                wp_send_json_error('Failed to create post.');
            }
        }

        // Register http-client post type
        function register_http_client_post_type() {
            $labels = array(
                'menu_name'     => _x('HTTP client', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_rest'  => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'http-client', $args );
        }

        function register_exception_notification_post_type() {
            $labels = array(
                'menu_name'     => _x('Notification', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'notification', $args );
        }

        // HTTP Client --> Integrate into the equipment-card
        function display_http_client_list() {
            ob_start();
            $profiles_class = new display_profiles();
            $todo_class = new to_do_list();
            //$current_user_id = get_current_user_id();
            //$current_user = get_userdata($current_user_id);
            //$site_id = get_user_meta($current_user_id, 'site_id', true);
            //$image_url = get_post_meta($site_id, 'image_url', true);
            $is_site_admin = $profiles_class->is_site_admin();
    
            // Check if the user is administrator
            if ($is_site_admin || current_user_can('administrator')) {
                ?>
                <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '溫濕度異常通知設定', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $profiles_class->display_select_profile("http-client");?></div>
                    <div style="text-align: right"></div>                        
                </div>
        
                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'ID', 'your-text-domain' );?></th>
                            <th><?php echo __( 'description', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Tc', 'your-text-domain' );?></th>
                            <th><?php echo __( 'H', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $query = $this->retrieve_http_client_list();
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $deviceID = get_post_meta(get_the_ID(), 'deviceID', true);
                                $temperature = get_post_meta(get_the_ID(), 'temperature', true);
                                $humidity = get_post_meta(get_the_ID(), 'humidity', true);
                                ?>
                                <tr id="edit-http-client-<?php the_ID();?>">
                                    <td style="text-align:center;"><?php echo esc_html($deviceID);?></td>
                                    <td><?php the_content();?></td>
                                    <td style="text-align:center;"><?php echo esc_html($temperature);?></td>
                                    <td style="text-align:center;"><?php echo esc_html($humidity);?><span style="font-size:small">%</span></td>
                                </tr>
                                <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                        </tbody>
                    </table>
                    <div id="new-http-client" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                    <div id="http-client-dialog" title="HTTP Client dialog"></div>
                </fieldset>
                </div>
                <?php
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
            return ob_get_clean();
        }
        
        function retrieve_http_client_list() {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $args = array(
                'post_type'      => 'http-client',
                'posts_per_page' => -1,        
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
            );
            $query = new WP_Query($args);
            return $query;
        }

        function display_http_client_dialog($http_client_id=false) {
            $deviceID = get_post_meta($http_client_id, 'deviceID', true);
            $description = get_post_field('post_content', $http_client_id);
            $mqtt_topic = get_the_title($http_client_id);
            $ssid = get_post_meta($http_client_id, 'ssid', true);
            $password = get_post_meta($http_client_id, 'password', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="http-client-id" value="<?php echo $http_client_id;?>" />
                <label for="device-id"><?php echo __( 'Device ID:', 'your-text-domain' );?></label>
                <input type="text" id="device-id" value="<?php echo $deviceID;?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="description"><?php echo __( 'Description:', 'your-text-domain' );?></label>
                <textarea id="description" rows="3" style="width:100%;"><?php echo $description;?></textarea>
                <label><?php echo __( 'Exception notification:', 'your-text-domain' );?></label>
                <div id="notification-list">
                <?php echo $this->display_notification_list($http_client_id);?>
                </div>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_http_client_dialog_data() {
            $response = array();
            if( isset($_POST['_http_client_id']) ) {
                $http_client_id = sanitize_text_field($_POST['_http_client_id']);
                $response['html_contain'] = $this->display_http_client_dialog($http_client_id);
            }
            wp_send_json($response);
        }

        function set_http_client_dialog_data() {
            if( isset($_POST['_http_client_id']) ) {
                $http_client_id = sanitize_text_field($_POST['_http_client_id']);
                $data = array(
                    'ID'           => $http_client_id,
                    'post_content' => $_POST['_description'],
                );
                wp_update_post( $data );
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => time(),
                    'post_content'  => 'xxx公司，xxx冷凍庫',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'http-client',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'deviceID', time());
                update_post_meta($post_id, 'site_id', $site_id);
            }
            $response = array('html_contain' => $this->display_http_client_list());
            wp_send_json($response);
        }

        function del_http_client_dialog_data() {
            wp_delete_post($_POST['_http_client_id'], true);
            $response = array('html_contain' => $this->display_http_client_list());
            wp_send_json($response);
        }
        
        // Exception notification
        function display_notification_list($http_client_id=false) {
            ob_start();
            ?>
            <fieldset>
                <table class="ui-widget" style="width:100%; font-size:small;">
                    <thead>
                        <th><?php echo __( 'User', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Max. Tc', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Max. H', 'your-text-domain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $query = $this->retrieve_notification_data($http_client_id);
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $user_id = get_post_meta(get_the_ID(), 'user_id', true);
                            $user_data = get_userdata($user_id);
                            $max_temperature = get_post_meta(get_the_ID(), 'max_temperature', true);
                            $max_humidity = get_post_meta(get_the_ID(), 'max_humidity', true).'%';
                            ?>
                            <tr id="edit-notification-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($user_data->display_name);?></td>
                                <td style="text-align:center;"><?php echo esc_html($max_temperature);?></td>
                                <td style="text-align:center;"><?php echo esc_html($max_humidity);?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div id="new-notification" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            </fieldset>
            <div id="notification-dialog" title="Exception notification dialog"></div>
            <div id="new-notification-dialog" title="Add new user">
            <fieldset>
                <label for="new-user-id"><?php echo __( 'Name:', 'your-text-domain' );?></label>
                <select id="new-user-id" class="text ui-widget-content ui-corner-all"><?php echo $this->select_user_id_option_data();?></select>
                <label for="new-max-temperature"><?php echo __( 'Max. Temperature(C):', 'your-text-domain' );?></label>
                <input type="text" id="new-max-temperature" value="25" class="text ui-widget-content ui-corner-all" />
                <label for="new-max-humidity"><?php echo __( 'Max. Humidity(%):', 'your-text-domain' );?></label>
                <input type="text" id="new-max-humidity" value="80" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            </div>

            <?php
            return ob_get_clean();
        }
        
        function backup_data($http_client_id=false) {
            ?>
            <?php
        }
        
        function retrieve_notification_data($http_client_id=false) {
            $args = array(
                'post_type'      => 'notification',
                'posts_per_page' => -1,        
                'meta_query'     => array(
                    array(
                        'key'     => 'http_client_id',
                        'value'   => $http_client_id,
                        'compare' => '=',
                    ),
                ),
            );
            $query = new WP_Query($args);
            return $query;
        }

        function get_notification_list_data() {
            $http_client_id = sanitize_text_field($_POST['_http_client_id']);
            $response = array('html_contain' => $this->display_notification_list($http_client_id));
            wp_send_json($response);
        }

        function display_notification_dialog($notification_id=false) {
            $user_id = get_post_meta($notification_id, 'user_id', true);
            $max_temperature = get_post_meta($notification_id, 'max_temperature', true);
            $max_humidity = get_post_meta($notification_id, 'max_humidity', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="notification-id" value="<?php echo $notification_id;?>" />
                <label for="user-id"><?php echo __( 'Name:', 'your-text-domain' );?></label>
                <select id="user-id" class="text ui-widget-content ui-corner-all"><?php echo $this->select_user_id_option_data($user_id);?></select>
                <label for="max-temperature"><?php echo __( 'Max. Temperature(C):', 'your-text-domain' );?></label>
                <input type="text" id="max-temperature" value="<?php echo $max_temperature;?>" class="text ui-widget-content ui-corner-all" />
                <label for="max-humidity"><?php echo __( 'Max. Humidity(%):', 'your-text-domain' );?></label>
                <input type="text" id="max-humidity" value="<?php echo $max_humidity;?>" class="text ui-widget-content ui-corner-all" />
                </div>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_notification_dialog_data() {
            $response = array();
            $notification_id = sanitize_text_field($_POST['_notification_id']);
            $response['html_contain'] = $this->display_notification_dialog($notification_id);
            wp_send_json($response);
        }

        function set_notification_dialog_data() {
            $response = array();
            if( isset($_POST['_notification_id']) ) {
                $notification_id = sanitize_text_field($_POST['_notification_id']);
                update_post_meta($notification_id, 'user_id', sanitize_text_field($_POST['_user_id']));
                update_post_meta($notification_id, 'max_temperature', sanitize_text_field($_POST['_max_temperature']));
                update_post_meta($notification_id, 'max_humidity', sanitize_text_field($_POST['_max_humidity']));
                $profiles_class = new display_profiles();
                $response['my_notification_list'] = $profiles_class->display_my_notification_list();
            } else {
                $new_post = array(
                    'post_title'    => time(),
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => get_current_user_id(),
                    'post_type'     => 'notification',
                );    
                $notification_id = wp_insert_post($new_post);
                update_post_meta($notification_id, 'http_client_id', sanitize_text_field($_POST['_http_client_id']));
                update_post_meta($notification_id, 'user_id', sanitize_text_field($_POST['_user_id']));
                update_post_meta($notification_id, 'max_temperature', sanitize_text_field($_POST['_max_temperature']));
                update_post_meta($notification_id, 'max_humidity', sanitize_text_field($_POST['_max_humidity']));
            }
            wp_send_json($response);
        }

        function del_notification_dialog_data() {
            $response = array();
            wp_delete_post($_POST['_notification_id'], true);
            $profiles_class = new display_profiles();
            $response['my_notification_list'] = $profiles_class->display_my_notification_list();
            wp_send_json($response);
        }

    }
    $iot_messages = new iot_messages();
}

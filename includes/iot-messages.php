<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!class_exists('iot_messages')) {
    class iot_messages {

        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_iot_message_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this,'add_mermaid_script' ) );
            add_action( 'wp_head', array($this, 'add_mermaid_custom_styles'));
            //add_action( 'init', array( $this, 'register_iot_message_meta' ) );
            //add_action( 'init', array( $this, 'register_iot_message_post_type' ) );

            add_action( 'wp_ajax_get_iot_device_dialog_data', array( $this, 'get_iot_device_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_iot_device_dialog_data', array( $this, 'get_iot_device_dialog_data' ) );
            add_action( 'wp_ajax_set_iot_device_dialog_data', array( $this, 'set_iot_device_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_iot_device_dialog_data', array( $this, 'set_iot_device_dialog_data' ) );
            add_action( 'wp_ajax_del_iot_device_dialog_data', array( $this, 'del_iot_device_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_iot_device_dialog_data', array( $this, 'del_iot_device_dialog_data' ) );

            add_action( 'wp_ajax_get_exception_notification_setting_dialog_data', array( $this, 'get_exception_notification_setting_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_exception_notification_setting_dialog_data', array( $this, 'get_exception_notification_setting_dialog_data' ) );
            add_action( 'wp_ajax_set_exception_notification_setting_dialog_data', array( $this, 'set_exception_notification_setting_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_exception_notification_setting_dialog_data', array( $this, 'set_exception_notification_setting_dialog_data' ) );
            add_action( 'wp_ajax_del_exception_notification_setting_dialog_data', array( $this, 'del_exception_notification_setting_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_exception_notification_setting_dialog_data', array( $this, 'del_exception_notification_setting_dialog_data' ) );
/*
            if (!wp_next_scheduled('five_minutes_action_process_event')) {
                wp_schedule_event(time(), 'every_five_minutes', 'five_minutes_action_process_event');
            }
            add_action('five_minutes_action_process_event', array( $this, 'update_iot_message_meta_data'));
*/            
            //add_action('rest_api_init', array( $this, 'register_iot_endpoint' ));

        }

        function enqueue_iot_message_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
            wp_enqueue_script('leaflet-script', "https://unpkg.com/leaflet/dist/leaflet.js");
            wp_enqueue_style('leaflet-style', "https://unpkg.com/leaflet@1.7.1/dist/leaflet.css");

            wp_enqueue_script('iot-messages', plugins_url('js/iot-messages.js', __FILE__), array('jquery'), time());
            wp_localize_script('iot-messages', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('iot-messages-nonce'), // Generate nonce
            ));                
        }        

        function add_mermaid_script() {
            // Add Mermaid script
            wp_enqueue_script('mermaid', 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.min.js', array(), null, true);
        }
        function add_mermaid_custom_styles() {
            echo '<style>
            .mermaid .node rect {
                fill: #ffcc00; /* Set the fill color */
                stroke: #333333; /* Set the stroke color */
            }
            .mermaid .plot .edgePath path {
                stroke: #ff0000; /* Set the line color */
            }
            </style>';
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

        function get_iot_device_id_by_device_number($device_number) {
            // Define the query arguments
            $args = array(
                'post_type'  => 'iot-device',
                'meta_query' => array(
                    array(
                        'key'   => 'device_number', // Meta key
                        'value' => $device_number,  // Meta value to match
                    ),
                ),
                'posts_per_page' => 1, // Limit to one result
                'fields'         => 'ids', // Retrieve only post IDs
            );
        
            // Execute the query
            $query = new WP_Query($args);
        
            // Return the ID if a matching post is found, otherwise return null
            return !empty($query->posts) ? $query->posts[0] : null;
        }

        function update_iot_message_meta_data() {
            error_log("update_iot_message_meta_data: Start execution");

            // Step 1: Process unprocessed IoT messages
            $unprocessed_args = array(
                'post_type'      => 'iot-message',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'     => 'processed',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
                'date_query'     => array(
                    array(
                        'after'     => '5 minutes ago',
                        'inclusive' => true,
                    ),
                ),
            );
        
            $unprocessed_query = new WP_Query($unprocessed_args);
        
            if ($unprocessed_query->have_posts()) {
                error_log("Found unprocessed IoT messages.");
                while ($unprocessed_query->have_posts()) {
                    $unprocessed_query->the_post();
                    $post_id = get_the_ID();
                    $device_number = get_post_meta($post_id, 'deviceID', true);
                    $temperature = get_post_meta($post_id, 'temperature', true);
                    $humidity = get_post_meta($post_id, 'humidity', true);
        
                    error_log("Processing post ID: $post_id, Device: $device_number, Temp: $temperature, Humidity: $humidity");
        
                    $device_id = $this->get_iot_device_id_by_device_number($device_number);
        
                    if ($device_id) {
                        if ($temperature) {
                            $this->process_exception_notification($device_id, 'temperature', $temperature);
                        }
                        if ($humidity) {
                            $this->process_exception_notification($device_id, 'humidity', $humidity);
                        }
                    } else {
                        error_log("Device ID not found for Device Number: $device_number");
                    }
        
                    // Mark post as processed
                    update_post_meta($post_id, 'processed', 1);
                }
                wp_reset_postdata();
            } else {
                error_log("No unprocessed IoT messages found.");
            }
        
            // Step 2: Retention logic for IoT messages
            $record_intervals = array(
                'daily'       => DAY_IN_SECONDS,
                'twice-daily' => 12 * HOUR_IN_SECONDS,
                'six-hours'   => 6 * HOUR_IN_SECONDS,
                'three-hours' => 3 * HOUR_IN_SECONDS,
                'one-hour'    => HOUR_IN_SECONDS,
            );
        
            // Define intervals in seconds for $records_removed
            $records_removed_intervals = array(
                'one-year'      => 365 * DAY_IN_SECONDS,
                'two-years'     => 2 * 365 * DAY_IN_SECONDS,
                'three-years'   => 3 * 365 * DAY_IN_SECONDS,
                'half-year'     => 6 * 30 * DAY_IN_SECONDS, // Approximation for half a year
                'three-months'  => 3 * 30 * DAY_IN_SECONDS, // Approximation for three months
            );
        
            $device_args = array(
                'post_type'      => 'iot-device',
                'posts_per_page' => -1,
            );
        
            $device_query = new WP_Query($device_args);
        
            if ($device_query->have_posts()) {
                while ($device_query->have_posts()) {
                    $device_query->the_post();
                    $device_id = get_the_ID();
                    $device_number = get_post_meta($device_id, 'device_number', true);
                    $record_frequency = get_post_meta($device_id, 'record_frequency', true);
                    $records_removed = get_post_meta($device_id, 'records_removed', true);

                    if (!isset($record_frequency) || !array_key_exists($record_frequency, $record_intervals)) {
                        $record_frequency = 'daily'; // Default to daily
                    }
        
                    $retention_interval = $record_intervals[$record_frequency];
                    error_log("Deleting the posts of device number: $device_number");
                    error_log("Retention interval is: $retention_interval");
                    error_log("Retention Date: " . gmdate('Y-m-d H:i:s', time() - $retention_interval));

                    // Query for retention deletion
                    $delete_args = array(
                        'post_type'      => 'iot-message',
                        'posts_per_page' => -1,
                        'meta_query'     => array(
                            array(
                                'key'     => 'deviceID',
                                'value'   => $device_number,
                            ),
                        ),
                        'orderby'        => 'date',
                        'order'          => 'DESC',
                        'date_query'     => array(
                            array(
                                'before'    => gmdate('Y-m-d H:i:s', time() - $retention_interval),
                                'inclusive' => true,
                            ),
                        ),
                    );
                    //unset($delete_args['date_query']);
                    $delete_query = new WP_Query($delete_args);
                    error_log("Delete Query Arguments: " . print_r($delete_args, true));
                    error_log("Number of posts found: " . $delete_query->post_count);
                    
                    if ($delete_query->have_posts()) {
                        $last_retained_timestamp = null;
        
                        while ($delete_query->have_posts()) {
                            $delete_query->the_post();
                            $post_id = get_the_ID();
                            $post_date = get_the_date('U');
        
                            if ($last_retained_timestamp === null || $post_date <= $last_retained_timestamp - $retention_interval) {
                                $last_retained_timestamp = $post_date;
                            } else {
                                wp_delete_post($post_id, true); // Force delete the post
                                error_log("Deleted post ID: $post_id");
                            }
                        }
                        wp_reset_postdata();
                    } else {
                        error_log("No posts exceeding retention policy for $record_frequency.");
                    }

                    // Delete posts older than $records_removed
                    if (!isset($records_removed) || !array_key_exists($records_removed, $records_removed_intervals)) {
                        error_log("Invalid records_removed value: $records_removed");
                        $records_removed = 'one-year'; // Default to daily
                    }
        
                    // Calculate the date threshold
                    $date_threshold = gmdate('Y-m-d H:i:s', time() - $records_removed_intervals[$records_removed]);

                    $delete_args = array(
                        'post_type'      => 'iot-message',
                        'posts_per_page' => -1,
                        'meta_query'     => array(
                            array(
                                'key'     => 'deviceID',
                                'value'   => $device_number,
                                //'compare' => '=',
                            ),
                        ),
                        'date_query'     => array(
                            array(
                                'before'    => $date_threshold,
                                'inclusive' => true,
                            ),
                        ),
                    );
                
                    // Debugging logs
                    error_log("Deleting records older than: $date_threshold");

                    $delete_query = new WP_Query($delete_args);
                
                    if ($delete_query->have_posts()) {
                        error_log("update_iot_message_meta_data: Found posts older than 3 days for deletion");
                
                        while ($delete_query->have_posts()) {
                            $delete_query->the_post();
                            $delete_post_id = get_the_ID();
                            wp_delete_post($delete_post_id, true); // Force delete the post
                
                            error_log("Deleted post ID: ".print_r($delete_post_id, true));
                        }
                        wp_reset_postdata();
                    } else {
                        error_log("update_iot_message_meta_data: No posts found for deletion");
                    }
        
                }
                wp_reset_postdata();
            }
        }        

        function process_exception_notification($device_id, $sensor_type, $sensor_value) {
            error_log("process_exception_notification: Device ID: ".print_r($device_id, true).", Sensor Type: ".print_r($sensor_type, true).", Sensor Value: ".print_r($sensor_value, true));
        
            $query = $this->retrieve_exception_notification_setting_data($device_id);
            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $setting_id = get_the_id();
                    $max_value = get_post_meta($setting_id, '_max_value', true);
                    $min_value = get_post_meta($setting_id, '_min_value', true);
                    $employee_id = get_post_meta($setting_id, '_employee_id', true);
                    $is_once_daily = get_post_meta($setting_id, '_is_once_daily', true);
                    $notification_message = $this->build_notification_message($device_id, $sensor_type, $sensor_value, $max_value, $min_value);
                    $this->send_notification_handler($device_id, $employee_id, $notification_message, $is_once_daily);
                    error_log("Notification message: ".print_r($notification_message, true));
                endwhile;
                wp_reset_postdata();
            endif;
        }

        function build_notification_message($device_id, $sensor_type, $sensor_value, $max_value, $min_value) {
            $formatted_time = wp_date(get_option('date_format')) . ' ' . wp_date(get_option('time_format'));
            $device_number = get_post_meta($device_id, 'device_number', true);

            if ($max_value && $sensor_value > $max_value) {
                return sprintf(
                    '#%s %s在%s的%s是%s，已經大於設定的%s。',
                    $device_number,
                    get_the_title($device_id),
                    $formatted_time,
                    $sensor_type,
                    $sensor_value,
                    $max_value
                );
            }
            if ($min_value && $sensor_value < $min_value) {
                return sprintf(
                    '#%s %s在%s的%s是%s，已經小於設定的%s。',
                    $device_number,
                    get_the_title($device_id),
                    $formatted_time,
                    $sensor_type,
                    $sensor_value,
                    $min_value
                );
            }
            return '';
        }

        function send_notification_handler($device_id=false, $user_id=false, $message=false, $is_once_daily=false) {
            $last_notification = get_user_meta($user_id, 'last_notification_time_' . $device_id, true);
            $today = wp_date('Y-m-d');
        
            if ($last_notification && wp_date('Y-m-d', $last_notification) === $today && $is_once_daily) {
                return; // Notification already sent today
            }
        
            $line_user_id = get_user_meta($user_id, 'line_user_id', true);
        
            if ($line_user_id) {
                error_log("Sending notification to Line User ID: " . print_r($line_user_id, true) . ", Message: " . print_r($message, true));
        
                $line_bot_api = new line_bot_api();
                $line_bot_api->send_flex_message([
                    'to' => $line_user_id,
                    'header_contents' => [['type' => 'text', 'text' => 'Notification', 'weight' => 'bold']],
                    'body_contents'   => [['type' => 'text', 'text' => $message, 'wrap' => true]],
                    'footer_contents' => [['type' => 'button', 'action' => ['type' => 'uri', 'label' => 'View Details', 'uri' => home_url("/to-do-list/?_select_todo=iot-devices&_device_id=$device_id")], 'style' => 'primary']],
                ]);
            } else {
                error_log("Line User ID not found for User ID: " . print_r($user_id, true));
            }

            update_user_meta($user_id, 'last_notification_time_' . $device_id, time());

        }

        // iot message
        function display_iot_message_list($device_id=false) {
            ob_start();
            $profiles_class = new display_profiles();
            $todo_class = new to_do_list();
            $current_user_id = get_current_user_id();
            $current_user = get_userdata($current_user_id);
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            ?>
            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Time', 'textdomain' );?></th>
                        <th><?php echo __( 'Sensor', 'textdomain' );?></th>
                        <th><?php echo __( 'Value', 'textdomain' );?></th>
                        <th></th>
                        <th></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $device_number = get_post_meta($device_id, 'device_number', true);
                    $query = $this->retrieve_iot_message_data($paged, $device_number);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages

                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $message_id = get_the_ID();
                            $post_time = get_post_time('Y-m-d H:i:s', false, $message_id);
                            $topic = get_the_title();
                            $message = get_the_content();
                            $device_number = get_post_meta($message_id, 'deviceID', true);
                            $temperature = get_post_meta($message_id, 'temperature', true);
                            $humidity = get_post_meta($message_id, 'humidity', true);
                            $latitude = get_post_meta($message_id, 'latitude', true);
                            $longitude = get_post_meta($message_id, 'longitude', true);
                            ?>
                            <tr id="edit-iot-message-<?php echo $message_id;?>">
                                <td style="text-align:center;"><?php echo esc_html($post_time);?></td>
                                <?php if ($temperature) {?>
                                    <td style="text-align:center;"><?php echo __( 'Temperature', 'textdomain' );?></td>
                                    <td style="text-align:center;"><?php echo esc_html($temperature);?></td>
                                <?php }?>
                                <?php if ($humidity) {?>
                                    <td style="text-align:center;"><?php echo __( 'Humidity(%)', 'textdomain' );?></td>
                                    <td style="text-align:center;"><?php echo esc_html($humidity);?><span style="font-size:small">%</span></td>
                                <?php }?>
                                <?php if ($latitude) {?>
                                    <td style="text-align:center;"><?php echo __( 'Latitude', 'textdomain' );?></td>
                                    <td style="text-align:center;"><?php echo esc_html($latitude);?></td>
                                <?php }?>
                                <?php if ($longitude) {?>
                                    <td style="text-align:center;"><?php echo __( 'Longitude', 'textdomain' );?></td>
                                    <td style="text-align:center;"><?php echo esc_html($longitude);?></td>
                                <?php }?>
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
        
        function retrieve_iot_message_data($paged=1, $device_number=false) {
            $args = array(
                'post_type'      => 'iot-message',
                'posts_per_page' => get_option('operation_row_counts'), // Show 20 records per page
                'orderby'        => 'date',
                'order'          => 'DESC',
                'paged'          => $paged,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'deviceID',
                        'value'   => $device_number,
                    ),
                ),
            );
            if ($paged==0) $args['posts_per_page']=-1;
            $query = new WP_Query($args);
            return $query;
        }

        // iot-device
        function register_iot_device_post_type() {
            $labels = array(
                'menu_name'     => _x('IoT devices', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'iot-device', $args );
        }

        function is_site_with_iot_device() {
            $query = $this->retrieve_iot_device_data(0);
            if ($query->have_posts()) :
                return true;
            endif;
            return false;
        }

        function display_iot_device_list() {
            ob_start();
            $todo_class = new to_do_list();
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( 'IoT devices', 'textdomain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $todo_class->display_select_todo('iot-devices');?></div>
                <div style="text-align:right; display:flex;">
                    <input type="text" id="search-device" style="display:inline" placeholder="<?php echo __( 'Search...', 'textdomain' );?>" />
                </div>
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'No.', 'textdomain' );?></th>
                        <th><?php echo __( 'Title', 'textdomain' );?></th>
                        <th><?php echo __( 'Description', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_iot_device_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $device_id = get_the_ID();
                            $device_title = get_the_title();
                            $device_content = get_the_content();
                            $device_number = get_post_meta($device_id, 'device_number', true);
                            ?>
                            <tr id="edit-iot-device-<?php echo $device_id;?>">
                                <td style="text-align:center;"><?php echo $device_number;?></td>
                                <td><?php echo $device_title;?></td>
                                <td><?php echo $device_content;?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-iot-device" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>
            <div style="background-color:lightblue; text-align:center;">
                <?php 
                echo $this->count_matching_iot_messages();
                echo __( ' total submissions', 'textdomain' );
                echo __( ' before today.', 'textdomain' );
                ?>
            </div>
            </div>
            <div id="iot-device-dialog" title="IoT device dialog"></div>
            <?php
            return ob_get_clean();
        }

        function count_matching_iot_messages() {
            // Initialize the total count
            $total_count = 0;
        
            // Step 1: Get all iot-device posts
            $query = $this->retrieve_iot_device_data(0);
        
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $device_id = get_the_ID();
                    $device_number = get_post_meta($device_id, 'device_number', true);
        
                    if (!empty($device_number)) {
                        // Step 2: Query iot-message posts where device_number matches deviceID
                        $iot_message_args = array(
                            'post_type'      => 'iot-message',
                            'posts_per_page' => -1, // Get all matching posts
                            'meta_query'     => array(
                                array(
                                    'key'   => 'deviceID',
                                    'value' => $device_number,
                                    //'compare' => '=',
                                ),
                            ),
                            'date_query'     => array(
                                array(
                                    'before'    => gmdate('Y-m-d H:i:s', time() - 86400),
                                    'inclusive' => true,
                                ),
                            ),
                            'fields'         => 'ids', // We only need the IDs for counting
                        );
                        $iot_message_query = new WP_Query($iot_message_args);
        
                        // Step 3: Increment the total count by the number of matching posts
                        $total_count += $iot_message_query->found_posts;
        
                        wp_reset_postdata(); // Reset the query
                    }
                }
                wp_reset_postdata(); // Reset the outer loop
            }
        
            // Log or return the total count
            error_log("Total matching iot-message posts: $total_count");
            return $total_count;
        }
        
        function retrieve_iot_device_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'iot-device',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_key'       => 'device_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );

            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            if (!current_user_can('administrator')) {
                $args['meta_query'] = array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
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
                $meta_keys = get_post_type_meta_keys('iot-device');
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

        function get_previous_device_id($current_device_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            // Get the current device's `device_number`
            $current_device_number = get_post_meta($current_device_id, 'device_number', true);
        
            if (!$current_device_number) {
                return null; // Return null if the current device_number is not set
            }
        
            $args = array(
                'post_type'      => 'iot-device',
                'posts_per_page' => 1,
                'meta_key'       => 'device_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value as a string
                'order'          => 'DESC', // Descending order to get the previous device
                'meta_query'     => array(
                    array(
                        'key'     => 'device_number',
                        'value'   => $current_device_number,
                        'compare' => '<', // Find `device_number` less than the current one
                        'type'    => 'CHAR', // Treat `device_number` as a string
                    ),
                ),
            );
        
            if (!current_user_can('administrator')) {
                $args['meta_query'][] = array(
                    'key'   => 'site_id',
                    'value' => $site_id,
                );
            }

            $query = new WP_Query($args);
        
            // Return the previous device ID or null if no previous device is found
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function get_next_device_id($current_device_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            // Get the current device's `device_number`
            $current_device_number = get_post_meta($current_device_id, 'device_number', true);
        
            if (!$current_device_number) {
                return null; // Return null if the current device_number is not set
            }
        
            $args = array(
                'post_type'      => 'iot-device',
                'posts_per_page' => 1,
                'meta_key'       => 'device_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value as a string
                'order'          => 'ASC', // Ascending order to get the next device
                'meta_query'     => array(
                    array(
                        'key'     => 'device_number',
                        'value'   => $current_device_number,
                        'compare' => '>', // Find `device_number` greater than the current one
                        'type'    => 'CHAR', // Treat `device_number` as a string
                    ),
                ),
            );
        
            if (!current_user_can('administrator')) {
                $args['meta_query'][] = array(
                    'key'   => 'site_id',
                    'value' => $site_id,
                );
            }

            $query = new WP_Query($args);
        
            // Return the next device ID or null if no next device is found
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function display_iot_device_dialog($device_id=false) {
            ob_start();
            $profiles_class = new display_profiles();
            $prev_device_id = $this->get_previous_device_id($device_id); // Fetch the previous device ID
            $next_device_id = $this->get_next_device_id($device_id);     // Fetch the next device ID
            $device_number = get_post_meta($device_id, 'device_number', true);
            $device_title = get_the_title($device_id);
            $device_content = get_post_field('post_content', $device_id);
            $site_id = get_post_meta($device_id, 'site_id', true);
            $temperature_offset = get_post_meta($device_id, 'temperature_offset', true);
            $record_frequency = get_post_meta($device_id, 'record_frequency', true);
            $records_removed = get_post_meta($device_id, 'records_removed', true);

            //$device_id = 'sensor_1';
            $sensor_value = get_option("iot_device_{$device_number}_temperature_last_update", 'No Data');
            echo "Latest Temperature Sensor Value: " . esc_html($sensor_value);
            
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( 'IoT devices', 'textdomain' );?></h2>
            <input type="hidden" id="prev-device-id" value="<?php echo esc_attr($prev_device_id); ?>" />
            <input type="hidden" id="next-device-id" value="<?php echo esc_attr($next_device_id); ?>" />
            <input type="hidden" id="device-id" value="<?php echo esc_attr($device_id);?>" />
            <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
            <fieldset>
                <label for="device-number"><?php echo __( 'No.', 'textdomain' );?></label>
                <input type="text" id="device-number" value="<?php echo esc_attr($device_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="device-title"><?php echo __( 'Title', 'textdomain' );?></label>
                <input type="text" id="device-title" value="<?php echo esc_attr($device_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="device-content"><?php echo __( 'Description', 'textdomain' );?></label>
                <textarea id="device-content" rows="3" style="width:100%;"><?php echo esc_html($device_content);?></textarea>
                <?php if (current_user_can('administrator')) {?>
                    <label for="site-id"><?php echo __( 'Site', 'textdomain' );?></label>
                    <select id="site-id" class="text ui-widget-content ui-corner-all" ><?php echo $profiles_class->select_site_profile_options($site_id);?></select>
                <?php }?>
                <label for="temperature-offset"><?php echo __( 'Temperature Offset', 'textdomain' );?></label>
                <input type="text" id="temperature-offset" value="<?php echo esc_attr($temperature_offset);?>" class="text ui-widget-content ui-corner-all" />
                <label for="record-frequency"><?php echo __( 'Record Frequency', 'textdomain' );?></label>
                <select id="record-frequency" class="text ui-widget-content ui-corner-all" >
                    <option value="daily" <?php echo ($record_frequency=='daily') ? 'selected' : '';?>><?php echo __( 'Record once a day', 'textdomain' );?></option>
                    <option value="twice-daily" <?php echo ($record_frequency=='twice-daily') ? 'selected' : '';?>><?php echo __( 'Record once every 12 hours', 'textdomain' );?></option>
                    <option value="six-hours" <?php echo ($record_frequency=='six-hours') ? 'selected' : '';?>><?php echo __( 'Record once every 6 hours', 'textdomain' );?></option>
                    <option value="three-hours" <?php echo ($record_frequency=='three-hours') ? 'selected' : '';?>><?php echo __( 'Record once every 3 hours', 'textdomain' );?></option>
                    <option value="one-hour" <?php echo ($record_frequency=='one-hour') ? 'selected' : '';?>><?php echo __( 'Record once every per hour', 'textdomain' );?></option>
                </select>
                <label for="records-removed"><?php echo __( 'Records Removed', 'textdomain' );?></label>
                <select id="records-removed" class="text ui-widget-content ui-corner-all" >
                    <option value="one-year" <?php echo ($records_removed=='one-year') ? 'selected' : '';?>><?php echo __( 'More than one year', 'textdomain' );?></option>
                    <option value="two-years" <?php echo ($records_removed=='two-years') ? 'selected' : '';?>><?php echo __( 'Two years or more', 'textdomain' );?></option>
                    <option value="three-years" <?php echo ($records_removed=='three-years') ? 'selected' : '';?>><?php echo __( 'Three years or more', 'textdomain' );?></option>
                    <option value="half-year" <?php echo ($records_removed=='half-year') ? 'selected' : '';?>><?php echo __( 'More than six months', 'textdomain' );?></option>
                    <option value="three-months" <?php echo ($records_removed=='three-months') ? 'selected' : '';?>><?php echo __( 'More than three months', 'textdomain' );?></option>
                </select>
                <?php
                $paged = max(1, get_query_var('paged')); // Get the current page number
                $query = $this->retrieve_iot_message_data($paged, $device_number);
                $data_points = []; // Initialize an array to hold valid temperature values
                $x_axis = [];
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $message_id = get_the_ID();
                        $temperature = get_post_meta($message_id, 'temperature', true);
                        $post_time = get_post_time('H.i', false, $message_id);
                        if (is_numeric($temperature)) { // Ensure the temperature is a valid number
                            $x_axis[] = $post_time;
                            $data_points[] = $temperature;
                        }
                    endwhile;
                    wp_reset_postdata();
                endif;
                $max_temperature = !empty($data_points) ? max($data_points)+1 : null;
                $min_temperature = !empty($data_points) ? min($data_points)-1 : null;

                // Output data as a comma-separated list
                if ($data_points!=array() && $x_axis!=array()) {        
                ?>
                <div id="mermaid-div">
                    <pre class="mermaid">
                        xychart-beta
                            title "Temperature"
                            x-axis [<?php echo implode(', ', $x_axis);?>]
                            y-axis "Temperature (in ℃)" <?php echo $min_temperature;?> --> <?php echo $max_temperature;?>
                            line [<?php echo implode(', ', $data_points);?>]
                    </pre>
                </div>                
                <?php }
                ?>
                <label for="iot-message"><?php echo __( 'IoT messages', 'textdomain' );?></label>
                <?php echo $this->display_iot_message_list($device_id);?>
                <?php
                // transaction data vs card key/value
                $key_value_pair = array(
                    '_iot_device'   => $device_id,
                );
                $documents_class = new display_documents();
                $documents_class->get_transactions_by_key_value_pair($key_value_pair);
                
                // exception notification setting
                //$iot_messages = new iot_messages();
                //$is_display = ($iot_messages->is_site_with_iot_device()) ? '' : 'display:none;';
                ?>
                <div>
                    <label id="my-exception-notification-setting-label" class="button"><?php echo __( 'Exception Notification Settings', 'textdomain' );?></label>
                    <div id="my-exception-notification-setting"><?php echo $this->display_exception_notification_setting_list($device_id);?></div>
                </div>

                <hr>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <?php if (current_user_can('administrator')) {?>
                            <input type="button" id="save-iot-device" value="<?php echo __( 'Save', 'textdomain' );?>" style="margin:3px;" />
                            <input type="button" id="del-iot-device" value="<?php echo __( 'Delete', 'textdomain' );?>" style="margin:3px;" />
                        <?php }?>
                    </div>
                    <div style="text-align: right">
                        <input type="button" id="iot-dialog-exit" value="<?php echo __( 'Exit', 'textdomain' );?>" style="margin:5px;" />
                    </div>
                </div>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }

        function get_iot_device_dialog_data() {
            if( isset($_POST['_device_id']) ) {
                $device_id = sanitize_text_field($_POST['_device_id']);
                $response = array('html_contain' => $this->display_iot_device_dialog($device_id));
            }
            wp_send_json($response);
        }

        function set_iot_device_dialog_data() {
            if( isset($_POST['_device_id']) ) {
                $device_id = sanitize_text_field($_POST['_device_id']);
                $device_number = (isset($_POST['_device_number'])) ? sanitize_text_field($_POST['_device_number']) : '';
                $device_title = (isset($_POST['_device_title'])) ? sanitize_text_field($_POST['_device_title']) : '';
                $site_id = (isset($_POST['_site_id'])) ? sanitize_text_field($_POST['_site_id']) : 0;
                $temperature_offset = (isset($_POST['_temperature_offset'])) ? sanitize_text_field($_POST['_temperature_offset']) : 0;
                $record_frequency = (isset($_POST['_record_frequency'])) ? sanitize_text_field($_POST['_record_frequency']) : 'daily';
                $records_removed = (isset($_POST['_records_removed'])) ? sanitize_text_field($_POST['_records_removed']) : 'one-year';
                $data = array(
                    'ID'           => $device_id,
                    'post_title'   => $device_title,
                    'post_content' => $_POST['_device_content'],
                );
                wp_update_post( $data );
                update_post_meta($device_id, 'device_number', $device_number);
                update_post_meta($device_id, 'site_id', $site_id);
                update_post_meta($device_id, 'temperature_offset', $temperature_offset);
                update_post_meta($device_id, 'record_frequency', $record_frequency);
                update_post_meta($device_id, 'records_removed', $records_removed);

                $params = array(
                    'log_message' => sprintf(
                        __( 'IoT device "#%s" has been updated successfully.', 'textdomain' ),
                        $device_number
                    ),                    
                    'device_id' => $device_id,
                );
                $todo_class = new to_do_list();
                $todo_class->set_system_log($params);    

            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_type'     => 'iot-device',
                    'post_title'    => 'New device',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'device_number', time());
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'temperature_offset', 0);
                update_post_meta($post_id, 'record_frequency', 'daily');
                update_post_meta($post_id, 'records_removed', 'one-year');
            }
            $response = array('html_contain' => $this->display_iot_device_list());
            wp_send_json($response);
        }

        function del_iot_device_dialog_data() {
            $device_id = isset($_POST['_device_id']) ? sanitize_text_field($_POST['_device_id']) : 0;
            $device_number = get_post_meta($device_id, 'device_number', true);
            $params = array(
                'log_message' => sprintf(
                    __( 'IoT device "#%s" has been deleted', 'textdomain' ),
                    $device_number
                ),                
                'device_id' => $device_id,
            );
            $todo_class = new to_do_list();
            $todo_class->set_system_log($params);    

            wp_delete_post($device_id, true);
            $response = array('html_contain' => $this->display_iot_device_list());
            wp_send_json($response);
        }

        function select_iot_device_options($selected_option=0) {
            $query = $this->retrieve_iot_device_data(0);
            $options = '<option value="">Select device</option>';
            while ($query->have_posts()) : $query->the_post();
                $device_id = get_the_ID();
                $device_title = get_the_title();
                $selected = ($selected_option == $device_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($device_id) . '" '.$selected.' />' . esc_html($device_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // exception notification setting
        function register_exception_notification_setting_post_type() {
            $labels = array(
                'menu_name'     => _x('notification', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'notification', $args );
        }

        function display_exception_notification_setting_list($device_id) {
            ob_start();
            $current_user_id = get_current_user_id();
            ?>
            <fieldset style="margin-top:5px;">
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Device', 'textdomain' );?></th>
                        <th><?php echo __( 'Max.', 'textdomain' );?></th>
                        <th><?php echo __( 'Min.', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                        $query = $this->retrieve_exception_notification_setting_data($device_id, $current_user_id);
                        if ($query->have_posts()) {
                            while ($query->have_posts()) : $query->the_post();
                                $setting_id = get_the_ID();
                                $device_id = get_post_meta($setting_id, '_device_id', true);
                                echo '<tr id="edit-exception-notification-setting-'.esc_attr($setting_id).'">';
                                echo '<td style="text-align:center;">'.esc_html(get_the_title($device_id)).'</td>';
                                echo '<td style="text-align:center;">'.esc_html(get_post_meta($setting_id, '_max_value', true)).'</td>';
                                echo '<td style="text-align:center;">'.esc_html(get_post_meta($setting_id, '_min_value', true)).'</td>';
                                echo '</tr>';
                            endwhile;
                            wp_reset_postdata();
                        }
                    ?>
                    </tbody>
                </table>
                <div id="new-exception-notification-setting-<?php echo $device_id;?>" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <div id="exception-notification-setting-dialog" title="exception-notification-setting"></div>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function retrieve_exception_notification_setting_data($device_id = false, $employee_id = false) {
            
            //if (!$employee_id) $employee_id = get_current_user_id();
            $args = array(
                'post_type'      => 'notification',
                'posts_per_page' => -1, // Retrieve all posts
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => '_max_value',
                        'compare' => 'EXISTS'
                    )
                ),
            );
            if ($device_id) {
                $args['meta_query'][] = array(
                    'key'     => '_device_id',
                    'value'   => $device_id,
                );
            }
            if ($employee_id) {
                $args['meta_query'][] = array(
                    'key'     => '_employee_id',
                    'value'   => $employee_id,
                );
            }
            $query = new WP_Query($args);
            return $query;
        }

        function display_exception_notification_setting_dialog($setting_id=false) {
            ob_start();
            $device_id = get_post_meta($setting_id, '_device_id', true);
            $max_value = get_post_meta($setting_id, '_max_value', true);
            $min_value = get_post_meta($setting_id, '_min_value', true);
            $is_once_daily = get_post_meta($setting_id, '_is_once_daily', true);
            $is_checked = ($is_once_daily==1) ? 'checked' : '';
            ?>
            <fieldset>
                <input type="hidden" id="setting-id" value="<?php echo esc_attr($setting_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <input type="hidden" id="device-id" value="<?php echo esc_attr($device_id);?>" />
<?php /*                
                <label for="device-id"><?php echo __( 'Device', 'textdomain' );?></label>
                <select id="device-id" class="text ui-widget-content ui-corner-all"><?php echo $this->select_iot_device_options($device_id);?></select>                
*/?>
                <label for="max-value"><?php echo __( 'Max.', 'textdomain' );?></label>
                <input type="text" id="max-value" value="<?php echo esc_attr($max_value);?>" class="text ui-widget-content ui-corner-all" />
                <label for="min-value"><?php echo __( 'Min', 'textdomain' );?></label>
                <input type="text" id="min-value" value="<?php echo esc_attr($min_value);?>" class="text ui-widget-content ui-corner-all" />
                <input type="checkbox" id="is-once-daily" <?php echo $is_checked;?> />
                <label for="is-once-daily"><?php echo __( 'Only reply once per day.', 'textdomain' );?></label>
                </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_exception_notification_setting_dialog_data() {
            $response = array();
            $setting_id = isset($_POST['_setting_id']) ? sanitize_text_field($_POST['_setting_id']) : 0;
            $response['html_contain'] = $this->display_exception_notification_setting_dialog($setting_id);
            wp_send_json($response);
        }

        function set_exception_notification_setting_dialog_data() {
            $response = array();
            $setting_id = isset($_POST['_setting_id']) ? sanitize_text_field($_POST['_setting_id']) : 0;
            $device_id = isset($_POST['_device_id']) ? sanitize_text_field($_POST['_device_id']) : 0;
            $max_value = isset($_POST['_max_value']) ? sanitize_text_field($_POST['_max_value']) : 0;
            $min_value = isset($_POST['_min_value']) ? sanitize_text_field($_POST['_min_value']) : 0;
            $is_once_daily = isset($_POST['_is_once_daily']) ? sanitize_text_field($_POST['_is_once_daily']) : 0;
            if( !isset($_POST['_setting_id']) ) {
                // Create the post
                $new_post = array(
                    'post_type'     => 'notification',
                    'post_status'   => 'publish',
                    'post_author'   => get_current_user_id(),
                );    
                $setting_id = wp_insert_post($new_post);
                update_post_meta($setting_id, '_employee_id', get_current_user_id());
            }
            update_post_meta($setting_id, '_device_id', $device_id);
            update_post_meta($setting_id, '_max_value', $max_value);
            update_post_meta($setting_id, '_min_value', $min_value);
            update_post_meta($setting_id, '_is_once_daily', $is_once_daily);
            $response['html_contain'] = $this->display_exception_notification_setting_list($device_id);
            wp_send_json($response);
        }

        function del_exception_notification_setting_dialog_data() {
            $response = array();
            $setting_id = isset($_POST['_setting_id']) ? sanitize_text_field($_POST['_setting_id']) : 0;
            $device_id = isset($_POST['_device_id']) ? sanitize_text_field($_POST['_device_id']) : 0;
            wp_delete_post($setting_id);
            $response['html_contain'] = $this->display_exception_notification_setting_list($device_id);
            wp_send_json($response);
        }
    }
    $iot_messages = new iot_messages();
}

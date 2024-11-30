<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!class_exists('iot_messages')) {
    class iot_messages {

        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_iot_message_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this,'add_mermaid_script' ) );
            add_action( 'init', array( $this, 'register_iot_message_meta' ) );
            add_action( 'init', array( $this, 'register_iot_message_post_type' ) );

            add_action( 'wp_ajax_get_iot_device_dialog_data', array( $this, 'get_iot_device_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_iot_device_dialog_data', array( $this, 'get_iot_device_dialog_data' ) );
            add_action( 'wp_ajax_set_iot_device_dialog_data', array( $this, 'set_iot_device_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_iot_device_dialog_data', array( $this, 'set_iot_device_dialog_data' ) );
            add_action( 'wp_ajax_del_iot_device_dialog_data', array( $this, 'del_iot_device_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_iot_device_dialog_data', array( $this, 'del_iot_device_dialog_data' ) );

            if (!wp_next_scheduled('five_minutes_action_process_event')) {
                wp_schedule_event(time(), 'every_five_minutes', 'five_minutes_action_process_event');
            }
            add_action('five_minutes_action_process_event', array( $this, 'update_iot_message_meta_data'));
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

        // iot-device post type
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

        function display_iot_device_list() {
            ob_start();
            $todo_class = new to_do_list();
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( 'IoT devices', 'your-text-domain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $todo_class->display_select_todo('iot-devices');?></div>
                <div style="text-align:right; display:flex;">
                    <input type="text" id="search-device" style="display:inline" placeholder="Search..." />
                </div>
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Number', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_iot_device_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $device_number = get_post_meta(get_the_ID(), 'device_number', true);
                            ?>
                            <tr id="edit-iot-device-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo $device_number;?></td>
                                <td><?php the_title();?></td>
                                <td><?php the_content();?></td>
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
            </div>
            <div id="iot-device-dialog" title="IoT device dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_iot_device_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'iot-device',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'device_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );

            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Remove the initial search query
                unset($args['s']);

                // Add meta query for searching across all meta keys
                $meta_keys = get_post_type_meta_keys('iot-device');
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
            }
        
            return $query;
        }

        function display_iot_device_dialog($device_id=false) {
            ob_start();
            $device_number = get_post_meta($device_id, 'device_number', true);
            $device_title = get_the_title($device_id);
            $device_content = get_post_field('post_content', $device_id);
            ?>
            <fieldset>
                <input type="hidden" id="device-id" value="<?php echo esc_attr($device_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="device-number"><?php echo __( 'Number: ', 'your-text-domain' );?></label>
                <input type="text" id="device-number" value="<?php echo esc_attr($device_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="device-title"><?php echo __( 'Title: ', 'your-text-domain' );?></label>
                <input type="text" id="device-title" value="<?php echo esc_attr($device_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="device-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="device-content" rows="3" style="width:100%;"><?php echo esc_html($device_content);?></textarea>
                <?php
                $paged = max(1, get_query_var('paged')); // Get the current page number
                $query = $this->retrieve_iot_message_data($paged, $device_number);
                $data_points = []; // Initialize an array to hold valid temperature values
                $x_axis = [];
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $temperature = get_post_meta(get_the_ID(), 'temperature', true);
                        //$post_time = get_post_time('Y-m-d H:i:s', false, get_the_ID());
                        $post_time = get_post_time('H.i', false, get_the_ID());
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
                <?php }?>
                <label for="iot-message"><?php echo __( 'IoT messages: ', 'your-text-domain' );?></label>
                <?php echo $this->display_iot_message_list($device_id)?>
                <?php
                // transaction data vs card key/value
                $key_value_pair = array(
                    '_iot_device'   => $device_id,
                );
                $documents_class = new display_documents();
                $documents_class->get_transactions_by_key_value_pair($key_value_pair);
                ?>
            </fieldset>
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
                $data = array(
                    'ID'           => $device_id,
                    'post_title'   => $device_title,
                    'post_content' => $_POST['_device_content'],
                );
                wp_update_post( $data );
                update_post_meta($device_id, 'device_number', $device_number);
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
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'device_number', time());
            }
            $response = array('html_contain' => $this->display_iot_device_list());
            wp_send_json($response);
        }

        function del_iot_device_dialog_data() {
            wp_delete_post($_POST['_device_id'], true);
            $response = array('html_contain' => $this->display_iot_device_list());
            wp_send_json($response);
        }

        function select_iot_device_options($selected_option=0) {
            $query = $this->retrieve_iot_device_data(0);
            $options = '<option value="">Select device</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
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

        function update_iot_message_meta_data() {
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
                // Collect all instrument codes to minimize database queries
                $device_numbers = array();
                while ($iot_query->have_posts()) {
                    $iot_query->the_post();
                    $device_numbers[get_the_ID()] = get_post_meta(get_the_ID(), 'deviceID', true);
                }

                // Query 'iot-device' posts that match any of the collected instrument codes
                $inner_args = array(
                    'post_type' => 'iot-device',
                    'meta_query' => array(
                        array(
                            'key'     => 'device_number',
                            'value'   => array_values($device_numbers),
                            'compare' => 'IN',
                        )
                    ),
                    'posts_per_page' => -1,
                );
                $inner_query = new WP_Query($inner_args);

                if ($inner_query->have_posts()) {
                    while ($inner_query->have_posts()) {
                        $inner_query->the_post();
                        $device_number = get_post_meta(get_the_ID(), 'device_number', true);
                        // Find the corresponding iot-message post
                        $iot_post_id = array_search($device_number, $device_numbers);
                        if ($iot_post_id !== false) {
                            $temperature = get_post_meta($iot_post_id, 'temperature', true);
                            $humidity = get_post_meta($iot_post_id, 'humidity', true);
                            // Update 'temperature' and 'humidity' metadata
                            if ($temperature !== '') {
                                update_post_meta(get_the_ID(), 'temperature', $temperature);
                                $this->create_exception_notification_events(get_the_ID(), 'temperature', $temperature);
                            }
                            if ($humidity !== '') {
                                update_post_meta(get_the_ID(), 'humidity', $humidity);
                                $this->create_exception_notification_events(get_the_ID(), 'humidity', $humidity);
                            }
                            // Mark the 'iot-message' post as processed
                            update_post_meta($iot_post_id, 'processed', 1);
                        }
                    }
                    wp_reset_postdata();
                }
                wp_reset_postdata();
            }
        }

        function get_doc_reports_by_doc_field($field_type = false, $field_value = false) {
            $args = array(
                'post_type'      => 'doc-field',
                'posts_per_page' => -1, // Retrieve all posts
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'field_type',
                        'value'   => $field_type,
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'field_value',
                        'value'   => $field_value,
                        'compare' => '='
                    )
                ),
                'fields' => 'ids' // Only return post IDs
            );
            $query = new WP_Query($args);

            // Initialize an array to accumulate post IDs
            $accumulated_post_ids = array();
            if ($query->have_posts()) {
                foreach ($query->posts as $field_id) {
                    $args = array(
                        'post_type'  => 'doc-report',  // Specify the post type
                        'meta_query' => array(
                            array(
                                'key'     => $field_id,     // The meta key you want to search by
                                'value'   => $field_value,    // The value of the meta key you are looking for
                                'compare' => '=',             // Optional, default is '=', can be omitted
                            ),
                        ),
                        'fields' => 'ids', // Retrieve only the IDs of the posts
                    );
                    // Retrieve the post IDs
                    $post_ids = get_posts($args);
                    // Merge the retrieved post IDs with the accumulated array
                    $accumulated_post_ids = array_merge($accumulated_post_ids, $post_ids);
                }
            }
            // Return the accumulated post IDs
            return $accumulated_post_ids;
        }

        function create_exception_notification_events($device_id=false, $iot_sensor=false, $sensor_value=false) {
            $device_number = get_post_meta($device_id, 'device_number', true);
            //$documents_class = new display_documents();
            $query = $this->get_doc_reports_by_doc_field('_instrument', $device_id);

            if ($query->have_posts()) {
                foreach ($query->posts as $report_id) {
                    $max_value = get_post_meta($report_id, '_max_value', true);
                    $min_value = get_post_meta($report_id, '_min_value', true);
                    $employee_ids = get_post_meta($report_id, '_employees', true);

                    // Prepare the notification message
                    $five_minutes_ago = time() - (5 * 60);
                    $five_minutes_ago_formatted = wp_date(get_option('date_format'), $five_minutes_ago) . ' ' . wp_date(get_option('time_format'), $five_minutes_ago);

                    foreach ($employee_ids as $employee_id) {
                        if ($max_value && $sensor_value>$max_value) {
                            if ($iot_sensor=='temperature') {
                                $text_message = '#'.$device_number.' '.get_the_title($device_id).'在'.$five_minutes_ago_formatted.'的溫度是'.$sensor_value.'°C，已經大於設定的'.$max_value.'°C了。';
                            }
                            if ($iot_sensor=='humidity') {
                                $text_message = '#'.$device_number.' '.get_the_title($device_id).'在'.$five_minutes_ago_formatted.'的濕度是'.$sensor_value.'%，已經大於設定的'.$max_value.'%了。';
                            }
                        }
                        if ($min_value && $sensor_value<$min_value) {
                            if ($iot_sensor=='temperature') {
                                $text_message = '#'.$device_number.' '.get_the_title($device_id).'在'.$five_minutes_ago_formatted.'的溫度是'.$sensor_value.'°C，已經小於設定的'.$min_value.'°C了。';
                            }
                            if ($iot_sensor=='humidity') {
                                $text_message = '#'.$device_number.' '.get_the_title($device_id).'在'.$five_minutes_ago_formatted.'的濕度是'.$sensor_value.'%，已經小於設定的'.$min_value.'%了。';
                            }
                        }
                        $this->prepare_exception_notification_event($device_id, $employee_id, $text_message);
                    }
                }
                return $query->posts; // Return the array of post IDs
            }
        }

        function prepare_exception_notification_event($device_id=false, $user_id=false, $text_message=false) {
            // Check if a notification has been sent today
            $last_notification_time = get_user_meta($user_id, 'last_notification_time_' . $device_id, true);
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
            update_user_meta($user_id, 'last_notification_time_' . $device_id, time());
        }

        function send_delayed_notification_handler($params) {
            $user_id = $params['user_id'];
            $text_message = $params['text_message'];

            $user_data = get_userdata($user_id);
            $line_user_id = get_user_meta($user_id, 'line_user_id', true);

            $header_contents = array(
                array(
                    'type' => 'text',
                    'text' => 'Hello, ' . $user_data->display_name,
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
                        'uri' => home_url() . '/display-profiles/?_id=' . $user_id,
                    ),
                    'style' => 'primary',
                    'margin' => 'sm',
                ),
            );

            // Generate the Flex Message
            $flexMessage = $line_bot_api->set_bubble_message([
                'header_contents' => $header_contents,
                'body_contents' => $body_contents,
                'footer_contents' => $footer_contents,
            ]);
            // Send the message via the LINE API
            $line_bot_api = new line_bot_api();
            $line_bot_api->pushMessage([
                'to' => $line_user_id,
                'messages' => [$flexMessage],
            ]);
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
<?php /*            
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( 'IoT Messages', 'your-text-domain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $todo_class->display_select_todo('iot-message');?></div>
                <div style="text-align: right"></div>                        
            </div>
*/?>
            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Time', 'your-text-domain' );?></th>
<?php /*                        
                        <th><?php echo __( 'Device', 'your-text-domain' );?></th>
*/?>                        
                        <th><?php echo __( 'Sensor', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Value', 'your-text-domain' );?></th>
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
                            // Get post creation time
                            $post_time = get_post_time('Y-m-d H:i:s', false, get_the_ID());
                            $topic = get_the_title();
                            $message = get_the_content();
                            $device_number = get_post_meta(get_the_ID(), 'deviceID', true);
                            $temperature = get_post_meta(get_the_ID(), 'temperature', true);
                            $humidity = get_post_meta(get_the_ID(), 'humidity', true);
                            $latitude = get_post_meta(get_the_ID(), 'latitude', true);
                            $longitude = get_post_meta(get_the_ID(), 'longitude', true);
                            ?>
                            <tr id="edit-iot-message-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($post_time);?></td>
<?php /*                                
                                <td style="text-align:center;"><?php echo esc_html($device_number);?></td>
*/?>                                
                                <?php if ($temperature) {?>
                                    <td style="text-align:center;"><?php echo __( 'Temperature', 'your-text-domain' );?></td>
                                    <td style="text-align:center;"><?php echo esc_html($temperature);?></td>
                                <?php }?>
                                <?php if ($humidity) {?>
                                    <td style="text-align:center;"><?php echo __( 'Humidity(%)', 'your-text-domain' );?></td>
                                    <td style="text-align:center;"><?php echo esc_html($humidity);?><span style="font-size:small">%</span></td>
                                <?php }?>
                                <?php if ($latitude) {?>
                                    <td style="text-align:center;"><?php echo __( 'Latitude', 'your-text-domain' );?></td>
                                    <td style="text-align:center;"><?php echo esc_html($latitude);?></td>
                                <?php }?>
                                <?php if ($longitude) {?>
                                    <td style="text-align:center;"><?php echo __( 'Longitude', 'your-text-domain' );?></td>
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
                        //'key'     => 'device_number',
                        'value'   => $device_number,
                        'compare' => '=',
                    ),
                ),
            );
            if ($paged==0) $args['posts_per_page']=-1;
            $query = new WP_Query($args);
            return $query;
        }
    }
    $iot_messages = new iot_messages();
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!class_exists('http_client')) {
    class http_client {

        public function __construct() {

            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_http_client_scripts' ) );
            add_action( 'init', array( $this, 'register_http_client_post_type' ) );
            add_action( 'init', array( $this, 'register_iot_message_post_type' ) );
            //add_action( 'init', array( $this, 'register_geolocation_message_post_type' ) );
            add_action( 'init', array( $this, 'register_exception_notification_post_type' ) );

            add_action( 'wp_ajax_get_http_client_list_data', array( $this, 'get_http_client_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_http_client_list_data', array( $this, 'get_http_client_list_data' ) );
            add_action( 'wp_ajax_get_http_client_dialog_data', array( $this, 'get_http_client_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_http_client_dialog_data', array( $this, 'get_http_client_dialog_data' ) );
            add_action( 'wp_ajax_set_http_client_dialog_data', array( $this, 'set_http_client_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_http_client_dialog_data', array( $this, 'set_http_client_dialog_data' ) );
            add_action( 'wp_ajax_del_http_client_dialog_data', array( $this, 'del_http_client_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_http_client_dialog_data', array( $this, 'del_http_client_dialog_data' ) );
            add_action( 'wp_ajax_update_http_client_data', array( $this, 'update_http_client_data' ) );
            add_action( 'wp_ajax_nopriv_update_http_client_data', array( $this, 'update_http_client_data' ) );                
            add_action( 'wp_ajax_get_exception_notification_list_data', array( $this, 'get_exception_notification_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_exception_notification_list_data', array( $this, 'get_exception_notification_list_data' ) );
            add_action( 'wp_ajax_get_exception_notification_dialog_data', array( $this, 'get_exception_notification_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_exception_notification_dialog_data', array( $this, 'get_exception_notification_dialog_data' ) );
            add_action( 'wp_ajax_set_exception_notification_dialog_data', array( $this, 'set_exception_notification_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_exception_notification_dialog_data', array( $this, 'set_exception_notification_dialog_data' ) );
            add_action( 'wp_ajax_del_exception_notification_dialog_data', array( $this, 'del_exception_notification_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_exception_notification_dialog_data', array( $this, 'del_exception_notification_dialog_data' ) );
            add_action( 'wp_ajax_set_geolocation_message_data', array( $this, 'set_geolocation_message_data' ) );
            add_action( 'wp_ajax_nopriv_set_geolocation_message_data', array( $this, 'set_geolocation_message_data' ) );
            add_action( 'wp_ajax_get_geolocation_message_data', array( $this, 'get_geolocation_message_data' ) );
            add_action( 'wp_ajax_nopriv_get_geolocation_message_data', array( $this, 'get_geolocation_message_data' ) );
/*
            add_action( 'rest_api_init', array( $this, 'register_mqtt_rest_endpoint' ) );

            // Schedule the event
            if (!wp_next_scheduled('http_clients_initialization_event')) {
                wp_schedule_event(time(), 'hourly', 'http_clients_initialization_event');
            }
            
            // Hook into that event to run the initialization function
            add_action( 'http_clients_initialization_event', array( $this, 'initialize_all_http_clients' ) );
            //add_action( 'send_delayed_notification', array( $this, 'send_delayed_notification' ) );
            add_action( 'send_delayed_notification', array( $this, 'send_delayed_notification_handler' ) );
*/
        }
/*        
        function register_mqtt_rest_endpoint() {
            register_rest_route('mqtt/v1', '/initialize', array(
                'methods' => 'GET',
                'callback' => array( $this, 'initialize_all_http_clients' ),
            ));
        }
        
        function initialize_all_http_clients() {
            // Fetch all MQTT client posts
            $args = array(
                'post_type' => 'http-client',
                'posts_per_page' => -1,
            );
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                $topics = [];
                while ($query->have_posts()) {
                    $query->the_post();
                    $topic = get_the_title();
                    $topics[] = $topic;
                }
                wp_reset_postdata();
                return new WP_REST_Response($topics, 200);
            } else {
                return new WP_REST_Response('No MQTT client posts found.', 404);
            }
        }
*/
        function enqueue_http_client_scripts() {
            $version = time(); // Update this version number when you make changes
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
            wp_enqueue_script('mqtt-js', "https://unpkg.com/mqtt/dist/mqtt.min.js");
            //wp_enqueue_script('leaflet-script', "https://unpkg.com/leaflet/dist/leaflet.js");
            //wp_enqueue_style('leaflet-style', "https://unpkg.com/leaflet@1.7.1/dist/leaflet.css");

            wp_enqueue_script('http-client', plugins_url('http-client.js', __FILE__), array('jquery'), $version);
            wp_localize_script('http-client', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('http-client-nonce'), // Generate nonce
            ));                
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

        // Register geolocation-message post type
        function register_iot_message_post_type() {
            $labels = array(
                'menu_name'     => _x('iot-message', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_rest'  => true,
                //'show_in_menu'  => false,
            );
            register_post_type( 'iot-message', $args );
        }
/*
        // Register geolocation-message post type
        function register_geolocation_message_post_type() {
            $labels = array(
                'menu_name'     => _x('Geolocation', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'geolocation-message', $args );
        }
*/
        // Register exception-notification post type
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

        // Geolocation message
        function display_iot_message_list() {
            ob_start();
            $profiles_class = new display_profiles();
            $current_user_id = get_current_user_id();
            $current_user = get_userdata($current_user_id);
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $is_site_admin = $profiles_class->is_site_admin();
    
            // Check if the user is administrator
            if ($is_site_admin || current_user_can('administrator')) {
                ?>
                <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
                <h2 style="display:inline;"><?php echo __( 'IoT Messages', 'your-text-domain' );?></h2>
                <fieldset>
                    <div style="display:flex; justify-content:space-between; margin:5px;">
                        <div><?php $profiles_class->display_select_profile(5);?></div>                        
                        <div style="text-align: right"></div>                        
                    </div>
        
                    <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Time', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Receiver', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Message', 'your-text-domain' );?></th>
                            <th><?php echo __( 'T(C)', 'your-text-domain' );?></th>
                            <th><?php echo __( 'H(%)', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Latitude', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Longitude', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        //$query = $this->retrieve_iot_message_data();
                        //$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                        //$query = $this->retrieve_iot_message_data($paged);
                        // Define the custom pagination parameters
                        $posts_per_page = get_option('operation_row_counts');
                        $current_page = max(1, get_query_var('paged')); // Get the current page number
                        $query = $this->retrieve_iot_message_data($current_page);
                        $total_posts = $query->found_posts;
                        $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages

                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                // Get post creation time
                                $post_time = get_post_time('Y-m-d H:i:s', false, get_the_ID());
                                $topic = get_the_title();
                                $message = get_the_content();
                                $temperature = get_post_meta(get_the_ID(), 'temperature', true);
                                $humidity = get_post_meta(get_the_ID(), 'humidity', true);
                                $latitude = get_post_meta(get_the_ID(), 'latitude', true);
                                $longitude = get_post_meta(get_the_ID(), 'longitude', true);
                                ?>
                                <tr id="edit-geolocation-message-<?php the_ID();?>">
                                    <td style="text-align:center;"><?php echo esc_html($post_time);?></td>
                                    <td style="text-align:center;"><?php the_title();?></td>
                                    <td><?php the_content();?></td>
                                    <td style="text-align:center;"><?php echo esc_html($temperature);?></td>
                                    <td style="text-align:center;"><?php echo esc_html($humidity);?></td>
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
                        if ($current_page > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($current_page - 1)) . '"> < </a></span>';
                        echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $current_page, $total_pages) . '</span>';
                        if ($current_page < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($current_page + 1)) . '"> > </a></span>';
                        ?>
                    </div>
                    </fieldset>
                    <?php
/*                    
                    // Pagination
                    $total_pages = $query->max_num_pages;
                    if ($total_pages > 1) {
                        $current_page = max(1, get_query_var('paged'));
                        echo paginate_links(array(
                            'base'      => get_pagenum_link(1) . '%_%',
                            'format'    => '/page/%#%',
                            'current'   => $current_page,
                            'total'     => $total_pages,
                            'prev_text' => __('« Prev'),
                            'next_text' => __('Next »'),
                        ));
                    }
*/                        
                    ?>
                </fieldset>
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
                'posts_per_page' => 20, // Show 20 records per page
                'orderby'        => 'date',
                'order'          => 'DESC',
                'paged'          => $paged,
            );
            $query = new WP_Query($args);
            return $query;
        }
/*        
        function retrieve_iot_message_data() {
            $args = array(
                'post_type'      => 'iot-message',
                'posts_per_page' => -1,        
            );
            $query = new WP_Query($args);
            return $query;
        }
*/
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

        // HTTP Client
        function display_http_client_list() {
            ob_start();
            $profiles_class = new display_profiles();
            $current_user_id = get_current_user_id();
            $current_user = get_userdata($current_user_id);
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $is_site_admin = $profiles_class->is_site_admin();
    
            // Check if the user is administrator
            if ($is_site_admin || current_user_can('administrator')) {
                ?>
                <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
                <h2 style="display:inline;"><?php echo __( 'HTTP Client', 'your-text-domain' );?></h2>
                <fieldset>
                    <div style="display:flex; justify-content:space-between; margin:5px;">
                        <div><?php $profiles_class->display_select_profile(4);?></div>                        
                        <div style="text-align: right"></div>                        
                    </div>
        
                    <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'ID', 'your-text-domain' );?></th>
                            <th><?php echo __( 'description', 'your-text-domain' );?></th>
                            <th><?php echo __( 'SSID', 'your-text-domain' );?></th>
                            <th><?php echo __( 'password', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Tc', 'your-text-domain' );?></th>
                            <th><?php echo __( 'H', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $query = $this->retrieve_http_client_list();
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $client_id = get_post_meta(get_the_ID(), 'client_id', true);
                                $ssid = get_post_meta(get_the_ID(), 'ssid', true);
                                $password = get_post_meta(get_the_ID(), 'password', true);
                                $temperature = get_post_meta(get_the_ID(), 'temperature', true);
                                $humidity = get_post_meta(get_the_ID(), 'humidity', true);
                                ?>
                                <tr id="edit-http-client-<?php the_ID();?>">
                                    <td style="text-align:center;"><?php echo esc_html($client_id);?></td>
                                    <td><?php the_content();?></td>
                                    <td style="text-align:center;"><?php echo esc_html($ssid);?></td>
                                    <td style="text-align:center;"><?php echo esc_html($password);?></td>
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
                    </fieldset>        
                </fieldset>
                <div id="http-client-dialog" title="HTTP Client dialog"></div>
                <?php
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
            return ob_get_clean();
        }
        
        function retrieve_http_client_list() {
            $args = array(
                'post_type'      => 'http-client',
                'posts_per_page' => -1,        
            );
            $query = new WP_Query($args);
            return $query;
        }

        function get_http_client_list_data() {
            $response = array('html_contain' => $this->display_http_client_list());
            wp_send_json($response);
        }

        function display_http_client_dialog($http_client_id=false) {
            $client_id = get_post_meta($http_client_id, 'client_id', true);
            $description = get_post_field('post_content', $http_client_id);
            $mqtt_topic = get_the_title($http_client_id);
            $ssid = get_post_meta($http_client_id, 'ssid', true);
            $password = get_post_meta($http_client_id, 'password', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="http-client-id" value="<?php echo $http_client_id;?>" />
                <label for="client-id"><?php echo __( 'Client ID:', 'your-text-domain' );?></label>
                <input type="text" id="client-id" value="<?php echo $client_id;?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="description"><?php echo __( 'Description:', 'your-text-domain' );?></label>
                <textarea id="description" rows="3" style="width:100%;"><?php echo $description;?></textarea>
                <label for="mqtt-messages"><?php echo __( 'Message received:', 'your-text-domain' );?></label>
                <div id="mqtt-messages-container" style="height:200px; font-size:smaller; overflow-y:scroll; border: 1px solid #ccc; padding: 10px; white-space: pre-wrap; word-wrap: break-word;">...</div>
                <label><?php echo __( 'Exception notification:', 'your-text-domain' );?></label>
                <div id="exception-notification-list">
                <?php echo $this->display_exception_notification_list($http_client_id);?>
                </div>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_http_client_dialog_data() {
            $response = array();
            $http_client_id = sanitize_text_field($_POST['_http_client_id']);
            $response['html_contain'] = $this->display_http_client_dialog($http_client_id);
            wp_send_json($response);
        }

        function set_http_client_dialog_data() {
            $response = array();
            if( isset($_POST['_http_client_id']) ) {
                $http_client_id = sanitize_text_field($_POST['_http_client_id']);
                $data = array(
                    'ID'           => $http_client_id,
                    'post_content' => sanitize_text_field($_POST['_description']),
                );
                wp_update_post( $data );
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => time(),
                    'post_content'  => 'xxx公司，xxx冷凍庫',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'http-client',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'client_id', time());
            }
            wp_send_json($response);
        }

        function del_http_client_dialog_data() {
            $response = array();
            wp_delete_post($_POST['_http_client_id'], true);
            wp_send_json($response);
        }

        function update_http_client_data() {
            if (isset($_POST['_topic']) && isset($_POST['_key']) && isset($_POST['_value'])) {
                $topic = sanitize_text_field($_POST['_topic']);
                $key = sanitize_text_field($_POST['_key']);
                $value = sanitize_text_field($_POST['_value']);

                // Find the http-client post by title
                $post = get_page_by_title($topic, OBJECT, 'http-client');

                // Update the post meta
                if ($key=='temperature') update_post_meta($post->ID, 'temperature', $value);
                if ($key=='humidity') update_post_meta($post->ID, 'humidity', $value);
                if ($key=="ssid") update_post_meta($post->ID, 'ssid', $value);
                if ($key=="password") update_post_meta($post->ID, 'password', $value);

                $query = $this->retrieve_exception_notification_data($post->ID);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $user_id = get_post_meta(get_the_ID(), 'user_id', true);
                        $max_temperature = (float) get_post_meta(get_the_ID(), 'max_temperature', true);
                        $max_humidity = (float) get_post_meta(get_the_ID(), 'max_humidity', true);
                        if ($key=='temperature' && $value>$max_temperature) $this->exception_notification_event($user_id, $topic, $value, $max_temperature);
                        if ($key=='humidity' && $value>$max_humidity) $this->exception_notification_event($user_id, $topic, $value, false, $max_humidity);
                    endwhile;
                    wp_reset_postdata();
                endif;

                wp_send_json_success(array('message' => 'Updated successfully.'));
            } else {
                wp_send_json_error(array('message' => 'Missing topic or value.'));
            }
        }

        function update_post_field($post_id, $field, $value) {
            // Update the post field
            $post_data = array(
                'ID' => $post_id,
                $field => $value,
            );
            wp_update_post($post_data);
        }
        
        // Exception notification
        function exception_notification_event($user_id=false, $topic=false, $value=false, $max_temperature=false, $max_humidity=false) {
            //$user_data = get_userdata($user_id);
            //$link_uri = home_url().'/display-profiles/?_id='.$user_id;
        
            // Find the post by title
            $post = get_page_by_title($topic, OBJECT, 'http-client');
            $content = get_post_field('post_content', $post->ID);
            
            // Prepare the notification message
            $five_minutes_ago = time() - (5 * 60);
            $five_minutes_ago_formatted = wp_date(get_option('date_format'), $five_minutes_ago) . ' ' . wp_date(get_option('time_format'), $five_minutes_ago);
        
            if ($max_temperature) {
                $text_message = '#'.$topic.' '.$content.'在'.$five_minutes_ago_formatted.'的溫度是'.$value.'°C，已經超過設定的'.$max_temperature.'°C了。';
            }
            if ($max_humidity) {
                $text_message = '#'.$topic.' '.$content.'在'.$five_minutes_ago_formatted.'的濕度是'.$value.'%，已經超過設定的'.$max_humidity.'%了。';
            }
        
            // Check if a notification has been sent today
            $last_notification_time = get_user_meta($user_id, 'last_notification_time_' . $topic, true);
            $today = wp_date('Y-m-d');
        
            if ($last_notification_time && wp_date('Y-m-d', $last_notification_time) === $today) {
                // Notification already sent today, do not send again
                return;
            }
        
            // Parameters to pass to the notification function
            $params = [
                'user_id' => $user_id,
                //'topic' => $topic,
                'text_message' => $text_message,
            ];
        
            // Schedule the event to run after 5 minutes (300 seconds)
            wp_schedule_single_event(time() + 300, 'send_delayed_notification', [$params]);
        
            // Update the last notification time
            update_user_meta($user_id, 'last_notification_time_' . $topic, time());
        }
        
        // Hook for sending delayed notification
        //add_action('send_delayed_notification', 'send_delayed_notification_handler');
        
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
/*        
        // Exception notification
        function exception_notification_event($user_id=false, $topic=false, $value=false, $max_temperature=false, $max_humidity=false) {
            $user_data = get_userdata($user_id);
            $link_uri = home_url().'/display-profiles/?_id='.$user_id;
            // Find the post by title
            $post = get_page_by_title($topic, OBJECT, 'http-client');
            $content = get_post_field('post_content', $post->ID);
        
            if ($max_temperature) $text_message = '#'.$topic.' '.$content.'的溫度已經超過'.$max_temperature.'°C。';
            if ($max_humidity) $text_message = '#'.$topic.' '.$content.'的濕度已經超過'.$max_humidity.'%。';

            $five_minutes_ago = time()-(5 * 60 * 1000);
            $five_minutes_ago = wp_date(get_option('date_format'), $five_minutes_ago).' '.wp_date(get_option('time_format'), $five_minutes_ago);
            if ($max_temperature) $text_message = '#'.$topic.' '.$content.'在'.$five_minutes_ago.'的溫度是'.$value.'°C，已經超過設定的'.$max_temperature.'°C了。';
            if ($max_humidity) $text_message = '#'.$topic.' '.$content.'在'.$five_minutes_ago.'的濕度是'.$value.'%，已經超過設定的'.$max_humidity.'%了。';

            // Parameters to pass to the notification function
            $params = [
                'user_id' => $user_id,
                'topic' => $topic,
                'text_message' => $text_message,
            ];
        
            // Schedule the event to run after 5 minutes (300 seconds)
            wp_schedule_single_event(time() + 300, 'send_delayed_notification', [$params]);
        }
        
        // Function to send the notification
        function send_delayed_notification($params) {
            $user_id = $params['user_id'];
            $topic = $params['topic'];
            $text_message = $params['text_message'];
        
            $user_data = get_userdata($user_id);
            $link_uri = home_url().'/display-profiles/?_id='.$user_id;
        
            $params = [
                'display_name' => $user_data->display_name,
                'link_uri' => $link_uri,
                'text_message' => $text_message,
            ];
        
            $flexMessage = set_flex_message($params);
            $line_bot_api = new line_bot_api();
            $line_bot_api->pushMessage([
                'to' => get_user_meta($user_id, 'line_user_id', TRUE),
                'messages' => [$flexMessage],
            ]);
        }
*/        
        function display_exception_notification_list($http_client_id=false) {
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
                    $query = $this->retrieve_exception_notification_data($http_client_id);
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $user_id = get_post_meta(get_the_ID(), 'user_id', true);
                            $user_data = get_userdata($user_id);
                            $max_temperature = get_post_meta(get_the_ID(), 'max_temperature', true);
                            $max_humidity = get_post_meta(get_the_ID(), 'max_humidity', true).'%';
                            ?>
                            <tr id="edit-exception-notification-<?php the_ID();?>">
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
                <div id="new-exception-notification" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            </fieldset>
            <div id="exception-notification-dialog" title="Exception notification dialog"></div>
            <div id="new-exception-notification-dialog" title="Exception notification dialog">
            <fieldset>
                <label for="new-user-id"><?php echo __( 'Name:', 'your-text-domain' );?></label>
                <select id="new-user-id" class="text ui-widget-content ui-corner-all"><?php echo $this->select_user_id_option_data();?></select>
                <label for="new-max-temperature"><?php echo __( 'Max. Temperature(C):', 'your-text-domain' );?></label>
                <input type="text" id="new-max-temperature" value="25" class="text ui-widget-content ui-corner-all" />
                <label for="new-max-humidity"><?php echo __( 'Max. Humidity(%):', 'your-text-domain' );?></label>
                <input type="text" id="new-max-humidity" value="80" class="text ui-widget-content ui-corner-all" />
                </div>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }
        
        function retrieve_exception_notification_data($http_client_id=false) {
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

        function get_exception_notification_list_data() {
            $http_client_id = sanitize_text_field($_POST['_http_client_id']);
            $response = array('html_contain' => $this->display_exception_notification_list($http_client_id));
            wp_send_json($response);
        }

        function display_exception_notification_dialog($exception_notification_id=false) {
            $user_id = get_post_meta($exception_notification_id, 'user_id', true);
            $max_temperature = get_post_meta($exception_notification_id, 'max_temperature', true);
            $max_humidity = get_post_meta($exception_notification_id, 'max_humidity', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="exception-notification-id" value="<?php echo $exception_notification_id;?>" />
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

        function get_exception_notification_dialog_data() {
            $response = array();
            $exception_notification_id = sanitize_text_field($_POST['_exception_notification_id']);
            $response['html_contain'] = $this->display_exception_notification_dialog($exception_notification_id);
            wp_send_json($response);
        }

        function set_exception_notification_dialog_data() {
            $response = array();
            if( isset($_POST['_exception_notification_id']) ) {
                $exception_notification_id = sanitize_text_field($_POST['_exception_notification_id']);
                update_post_meta($exception_notification_id, 'user_id', sanitize_text_field($_POST['_user_id']));
                update_post_meta($exception_notification_id, 'max_temperature', sanitize_text_field($_POST['_max_temperature']));
                update_post_meta($exception_notification_id, 'max_humidity', sanitize_text_field($_POST['_max_humidity']));
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => time(),
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'notification',
                );    
                $exception_notification_id = wp_insert_post($new_post);
                update_post_meta($exception_notification_id, 'http_client_id', sanitize_text_field($_POST['_http_client_id']));
                update_post_meta($exception_notification_id, 'user_id', sanitize_text_field($_POST['_user_id']));
                update_post_meta($exception_notification_id, 'max_temperature', sanitize_text_field($_POST['_max_temperature']));
                update_post_meta($exception_notification_id, 'max_humidity', sanitize_text_field($_POST['_max_humidity']));
            }
            wp_send_json($response);
        }

        function del_exception_notification_dialog_data() {
            $response = array();
            wp_delete_post($_POST['_exception_notification_id'], true);
            wp_send_json($response);
        }

        function select_user_id_option_data($selected_option = 0) {
            $users = get_users();
            $options = '<option value="">Select user</option>';            
            foreach ($users as $user) {
                $selected = ($selected_option == $user->ID) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
            }            
            return $options;
        }
    }
    $http_client = new http_client();
}

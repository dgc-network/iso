<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'display-documents.php';
require_once plugin_dir_path( __FILE__ ) . 'to-do-list.php';
require_once plugin_dir_path( __FILE__ ) . 'display-profiles.php';
require_once plugin_dir_path( __FILE__ ) . 'embedded-items.php';
require_once plugin_dir_path( __FILE__ ) . 'iot-messages.php';

function wp_enqueue_scripts_and_styles() {
    wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
    wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);

    // Enqueue the TinyMCE CDN script
    wp_enqueue_script('wp-tinymce', 'https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js', array(), null, true);
    // You can also enqueue the TinyMCE configuration script if needed
    wp_add_inline_script('wp-tinymce', '
        tinymce.init({
            selector: ".visual-editor", // Replace with your editor ID
            height: 400,
            plugins: "lists link image charmap fullscreen media paste",
            toolbar: "undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | visualblocks",
            setup: function (editor) {
                editor.on("change", function () {
                    editor.save();
                });
            }
        });
    ');

    wp_enqueue_style('wp-enqueue-css', plugins_url('/assets/css/wp-enqueue.css', __DIR__), '', time());

    wp_enqueue_script('iso-helper', plugins_url('js/iso-helper.js', __FILE__), array('jquery'), time());
    wp_localize_script('iso-helper', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('iso-helper-nonce'), // Generate nonce
    ));
}
add_action('wp_enqueue_scripts', 'wp_enqueue_scripts_and_styles');

function display_iso_helper_logo() {
    ob_start();
    $current_user_id = get_current_user_id();
    $site_id = get_user_meta($current_user_id, 'site_id', true);
    $image_url = get_post_meta($site_id, 'image_url', true);
    ?>
    <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
    <?php
    return ob_get_clean();
}

function is_site_admin($user_id=false, $site_id=false) {
    // Get the current user ID
    if (!$user_id) {
        $user_id = get_current_user_id();
        if (current_user_can('administrator')) return true;
    }
    if (!$site_id) $site_id = get_user_meta($user_id, 'site_id', true);
    // Get the user's site_admin_ids as an array
    $site_admin_ids = get_user_meta($user_id, 'site_admin_ids', true);
    // If $site_admin_ids is not an array, convert it to an array
    if (!is_array($site_admin_ids)) $site_admin_ids = array();
    // Check if the current user has the specified site_id in their metadata
    return in_array($site_id, $site_admin_ids);
}

// User is not logged in yet
function user_is_not_logged_in() {
    $line_login_api = new line_login_api();
    $line_login_api->display_line_login_button();
}

function is_site_not_configured($user_id=false) {
    if (empty($user_id)) $user_id=get_current_user_id();
    if (current_user_can('administrator')) {
        return false;
    }
    $user = get_userdata($user_id);
    // Get the site_id meta for the user
    $site_id = get_user_meta($user_id, 'site_id', true);
    $activated_site_users = get_post_meta($site_id, 'activated_site_users', true);
    if (!is_array($activated_site_users)) $activated_site_users = array();
    $user_exists = in_array($user_id, $activated_site_users);

    if ($site_id && $user_exists) {
        return false;
    }
    return true;
}
        
function display_NDA_assignment($user_id=false) {
    if (empty($user_id)) $user_id=get_current_user_id();
    $profiles_class = new display_profiles();
    $profiles_class->get_NDA_assignment($user_id);
}

function get_site_admin_ids_for_site($site_id=false) {
    $site_admin_ids = array();
    $args = array(
        'role'    => 'Administrator',
        'fields'  => 'ID', // Only retrieve user IDs
    );

    $user_query = new WP_User_Query($args);
    $user_ids = $user_query->get_results();
    $site_admin_ids = array_merge($site_admin_ids, $user_ids);

    // Step 1: Get all users who are linked to this site ID
    $args = array(
        'meta_query' => array(
            array(
                'key'     => 'site_id',    // User meta key where site_id is stored
                'value'   => $site_id,    // Match the provided site ID
                //'compare' => '=',         // Comparison operator
            ),
        ),
    );
    $users = get_users($args);

    if (!empty($users)) {
        // Step 2: Collect all "site_admin_ids" from matching users
        foreach ($users as $user) {
            $user_site_admin_ids = get_user_meta($user->ID, 'site_admin_ids', true);

            if (!empty($user_site_admin_ids) && is_array($user_site_admin_ids)) {
                $site_admin_ids = array_merge($site_admin_ids, $user_site_admin_ids);
            }
        }
    }

    // Remove duplicates and return the result
    return array_unique($site_admin_ids);
}

function init_webhook_events() {
    $line_bot_api = new line_bot_api();
    $open_ai_api = new open_ai_api();

    $entityBody = file_get_contents('php://input');
    $data = json_decode($entityBody, true);
    $events = $data['events'] ?? [];

    foreach ((array)$events as $event) {
        $line_user_id = $event['source']['userId'];
        $profile = $line_bot_api->getProfile($line_user_id);
        $display_name = str_replace(' ', '', $profile['displayName']);

        // Regular expression to detect URLs
        $urlRegex = '/\bhttps?:\/\/\S+\b/';
        // Match URLs in the text
        if (preg_match_all($urlRegex, $event['message']['text'], $matches)) {
            // Extract the matched URLs
            $urls = $matches[0];
            // Output the detected URLs
            foreach ($urls as $url) {
                // Parse the URL
                $parsed_url = parse_url($url);
                // Check if the URL contains a query string
                if (isset($parsed_url['query'])) {
                    // Parse the query string
                    parse_str($parsed_url['query'], $query_params);
                    // Check if the 'doc_id' parameter exists in the query parameters
                    if (isset($query_params['_duplicate_document'])) {
                        // Retrieve the value of the 'doc_id' parameter
                        $doc_id = $query_params['_duplicate_document'];
                        //$doc_title = get_post_meta($doc_id, 'doc_title', true);
                        $doc_title = get_the_title($doc_id);
                        $text_message = sprintf(
                            __( '您可以點擊下方按鍵將文件「%s」加入您的文件匣中。', 'textdomain' ),
                            $doc_title
                        );
                        
                    }
                }

                $header_contents = array(
                    array(
                        'type' => 'text',
                        'text' => 'Hello, ' . $display_name,
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
                            'label' => __( '點擊這裡', 'textdomain' ),
                            'uri' => $url,
                        ),
                        'style' => 'primary',
                        'margin' => 'sm',
                    ),
                );

                $line_bot_api->send_flex_message([
                    'replyToken' => $event['replyToken'],
                    'header_contents' => $header_contents,
                    'body_contents' => $body_contents,
                    'footer_contents' => $footer_contents,
                ]);
            }
        }
        
        // Regular webhook response
        switch ($event['type']) {
            case 'message':
                $message = $event['message'];
                switch ($message['type']) {
                    case 'text':
                        global $wpdb;
                        $user_id = $wpdb->get_var($wpdb->prepare(
                            "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'line_user_id' AND meta_value = %s",
                            $line_user_id
                        ));
                        $todo_class = new to_do_list();
                        $query = $todo_class->retrieve_start_job_data(0, $user_id, $message['text']);
                        if ( $query->have_posts() ) {
                            $header_contents = array(
                                array(
                                    'type' => 'text',
                                    'text' => 'Hello, ' . $display_name,
                                    'size' => 'lg',
                                    'weight' => 'bold',
                                ),
                            );

                            $body_content = array(
                                'type' => 'text',
                                'text' => sprintf(
                                    __( 'You can click on the list below to directly execute the %s related tasks.', 'textdomain' ),
                                    $message['text']
                                ),
                                'wrap' => true,
                            );
                            $body_contents[] = $body_content;

                            $footer_contents = array();
                            while ( $query->have_posts() ) {
                                $query->the_post(); // Setup post data
                                $doc_id = get_the_ID();
                                //$doc_title = get_post_meta($doc_id, 'doc_title', true);
                                $doc_title = get_the_title($doc_id);
                                $link_uri = home_url().'/to-do-list/?_select_todo=start-job&_job_id='.$doc_id;
                                $footer_content = array(
                                    'type' => 'button',
                                    'action' => array(
                                        'type' => 'uri',
                                        'label' => $doc_title,
                                        'uri' => $link_uri,
                                    ),
                                    'style' => 'primary',
                                    'margin' => 'sm',
                                );
                                $footer_contents[] = $footer_content;
                            } 
                            wp_reset_postdata();

                            $line_bot_api->send_flex_message([
                                'replyToken' => $event['replyToken'],
                                'header_contents' => $header_contents,
                                'body_contents' => $body_contents,
                                'footer_contents' => $footer_contents,
                            ]);
                        } else {
                            // Open-AI auto reply
                            $response = $open_ai_api->createChatCompletion($message['text']);
                            $line_bot_api->replyMessage([
                                'replyToken' => $event['replyToken'],
                                'messages' => [
                                    [
                                        'type' => 'text',
                                        'text' => $response
                                    ]                                                                    
                                ]
                            ]);
                        }
                        break;
                    default:
                        error_log('Unsupported message type: ' . $message['type']);
                        break;
                }
                break;
            default:
                error_log('Unsupported event type: ' . $event['type']);
                break;
        }
    }
}
add_action( 'parse_request', 'init_webhook_events' );

// Google Gemini AI
function generate_content($prompt=false, $each_line_link=false) {
    $gemini_api_key = get_user_meta(get_current_user_id(), 'gemini_api_key', true);
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $gemini_api_key;
    
    $data = array(
      "contents" => array(
        array(
          "parts" => array(
            array(
              "text" => $prompt,
            )
          )
        )
      )
    );
    
    $json_data = json_encode($data);
    
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Set to true to return the response
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        return 'Error:' . curl_error($ch);
    } else {
        $decoded_response = json_decode($response, true);

        if (isset($decoded_response['candidates'][0]['content']['parts'][0]['text'])) {
            $generated_text = $decoded_response['candidates'][0]['content']['parts'][0]['text'];
            return convert_content_to_styled_html($generated_text);
        } else {
            return __( 'Failed to generate text. Please enter the API key in my-profile page first.', 'textdomain' );
        }
    }
    curl_close($ch);
}

function convert_content_to_styled_html($content) {
    // Replace markdown-like elements with HTML
    $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
    // Headings (## Heading ## or ## Heading)
    $content = preg_replace('/^\#\#\s*(.+?)\s*$/um', '<h2>$1</h2>', $content);
    $content = preg_replace('/\*\*([^\*]+)\*\*/', '<strong>$1</strong>', $content); // Bold
    $content = preg_replace('/\*([^\*]+)\*/', '<em>$1</em>', $content);           // Italic
/*
    $content = preg_replace('/^\s*\*\s(.+)$/m', '<li>$1</li>', $content);         // List items
    $content = preg_replace_callback(
        '/(<li>.*?<\/li>)+/s',
        function ($matches) {
            return '<ul>' . $matches[0] . '</ul>';
        },
        $content
    );
*/    
    $content = preg_replace('/\n{2,}/', '</p><p>', $content); // Paragraphs
    $content = '<p>' . $content . '</p>'; // Wrap in paragraph tags
    $content = str_replace("\n", '<br>', $content); // Line breaks
    //$content = preg_replace('/<\/ul>\s*<ul>/', '', $content); // Clean nested lists
    return $content;
}

// Add the custom schedules
function select_cron_schedules_option($selected_option = false) {
    $options = '<option value="">' . __('None', 'textdomain') . '</option>';
    
    $intervals = [
        'hourly' => __('Per hour', 'textdomain'),
        'twicedaily' => __('Every 12 hours', 'textdomain'),
        'weekday_daily' => __('Once Daily on Weekdays Only', 'textdomain'),
        'daily' => __('Daily', 'textdomain'),
        'weekly' => __('Weekly', 'textdomain'),
        'biweekly' => __('Every Two Weeks', 'textdomain'),
        'monthly' => __('Monthly', 'textdomain'),
        'bimonthly' => __('Every Two Months', 'textdomain'),
        'half_yearly' => __('Every Six Months', 'textdomain'),
        'yearly' => __('Yearly', 'textdomain'),
    ];

    foreach ($intervals as $value => $label) {
        $selected = ($selected_option === $value) ? 'selected' : '';
        $options .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
    }

    return $options;
}

function add_weekday_only_cron_schedule($schedules) {
    $schedules['weekday_daily'] = array(
        'interval' => 86400, // 24 hours in seconds
        'display'  => __('Once Daily on Weekdays Only', 'textdomain'),
    );
    return $schedules;
}
add_filter('cron_schedules', 'add_weekday_only_cron_schedule');

function iso_helper_cron_schedules($schedules) {
    $schedules['biweekly'] = array(
        'interval' => 2 * WEEK_IN_SECONDS, // 2 weeks in seconds
        'display'  => __('Every Two Weeks', 'textdomain'),
    );
    $schedules['monthly'] = array(
        'interval' => 30 * DAY_IN_SECONDS, // Approximate monthly interval
        'display'  => __('Monthly', 'textdomain'),
    );
    $schedules['bimonthly'] = array(
        'interval' => 60.5 * DAY_IN_SECONDS, // Approximate monthly interval
        'display'  => __('Every Two Months', 'textdomain'),
    );
    $schedules['half_yearly'] = array(
        'interval' => 182.5 * DAY_IN_SECONDS, // Approximate half-year interval
        'display'  => __('Every Six Months', 'textdomain'),
    );
    $schedules['yearly'] = array(
        'interval' => 365 * DAY_IN_SECONDS, // Approximate yearly interval
        'display'  => __('Yearly', 'textdomain'),
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'iso_helper_cron_schedules' );

add_filter('cron_schedules', function($schedules) {
    $schedules['every_five_minutes'] = array(
        'interval' => 300, // 5 minutes in seconds
        'display'  => __('Every 5 Minutes'),
    );
    return $schedules;
});

register_activation_hook(__FILE__, function() {
    if (!wp_next_scheduled('five_minutes_action_process_event')) {
        wp_schedule_event(time(), 'every_five_minutes', 'five_minutes_action_process_event');
    }
});

register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('five_minutes_action_process_event');
});

function remove_weekday_event() {
    $timestamp = wp_next_scheduled('my_weekday_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'my_weekday_event');
    }
}

// Enqueue jQuery
function enqueue_export_scripts() {
    // Enqueue jQuery if not already loaded
    wp_enqueue_script('jquery');

    // Enqueue the TableToExcel library from the CDN
    wp_enqueue_script(
        'table-to-excel',
        'https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js',
        array(),
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_export_scripts');

// Add the custom REST API endpoint
function custom_api_get_data( WP_REST_Request $request ) {
    $param = $request->get_param('param');

    // Custom logic (modify based on your needs)
    $response_data = [
        'status'  => 'success',
        'message' => 'API works!',
        'param'   => $param
    ];

    return new WP_REST_Response($response_data, 200);
}

// Register API endpoint
function custom_register_api_routes() {
    register_rest_route('api/v1', '/data/', [
        'methods'  => 'GET', // Or 'POST'
        'callback' => 'custom_api_get_data',
        'permission_callback' => '__return_true' // Public API (Restrict if needed)
    ]);
}
add_action('rest_api_init', 'custom_register_api_routes');

// Add the custom REST API endpoint
function custom_api_post_data( WP_REST_Request $request ) {
    $data = $request->get_json_params(); // Get JSON payload

    if (empty($data['name'])) {
        return new WP_REST_Response(['error' => 'Missing name'], 400);
    }

    return new WP_REST_Response([
        'message' => 'Received!',
        'name'    => sanitize_text_field($data['name'])
    ], 200);
}

function custom_register_post_api() {
    register_rest_route('api/v1', '/submit/', [
        'methods'  => 'POST',
        'callback' => 'custom_api_post_data',
        'permission_callback' => '__return_true'
    ]);
}
add_action('rest_api_init', 'custom_register_post_api');
/*
function general_api_permission_callback() {
    return is_user_logged_in(); // Allow only logged-in users
}
*/
function general_api_permission_callback($request) {
    return is_user_logged_in() || jwt_auth_check_token($request);
}

// API endpoint
// Register the send-message API endpoint
function send_message_register_post_api() {
    register_rest_route('api/v1', '/send-message/', [
        'methods'  => 'POST',
        'callback' => 'send_message_api_post_data',
        //'permission_callback' => '__return_true', // Allow external authentication
        //'permission_callback' => 'general_api_permission_callback'
        'permission_callback' => function ($request) {
            return is_user_logged_in() || jwt_auth_check_token($request);
        }
    ]);
}
add_action('rest_api_init', 'send_message_register_post_api');

// Securely handle API request
function send_message_api_post_data(WP_REST_Request $request) {
    $data = $request->get_json_params(); // Get JSON payload
    $user_id = sanitize_text_field($data['user_id']);
    $user = get_userdata($user_id);
    $text_message = sanitize_text_field($data['text_message']);
    $link_uri = sanitize_text_field($data['link_uri']);
    $new_todo_id = sanitize_text_field($data['new_todo_id']);

    // Validate 'body_contents' exists and is an array
    if (empty($user_id) || empty($text_message) || empty($link_uri)) {
        return new WP_REST_Response(['error' => 'Invalid or missing body contents'], 400);
    }

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
                'label' => get_the_title($new_todo_id),
                'uri' => $link_uri, // Use the desired URI
            ),
            'style' => 'primary',
            'margin' => 'sm',
        ),
    );

    // Send message via Line Bot API
    $line_bot_api = new line_bot_api();

    $request_data = [
        'header_contents' => $header_contents,
        'body_contents' => $body_contents,
        'footer_contents' => $footer_contents,
    ];
    
    $line_user_id = get_user_meta($user->ID, 'line_user_id', true);
    
    if (!empty($line_user_id)) {
        $request_data['to'] = $line_user_id;
    }
    
    $line_bot_api->send_flex_message($request_data);
/*    
    $line_bot_api = new line_bot_api();
    $line_bot_api->send_flex_message([
        'to' => get_user_meta($user->ID, 'line_user_id', TRUE),
        'header_contents' => $header_contents,
        'body_contents' => $body_contents,
        'footer_contents' => $footer_contents,
    ]);
*/
    return new WP_REST_Response([
        'message' => 'Message Sent!',
        'alt_text' => $text_message,
    ], 200);
}

// Register the release-document API endpoint
function release_document_register_post_api() {
    register_rest_route('api/v1', '/release-document/', [
        'methods'  => 'POST',
        'callback' => 'release_document_api_post_data',
        'permission_callback' => function ($request) {
            return is_user_logged_in() || jwt_auth_check_token($request);
        }
    ]);
}
add_action('rest_api_init', 'release_document_register_post_api');

// Securely handle API request
function release_document_api_post_data(WP_REST_Request $request) {
    $data = $request->get_json_params(); // Get JSON payload
    $new_todo_id = sanitize_text_field($data['new_todo_id']);
    $user_id = sanitize_text_field($data['user_id']);
    $action_id = sanitize_text_field($data['action_id']);
    $next_job = sanitize_text_field($data['next_job']);
    $prev_report_id = sanitize_text_field($data['prev_report_id']);

    // Validate 'todo_id' exists and is an array
    if (empty($new_todo_id) || empty($user_id) || empty($action_id)) {
        return new WP_REST_Response(['error' => 'Invalid or missing request data'], 400);
    }

    update_post_meta($new_todo_id, 'submit_user', $user_id);
    update_post_meta($new_todo_id, 'submit_action', $action_id);
    update_post_meta($new_todo_id, 'submit_time', time());
    if ($prev_report_id) update_post_meta($prev_report_id, 'todo_status', $next_job );

    //if ($next_job==-1 || $next_job==-2) {
        //update_post_meta($new_todo_id, 'next_job', $next_job);
        // Notice the persons in site
        //$this->notice_the_persons_in_site($new_todo_id, $next_job);
    //}

    return new WP_REST_Response([
        'message' => 'Message Sent!',
        'new_todo_id' => $new_todo_id,
    ], 200);
}

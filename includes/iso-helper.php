<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'display-documents.php';
require_once plugin_dir_path( __FILE__ ) . 'to-do-list.php';
require_once plugin_dir_path( __FILE__ ) . 'display-profiles.php';
//require_once plugin_dir_path( __FILE__ ) . 'erp-cards.php';
require_once plugin_dir_path( __FILE__ ) . 'sub-items.php';
require_once plugin_dir_path( __FILE__ ) . 'iot-messages.php';

function wp_enqueue_scripts_and_styles() {
    wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
    wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);

    //wp_enqueue_script('wp-tinymce'); // WordPress built-in TinyMCE
    // Enqueue the TinyMCE CDN script
    wp_enqueue_script('wp-tinymce', 'https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js', array(), null, true);
    // You can also enqueue the TinyMCE configuration script if needed
    wp_add_inline_script('wp-tinymce', '
        tinymce.init({
            selector: ".editor-content", // Replace with your editor ID
            plugins: "advlist autolink lists link image charmap anchor visualblocks fullscreen print media table paste",
            toolbar: "undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | visualblocks"
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
    if (!$user_id && current_user_can('administrator')) return true;
    // Get the current user ID
    if (!$user_id) $user_id = get_current_user_id();
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
    $user = get_userdata($user_id);
    // Get the site_id meta for the user
    $site_id = get_user_meta($user_id, 'site_id', true);
    
    // Check if site_id does not exist or is empty
    if (empty($site_id)) {
        return true;
    }
    return false;
}

function get_NDA_assignment($user_id=false) {
    if (empty($user_id)) $user_id=get_current_user_id();
    $user = get_userdata($user_id);
    $site_id = get_user_meta($user_id, 'site_id', true);            
    ?>
    <div class="ui-widget" id="result-container">
        <h2 style="display:inline; text-align:center;"><?php echo __( '保密切結書', 'your-text-domain' );?></h2>
        <div>
            <label for="select-nda-site"><b><?php echo __( '甲方：', 'your-text-domain' );?></b></label>
            <select id="select-nda-site" class="text ui-widget-content ui-corner-all" >
                <option value=""><?php echo __( 'Select Site', 'your-text-domain' );?></option>
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
            <label for="unified-number"><?php echo __( '統一編號：', 'your-text-domain' );?></label>
            <input type="text" id="unified-number" class="text ui-widget-content ui-corner-all" />
        </div>
        <div>
            <label for="display-name"><b><?php echo __( '乙方：', 'your-text-domain' );?></b></label>
            <input type="text" id="display-name" value="<?php echo $user->display_name;?>" class="text ui-widget-content ui-corner-all" />
            <label for="identify-number"><?php echo __( '身分證字號：', 'your-text-domain' );?></label>
            <input type="text" id="identify-number" class="text ui-widget-content ui-corner-all" />
            <input type="hidden" id="user-id" value="<?php echo $user_id;?>"/>
        </div>
        <div id="site-content">
            <!-- The site content will be displayed here -->
        </div>
        <div>
            <label for="identify-number"><?php echo __( '簽名：', 'your-text-domain' );?></label>
            <div id="signature-pad-div">
                <div>
                    <canvas id="signature-pad" width="500" height="200" style="border:1px solid #000;"></canvas>
                </div>
                <button id="clear-signature" style="margin:3px;">Clear</button>
            </div>
        </div>
        <div style="display:flex;">
            <?php echo __( '日期：', 'your-text-domain' );?>
            <input type="date" id="nda-date" value="<?php echo wp_date('Y-m-d', time())?>"/>
        </div>
        <hr>
        <button type="submit" id="nda-submit"><?php echo __( 'Submit', 'your-text-domain' );?></button>
        <button type="submit" id="nda-exit"><?php echo __( 'Exit', 'your-text-domain' );?></button>
    </div>
    <?php
}

function set_NDA_assignment() {
    $response = array();
    if(isset($_POST['_user_id']) && isset($_POST['_site_id'])) {
        $user_id = intval($_POST['_user_id']);        
        $site_id = intval($_POST['_site_id']);        
        update_user_meta( $user_id, 'site_id', $site_id);
        update_user_meta( $user_id, 'display_name', sanitize_text_field($_POST['_display_name']));
        update_user_meta( $user_id, 'identity_number', sanitize_text_field($_POST['_identity_number']));
        update_user_meta( $user_id, 'signature_image', $_POST['_signature_image']);
        update_user_meta( $user_id, 'nda_date', sanitize_text_field($_POST['_nda_date']));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_NDA_assignment', 'set_NDA_assignment' );
add_action( 'wp_ajax_nopriv_set_NDA_assignment', 'set_NDA_assignment' );

function get_site_profile_content() {
    // Check if the site_id is passed
    if(isset($_POST['site_id'])) {
        $site_id = intval($_POST['site_id']);

        // Retrieve the post content
        $post = get_post($site_id);

        if($post && $post->post_type == 'site-profile') {
            wp_send_json_success(array('content' => apply_filters('the_content', $post->post_content)));
        } else {
            wp_send_json_error(array('message' => 'Invalid site ID or post type.'));
        }
    } else {
        wp_send_json_error(array('message' => 'No site ID provided.'));
    }
}
add_action( 'wp_ajax_get_site_profile_content', 'get_site_profile_content' );
add_action( 'wp_ajax_nopriv_get_site_profile_content', 'get_site_profile_content' );

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
                        $doc_title = get_post_meta($doc_id, 'doc_title', true);
                        $text_message = __( '您可以點擊下方按鍵將文件「', 'your-text-domain' ).$doc_title.__( '」加入您的文件匣中。', 'your-text-domain' );
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
                            'label' => 'Click me!',
                            'uri' => $url,
                        ),
                        'style' => 'primary',
                        'margin' => 'sm',
                    ),
                );

                $line_bot_api->send_bubble_message([
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
                        
                            $body_contents = array();
                            $text_message = __( '您可以點擊下方列示，直接執行『', 'your-text-domain' ) . $message['text'] . __( '』相關作業。', 'your-text-domain' );
                            $body_content = array(
                                'type' => 'text',
                                'text' => $text_message,
                                'wrap' => true,
                            );
                            $body_contents[] = $body_content;

                            $footer_contents = array();
                            while ( $query->have_posts() ) {
                                $query->the_post(); // Setup post data
                                $doc_title = get_post_meta(get_the_ID(), 'doc_title', true);
                                $link_uri = home_url().'/to-do-list/?_select_todo=start-job&_job_id='.get_the_ID();
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

                            $line_bot_api->send_bubble_message([
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

function generate_content($userMessage) {
    $gemini_api_key = get_user_meta(get_current_user_id(), 'gemini_api_key', true);
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $gemini_api_key;
    
    $data = array(
      "contents" => array(
        array(
          "parts" => array(
            array(
              "text" => $userMessage,
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
            return "Failed to generate text. Please enter the API key in my-profile page first.";
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
    $content = preg_replace('/^\s*\*\s(.+)$/m', '<li>$1</li>', $content);         // List items
    $content = preg_replace_callback(
        '/(<li>.*?<\/li>)+/s',
        function ($matches) {
            return '<ul>' . $matches[0] . '</ul>';
        },
        $content
    );
    $content = preg_replace('/\n{2,}/', '</p><p>', $content); // Paragraphs
    $content = '<p>' . $content . '</p>'; // Wrap in paragraph tags
    $content = str_replace("\n", '<br>', $content); // Line breaks
    $content = preg_replace('/<\/ul>\s*<ul>/', '', $content); // Clean nested lists
    return $content;
}

// Add the custom schedules
function select_cron_schedules_option($selected_option = false) {
    $options = '<option value="">' . __('None', 'your-text-domain') . '</option>';
    
    $intervals = [
        'hourly' => __('每小時', 'your-text-domain'),
        'twicedaily' => __('每12小時', 'your-text-domain'),
        'weekday_daily' => __('週間每日', 'your-text-domain'),
        'daily' => __('每日', 'your-text-domain'),
        'weekly' => __('每週', 'your-text-domain'),
        'biweekly' => __('每二週', 'your-text-domain'),
        'monthly' => __('每月', 'your-text-domain'),
        'bimonthly' => __('每二月', 'your-text-domain'),
        'half_yearly' => __('每半年', 'your-text-domain'),
        'yearly' => __('每年', 'your-text-domain'),
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
        'display'  => __('Once Daily on Weekdays Only'),
    );
    return $schedules;
}
add_filter('cron_schedules', 'add_weekday_only_cron_schedule');

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

function enqueue_editor_scripts() {
    if (!is_page('editor-page-slug')) { // Replace with the specific page slug or condition
        return;
    }

    // Enqueue TinyMCE or CKEditor assets
    wp_enqueue_script('wp-tinymce'); // WordPress built-in TinyMCE
    wp_enqueue_script(
        'front-end-editor',
        get_template_directory_uri() . '/js/front-end-editor.js', // Adjust the path
        ['jquery', 'wp-tinymce'],
        null,
        true
    );

    wp_enqueue_style(
        'front-end-editor-styles',
        get_template_directory_uri() . '/css/front-end-editor.css' // Optional, for custom styles
    );
}
//add_action('wp_enqueue_scripts', 'enqueue_editor_scripts');

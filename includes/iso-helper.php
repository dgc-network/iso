<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'display-documents.php';
require_once plugin_dir_path( __FILE__ ) . 'to-do-list.php';
require_once plugin_dir_path( __FILE__ ) . 'display-profiles.php';

function admin_enqueue_scripts_and_styles() {
    $version = '1.0.0.'.time(); // Update this version number when you make changes
    wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
    wp_enqueue_script('jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), '1.13.2', true);
    wp_enqueue_style('admin-enqueue-css', plugins_url('/assets/css/admin-enqueue.css', __DIR__), '', $version);
    wp_enqueue_script('admin-enqueue-js', plugins_url('/assets/js/admin-enqueue.js', __DIR__), array('jquery', 'jquery-ui-js'), $version, true);
    wp_localize_script('admin-enqueue-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('iso_documents_nonce'), // Generate nonce
    ));
}
add_action('admin_enqueue_scripts', 'admin_enqueue_scripts_and_styles');

function wp_enqueue_scripts_and_styles() {
    $version = '1.0.5.'.time(); // Update this version number when you make changes
    wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
    wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
    wp_enqueue_style('wp-enqueue-css', plugins_url('/assets/css/wp-enqueue.css', __DIR__), '', $version);

    wp_enqueue_script('wp-enqueue-js', plugins_url('/assets/js/wp-enqueue.js', __DIR__), array('jquery'), $version);
    wp_localize_script('wp-enqueue-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('iso_documents_nonce'), // Generate nonce
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

function display_iso_statement($atts) {
    ob_start();

    // Extract and sanitize the shortcode attributes
    $atts = shortcode_atts(array(
        'parent_category' => false,
    ), $atts);

    $parent_category = $atts['parent_category'];

    $meta_query = array(
        'relation' => 'OR',
    );

    if ($parent_category) {
        $meta_query[] = array(
            'key'   => 'parent_category',
            'value' => $parent_category,
        );
    }

    $args = array(
        'post_type'      => 'iso-category',
        'posts_per_page' => -1,
        'meta_query'     => $meta_query,
    );

    $query = new WP_Query($args);

    while ($query->have_posts()) : $query->the_post();
        $category_url = get_post_meta(get_the_ID(), 'category_url', true);
        $start_ai_url = '/display-documents/?_statement=' . get_the_ID();
        ?>
        <div class="iso-category-content">
            <?php the_content(); ?>
            <div class="wp-block-buttons">
                <div class="wp-block-button">
                    <a class="wp-block-button__link wp-element-button" href="<?php echo esc_url($category_url); ?>"><?php the_title(); ?></a>                                            
                </div>
                <div class="wp-block-button">
                    <a class="wp-block-button__link wp-element-button" href="<?php echo esc_url($start_ai_url); ?>"><?php echo __( '啟動AI輔導', 'your-text-domain' ); ?></a>                                            
                </div>
            </div>
        </div>
        <?php
    endwhile;
    
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('display-iso-statement', 'display_iso_statement');

function display_no_permission_page() {
    //ob_start();
    ?>
    <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
    <?php
    //return ob_get_clean();    
}

function set_flex_message($params) {
    $display_name = $params['display_name'];
    $link_uri = $params['link_uri'];
    $text_message = $params['text_message'];

    // Flex Message JSON structure with a button
    return [
        'type' => 'flex',
        'altText' => $text_message,
        'contents' => [
            'type' => 'bubble',
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => 'Hello, '.$display_name,
                        'size' => 'lg',
                        'weight' => 'bold',
                    ],
                    [
                        'type' => 'text',
                        'text' => $text_message,
                        'wrap' => true,
                    ],
                ],
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'button',
                        'action' => [
                            'type' => 'uri',
                            'label' => 'Click me!',
                            'uri' => $link_uri, // Replace with your desired URI
                        ],
                    ],
                ],
            ],
        ],
    ];
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
                    if (isset($query_params['_get_shared_doc_id'])) {
                        // Retrieve the value of the 'doc_id' parameter
                        $doc_id = $query_params['_get_shared_doc_id'];
                        $doc_title = get_post_meta($doc_id, 'doc_title', true);
                        $text_message = __( '您可以點擊下方按鍵將文件「', 'your-text-domain' ).$doc_title.__( '」加入您的文件匣中。', 'your-text-domain' );
                    }
                }
                $params = [
                    'display_name' => $display_name,
                    'link_uri' => $url,
                    'text_message' => $text_message,
                ];
                $flexMessage = set_flex_message($params);
                $line_bot_api->replyMessage([
                    'replyToken' => $event['replyToken'],
                    'messages' => [$flexMessage],
                ]);            
            }
        }
        
        // Regular webhook response
        switch ($event['type']) {
            case 'message':
                $message = $event['message'];
                switch ($message['type']) {
                    case 'text':
                        $query = get_keyword_matched($message['text']);
                        if ($query) {
                            if ($query==-1) {
                                $text_message = 'You have not logged in yet. Please click the button below to go to the Login/Registration system.';
                                $text_message = __( '您尚未登入系統！請點擊下方按鍵登入或註冊本系統。', 'your-text-domain' );
                                // Encode the Chinese characters for inclusion in the URL
                                $link_uri = home_url().'/display-profiles/?_id='.$line_user_id.'&_name='.urlencode($display_name);

                                $params = [
                                    'display_name' => $display_name,
                                    'link_uri' => $link_uri,
                                    'text_message' => $text_message,
                                ];

                                $flexMessage = set_flex_message($params);

                                $line_bot_api->replyMessage([
                                    'replyToken' => $event['replyToken'],
                                    'messages' => [$flexMessage],
                                ]);
                            } else {
                                if ( $query->have_posts() ) {
                                    $text_message = __( '您可以點擊下方按鍵執行『', 'your-text-domain' ).$message['text'].__( '』相關作業。', 'your-text-domain' );
                                    $link_uri = home_url().'/to-do-list/?_search='.urlencode($message['text']);
                                    $params = [
                                        'display_name' => $display_name,
                                        'link_uri' => $link_uri,
                                        'text_message' => $text_message,
                                    ];
                                    $flexMessage = set_flex_message($params);
                                    $line_bot_api->replyMessage([
                                        'replyToken' => $event['replyToken'],
                                        'messages' => [$flexMessage],
                                    ]);
                                }
                            }
                        } else {
                            // Open-AI auto reply
                            $response = $open_ai_api->createChatCompletion($message['text']);

                            //$response = $open_ai_api->generate_openai_proposal($message['text']);
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

function get_keyword_matched($search_query) {

    if (strpos($search_query, '註冊') !== false) return -1;
    if (strpos($search_query, '登入') !== false) return -1;
    if (strpos($search_query, '登錄') !== false) return -1;
    if (strpos($search_query, 'login') !== false) return -1;
    if (strpos($search_query, 'Login') !== false) return -1;

    // WP_Query arguments
    $args = array(
        'post_type'      => 'document',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => 'todo_status',
                'compare' => 'NOT EXISTS',
            ),
        ),
    );

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

    // Instantiate new WP_Query
    $query = new WP_Query( $args );
    
    // Check if there are any posts that match the query
    if ( $query->have_posts() ) return $query;

    return false;
}

function proceed_to_registration_login($line_user_id, $display_name) {
    // Using Line User ID to register and login into the system
    $users = get_users( array( 'meta_value' => $line_user_id ));
    if (empty($users)) {
        $user_id = wp_insert_user( array(
            'user_login' => $line_user_id,
            'user_pass' => $line_user_id,
        ));
        add_user_meta( $user_id, 'line_user_id', $line_user_id);
    } else {
        // Get user by 'line_user_id' meta
        global $wpdb;
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'line_user_id' AND meta_value = %s",
            $line_user_id
        ));
        $site_id = get_user_meta($user_id, 'site_id', true);
        $site_title = get_the_title($site_id);
    }
    $current_user = get_userdata( $user_id );
    ob_start();
    ?>
    <div class="ui-widget">
        <h2><?php echo __( 'User registration/login', 'your-text-domain' );?></h2>
        <fieldset>
            <label for="display-name"><?php echo __( 'Name:', 'your-text-domain' );?></label>
            <input type="text" id="display-name" value="<?php echo esc_attr($display_name);?>" class="text ui-widget-content ui-corner-all" />
            <label for="user-email"><?php echo __( 'Email:', 'your-text-domain' );?></label>
            <input type="text" id="user-email" value="<?php echo esc_attr($current_user->user_email);?>" class="text ui-widget-content ui-corner-all" />
            <label for="site-id"><?php echo __( 'Site:', 'your-text-domain' );?></label>
            <input type="text" id="site-title" value="<?php echo esc_attr($site_title);?>" class="text ui-widget-content ui-corner-all" />
            <div id="site-hint" style="display:none; color:#999;"></div>
            <input type="hidden" id="site-id" value="<?php echo esc_attr($site_id);?>" />
            <input type="hidden" id="log" value="<?php echo esc_attr($line_user_id);?>" />
            <input type="hidden" id="pwd" value="<?php echo esc_attr($line_user_id);?>" />
            <hr>
            <input type="submit" id="wp-login-submit" class="button button-primary" value="Submit" />
            <a href="https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=YOUR_CHANNEL_ID&redirect_uri=YOUR_CALLBACK_URL&state=YOUR_CSRF_TOKEN&scope=profile%20openid%20email"><img src="https://d.line-scdn.net/r/line_lp/button_login.png"></a>

        </fieldset>
    </div>
    <?php        
    return ob_get_clean();
}

// User did not login system yet
function is_user_not_in_site() {

}

function check_user_site_id($user_id=false) {
    if (empty($user_id)) $user_id=get_current_user_id();
    // Get the site_id meta for the user
    $site_id = get_user_meta($user_id, 'site_id', true);
    
    // Check if site_id does not exist or is empty
    if (empty($site_id)) {
        //return true;
    }
    ?>
    <div class="ui-widget" id="result-container">
    <h2 style="display:inline;"><?php echo __( '保密切結書', 'your-text-domain' );?></h2>
    <div style="display:flex; justify-content:space-between; margin:5px;">
        <div style="display:flex;">
            <?php echo __( '甲方：', 'your-text-domain' );?>
            <select id="select-site" >
                <option value=""><?php echo __( 'Select Site', 'your-text-domain' );?></option>
                <?php
                    $site_args = array(
                        'post_type'      => 'site',
                        'posts_per_page' => -1,
                    );
                    $sites = get_posts($site_args);    
                    foreach ($sites as $site) {
                        echo '<option value="' . esc_attr($site->ID) . '" >' . esc_html($site->post_title) . '</option>';
                    }
                ?>
            </select>
        </div>
        <div style="text-align: right">
            <button type="submit" id="my-profile-submit"><?php echo __( 'Submit', 'your-text-domain' );?></button>
        </div>
    </div>
    </div>
    <?php
    exit;
    //return false;
}

// User did not login system yet
function user_did_not_login_yet() {
    if( isset($_GET['_id']) && isset($_GET['_name']) ) {
        // Using Line User ID to register and login into the system
        $array = get_users( array( 'meta_value' => $_GET['_id'] ));
        if (empty($array)) {
            $user_id = wp_insert_user( array(
                'user_login' => $_GET['_id'],
                'user_pass' => $_GET['_id'],
            ));
            add_user_meta( $user_id, 'line_user_id', $_GET['_id']);
        } else {
            // Get user by 'line_user_id' meta
            global $wpdb;
            $user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'line_user_id' AND meta_value = %s",
                $_GET['_id']
            ));
            $site_id = get_user_meta($user_id, 'site_id', true);
            $site_title = get_the_title($site_id);
        }
        //$user = get_user_by( 'ID', $user_id );
        $user = get_userdata( $user_id );
        ?>
        <div class="ui-widget">
            <h2><?php echo __( 'User registration/login', 'your-text-domain' );?></h2>
            <fieldset>
                <label for="display-name"><?php echo __( 'Name:', 'your-text-domain' );?></label>
                <input type="text" id="display-name" value="<?php echo esc_attr($_GET['_name']);?>" class="text ui-widget-content ui-corner-all" />
                <label for="user-email"><?php echo __( 'Email:', 'your-text-domain' );?></label>
                <input type="text" id="user-email" value="<?php echo esc_attr($user->user_email);?>" class="text ui-widget-content ui-corner-all" />
<?php /*                
                <label for="site-id"><?php echo __( 'Site:', 'your-text-domain' );?></label>
                <input type="text" id="site-title" value="<?php echo esc_attr($site_title);?>" class="text ui-widget-content ui-corner-all" />
                <div id="site-hint" style="display:none; color:#999;"></div>
                <input type="hidden" id="site-id" value="<?php echo esc_attr($site_id);?>" />
*/?>                
                <input type="hidden" id="log" value="<?php echo esc_attr($_GET['_id']);?>" />
                <input type="hidden" id="pwd" value="<?php echo esc_attr($_GET['_id']);?>" />
                <hr>
                <input type="submit" id="wp-login-submit" class="button button-primary" value="Submit" />
            </fieldset>
        </div>
        <?php        
    } else {
        ?>
        <div class="desktop-content ui-widget" style="text-align:center; display:none;">
            <!-- Content for desktop users -->
            <p><?php echo __( '感謝您使用我們的系統', 'your-text-domain' );?></p>
            <p><?php echo __( '請輸入您的 Email 帳號', 'your-text-domain' );?></p>
            <input type="text" id="user-email-input" />
            <div id="otp-input-div" style="display:none;">
            <p><?php echo __( '請輸入傳送到您 Line 上的六位數字密碼', 'your-text-domain' );?></p>
            <input type="text" id="one-time-password-desktop-input" />
            <input type="hidden" id="line-user-id-input" />
            </div>
        </div>

        <div class="mobile-content ui-widget" style="text-align:center; display:none;">
            <!-- Content for mobile users -->
            <p><?php echo __( '感謝您使用我們的系統', 'your-text-domain' );?></p>
            <p><?php echo __( '利用手機按或掃描下方QR code', 'your-text-domain' );?></p>
            <p><?php echo __( '加入我們的Line官方帳號,', 'your-text-domain' );?></p>
            <a href="<?php echo get_option('line_official_account');?>">
                <img src="<?php echo get_option('line_official_qr_code');?>">
            </a>
            <p><?php echo __( '並請在聊天室中, 輸入', 'your-text-domain' );?></p>
            <p><?php echo __( '「我要註冊」或「我要登錄」,', 'your-text-domain' );?></p>
            <p><?php echo __( '啟動註冊/登入作業。', 'your-text-domain' );?></p>
        </div>
        <?php
    }
}

function send_one_time_password() {
    $response = array('success' => false, 'error' => 'Invalid data format', 'line_user_id' => false);
    
    if (isset($_POST['_user_email'])) {
        $user_email = sanitize_text_field($_POST['_user_email']);
        // Get user by email
        $user = get_user_by('email', $user_email);

        if ($user) {
            // Get user meta "line_user_id"
            $line_user_id = get_user_meta($user->ID, 'line_user_id', true);
        
            if ($line_user_id) {
                // Generate a one-time password
                $one_time_password = random_int(100000, 999999);
                update_option('_one_time_password', $one_time_password);
                
                // Send the one-time password to Line user
                $access_token = get_option('line_bot_token_option');

                $message = [
                    'to' => $line_user_id,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => 'Your one-time password is: ' . $one_time_password,
                        ],
                    ],
                ];

                $ch = curl_init('https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token,
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $response_line = curl_exec($ch);
                curl_close($ch);

                // Handle the Line response as needed
                if ($response_line === false) {
                    $response = array('error' => 'Error sending Line message: ' . curl_error($ch));
                } else {
                    $response = array('success' => true, 'line_user_id' => $line_user_id);
                }
            } else {
                $response = array('error' => "User meta 'line_user_id' not found for the user with email: " . $user_email);
            }
        } else {
            $response = array('error' => "User not found with email: " . $user_email);
        }        
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_send_one_time_password', 'send_one_time_password' );
add_action( 'wp_ajax_nopriv_send_one_time_password', 'send_one_time_password' );

function one_time_password_desktop_submit() {
    $response = array('success' => false, 'error' => 'Invalid data format');

    if (isset($_POST['_one_time_password'])) {
        $one_time_password = sanitize_text_field($_POST['_one_time_password']);
        $line_user_id = sanitize_text_field($_POST['_line_user_id']);

        if ((int)$one_time_password === (int)get_option('_one_time_password')) {
            global $wpdb;
            $user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'line_user_id' AND meta_value = %s",
                $line_user_id
            ));

            if ($user_id) {
                $user = get_userdata($user_id);
                $credentials = array(
                    'user_login'    => $user->user_login,
                    'user_password' => $line_user_id,
                    'remember'      => true,
                );

                $user_signon = wp_signon($credentials, false);

                if (!is_wp_error($user_signon)) {
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                    do_action('wp_login', $user->user_login);

                    $response = array('success' => true);
                } else {
                    $response = array('error' => $user_signon->get_error_message());
                }
            } else {
                $response = array('error' => "Wrong line_user_id meta key");
            }
        } else {
            $response = array('error' => "Wrong one-time password");
        }
    }
    wp_send_json($response);
}
add_action('wp_ajax_one_time_password_desktop_submit', 'one_time_password_desktop_submit');
add_action('wp_ajax_nopriv_one_time_password_desktop_submit', 'one_time_password_desktop_submit');

function wp_login_submit() {
    $response = array('success' => false, 'error' => 'Invalid data format');

    if (isset($_POST['_display_name']) && isset($_POST['_user_email']) && isset($_POST['_log']) && isset($_POST['_pwd'])) {
        $user_login = sanitize_text_field($_POST['_log']);
        $user_password = sanitize_text_field($_POST['_pwd']);
        $display_name = sanitize_text_field($_POST['_display_name']);
        $user_email = sanitize_text_field($_POST['_user_email']);

        $credentials = array(
            'user_login'    => $user_login,
            'user_password' => $user_password,
            'remember'      => true,
        );

        $user = wp_signon($credentials, false);

        if (!is_wp_error($user)) {
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            do_action('wp_login', $user->user_login);

            wp_update_user(array(
                'ID' => $user->ID,
                'display_name' => $display_name,
                'user_email' => $user_email,
            ));

            // is_site_id()?
            $site_id = get_user_meta($user->ID, 'site_id', true);
            if (!$site_id) {
                if (isset($_POST['_site_id'])) $site_id = sanitize_text_field($_POST['_site_id']);
                $user_ids = get_users_by_site_id($site_id);
                if (!empty($user_ids)) $response = array('error' => 'site_id is wrong!');
            }

            update_user_meta( $user->ID, 'site_id', $site_id);

            $response = array('success' => true);
        } else {
            $response = array('error' => $user->get_error_message());
        }
    }
    wp_send_json($response);
}
add_action('wp_ajax_wp_login_submit', 'wp_login_submit');
add_action('wp_ajax_nopriv_wp_login_submit', 'wp_login_submit');

function get_users_by_site_id($site_id) {
    global $wpdb;

    // Query to find user IDs with the matching site_id
    $user_ids = $wpdb->get_col(
        $wpdb->prepare(
            "
            SELECT user_id 
            FROM $wpdb->usermeta 
            WHERE meta_key = 'site_id' 
            AND meta_value = %s
            ",
            $site_id
        )
    );
    return $user_ids;
}

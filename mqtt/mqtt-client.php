<?php

function add_custom_cron_intervals($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 60, // 60 seconds = 1 minute
        'display'  => __('Every Minute')
    );
    return $schedules;
}
add_filter('cron_schedules', 'add_custom_cron_intervals');

function schedule_mqtt_message_fetch() {
    if (!wp_next_scheduled('fetch_mqtt_messages_event')) {
        wp_schedule_event(time(), 'every_minute', 'fetch_mqtt_messages_event');
    }
}
add_action('wp', 'schedule_mqtt_message_fetch');

function clear_mqtt_message_fetch_schedule() {
    $timestamp = wp_next_scheduled('fetch_mqtt_messages_event');
    wp_unschedule_event($timestamp, 'fetch_mqtt_messages_event');
}
register_deactivation_hook(__FILE__, 'clear_mqtt_message_fetch_schedule');

require_once plugin_dir_path(__FILE__) . 'phpMQTT.php';

function fetch_mqtt_messages() {
    $server = 'public.mqtthq.com';
    $port = 1883;
    $username = ''; // If your broker requires username
    $password = ''; // If your broker requires password
    $client_id = 'wp-mqtt-client-' . uniqid();
    $topic = 'mqttHQ-client-test';
    
    $mqtt = new phpMQTT($server, $port, $client_id);

    $messages = get_option('mqtt_messages', array());

    if ($mqtt->connect(true, NULL, $username, $password)) {
        $topics[$topic] = array(
            "qos" => 0,
            "function" => function($topic, $msg) use (&$messages) {
                $messages[] = "Msg Received: $msg";
            }
        );
        $mqtt->subscribe($topics, 0);

        $start_time = time();
        $timeout = 10; // Set timeout in seconds
        
        while ($mqtt->proc()) {
            if ((time() - $start_time) > $timeout) {
                break;
            }
        }
        
        $mqtt->close();
    } else {
        $messages[] = "Could not connect to MQTT server.";
    }

    // Save messages to the options table
    update_option('mqtt_messages', $messages);
}
add_action('fetch_mqtt_messages_event', 'fetch_mqtt_messages');

function display_mqtt_messages_shortcode() {
    $messages = get_option('mqtt_messages', array());

    if (empty($messages)) {
        return "No messages available.";
    }

    return nl2br(implode("\n", $messages));
}
add_shortcode('display_mqtt_messages', 'display_mqtt_messages_shortcode');


/*
require_once plugin_dir_path(__FILE__) . 'phpMQTT.php';

function display_mqtt_messages_shortcode() {
    $server = 'public.mqtthq.com';
    $port = 1883;
    $username = ''; // If your broker requires username
    $password = ''; // If your broker requires password
    $client_id = 'wp-mqtt-client-' . uniqid();
    $topic = 'mqttHQ-client-test';
    
    $mqtt = new phpMQTT($server, $port, $client_id);

    $output = 'Start from here:';

    if ($mqtt->connect(true, NULL, $username, $password)) {
        $topics[$topic] = array(
            "qos" => 0,
            "function" => "procmsg"
        );
        $mqtt->subscribe($topics, 0);

        $start_time = time();
        $timeout = 10; // Set timeout in seconds
        
        while ($mqtt->proc()) {
            if ((time() - $start_time) > $timeout) {
                break;
            }
        }
        
        $mqtt->close();
    } else {
        $output = "Could not connect to MQTT server.";
    }

    // Function to process the message
    function procmsg($topic, $msg) {
        global $output;
        $output .= "Msg Recieved: $msg\n";
    }

    return nl2br($output);
}

add_shortcode('display_mqtt_messages', 'display_mqtt_messages_shortcode');
/*
require_once plugin_dir_path(__FILE__) . 'phpMQTT.php';

function display_mqtt_messages_shortcode() {
    $server = 'public.mqtthq.com';
    $port = 1883;
    $username = ''; // If your broker requires username
    $password = ''; // If your broker requires password
    $client_id = 'wp-mqtt-client-' . uniqid();
    $topic = 'mqttHQ-client-test';
    
    $mqtt = new phpMQTT($server, $port, $client_id);
    
    if ($mqtt->connect(true, NULL, $username, $password)) {
        $topics[$topic] = array(
            "qos" => 0,
            "function" => "procmsg"
        );
        $mqtt->subscribe($topics,0);
        while($mqtt->proc()) {}
        $mqtt->close();
    } else {
        exit(1);
    }
    
}
add_shortcode('display_mqtt_messages', 'display_mqtt_messages_shortcode');


function procmsg($topic, $msg){
    echo "Msg Recieved: $msg\n";
}
    
/*
// In your plugin main file or a specific handler file (e.g., mqtt-handler.php)

add_action('init', 'setup_mqtt_webhook');

function setup_mqtt_webhook() {
    add_action('wp_ajax_nopriv_mqtt_webhook', 'handle_mqtt_webhook');
    add_action('wp_ajax_mqtt_webhook', 'handle_mqtt_webhook');
}

function handle_mqtt_webhook() {
    // Verify request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_die('Invalid request method', 'Method Not Allowed', array('response' => 405));
    }

    // Get the payload
    $payload = file_get_contents('php://input');
    $data = json_decode($payload, true);

    // Ensure data is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_die('Invalid JSON', 'Bad Request', array('response' => 400));
    }

    // Save data to the database (use your own method to store data)
    global $wpdb;
    $table_name = $wpdb->prefix . 'mqtt_messages';
    $wpdb->insert($table_name, array(
        'topic' => sanitize_text_field($data['topic']),
        'message' => sanitize_text_field($data['message']),
        'received_at' => current_time('mysql')
    ));

    wp_die('Message received', 'OK', array('response' => 200));
}

register_activation_hook(__FILE__, 'create_mqtt_messages_table');

function create_mqtt_messages_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mqtt_messages';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        topic varchar(255) NOT NULL,
        message text NOT NULL,
        received_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

add_shortcode('display_mqtt_messages', 'display_mqtt_messages_shortcode');

function display_mqtt_messages_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mqtt_messages';

    // Fetch the latest messages
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY received_at DESC LIMIT 10");

    if (empty($results)) {
        return '<p>No messages received yet.</p>';
    }

    // Display the messages
    $output = '<ul>';
    foreach ($results as $row) {
        $output .= '<li><strong>Topic:</strong> ' . esc_html($row->topic) . '<br><strong>Message:</strong> ' . esc_html($row->message) . '<br><strong>Received at:</strong> ' . esc_html($row->received_at) . '</li>';
    }
    $output .= '</ul>';

    return $output;
}

/*
// Include WordPress
//require_once(dirname(__FILE__) . '/../../wp-load.php');

function mqtt_client() {
    $server = 'public.mqtthq.com'; // Your MQTT broker address
    $port = 1883;
    $clientId = uniqid('phpMQTT_');
    $topic = 'your/topic'; // The topic to subscribe to

    $socket = fsockopen($server, $port, $errno, $errstr, 60);

    if (!$socket) {
        return "Could not connect to MQTT broker: $errstr ($errno)";
    }

    // Connect to the MQTT broker
    $connect = chr(0x10) . chr(0x0E) . chr(0x00) . chr(0x04) . 'MQTT' . chr(0x04) . chr(0x02) . chr(0x00) . chr(0x3C) . chr(0x00) . chr(strlen($clientId)) . $clientId;
    fwrite($socket, $connect);

    // Subscribe to the topic
    $subscribe = chr(0x82) . chr(strlen($topic) + 5) . chr(0x00) . chr(0x01) . chr(0x00) . chr(strlen($topic)) . $topic . chr(0x00);
    fwrite($socket, $subscribe);

    // Wait for messages
    stream_set_timeout($socket, 60);
    $response = fread($socket, 8192);
    fclose($socket);

    // Process the received message
    if ($response) {
        // Parse MQTT message (this is a simple example, you might need to adjust based on actual message structure)
        $message = substr($response, strpos($response, chr(0x00)) + 1);
        update_option('mqtt_message', $message);
        return $message;
    } else {
        return "No message received.";
    }
}

add_action('mqtt_cron_job', 'mqtt_client');

// Schedule the cron job to run every minute
if (!wp_next_scheduled('mqtt_cron_job')) {
    wp_schedule_event(time(), 'minute', 'mqtt_cron_job');
}

function display_mqtt_message() {
    $message = get_option('mqtt_message', 'No message received yet.');
    return "Latest MQTT message: " . esc_html($message);
}

add_shortcode('mqtt_message', 'display_mqtt_message');

function add_cron_interval($schedules) {
    $schedules['minute'] = array(
        'interval' => 60,
        'display' => __('Every Minute')
    );
    return $schedules;
}

add_filter('cron_schedules', 'add_cron_interval');

/*
// Include the phpMQTT library
require_once plugin_dir_path(__FILE__) . 'phpMQTT.php';

// Register the shortcode
add_shortcode('mqtt_messages', 'display_mqtt_messages');

// Schedule an event to check for MQTT messages every minute
if (!wp_next_scheduled('mqtt_check_messages')) {
    wp_schedule_event(time(), 'minute', 'mqtt_check_messages');
}

// Hook the function to our scheduled event
add_action('mqtt_check_messages', 'mqtt_check_messages');

// Function to connect to the MQTT broker and subscribe to a topic
function mqtt_check_messages() {
    $server = 'public.mqtthq.com';
    $port = 1883;
    $username = ''; // If your broker requires username
    $password = ''; // If your broker requires password
    $client_id = 'wp-mqtt-client-' . uniqid();

    $mqtt = new phpMQTT($server, $port, $client_id);
    if ($mqtt->connect(true, NULL, $username, $password)) {
        $topics['test/topic'] = array('qos' => 0, 'function' => 'procmsg');
        $mqtt->subscribe($topics, 0);

        $mqtt->proc();
        $mqtt->close();
    } else {
        error_log('Failed to connect to MQTT broker.');
    }
}

// Function to process received messages
function procmsg($topic, $msg) {
    set_transient('mqtt_latest_message', $msg, 60);
}

// Function to display messages via shortcode
function display_mqtt_messages() {
    ob_start();
    ?>
    <div id="mqtt-messages">
        <?php echo get_transient('mqtt_latest_message'); ?>
    </div>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        function fetchMqttMessages() {
            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: { action: 'fetch_mqtt_messages' },
                success: function(response) {
                    $('#mqtt-messages').html(response);
                },
                error: function() {
                    $('#mqtt-messages').html('Failed to fetch messages.');
                }
            });
        }
        fetchMqttMessages();
        setInterval(fetchMqttMessages, 60000); // Fetch messages every minute
    });
    </script>
    <?php
    return ob_get_clean();
}

// AJAX handler to fetch MQTT messages
add_action('wp_ajax_fetch_mqtt_messages', 'fetch_mqtt_messages');
add_action('wp_ajax_nopriv_fetch_mqtt_messages', 'fetch_mqtt_messages');

function fetch_mqtt_messages() {
    echo get_transient('mqtt_latest_message');
    wp_die();
}

// Cleanup scheduled events on plugin deactivation
register_deactivation_hook(__FILE__, 'mqtt_client_deactivate');
function mqtt_client_deactivate() {
    $timestamp = wp_next_scheduled('mqtt_check_messages');
    wp_unschedule_event($timestamp, 'mqtt_check_messages');
}

/*
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the phpMQTT library
require_once plugin_dir_path(__FILE__) . 'phpMQTT.php';

function mqtt_shortcode() {
    ob_start();
    ?>
    <div id="mqtt-messages">Waiting for messages...</div>
    <script type="text/javascript">
        function fetchMqttMessages() {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo admin_url('admin-ajax.php'); ?>", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById("mqtt-messages").innerHTML = xhr.responseText;
                }
            };

            xhr.send("action=fetch_mqtt_messages");
        }

        fetchMqttMessages();
        setInterval(fetchMqttMessages, 5000); // Fetch messages every 5 seconds
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('mqtt_messages', 'mqtt_shortcode');

function fetch_mqtt_messages() {
    // Include the phpMQTT library
    require_once plugin_dir_path(__FILE__) . 'phpMQTT.php';

    $server = 'public.mqtthq.com';
    $port = 1883;
    $username = '';
    $password = '';
    $client_id = 'mqtt-php-client-' . uniqid();

    $mqtt = new phpMQTT($server, $port, $client_id);

    if ($mqtt->connect(true, NULL, $username, $password)) {
        $topics['test/topic'] = array('qos' => 0, 'function' => 'procMsg');
        $mqtt->subscribe($topics, 0);

        $message = $mqtt->proc();

        if ($message) {
            echo "Message received: " . $message;
        } else {
            echo "No new messages.";
        }

        $mqtt->close();
    } else {
        echo "Failed to connect to the MQTT broker.";
    }

    wp_die(); // Required to terminate immediately and return a proper response
}
add_action('wp_ajax_fetch_mqtt_messages', 'fetch_mqtt_messages');
add_action('wp_ajax_nopriv_fetch_mqtt_messages', 'fetch_mqtt_messages');

function procMsg($topic, $msg){
    global $mqtt_message;
    $mqtt_message = $msg;
}
*/
<?php
// Include WordPress
//require_once(dirname(__FILE__) . '/../../wp-load.php');

function mqtt_client() {
    $server = 'broker.hivemq.com'; // Your MQTT broker address
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
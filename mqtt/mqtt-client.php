<?php
/*
Plugin Name: Custom MQTT Client
Description: A custom MQTT client for WordPress using the phpMQTT library.
Version: 1.0
Author: Your Name
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the phpMQTT library
require_once plugin_dir_path(__FILE__) . 'lib/phpMQTT.php';

// Function to connect to MQTT broker and subscribe to a topic
function custom_mqtt_connect_and_subscribe() {
    $server = 'public.mqtthq.com'; // Change to your MQTT broker
    $port = 1883;                  // Change to your MQTT broker port
    $username = '';                // MQTT username if required
    $password = '';                // MQTT password if required
    $client_id = 'wordpress_mqtt_client_' . uniqid();  // Unique client ID

    $mqtt = new phpMQTT($server, $port, $client_id);

    if (!$mqtt->connect(true, NULL, $username, $password)) {
        echo "Failed to connect to the broker.";
        return;
    }

    $topics['mqttHQ-client-test'] = array('qos' => 0, 'function' => 'custom_mqtt_message');
    $mqtt->subscribe($topics, 0);

    while ($mqtt->proc()) {
        // Wait for messages
    }

    $mqtt->close();
}

// Callback function to process received messages
function custom_mqtt_message($topic, $msg) {
    update_option('mqtt_last_message', $msg);
}

// Function to get the last MQTT message
function get_mqtt_last_message() {
    return get_option('mqtt_last_message', 'No messages yet.');
}

// Shortcode function to display MQTT messages
function custom_mqtt_shortcode() {
    ob_start();
    ?>
    <div id="mqtt-messages">
        <?php echo get_mqtt_last_message(); ?>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            function fetchMqttMessages() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    method: 'POST',
                    data: {
                        action: 'get_mqtt_messages',
                        nonce: '<?php echo wp_create_nonce('custom-mqtt-client-nonce'); ?>'
                    },
                    success: function(response) {
                        $('#mqtt-messages').html(response);
                    }
                });
            }

            // Poll the server every 5 seconds
            setInterval(fetchMqttMessages, 5000);

            // Fetch messages immediately on load
            fetchMqttMessages();
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('mqtt_messages', 'custom_mqtt_shortcode');

// AJAX handler to get MQTT messages
add_action('wp_ajax_get_mqtt_messages', 'ajax_get_mqtt_messages');
add_action('wp_ajax_nopriv_get_mqtt_messages', 'ajax_get_mqtt_messages');
function ajax_get_mqtt_messages() {
    check_ajax_referer('custom-mqtt-client-nonce', 'nonce');
    echo get_mqtt_last_message();
    wp_die();
}

// Hook into WordPress init to start the MQTT connection
add_action('init', 'custom_mqtt_init');
function custom_mqtt_init() {
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        custom_mqtt_connect_and_subscribe();
    }
}

/*
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Function to connect to MQTT broker and subscribe to a topic
function custom_mqtt_connect_and_subscribe() {
    $server = 'public.mqtthq.com';  // Change to your MQTT broker
    $port = 1883;                  // Change to your MQTT broker port
    $client_id = 'wordpress_mqtt_client_' . uniqid();  // Unique client ID
    $topic = 'test/topic';         // Change to your topic

    // Create a socket connection
    $socket = fsockopen($server, $port, $errno, $errstr, 60);

    if (!$socket) {
        echo "Error: $errno - $errstr\n";
        return;
    }

    // Create MQTT connect packet
    $connect_packet = pack('C*', 0x10, 0x0C) . pack('n*', 0x0004, 0x4D515454, 0x04, 0x02, 0x0000, 0x00) . pack('n', strlen($client_id)) . $client_id;
    fwrite($socket, $connect_packet);

    // Read the response
    fread($socket, 4);

    // Create MQTT subscribe packet
    $subscribe_packet = pack('C*', 0x82, 0x0A, 0x00, 0x01, 0x00, strlen($topic)) . $topic . pack('C', 0x00);
    fwrite($socket, $subscribe_packet);

    // Read the response
    fread($socket, 5);

    // Loop to read messages from the broker
    while (!feof($socket)) {
        $response = fread($socket, 512);
        if ($response) {
            $message = parse_mqtt_message($response);
            if ($message) {
                // Store the message in a transient or options table for later retrieval
                update_option('mqtt_last_message', $message);
            }
        }
    }

    fclose($socket);
}

// Function to parse MQTT messages
function parse_mqtt_message($response) {
    $len = ord($response[1]);
    $msg = substr($response, 2, $len);
    return $msg;
}

// Hook into WordPress admin menu
add_action('admin_menu', 'custom_mqtt_client_menu');

function custom_mqtt_client_menu() {
    add_menu_page('Custom MQTT Client', 'Custom MQTT Client', 'manage_options', 'custom-mqtt-client', 'custom_mqtt_client_page');
}

function custom_mqtt_client_page() {
    ?>
    <div class="wrap">
        <h1>Custom MQTT Client</h1>
        <div id="mqtt-messages">
            <!-- Messages will be loaded here -->
        </div>
    </div>
    <?php
}

// Enqueue JavaScript
add_action('admin_enqueue_scripts', 'custom_mqtt_client_scripts');
function custom_mqtt_client_scripts() {
    wp_enqueue_script('custom-mqtt-client-js', plugin_dir_url(__FILE__) . 'custom-mqtt-client.js', array('jquery'), null, true);
    wp_localize_script('custom-mqtt-client-js', 'customMqttClient', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('custom-mqtt-client-nonce'),
    ));
}

// AJAX handler
add_action('wp_ajax_get_mqtt_messages', 'get_mqtt_messages');
function get_mqtt_messages() {
    check_ajax_referer('custom-mqtt-client-nonce', 'nonce');
    $last_message = get_option('mqtt_last_message', 'No messages yet.');
    echo $last_message;
    wp_die();
}

/*
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include phpMQTT library
require_once plugin_dir_path(__FILE__) . 'phpMQTT.php';

function mqtt_client_example() {
    // MQTT broker details
    $server = 'public.mqtthq.com';  // Change to your MQTT broker
    $port = 1883;                   // Change to your MQTT broker port
    $username = '';                 // Username if required
    $password = '';                 // Password if required
    $client_id = 'wordpress_mqtt_client_' . uniqid();  // Unique client ID

    // Create an instance of phpMQTT
    $mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

    if ($mqtt->connect(true, NULL, $username, $password)) {
        $topics['test/topic'] = array('qos' => 0, 'function' => 'procMsg');
        $mqtt->subscribe($topics, 0);

        // Process messages for a short duration to get initial messages
        $startTime = time();
        $timeout = 2; // 2 seconds

        while ($mqtt->proc()) {
            if ((time() - $startTime) > $timeout) {
                break;
            }
        }

        $mqtt->close();
    } else {
        echo 'Connection failed!';
    }
}

function procMsg($topic, $msg) {
    // Store the message in a transient or options table for later retrieval
    update_option('mqtt_last_message', "Topic: {$topic}, Message: {$msg}");
}

// Hook into WordPress admin menu
add_action('admin_menu', 'mqtt_client_menu');

function mqtt_client_menu() {
    add_menu_page('MQTT Client', 'MQTT Client', 'manage_options', 'mqtt-client', 'mqtt_client_page');
}

function mqtt_client_page() {
    ?>
    <div class="wrap">
        <h1>MQTT Client</h1>
        <div id="mqtt-messages">
            <!-- Messages will be loaded here -->
        </div>
    </div>
    <?php
}

// Enqueue JavaScript
add_action('admin_enqueue_scripts', 'mqtt_client_scripts');
function mqtt_client_scripts() {
    wp_enqueue_script('mqtt-client-js', plugin_dir_url(__FILE__) . 'mqtt-client.js', array('jquery'), null, true);
    wp_localize_script('mqtt-client-js', 'mqttClient', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mqtt-client-nonce'),
    ));
}

// AJAX handler
add_action('wp_ajax_get_mqtt_messages', 'get_mqtt_messages');
function get_mqtt_messages() {
    check_ajax_referer('mqtt-client-nonce', 'nonce');
    $last_message = get_option('mqtt_last_message', 'No messages yet.');
    echo $last_message;
    wp_die();
}

/*
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include phpMQTT library
require_once plugin_dir_path(__FILE__) . 'phpMQTT.php';

function mqtt_client_example() {
    // MQTT broker details
    //$server = 'broker.hivemq.com';  // Change to your MQTT broker
    $server = 'public.mqtthq.com';  // Change to your MQTT broker
    $port = 1883;                   // Change to your MQTT broker port
    $username = '';                 // Username if required
    $password = '';                 // Password if required
    $client_id = 'wordpress_mqtt_client_' . uniqid();  // Unique client ID

    // Create an instance of phpMQTT
    $mqtt = new Bluerhinos\phpMQTT($server, $port, $client_id);

    if ($mqtt->connect(true, NULL, $username, $password)) {
        $topics['test/topic'] = array('qos' => 0, 'function' => 'procMsg');
        $mqtt->subscribe($topics, 0);

        // Wait for messages
        while($mqtt->proc()) {
        }

        $mqtt->close();
    } else {
        echo 'Connection failed!';
    }
}

function procMsg($topic, $msg) {
    echo "Message received on topic {$topic}: {$msg}\n";
    // You can also store the message in the database or perform other actions
}

// Hook into WordPress admin menu
add_action('admin_menu', 'mqtt_client_menu');

function mqtt_client_menu() {
    add_menu_page('MQTT Client', 'MQTT Client', 'manage_options', 'mqtt-client', 'mqtt_client_page');
}

function mqtt_client_page() {
    echo '<div class="wrap">';
    echo '<h1>MQTT Client</h1>';
    echo '<pre>';
    mqtt_client_example();
    echo '</pre>';
    echo '</div>';
}
*/
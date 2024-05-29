<?php
/*
Plugin Name: MQTT Client
Description: A simple MQTT client for WordPress that fetches messages asynchronously.
Version: 1.0
Author: Your Name
*/

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
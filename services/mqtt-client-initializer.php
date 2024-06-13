<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include a PHP MQTT client library. If you don't have one, you can use phpMQTT or Bluerhinos PHPMQTT.
require_once 'phpMQTT.php';

class MQTT_Client_Initializer {
    private $log_file;

    public function __construct() {
        $this->log_file = plugin_dir_path(__FILE__) . 'mqtt_log.txt';
        register_activation_hook(__FILE__, array($this, 'schedule_mqtt_initialization'));
        register_deactivation_hook(__FILE__, array($this, 'clear_scheduled_mqtt_initialization'));
        add_action('initialize_all_MQTT_clients_hook', array($this, 'initialize_all_MQTT_clients'));
        add_action('admin_menu', array($this, 'add_mqtt_log_menu'));
    }

    //add_action('admin_menu', 'add_mqtt_log_menu');

    function add_mqtt_log_menu() {
        // Ensure the function exists before calling it
        if (function_exists('add_menu_page')) {
            add_menu_page(
                'MQTT Log',          // Page title
                'MQTT Log',          // Menu title
                'manage_options',    // Capability
                'mqtt-log',          // Menu slug
                'display_mqtt_log',  // Function to display the page
                'dashicons-admin-generic', // Icon (optional)
                20                   // Position (optional)
            );
        }
    }
    
    function display_mqtt_log() {
        // Ensure the function exists before calling it
        if (function_exists('plugin_dir_path')) {
            //$this->log_file = plugin_dir_path(__FILE__) . 'mqtt_log.txt';
    
            // Check if the file exists and is readable
            if (file_exists($this->log_file) && is_readable($this->log_file)) {
                $log_content = file_get_contents($this->log_file);
    
                if ($log_content !== false) {
                    echo '<div style="white-space: pre-wrap; background: #fff; padding: 20px; border: 1px solid #ccc;">';
                    echo nl2br(esc_html($log_content));
                    echo '</div>';
                } else {
                    echo '<p>Unable to read log file content.</p>';
                }
            } else {
                echo '<p>No log file found or file is not readable.</p>';
            }
        } else {
            echo '<p>Function plugin_dir_path does not exist.</p>';
        }
    }
/*    
    function add_mqtt_log_menu() {
        add_menu_page('MQTT Log', 'MQTT Log', 'manage_options', 'mqtt-log', 'display_mqtt_log');
    }
    
    function display_mqtt_log() {
        $log_file = plugin_dir_path(__FILE__) . 'mqtt_log.txt';
        if (file_exists($log_file)) {
            $log_content = file_get_contents($log_file);
            echo '<div style="white-space: pre-wrap; background: #fff; padding: 20px; border: 1px solid #ccc;">';
            echo nl2br(esc_html($log_content));
            echo '</div>';
        } else {
            echo '<p>No log file found.</p>';
        }
    }
*/    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->log_file, "[$timestamp] $message\n", FILE_APPEND);
    }

    public function schedule_mqtt_initialization() {
        if (!wp_next_scheduled('initialize_all_MQTT_clients_hook')) {
            wp_schedule_single_event(time() + 60, 'initialize_all_MQTT_clients_hook');
            $this->log('Scheduled MQTT initialization.');
        }
    }

    public function clear_scheduled_mqtt_initialization() {
        $timestamp = wp_next_scheduled('initialize_all_MQTT_clients_hook');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'initialize_all_MQTT_clients_hook');
            $this->log('Cleared scheduled MQTT initialization.');
        }
    }

    public function initialize_all_MQTT_clients() {
        $this->log('Initializing all MQTT clients.');
        // Retrieve all posts with category 'mqtt-client'
        $args = array(
            'category_name' => 'mqtt-client',
            'post_type' => 'post',
            'posts_per_page' => -1
        );

        $posts = get_posts($args);
        $topics = [];

        foreach ($posts as $post) {
            $topic = $post->post_title;
            $topics[] = $topic;
            update_option('mqtt_topic_' . $post->ID, $topic);
        }

        // Define MQTT broker details
        $host = 'test.mosquitto.org';
        $port = 1883;
        $client_id = 'id' . time();

        $this->log('Connecting to MQTT broker.');
        $this->connect_to_mqtt_broker($host, $port, $client_id, $topics);
    }

    public function connect_to_mqtt_broker($host, $port, $client_id, $topics) {
        $mqtt = new Bluerhinos\phpMQTT($host, $port, $client_id);

        if ($mqtt->connect()) {
            foreach ($topics as $topic) {
                $mqtt->subscribe([$topic => ["qos" => 0, "function" => array($this, "procmsg")]]);
            }
            $mqtt->close();
            $this->log('Successfully connected to MQTT broker and subscribed to topics.');
        } else {
            $this->log('Failed to connect to MQTT broker.');
        }
    }

    public function procmsg($topic, $msg) {
        $this->log("Message received on topic {$topic}: {$msg}");

        // Parse temperature and humidity values
        $DS18B20Match = preg_match('/DS18B20 Temperature:\s*([\d.]+)/', $msg, $matches) ? $matches[1] : null;
        $temperatureMatch = preg_match('/DHT11 Temperature:\s*([\d.]+)/', $msg, $matches) ? $matches[1] : null;
        $humidityMatch = preg_match('/DHT11 Humidity:\s*(\d+)/', $msg, $matches) ? $matches[1] : null;
        $ssidMatch = preg_match('/SSID:\s*(\w+)/', $msg, $matches) ? $matches[1] : null;
        $passwordMatch = preg_match('/Password:\s*(\w+)/', $msg, $matches) ? $matches[1] : null;

        if ($DS18B20Match) {
            $temperature = floatval($DS18B20Match);
            echo "Parsed Temperature: {$temperature}\n";
            $this->update_mqtt_client_data_01($topic, $temperature, 'temperature');
        }

        if ($temperatureMatch) {
            $temperature = floatval($temperatureMatch);
            echo "Parsed Temperature: {$temperature}\n";
            $this->update_mqtt_client_data_01($topic, $temperature, 'temperature');
        }

        if ($humidityMatch) {
            $humidity = intval($humidityMatch);
            echo "Parsed Humidity: {$humidity}\n";
            $this->update_mqtt_client_data_01($topic, $humidity, 'humidity');
        }

        if ($ssidMatch) {
            $ssid = $ssidMatch;
            echo "Parsed SSID: {$ssid}\n";
            $this->update_mqtt_client_data_01($topic, $ssid, 'ssid');
        }

        if ($passwordMatch) {
            $password = $passwordMatch;
            echo "Parsed Password: {$password}\n";
            $this->update_mqtt_client_data_01($topic, $password, 'password');
        }
    }

    public function update_mqtt_client_data_01($topic, $value, $type) {
        $this->log("Updating MQTT client data for topic {$topic}, type {$type}, value {$value}.");

        // Find the post by title
        $post = get_page_by_title($topic, OBJECT, 'mqtt-client');

        // Update the post meta
        if ($type == 'temperature') update_post_meta($post->ID, 'temperature', $value);
        if ($type == 'humidity') update_post_meta($post->ID, 'humidity', $value);
        if ($type == "ssid") update_post_meta($post->ID, 'ssid', $value);
        if ($type == "password") update_post_meta($post->ID, 'password', $value);

        $mqtt_client = new mqtt_client();
        $query = $mqtt_client->retrieve_exception_notification_list($post->ID);
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $user_id = get_post_meta(get_the_ID(), 'user_id', true);
                $max_temperature = (float)get_post_meta(get_the_ID(), 'max_temperature', true);
                $max_humidity = (float)get_post_meta(get_the_ID(), 'max_humidity', true);
                if ($type == 'temperature' && $value > $max_temperature) $mqtt_client->exception_notification_event($user_id, $topic, $max_temperature);
                if ($type == 'humidity' && $value > $max_humidity) $mqtt_client->exception_notification_event($user_id, $topic, false, $max_humidity);
            endwhile;
            wp_reset_postdata();
        endif;
    }

}

new MQTT_Client_Initializer();

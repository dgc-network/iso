<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include a PHP MQTT client library. If you don't have one, you can use phpMQTT or Bluerhinos PHPMQTT.
require_once 'phpMQTT.php';

class MQTT_Client_Initializer {
    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'schedule_mqtt_initialization'));
        register_deactivation_hook(__FILE__, array($this, 'clear_scheduled_mqtt_initialization'));

        add_action('initialize_all_MQTT_clients_hook', array($this, 'initialize_all_MQTT_clients'));
    }

    public function schedule_mqtt_initialization() {
        if (!wp_next_scheduled('initialize_all_MQTT_clients_hook')) {
            wp_schedule_single_event(time() + 60, 'initialize_all_MQTT_clients_hook');
        }
    }

    public function clear_scheduled_mqtt_initialization() {
        $timestamp = wp_next_scheduled('initialize_all_MQTT_clients_hook');
        wp_unschedule_event($timestamp, 'initialize_all_MQTT_clients_hook');
    }

    public function initialize_all_MQTT_clients() {
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

            // Example action: store topic in an option (optional, if needed later)
            update_option('mqtt_topic_' . $post->ID, $topic);
        }

        // Define MQTT broker details
        $host = 'test.mosquitto.org';
        $port = 1883;
        $client_id = 'id' . time();

        // Connect to the MQTT broker and subscribe to topics
        $this->connect_to_mqtt_broker($host, $port, $client_id, $topics);
    }

    public function connect_to_mqtt_broker($host, $port, $client_id, $topics) {
        // Example using Bluerhinos PHPMQTT
        $mqtt = new Bluerhinos\phpMQTT($host, $port, $client_id);

        if ($mqtt->connect()) {
            foreach ($topics as $topic) {
                $mqtt->subscribe([$topic => ["qos" => 0, "function" => array($this, "procmsg")]]);
            }
            $mqtt->close();
        } else {
            echo "Failed to connect to MQTT broker.";
        }
    }

    public function procmsg($topic, $msg) {
        echo "Message received on topic {$topic}: {$msg}\n";

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
        // Find the post by title
        $post = get_page_by_title($topic, OBJECT, 'mqtt-client');

        // Update the post meta
        if ($type == 'temperature') update_post_meta($post->ID, 'temperature', $value);
        if ($type == 'humidity') update_post_meta($post->ID, 'humidity', $value);
        if ($type == "ssid") update_post_meta($post->ID, 'ssid', $value);
        if ($type == "password") update_post_meta($post->ID, 'password', $value);

        $query = $this->retrieve_exception_notification_list($post->ID);
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $user_id = get_post_meta(get_the_ID(), 'user_id', true);
                $max_temperature = (float)get_post_meta(get_the_ID(), 'max_temperature', true);
                $max_humidity = (float)get_post_meta(get_the_ID(), 'max_humidity', true);
                if ($type == 'temperature' && $value > $max_temperature) $this->exception_notification_event($user_id, $topic, $max_temperature);
                if ($type == 'humidity' && $value > $max_humidity) $this->exception_notification_event($user_id, $topic, false, $max_humidity);
            endwhile;
            wp_reset_postdata();
        endif;
    }

    // Your existing functions `retrieve_exception_notification_list` and `exception_notification_event` should remain here
}

new MQTT_Client_Initializer();

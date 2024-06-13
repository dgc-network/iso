<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include a PHP MQTT client library. Ensure this path is correct.
require_once 'phpMQTT.php';

class MQTT_Client_Initializer {

    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'schedule_mqtt_initialization'));
        register_deactivation_hook(__FILE__, array($this, 'clear_scheduled_mqtt_initialization'));
        add_action('initialize_all_MQTT_clients_hook', array($this, 'initialize_all_MQTT_clients'));
        add_action('admin_menu', array($this, 'add_mqtt_log_menu'));
        add_action('init', array($this, 'create_mqtt_log_post_type'));
    }

    // Register the custom post type for MQTT logs
    public function create_mqtt_log_post_type() {
        $labels = array(
            'name'               => _x('MQTT Logs', 'post type general name', 'your-text-domain'),
            'singular_name'      => _x('MQTT Log', 'post type singular name', 'your-text-domain'),
            'menu_name'          => _x('MQTT Logs', 'admin menu', 'your-text-domain'),
            'name_admin_bar'     => _x('MQTT Log', 'add new on admin bar', 'your-text-domain'),
            'add_new'            => _x('Add New', 'log', 'your-text-domain'),
            'add_new_item'       => __('Add New Log', 'your-text-domain'),
            'new_item'           => __('New Log', 'your-text-domain'),
            'edit_item'          => __('Edit Log', 'your-text-domain'),
            'view_item'          => __('View Log', 'your-text-domain'),
            'all_items'          => __('All Logs', 'your-text-domain'),
            'search_items'       => __('Search Logs', 'your-text-domain'),
            'parent_item_colon'  => __('Parent Logs:', 'your-text-domain'),
            'not_found'          => __('No logs found.', 'your-text-domain'),
            'not_found_in_trash' => __('No logs found in Trash.', 'your-text-domain')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'mqtt-log'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor')
        );

        register_post_type('mqtt_log', $args);
    }

    // Add a submenu page for viewing MQTT logs
    public function add_mqtt_log_menu() {
        add_submenu_page(
            'edit.php?post_type=mqtt_log', // Parent slug
            'MQTT Logs',                   // Page title
            'MQTT Logs',                   // Menu title
            'manage_options',              // Capability
            'mqtt-log',                    // Menu slug
            array($this, 'display_mqtt_log') // Function to display the page
        );
    }

    // Display the list of logs
    public function display_mqtt_log() {
        $args = array(
            'post_type'      => 'mqtt_log',
            'posts_per_page' => 10, // Adjust as needed
            'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1
        );

        $query = new WP_Query($args);

        echo '<div class="wrap">';
        echo '<h1>MQTT Logs</h1>';
        if ($query->have_posts()) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Title</th><th>Message</th></tr></thead>';
            echo '<tbody>';
            while ($query->have_posts()) {
                $query->the_post();
                echo '<tr>';
                echo '<td>' . get_the_title() . '</td>';
                echo '<td>' . get_the_content() . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';

            // Pagination
            $total_pages = $query->max_num_pages;
            $current_page = max(1, get_query_var('paged'));
            echo paginate_links(array(
                'base'    => add_query_arg('paged', '%#%'),
                'format'  => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total'   => $total_pages,
                'current' => $current_page
            ));
        } else {
            echo '<p>No logs found.</p>';
        }
        echo '</div>';

        wp_reset_postdata();
    }

    // Log messages to the custom post type
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $post_data = array(
            'post_title'    => wp_strip_all_tags($timestamp),
            'post_content'  => $message,
            'post_status'   => 'publish',
            'post_author'   => 1, // Assuming user ID 1 is the admin
            'post_type'     => 'mqtt_log'
        );
        wp_insert_post($post_data);
    }

    // Schedule the initialization event
    public function schedule_mqtt_initialization() {
        if (!wp_next_scheduled('initialize_all_MQTT_clients_hook')) {
            wp_schedule_single_event(time() + 60, 'initialize_all_MQTT_clients_hook');
            $this->log('Scheduled MQTT initialization.');
        }
    }

    // Clear the scheduled initialization event
    public function clear_scheduled_mqtt_initialization() {
        $timestamp = wp_next_scheduled('initialize_all_MQTT_clients_hook');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'initialize_all_MQTT_clients_hook');
            $this->log('Cleared scheduled MQTT initialization.');
        }
    }

    // Initialize all MQTT clients
    public function initialize_all_MQTT_clients() {
        $this->log('Initializing all MQTT clients.');
        $args = array(
            'category_name' => 'mqtt-client',
            'post_type'     => 'post',
            'posts_per_page'=> -1
        );

        $posts = get_posts($args);
        $topics = [];

        foreach ($posts as $post) {
            $topic = $post->post_title;
            $topics[] = $topic;
            update_option('mqtt_topic_' . $post->ID, $topic);
        }

        $host = 'test.mosquitto.org';
        $port = 1883;
        $client_id = 'id' . time();

        $this->log('Connecting to MQTT broker.');
        $this->connect_to_mqtt_broker($host, $port, $client_id, $topics);
    }

    // Connect to the MQTT broker and subscribe to topics
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

    // Process incoming MQTT messages
    public function procmsg($topic, $msg) {
        $this->log("Message received on topic {$topic}: {$msg}");

        // Create a new post in the custom post type
        $post_data = array(
            'post_title'    => wp_strip_all_tags($topic),
            'post_content'  => $msg,
            'post_status'   => 'publish',
            'post_author'   => 1, // Assuming user ID 1 is the admin
            'post_type'     => 'mqtt_log'
        );
        wp_insert_post($post_data);

        // Parse temperature and humidity values if needed
        // Update other relevant data as per your application needs
    }

}

new MQTT_Client_Initializer();

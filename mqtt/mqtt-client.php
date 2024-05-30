<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the phpMQTT library
require_once plugin_dir_path(__FILE__) . 'phpMQTT.php';

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

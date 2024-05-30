<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the phpMQTT library
require_once plugin_dir_path(__FILE__) . 'phpMQTT.php';

function custom_mqtt_connect_and_subscribe() {
    $server = 'public.mqtthq.com'; // Change to your MQTT broker
    $port = 1883;                  // Change to your MQTT broker port
    $username = '';                // MQTT username if required
    $password = '';                // MQTT password if required
    $client_id = 'wordpress_mqtt_client_' . uniqid();  // Unique client ID

    $mqtt = new phpMQTT($server, $port, $client_id);

    if (!$mqtt->connect(true, NULL, $username, $password)) {
        return "Failed to connect to the broker.";
    }

    $topics['test/topic'] = array('qos' => 0, 'function' => 'custom_mqtt_message');
    $mqtt->subscribe($topics, 0);

    // Set a timeout for proc, just in case
    $mqtt->proc(true, 1000);

    $mqtt->close();

    return get_option('mqtt_last_message', 'No messages yet.');
}

// Callback function to process received messages
function custom_mqtt_message($topic, $msg) {
    update_option('mqtt_last_message', $msg);
}
/*
// Function to connect to MQTT broker and subscribe to a topic
function custom_mqtt_connect_and_subscribe() {
    $server = 'public.mqtthq.com'; // Change to your MQTT broker
    $port = 1883;                  // Change to your MQTT broker port
    $username = '';                // MQTT username if required
    $password = '';                // MQTT password if required
    $client_id = 'wordpress_mqtt_client_' . uniqid();  // Unique client ID

    $mqtt = new phpMQTT($server, $port, $client_id);

    if (!$mqtt->connect(true, NULL, $username, $password)) {
        error_log("Failed to connect to the broker.");
        return "Failed to connect to the broker.";
    }

    $topics['test/topic'] = array('qos' => 0, 'function' => 'custom_mqtt_message');
    $mqtt->subscribe($topics, 0);

    while($mqtt->proc()) {
        // Keep processing messages
    }

    $mqtt->close();

    return get_option('mqtt_last_message', 'No messages yet.');
}
/*
// Function to connect to MQTT broker and subscribe to a topic
function custom_mqtt_connect_and_subscribe() {
    $server = 'public.mqtthq.com'; // Change to your MQTT broker
    $port = 1883;                  // Change to your MQTT broker port
    $username = '';                // MQTT username if required
    $password = '';                // MQTT password if required
    $client_id = 'wordpress_mqtt_client_' . uniqid();  // Unique client ID

    $mqtt = new phpMQTT($server, $port, $client_id);

    if (!$mqtt->connect(true, NULL, $username, $password)) {
        return "Failed to connect to the broker.";
    }

    $topics['test/topic'] = array('qos' => 0, 'function' => 'custom_mqtt_message');
    $mqtt->subscribe($topics, 0);

    $mqtt->proc();

    $mqtt->close();

    return get_option('mqtt_last_message', 'No messages yet.');
}
*/
/*
// Callback function to process received messages
function custom_mqtt_message($topic, $msg) {
    update_option('mqtt_last_message', $msg);
}
*/
add_action('wp_ajax_get_mqtt_messages', 'ajax_get_mqtt_messages');
add_action('wp_ajax_nopriv_get_mqtt_messages', 'ajax_get_mqtt_messages');
function ajax_get_mqtt_messages() {
    check_ajax_referer('custom-mqtt-client-nonce', 'nonce');
    
    try {
        $result = custom_mqtt_connect_and_subscribe();
        echo $result;
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    
    wp_die();
}
/*
// AJAX handler to get MQTT messages
add_action('wp_ajax_get_mqtt_messages', 'ajax_get_mqtt_messages');
add_action('wp_ajax_nopriv_get_mqtt_messages', 'ajax_get_mqtt_messages');
function ajax_get_mqtt_messages() {
    check_ajax_referer('custom-mqtt-client-nonce', 'nonce');

    $result = custom_mqtt_connect_and_subscribe();
    if ($result === "Failed to connect to the broker.") {
        wp_send_json_error($result);
    } else {
        wp_send_json_success($result);
    }
    wp_die();
}
/*
// AJAX handler to get MQTT messages
add_action('wp_ajax_get_mqtt_messages', 'ajax_get_mqtt_messages');
add_action('wp_ajax_nopriv_get_mqtt_messages', 'ajax_get_mqtt_messages');
function ajax_get_mqtt_messages() {
    check_ajax_referer('custom-mqtt-client-nonce', 'nonce');
    //echo custom_mqtt_connect_and_subscribe();
    wp_die();
}
*/
function custom_mqtt_shortcode() {
    ob_start();
    ?>
    <div id="mqtt-messages">
        Loading messages...
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
                    },
                    error: function(xhr, status, error) {
                        $('#mqtt-messages').html('Failed to fetch messages. Error: ' + error);
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
/*
function custom_mqtt_shortcode() {
    ob_start();
    ?>
    <div id="mqtt-messages">
        Loading messages...
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
                        if (response.success) {
                            $('#mqtt-messages').html(response.data);
                        } else {
                            $('#mqtt-messages').html('Error: ' + response.data);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#mqtt-messages').html('Failed to fetch messages. Error: ' + error);
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
/*
// Shortcode function to display MQTT messages
function custom_mqtt_shortcode() {
    ob_start();
    ?>
    <div id="mqtt-messages">
        Loading messages...
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
                    },
                    error: function() {
                        $('#mqtt-messages').html('Failed to fetch messages.');
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
*/

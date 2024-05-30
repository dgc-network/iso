<?php
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

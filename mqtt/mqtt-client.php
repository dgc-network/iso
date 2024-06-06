<?php

require_once plugin_dir_path(__FILE__) . 'phpMQTT.php';

function retrieve_MQTT_temperature($topic, $host, $port) {
    $client_id = 'wp-mqtt-client-' . uniqid();
    $mqtt = new phpMQTT($host, $port, $client_id);

    $temperature = null;

    if ($mqtt->connect(true, NULL, '', '')) {
        $mqtt->subscribe([$topic => ["qos" => 0, "function" => function ($topic, $msg) use (&$temperature) {
            $temperature = $msg;
        }]], 0);

        // Loop until we get the message or timeout
        $timeout = 10; // seconds
        $start_time = time();
        while (time() - $start_time < $timeout) {
            $mqtt->proc();
            if ($temperature !== null) {
                break;
            }
        }

        $mqtt->close();
    }

    return $temperature;
}

function display_mqtt_messages_shortcode() {
    ob_start(); ?>
    <div id="mqtt-messages-container">No messages available.</div>
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
    <script>
        (function() {
            const container = document.getElementById('mqtt-messages-container');
            const client  = mqtt.connect('wss://test.mosquitto.org:8081/mqtt'); // Secure WebSocket URL

            client.on('connect', function () {
                console.log('Connected to MQTT broker');
                client.subscribe('mytopic/test', function (err) {
                    if (err) {
                        console.error('Subscription error:', err);
                    }
                });
            });

            client.on('message', function (topic, message) {
                const msg = message.toString();
                console.log('Message received:', msg);
                const newMessage = document.createElement('div');
                newMessage.textContent = `Msg Received: ${msg}`;
                container.appendChild(newMessage);
            });

            client.on('error', function (error) {
                console.error('MQTT error:', error);
                container.textContent = 'Error fetching messages. Please check the console for more details.';
            });
        })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('display_mqtt_messages', 'display_mqtt_messages_shortcode');

function display_mqtt_messages($topic = 'mqttHQ-client-test', $host = 'test.mosquitto.org', $port = '8081') {
    ob_start(); ?>
    <div id="mqtt-messages-container" style="height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;">
        No messages available.
    </div>
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
    <script>
        function initializeMQTTClient() {
            const container = document.getElementById('mqtt-messages-container');
            const client = mqtt.connect('wss://<?php echo $host; ?>:<?php echo $port; ?>/mqtt'); // Secure WebSocket URL

            client.on('connect', function () {
                console.log('Connected to MQTT broker');
                client.subscribe('<?php echo $topic; ?>', function (err) {
                    if (err) {
                        console.error('Subscription error:', err);
                    }
                });
            });

            client.on('message', function (topic, message) {
                const msg = message.toString();
                console.log('Message received:', msg);
                
                const newMessage = document.createElement('div');
                newMessage.textContent = msg;
                newMessage.style.padding = '5px 0';
                
                // Prepend new message to the top
                container.insertBefore(newMessage, container.firstChild);
                
                // Scroll to top
                container.scrollTop = 0;
            });

            client.on('error', function (error) {
                console.error('MQTT error:', error);
                container.textContent = 'Error fetching messages. Please check the console for more details.';
            });
        }
        //initializeMQTTClient();
/*
        jQuery(document).ready(function($) {
            $("#mqtt-client-dialog").dialog({
                autoOpen: false,
                open: function(event, ui) {
                    initializeMQTTClient();
                }
            });

            // Assuming you have some trigger to open the dialog, like a button
            $("#open-mqtt-dialog").on("click", function() {
                $("#mqtt-client-dialog").dialog("open");
            });
        });
*/        
    </script>
    <?php
    return ob_get_clean();
}
/*
function display_mqtt_messages($topic = 'mqttHQ-client-test', $host = 'test.mosquitto.org', $port = '8081') {
    ob_start(); ?>
    <div id="mqtt-messages-container" style="height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;">
        No messages available.
    </div>
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
    <script>
        (function() {
            const container = document.getElementById('mqtt-messages-container');
            const client = mqtt.connect('wss://<?php echo $host; ?>:<?php echo $port; ?>/mqtt'); // Secure WebSocket URL

            client.on('connect', function () {
                console.log('Connected to MQTT broker');
                client.subscribe('<?php echo $topic; ?>', function (err) {
                    if (err) {
                        console.error('Subscription error:', err);
                    }
                });
            });

            client.on('message', function (topic, message) {
                const msg = message.toString();
                console.log('Message received:', msg);
                
                const newMessage = document.createElement('div');
                //newMessage.textContent = `Msg Received: ${msg}`;
                newMessage.textContent = msg;
                newMessage.style.padding = '5px 0';
                
                // Prepend new message to the top
                container.insertBefore(newMessage, container.firstChild);
                
                // Scroll to top
                container.scrollTop = 0;
            });

            client.on('error', function (error) {
                console.error('MQTT error:', error);
                container.textContent = 'Error fetching messages. Please check the console for more details.';
            });
        })();
    </script>
    <?php
    return ob_get_clean();
}
*/
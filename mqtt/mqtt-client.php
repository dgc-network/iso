<?php

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
                newMessage.textContent = `Msg Received: ${msg}`;
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
/*
add_shortcode('display_mqtt_messages', 'display_mqtt_messages');

function display_mqtt_messages($topic = false, $host = 'test.mosquitto.org', $port = '8081') {
    ob_start(); ?>
    <div id="mqtt-messages-container">No messages available.</div>
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
    <script>
        (function() {
            const container = document.getElementById('mqtt-messages-container');
            const client = mqtt.connect('wss://<?php echo $host;?>:<?php echo $port;?>/mqtt'); // Secure WebSocket URL

            client.on('connect', function () {
                console.log('Connected to MQTT broker');
                client.subscribe('<?php echo $topic;?>', function (err) {
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

add_shortcode('display_mqtt_messages', 'display_mqtt_messages');
/*
function display_mqtt_messages($topic = false, $host = 'test.mosquitto.org', $port = '8081') {
    ob_start(); ?>
    <div id="mqtt-messages-container">No messages available.</div>
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
    <script>
        (function() {
            const container = document.getElementById('mqtt-messages-container');
            const client  = mqtt.connect(`wss://${<?php echo $host;?>}:${<?php echo $port;?>}/mqtt`); // Secure WebSocket URL

            client.on('connect', function () {
                console.log('Connected to MQTT broker');
                client.subscribe('<?php echo $topic;?>', function (err) {
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
*/
jQuery(document).ready(function($) {

    // mqtt-client scripts
    function activate_mqtt_client_list_data(){
        activate_exception_notification_list_data($("#mqtt-client-id").val());

        $("#search-mqtt-client").on( "change", function() {

            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
        
            var searchValue = $("#search-mqtt-client").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
        
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        
            // Clear the values of all select elements after redirection
            $("#select-profile, #search-mqtt-client").val('');
        
        });

        $("#new-mqtt-client").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_mqtt_client_dialog_data',
                },
                success: function (response) {
                    get_mqtt_client_list_data();
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-mqtt-client-"]').on("click", function () {
            const mqtt_client_id = this.id.substring(17);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_mqtt_client_dialog_data',
                    '_mqtt_client_id': mqtt_client_id,
                },
                success: function (response) {
                    $("#mqtt-client-dialog").html(response.html_contain);
                    $("#mqtt-client-dialog").dialog('open');
                    activate_mqtt_client_list_data();
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#mqtt-client-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            open: function(event, ui) {
                open_MQTT_Client($("#mqtt-topic").val());
            },
            close: function(event, ui) {
                close_MQTT_Client();
            },
            buttons: {
                "Save": function () {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_mqtt_client_dialog_data',
                            '_mqtt_client_id': $("#mqtt-client-id").val(),
                            '_client_id': $("#client-id").val(),
                            '_mqtt_topic': $("#mqtt-topic").val(),
                            '_description': $("#description").val(),
                            '_ssid': $("#ssid").val(),
                            '_password': $("#password").val(),
                        },
                        success: function (response) {
                            //publishMQTTMessage("mytopic/newDeviceID", $("#client-id").val());
                            $("#mqtt-client-dialog").dialog('close');
                            get_mqtt_client_list_data();
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this MQTT client?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_mqtt_client_dialog_data',
                                '_mqtt_client_id': $("#mqtt-client-id").val(),
                            },
                            success: function (response) {
                                $("#mqtt-client-dialog").dialog('close');
                                get_mqtt_client_list_data();
                            },
                            error: function (error) {
                                console.error(error);
                                alert(error);
                            }
                        });
                    }
                },
            }
        });    
    }

    function get_mqtt_client_list_data(){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_mqtt_client_list_data',
            },
            success: function (response) {
                $("#result-container").html(response.html_contain);
                activate_mqtt_client_list_data();
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    // Hook the initialize_all_MQTT_clients() function to wp_loaded event
    $(window).on('load', function() {
        //initialize_all_MQTT_clients();
    });
    initialize_all_MQTT_clients();

    // Function to initialize MQTT client with a specific topic
    function initialize_all_MQTT_clients() {
        // Retrieve all MQTT client posts via AJAX
        $.ajax({
            url: '/wp-json/wp/v2/mqtt-client', // Adjust the endpoint URL as needed
            method: 'GET',
            success: function(response) {
                if (response.length > 0) {
                    // Loop through all MQTT client posts
                    response.forEach(function(post) {
                        const topic = post.title.rendered; // Get the topic from the post title
                        console.log('Post title for topic: ' + topic);
                        // Initialize MQTT client for this topic
                        initializeMQTTClient(topic);
                    });
                } else {
                    console.error('No MQTT client posts found.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching MQTT client posts:', error);
            }
        });
    }

    function initializeMQTTClient(topic = false, host = 'test.mosquitto.org', port = '8081') {
        const container = document.getElementById('mqtt-messages-container');
    
        mqttClientInit = mqtt.connect('wss://' + host + ':' + port + '/mqtt'); // Secure WebSocket URL
    
        mqttClientInit.on('connect', function () {
            console.log('Connected to MQTT broker');
            mqttClientInit.subscribe(topic, function (err) {
                if (err) {
                    console.error('Subscription error:', err);
                }
            });
        });
    
        mqttClientInit.on('message', function (topic, message) {
            const msg = message.toString();
            console.log('Message received:', msg);

            // Parse temperature and humidity values
            const DS18B20Match = msg.match(/DS18B20 Temperature:\s*([\d.]+)/);
            const temperatureMatch = msg.match(/DHT11 Temperature:\s*([\d.]+)/);
            const humidityMatch = msg.match(/DHT11 Humidity:\s*(\d+)/);
            const ssidMatch = msg.match(/SSID:\s*(\w+)/);
            const passwordMatch = msg.match(/Password:\s*(\w+)/);
    
            if (DS18B20Match) {
                const temperature = parseFloat(DS18B20Match[1]);
                console.log('Parsed Temperature:', temperature);
                update_mqtt_client_data(topic, temperature, 'temperature');
            }
    
            if (temperatureMatch) {
                const temperature = parseFloat(temperatureMatch[1]);
                console.log('Parsed Temperature:', temperature);
                update_mqtt_client_data(topic, temperature, 'temperature');
            }
    
            if (humidityMatch) {
                const humidity = parseInt(humidityMatch[1], 10);
                console.log('Parsed Humidity:', humidity);
                update_mqtt_client_data(topic, humidity, 'humidity');
            }
    
            if (ssidMatch) {
                const ssid = ssidMatch[1];
                console.log('Parsed SSID:', ssid);
                update_mqtt_client_data(topic, ssid, 'ssid');
            }
    
            if (passwordMatch) {
                const password = passwordMatch[1];
                console.log('Parsed Password:', password);
                update_mqtt_client_data(topic, password, 'password');
            }
        });
    }

    let mqttClient;

    function close_MQTT_Client() {
        if (mqttClient) {
            mqttClient.end();
            mqttClient = null;
            console.log('Disconnected from MQTT broker');
        }
    }

    function open_MQTT_Client(topic = false, host = 'test.mosquitto.org', port = '8081') {
        const container = document.getElementById('mqtt-messages-container');
    
        // Disconnect previous client if exists
        if (mqttClient) {
            mqttClient.end();
        }
    
        mqttClient = mqtt.connect('wss://' + host + ':' + port + '/mqtt'); // Secure WebSocket URL
    
        mqttClient.on('connect', function () {
            console.log('Connected to MQTT broker');
            mqttClient.subscribe(topic, function (err) {
                if (err) {
                    console.error('Subscription error:', err);
                }
            });
        });
    
        mqttClient.on('message', function (topic, message) {
            const msg = message.toString();
            console.log('Message received:', msg);
    
            const container = document.getElementById('mqtt-messages-container'); // Ensure container is selected again
            if (!container) {
                console.error('Container not found');
                return;
            }
    
            const newMessage = document.createElement('div');
            newMessage.textContent = msg;
    
            // Prepend new message to the top
            if (container.firstChild) {
                container.insertBefore(newMessage, container.firstChild);
            } else {
                container.appendChild(newMessage);
            }
    
            // Scroll to top
            container.scrollTop = 0;
        });
    
        mqttClient.on('error', function (error) {
            console.error('MQTT error:', error);
            const container = document.getElementById('mqtt-messages-container'); // Ensure container is selected again
            if (container) {
                container.textContent = 'Error fetching messages. Please check the console for more details.';
            }
        });
    }

    function publishMQTTMessage(topic, message) {
        if (mqttClient && mqttClient.connected) {
            mqttClient.publish(topic, message, {}, function (err) {
                if (err) {
                    console.error('Publish error:', err);
                } else {
                    console.log('Message published:', message);
                }
            });
        } else {
            console.error('MQTT client is not connected');
        }
    }

    function update_mqtt_client_data(topic, value, flag=false) {
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                action: 'update_mqtt_client_data',
                _topic: topic,
                _value: value,
                _flag: flag
            },
            success: function(response) {
                if (response.success) {
                    console.log(response.data.message);
                } else {
                    console.error(response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    }

    // Exception notification scripts
    function activate_exception_notification_list_data(mqtt_client_id=false){
        $("#new-exception-notification").on("click", function() {
            $("#new-exception-notification-dialog").dialog('open');
        });
    
        $("#new-exception-notification-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {
                "Add": function () {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_exception_notification_dialog_data',
                            '_mqtt_client_id': mqtt_client_id,
                            '_user_id': $("#new-user-id").val(),
                            '_max_temperature': $("#new-max-temperature").val(),
                            '_max_humidity': $("#new-max-humidity").val(),
                        },
                        success: function (response) {
                            $("#new-exception-notification-dialog").dialog('close');
                            get_exception_notification_list_data(mqtt_client_id);
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
            }
        });    

        $('[id^="edit-exception-notification-"]').on("click", function () {
            const exception_notification_id = this.id.substring(28);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_exception_notification_dialog_data',
                    '_exception_notification_id': exception_notification_id,
                },
                success: function (response) {
                    $("#exception-notification-dialog").html(response.html_contain);
                    $("#exception-notification-dialog").dialog('open');
                    activate_exception_notification_list_data(mqtt_client_id);
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#exception-notification-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {
                "Save": function () {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_exception_notification_dialog_data',
                            '_exception_notification_id': $("#exception-notification-id").val(),
                            '_user_id': $("#user-id").val(),
                            '_max_temperature': $("#max-temperature").val(),
                            '_max_humidity': $("#max-humidity").val(),
                        },
                        success: function (response) {
                            $("#exception-notification-dialog").dialog('close');
                            get_exception_notification_list_data(mqtt_client_id);
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this exception notification?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_exception_notification_dialog_data',
                                '_exception_notification_id': $("#exception_notification-id").val(),
                            },
                            success: function (response) {
                                $("#exception-notification-dialog").dialog('close');
                                get_exception_notification_list_data(mqtt_client_id);
                            },
                            error: function (error) {
                                console.error(error);
                                alert(error);
                            }
                        });
                    }
                },
            }
        });    
    }

    function get_exception_notification_list_data(mqtt_client_id=false){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_exception_notification_list_data',
                '_mqtt_client_id': mqtt_client_id,
            },
            success: function (response) {
                $("#exception-notification-list").html(response.html_contain);
                activate_exception_notification_list_data();
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

/*    
    function fetchMqttMessages() {
        $.ajax({
            url: mqttClient.ajax_url,
            method: 'POST',
            data: {
                action: 'get_mqtt_messages',
                nonce: mqttClient.nonce
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
*/    
});

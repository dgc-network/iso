jQuery(document).ready(function($) {
    let lat;
    // Function to initialize MQTT client with a specific topic
    initialize_all_MQTT_clients();
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
        
            let parsedMessage;
            try {
                parsedMessage = JSON.parse(msg);
            } catch (e) {
                console.error('Failed to parse JSON message:', e);
                return;
            }
        
            // Define a mapping of keys to their respective handlers or types
            const keyMapping = {
                ssid: 'ssid',
                password: 'password',
                temperature: 'temperature',
                humidity: 'humidity',
                //topic: 'topic',
                //message: 'message',
                //latitude: 'latitude',
                //longitude: 'longitude',
                // Add more mappings as needed
            };
        
            Object.keys(parsedMessage).forEach(key => {
                if (keyMapping[key] !== undefined) {
                    console.log(`Parsed ${key.charAt(0).toUpperCase() + key.slice(1)}:`, parsedMessage[key]);
                    update_mqtt_client_data(topic, keyMapping[key], parsedMessage[key]);
                }
            });

            // Check if all required keys are present
            const requiredKeys = ['phone', 'message', 'latitude', 'longitude'];
            const hasAllKeys = requiredKeys.every(key => parsedMessage.hasOwnProperty(key));
        
            if (hasAllKeys) {
                createGeolocationMessagePost(parsedMessage);
            } else {
                console.log('Message does not contain all required keys');
            }

        });
    }

    // Geolocation map
    var map, marker;

    function updateMap(geolocationData) {
        // Ensure latitude and longitude are numbers
        const latitude = parseFloat(geolocationData.data.Latitude);
        const longitude = parseFloat(geolocationData.data.Longitude);

        // Update the map view to the new geolocation data
        map.setView([latitude, longitude], 13);

        // If marker already exists, remove it
        if (marker) {
            map.removeLayer(marker);
        }

        // Add a new marker at the updated location
        marker = L.marker([latitude, longitude]).addTo(map);

        // Add a popup to the marker with some information
        marker.bindPopup(`<b>Device ID:</b> ${geolocationData.deviceID}<br>
                          <b>Latitude:</b> ${latitude}<br>
                          <b>Longitude:</b> ${longitude}<br>
                          <b>Timestamp:</b> ${new Date(geolocationData.timestamp * 1000).toLocaleString()}<br>
                          <b>Phone:</b> ${geolocationData.data.Phone}°C<br>
                          <b>Message:</b> ${geolocationData.data.Message}%`).openPopup();
    }

    function createGeolocationMessagePost(data) {
        // Use AJAX to call a WordPress function to create a new post
        $.ajax({
            //url: ajaxurl, // WordPress AJAX URL
            url: ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'create_geolocation_message_post', // Custom action name
                phone: data.phone,
                message: data.message,
                latitude: data.latitude,
                longitude: data.longitude,
            },
            success: function (response) {
                console.log('Post created successfully:', response);
            },
            error: function (error) {
                console.error('Failed to create post:', error);
            }
        });
    }

    activate_geolocation_message_list_data();

    // geolocation-message scripts
    function activate_geolocation_message_list_data(){

        $('[id^="edit-geolocation-message-"]').on("click", function () {
            const geolocation_message_id = this.id.substring(25);
            //$("#geolocation-dialog").dialog('open');

            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_geolocation_message_dialog_data',
                    '_geolocation_message_id': geolocation_message_id,
                },
                success: function (response) {
                    //$("#mqtt-client-dialog").html(response.html_contain);
                    //$("#mqtt-client-dialog").dialog('open');
                    $("#latitude").val(response.latitude);
                    $("#longitude").val(response.longitude);
                    $("#geolocation-dialog").dialog('open');
                    //activate_mqtt_client_list_data();
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });

        });

        $("#geolocation-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            open: function(event, ui) {
                //display_geolocation($("#mqtt-topic").val());
                // Initialize the map
                map = L.map('map').setView([0, 0], 2); // Initial view, will be updated

                // Add a tile layer to the map (OpenStreetMap tiles)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
        
                // Update the map view to the new geolocation data
                latitude = $("#latitude").val();
                longitude = $("#longitude").val();
                map.setView([latitude, longitude], 13);


            },
        });

    }

    function display_geolocation(topic = false, host = 'test.mosquitto.org', port = '8081'){
        // Initialize the map
        map = L.map('map').setView([0, 0], 2); // Initial view, will be updated

        // Add a tile layer to the map (OpenStreetMap tiles)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

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
            try {
                const geolocationData = JSON.parse(msg);
                updateMap(geolocationData);
            } catch (e) {
                console.error("Invalid JSON message:", msg);
            }
        });
    }

    activate_mqtt_client_list_data();

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
                    //$("#latitude").val(lat);
                    //$("#geolocation-dialog").dialog('open');
                    activate_mqtt_client_list_data();
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });
/*
        $("#geolocation-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            open: function(event, ui) {
                display_geolocation($("#mqtt-topic").val());
            },
        });
*/
        $("#mqtt-client-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            open: function(event, ui) {
                display_MQTT_message($("#mqtt-topic").val());
            },
            close: function(event, ui) {
                //close_MQTT_Client();
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
/*
            // Function to get current geolocation
            function getCurrentLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showPosition, showError);
                } else {
                    alert("Geolocation is not supported by this browser.");
                }
            }
    
            // Show the position on the map
            function showPosition(position) {
                //var lat = position.coords.latitude;
                lat = position.coords.latitude;
                var lon = position.coords.longitude;
                var accuracy = position.coords.accuracy;
            }
    
            // Handle geolocation errors
            function showError(error) {
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        alert("User denied the request for Geolocation.");
                        break;
                    case error.POSITION_UNAVAILABLE:
                        alert("Location information is unavailable.");
                        break;
                    case error.TIMEOUT:
                        alert("The request to get user location timed out.");
                        break;
                    case error.UNKNOWN_ERROR:
                        alert("An unknown error occurred.");
                        break;
                }
            }
    
            // Get current location on page load
            window.onload = getCurrentLocation;
*/    
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

    let mqttClient;

    function display_MQTT_message(topic = false, host = 'test.mosquitto.org', port = '8081') {
        //const container = document.getElementById('mqtt-messages-container');
    
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
        
            let prettyJsonString;
            try {
                const jsonObject = JSON.parse(msg);
                prettyJsonString = JSON.stringify(jsonObject, null, 2);
            } catch (e) {
                // If the message is not valid JSON, just display the raw message
                prettyJsonString = msg;
            }
        
            const container = document.getElementById('mqtt-messages-container');
            if (!container) {
                console.error('Container not found');
                return;
            }
        
            const newMessage = document.createElement('div');
            newMessage.style.whiteSpace = 'pre-wrap'; // Ensure whitespace is preserved
            newMessage.style.background = '#f9f9f9'; // Optional: Add some styling
            newMessage.style.padding = '10px';      // Optional: Add some styling
            newMessage.style.border = '1px solid #ccc'; // Optional: Add some styling
            newMessage.textContent = prettyJsonString;
        
            // Append new message to the bottom
            container.appendChild(newMessage);
        
            // Scroll to bottom
            container.scrollTop = container.scrollHeight;
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

    function close_MQTT_Client() {
        if (mqttClient) {
            mqttClient.end();
            mqttClient = null;
            console.log('Disconnected from MQTT broker');
        }
    }

    function update_mqtt_client_data(topic, key, value) {
        console.log(`Updating ${key} for topic ${topic} with value ${value}`);
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                action: 'update_mqtt_client_data',
                _topic: topic,
                _key: key,
                _value: value,
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
                                '_exception_notification_id': $("#exception-notification-id").val(),
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
});

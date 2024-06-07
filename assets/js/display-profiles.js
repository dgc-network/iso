// display-profiles
jQuery(document).ready(function($) {

    // Function to check if the string is a valid URL
    function isURL(str) {
        var pattern = /^(http|https):\/\/[^ "]+$/;
        return pattern.test(str);
    }
    
    $("#select-profile").on("change", function() {
        window.location.replace("?_select_profile="+$(this).val());
        $(this).val('');
    });

    activate_site_profile_data();
    activate_site_job_list_data();
    activate_doc_category_list_data();
    activate_mqtt_client_list_data();

    // my-profile scripts
    $("#my-profile-submit").on("click", function () {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_my_profile_data',
                '_display_name': $("#display-name").val(),
                '_user_email': $("#user-email").val(),
            },
            success: function (response) {
                console.log(response);
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });            
    });

    // site-profile scripts
    function activate_site_profile_data(){
        $("#site-image-container").on("click", function() {
            $("#site-image-container").hide();
            $("#site-image-url").show();
        });
    
        $("#set-image-url").on("click", function() {
            $("#site-image-container").show();
            $("#site-image-url").hide();
            if (isURL($('#image-url').val())) {
                $("#site-image-container").html('<img src="'+$('#image-url').val()+'" style="object-fit:cover; width:250px; height:250px;">');
            } else {
                $("#site-image-container").html('<a href="#" id="custom-image-href">Set image URL</a>');
            }
        });
    
        // Show the site-hint when the user starts typing
        $('#site-title').on('input', function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_site_list_data',
                    '_site_title': $(this).val(),
                },
                success: function (response) {
                    $('#site-hint').empty();
                    let output = '<table>'        
                    $.each(response, function (index, value) {
                        output += '<tr><td id="select-site-id-'+value.site_id+'">'
                        output += value.site_title
                        output += '</td></tr>'
                    });
                    output += '</table>'
                    $('#site-hint').append(output).show();
    
                    $('[id^="select-site-id-"]').on("click", function () {
                        const id = this.id.substring(15);
                        $('#site-id').val(id);
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'get_site_dialog_data',
                                '_site_id': $("#site-id").val(),
                            },
                            success: function (response) {
                                $('#site-title').val(response.site_title);
                                $("#site-hint").hide();
                            },
                            error: function (error) {
                                console.error(error);
                                alert(error);
                            }
                        });            
                    });            
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });
    
        $("#site-profile-submit").on("click", function () {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_site_profile_data',
                    '_site_id': $("#site-id").val(),
                    '_site_title': $("#site-title").val(),
                    '_image_url': $("#image-url").val(),
                },
                success: function (response) {
                    if (response.success) {
                        alert("Success!");
                    } else {
                        alert("Error: " + response.error);
                    }
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });            
        });            
    
        $("#new-site-user").on("click", function() {
            $("#new-user-dialog").dialog('open');
        });
    
        $('[id^="edit-site-user-"]').on("click", function () {
            const user_id = this.id.substring(15);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_site_user_dialog_data',
                    '_user_id': user_id,
                },
                success: function (response) {
                    $("#site-user-dialog").html(response.html_contain);
                    $("#site-user-dialog").dialog('open');

                    $('[id^="check-user-job-"]').on("click", function () {
                        const doc_id = this.id.substring(15);
                        // Toggle the checkbox state
                        $("#is-user-doc-"+doc_id).prop("checked", function(i, value) {
                            return !value;
                        });
                        
                        if (window.confirm("Are you sure you want to change this setting?")) {
                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'set_user_doc_data',
                                    _doc_id : doc_id,
                                    _user_id : user_id,
                                    _is_user_doc : $("#is-user-doc-"+doc_id).is(":checked") ? 1 : 0,
                                },
                                success: function (response) {
                                    console.log(response);
                                },
                                error: function (error) {
                                    console.error(error);
                                    alert(error);
                                }
                            });
                        }
                    });
                                
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#new-user-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
        });
    
        $("#site-user-dialog").dialog({
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
                            'action': 'set_site_user_dialog_data',
                            '_user_id': $("#user-id").val(),
                            '_display_name': $("#display-name").val(),
                            '_user_email': $("#user-email").val(),
                            '_is_site_admin': $('#is-site-admin').is(":checked") ? 1 : 0,
                            '_select_site': $("#select-site").val(),
                        },
                        success: function (response) {
                            $("#site-user-dialog").dialog('close');
                            //get_site_profile_data();
                            window.location.replace(window.location.href);
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this site user?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_site_user_dialog_data',
                                '_user_id': $("#user-id").val(),
                            },
                            success: function (response) {
                                $("#site-user-dialog").dialog('close');
                                get_site_profile_data();
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

    function get_site_profile_data(){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_site_profile_data',
                '_site_id': site_id,
            },
            success: function (response) {
                $("#result-container").html(response.html_contain);
                activate_site_profile_data();
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    // site-job scripts
    function activate_site_job_list_data(){
        $("#search-site-job").on( "change", function() {

            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
        
            var siteJobValue = $("#search-site-job").val();
            if (siteJobValue) {
                queryParams.push("_search=" + siteJobValue);
            }
        
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        
            // Clear the values of all select elements after redirection
            $("#select-profile, #search-site-job").val('');
        
        });

        //activate_doc_action_list_data();

        $("#new-site-job").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_site_job_dialog_data',
                },
                success: function (response) {
                    get_site_job_list_data();
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-site-job-"]').on("click", function () {
            const doc_id = this.id.substring(14);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_site_job_dialog_data',
                    '_doc_id': doc_id,
                },
                success: function (response) {
                    $("#site-job-dialog").html(response.html_contain);
                    $("#site-job-dialog").dialog('open');
                    activate_job_action_list_data(doc_id);
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#site-job-dialog").dialog({
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
                            'action': 'set_site_job_dialog_data',
                            '_doc_id': $("#doc-id").val(),
                            '_job_number': $("#job-number").val(),
                            '_job_title': $("#job-title").val(),
                            '_job_content': $("#job-content").val(),
                            '_department': $("#department").val(),
                        },
                        success: function (response) {
                            $("#site-job-dialog").dialog('close');
                            get_site_job_list_data();
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this site job?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_site_job_dialog_data',
                                '_doc_id': $("#doc-id").val(),
                            },
                            success: function (response) {
                                $("#site-job-dialog").dialog('close');
                                get_site_job_list_data();
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

    function get_site_job_list_data(){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_site_job_list_data',
            },
            success: function (response) {
                $("#result-container").html(response.html_contain);
                activate_site_job_list_data();
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    // doc-category scripts
    function activate_doc_category_list_data(){
        $("#new-doc-category").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_doc_category_dialog_data',
                },
                success: function (response) {
                    get_doc_category_list_data();
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-doc-category-"]').on("click", function () {
            const category_id = this.id.substring(18);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_doc_category_dialog_data',
                    '_category_id': category_id,
                },
                success: function (response) {
                    $("#doc-category-dialog").dialog('open');
                    $("#category-id").val(category_id);
                    $("#category-title").val(response.category_title);
                    $("#category-content").val(response.category_content);
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#doc-category-dialog").dialog({
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
                            'action': 'set_doc_category_dialog_data',
                            '_category_id': $("#category-id").val(),
                            '_category_title': $("#category-title").val(),
                            '_category_content': $("#category-content").val(),
                        },
                        success: function (response) {
                            $("#doc-category-dialog").dialog('close');
                            get_doc_category_list_data();
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this doc category?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_doc_category_dialog_data',
                                '_category_id': $("#category-id").val(),
                            },
                            success: function (response) {
                                $("#doc-category-dialog").dialog('close');
                                get_doc_category_list_data();
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

    function get_doc_category_list_data(){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_doc_category_list_data',
            },
            success: function (response) {
                $("#result-container").html(response.html_contain);
                activate_doc_category_list_data();
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    // doc-action scripts
    function activate_job_action_list_data(doc_id=false) {
        $("#new-doc-action").on("click", function() {
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_doc_action_dialog_data',
                    '_doc_id': doc_id,
                },
                success: function (response) {
                    get_job_action_list_data(doc_id);
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });

        $('[id^="edit-doc-action-"]').on("click", function () {
            const action_id = this.id.substring(16);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_doc_action_dialog_data',
                    '_action_id': action_id,
                },
                success: function (response) {
                    $("#doc-action-dialog").html(response.html_contain);
                    $("#doc-action-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#doc-action-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {
                "Save": function() {
                    jQuery.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_doc_action_dialog_data',
                            '_action_id': $("#action-id").val(),
                            '_action_title': $("#action-title").val(),
                            '_action_content': $("#action-content").val(),
                            '_next_job': $("#next-job").val(),
                            '_next_leadtime': $("#next-leadtime").val(),
                        },
                        success: function (response) {
                            $("#doc-action-dialog").dialog('close');
                            get_job_action_list_data(doc_id);
                        },
                        error: function (error) {
                            console.error(error);                    
                            alert(error);
                        }
                    });            
                },
                "Delete": function() {
                    if (window.confirm("Are you sure you want to delete this doc action?")) {
                        jQuery.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_doc_action_dialog_data',
                                '_action_id': $("#action-id").val(),
                            },
                            success: function (response) {
                                $("#doc-action-dialog").dialog('close');
                                get_job_action_list_data(doc_id);
                            },
                            error: function(error){
                                console.error(error);
                                alert(error);
                            }
                        });
                    }
                }
            }
        });    

    }

    function get_job_action_list_data(doc_id=false) {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_doc_action_list_data',
                '_doc_id': doc_id,
            },
            success: function (response) {
                $("#doc-action-list").html(response.html_contain);
                activate_job_action_list_data(doc_id);
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    // mqtt-client scripts
    function activate_mqtt_client_list_data(){
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
                    //$("#result-container").html(response.html_contain);
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
                initializeMQTTClient($("#mqtt-topic").val());
            },
            close: function(event, ui) {
                closeMQTTClient();
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
                            publishMQTTMessage("mytopic/newDeviceID", $("#client-id").val());
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
        initialize_all_MQTT_clients();
    });

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

    let mqttClient;

    function closeMQTTClient() {
        if (mqttClient) {
            mqttClient.end();
            mqttClient = null;
            console.log('Disconnected from MQTT broker');
        }
    }

    function initializeMQTTClient(topic = false, host = 'test.mosquitto.org', port = '8081') {
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
            //newMessage.style.padding = '5px 0';
    
            // Prepend new message to the top
            if (container.firstChild) {
                container.insertBefore(newMessage, container.firstChild);
            } else {
                container.appendChild(newMessage);
            }
    
            // Scroll to top
            container.scrollTop = 0;

            // Parse temperature value and ...
            const temperature = parseFloat(msg);
            updateTemperatureOption(topic, temperature);
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

    function updateTemperatureOption(topic, temperature) {
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                action: 'update_temperature_option',
                topic: topic,
                temperature: temperature
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

});

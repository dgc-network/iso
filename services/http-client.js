jQuery(document).ready(function($) {
    // geolocation-message scripts
    var map, marker;
    activate_geolocation_message_list_data();

    function set_geolocation_message_data(data) {
        // Use AJAX to call a WordPress function to create a new post
        $.ajax({
            //url: ajaxurl, // WordPress AJAX URL
            url: ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'set_geolocation_message_data', // Custom action name
                receiver: data.receiver,
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

    function activate_geolocation_message_list_data(){

        $('[id^="edit-geolocation-message-"]').on("click", function () {
            const geolocation_message_id = this.id.substring(25);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_geolocation_message_data',
                    '_geolocation_message_id': geolocation_message_id,
                },
                success: function (response) {
                    $("#latitude").val(response.latitude);
                    $("#longitude").val(response.longitude);
                    $("#message").text(response.message);
                    $("#geolocation-dialog").dialog('open');
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
                // Initialize the map
                map = L.map('map').setView([0, 0], 2); // Initial view, will be updated

                // Add a tile layer to the map (OpenStreetMap tiles)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
        
                // Update the map view to the new geolocation data
                latitude = $("#latitude").val();
                longitude = $("#longitude").val();
                map.setView([latitude, longitude], 18);
            },
            close: function(event, ui) {
                if (map) {
                    map.remove(); // Properly remove the map instance
                    map = null; // Clear the map variable
                }
            }
        });
    }

    // http-client scripts
    activate_http_client_list_data();

    function activate_http_client_list_data(){

        activate_exception_notification_list_data($("#http-client-id").val());
/*
        $("#select-todo").on( "change", function() {
            window.location.replace("?_select_todo="+$(this).val());
            $(this).val('');
        });
*/    
        $("#search-http-client").on( "change", function() {

            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-todo").val();
            if (profileValue) {
                queryParams.push("_select_todo=" + profileValue);
            }
        
            var searchValue = $("#search-http-client").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
        
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        
            // Clear the values of all select elements after redirection
            $("#select-todo, #search-http-client").val('');
        
        });

        $("#new-http-client").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_http_client_dialog_data',
                },
                success: function (response) {
                    get_http_client_list_data();
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-http-client-"]').on("click", function () {
            const http_client_id = this.id.substring(17);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_http_client_dialog_data',
                    '_http_client_id': http_client_id,
                },
                success: function (response) {
                    $("#http-client-dialog").html(response.html_contain);
                    $("#http-client-dialog").dialog('open');
                    //activate_http_client_list_data();
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#http-client-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            open: function(event, ui) {
                display_MQTT_message($("#mqtt-topic").val());
            },
            buttons: {
                "Save": function () {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_http_client_dialog_data',
                            '_http_client_id': $("#http-client-id").val(),
                            '_client_id': $("#client-id").val(),
                            '_description': $("#description").val(),
                            '_ssid': $("#ssid").val(),
                            '_password': $("#password").val(),
                        },
                        success: function (response) {
                            $("#http-client-dialog").dialog('close');
                            get_http_client_list_data();
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this HTTP client?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_http_client_dialog_data',
                                '_http_client_id': $("#http-client-id").val(),
                            },
                            success: function (response) {
                                $("#http-client-dialog").dialog('close');
                                get_http_client_list_data();
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

    function get_http_client_list_data(){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_http_client_list_data',
            },
            success: function (response) {
                $("#result-container").html(response.html_contain);
                activate_http_client_list_data();
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }
    // Exception notification scripts
    function activate_exception_notification_list_data(http_client_id=false){
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
                            '_http_client_id': http_client_id,
                            '_user_id': $("#new-user-id").val(),
                            '_max_temperature': $("#new-max-temperature").val(),
                            '_max_humidity': $("#new-max-humidity").val(),
                        },
                        success: function (response) {
                            $("#new-exception-notification-dialog").dialog('close');
                            get_exception_notification_list_data(http_client_id);
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
                    //activate_exception_notification_list_data(http_client_id);
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
                            get_exception_notification_list_data(http_client_id);
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
                                get_exception_notification_list_data(http_client_id);
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

    function get_exception_notification_list_data(http_client_id=false){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_exception_notification_list_data',
                '_http_client_id': http_client_id,
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

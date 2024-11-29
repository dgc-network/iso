jQuery(document).ready(function($) {
    // iot-device scripts
    activate_iot_device_list_data();
    function activate_iot_device_list_data(){
        $("#select-todo").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-todo").val();
            if (profileValue) {
                queryParams.push("_select_todo=" + profileValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

        $("#search-device").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-todo").val();
            if (profileValue) {
                queryParams.push("_select_todo=" + profileValue);
            }
        
            var searchValue = $("#search-device").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
            // Clear the values of all select elements after redirection
            $("#search-device").val('');
        });

        $("#new-iot-device").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_iot_device_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_iot_device_list_data();    
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-iot-device-"]').on("click", function () {
            const device_id = this.id.substring(16);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_iot_device_dialog_data',
                    '_device_id': device_id,
                },
                success: function (response) {
                    $("#iot-device-dialog").html(response.html_contain);
                    // Initialize Mermaid when the document is ready
                    if (typeof mermaid !== 'undefined') {
                        mermaid.initialize({ startOnLoad: true });
                        mermaid.init(undefined, $('#iot-device-dialog .mermaid'));
                    } else {
                        console.log('Mermaid is not loaded');
                    }
    
                    if ($("#is-site-admin").val() === "1") {
                        $("#iot-device-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_iot_device_dialog_data',
                                        '_device_id': $("#device-id").val(),
                                        '_device_number': $("#device-number").val(),
                                        '_device_title': $("#device-title").val(),
                                        '_device_content': $("#device-content").val(),
                                    },
                                    success: function (response) {
                                        $("#iot-device-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_iot_device_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this IoT device?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_iot_device_dialog_data',
                                            '_device_id': $("#device-id").val(),
                                        },
                                        success: function (response) {
                                            $("#iot-device-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_iot_device_list_data();
                                        },
                                        error: function (error) {
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            },
                        });
                    }
                    $("#iot-device-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#iot-device-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });    
    }

    
    // iot-message scripts
    activate_iot_message_list_data();
    function activate_iot_message_list_data(){

        $("#select-todo").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var todoValue = $("#select-todo").val();
            if (todoValue) {
                queryParams.push("_select_todo=" + todoValue);
            }

            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });
    }

});

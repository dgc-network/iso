jQuery(document).ready(function($) {
    // iot-device scripts
    activate_iot_device_list_data();
    function activate_iot_device_list_data(){
        $("#select-todo").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var selectValue = $("#select-todo").val();
            if (selectValue) {
                queryParams.push("_select_todo=" + selectValue);
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
            var selectValue = $("#select-todo").val();
            if (selectValue) {
                queryParams.push("_select_todo=" + selectValue);
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
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Add or update the `_device_id` parameter
            urlParams.set("_device_id", device_id);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
        });

        $("#iot-dialog-exit").on("click", function () {
            //window.location.replace(window.location.href);
            // Get the current URL
            var currentUrl = window.location.href;
            // Create a URL object
            var url = new URL(currentUrl);
            // Remove the specified parameter
            url.searchParams.delete('_device_id');
            // Get the modified URL
            var modifiedUrl = url.toString();
            // Reload the page with the modified URL
            window.location.replace(modifiedUrl);
        });

        $('[id^="backup-edit-iot-device-"]').on("click", function () {
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
                    //$("#result-container").html(response.html_contain);
                    // Initialize Mermaid when the document is ready
                    if (typeof mermaid !== 'undefined') {
                        mermaid.initialize({ 
                            startOnLoad: true,
                            themeVariables: {
                                lineColor: "#FF0000", // Replace with your desired color
                            }
                        });
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
            var selectValue = $("#select-todo").val();
            if (selectValue) {
                queryParams.push("_select_todo=" + selectValue);
            }

            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });
    }

});

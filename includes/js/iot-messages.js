jQuery(document).ready(function($) {
    // Function to update the URL with new query parameters
    function updateUrlWithParams(params) {
        var currentUrl = new URL(window.location.href);
    
        // Loop through the params object and set query parameters
        for (const key in params) {
            if (params[key]) {
                currentUrl.searchParams.set(key, params[key]);
            } else {
                currentUrl.searchParams.delete(key); // Remove parameter if value is null or empty
            }
        }
    
        // Redirect to the updated URL
        window.location.href = currentUrl.toString();
    }
    
    // iot-device scripts
    activate_iot_device_list_data();
    function activate_iot_device_list_data(){
/*        
        $("#select-todo").on("change", function () {
            // Get the selected value
            var selectValue = $("#select-todo").val();
        
            // Update the URL with the new parameter
            updateUrlWithParams({ _select_todo: selectValue });
        });
        
        $("#search-device").on("change", function () {
            // Get values from select and search inputs
            var selectValue = $("#select-todo").val();
            var searchValue = $("#search-device").val();
        
            // Update the URL with new parameters
            updateUrlWithParams({
                _select_todo: selectValue,
                _search: searchValue,
            });
        
            // Clear the search input value after updating the URL
            $("#search-device").val('');
        });
*/
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

        $("#save-iot-device").on("click", function () {
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
                    //$("#iot-device-dialog").dialog('close');
                    $("#result-container").html(response.html_contain);
                    activate_iot_device_list_data();
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#del-iot-device").on("click", function () {
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
                        //$("#iot-device-dialog").dialog('close');
                        $("#result-container").html(response.html_contain);
                        activate_iot_device_list_data();
                    },
                    error: function (error) {
                        console.error(error);
                        alert(error);
                    }
                });
            }
        });

        $("#iot-dialog-exit").on("click", function () {
            // Get the current URL
            var currentUrl = window.location.href;
            // Create a URL object
            var url = new URL(currentUrl);
            // Remove the specified parameter
            url.searchParams.delete('_device_id');
            // Reset the 'paged' parameter to 1
            url.searchParams.set('paged', 1);
            // Get the modified URL
            var modifiedUrl = url.toString();
            // Reload the page with the modified URL
            window.location.replace(modifiedUrl);
        });
    }

/*    
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
*/
});

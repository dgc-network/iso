jQuery(document).ready(function($) {
    const prevDeviceId = $("#prev-device-id").val();
    const nextDeviceId = $("#next-device-id").val();

    // Function to navigate to the previous or next device
    function navigateToDevice(deviceId) {
        if (deviceId) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set("_device_id", deviceId);
            window.location.href = currentUrl.toString();
        }
    }

    // Keyboard navigation
    $(document).on("keydown", function (event) {
        if (event.ctrlKey && event.key === "ArrowRight" && nextDeviceId) {
            navigateToDevice(nextDeviceId); // Move to the next device
        } else if (event.ctrlKey && event.key === "ArrowLeft" && prevDeviceId) {
            navigateToDevice(prevDeviceId); // Move to the previous device
        }
    });

    // Touch navigation for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    $(document).on("touchstart", function (event) {
        touchStartX = event.originalEvent.changedTouches[0].screenX;
    });

    $(document).on("touchend", function (event) {
        touchEndX = event.originalEvent.changedTouches[0].screenX;
        handleDeviceSwipe();
    });

    function handleDeviceSwipe() {
        const swipeThreshold = 50; // Minimum swipe distance
        if (touchEndX < touchStartX - swipeThreshold && nextDeviceId) {
            navigateToDevice(nextDeviceId); // Swipe left: Move to the next device
        } else if (touchEndX > touchStartX + swipeThreshold && prevDeviceId) {
            navigateToDevice(prevDeviceId); // Swipe right: Move to the previous device
        }
    }

    // iot-device scripts
    activate_iot_device_list_data();
    function activate_iot_device_list_data(){
        $("#search-device").on( "change", function() {
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            var selectValue = $("#select-todo").val();
            // Remove or Update the parameters
            if (selectValue) urlParams.set("_select_todo", selectValue);
            urlParams.set("_search", $(this).val());
            urlParams.set("paged", 1);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
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
            // Remove or update the parameters
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
                    '_site_id': $("#site-id").val(),
                    '_temperature_offset': $("#temperature-offset").val(),
                    '_record_frequency': $("#record-frequency").val(),
                    '_records_removed': $("#records-removed").val(),
                },
                success: function (response) {
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
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or Update the parameters
            urlParams.delete("_device_id");
            urlParams.set("paged", 1);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
        });
    }
});

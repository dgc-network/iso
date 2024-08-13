jQuery(document).ready(function($) {
    // JavaScript to detect mobile browser
    if (/Mobi/.test(navigator.userAgent)) {
        // User is on a mobile device
        $('.mobile-content').show();
    } else {
        // User is not on a mobile device, send one-time password via Line
        $('.desktop-content').show();
    }

    $("#user-email-input").on( "change", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'send_one_time_password',
                '_user_email': $(this).val(),
            },
            success: function (response) {
                if (response.success) {
                    $('#otp-input-div').show();
                    console.log("Line User ID:", response.line_user_id); 
                    $('#line-user-id-input').val(response.line_user_id);
                    //alert("Success!");
                } else {
                    alert("Error: " + response.error);
                }
            },
            error: function (error) {
                console.error(error);
                alert("An error occurred while processing your request. Please try again later.");
            }
        });
    });

    $("#one-time-password-desktop-input").on( "change", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'one_time_password_desktop_submit',
                '_one_time_password': $(this).val(),
                '_line_user_id': $('#line-user-id-input').val(),
            },
            success: function (response) {
                if (response.success) {
                    window.location.replace("/");
                    //alert("Success!");
                } else {
                    alert("Error: " + response.error);
                }
            },
            error: function (error) {
                console.error(error);
                //alert(error);
            }
        });            
    });            

    $("#wp-login-submit").on( "click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'wp_login_submit',
                '_display_name': $('#display-name').val(),
                '_user_email': $('#user-email').val(),
                '_site_id': $('#site-id').val(),
                '_log': $('#log').val(),
                '_pwd': $('#pwd').val(),
                '_rememberme': $('#rememberme').val(),
            },
            success: function (response) {
                if (response.success) {
                    window.location.replace("/");
                    //alert("Success!");
                } else {
                    alert("Error: " + response.error);
                }
            },
            error: function (error) {
                console.error(error);
                //alert(error);
            }
        });            
    });
    
    $("#select-site").on("change", function() {
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

})


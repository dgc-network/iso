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
                alert(error);
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
                alert(error);
            }
        });            
    });
/*
    $("#select-nda-site").on("change", function() {
        // Get the selected value from the dropdown
        var siteID = $(this).val();
        alert('ID:'+siteID);

        // Check if a site is selected
        if (siteID) {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url, // Ensure this is set in your localized script
                data: {
                    'action': 'get_site_content', // Define a custom action in your functions.php
                    'site_id': siteID,
                },
                success: function(response) {
                    if(response.success) {
                        // Display the post content in a designated div or element
                        $("#site-content").html(response.data.content);
                    } else {
                        // Handle the case where no content is returned or an error occurred
                        $("#site-content").html('<p>No content found for the selected site.</p>');
                    }
                },
                error: function(error) {
                    console.error(error);
                    alert("An error occurred while retrieving the site content.");
                }
            });
        } else {
            // Clear the content area if no site is selected
            $("#site-content").empty();
        }
    });
*/    
});


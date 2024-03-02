jQuery(document).ready(function($) {
/*
    // Check if the user is on a mobile device
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        // User is on a mobile device
        $('.desktop-content').hide();
    } else {
        // User is on a desktop
        $('.mobile-content').hide();
    }
*/
    // JavaScript to detect mobile browser
    if (/Mobi/.test(navigator.userAgent)) {
        // User is on a mobile device
        $('.mobile-content').show();
        //console.log('User is on a mobile device.');
    } else {
        // User is not on a mobile device, send one-time password via email
        $('.desktop-content').show();
        //console.log('User is not on a mobile device. Send one-time password via email.');

        // You can use AJAX to send a request to your server to trigger the email sending
        // Example using jQuery
        // $.post('your_email_sending_endpoint.php', { oneTimePassword: <?php echo $one_time_password; ?> });
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
                    $('#line-user-id-input').val(response.line_user_id);
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

    $("#one-time-password-input").on( "change", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'submit_one_time_password',
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

})

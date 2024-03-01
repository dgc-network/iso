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
        console.log('User is on a mobile device.');
        $('.desktop-content').hide();
    } else {
        // User is not on a mobile device, send one-time password via email
        console.log('User is not on a mobile device. Send one-time password via email.');
        $('.mobile-content').hide();

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
                $('#otp-input-div').show();

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
            },
            success: function (response) {
                window.location.replace("?_search="+$(this).val());
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });            
    });            

})

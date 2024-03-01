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

    $("#user-email").on( "change", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'send_one_time_password',
                '_user_email': $(this).val(),
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

        window.location.replace("?_search="+$(this).val());
        $(this).val('');
    });


})

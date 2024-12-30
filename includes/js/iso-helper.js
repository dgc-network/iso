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

    $(document).ready(function () {
        const canvas = document.getElementById('signature-pad');
        if (canvas) {
            canvas.width = window.innerWidth - 10;
    
            const context = canvas.getContext('2d');
            let isDrawing = false;
    
            // Configure drawing styles
            context.strokeStyle = "#000000";
            context.lineWidth = 2;
    
            // Helper function to get touch position
            const getCanvasPosition = (touch) => {
                const rect = canvas.getBoundingClientRect();
                return {
                    x: touch.clientX - rect.left,
                    y: touch.clientY - rect.top,
                };
            };
    
            // Mouse Events
            $(canvas).on('mousedown', function (e) {
                isDrawing = true;
                context.beginPath();
                context.moveTo(e.offsetX, e.offsetY);
            });
    
            $(canvas).on('mousemove', function (e) {
                if (isDrawing) {
                    context.lineTo(e.offsetX, e.offsetY);
                    context.stroke();
                }
            });
    
            $(document).on('mouseup', function () {
                isDrawing = false;
            });
    
            // Touch Events
            $(canvas).on('touchstart', function (e) {
                e.preventDefault();
                isDrawing = true;
                const touchPosition = getCanvasPosition(e.touches[0]);
                context.beginPath();
                context.moveTo(touchPosition.x, touchPosition.y);
            });
    
            $(canvas).on('touchmove', function (e) {
                e.preventDefault();
                if (isDrawing) {
                    const touchPosition = getCanvasPosition(e.touches[0]);
                    context.lineTo(touchPosition.x, touchPosition.y);
                    context.stroke();
                }
            });
    
            $(document).on('touchend', function () {
                isDrawing = false;
            });
    
            // Clear button functionality
            $('#clear-signature').on('click', function () {
                context.clearRect(0, 0, canvas.width, canvas.height);
            });
    
            // Redraw button functionality
            $('#redraw-signature').on('click', function () {
                $('#signature-pad-div').show();
                $('#signature-image-div').hide();
            });
        }
    });
});


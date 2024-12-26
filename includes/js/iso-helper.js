jQuery(document).ready(function ($) {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.editor-content', // Target the textarea by ID
            height: 400,
            menubar: true,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar:
                'undo redo | formatselect | bold italic backcolor | ' +
                'alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | removeformat | help',
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
        });
    }
});

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

    $("#nda-submit").on("click", function () {
        const dataURL = canvas.toDataURL('image/png');
        //console.log("Signature saved as:", dataURL); // You can also use this URL for further processing

        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_NDA_assignment',
                '_site_id': $("#select-nda-site").val(),
                '_unified_number': $("#unified-number").val(),
                '_display_name': $("#display-name").val(),
                '_identity_number': $("#identity-number").val(),
                '_signature_image': dataURL,
                '_nda_date': $("#nda-date").val(),
                '_user_id': $("#user-id").val(),
            },
            success: function (response) {
                console.log(response);
                window.location.replace('/');
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });            
    });

    $("#nda-exit").on("click", function () {
        window.location.replace('/');
    });

    $("#select-nda-site").on("change", function() {
        // Get the selected value from the dropdown
        var siteID = $(this).val();
        //alert('ID:'+siteID);

        // Check if a site is selected
        if (siteID) {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url, // Ensure this is set in your localized script
                data: {
                    'action': 'get_site_profile_content', // Define a custom action in your functions.php
                    'site_id': siteID,
                },
                success: function(response) {
                    if(response.success) {
                        // Display the post content in a designated div or element
                        $("#site-content").html(response.data.content);
                        console.log(response.data.content);
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
/*    
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
            $('#signature-pad').on('mousedown', function (e) {
                isDrawing = true;
                context.beginPath();
                context.moveTo(e.offsetX, e.offsetY);
            });
    
            $('#signature-pad').on('mousemove', function (e) {
                if (isDrawing) {
                    context.lineTo(e.offsetX, e.offsetY);
                    context.stroke();
                }
            });
    
            $(document).on('mouseup', function () {
                isDrawing = false;
            });
    
            // Touch Events
            canvas.addEventListener(
                'touchstart',
                (e) => {
                    e.preventDefault();
                    isDrawing = true;
                    const touchPosition = getCanvasPosition(e.touches[0]);
                    context.beginPath();
                    context.moveTo(touchPosition.x, touchPosition.y);
                },
                { passive: false }
            );
    
            canvas.addEventListener(
                'touchmove',
                (e) => {
                    e.preventDefault();
                    if (isDrawing) {
                        const touchPosition = getCanvasPosition(e.touches[0]);
                        context.lineTo(touchPosition.x, touchPosition.y);
                        context.stroke();
                    }
                },
                { passive: false }
            );
    
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
/*    
    const canvas = document.getElementById('signature-pad');
    if (canvas) {
        // Set canvas dimensions
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
        canvas.addEventListener('mousedown', (e) => {
            isDrawing = true;
            context.beginPath();
            context.moveTo(e.offsetX, e.offsetY);
        });
    
        canvas.addEventListener('mousemove', (e) => {
            if (isDrawing) {
                context.lineTo(e.offsetX, e.offsetY);
                context.stroke();
            }
        });
    
        document.addEventListener('mouseup', () => {
            isDrawing = false;
        });
    
        // Touch Events
        canvas.addEventListener(
            'touchstart',
            (e) => {
                e.preventDefault();
                isDrawing = true;
                const touchPosition = getCanvasPosition(e.touches[0]);
                context.beginPath();
                context.moveTo(touchPosition.x, touchPosition.y);
            },
            { passive: false }
        );
    
        canvas.addEventListener(
            'touchmove',
            (e) => {
                e.preventDefault();
                if (isDrawing) {
                    const touchPosition = getCanvasPosition(e.touches[0]);
                    context.lineTo(touchPosition.x, touchPosition.y);
                    context.stroke();
                }
            },
            { passive: false }
        );
    
        document.addEventListener('touchend', () => {
            isDrawing = false;
        });
    
        // Clear button functionality
        const clearButton = document.getElementById('clear-signature');
        if (clearButton) {
            clearButton.addEventListener('click', () => {
                context.clearRect(0, 0, canvas.width, canvas.height);
            });
        }
    
        // Redraw button functionality
        const redrawButton = document.getElementById('redraw-signature');
        if (redrawButton) {
            redrawButton.addEventListener('click', () => {
                const signaturePadDiv = document.getElementById('signature-pad-div');
                const signatureImageDiv = document.getElementById('signature-image-div');
    
                if (signaturePadDiv) signaturePadDiv.style.display = 'block';
                if (signatureImageDiv) signatureImageDiv.style.display = 'none';
            });
        }
    }
    
/*
    const canvas = document.getElementById('signature-pad');
    if (canvas) {
        canvas.width = window.innerWidth-10;

        const context = canvas.getContext('2d');
        let isDrawing = false;

        // Set up drawing styles
        context.strokeStyle = "#000000";
        context.lineWidth = 2;

        // Mouse Events for drawing
        $('#signature-pad').mousedown(function(e) {
            isDrawing = true;
            context.beginPath();
            context.moveTo(e.offsetX, e.offsetY);
        });

        $('#signature-pad').mousemove(function(e) {
            if (isDrawing) {
                context.lineTo(e.offsetX, e.offsetY);
                context.stroke();
            }
        });

        $(document).mouseup(function() {
            isDrawing = false;
        });

        // Get canvas offset for touch position calculations
        const getCanvasPosition = (touch) => {
            const rect = canvas.getBoundingClientRect();
            return {
                x: touch.clientX - rect.left,
                y: touch.clientY - rect.top
            };
        };

        // Touch start event
        canvas.addEventListener('touchstart', function(e) {
            e.preventDefault();
            isDrawing = true;
            const touchPosition = getCanvasPosition(e.touches[0]);
            context.beginPath();
            context.moveTo(touchPosition.x, touchPosition.y);
        }, { passive: false });
        
        // Touch move event
        canvas.addEventListener('touchmove', function(e) {
            e.preventDefault();
            if (isDrawing) {
                const touchPosition = getCanvasPosition(e.touches[0]);
                context.lineTo(touchPosition.x, touchPosition.y);
                context.stroke();
            }
        }, { passive: false });

        $(document).on('touchend', function() {
            isDrawing = false;
        });

        // Clear button functionality
        $('#clear-signature').click(function() {
            context.clearRect(0, 0, canvas.width, canvas.height);
        });

        // Redraw button functionality
        $('#redraw-signature').click(function() {
            $('#signature-pad-div').show();
            $('#signature-image-div').hide();
        });
    }
*/
});


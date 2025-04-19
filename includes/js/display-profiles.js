// display-profiles
jQuery(document).ready(function($) {

    // Function to check if the string is a valid URL
    function isURL(str) {
        var pattern = /^(http|https):\/\/[^ "]+$/;
        return pattern.test(str);
    }

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

    // Function to check if the string is a valid URL
    $("#select-profile").on("change", function() {
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Remove or Update the parameters
        urlParams.delete("_search");
        urlParams.set("_select_profile", $(this).val());
        urlParams.set("paged", 1);
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    });

    // my-profile
    activate_my_profile_data();
    function activate_my_profile_data(){
        $("#my-transaction-button").on("click", function () {
            $("#transaction-data").toggle();
        })

        $("#my-profile-submit").on("click", function () {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_my_profile_data',
                    '_display_name': $("#display-name").val(),
                    '_user_email': $("#user-email").val(),
                    '_phone_number': $("#phone-number").val(),
                    '_gemini_api_key': $("#gemini-api-key").val(),
                },
                success: function (response) {
                    console.log(response);

                    // Show the custom alert message
                    var alertBox = $("<div class='custom-alert'>Data update success!</div>");
                    $("body").append(alertBox);

                    // Center the alert box
                    alertBox.css({
                        position: "fixed",
                        top: "50%",
                        left: "50%",
                        transform: "translate(-50%, -50%)",
                    });

                    alertBox.fadeIn(500).delay(3000).fadeOut(500, function() {
                        $(this).remove();
                    });
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });            
        });

        $('[id^="edit-my-job-"]').on("click", function () {
            const job_id = this.id.substring(12);
        
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_my_job_dialog_data',
                    '_job_id': job_id,
                },
                success: function (response) {
                    $("#my-job-dialog").html(response.html_contain);
        
                    $("#my-job-dialog").dialog("option", "buttons", {
                        "Authorization": function () {
                            $("#authorization-settings").show();
                            $("#recurrence-settings").hide();
        
                            // Hide Authorization button only
                            $(".ui-dialog-buttonpane button:contains('Authorization')").hide();
        
                            // Add Set/Unset next to it
                            insertSetUnsetButtons('authorization');
                        },
                        "Recurrence": function () {
                            $("#recurrence-settings").show();
                            $("#authorization-settings").hide();
        
                            // Hide Recurrence button only
                            $(".ui-dialog-buttonpane button:contains('Recurrence')").hide();
        
                            // Add Set/Unset next to it
                            insertSetUnsetButtons('recurrence');
                        }
                    });
        
                    $("#my-job-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });
        
        function insertSetUnsetButtons(context) {
            const $buttonPane = $(".ui-dialog-buttonpane");
            const $existing = $("#custom-set-unset");
        
            // Remove any previous ones
            if ($existing.length) $existing.remove();
        
            const $btnGroup = $(`
                <span id="custom-set-unset" style="display:inline-block; margin-right: 10px;">
                    <button type="button" id="set-btn" class="ui-button ui-corner-all ui-widget">Set</button>
                    <button type="button" id="unset-btn" class="ui-button ui-corner-all ui-widget">Unset</button>
                </span>
            `);
        
            // Insert before whichever button is still visible
            if (context === 'authorization') {
                $btnGroup.insertBefore($buttonPane.find("button:contains('Recurrence')"));
            } else {
                $btnGroup.insertBefore($buttonPane.find("button:contains('Authorization')"));
            }
        
            // Button actions
            $("#set-btn").on("click", function () {
                console.log("Set clicked for", context);
            });
        
            $("#unset-btn").on("click", function () {
                console.log("Unset clicked for", context);
        
                // Hide the active section
                if (context === 'authorization') {
                    $("#authorization-settings").hide();
                    $(".ui-dialog-buttonpane button:contains('Authorization')").show();
                } else {
                    $("#recurrence-settings").hide();
                    $(".ui-dialog-buttonpane button:contains('Recurrence')").show();
                }
        
                $("#custom-set-unset").remove();
            });
        }
/*        
        $('[id^="edit-my-job-"]').on("click", function () {
            const job_id = this.id.substring(12);
        
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_my_job_dialog_data',
                    '_job_id': job_id,
                },
                success: function (response) {
                    $("#my-job-dialog").html(response.html_contain);
        
                    // Set up base buttons
                    $("#my-job-dialog").dialog("option", "buttons", {
                        "Authorization": function () {
                            $("#authorization-settings").show();
                            $("#recurrence-settings").hide();
                            showSetUnsetButtons('authorization');
                        },
                        "Recurrence": function () {
                            $("#recurrence-settings").show();
                            $("#authorization-settings").hide();
                            showSetUnsetButtons('recurrence');
                        }
                    });
        
                    $("#my-job-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });
        
        // Helper to add custom Set/Unset buttons
        function showSetUnsetButtons(context) {
            // Remove existing buttons
            $(".ui-dialog-buttonpane button").hide();
        
            // Create container if not exists
            if ($("#custom-button-container").length === 0) {
                $(".ui-dialog-buttonpane").append('<div id="custom-button-container" style="position:absolute; left:10px; bottom:5px;"></div>');
            }
        
            // Add buttons
            $("#custom-button-container").html(`
                <button type="button" id="set-btn" class="ui-button ui-corner-all ui-widget">Set</button>
                <button type="button" id="unset-btn" class="ui-button ui-corner-all ui-widget">Unset</button>
            `);
        
            // Optional: Bind their logic
            $("#set-btn").on("click", function () {
                console.log("Set clicked for", context);
                // You can add additional logic here (e.g., submit or save)
            });
        
            $("#unset-btn").on("click", function () {
                console.log("Unset clicked for", context);
                // Hide the section and bring back dialog buttons
                if (context === 'authorization') {
                    $("#authorization-settings").hide();
                } else {
                    $("#recurrence-settings").hide();
                }
        
                // Show original buttons again
                $(".ui-dialog-buttonpane button").show();
                $("#custom-button-container").remove();
            });
        }
/*        
        $('[id^="edit-my-job-"]').on("click", function () {
            const job_id = this.id.substring(12);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_my_job_dialog_data',
                    '_job_id': job_id,
                },
                success: function (response) {
                    $("#my-job-dialog").html(response.html_contain);
        
                    let isAuthToggled = false;
                    let isRecToggled = false;
        
                    function updateDialogButtons() {
                        let authLabel = isAuthToggled ? "Set" : "Authorization";
                        let recLabel = isRecToggled ? "Set" : "Recurrence";
        
                        $("#my-job-dialog").dialog("option", "buttons", {
                            [authLabel]: function () {
                                $("#authorization-settings").toggle();
                                $("#recurrence-settings").hide();
        
                                isAuthToggled = !isAuthToggled;
                                isRecToggled = false; // reset other
                                updateDialogButtons();
                            },
                            [recLabel]: function () {
                                $("#recurrence-settings").toggle();
                                $("#authorization-settings").hide();
        
                                isRecToggled = !isRecToggled;
                                isAuthToggled = false; // reset other
                                updateDialogButtons();
                            }
                        });
                    }
        
                    updateDialogButtons();
                    $("#my-job-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });
/*        
        $('[id^="edit-my-job-"]').on("click", function () {
            const job_id = this.id.substring(12);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_my_job_dialog_data',
                    '_job_id': job_id,
                },
                success: function (response) {
                    $("#my-job-dialog").html(response.html_contain);
        
                    let isAuthToggled = false;
        
                    $("#my-job-dialog").dialog("option", "buttons", {
                        "Authorization": function () {
                            $("#authorization-settings").toggle();
                            $("#recurrence-settings").hide();
        
                            // Toggle label between "Authorization" and "Set"
                            const currentButtons = $("#my-job-dialog").dialog("option", "buttons");
                            const newLabel = isAuthToggled ? "Authorization" : "Set";
                            isAuthToggled = !isAuthToggled;
        
                            // Rebuild the button set with updated label
                            $("#my-job-dialog").dialog("option", "buttons", {
                                [newLabel]: currentButtons["Authorization"], // keep same function
                                "Recurrence": currentButtons["Recurrence"]
                            });
                        },
                        "Recurrence": function () {
                            $("#recurrence-settings").toggle();
                            $("#authorization-settings").hide();
                        }
                    });
        
                    $("#my-job-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });
/*        
        $('[id^="edit-my-job-"]').on("click", function () {
            const job_id = this.id.substring(12);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_my_job_dialog_data',
                    '_job_id': job_id,
                },
                success: function (response) {
                    $("#my-job-dialog").html(response.html_contain);
                    $("#my-job-dialog").dialog("option", "buttons", {
                        "Authorization": function () {
                            $("#authorization-settings").toggle();
                            $("#recurrence-settings").hide();
                        },
                        "Recurrence": function () {
                            $("#recurrence-settings").toggle();
                            $("#authorization-settings").hide();
                        },
                    });
                    $("#my-job-dialog").dialog('open');

                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });
*/
        $("#my-job-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {},
            open: function() {
                // Ensure scroll only affects content area
                $(this).css({
                    'overflow-y': 'auto'
                });
            }
        });

        $('[id^="edit-my-action-"]').on("click", function () {
            const action_id = this.id.substring(15);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_my_action_dialog_data',
                    '_action_id': action_id,
                },
                success: function (response) {
                    $("#my-action-dialog").html(response.html_contain);
                    $("#my-action-dialog").dialog("option", "buttons", {
                        "Update": function () {
                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'set_my_action_dialog_data',
                                    _action_id: $("#action-id").val(),
                                    _is_action_authorized: $("#is-action-authorized").is(":checked") ? 1 : 0,
                                    _interval_setting: $("#interval-setting").val(),
                                },
                                success: function (response) {
                                    console.log(response);
                                    window.location.replace(window.location.href);
                                },
                                error: function (error) {
                                    console.error(error);
                                    alert(error);
                                }
                            });
                        },
                        "Cancel": function () {
                            $("#my-action-dialog").dialog('close');
                        },
                    });
                    $("#my-action-dialog").dialog('open');

                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#my-action-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });

        if ($("#interval-setting").val()) {
            $("#recurrence-start-time-div").show();
        }

        $("#interval-setting").on("change", function () {
            if ($(this).val()) {
                $("#recurrence-start-time-div").show();
            } else {
                $("#recurrence-start-time-div").hide();
            }
        });

        // exception notifiction
        $("#my-exception-notification-setting-label").on("click", function () {
            $("#my-exception-notification-setting").toggle();
        });
        
        $('[id^="new-exception-notification-setting-"]').on("click", function () {
            const device_id = this.id.substring(35);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_exception_notification_setting_dialog_data',
                    _device_id: device_id,
                },
                success: function (response) {
                    $("#exception-notification-setting-dialog").html(response.html_contain);
                    $("#exception-notification-setting-dialog").dialog("option", "buttons", {
                        "Add": function () {
                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'set_exception_notification_setting_dialog_data',
                                    '_device_id': device_id,
                                    '_employee_id': $("#employee-id").val(),
                                    '_max_value': $("#max-value").val(),
                                    '_min_value': $("#min-value").val(),
                                    _is_once_daily : $("#is-once-daily").is(":checked") ? 1 : 0,
                                },
                                success: function (response) {
                                    console.log(response);
                                    window.location.replace(window.location.href);
                                },
                                error: function (error) {
                                    console.error(error);
                                    alert(error);
                                }
                            });
                        },
                        "Cancel": function () {
                            $("#exception-notification-setting-dialog").dialog('close');
                        },
                    });
                    $("#exception-notification-setting-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });
    
        $('[id^="edit-exception-notification-setting-"]').on("click", function () {
            const setting_id = this.id.substring(36);
            // Ensure the dialog is initialized before use
            if (!$("#exception-notification-setting-dialog").hasClass('ui-dialog-content')) {
                $("#exception-notification-setting-dialog").dialog({
                    autoOpen: false, // Start closed
                    modal: true, // Makes it a modal dialog
                    width: 390 // Set width or adjust as needed
                });
            }

            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_exception_notification_setting_dialog_data',
                    '_setting_id': setting_id,
                },
                success: function (response) {
                    $("#exception-notification-setting-dialog").html(response.html_contain);
                    $("#exception-notification-setting-dialog").dialog("option", "buttons", {
                        "Save": function () {
                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'set_exception_notification_setting_dialog_data',
                                    '_setting_id': setting_id,
                                    '_device_id': $("#device-id").val(),
                                    '_employee_id': $("#employee-id").val(),
                                    '_max_value': $("#max-value").val(),
                                    '_min_value': $("#min-value").val(),
                                    _is_once_daily : $("#is-once-daily").is(":checked") ? 1 : 0,
                                },
                                success: function (response) {
                                    console.log(response);
                                    window.location.replace(window.location.href);
                                },
                                error: function (error) {
                                    console.error(error);
                                    alert(error);
                                }
                            });
                        },
                        "Delete": function () {
                            if (window.confirm("Are you sure you want to delete this setting?")) {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'del_exception_notification_setting_dialog_data',
                                        '_setting_id': setting_id,
                                    },
                                    success: function (response) {
                                        console.log(response);
                                        window.location.replace(window.location.href);
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            }
                        },        
                    });
                    $("#exception-notification-setting-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#exception-notification-setting-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });

    }

    // site-profile
    activate_site_profile_data();
    function activate_site_profile_data(){
        $("#site-image-container").on("click", function() {
            $("#site-image-container").hide();
            $("#site-image-url").show();
        });

        $("#set-image-url").on("click", function() {
            $("#site-image-container").show();
            $("#site-image-url").hide();
            if (isURL($('#image-url').val())) {
                $("#site-image-container").html('<img src="'+$('#image-url').val()+'" style="object-fit:cover; width:250px; height:250px;">');
            } else {
                $("#site-image-container").html('<a href="#" id="custom-image-href">Set image URL</a>');
            }
        });

        // Show the site-hint when the user starts typing
        $('#site-title').on('input', function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_site_list_data',
                    '_site_title': $(this).val(),
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
        });

        $("#site-profile-submit").on("click", function () {
            // Initialize an empty array to store the key-value pairs
            const keyValuePairs = [];

            // Select all elements with the specified class and iterate over them
            $('.embedded-item-class').each(function() {
                // Get the key from the data attribute
                const key = $(this).data('key');
                let value;
                // Check if the element is a checkbox or radio button
                if ($(this).is(':checkbox') || $(this).is(':radio')) {
                    // Set the value to 1 if checked, otherwise set it to 0
                    value = $(this).is(':checked') ? 1 : 0;
                } else {
                    // Get the value (for input elements) or text content (for others)
                    value = $(this).val() || $(this).text();
                }
                // Add the key-value pair to the array
                keyValuePairs.push({ [key]: value });
            });
            // Now, keyValuePairs contains the key-value pairs of all elements with the specified class
            console.log(keyValuePairs);
    
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_site_profile_data',
                    '_site_id': $("#site-id").val(),
                    '_site_title': $("#site-title").val(),
                    '_image_url': $("#image-url").val(),
                    _keyValuePairs : keyValuePairs,
                    '_site_content': $("#site-content").val(),
                    '_company_phone': $("#company-phone").val(),
                    '_company_address': $("#company-address").val(),
                    '_unified_number': $("#unified-number").val(),
                },
                success: function (response) {
                    // Show the custom alert message
                    var alertBox = $("<div class='custom-alert'>Data update success!</div>");
                    $("body").append(alertBox);

                    // Center the alert box
                    alertBox.css({
                        position: "fixed",
                        top: "50%",
                        left: "50%",
                        transform: "translate(-50%, -50%)",
                    });

                    alertBox.fadeIn(500).delay(3000).fadeOut(500, function() {
                        $(this).remove();
                    });
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });            
        });            

        $('[id^="edit-site-user-"]').on("click", function () {
            const user_id = this.id.substring(15);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_site_user_dialog_data',
                    '_user_id': user_id,
                },
                success: function (response) {
                    $("#site-user-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#site-user-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_site_user_dialog_data',
                                        '_user_id': $("#user-id").val(),
                                        '_display_name': $("#display-name").val(),
                                        '_user_email': $("#user-email").val(),
                                        '_is_site_admin': $('#is-site-admin-setting').is(":checked") ? 1 : 0,
                                        '_select_site': $("#select-site").val(),
                                    },
                                    success: function (response) {
                                        $("#site-user-dialog").dialog('close');
                                        window.location.replace(window.location.href);
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this site user?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_site_user_dialog_data',
                                            '_user_id': $("#user-id").val(),
                                        },
                                        success: function (response) {
                                            $("#site-user-dialog").dialog('close');
                                            get_site_profile_data();
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

                    $("#site-user-dialog").dialog('open');

                    $('[id^="check-user-action-"]').on("click", function () {
                        if (window.confirm("Are you sure you want to change this setting?")) {
                            const action_id = this.id.substring(18);
                            $("#is-user-action-"+action_id).prop("checked", function(i, value) {
                                return !value; // Toggle the checkbox state
                            });

                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'set_site_user_action_data',
                                    _action_id : action_id,
                                    _user_id : user_id,
                                    _is_user_action : $("#is-user-action-"+action_id).is(":checked") ? 1 : 0,
                                },
                                success: function (response) {
                                    console.log(response);
                                },
                                error: function (error) {
                                    console.error(error);
                                    alert(error);
                                }
                            });
                        }
                    });
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#site-user-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });    
    }

    function get_site_profile_data(){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_site_profile_data',
                '_site_id': site_id,
            },
            success: function (response) {
                $("#result-container").html(response.html_contain);
                activate_site_profile_data();
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    // site-actions
    activate_site_action_list_data();
    function activate_site_action_list_data(){
        $("#site-action-search").on( "change", function() {
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            var selectValue = $("#select-profile").val();
            // Remove or Update the parameters
            if (selectValue) urlParams.set("_select_profile", selectValue);
            urlParams.set("_action_search", $(this).val());
            urlParams.set("paged", 1);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
        });

        $("#new-site-action").on("click", function() {
            const pathSegments = window.location.pathname.split('/'); // Split the URL path
            const pagedIndex = pathSegments.indexOf("page"); // Find the "page" keyword
            let paged = null;
            if (pagedIndex !== -1 && pathSegments[pagedIndex + 1]) {
                paged = pathSegments[pagedIndex + 1]; // Get the next segment as the paged value
            }
            console.log(paged); // Output: "2" if the URL contains "/page/2/"
                        
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_site_action_dialog_data',
                    '_paged': paged,
                    '_doc_id': $("#doc-id").val(),
                },
                success: function (response) {
                    $("#site-action-list").html(response.html_contain);
                    activate_site_action_list_data();
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-site-action-"]').on("click", function () {
            const action_id = this.id.substring(17);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_site_action_dialog_data',
                    '_action_id': action_id,
                },
                success: function (response) {
                    $("#site-action-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        const pathSegments = window.location.pathname.split('/'); // Split the URL path
                        const pagedIndex = pathSegments.indexOf("page"); // Find the "page" keyword
                        let paged = null;
                        if (pagedIndex !== -1 && pathSegments[pagedIndex + 1]) {
                            paged = pathSegments[pagedIndex + 1]; // Get the next segment as the paged value
                        }
                        console.log(paged); // Output: "2" if the URL contains "/page/2/"
                        $("#site-action-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_site_action_dialog_data',
                                        '_action_id': action_id,
                                        '_paged': paged,
                                        '_doc_id': $("#doc-id").val(),
                                        '_action_number': $("#action-number").val(),
                                        '_action_title': $("#action-title").val(),
                                        '_action_content': $("#action-content").val(),
                                        '_action_connector': $("#action-connector").val(),
                                        '_next_job': $("#next-job").val(),
                                    },
                                    success: function (response) {
                                        $("#site-action-dialog").dialog('close');
                                        //window.location.replace(window.location.href);
                                        $("#site-action-list").html(response.html_contain);
                                        activate_site_action_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this site action?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_site_action_dialog_data',
                                            '_action_id': action_id,
                                            '_paged': paged,
                                            '_doc_id': $("#doc-id").val(),
                                        },
                                        success: function (response) {
                                            $("#site-action-dialog").dialog('close');
                                            $("#site-action-list").html(response.html_contain);
                                            activate_site_action_list_data();
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
                    $("#site-action-dialog").dialog('open');
                    activate_site_action_dialog_data(action_id);
                    activate_action_users_data(action_id);
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#site-action-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });    
    }

    function activate_site_action_dialog_data(action_id){
        $("#action-connector").on( "change", function() {
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'post',
                data: {
                    action: 'get_site_action_dialog_data',
                    '_action_id': action_id,
                    _action_connector: $(this).val(),
                    '_next_job': $("#next-job").val(),
                },
                success: function (response) {
                    $("#next-job").html(response.next_job);
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                },
            });
        });
    }

    // action-user
    function activate_action_users_data(action_id=false) {
        $("#new-action-user").on("click", function() {
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_new_action_user',
                },
                success: function (response) {
                    $("#new-action-users-dialog").html(response.html_contain);
                    $("#new-action-users-dialog").dialog('open');
                    $('[id^="add-action-user-"]').on("click", function () {
                        if (window.confirm("Are you sure you want to add this new user for action?")) {
                            const user_id = this.id.substring(16);
                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'set_action_user_data',
                                    '_action_id': action_id,
                                    '_user_id': user_id,
                                },
                                success: function (response) {
                                    console.log(response)
                                    $("#new-action-users-dialog").dialog('close');
                                    $("#action-user-list").html(response.html_contain);
                                    activate_action_users_data(action_id);
                    
                                },
                                error: function (error) {
                                    console.error(error);
                                    alert(error);
                                }
                            });
                        }
                    });                        
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });

        $('[id^="del-action-user-"]').on("click", function () {
            if (window.confirm("Are you sure you want to delete this action user?")) {
                const user_id = this.id.substring(16);
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'del_action_user_data',
                        '_action_id': action_id,
                        '_user_id': user_id,
                    },
                    success: function (response) {
                        $("#action-user-list").html(response.html_contain);
                        activate_action_users_data(action_id);
                    },
                    error: function (error) {
                        console.error(error);
                        alert(error);
                    }
                });
            }
        });

        $("#new-action-users-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
        });
    }

    // NDA assignment
    $("#nda-submit").on("click", function () {
        const dataURL = canvas.toDataURL('image/png');
        //console.log("Signature saved as:", dataURL); // You can also use this URL for further processing

        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_NDA_assignment',
                '_user_id': $("#user-id").val(),
                '_site_id': $("#select-nda-site").val(),
                '_display_name': $("#display-name").val(),
                //'_identity_number': $("#identity-number").val(),
                '_nda_signature': dataURL,
                '_nda_content': $("#site-content").val(),
                '_submit_date': $("#submit-date").val(),
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

    $("#nda-approve").on("click", function () {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_NDA_assignment',
                '_user_id': $("#user-id").val(),
                '_site_id': $("#site-id").val(),
                '_approve_date': $("#nda-date").val(),
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
    
    $("#nda-reject").on("click", function () {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_NDA_assignment',
                '_user_id': $("#user-id").val(),
                '_site_id': $("#nda-site").val(),
                '_reject_date': $("#nda-date").val(),
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
                    'action': 'get_site_NDA_content', // Define a custom action in your functions.php
                    'site_id': siteID,
                },
                success: function(response) {
                    if(response.success) {
                        // Display the post content in a designated div or element
                        $("#site-content").html(response.data.content);
                        $("#unified-number").val(response.data.unified_number);
                        //console.log(response.data.content);
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
});

// display-profiles
jQuery(document).ready(function($) {

    // Function to check if the string is a valid URL
    function isURL(str) {
        var pattern = /^(http|https):\/\/[^ "]+$/;
        return pattern.test(str);
    }

    $("#select-profile").on("change", function() {
        // Initialize an empty array to store query parameters
        var queryParams = [];
        // Check the selected value for each select element and add it to the queryParams array
        var selectValue = $("#select-profile").val();
        if (selectValue) {
            queryParams.push("_select_profile=" + selectValue);
        }
        // Combine all query parameters into a single string
        var queryString = queryParams.join("&");
        // Redirect to the new URL with all combined query parameters
        window.location.href = "?" + queryString;
    });

    // my-profile
    activate_my_profile_data();
    function activate_my_profile_data(){
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

        $("#my-job-action-list").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
        });

        if ($("#frequence-report-setting").val()) {
            $("#frquence-report-start-time-div").show();
        }

        $("#frequence-report-setting").on("change", function () {
            if ($(this).val()) {
                $("#frquence-report-start-time-div").show();
            } else {
                $("#frquence-report-start-time-div").hide();
            }
        });

        $('[id^="edit-my-job-"]').on("click", function () {
            const doc_id = this.id.substring(12);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_my_job_action_list_data',
                    '_doc_id': doc_id,
                },
                success: function (response) {
                    $("#my-job-action-list").html(response.html_contain);
                    $("#my-job-action-list").dialog('open');

                    $('[id^="edit-my-job-action-"]').on("click", function () {
                        $("#my-job-action-list").dialog('close');
                        const action_id = this.id.substring(19);
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'get_my_job_action_dialog_data',
                                '_action_id': action_id,
                            },
                            success: function (response) {
                                $("#my-job-action-dialog").dialog({
                                    width: 390,
                                    modal: true,
                                    autoOpen: false,
                                    buttons: {
                                        "Update": function () {
                                            $.ajax({
                                                type: 'POST',
                                                url: ajax_object.ajax_url,
                                                dataType: "json",
                                                data: {
                                                    'action': 'set_my_job_action_dialog_data',
                                                    _action_id: $("#action-id").val(),
                                                    _is_action_authorized: $("#is-action-authorized").val(),
                                                    _frequence_report_setting: $("#frequence-report-setting").val(),
                                                    _frequence_report_start_date: $("#frequence-report-start-date").val(),
                                                    _frequence_report_start_time: $("#frequence-report-start-time").val(),
                                                    _prev_start_time: $("#prev-start-time").val(),
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
                                            $("#my-job-action-dialog").dialog('close');
                                        },
                                    }                        
                                });
                                $("#my-job-action-dialog").html(response.html_contain);
                                $("#my-job-action-dialog").dialog('open');
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

        // exception notifiction
        $("#my-exception-notification-setting-label").on("click", function () {
            $("#my-exception-notification-setting").toggle();
        });
        
        $("#new-exception-notification-setting").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_exception_notification_setting_dialog_data',
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
                                    '_device_id': $("#device-id").val(),
                                    '_max_value': $("#max-value").val(),
                                    '_min_value': $("#min-value").val(),
                                    _is_once_daily : $("#is-once-daily").is(":checked") ? 1 : 0,
                                },
                                success: function (response) {
                                    console.log(response);
                                    window.location.replace(window.location.href);
                                    //$("#my-exception-notification-setting").html(response.html_contain);
                                    //$("#exception-notification-setting-dialog").dialog('close');
                                },
                                error: function (error) {
                                    console.error(error);
                                    alert(error);
                                }
                            });
                        },
                        "Cancel": function () {
                            $("#exception-notification-setting-dialog").dialog('close');
                            //window.location.replace(window.location.href);
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
                                    '_max_value': $("#max-value").val(),
                                    '_min_value': $("#min-value").val(),
                                    _is_once_daily : $("#is-once-daily").is(":checked") ? 1 : 0,
                                },
                                success: function (response) {
                                    console.log(response);
                                    //$("#my-exception-notification-setting").html(response.html_contain);
                                    //$("#exception-notification-setting-dialog").dialog('close');
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
                                        //$("#my-exception-notification-setting").html(response.html_contain);
                                        //$("#exception-notification-setting-dialog").dialog('close');
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
/*        
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var selectValue = $("#select-profile").val();
            if (selectValue) {
                queryParams.push("_select_profile=" + selectValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });
*/
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
            $('.sub-item-class').each(function() {
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
                    $("#site-user-dialog").dialog('open');

                    $('[id^="check-user-job-"]').on("click", function () {
                        if (window.confirm("Are you sure you want to change this setting?")) {
                            const doc_id = this.id.substring(15);
                            $("#is-user-doc-"+doc_id).prop("checked", function(i, value) {
                                return !value; // Toggle the checkbox state
                            });

                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'set_site_user_doc_data',
                                    _doc_id : doc_id,
                                    _user_id : user_id,
                                    _is_user_doc : $("#is-user-doc-"+doc_id).is(":checked") ? 1 : 0,
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
/*
        $("#new-user-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
        });
*/
        $("#site-user-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {
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
                            '_is_site_admin': $('#is-site-admin').is(":checked") ? 1 : 0,
                            '_select_site': $("#select-site").val(),
                        },
                        success: function (response) {
                            $("#site-user-dialog").dialog('close');
                            //get_site_profile_data();
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
            }
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

    // site-job
    activate_site_job_list_data();
    function activate_site_job_list_data(){
/*        
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var selectValue = $("#select-profile").val();
            if (selectValue) {
                queryParams.push("_select_profile=" + selectValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });
*/
        $("#search-site-job").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var selectValue = $("#select-profile").val();
            if (selectValue) {
                queryParams.push("_select_profile=" + selectValue);
            }
            var siteJobValue = $("#search-site-job").val();
            if (siteJobValue) {
                queryParams.push("_search=" + siteJobValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
            // Clear the values of all select elements after redirection
            $("#select-profile, #search-site-job").val('');
        });

        $("#new-site-job").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_site_job_dialog_data',
                },
                success: function (response) {
                    window.location.replace(window.location.href);
                    activate_site_job_list_data();
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-site-job-"]').on("click", function () {
            const doc_id = this.id.substring(14);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_site_job_dialog_data',
                    '_doc_id': doc_id,
                },
                success: function (response) {
                    $("#site-job-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#site-job-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_site_job_dialog_data',
                                        '_doc_id': $("#doc-id").val(),
                                        '_job_number': $("#job-number").val(),
                                        '_job_title': $("#job-title").val(),
                                        '_job_content': $("#job-content").val(),
                                        '_department_id': $("#department-id").val(),
                                        '_is_summary_job': $("#is-summary-job").is(":checked") ? 1 : 0,
                                    },
                                    success: function (response) {
                                        $("#site-job-dialog").dialog('close');
                                        window.location.replace(window.location.href);
                                        activate_site_job_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this site job?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_site_job_dialog_data',
                                            '_doc_id': $("#doc-id").val(),
                                        },
                                        success: function (response) {
                                            $("#site-job-dialog").dialog('close');
                                            window.location.replace(window.location.href);
                                            activate_site_job_list_data();
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
                    $("#site-job-dialog").dialog('open');
                    activate_job_action_list_data(doc_id);
                    activate_doc_user_list_data(doc_id);
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#site-job-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });    
    }

    // doc-action
    function activate_job_action_list_data(doc_id=false) {
        $("#new-doc-action").on("click", function() {
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_doc_action_dialog_data',
                    '_doc_id': doc_id,
                },
                success: function (response) {
                    $("#doc-action-list").html(response.html_contain);
                    activate_job_action_list_data(doc_id);
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });

        $('[id^="edit-doc-action-"]').on("click", function () {
            const action_id = this.id.substring(16);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_doc_action_dialog_data',
                    '_action_id': action_id,
                },
                success: function (response) {
                    $("#doc-action-dialog").html(response.html_contain);
                    $("#doc-action-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#doc-action-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {
                "Save": function() {
                    jQuery.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_doc_action_dialog_data',
                            '_doc_id': doc_id,
                            '_action_id': $("#action-id").val(),
                            '_action_title': $("#action-title").val(),
                            '_action_content': $("#action-content").val(),
                            '_next_job': $("#next-job").val(),
                            '_next_leadtime': $("#next-leadtime").val(),
                        },
                        success: function (response) {
                            $("#doc-action-dialog").dialog('close');
                            $("#doc-action-list").html(response.html_contain);
                            activate_job_action_list_data(doc_id);
                        },
                        error: function (error) {
                            console.error(error);                    
                            alert(error);
                        }
                    });            
                },
                "Delete": function() {
                    if (window.confirm("Are you sure you want to delete this doc action?")) {
                        jQuery.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_doc_action_dialog_data',
                                '_doc_id': doc_id,
                                '_action_id': $("#action-id").val(),
                            },
                            success: function (response) {
                                $("#doc-action-dialog").dialog('close');
                                $("#doc-action-list").html(response.html_contain);
                                activate_job_action_list_data(doc_id);
                            },
                            error: function(error){
                                console.error(error);
                                alert(error);
                            }
                        });
                    }
                }
            }
        });
    }

    // doc-user
    function activate_doc_user_list_data(doc_id=false) {
        $("#new-doc-user").on("click", function() {
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_new_user_list',
                },
                success: function (response) {
                    $("#new-user-list-dialog").html(response.html_contain);
                    $("#new-user-list-dialog").dialog('open');
                    $('[id^="add-doc-user-"]').on("click", function () {
                        if (window.confirm("Are you sure you want to add this new user for doc?")) {
                            const user_id = this.id.substring(13);
                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'add_doc_user_data',
                                    '_doc_id': doc_id,
                                    '_user_id': user_id,
                                },
                                success: function (response) {
                                    console.log(response)
                                    $("#new-user-list-dialog").dialog('close');
                                    $("#doc-user-list").html(response.html_contain);
                                    activate_doc_user_list_data(doc_id);
                    
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

        $('[id^="del-doc-user-"]').on("click", function () {
            if (window.confirm("Are you sure you want to delete this doc user?")) {
                const user_id = this.id.substring(13);
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'del_doc_user_data',
                        '_doc_id': doc_id,
                        '_user_id': user_id,
                    },
                    success: function (response) {
                        $("#doc-user-list").html(response.html_contain);
                        activate_doc_user_list_data(doc_id);
                    },
                    error: function (error) {
                        console.error(error);
                        alert(error);
                    }
                });
            }
        });

        $("#new-user-list-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
        });
    }
});

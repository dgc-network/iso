// display-profiles
jQuery(document).ready(function($) {

    // Function to check if the string is a valid URL
    function isURL(str) {
        var pattern = /^(http|https):\/\/[^ "]+$/;
        return pattern.test(str);
    }

    activate_my_profile_data();
    activate_site_profile_data();
    activate_site_job_list_data();
    activate_doc_category_list_data();

    // my-profile scripts
    function activate_my_profile_data(){
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }

            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

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
                    alert("Data update success!");
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
            close: function(event, ui) {
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'get_my_job_list_data',
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
        });

        $('[id^="edit-my-job-"]').on("click", function () {
            const doc_id = this.id.substring(12);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_my_job_action_dialog_data',
                    '_doc_id': doc_id,
                },
                success: function (response) {
                    $("#my-job-action-list").html(response.html_contain);
                    $("#my-job-action-list").dialog('open');

                    $('[id^="check-action-authorize-"]').on("click", function () {
                        const action_id = this.id.substring(23);
                        const radioButton = $("#is-action-authorized-" + action_id);
                    
                        if (window.confirm("Are you sure you want to change this setting?")) {
                            // Toggle the radio button state
                            const isChecked = radioButton.is(":checked");
                            radioButton.prop("checked", !isChecked);
                    
                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'set_action_authorized_data',
                                    _action_id: action_id,
                                    _is_action_authorized: !isChecked ? 1 : 0,
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

        $('[id^="edit-my-notification-"]').on("click", function () {
            const notification_id = this.id.substring(21);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_notification_dialog_data',
                    '_notification_id': notification_id,
                },
                success: function (response) {
                    $("#my-notification-dialog").html(response.html_contain);
                    $("#my-notification-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#my-notification-dialog").dialog({
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
                            'action': 'set_notification_dialog_data',
                            '_notification_id': $("#notification-id").val(),
                            '_user_id': $("#user-id").val(),
                            '_max_temperature': $("#max-temperature").val(),
                            '_max_humidity': $("#max-humidity").val(),
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
                    if (window.confirm("Are you sure you want to delete this notification settings?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_notification_dialog_data',
                                '_notification_id': $("#notification-id").val(),
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
            }
        });    
    }

    // site-profile scripts
    function activate_site_profile_data(){
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }

            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

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
    
        $("#site-title").on("change", function () {
            new_site_title = $(this).val();
            if (window.confirm("Are you sure you want to use "+new_site_title+" as your new site title?")) {
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'set_new_site_by_title',
                        '_new_site_title': new_site_title,
                    },
                    success: function (response) {
                        $("#site-id").val(response.new_site_id);
                    },
                    error: function(error){
                        console.error(error);                    
                    }
                });        
            }
        });
        
        $("#site-profile-submit").on("click", function () {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_site_profile_data',
                    '_site_id': $("#site-id").val(),
                    '_site_title': $("#site-title").val(),
                    '_image_url': $("#image-url").val(),
                },
                success: function (response) {
                    if (response.success) {
                        alert("Data update success!");
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
    
        $("#new-site-user").on("click", function() {
            $("#new-user-dialog").dialog('open');
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
                        const doc_id = this.id.substring(15);
                        // Toggle the checkbox state
                        $("#is-user-doc-"+doc_id).prop("checked", function(i, value) {
                            return !value;
                        });
                        
                        if (window.confirm("Are you sure you want to change this setting?")) {
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

        $("#new-user-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
        });

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

    // site-job scripts
    function activate_site_job_list_data(){
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }

            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

        $("#search-site-job").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
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
                    //$("#result-container").html(response.html_contain);
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
            buttons: {
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
                        },
                        success: function (response) {
                            $("#site-job-dialog").dialog('close');
                            //$("#result-container").html(response.html_contain);
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
                                //$("#result-container").html(response.html_contain);
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
            }
        });    
    }

    // doc-action scripts
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

    // doc-user scripts
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

    // doc-category scripts
    function activate_doc_category_list_data(){
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }

            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

        $("#new-doc-category").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_doc_category_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_doc_category_list_data();
                    },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-doc-category-"]').on("click", function () {
            const category_id = this.id.substring(18);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_doc_category_dialog_data',
                    '_category_id': category_id,
                },
                success: function (response) {
                    $("#doc-category-dialog").html(response.html_contain);
                    $("#doc-category-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#doc-category-dialog").dialog({
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
                            'action': 'set_doc_category_dialog_data',
                            '_category_id': $("#category-id").val(),
                            '_category_title': $("#category-title").val(),
                            '_category_content': $("#category-content").val(),
                            '_category_url': $("#category-url").val(),
                            '_parent_category': $("#parent-category").val(),
                        },
                        success: function (response) {
                            $("#doc-category-dialog").dialog('close');
                            $("#result-container").html(response.html_contain);
                            activate_doc_category_list_data();
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this doc category?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_doc_category_dialog_data',
                                '_category_id': $("#category-id").val(),
                            },
                            success: function (response) {
                                $("#doc-category-dialog").dialog('close');
                                $("#result-container").html(response.html_contain);
                                activate_doc_category_list_data();
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
});

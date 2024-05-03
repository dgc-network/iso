// display-profiles
jQuery(document).ready(function($) {

    // Function to check if the string is a valid URL
    function isURL(str) {
        var pattern = /^(http|https):\/\/[^ "]+$/;
        return pattern.test(str);
    }
    
    $("#my-profile-submit").on("click", function () {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_my_profile_data',
                '_display_name': $("#display-name").val(),
                '_user_email': $("#user-email").val(),
            },
            success: function (response) {
                if (response.success) {
                    alert("Success!");
                } else {
                    alert("Error: " + response.error);
                }
            },
            error: function (error) {
                console.error(error);
                alert("Error: Something went wrong!");
            }
        });            
    });

    activate_site_profile_data()

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
                //alert(error);
            }
        });
    }

    function get_site_job_list_data(){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_site_job_list_data',
            },
            success: function (response) {
                $("#result-container").html(response.html_contain);
                activate_site_profile_data();
            },
            error: function (error) {
                console.error(error);
                //alert(error);
            }
        });
    }

    function get_doc_category_list_data(){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_doc_category_list_data',
            },
            success: function (response) {
                $("#result-container").html(response.html_contain);
                activate_site_profile_data();
            },
            error: function (error) {
                console.error(error);
                //alert(error);
            }
        });
    }

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
                                //alert(error);
                            }
                        });            
                    });            
                },
                error: function (error) {
                    console.error(error);
                    //alert(error);
                }
            });
        });
    
        $("#site-profile-submit").on("click", function () {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_site_dialog_data',
                    '_site_id': $("#site-id").val(),
                    '_site_title': $("#site-title").val(),
                    '_image_url': $("#image-url").val(),
                },
                success: function (response) {
                    if (response.success) {
                        alert("Success!");
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
    
        $("#new-site-user").on("click", function() {
            $("#new-user-dialog").dialog('open');
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
                    get_site_job_list_data();
                },
                error: function(error){
                    console.error(error);
                    //alert(error);
                }
            });    
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
                    get_doc_category_list_data();
                },
                error: function(error){
                    console.error(error);
                    //alert(error);
                }
            });    
        });
    
        $("#select-profile").on("change", function() {
            window.location.replace("?_select_profile="+$(this).val());
            $(this).val('');
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
                    $("#site-user-dialog").dialog('open');
                    $("#user-id").val(user_id);
                    $("#display-name").val(response.display_name);
                    $("#user-email").val(response.user_email);
                    $('#is-site-admin').prop('checked', response.is_site_admin == 1);
                    $("#select-site").val(response.site_id);
                    $("#user-job-list").html(response.user_job_list);

                    $('[id^="check-user-job-"]').on("click", function () {
                        const job_id = this.id.substring(15);
                        // Toggle the checkbox state
                        $("#myCheckbox-"+job_id).prop("checked", function(i, value) {
                            return !value;
                        });
                        
                        if (window.confirm("Are you sure you want to change this setting?")) {
                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'set_user_job_data',
                                    _job_id : job_id,
                                    _user_id : user_id,
                                    _is_user_job : $("#myCheckbox-"+job_id).is(":checked") ? 1 : 0,
                                },
                                success: function (response) {
                                    if (response.success) {
                                        //alert("Success!");
                                    } else {
                                        alert("Error: " + response.error);
                                    }
                                },
                                error: function (error) {
                                    console.error(error);
                                    alert("Error: Something went wrong!");
                                }
                            });
                        }
                    });
                                
                },
                error: function (error) {
                    console.error(error);
                    //alert(error);
                }
            });
        });

        $('[id^="edit-site-job-"]').on("click", function () {
            const job_id = this.id.substring(14);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_site_job_dialog_data',
                    '_job_id': job_id,
                },
                success: function (response) {
                    $("#site-job-dialog").dialog('open');
                    $("#job-id").val(job_id);
                    $("#job-number").val(response.job_number);
                    $("#job-title").val(response.job_title);
                    $("#job-content").val(response.job_content);
                    $("#department").val(response.department);
                    get_job_action_list_data(job_id);
                },
                error: function (error) {
                    console.error(error);
                    //alert(error);
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
                    $("#doc-category-dialog").dialog('open');
                    $("#category-id").val(category_id);
                    $("#category-title").val(response.category_title);
                    $("#category-content").val(response.category_content);
                },
                error: function (error) {
                    console.error(error);
                    //alert(error);
                }
            });
        });

        $("#new-user-dialog").dialog({
            width: 450,
            modal: true,
            autoOpen: false,
            buttons: {
                "Add": function () {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_site_user_dialog_data',
                            '_display_name': $("#new-display-name").val(),
                            '_user_email': $("#new-user-email").val(),
                            '_job_title': $("#new-job-title").val(),
                            '_job_content': $("#new-job-content").val(),
                            '_is_site_admin': $('#new-is-site-admin').is(":checked") ? 1 : 0,
                            '_site_id': $("#new-site-id").val(),
                        },
                        success: function (response) {
                            $("#new-user-dialog").dialog('close');
                            get_site_profile_data();
                        },
                        error: function (error) {
                            console.error(error);
                        }
                    });
                },
            }
        });
    
        $("#site-user-dialog").dialog({
            width: 450,
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
                            get_site_profile_data();
                        },
                        error: function (error) {
                            console.error(error);
                            //alert(error);
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
                            }
                        });
                    }
                },
            }
        });
    
        $("#site-job-dialog").dialog({
            width: 450,
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
                            '_job_id': $("#job-id").val(),
                            '_job_number': $("#job-number").val(),
                            '_job_title': $("#job-title").val(),
                            '_job_content': $("#job-content").val(),
                            '_department': $("#department").val(),
                        },
                        success: function (response) {
                            $("#site-job-dialog").dialog('close');
                            window.location.replace(window.location.href);
                        },
                        error: function (error) {
                            console.error(error);
                            //alert(error);
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
                                '_job_id': $("#job-id").val(),
                            },
                            success: function (response) {
                                $("#site-job-dialog").dialog('close');
                                window.location.replace(window.location.href);
                            },
                            error: function (error) {
                                console.error(error);
                                //alert(error);
                            }
                        });
                    }
                },
            }
        });
    
        $("#doc-category-dialog").dialog({
            width: 450,
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
                        },
                        success: function (response) {
                            $("#doc-category-dialog").dialog('close');
                            get_doc_category_list_data();
                        },
                        error: function (error) {
                            console.error(error);
                            //alert(error);
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
                                get_doc_category_list_data();
                            },
                            error: function (error) {
                                console.error(error);
                                //alert(error);
                            }
                        });
                    }
                },
            }
        });    
    }

    // Site job actions
    $("#new-job-action").on("click", function() {
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_job_action_dialog_data',
                '_job_id': $("#job-id").val(),
            },
            success: function (response) {
                get_job_action_list_data($("#job-id").val());
            },
            error: function(error){
                console.error(error);
                //alert(error);
            }
        });    
    });

    function get_job_action_list_data(job_id) {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_job_action_list_data',
                '_job_id': job_id,
            },
            success: function (response) {
                for (let index = 0; index < 50; index++) {
                    $(`.site-job-action-list-${index}`).hide().empty();
                }    
                $.each(response, function (index, value) {
                    $(`.site-job-action-list-${index}`).attr("id", `edit-job-action-${value.action_id}`)
                    const output = `
                        <td style="text-align:center;">${value.action_title}</td>
                        <td>${value.action_content}</td>
                        <td style="text-align:center;">${value.next_job}</td>
                        <td style="text-align:center;">${value.next_leadtime}</td>`;
                    $(`.site-job-action-list-${index}`).append(output).show();
                });
    
                $('[id^="edit-job-action-"]').on("click", function () {
                    const job_id = this.id.substring(16);
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'get_job_action_dialog_data',
                            '_action_id': job_id,
                        },
                        success: function (response) {
                            $("#job-action-dialog").dialog('open');
                            $("#action-id").val(job_id);
                            $("#action-title").val(response.action_title);
                            $("#action-content").val(response.action_content);
                            $("#next-job").empty().append(response.next_job);
                            $("#next-leadtime").val(response.next_leadtime);
                        },
                        error: function (error) {
                            console.error(error);
                            //alert(error);
                        }
                    });
                });
            },
            error: function (error) {
                console.error(error);
                //alert(error);
            }
        });
    }

    $("#job-action-dialog").dialog({
        width: 450,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'set_job_action_dialog_data',
                        '_action_id': $("#action-id").val(),
                        '_action_title': $("#action-title").val(),
                        '_action_content': $("#action-content").val(),
                        '_next_job': $("#next-job").val(),
                        '_next_leadtime': $("#next-leadtime").val(),
                    },
                    success: function (response) {
                        $("#job-action-dialog").dialog('close');
                        get_job_action_list_data($("#job-id").val());
                    },
                    error: function (error) {
                        console.error(error);                    
                        //alert(error);
                    }
                });            
            },
            "Delete": function() {
                if (window.confirm("Are you sure you want to delete this job action?")) {
                    jQuery.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'del_job_action_dialog_data',
                            '_action_id': $("#action-id").val(),
                        },
                        success: function (response) {
                            $("#job-action-dialog").dialog('close');
                            get_job_action_list_data($("#job-id").val());
                        },
                        error: function(error){
                            console.error(error);
                            //alert(error);
                        }
                    });
                }
            }
        }
    });
});

// my-jobs
jQuery(document).ready(function($) {

    activate_site_job_list_data()

    $('[id^="btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'red');
    });
        
    $('[id^="btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', 'black');
    });        

    $("#btn-new-site-job").on("click", function() {
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_site_job_dialog_data',
                '_site_id': $("#site-id").val(),
            },
            success: function (response) {
                get_site_job_list_data($("#site-id").val());
            },
            error: function(error){
                console.error(error);
                alert(error);
            }
        });    
    });

    function get_site_job_list_data(id){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_site_job_list_data',
                '_site_id': id,
            },
            success: function (response) {
                $("[class^='site-job-list-']").hide().empty();
        
                $.each(response, function (index, value) {
                    let targetTr = $(".site-job-list-" + index).first();
                    targetTr.attr("id", "edit-site-job-" + value.job_id);
        
                    const isChecked = value.is_my_job == 1 ? 'checked' : '';
                    const output = `
                        <td style="text-align: center;"><input type="checkbox" id="check-my-job-${value.job_id}" ${isChecked} /></td>
                        <td style="text-align:center;">${value.job_title}</td>
                        <td>${value.job_content}</td>
                    `;
                    targetTr.append(output).show();
                });
        
                activate_site_job_list_data();
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    function activate_site_job_list_data(){
        $('[id^="edit-site-job-"]').on("click", function () {
            const id = this.id.substring(14);        
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_site_job_dialog_data',
                    '_job_id': id,
                },
                success: function (response) {
                    $("#job-dialog").dialog('open');
                    $("#job-id").val(id);
                    $("#job-title").val(response.job_title);
                    $("#job-content").val(response.job_content);
                    $('#is-my-job').prop('checked', response.is_my_job == 1);
                    get_site_job_action_list_data(id);
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });
    }

    $("#job-dialog").dialog({
        width: 500,
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
                        '_job_title': $("#job-title").val(),
                        '_job_content': $("#job-content").val(),
                        '_is_my_job': $('#is-my-job').is(":checked") ? 1 : 0,
                    },
                    success: function (response) {
                        $("#job-dialog").dialog('close');
                        get_site_job_list_data($("#site-id").val());
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
                            '_job_id': $("#job-id").val(),
                        },
                        success: function (response) {
                            $("#job-dialog").dialog('close');
                            get_site_job_list_data($("#site-id").val());
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

    // Site job actions template
    $("#btn-new-site-job-action").on("click", function() {
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_job_action_dialog_data',
                '_job_id': $("#job-id").val(),
            },
            success: function (response) {
                get_site_job_action_list_data($("#job-id").val());
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });                        

    function get_site_job_action_list_data(job_id) {
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
                    $(`.site-job-action-list-${index}`).attr("id", `edit-job-action-site-${value.action_id}`)
                    const output = `
                        <td style="text-align:center;">${value.action_title}</td>
                        <td>${value.action_content}</td>
                        <td style="text-align:center;">${value.next_job}</td>
                        <td style="text-align:center;">${value.next_leadtime}</td>`;
                    $(`.site-job-action-list-${index}`).append(output).show();
                });
    
                $('[id^="edit-job-action-site-"]').on("click", function () {
                    let id = this.id.substring(21);
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'get_job_action_dialog_data',
                            '_action_id': id,
                            '_site_id': $("#site-id").val(),
                        },
                        success: function (response) {
                            $("#site-job-action-dialog").dialog('open');
                            $("#action-id").val(id);
                            $("#action-title").val(response.action_title);
                            $("#action-content").val(response.action_content);
                            $("#next-job").empty().append(response.next_job);
                            $("#next-leadtime").val(response.next_leadtime);
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
    }

    $("#site-job-action-dialog").dialog({
        width: 400,
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
                        $("#site-job-action-dialog").dialog('close');
                        get_site_job_action_list_data($("#job-id").val());
                    },
                    error: function (error) {
                        console.error(error);                    
                        alert(error);
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
                            $("#site-job-action-dialog").dialog('close');
                            get_site_job_action_list_data($("#job-id").val());
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
});

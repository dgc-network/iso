// To-do list
jQuery(document).ready(function($) {

    function get_todo_list_data(job_id){
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_todo_list_data',
                '_job_id': job_id,
            },
            success: function (response) {            
                for(index=0;index<50;index++) {
                    $(".todo-list-"+index).hide().empty();
                    //$(".todo-list-"+index).hide();
                    //$(".todo-list-"+index).empty();
                }
                $.each(response, function (index, value) {
                    // Find the first <tr> with the specified class
                    //let targetTr = $(".todo-list-" + index).first();
                    // Add an id attribute
                    //targetTr.attr("id", "edit-todo-" + value.todo_id);                
                    $(".todo-list-" + index).attr("id", "edit-todo-" + value.todo_id);
                    output = '';
                    output = output+'<td style="text-align:center;">'+value.job_title+'</td>';
                    output = output+'<td>'+value.doc_title+'</td>';
                    output = output+'<td style="text-align:center;">'+value.due_date+'</td>';
                    const output = `
                        <td style="text-align:center;">${value.job_title}</td>
                        <td>${value.doc_title}</td>
                        <td style="text-align:center;">${value.due_date}</td>
                    `;

                    $(".todo-list-"+index).append(output).show();
                    //$(".todo-list-"+index).append(output);
                    //$(".todo-list-"+index).show();
                })
            },
            error: function (error) {
                console.error(error);                    
                alert(error);
            }
        });            
    }

    $('[id^="edit-todo-"]').on("click", function () {
        id = this.id;
        id = id.substring(9);
        $("#todo-id").val(id);
    
        // Dialog content
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_todo_dialog_data',
                '_todo_id': id,
            },
            success: function (response) {
                $("#todo-dialog").dialog('open');
                $("#doc-title").val(response.doc_title);
                $("#doc-number").val(response.doc_number);
                $("#doc-revision").val(response.doc_revision);
                $("#btn-doc-url").val(response.doc_url);

                $('[id^="btn-"]').mouseover(function() {
                    $(this).css('cursor', 'pointer');
                    $(this).css('color', 'red');
                });
                    
                $('[id^="btn-"]').mouseout(function() {
                    $(this).css('cursor', 'default');
                    $(this).css('color', 'black');
                });
                
                $("#btn-doc-url").on( "click", function() {
                    window.location.replace(response.doc_url);
                })

                $("#btn-workflow").on( "click", function() {
                    $("#job-id").val(id);
                    get_workflow_todo_action_list_data(id)            
                })
/*
                $('[id^="btn-edit-workflow-"]').on( "click", function() {
                    id = this.id;
                    id = id.substring(18);
                    $("#job-id").val(id);
                    get_workflow_todo_action_list_data(id)            
                })            
*/            
            },
            error: function (error) {
                console.error(error);                
                alert(error);
            }
        });            

        // Dialog buttons
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_todo_action_list_data',
                '_todo_id': id,
            },
            success: function (response) {
                let buttonData = [];
                $.each(response, function (index, value) {
                    // JSON data as a string
                    var jsonDataString = '{"label": "' + value.action_title + '", "action": "' + value.action_id + '"}';
                    // Parse JSON string to JavaScript object
                    var jsonData = $.parseJSON(jsonDataString);
                    // Add JSON object to the array
                    buttonData.push(jsonData);
                })
                openTodoDialog(buttonData);
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    });
    
    function openTodoDialog(buttonData) {
        let buttons = {};
        for (let i = 0; i < buttonData.length; i++) {
            let btn = buttonData[i];
            buttons[btn.label] = function () {
                if (window.confirm("Are you sure you want to proceed this job action?")) {
                    jQuery.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_todo_action_dialog_data',
                            '_action_id': btn.action,
                            '_todo_id': $("#todo-id").val()
                        },
                        success: function (response) {
                            $("#todo-dialog").dialog('close');
                            get_todo_list_data($("#job-id").val());
                        },
                        error: function(error){
                            console.error(error);
                            alert(error);
                        }
                    });
                }
            };
        }
    
        $("#todo-dialog").dialog({
            width: 600,
            autoOpen: false,
            modal: true,
            buttons: buttons
        });
    
        // Open the dialog after it has been initialized
        $("#todo-dialog").dialog("open");
    }
    
    // Job action list
    $("#workflow-todo-action-list-dialog").dialog({
        width: 500,
        modal: true,
        autoOpen: false,
    });

    // Todo job actions settings
    $("#btn-new-todo-action").on("click", function() {
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_job_action_dialog_data',
                '_job_id': $("#job-id").val(),
            },
            success: function (response) {
                get_workflow_todo_action_list_data($("#job-id").val());
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });                        

    function get_workflow_todo_action_list_data(id){
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_job_action_list_data',
                '_job_id': id,
            },
            success: function (response) {            
                $("#workflow-todo-action-list-dialog").dialog('open');
                // Action list in job
                for(index=0;index<50;index++) {
                    $(".todo-job-action-list-"+index).hide();
                    $(".todo-job-action-list-"+index).empty();
                }
                $.each(response, function (index, value) {
                    // Find the first <tr> with the specified class
                    let targetTr = $(".todo-job-action-list-" + index).first();
                    // Add an id attribute
                    targetTr.attr("id", "edit-job-action-todo-" + value.action_id);                
                    output = '';
                    output = output+'<td style="text-align:center;">'+value.action_title+'</td>';
                    output = output+'<td>'+value.action_content+'</td>';
                    output = output+'<td style="text-align:center;">'+value.next_job+'</td>';
                    output = output+'<td style="text-align:center;">'+value.next_leadtime+'</td>';
                    $(".todo-job-action-list-"+index).append(output);
                    $(".todo-job-action-list-"+index).show();
                })

                $('[id^="edit-job-action-todo-"]').on( "click", function() {
                    id = this.id;
                    id = id.substring(21);
                    jQuery.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'get_job_action_dialog_data',
                            '_action_id': id,
                            '_site_id': $("#site-id").val(),
                        },
                        success: function (response) {
                            $("#workflow-todo-action-dialog").dialog('open');
                            $("#action-id").val(id);
                            $("#action-title").val(response.action_title);
                            $("#action-content").val(response.action_content);
                            $("#next-job").empty();
                            $("#next-job").append(response.next_job);
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
    
    $("#workflow-todo-action-dialog").dialog({
        width: 400,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                if (window.confirm("Are you sure you want to proceed this job action?")) {
                    jQuery.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_next_job_action_data',
                            '_action_id': $("#action-id").val(),
                            '_next_job': $("#next-job").val(),
                            '_next_leadtime': $("#next-leadtime").val(),
                            '_doc_id': $("#doc-id").val(),
                        },
                        success: function (response) {
                            $("#workflow-todo-action-dialog").dialog('close');
                            get_workflow_todo_action_list_data($("#job-id").val());
                        },
                        error: function (error) {
                            console.error(error);                    
                            alert(error);
                        }
                    });            
                }
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
                            $("#workflow-todo-action-dialog").dialog('close');
                            get_workflow_todo_action_list_data($("#job-id").val());
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

})

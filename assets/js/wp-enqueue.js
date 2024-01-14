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
                alert(error);
            }
        });    
    });

    function activate_site_job_list_data(){
        $('[id^="edit-site-job-"]').on( "click", function() {
            id = this.id;
            id = id.substring(14);
            jQuery.ajax({
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
                    get_job_action_list_data(id);
                },
                error: function (error) {
                    console.error(error);                
                    alert(error);
                }
            });
        });
    }

    function get_site_job_list_data(id){
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_site_job_list_data',
                '_site_id': id,
            },
            success: function (response) {
                for(index=0;index<50;index++) {
                    $(".site-job-list-"+index).hide();
                    $(".site-job-list-"+index).empty();
                }
                $.each(response, function (index, value) {
                    // Find the first <tr> with the specified class
                    let targetTr = $(".site-job-list-" + index).first();
                    // Add an id attribute
                    targetTr.attr("id", "edit-site-job-" + value.job_id);
                
                    output = '';
                    output = output+'<td style="text-align: center;"><input type="checkbox" id="check-my-job-'+value.job_id+'>" /></td>';
                    output = output+'<td style="text-align:center;">'+value.job_title+'</td>';
                    output = output+'<td>'+value.job_content+'</td>';
                    $(".site-job-list-"+index).append(output);
                    $(".site-job-list-"+index).show();
                });

                activate_site_job_list_data();
            },
            error: function(error){
                alert(error);
            }
        });
    }

    $("#job-dialog").dialog({
        width: 500,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'set_site_job_dialog_data',
                        '_job_id': $("#job-id").val(),
                        '_job_title': $("#job-title").val(),
                        '_job_content': $("#job-content").val(),
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
            "Delete": function() {
                if (window.confirm("Are you sure you want to delete this site job?")) {
                    jQuery.ajax({
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
                        error: function(error){
                            alert(error);
                        }
                    });
                }
            },
        }
    });

    // Site job template actions
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
                get_job_action_list_data($("#job-id").val());
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });                        

    function get_job_action_list_data(job_id){
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_job_action_list_data',
                '_job_id': job_id,
            },
            success: function (response) {            
                //$("#site-job-action-list-dialog").dialog('open');
                // Action list in job
                for(index=0;index<50;index++) {
                    $(".site-job-action-list-"+index).hide();
                    $(".site-job-action-list-"+index).empty();
                }
                $.each(response, function (index, value) {
                    // Find the first <tr> with the specified class
                    let targetTr = $(".site-job-action-list-" + index).first();
                    // Add an id attribute
                    targetTr.attr("id", "edit-site-job-action-" + value.action_id);
                
                    output = '';
                    output = output+'<td style="text-align:center;">'+value.action_title+'</td>';
                    output = output+'<td>'+value.action_content+'</td>';
                    output = output+'<td style="text-align:center;">'+value.next_job+'</td>';
                    output = output+'<td style="text-align:center;">'+value.next_leadtime+'</td>';
                    $(".site-job-action-list-"+index).append(output);
                    $(".site-job-action-list-"+index).show();
                })

                $('[id^="edit-site-job-action-"]').on( "click", function() {
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
                            $("#action-dialog").dialog('open');
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

    $("#action-dialog").dialog({
        width: 500,
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
                        $("#action-dialog").dialog('close');
                        get_job_action_list_data($("#job-id").val());
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
                            '_action_id': id,
                        },
                        success: function (response) {
                            $("#action-dialog").dialog('close');
                            get_job_action_list_data($("#job-id").val());
                        },
                        error: function(error){
                            alert(error);
                        }
                    });
                }
            }
        }
    });
});

// display documents
jQuery(document).ready(function($) {

    activate_document_list_data()

    $('[id^="btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'red');
    });
        
    $('[id^="btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', 'black');
    });

    $("#btn-new-document").on("click", function() {
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_document_dialog_data',
                '_site_id': $("#site-id").val(),
            },
            success: function (response) {
                get_document_list_data($("#site-id").val());
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });

    function get_document_list_data(site_id){
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_document_list_data',
                '_site_id': site_id,
            },
            success: function (response) {
                for(index=0;index<50;index++) {
                    $(".document-list-"+index).hide();
                    $(".document-list-"+index).empty();
                }
                $.each(response, function (index, value) {
                    // Find the first <tr> with the specified class
                    let targetTr = $(".document-list-" + index).first();
                    // Add an id attribute
                    targetTr.attr("id", "edit-document-" + value.doc_id);
                
                    output = '';
                    output = output+'<td style="text-align: center;">'+value.doc_number+'</td>';
                    output = output+'<td>'+value.doc_title+'</td>';
                    output = output+'<td style="text-align: center;" id="todo-workflow-list-'+value.doc_id+'">'+value.doc_revision+'</td>';
                    output = output+'<td style="text-align: center;">'+value.doc_date+'</td>';
                    $(".document-list-"+index).append(output);
                    $(".document-list-"+index).show();
                });

                activate_document_list_data();
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });
    }

    function activate_document_list_data(){

        $('[id^="edit-document-"]').on( "click", function() {
            id = this.id;
            id = id.substring(14);
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_document_dialog_data',
                    '_doc_id': id,
                    '_site_id': $("#site-id").val(),
                },
                success: function (response) {
                    $("#document-dialog").dialog('open');
                    $("#doc-id").val(id);
                    $("#doc-title").val(response.doc_title);
                    $("#doc-number").val(response.doc_number);
                    $("#doc-revision").val(response.doc_revision);
                    $("#doc-date").val(response.doc_date);
                    $("#doc-url").val(response.doc_url);
                    $("#start-job").empty();
                    $("#start-job").append(response.start_job);
                    $("#start-leadtime").val(response.start_leadtime);
                    $("#final-job").empty();
                    $("#final-job").append(response.final_job);
                },
                error: function (error) {
                    console.error(error);                
                    alert(error);
                }
            });            
        });

        $('[id^="todo-workflow-list-"]').on( "click", function() {
            id = this.id;
            id = id.substring(14);
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_workflow_todo_list_data',
                    '_doc_id': id,
                },
                success: function (response) {
                    $("#doc-id").val(id);
                    $("#workflow-todo-list-dialog").dialog('open');
                    for(index=0;index<50;index++) {
                        $(".workflow-list-"+index).hide();
                        $(".workflow-list-"+index).empty();
                    }
                    $.each(response, function (index, value) {
                        // Find the first <tr> with the specified class
                        let targetTr = $(".workflow-list-" + index).first();
                        // Add an id attribute
                        targetTr.attr("id", "edit-workflow-" + value.doc_id);                    
                        output = '';
                        output = output+'<td style="text-align: center;">'+value.job_title+'</td>';
                        output = output+'<td>'+value.job_content+'</td>';
                        output = output+'<td style="text-align: center;">'+value.submit_user+'</td>';
                        output = output+'<td style="text-align: center;">'+value.submit_time+'</td>';
                        $(".workflow-list-"+index).append(output);
                        $(".workflow-list-"+index).show();
                    });
                },
                error: function (error) {
                    console.error(error);                
                    alert(error);
                }
            });            
        });

        $('#doc-date').datepicker({
            onSelect: function(dateText, inst) {
                $(this).val(dateText);
            }
        });            
    }

    $("#document-dialog").dialog({
        width: 600,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'set_document_dialog_data',
                        '_doc_id': $("#doc-id").val(),
                        '_doc_title': $("#doc-title").val(),
                        '_doc_number': $("#doc-number").val(),
                        '_doc_revision': $("#doc-revision").val(),
                        '_doc_date': $("#doc-date").val(),
                        '_doc_url': $("#doc-url").val(),
                        '_start_job': $("#start-job").val(),
                        '_start_leadtime': $("#start-leadtime").val(),
                        '_final_job': $("#final-job").val(),
                    },
                    success: function (response) {
                        $("#document-dialog").dialog('close');
                        get_document_list_data($("#site-id").val());
                    },
                    error: function (error) {
                        console.error(error);                    
                        alert(error);
                    }
                });            
            },
            "Delete": function() {
                if (window.confirm("Are you sure you want to delete this document?"+id)) {
                    jQuery.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'del_document_dialog_data',
                            '_doc_id': $("#doc-id").val(),
                        },
                        success: function (response) {
                            $("#document-dialog").dialog('close');
                            get_document_list_data($("#site-id").val());
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

    // Document job list
    $("#workflow-todo-list-dialog").dialog({
        width: 500,
        modal: true,
        autoOpen: false,
    });

    // Job action list
    $("#site-todo-job-action-list-dialog").dialog({
        width: 500,
        modal: true,
        autoOpen: false,
    });

});

// To-do list
jQuery(document).ready(function($) {

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
                $("#todo-id").val(id);
                $("#doc-id").val(response.doc_title);
                $("#doc-title").val(response.doc_title);
                $("#doc-number").val(response.doc_number);
                $("#doc-revision").val(response.doc_revision);
                $("#doc-date").val(response.doc_date);
                $("#doc-url").val(response.doc_url);
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
                //alert(`Button "${btn.label}" clicked`);
                if (window.confirm("Are you sure you want to do this job action?")) {
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
                    $(".todo-list-"+index).hide();
                    $(".todo-list-"+index).empty();
                }
                $.each(response, function (index, value) {
                    // Find the first <tr> with the specified class
                    let targetTr = $(".todo-list-" + index).first();
                    // Add an id attribute
                    targetTr.attr("id", "edit-todo-" + value.todo_id);
                
                    output = '';
                    output = output+'<td style="text-align:center;">'+value.job_title+'</td>';
                    output = output+'<td>'+value.doc_title+'</td>';
                    output = output+'<td style="text-align:center;">'+value.due_date+'</td>';
                    $(".todo-list-"+index).append(output);
                    $(".todo-list-"+index).show();
                })
            },
            error: function (error) {
                console.error(error);                    
                alert(error);
            }
        });            
    }
/*
    function get_todo_action_list_data(todo_id){
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_todo_action_list_data',
                '_todo_id': todo_id,
            },
            success: function (response) {            
                $("#site-job-action-list-dialog").dialog('open');
                // Action list in job
                $("#btn-new-site-job-action").hide();
                for(index=0;index<50;index++) {
                    $("#site-job-action-list-"+index).hide();
                    $("#site-job-action-list-"+index).empty();
                }
                $.each(response, function (index, value) {
                    output = '';
                    output = output+'<td></td>';
                    //output = output+'<td style="text-align:center;"><span id="btn-edit-job-action-'+value.action_id+'" class="dashicons dashicons-edit"></span></td>';
                    output = output+'<td style="text-align:center;" id="btn-todo-action-'+value.action_id+'">'+value.action_title+'</td>';
                    output = output+'<td>'+value.action_content+'</td>';
                    output = output+'<td style="text-align:center;">'+value.next_job+'</td>';
                    output = output+'<td style="text-align:center;">'+value.next_leadtime+'</td>';
                    output = output+'<td></td>';
                    //output = output+'<td style="text-align:center;"><span id="btn-del-job-action-'+value.action_id+'" class="dashicons dashicons-trash"></span></td>';
                    $("#site-job-action-list-"+index).append(output);
                    $("#site-job-action-list-"+index).show();
                })

                $('[id^="btn-"]').mouseover(function() {
                    $(this).css('cursor', 'pointer');
                    $(this).css('color', 'red');
                });
                    
                $('[id^="btn-"]').mouseout(function() {
                    $(this).css('cursor', 'default');
                    $(this).css('color', 'black');
                });
                
                $('[id^="btn-todo-action-"]').on( "click", function() {
                    id = this.id;
                    id = id.substring(16);
                    if (window.confirm("Are you sure you want to do this job action?")) {
                        jQuery.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'set_todo_action_dialog_data',
                                '_action_id': id,
                                '_todo_id': $("#todo-id").val()
                            },
                            success: function (response) {
                                $("#site-job-action-list-dialog").dialog('close');
                                get_todo_list_data($("#job-id").val());
                            },
                            error: function(error){
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

    }
*/

})

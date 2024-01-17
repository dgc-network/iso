// display documents
jQuery(document).ready(function($) {

    activate_document_list_data()
    activate_workflow_list_data()

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
                    output = output+'<td style="text-align: center;">'+value.doc_revision+'</td>';
                    output = output+'<td style="text-align: center;">'+value.doc_date+'</td>';
                    output = output+'<td style="text-align: center;" id="btn-workflow-todo-list-'+value.doc_id+'"><span class="dashicons dashicons-networking">Flow</span></td>';
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

        $('[id^="btn-workflow-todo-list-"]').on( "click", function() {
            id = this.id;
            id = id.substring(23);
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
                    $("#workflow-list-dialog").dialog('open');
                    for(index=0;index<50;index++) {
                        $(".workflow-todo-list-"+index).hide();
                        $(".workflow-todo-list-"+index).empty();
                    }
                    $.each(response, function (index, value) {
                        // Find the first <tr> with the specified class
                        let targetTr = $(".workflow-todo-list-" + index).first();
                        // Add an id attribute
                        targetTr.attr("id", "edit-workflow-" + value.todo_id);                    
                        output = '';
                        output = output+'<td style="text-align: center;">'+value.job_title+'</td>';
                        output = output+'<td>'+value.job_content+'</td>';
                        output = output+'<td style="text-align: center;">'+value.submit_user+'</td>';
                        output = output+'<td style="text-align: center;">'+value.submit_time+'</td>';
                        $(".workflow-todo-list-"+index).append(output);
                        $(".workflow-todo-list-"+index).show();
                    });
                    activate_workflow_list_data();
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

    // Document todo/job list
    $("#workflow-list-dialog").dialog({
        width: 600,
        modal: true,
        autoOpen: false,
    });

    function activate_workflow_list_data(){
        $('[id^="edit-workflow-"]').on( "click", function() {
            id = this.id;
            id = id.substring(14);
            $("#job-id").val(id);
            get_workflow_todo_action_list_data(id)            
        })    
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
                        $("#workflow-todo-action-dialog").dialog('close');
                        get_workflow_todo_action_list_data($("#job-id").val());
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
});

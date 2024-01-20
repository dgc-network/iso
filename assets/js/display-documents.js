// display documents
jQuery(document).ready(function($) {

    activate_document_list_data()
    //activate_workflow_list_data()

    $('[id^="btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'red');
    });
        
    $('[id^="btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', 'black');
    });

    $("#btn-new-document").on("click", function() {
        $.ajax({
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

    function get_document_list_data(siteId) {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_document_list_data',
                '_site_id': siteId,
            },
            success: function (response) {
                for (let index = 0; index < 50; index++) {
                    const targetTr = $(`.document-list-${index}`).hide().empty();
                    $.each(response, function (i, value) {
                        targetTr.attr("id", `edit-document-${value.doc_id}`);
                        const output = `
                            <td style="text-align: center;">${value.doc_number}</td>
                            <td>${value.doc_title}</td>
                            <td style="text-align: center;">${value.doc_revision}</td>
                            <td style="text-align: center;">${value.doc_date}</td>`;
                        targetTr.append(output).show();
                    });    
                    activate_document_list_data();
                }
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    function activate_document_list_data(){
        $('[id^="edit-document-"]').on( "click", function() {
            id = this.id;
            id = id.substring(14);
            $.ajax({
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
                    //$("#final-job").empty();
                    //$("#final-job").append(response.final_job);
                },
                error: function (error) {
                    console.error(error);                
                    alert(error);
                }
            });            
        });
/*
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
*/                    
    }

    $("#document-dialog").dialog({
        width: 600,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                $.ajax({
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
                    $.ajax({
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
/*
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
*/
});

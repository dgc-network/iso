// display documents
jQuery(document).ready(function($) {
    $("#select-category").on( "change", function() {
        window.location.replace("?_category="+$(this).val());
        $(this).val('');
    });

    $("#search-document").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
        $(this).val('');
    });

    $("#btn-document-setting").on("click", function () {
        $("#document-setting-div").toggle();
    });

    activate_document_list_data()

    $('[id^="btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'red');
    });
        
    $('[id^="btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', '');
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

    $("#btn-doc-workflow").on( "click", function() {
        get_doc_workflow_list_data($("#doc-id").val());
    })

    $("#btn-doc-status").on( "click", function() {
        get_doc_workflow_list_data($("#doc-id").val());
    })

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
                    $(`.document-list-${index}`).hide().empty();
                }    
                $.each(response, function (index, value) {
                    $(`.document-list-${index}`).attr("id", `edit-document-${value.doc_id}`)
                    const output = `
                        <td style="text-align: center;">${value.doc_number}</td>
                        <td>${value.doc_title}</td>
                        <td style="text-align: center;">${value.doc_revision}</td>
                        <td style="text-align: center;">${value.doc_date ? value.doc_date : ''}</td>`;
                    $(`.document-list-${index}`).append(output).show();
                });
                activate_document_list_data()
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    function activate_document_list_data(){
        $('[id^="edit-document-"]').on( "click", function() {
            const id = this.id.substring(14);
        
            // Dialog content
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_document_dialog_data',
                    '_doc_id': id,
                    //'_site_id': $("#site-id").val(),
                },
                success: function (response) {
                    $("#document-dialog").dialog('open');
                    $("#doc-id").val(id);
                    $("#btn-doc-status").val(response.doc_status);
                    $("#doc-title").val(response.doc_title);
                    $("#doc-number").val(response.doc_number);
                    $("#doc-revision").val(response.doc_revision);
                    $("#doc-url").val(response.doc_url);
                    $("#start-job").empty().append(response.start_job);
                    $("#start-leadtime").val(response.start_leadtime);
                    $("#doc-date").val(response.doc_date ? response.doc_date : '');
                    $("#doc-category").empty().append(response.doc_category);
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
            
            // Open the Dialog with dynamic buttons
            get_doc_dialog_buttons_data($("#todo-id").val());
            
        });
    }

    function get_doc_dialog_buttons_data(id) {
    }

    $("#document-dialog").dialog({
        width: 600,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                if (window.confirm("Are you sure you want to proceed this action?")) {
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
                            '_doc_url': $("#doc-url").val(),
                            '_start_job': $("#start-job").val(),
                            '_start_leadtime': $("#start-leadtime").val(),
                            '_doc_date': $("#doc-date").val(),
                            '_doc_category': $("#doc-category").val(),
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
                }
            },
            "Delete": function() {
                if (window.confirm("Are you sure you want to delete this document?")) {
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

    function get_doc_workflow_list_data(id){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_doc_workflow_list_data',
                '_doc_id': id,
            },
            success: function (response) {            
                $("#doc-workflow-list-dialog").dialog('open');
                for(index=0;index<50;index++) {
                    $(".doc-workflow-list-"+index).hide().empty();
                }
                $.each(response, function (index, value) {
                    //$(".doc-workflow-list-" + index).attr("id", "edit-doc-workflow-" + value.todo_id);
                    const output = `
                        <td style="text-align:center;">${value.todo_title}</td>
                        <td>${value.todo_content}</td>
                        <td style="text-align:center;">${value.submit_user}</td>
                        <td style="text-align:center;">${value.submit_action}</td>
                        <td style="text-align:center;">${value.submit_time}</td>
                    `;
                    $(".doc-workflow-list-"+index).append(output).show();
                })
            },
            error: function (error) {
                console.error(error);                
                alert(error);
            }
        });
    }

    $("#doc-workflow-list-dialog").dialog({
        width: 500,
        modal: true,
        autoOpen: false,
    });    
});

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

    $('[id^="edit-document-"]').on("click", function () {
        const doc_id = this.id.substring(14);
        open_doc_dialog_and_buttons(doc_id)
    });            

    $("#new-document-button").on("click", function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_document_dialog_data',
                '_site_id': $("#site-id").val(),
            },
            success: function (response) {
                window.location.replace("/display-documents/");
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });

    function open_doc_dialog_and_buttons(doc_id){
        // AJAX request
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'open_doc_dialog_and_buttons',
                _doc_id: doc_id,
            },
            success: function (response) {
                // Display the result
                $('#result-container').html(response);

                $("#set-document-button").on("click", function(e) {
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_document_dialog_data',
                            '_doc_id': doc_id,
                            '_doc_title': $("#doc_title").val(),
                            '_doc_number': $("#doc_number").val(),
                            '_doc_revision': $("#doc_revision").val(),
                            '_doc_url': $("#doc_url").val(),
                            '_start_job': $("#start_job").val(),
                            '_start_leadtime': $("#start_leadtime").val(),
                            '_doc_date': $("#doc_date").val(),
                            '_doc_category': $("#doc_category").val(),

                        },
                        success: function (response) {
                            window.location.replace("/display-documents/");
                        },
                        error: function(error){
                            console.error(error);
                            alert(error);
                        }
                    });
                });

                $("#del-document-button").on("click", function(e) {
                    e.preventDefault();
                    if (window.confirm("Are you sure you want to delete this document?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_document_dialog_data',
                                '_doc_id': doc_id,
                            },
                            success: function (response) {
                                window.location.replace("/display-documents/");
                            },
                            error: function(error){
                                console.error(error);
                                alert(error);
                            }
                        });
                    }
                });

                $("#doc-field-setting").on("click", function () {
                    $("#doc-field-list-dialog").toggle();
                    get_doc_field_list_data(doc_id);
                });            
/*
                $("#doc-field-list-dialog").dialog({
                    width: 400,
                    modal: true,
                    autoOpen: false,
                });
*/            
                $("#new-doc-field").on("click", function(e) {
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_doc_field_dialog_data',
                            '_doc_id': doc_id,
                        },
                        success: function (response) {
                            get_doc_field_list_data(doc_id);
                        },
                        error: function(error){
                            console.error(error);                    
                            alert(error);
                        }
                    });    
                });                                        
                
                $("#doc-field-dialog").dialog({
                    width: 400,
                    modal: true,
                    autoOpen: false,
                    buttons: {
                        "Save": function() {
                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'set_doc_field_dialog_data',
                                    '_field_id': $("#field-id").val(),
                                    '_field_title': $("#field-title").val(),
                                    '_field_content': $("#field-content").val(),
                                    '_is_listing': $("#is-listing").val(),
                                    '_is_editing': $("#is-editing").val(),
                                },
                                success: function (response) {
                                    $("#doc-field-dialog").dialog('close');
                                    get_doc_field_list_data(doc_id);
                                },
                                error: function (error) {
                                    console.error(error);                    
                                    alert(error);
                                }
                            });            
                        },
                        "Delete": function() {
                            if (window.confirm("Are you sure you want to delete this doc field?")) {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'del_doc_field_dialog_data',
                                        '_field_id': $("#field-id").val(),
                                    },
                                    success: function (response) {
                                        $("#doc-field-dialog").dialog('close');
                                        get_doc_field_list_data(doc_id);
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
            
            },
            error: function (error) {
                console.log(error);
            }
        });
    }

    function get_doc_field_list_data(doc_id){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_doc_field_list_data',
                '_doc_id': doc_id,
            },
            success: function (response) {            
                for(index=0;index<50;index++) {
                    $(".doc-field-list-"+index).hide().empty();
                }
                $.each(response, function (index, value) {
                    $(".doc-field-list-" + index).attr("id", "edit-doc-field-" + value.field_id);
                    const is_listing_checked = value.is_listing == 1 ? 'checked' : '';
                    const is_editing_checked = value.is_editing == 1 ? 'checked' : '';
                    const output = `
                        <td style="text-align:center;">${value.field_title}</td>
                        <td>${value.field_content}</td>
                        <td style="text-align: center;"><input type="checkbox" ${is_listing_checked} /></td>
                        <td style="text-align: center;"><input type="checkbox" ${is_editing_checked} /></td>
                    `;
                    $(".doc-field-list-"+index).append(output).show();
                })

                $('[id^="edit-doc-field-"]').on( "click", function() {
                    const field_id = this.id.substring(15);
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'get_doc_field_dialog_data',
                            '_field_id': field_id,
                        },
                        success: function (response) {
                            $("#doc-field-dialog").dialog('open');
                            $("#field-id").val(field_id);
                            $("#field-title").val(response.field_title);
                            $("#field-content").val(response.field_content);
                            $('#is-listing').prop('checked', response.is_listing == 1);
                            $('#is-editing').prop('checked', response.is_editing == 1);
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
/*    
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
                    //$("#document-dialog").dialog('open');
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
            //get_doc_dialog_buttons_data($("#start-job").val());
            
        });
    }

    function get_doc_dialog_buttons_data(id) {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_todo_dialog_buttons_data',
                '_todo_id': id,
            },
            success: function (response) {
                let buttonData = [];
                $.each(response, function (index, value) {
                    var jsonDataString = '{"label": "' + value.action_title + '", "action": "' + value.action_id + '"}';
                    var jsonData;
                    try {
                        jsonData = JSON.parse(jsonDataString);
                    } catch (error) {
                        console.error('Error parsing JSON:', error);
                    }
                    buttonData.push(jsonData);
                })

                let buttons = {};
                for (let i = 0; i < buttonData.length; i++) {
                    let btn = buttonData[i];
                    buttons[btn.label] = function () {
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

                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'set_todo_dialog_data',
                                    '_action_id': btn.action,
                                    '_todo_id': $("#todo-id").val()
                                },
                                success: function (response) {
                                    $("#todo-dialog").dialog('close');
                                    get_todo_list_data();
                                },
                                error: function(error){
                                    console.error(error);
                                    alert(error);
                                }
                            });
                        }
                    };
                }
            
                $("#document-dialog").dialog({
                    width: 600,
                    autoOpen: false,
                    modal: true,
                    buttons: buttons
                });
            
                // Open the dialog after it has been initialized
                $("#document-dialog").dialog("open");

            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
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
*/
        //activate_document_list_data()

        $('[id^="btn-"]').mouseover(function() {
            $(this).css('cursor', 'pointer');
            $(this).css('color', 'red');
        });
            
        $('[id^="btn-"]').mouseout(function() {
            $(this).css('cursor', 'default');
            $(this).css('color', '');
        });
    
    
});

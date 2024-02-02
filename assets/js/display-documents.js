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

    //activate_document_list_data()

    $('[id^="btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'red');
    });
        
    $('[id^="btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', '');
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
                //todo_id = $("#start_job").val(),

                $('[id^="doc-dialog-button-"]').on("click", function (e) {
                    e.preventDefault();
                    const action_id = this.id.substring(18);
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_todo_dialog_data',
                            '_action_id': action_id,
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

                $("#start_job").on( "change", function(e) {
                    e.preventDefault();
                    if ($("#todo-status").val()=='') {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'set_todo_dialog_data',
                                '_job_id': $(this).val(),
                                '_doc_id': doc_id,
                            },
                            success: function (response) {
                                $("#todo-status").val(response);
                            },
                            error: function(error){
                                console.error(error);
                                alert(error);
                            }
                        });    
                    } else {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'set_todo_dialog_data',
                                '_job_id': $(this).val(),
                                '_doc_id': doc_id,
                                '_todo_id': $("#todo-status").val(),
                            },
                            success: function (response) {
                                $("#todo-status").val(response);
                            },
                            error: function(error){
                                console.error(error);
                                alert(error);
                            }
                        });    
                    }
                });
            
                $("#btn-action-list").on( "click", function(e) {
                    e.preventDefault();
                    get_doc_action_list_data($("#todo-status").val());
                })

                // Job action list
                $("#todo-action-list-dialog").dialog({
                    width: 500,
                    modal: true,
                    autoOpen: false,
                });
            
                // Todo job actions settings
                $("#btn-new-todo-action").on("click", function(e) {
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_todo_action_dialog_data',
                            '_todo_id': todo_id,
                        },
                        success: function (response) {
                            //open_todo_dialog_and_buttons(todo_id)
                            get_doc_action_list_data(todo_id);
                        },
                        error: function(error){
                            console.error(error);                    
                            alert(error);
                        }
                    });    
                });                                        
                
                $("#todo-action-dialog").dialog({
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
                                    'action': 'set_todo_action_dialog_data',
                                    '_action_id': $("#action-id").val(),
                                    '_action_title': $("#action-title").val(),
                                    '_action_content': $("#action-content").val(),
                                    '_next_job': $("#next-job").val(),
                                    '_next_leadtime': $("#next-leadtime").val(),
                                    //'_doc_id': $("#doc-id").val(),
                                },
                                success: function (response) {
                                    $("#todo-action-dialog").dialog('close');
                                    open_doc_dialog_and_buttons(todo_id)
                                    get_doc_action_list_data(todo_id);
                                },
                                error: function (error) {
                                    console.error(error);                    
                                    alert(error);
                                }
                            });            
                        },
                        "Delete": function() {
                            if (window.confirm("Are you sure you want to delete this todo action?")) {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'del_todo_action_dialog_data',
                                        '_action_id': $("#action-id").val(),
                                    },
                                    success: function (response) {
                                        $("#todo-action-dialog").dialog('close');
                                        open_doc_dialog_and_buttons(todo_id)
                                        get_doc_action_list_data(todo_id);
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

    function get_doc_action_list_data(todo_id){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_todo_action_list_data',
                '_todo_id': todo_id,
            },
            success: function (response) {            
                for(index=0;index<50;index++) {
                    $(".todo-action-list-"+index).hide().empty();
                }
                $.each(response, function (index, value) {
                    $(".todo-action-list-" + index).attr("id", "edit-action-todo-" + value.action_id);
                    const output = `
                        <td style="text-align:center;">${value.action_title}</td>
                        <td>${value.action_content}</td>
                        <td style="text-align:center;">${value.next_job}</td>
                        <td style="text-align:center;">${value.next_leadtime}</td>
                    `;
                    $(".todo-action-list-"+index).append(output).show();
                })
                $("#todo-action-list-dialog").dialog('open');

                $('[id^="edit-action-todo-"]').on( "click", function() {
                    const action_id = this.id.substring(17);
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'get_todo_action_dialog_data',
                            '_action_id': action_id,
                        },
                        success: function (response) {
                            $("#todo-action-dialog").dialog('open');
                            $("#action-id").val(action_id);
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
/*        
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
*/                            
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
});

// To-do list
jQuery(document).ready(function($) {

    $("#select-site-job").on( "change", function() {
        window.location.replace("?_job="+$(this).val());
        $(this).val('');
    });

    $("#search-todo").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
        $(this).val('');
    });

    $("#btn-todo-setting").on("click", function () {
        $("#todo-setting-div").toggle();
    });

    activate_to_do_list_data();

    $('[id^="btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'red');
    });
        
    $('[id^="btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', '');
    });
    
    $("#btn-doc-url").on( "click", function() {
        window.location.replace($("#btn-doc-url").val());
    })

    $("#btn-workflow").on( "click", function() {
        get_todo_action_list_data($("#todo-id").val());
    })

    function get_todo_list_data(){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_todo_list_data',
            },
            success: function (response) {            
                for(index=0;index<50;index++) {
                    $(".todo-list-"+index).hide().empty();
                }
                $.each(response, function (index, value) {
                    $(".todo-list-" + index).attr("id", "edit-todo-" + value.todo_id);
                    const output = `
                        <td style="text-align:center;">${value.todo_title}</td>
                        <td>${value.doc_title}</td>
                    `;
                    if (value.due_color==1){
                        output += `<td style="text-align:center; color:red;">${value.due_date}</td>`;
                    } else {
                        output += `<td style="text-align:center;">${value.due_date}</td>`;
                    }                    
                    $(".todo-list-"+index).append(output).show();
                })
                activate_to_do_list_data()
            },
            error: function (error) {
                console.error(error);                    
                alert(error);
            }
        });            
    }

    function activate_to_do_list_data(){
        $('[id^="edit-todo-"]').on("click", function () {
            const id = this.id.substring(10);
            // AJAX request
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'post',
                data: {
                    action: 'your_ajax_action',
                    _todo_id: id,
                },
                success: function (response) {
                    // Display the result
                    $('#result-container').html(response);
                    $('[id^="todo-action-"]').on("click", function () {
                        const id = this.id.substring(12);
                        alert('Hi, '+id)
                    });            
                                
                },
                error: function (error) {
                    console.log(error);
                }
            });
/*
            // Dialog content
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_todo_dialog_data',
                    '_todo_id': $("#todo-id").val(),
                },
                success: function (response) {
                    //$("#todo-dialog").dialog('open');
                    $("#doc-title").val(response.doc_title);
                    $("#doc-number").val(response.doc_number);
                    $("#doc-revision").val(response.doc_revision);
                    $("#btn-doc-url").val(response.doc_url);
                    $("#job-id").val(response.job_id);
                    $("#site-id").val(response.site_id);
                },
                error: function (error) {
                    console.error(error);                
                    alert(error);
                }
            });
    
            // Open the Dialog with dynamic buttons
            get_todo_dialog_buttons_data($("#todo-id").val());
*/
        });            
    }

    function get_todo_dialog_buttons_data(id) {
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
                //openTodoDialog(buttonData);

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
            
                $("#todo-dialog").dialog({
                    width: 600,
                    autoOpen: false,
                    modal: true,
                    buttons: buttons
                });
            
                // Open the dialog after it has been initialized
                $("#todo-dialog").dialog("open");

            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    function openTodoDialog(buttonData) {
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
    $("#todo-action-list-dialog").dialog({
        width: 500,
        modal: true,
        autoOpen: false,
    });

    // Todo job actions settings
    $("#btn-new-todo-action").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_todo_action_dialog_data',
                '_todo_id': $("#todo-id").val(),
            },
            success: function (response) {
                get_todo_action_list_data($("#todo-id").val());
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });                        

    function get_todo_action_list_data(id){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_todo_action_list_data',
                '_todo_id': id,
            },
            success: function (response) {            
                $("#todo-action-list-dialog").dialog('open');
                for(index=0;index<50;index++) {
                    $(".todo-action-list-"+index).hide().empty();
                }
                $.each(response, function (index, value) {
                    $(".todo-action-list-" + index).attr("id", "edit-job-action-todo-" + value.action_id);
                    const output = `
                        <td style="text-align:center;">${value.action_title}</td>
                        <td>${value.action_content}</td>
                        <td style="text-align:center;">${value.next_job}</td>
                        <td style="text-align:center;">${value.next_leadtime}</td>
                    `;
                    $(".todo-action-list-"+index).append(output).show();
                })

                $('[id^="edit-job-action-todo-"]').on( "click", function() {
                    const id = this.id.substring(21);
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
                            $("#todo-action-dialog").dialog('open');
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
                        '_next_job': $("#next-job").val(),
                        '_next_leadtime': $("#next-leadtime").val(),
                        '_doc_id': $("#doc-id").val(),
                    },
                    success: function (response) {
                        $("#todo-action-dialog").dialog('close');
                        get_todo_dialog_buttons_data($("#todo-id").val());
                        get_todo_action_list_data($("#todo-id").val());
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
                            get_todo_dialog_buttons_data($("#todo-id").val());
                            get_todo_action_list_data($("#todo-id").val());
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

// To-do list
jQuery(document).ready(function($) {

    $("#select-todo").on( "change", function() {
        window.location.replace("?_select_todo="+$(this).val());
        $(this).val('');
    });

    $("#search-todo").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
        $(this).val('');
    });

    $("#todo-setting").on("click", function () {
        $("#todo-setting-div").toggle();
    });

    $('[id^="edit-todo-"]').on("click", function () {
        const todo_id = this.id.substring(10);
        get_todo_dialog_data(todo_id)
    });            

    activate_todo_dialog_data(response);

    function activate_todo_dialog_data(response){
        $(".datepicker").datepicker({
            onSelect: function(dateText, inst) {
                $(this).val(dateText);
            }
        });
    
        $('[id^="todo-dialog-button-"]').on("click", function () {
            const action_id = this.id.substring(19);

            const ajaxData = {
                'action': 'set_todo_dialog_data',
            };
            ajaxData['_action_id'] = action_id;
            ajaxData['_doc_id'] = $("#doc-id").val();
            ajaxData['_report_id'] = $("#report-id").val();
        
            $.each(response.doc_fields, function(index, value) {
                const field_name_tag = '#' + value.field_name;
                if (value.field_type === 'checkbox' || value.field_type === 'radio') {
                    ajaxData[value.field_name] = $(field_name_tag).is(":checked") ? 1 : 0;
                } else {
                    ajaxData[value.field_name] = $(field_name_tag).val();
                }
            });

            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: ajaxData,
                success: function (response) {
                    window.location.replace(window.location.href);
                },
                error: function(error){
                    console.error(error);
                    //alert(error);
                }
            });
        });

        $("#new-action").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_todo_action_dialog_data',
                    '_todo_id': todo_id,
                },
                success: function (response) {
                    get_todo_dialog_data(todo_id)
                },
                error: function(error){
                    console.error(error);                    
                    //alert(error);
                }
            });    
        });

        $('[id^="edit-action-"]').on( "click", function() {
            const action_id = this.id.substring(12);
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
                    //alert(error);
                }
            });
        });

        $("#todo-action-dialog").dialog({
            width: 450,
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
                        },
                        success: function (response) {
                            $("#todo-action-dialog").dialog('close');
                            get_todo_dialog_data(todo_id)
                        },
                        error: function (error) {
                            console.error(error);                    
                            //alert(error);
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
                                get_todo_dialog_data(todo_id)
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
    }

    function get_todo_dialog_data(todo_id){
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_todo_dialog_data',
                _todo_id: todo_id,
            },
            success: function (response) {
                // Display the result
                $('#result-container').html(response.html_contain);
                activate_todo_dialog_data(response);
            },
            error: function (error) {
                console.error(error);
            }
        });
    }
})

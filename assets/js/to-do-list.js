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
                $("#doc-title").val(response.doc_title);
                $("#doc-number").val(response.doc_number);
                $("#doc-revision").val(response.doc_revision);
                //$("#doc-url").val(response.doc_url);
                $("#doc-url").attr("href", response.doc_url).empty().append(response.doc_url);
                $(`.btn-workflow`).attr("id", `btn-workflow-todo-list-${id}`);
                $('[id^="btn-"]').mouseover(function() {
                    $(this).css('cursor', 'pointer');
                    $(this).css('color', 'red');
                });
                    
                $('[id^="btn-"]').mouseout(function() {
                    $(this).css('cursor', 'default');
                    $(this).css('color', 'black');
                });
            
            
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
})

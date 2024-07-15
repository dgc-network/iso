// To-do list
jQuery(document).ready(function($) {

    $("#select-todo").on("change", function() {
        // Initialize an empty array to store query parameters
        var queryParams = [];
    
        // Check the selected value for each select element and add it to the queryParams array
        var todoValue = $("#select-todo").val();
        if (todoValue) {
            queryParams.push("_select_todo=" + todoValue);
        }

        // Combine all query parameters into a single string
        var queryString = queryParams.join("&");
    
        // Redirect to the new URL with all combined query parameters
        window.location.href = "?" + queryString;
    });

/*
    $("#select-todo").on( "change", function() {
        window.location.replace("?_select_todo="+$(this).val());
        $(this).val('');
    });
*/
    $("#search-todo").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
        $(this).val('');
    });

    $("#search-job").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
        $(this).val('');
    });

    $("#todo-setting").on("click", function () {
        $("#todo-setting-div").toggle();
    });

    $('[id^="edit-todo-"]').on("click", function () {
        const todo_id = this.id.substring(10);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_todo_dialog_data',
                _todo_id: todo_id,
            },
            success: function (response) {
                $('#result-container').html(response.html_contain);
                activate_todo_dialog_data(response.doc_fields);
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });

    });            

    var docFieldsValue = $("#doc-fields").val();

    // Check if docFieldsValue exists, is a non-empty string, and is a valid JSON array
    if (docFieldsValue && docFieldsValue.trim() !== '') {
        try {
            var docFields = JSON.parse(docFieldsValue);
    
            // Check if docFields is an array and not empty
            if (Array.isArray(docFields) && docFields.length > 0) {
                // Now docFields is an array that you can use in your JavaScript code
                activate_todo_dialog_data(docFields);
            } else {
                // Handle the case where docFields is not an array or is empty
                console.error('Invalid or empty docFields:', docFields);
            }
        } catch (error) {
            // Handle JSON parsing errors
            console.error('Error parsing docFields:', error);
        }
    }
    
    function activate_todo_dialog_data(doc_fields){
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
        
            $.each(doc_fields, function(index, value) {
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
                    alert(error);
                }
            });
        });

        $("#todo-dialog-exit").on("click", function () {
            window.location.replace(window.location.href);
        });
    }
})

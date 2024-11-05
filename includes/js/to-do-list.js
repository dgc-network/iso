// To-do list
jQuery(document).ready(function($) {

    // Select the element to observe
    const targetNode = document.getElementById("get-todo-id");

    if (targetNode) {
        // Set up the mutation observer to watch for attribute changes
        const observer = new MutationObserver(function(mutationsList) {
            for (let mutation of mutationsList) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                    // AJAX request when value changes
                    $.ajax({
                        url: ajax_object.ajax_url,
                        type: 'post',
                        data: {
                            action: 'get_todo_dialog_data',
                            _todo_id: $(targetNode).attr("value"),
                        },
                        success: function(response) {
                            $('#result-container').html(response.html_contain);
                            activate_todo_dialog_data(response.doc_fields);
                        },
                        error: function(error) {
                            console.error(error);
                            alert('An error occurred. Please try again.');
                        }
                    });
                }
            }
        });
    
        // Observe the target node for attribute changes
        observer.observe(targetNode, { attributes: true });
    } else {
        console.error("Element with ID 'get-todo-id' not found.");
    }
/*    
    // Select the element to observe
    const targetNode = document.getElementById("get-todo-id");

    // Set up the mutation observer to watch for attribute changes
    const observer = new MutationObserver(function(mutationsList) {
        for (let mutation of mutationsList) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                // AJAX request when value changes
                $.ajax({
                    url: ajax_object.ajax_url,
                    type: 'post',
                    data: {
                        action: 'get_todo_dialog_data',
                        _todo_id: $("#get-todo-id").val(),
                    },
                    success: function(response) {
                        $('#result-container').html(response.html_contain);
                        activate_todo_dialog_data(response.doc_fields);
                    },
                    error: function(error) {
                        console.error(error);
                        alert(error);
                    }
                });
            }
        }
    });

    // Start observing the target element for attribute changes
    observer.observe(targetNode, { attributes: true });
*/
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

    $("#search-todo").on( "change", function() {
        window.location.replace("?_search="+$(this).val()+"&paged=1");
        $(this).val('');
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

    $('[id^="view-todo-"]').on("click", function () {
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

    function activate_todo_dialog_data(doc_fields){
        $('[id^="todo-dialog-button-"]').on("click", function () {
            const action_id = this.id.substring(19);
            const ajaxData = {
                'action': 'set_todo_dialog_data',
            };
            ajaxData['_action_id'] = action_id;

            $.each(doc_fields, function(index, value) {
                const field_id_tag = '#' + value.field_id;
                if (value.field_type === 'checkbox' || value.field_type === 'radio') {
                    ajaxData[value.field_id] = $(field_id_tag).is(":checked") ? 1 : 0;
                } else {
                    ajaxData[value.field_id] = $(field_id_tag).val();
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

    $("#search-start-job").on( "change", function() {
        window.location.replace("?_select_todo=start-job&_search="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $('[id^="edit-start-job-"]').on("click", function () {
        const job_id = this.id.substring(15);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_start_job_dialog_data',
                _job_id: job_id,
            },
            success: function (response) {
                $('#result-container').html(response.html_contain);
                activate_start_job_dialog_data(response.doc_fields);
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });

    });            

    function activate_start_job_dialog_data(doc_fields){
        $('[id^="start-job-dialog-button-"]').on("click", function () {
            const action_id = this.id.substring(24);
            const ajaxData = {
                'action': 'set_start_job_dialog_data',
            };
            ajaxData['_action_id'] = action_id;

            $.each(doc_fields, function(index, value) {
                const field_id_tag = '#' + value.field_id;
                if (value.field_type === 'checkbox' || value.field_type === 'radio') {
                    ajaxData[value.field_id] = $(field_id_tag).is(":checked") ? 1 : 0;
                } else {
                    ajaxData[value.field_id] = $(field_id_tag).val();
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

        $("#job-dialog-exit").on("click", function () {
            window.location.replace(window.location.href);
        });
    }
})

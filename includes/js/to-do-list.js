// To-do list
$(document).ready(function () {
    console.log("Document is ready");

});

jQuery(document).ready(function($) {

    const targetNode = document.getElementById("get-todo-id");

    if (!targetNode) {
        console.error("Target node 'get-todo-id' not found");
        return;
    } else {
        console.log("Target node 'get-todo-id' found");
    }

    // Log value on change
    targetNode.addEventListener('change', function() {
        console.log("Change event triggered, new value: " + targetNode.value);
    });

    // Also log input events
    targetNode.addEventListener('input', function() {
        console.log("Input event triggered, new value: " + targetNode.value);
    });

    // Set up the mutation observer
    const observer = new MutationObserver(function(mutationsList) {
        for (let mutation of mutationsList) {
            console.log("I am here"); // This should trigger if there are mutations
            if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                console.log("Mutation observer triggered: value changed");
            } else {
                console.log(`Mutation observed: ${mutation.type}, attribute changed: ${mutation.attributeName}`);
            }
        }
    });

    // Start observing for attribute changes
    observer.observe(targetNode, { attributes: true, attributeFilter: ['value'] });

    console.log("Starting observation on 'get-todo-id' for value attribute changes");
/*
    const targetNode = document.getElementById("get-todo-id");

    if (!targetNode) {
        console.error("Target node 'get-todo-id' not found");
        return;
    } else {
        console.log("Target node 'get-todo-id' found");
    }

    // Set up the mutation observer to watch for attribute changes
    const observer = new MutationObserver(function(mutationsList) {
        for (let mutation of mutationsList) {
            console.log("This should trigger if there are mutations"); // This should trigger if there are mutations

            if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                console.log("Mutation observer triggered: value changed");

                // AJAX request when value changes
                $.ajax({
                    url: ajax_object.ajax_url,
                    type: 'post',
                    data: {
                        action: 'get_todo_dialog_data',
                        _todo_id: $("#get-todo-id").val(),
                    },
                    success: function(response) {
                        console.log("AJAX success, response received");
                        $('#result-container').html(response.html_contain);
                        activate_todo_dialog_data(response.doc_fields);
                    },
                    error: function(error) {
                        console.error("AJAX error:", error);
                        alert("AJAX error occurred");
                    }
                });
            } else {
                console.log(`Mutation observed: ${mutation.type}, attribute changed: ${mutation.attributeName}`);
            }
        }
    });

    // Log when observing starts
    console.log("Starting observation on 'get-todo-id' for value attribute changes");

    // Observe the target node for attribute changes
    observer.observe(targetNode, { attributes: true, attributeFilter: ['value'] });

    // Add an input event listener as a backup
    targetNode.addEventListener('input', function() {
        console.log("Input event triggered, value changed");
        // You can call the AJAX request directly if needed
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_todo_dialog_data',
                _todo_id: targetNode.value, // Use the value directly
            },
            success: function(response) {
                console.log("AJAX success, response received");
                $('#result-container').html(response.html_contain);
                activate_todo_dialog_data(response.doc_fields);
            },
            error: function(error) {
                console.error("AJAX error:", error);
                alert("AJAX error occurred");
            }
        });
    });
/*
    //window.onload = function() {
    //    console.log("Window onload triggered");
    
    //$.addEventListener("DOMContentLoaded", function() {
        //console.log("Document is ready");
    
        // Select the element to observe
        const targetNode = document.getElementById("get-todo-id");
    
        if (!targetNode) {
            console.error("Target node 'get-todo-id' not found");
            return;
        } else {
            console.log("Target node 'get-todo-id' found");
        }
    
        // Set up the mutation observer to watch for attribute changes
        const observer = new MutationObserver(function(mutationsList) {
            for (let mutation of mutationsList) {
                console.log("I am here");
                if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                    console.log("Mutation observer triggered: value changed");
                    
                    // AJAX request when value changes
                    $.ajax({
                        url: ajax_object.ajax_url,
                        type: 'post',
                        data: {
                            action: 'get_todo_dialog_data',
                            _todo_id: $("#get-todo-id").val(),
                        },
                        success: function(response) {
                            console.log("AJAX success, response received");
                            $('#result-container').html(response.html_contain);
                            activate_todo_dialog_data(response.doc_fields);
                        },
                        error: function(error) {
                            console.error("AJAX error:", error);
                            alert("AJAX error occurred");
                        }
                    });
                } else {
                    console.log(`Mutation observed: ${mutation.type}, attribute changed: ${mutation.attributeName}`);
                }
            }
        });
    
        // Log when observing starts
        console.log("Starting observation on 'get-todo-id' for value attribute changes");
    
        // Observe the target node for attribute changes
        observer.observe(targetNode, { attributes: true, attributeFilter: ['value'] });
    //});
    //};
/*    
    // Select the element to observe
    const targetNode = document.getElementById("get-todo-id");

    if (targetNode) {
        console.log("Target node found:", targetNode);
    
        // Set up the mutation observer to watch for attribute changes
        const observer = new MutationObserver(function(mutationsList) {
            console.log("Mutation observer triggered");
    
            for (let mutation of mutationsList) {
                console.log("Mutation detected:", mutation);
    
                if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                    console.log("Value attribute changed on target node");
    
                    // AJAX request when value changes
                    const todoId = $(targetNode).attr("value");
                    console.log("Sending AJAX request with _todo_id:", todoId);
    
                    $.ajax({
                        url: ajax_object.ajax_url,
                        type: 'post',
                        data: {
                            action: 'get_todo_dialog_data',
                            _todo_id: todoId,
                        },
                        success: function(response) {
                            console.log("AJAX request successful:", response);
    
                            $('#result-container').html(response.html_contain);
                            activate_todo_dialog_data(response.doc_fields);
                        },
                        error: function(error) {
                            console.error("AJAX request failed:", error);
                            alert('An error occurred. Please try again.');
                        }
                    });
                }
            }
        });
    
        // Observe the target node for attribute changes
        observer.observe(targetNode, { attributes: true });
        console.log("Observer started on target node:", targetNode);
    } else {
        console.error("Element with ID 'get-todo-id' not found.");
    }
    
/*
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

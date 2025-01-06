// To-do list
jQuery(document).ready(function($) {
    const canvas = document.getElementById('signature-pad');
    if (canvas) {
        canvas.width = window.innerWidth - 10;

        const context = canvas.getContext('2d');
        let isDrawing = false;

        // Configure drawing styles
        context.strokeStyle = "#000000";
        context.lineWidth = 2;

        // Helper function to get touch position
        const getCanvasPosition = (touch) => {
            const rect = canvas.getBoundingClientRect();
            return {
                x: touch.clientX - rect.left,
                y: touch.clientY - rect.top,
            };
        };

        // Mouse Events
        $(canvas).on('mousedown', function (e) {
            isDrawing = true;
            context.beginPath();
            context.moveTo(e.offsetX, e.offsetY);
        });

        $(canvas).on('mousemove', function (e) {
            if (isDrawing) {
                context.lineTo(e.offsetX, e.offsetY);
                context.stroke();
            }
        });

        $(document).on('mouseup', function () {
            isDrawing = false;
        });

        // Touch Events
        $(canvas).on('touchstart', function (e) {
            e.preventDefault();
            isDrawing = true;
            const touchPosition = getCanvasPosition(e.touches[0]);
            context.beginPath();
            context.moveTo(touchPosition.x, touchPosition.y);
        });

        $(canvas).on('touchmove', function (e) {
            e.preventDefault();
            if (isDrawing) {
                const touchPosition = getCanvasPosition(e.touches[0]);
                context.lineTo(touchPosition.x, touchPosition.y);
                context.stroke();
            }
        });

        $(document).on('touchend', function () {
            isDrawing = false;
        });

        // Clear button functionality
        $('#clear-signature').on('click', function () {
            context.clearRect(0, 0, canvas.width, canvas.height);
        });

        // Redraw button functionality
        $('#redraw-signature').on('click', function () {
            $('#signature-pad-div').show();
            $('#signature-image-div').hide();
        });
    }

    // todo-list
    $("#select-todo").on("change", function() {
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Remove or Update the parameters
        urlParams.delete("_search");
        urlParams.set("_select_todo", $(this).val());
        urlParams.set("paged", 1);
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    });

    // start-job
    const prevJobId = $("#prev-job-id").val();
    const nextJobId = $("#next-job-id").val();

    // Function to navigate to the previous or next record
    function navigateToJob(Id) {
        if (Id) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set("_job_id", Id);
            window.location.href = currentUrl.toString();
        }
    }

    // Keyboard navigation
    $(document).on("keydown", function (event) {
        if (event.ctrlKey && event.key === "ArrowRight" && nextJobId) {
            navigateToJob(nextJobId); // Move to the next record
        } else if (event.ctrlKey && event.key === "ArrowLeft" && prevJobId) {
            navigateToJob(prevJobId); // Move to the previous record
        }
    });

    // Touch navigation for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    $(document).on("touchstart", function (event) {
        touchStartX = event.originalEvent.changedTouches[0].screenX;
    });

    $(document).on("touchend", function (event) {
        touchEndX = event.originalEvent.changedTouches[0].screenX;
        handleJobSwipe();
    });

    function handleJobSwipe() {
        const swipeThreshold = 50; // Minimum swipe distance
        if (touchEndX < touchStartX - swipeThreshold && nextJobId) {
            navigateToJob(nextJobId); // Swipe left: Move to the next record
        } else if (touchEndX > touchStartX + swipeThreshold && prevJobId) {
            navigateToJob(prevJobId); // Swipe right: Move to the previous record
        }
    }

    // todo-list
    const prevTodoId = $("#prev-todo-id").val();
    const nextTodoId = $("#next-todo-id").val();

    // Function to navigate to the previous or next record
    function navigateToTodo(Id) {
        if (Id) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set("_todo_id", Id);
            window.location.href = currentUrl.toString();
        }
    }

    // Keyboard navigation
    $(document).on("keydown", function (event) {
        if (event.ctrlKey && event.key === "ArrowRight" && nextTodoId) {
            navigateToTodo(nextTodoId); // Move to the next record
        } else if (event.ctrlKey && event.key === "ArrowLeft" && prevTodoId) {
            navigateToTodo(prevTodoId); // Move to the previous record
        }
    });

    // Touch navigation for mobile
    //let touchStartX = 0;
    //let touchEndX = 0;

    $(document).on("touchstart", function (event) {
        touchStartX = event.originalEvent.changedTouches[0].screenX;
    });

    $(document).on("touchend", function (event) {
        touchEndX = event.originalEvent.changedTouches[0].screenX;
        handleTodoSwipe();
    });

    function handleTodoSwipe() {
        const swipeThreshold = 50; // Minimum swipe distance
        if (touchEndX < touchStartX - swipeThreshold && nextTodoId) {
            navigateToTodo(nextTodoId); // Swipe left: Move to the next record
        } else if (touchEndX > touchStartX + swipeThreshold && prevTodoId) {
            navigateToTodo(prevTodoId); // Swipe right: Move to the previous record
        }
    }

    // action-log
    const prevLogId = $("#prev-log-id").val();
    const nextLogId = $("#next-log-id").val();

    // Function to navigate to the previous or next record
    function navigateToLog(Id) {
        if (Id) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set("_log_id", Id);
            window.location.href = currentUrl.toString();
        }
    }

    // Keyboard navigation
    $(document).on("keydown", function (event) {
        if (event.ctrlKey && event.key === "ArrowRight" && nextLogId) {
            navigateToLog(nextLogId); // Move to the next record
        } else if (event.ctrlKey && event.key === "ArrowLeft" && prevLogId) {
            navigateToLog(prevLogId); // Move to the previous record
        }
    });

    // Touch navigation for mobile
    //let touchStartX = 0;
    //let touchEndX = 0;

    $(document).on("touchstart", function (event) {
        touchStartX = event.originalEvent.changedTouches[0].screenX;
    });

    $(document).on("touchend", function (event) {
        touchEndX = event.originalEvent.changedTouches[0].screenX;
        handleLogSwipe();
    });

    function handleLogSwipe() {
        const swipeThreshold = 50; // Minimum swipe distance
        if (touchEndX < touchStartX - swipeThreshold && nextLogId) {
            navigateToLog(nextLogId); // Swipe left: Move to the next record
        } else if (touchEndX > touchStartX + swipeThreshold && prevLogId) {
            navigateToLog(prevLogId); // Swipe right: Move to the previous record
        }
    }

    // start-job
    $("#search-start-job").on( "change", function() {
        const urlParams = new URLSearchParams(window.location.search);
        var selectValue = $("#select-todo").val();
        // Remove or Update the parameters
        if (selectValue) urlParams.set("_select_todo", selectValue);
        urlParams.set("_search", $(this).val());
        urlParams.set("paged", 1);
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    });

    $('[id^="edit-start-job-"]').on("click", function () {
        const job_id = this.id.substring(15);
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Remove or Add the parameters
        urlParams.set("_job_id", job_id);
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    });            

    function get_start_job_dialog_data(job_id, callback) {
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_start_job_dialog_data',
                _job_id: job_id,
            },
            success: function (response) {
                if (typeof callback === "function") {
                    callback(null, response); // Pass the data to the callback
                }
            },
            error: function (error) {
                console.error(error);
                alert('An error occurred. Please try again.');
                if (typeof callback === "function") {
                    callback(error, null); // Pass the error to the callback
                }
            }
        });
    }

    activate_start_job_dialog_data();
    function activate_start_job_dialog_data(){
        $('[id^="start-job-dialog-button-"]').on("click", function () {
            const action_id = this.id.substring(24);
            const ajaxData = {
                'action': 'set_start_job_dialog_data',
            };
            ajaxData['_action_id'] = action_id;

            get_start_job_dialog_data($("#job-id").val(), function (error, response) {
                $.each(response.doc_field_keys, function(index, value) {
                    const field_id_tag = '#' + value.field_id;
                    if (value.field_type === 'checkbox' || value.field_type === 'radio') {
                        ajaxData[value.field_id] = $(field_id_tag).is(":checked") ? 1 : 0;
                    } else {
                        ajaxData[value.field_id] = $(field_id_tag).val();
    
                        if (value.default_value === '_post_number') {
                            ajaxData['_post_number'] = $(field_id_tag).val();
                        }
                        if (value.default_value === '_post_title') {
                            ajaxData['_post_title'] = $(field_id_tag).val();
                        }
                        if (value.default_value === '_post_content') {
                            ajaxData['_post_content'] = $(field_id_tag).val();
                        }
    
                        if (value.field_type === 'canvas') {
                            const dataURL = canvas.toDataURL('image/png');
                            ajaxData[value.field_id] = dataURL;
                            console.log("Signature saved as:", dataURL); // You can also use this URL for further processing
                        }
    
                        if (value.field_type === '_embedded') {
                            $.each(response.embedded_item_keys, function(index, inner_value) {
                                const embedded_field = String(value.field_id) + String(inner_value.embedded_item_id);
                                const embedded_field_tag = '#' + value.field_id + inner_value.embedded_item_id;
                                if (inner_value.embedded_item_type === 'checkbox' || inner_value.embedded_item_type === 'radio') {
                                    ajaxData[embedded_field] = $(embedded_field_tag).is(":checked") ? 1 : 0;
                                } else {
                                    ajaxData[embedded_field] = $(embedded_field_tag).val();
                                }
                            });
                        }
                    }
                });
    
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: ajaxData,
                    success: function (response) {
                        // Get existing URL parameters
                        const urlParams = new URLSearchParams(window.location.search);
                        // Remove or Update the parameters
                        urlParams.delete("_job_id");
                        urlParams.delete("_prompt");
                        urlParams.set("paged", 1);
                        // Redirect to the updated URL
                        window.location.href = "?" + urlParams.toString();
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            })
        });

        $("#exit-start-job").on("click", function () {
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or Update the parameters
            urlParams.delete("_job_id");
            urlParams.delete("_prompt");
            urlParams.set("paged", 1);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
        });
    }

    // todo-list
    $("#search-todo").on( "change", function() {
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        var selectValue = $("#select-todo").val();
        // Remove or Update the parameters
        if (selectValue) urlParams.set("_select_todo", selectValue);
        urlParams.set("_search", $(this).val());
        urlParams.set("paged", 1);
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    });

    $('[id^="edit-todo-"]').on("click", function () {
        const todo_id = this.id.substring(10);
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Add or update the `_todo_id` parameter
        urlParams.set("_todo_id", todo_id);
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    });            

    function get_todo_dialog_data(todo_id, callback) {
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_todo_dialog_data',
                _todo_id: todo_id,
            },
            success: function (response) {
                if (typeof callback === "function") {
                    callback(null, response); // Pass the data to the callback
                }
            },
            error: function (error) {
                console.error(error);
                alert('An error occurred. Please try again.');
                if (typeof callback === "function") {
                    callback(error, null); // Pass the error to the callback
                }
            }
        });
    }

    activate_todo_dialog_data();
    function activate_todo_dialog_data(){
        $('[id^="todo-dialog-button-"]').on("click", function () {
            const action_id = this.id.substring(19);
            const ajaxData = {
                'action': 'set_todo_dialog_data',
            };
            ajaxData['_action_id'] = action_id;

            get_todo_dialog_data($("#todo-id").val(), function (error, response) {
                $.each(response.doc_field_keys, function(index, value) {
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
                        // Get existing URL parameters
                        const urlParams = new URLSearchParams(window.location.search);
                        // Remove or Update the parameters
                        urlParams.delete("_todo_id");
                        urlParams.set("paged", 1);
                        // Redirect to the updated URL
                        window.location.href = "?" + urlParams.toString();
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });    
            })
        });

        $("#todo-dialog-exit").on("click", function () {
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or Update the parameters
            urlParams.delete("_todo_id");
            urlParams.set("paged", 1);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
        });
    }

    // action-log
    $('[id^="edit-action-log"]').on("click", function () {
        const log_id = this.id.substring(15);
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Remove or update the parameters
        urlParams.set("_log_id", log_id);
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    });            

    activate_action_log_dialog_data();
    function activate_action_log_dialog_data(){
        $("#del-action-log").on("click", function () {
            if (window.confirm("Are you sure you want to delete this action log?")) {
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'del_action_log_dialog_data',
                        '_log_id': $("#log-id").val(),
                    },
                    success: function (response) {
                        //$("#result-container").html(response.html_contain);
                        // Get existing URL parameters
                        const urlParams = new URLSearchParams(window.location.search);
                        // Remove or update the parameters
                        urlParams.delete('_log_id');
                        // Redirect to the updated URL
                        window.location.href = "?" + urlParams.toString();
                    },
                    error: function (error) {
                        console.error(error);
                        alert(error);
                    }
                });
            }
        });

        $("#exit-action-log").on("click", function () {
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or Update the parameters
            urlParams.delete("_log_id");
            //urlParams.set("paged", 1);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
        });
    }
})

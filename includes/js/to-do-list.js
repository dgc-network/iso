// To-do list
jQuery(document).ready(function($) {
    // start-job
    const prevJobId = $("#prev-job-id").val();
    const nextJobId = $("#next-job-id").val();

    // Function to navigate to the previous or next record
    function navigateToDoc(Id) {
        if (Id) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set("_job_id", Id);
            window.location.href = currentUrl.toString();
        }
    }

    // Keyboard navigation
    $(document).on("keydown", function (event) {
        if (event.key === "ArrowRight" && nextJobId) {
            navigateToDoc(nextJobId); // Move to the next record
        } else if (event.key === "ArrowLeft" && prevJobId) {
            navigateToDoc(prevJobId); // Move to the previous record
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
            navigateToDoc(nextJobId); // Swipe left: Move to the next record
        } else if (touchEndX > touchStartX + swipeThreshold && prevJobId) {
            navigateToDoc(prevJobId); // Swipe right: Move to the previous record
        }
    }


    // Check if the target node exists
    const targetNode = document.getElementById("get-todo-id");
    if (targetNode) {
        console.log("Target node 'get-todo-id' found");
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_todo_dialog_data',
                _todo_id: targetNode.value,
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

    $("#select-todo").on("change", function() {
        // Initialize an empty array to store query parameters
        var queryParams = [];    
        // Check the selected value for each select element and add it to the queryParams array
        var selectValue = $("#select-todo").val();
        if (selectValue) {
            queryParams.push("_select_todo=" + selectValue);
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
                _mode: 'view_mode',
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
                    // Get the current URL
                    const currentUrl = window.location.href;
                    // Check if the current URL includes '/to-do-list/?_id='
                    if (currentUrl.includes('/to-do-list/?_id=')) {
                        // Redirect to '/to-do-list'
                        window.location.replace('/to-do-list');
                    } else {
                        window.location.replace(window.location.href);
                    }
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#todo-dialog-exit").on("click", function () {
            // Get the current URL
            const currentUrl = window.location.href;
            // Check if the current URL includes '/to-do-list/?_id='
            if (currentUrl.includes('/to-do-list/?_id=')) {
                // Redirect to '/to-do-list'
                window.location.replace('/to-do-list');
            } else {
                window.location.replace(window.location.href);
            }
        });
    }

    $("#search-start-job").on( "change", function() {
        window.location.replace("?_select_todo=start-job&_search="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $('[id^="edit-start-job-"]').on("click", function () {
        const job_id = this.id.substring(15);
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Add or update the `_job_id` parameter
        urlParams.set("_job_id", job_id);
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
/*
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
*/        
    });            

    function activate_start_job_dialog_data(doc_fields){
        const canvas = document.getElementById('signature-pad');
        if (canvas) {
            canvas.width = window.innerWidth-10;

            const context = canvas.getContext('2d');
            let isDrawing = false;
            
            // Set up drawing styles
            context.strokeStyle = "#000000";
            context.lineWidth = 2;
            
            // Mouse Events for drawing (Desktop)
            $('#signature-pad').mousedown(function(e) {
                isDrawing = true;
                context.beginPath();
                context.moveTo(e.offsetX, e.offsetY);
            });
            
            $('#signature-pad').mousemove(function(e) {
                if (isDrawing) {
                    context.lineTo(e.offsetX, e.offsetY);
                    context.stroke();
                }
            });
            
            $(document).mouseup(function() {
                isDrawing = false;
            });
            
            // Get canvas offset for touch position calculations
            const getCanvasPosition = (touch) => {
                const rect = canvas.getBoundingClientRect();
                return {
                    x: touch.clientX - rect.left,
                    y: touch.clientY - rect.top
                };
            };
    
            // Touch start event
            canvas.addEventListener('touchstart', function(e) {
                e.preventDefault();
                isDrawing = true;
                const touchPosition = getCanvasPosition(e.touches[0]);
                context.beginPath();
                context.moveTo(touchPosition.x, touchPosition.y);
            }, { passive: false });
            
            // Touch move event
            canvas.addEventListener('touchmove', function(e) {
                e.preventDefault();
                if (isDrawing) {
                    const touchPosition = getCanvasPosition(e.touches[0]);
                    context.lineTo(touchPosition.x, touchPosition.y);
                    context.stroke();
                }
            }, { passive: false });
    
            $(document).on('touchend', function() {
                isDrawing = false;
            });
            
            // Clear button functionality
            $('#clear-signature').click(function() {
                context.clearRect(0, 0, canvas.width, canvas.height);
            });
            
            // Redraw button functionality
            $('#redraw-signature').click(function() {
                $('#signature-pad-div').show();
                $('#signature-image-div').hide();
            });
        }

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

                    if (value.field_type === '_embedded' || value.field_type === '_planning' || value.field_type === '_select') {
                        $.each(response.sub_item_fields, function(index, inner_value) {
                            const embedded_field = String(value.field_id) + String(inner_value.sub_item_id);
                            const embedded_field_tag = '#' + value.field_id + inner_value.sub_item_id;
                            if (inner_value.sub_item_type === 'checkbox' || inner_value.sub_item_type === 'radio') {
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

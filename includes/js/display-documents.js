// display documents
jQuery(document).ready(function($) {
    // copyToClipboard
    function copyToClipboard(text) {
        // Create a temporary textarea element
        var textarea = $("<textarea>")
            .val(text)
            .appendTo("body")
            .select();
        // Execute the copy command
        document.execCommand("copy");
        // Remove the textarea from the document
        textarea.remove();
    }

    // document
    const prevDocId = $("#prev-doc-id").val();
    const nextDocId = $("#next-doc-id").val();

    // Function to navigate to the previous or next record
    function navigateToDoc(Id) {
        if (Id) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set("_doc_id", Id);
            window.location.href = currentUrl.toString();
        }
    }

    // Keyboard navigation
    $(document).on("keydown", function (event) {
        if (event.ctrlKey && event.key === "ArrowRight" && nextDocId) {
            navigateToDoc(nextDocId); // Move to the next record
        } else if (event.ctrlKey && event.key === "ArrowLeft" && prevDocId) {
            navigateToDoc(prevDocId); // Move to the previous record
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
        handleDocSwipe();
    });

    function handleDocSwipe() {
        const swipeThreshold = 50; // Minimum swipe distance
        if (touchEndX < touchStartX - swipeThreshold && nextDocId) {
            navigateToDoc(nextDocId); // Swipe left: Move to the next record
        } else if (touchEndX > touchStartX + swipeThreshold && prevDocId) {
            navigateToDoc(prevDocId); // Swipe right: Move to the previous record
        }
    }

    // doc-report
    const prevReportId = $("#prev-report-id").val();
    const nextReportId = $("#next-report-id").val();

    // Function to navigate to the previous or next record
    function navigateToReport(Id) {
        if (Id) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set("_report_id", Id);
            window.location.href = currentUrl.toString();
        }
    }

    // Keyboard navigation
    $(document).on("keydown", function (event) {
        if (event.ctrlKey && event.key === "ArrowRight" && nextReportId) {
            navigateToReport(nextReportId); // Move to the next record
        } else if (event.ctrlKey && event.key === "ArrowLeft" && prevReportId) {
            navigateToReport(prevReportId); // Move to the previous record
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
        handleReportSwipe();
    });

    function handleReportSwipe() {
        const swipeThreshold = 50; // Minimum swipe distance
        if (touchEndX < touchStartX - swipeThreshold && nextReportId) {
            navigateToReport(nextReportId); // Swipe left: Move to the next record
        } else if (touchEndX > touchStartX + swipeThreshold && prevReportId) {
            navigateToReport(prevReportId); // Swipe right: Move to the previous record
        }
    }

    // statement
    $("#exit-statement").on("click", function () {
        window.location.replace('/');
    })

    $("#statement-page1-next-step").on("click", function () {
        // Initialize an empty array to store the key-value pairs
        const keyValuePairs = [];
        // Select all elements with the specified class and iterate over them
        $('.sub-item-class').each(function() {
            // Use the 'id' attribute as the key
            const key = $(this).attr('id');
            let value;
            // Check if the element is a checkbox or radio button
            if ($(this).is(':checkbox') || $(this).is(':radio')) {
                // Set the value to 1 if checked, otherwise set it to 0
                value = $(this).is(':checked') ? 1 : 0;
            } else {
                // Get the value (for input elements) or text content (for others)
                value = $(this).val() || $(this).text();
            }
            // Add the key-value pair to the array
            keyValuePairs.push({ [key]: value });
        });
        // Now, keyValuePairs contains the key-value pairs of all elements with the specified class
        console.log(keyValuePairs);

        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_iso_document_statement',
                _keyValuePairs : keyValuePairs,
            },
            success: function (response) {
                console.log(response)
                iso_category_id = $("#iso-category-id").val();
                window.location.replace('/display-documents/?_statement='+iso_category_id+'&_paged=2');
            },
            error: function(error){
                console.error(error); 
                alert(error);
            }
        });
    })

    $("#statement-page2-prev-step").on("click", function () {
        iso_category_id = $("#iso-category-id").val();
        window.location.replace('/display-documents/?_statement='+iso_category_id+'&_paged=1');
    })

    $("#proceed-copy-statement").on("click", function () {
        // Initialize an empty array to store the IDs of selected (checked) elements
        const duplicated_ids = [];

        // Select all elements with the specified class and iterate over them
        $('.copy-document-class').each(function() {
            // Get the 'id' attribute of the current element
            const key = $(this).attr('id');
            let value;
        
            // Check if the element is a checkbox or radio button
            if ($(this).is(':checkbox') || $(this).is(':radio')) {
                // Set the value to 1 if the checkbox/radio is checked, otherwise set it to 0
                value = $(this).is(':checked') ? 1 : 0;
        
                // If the element is checked, add the 'id' to the duplicated_ids array
                if (value == 1) {
                    duplicated_ids.push(key);  // Store the ID of checked elements
                }
            }
        });
        
        // Now, duplicated_ids contains the IDs of all checked elements with the specified class
        console.log(duplicated_ids);
        
        // Count the number of checked elements by getting the length of the array
        const countDuplicatedIds = duplicated_ids.length;
        console.log('Number of checked elements:', countDuplicatedIds);

        iso_category_title = $("#iso-category-title").val();

        if (window.confirm("Are you sure you want to have "+countDuplicatedIds+" new copies from "+ iso_category_title)) {
            // Show the custom alert message
            var alertBox = $("<div class='custom-alert'>Data processing...</div>");
            $("body").append(alertBox);

            // Center the alert box
            alertBox.css({
                position: "fixed",
                top: "50%",
                left: "50%",
                transform: "translate(-50%, -50%)",
            });

            alertBox.fadeIn(500);

            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_iso_document_statement',
                    _duplicated_ids : duplicated_ids,
                },
                success: function (response) {
                    console.log(response)
                    window.location.replace('/display-documents/');
                    alertBox.fadeOut(500, function() {
                        $(this).remove();
                    });
                },
                error: function(error){
                    console.error(error);                    
                    alert(error);
                }
            });

        } else {
            iso_category_id = $("#iso-category-id").val();
            window.location.replace('/display-documents/?_statement='+iso_category_id+'&_paged=1');
        }
    })

    // document
    $("#select-category").on( "change", function() {
        window.location.replace("?_category="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $("#search-document").on( "change", function() {
        window.location.replace("?_search="+$(this).val()+"&paged=1");
        $(this).val('');
    });

    $("#document-setting-button").on("click", function () {
        $("#document-setting-dialog").dialog('open');
    });

    $("#document-setting-dialog").dialog({
        width: 390,
        modal: true,
        autoOpen: false,
    });

    $("#new-document").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_document_dialog_data',
            },
            success: function (response) {
                $('#result-container').html(response.html_contain);
                activate_document_dialog_data($("#doc-id").val());
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });

    $('[id^="edit-document-"]').on("click", function () {
        const doc_id = this.id.substring(14);
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Add or update the `_doc_id` parameter
        urlParams.set("_doc_id", doc_id);
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    });            

    activate_document_dialog_data();
    function activate_document_dialog_data(){
        doc_id = $('#doc-id').val();
        activate_doc_action_list_data(doc_id);
        activate_doc_field_list_data(doc_id);

        $("#doc-frame-label").on("click", function () {
            $("#doc-report-div").toggle();
            $("#doc-frame-div").toggle();
            $("#is-doc-report").val(1)
        });

        $("#doc-field-label").on("click", function () {
            $("#doc-report-div").toggle();
            $("#doc-frame-div").toggle();
            $("#is-doc-report").val(0)
        });

        $("#doc-frame-job-setting").on("click", function () {
            $("#doc-frame-job-setting").toggle()
            $(".mermaid").toggle()
            $("#job-setting-div").toggle();
        });

        $("#doc-report-job-setting").on("click", function () {
            $("#doc-report-job-setting").toggle();
            $(".mermaid").toggle()
            $("#job-setting-div").toggle();
        });

        $("#save-document-button").on("click", function() {
            const ajaxData = {
                'action': 'set_document_dialog_data',
            };
            ajaxData['_doc_id'] = doc_id;
            ajaxData['_job_number'] = $("#job-number").val();
            ajaxData['_job_title'] = $("#job-title").val();
            ajaxData['_job_content'] = $("#job-content").val();
            ajaxData['_department_id'] = $("#department-id").val();
            ajaxData['_doc_number'] = $("#doc-number").val();
            ajaxData['_doc_title'] = $("#doc-title").val();
            ajaxData['_doc_revision'] = $("#doc-revision").val();
            ajaxData['_doc_category'] = $("#doc-category").val();
            ajaxData['_doc_frame'] = $("#doc-frame").val();
            ajaxData['_is_doc_report'] = $("#is-doc-report").val();
            ajaxData['_system_doc'] = $("#system-doc").val();

            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: ajaxData,
                success: function (response) {
                    // Get the current URL
                    var currentUrl = window.location.href;
                    // Create a URL object
                    var url = new URL(currentUrl);
                    // Remove the specified parameter
                    url.searchParams.delete('_doc_id');
                    // Get the modified URL
                    var modifiedUrl = url.toString();
                    // Reload the page with the modified URL
                    window.location.replace(modifiedUrl);
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#del-document-button").on("click", function() {
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
                        // Get the current URL
                        var currentUrl = window.location.href;
                        // Create a URL object
                        var url = new URL(currentUrl);
                        // Remove the specified parameter
                        url.searchParams.delete('_doc_id');
                        // Get the modified URL
                        var modifiedUrl = url.toString();
                        // Reload the page with the modified URL
                        window.location.replace(modifiedUrl);
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            }
        });

        $("#doc-report-preview").on("click", function () {
            get_doc_report_list_data(doc_id);
        });

        $("#doc-frame-preview").on("click", function () {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: 'json',
                data: {
                    action: 'get_doc_frame_contain',
                    _doc_id: doc_id,
                },
                success: function(response) {
                    $('#result-container').html(response.html_contain);
                    activate_doc_frame_contain_data(doc_id);
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#document-dialog-exit").on("click", function () {
            // Get the current URL
            var currentUrl = window.location.href;
            // Create a URL object
            var url = new URL(currentUrl);
            // Remove the specified parameter
            url.searchParams.delete('_doc_id');
            // Get the modified URL
            var modifiedUrl = url.toString();
            // Reload the page with the modified URL
            window.location.replace(modifiedUrl);
        });
    }

    activate_doc_frame_contain_data($("#doc-id").val());
    function activate_doc_frame_contain_data(doc_id){
        $("#share-document").on("click", function() {
            var homeAddress = window.location.origin;
            var textToCopy = homeAddress + "/display-documents/?_duplicate_document=" + doc_id;
            // Copy the text to clipboard
            copyToClipboard(textToCopy);
            // Show the custom alert message
            var alertBox = $("<div class='custom-alert'>Ducument is copied to clipboard</div>");
            $("body").append(alertBox);
            // Center the alert box
            alertBox.css({
                position: "fixed",
                top: "50%",
                left: "50%",
                transform: "translate(-50%, -50%)",
            });
            alertBox.fadeIn(500).delay(3000).fadeOut(500, function() {
                $(this).remove();
            });
        });
/*
        $("#action-log-button").on("click", function () {
            $("#action-log-div").toggle()
        });
*/
        $("#exit-doc-frame").on("click", function () {
            //window.location.replace(window.location.href);
            // Get the current URL
            var currentUrl = window.location.href;
            // Create a URL object
            var url = new URL(currentUrl);
            // Remove the specified parameter
            url.searchParams.delete('_doc_id');
            // Get the modified URL
            var modifiedUrl = url.toString();
            // Reload the page with the modified URL
            window.location.replace(modifiedUrl);
        });

    }

    // doc-field scripts
    function activate_doc_field_list_data(doc_id=false){
        $("#new-doc-field").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_doc_field_dialog_data',
                    '_doc_id': doc_id,
                },
                success: function (response) {
                    $('#fields-container').html(response.html_contain);
                    activate_doc_field_list_data(doc_id);
                },
                error: function(error){
                    console.error(error);                    
                    alert(error);
                }
            });    
        });

        $('#sortable-doc-field-list').sortable({
            update: function(event, ui) {
                const field_id_array = $(this).sortable('toArray', { attribute: 'data-field-id' });                
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: 'json',
                    data: {
                        action: 'sort_doc_field_list_data',
                        _field_id_array: field_id_array,
                    },
                    success: function(response) {
                        console.log(response);
                    },
                    error: function(error) {
                        console.error(error);
                    }
                });
            }
        });

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
                    $("#doc-field-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#doc-field-dialog").dialog("option", "buttons", {
                            "Save": function() {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_doc_field_dialog_data',
                                        '_doc_id': doc_id,
                                        '_field_id': $("#field-id").val(),
                                        '_field_title': $("#field-title").val(),
                                        '_field_type': $("#field-type").val(),
                                        '_default_value': $("#default-value").val(),
                                        '_listing_style': $("#listing-style").val(),
                                        '_order_field': $('#order-field').is(":checked") ? 'ASC' : '',
                                    },
                                    success: function (response) {
                                        $("#doc-field-dialog").dialog('close');
                                        $('#fields-container').html(response.html_contain);
                                        activate_doc_field_list_data(doc_id);
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
                                            '_doc_id': doc_id,
                                            '_field_id': $("#field-id").val(),
                                        },
                                        success: function (response) {
                                            $("#doc-field-dialog").dialog('close');
                                            $('#fields-container').html(response.html_contain);
                                            activate_doc_field_list_data(doc_id);
                                        },
                                        error: function(error){
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            }
                        });
                    }
                    $("#doc-field-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });
    
        $("#doc-field-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });    
    }

    // doc-action scripts
    function activate_doc_action_list_data(doc_id) {
        $("#new-doc-action").on("click", function() {
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_doc_action_dialog_data',
                    '_doc_id': doc_id,
                },
                success: function (response) {
                    $("#doc-action-list").html(response.html_contain);
                    activate_doc_action_list_data(doc_id);
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });

        $('[id^="edit-doc-action-"]').on("click", function () {
            const action_id = this.id.substring(16);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_doc_action_dialog_data',
                    '_action_id': action_id,
                },
                success: function (response) {
                    $("#doc-action-dialog").html(response.html_contain);
                    $("#doc-action-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#doc-action-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {
                "Save": function() {
                    jQuery.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_doc_action_dialog_data',
                            '_doc_id': doc_id,
                            '_action_id': $("#action-id").val(),
                            '_action_title': $("#action-title").val(),
                            '_action_content': $("#action-content").val(),
                            '_next_job': $("#next-job").val(),
                            '_next_leadtime': $("#next-leadtime").val(),
                        },
                        success: function (response) {
                            $("#doc-action-dialog").dialog('close');
                            $("#doc-action-list").html(response.html_contain);
                            activate_doc_action_list_data(doc_id);
                        },
                        error: function (error) {
                            console.error(error);                    
                            alert(error);
                        }
                    });            
                },
                "Delete": function() {
                    if (window.confirm("Are you sure you want to delete this doc action?")) {
                        jQuery.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_doc_action_dialog_data',
                                '_doc_id': doc_id,
                                '_action_id': $("#action-id").val(),
                            },
                            success: function (response) {
                                $("#doc-action-dialog").dialog('close');
                                $("#doc-action-list").html(response.html_contain);
                                activate_doc_action_list_data(doc_id);
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
    }

    // doc-report
    activate_doc_report_list_data($("#doc-id").val());
    function activate_doc_report_list_data(doc_id){
        $("#doc-field-setting-button").on("click", function () {
            $("#doc-field-setting-dialog").dialog('open');
        });
    
        $("#doc-field-setting-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
        });
    
        $("#search-doc-report").on( "change", function() {
            get_doc_report_list_data(doc_id, $(this).val())
            $(this).val('');
        });

        $("#new-doc-report").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_doc_report_dialog_data',
                    '_doc_id': doc_id,
                },
                success: function (response) {
                    get_doc_report_list_data(doc_id)
                },
                error: function(error){
                    console.error(error);
                }
            });
        });

        $('[id^="edit-doc-report-"]').on("click", function () {
            const report_id = this.id.substring(16);
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Add or update the `_device_id` parameter
            urlParams.set("_report_id", report_id);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
        });            

        $("#export-to-excel").on("click", function () {
            let table = document.getElementsByTagName('table');
            TableToExcel.convert(table[0], {
                name: 'UserManagement.xlsx',
                sheet: {
                    name: 'Usermanagement'
                }
            });
        });

        $("#exit-doc-report-list").on("click", function () {
            // Get the current URL
            var currentUrl = window.location.href;
            // Create a URL object
            var url = new URL(currentUrl);
            // Remove the specified parameter
            url.searchParams.delete('_doc_id');
            url.searchParams.delete('_report_id');
            // Get the modified URL
            var modifiedUrl = url.toString();
            // Reload the page with the modified URL
            window.location.replace(modifiedUrl);
        });
    }

    function get_doc_report_list_data(doc_id=false, search_doc_report=false) {
        const ajaxData = {
            'action': 'get_doc_report_list_data',
        };

        if (doc_id) ajaxData['_doc_id'] = doc_id;
        if (search_doc_report) ajaxData['_search_doc_report'] = search_doc_report;

        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: 'json',
            data: ajaxData,
            success: function (response) {
                $('#result-container').html(response.html_contain);
                activate_doc_report_list_data(doc_id);
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    function get_doc_report_dialog_data(report_id, callback) {
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_doc_report_dialog_data',
                _report_id: report_id,
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

    activate_doc_report_dialog_data()
    function activate_doc_report_dialog_data(){
        const canvas = document.getElementById('signature-pad');
        if (canvas) {
            canvas.width = window.innerWidth-10;

            const context = canvas.getContext('2d');
            let isDrawing = false;
    
            // Set up drawing styles
            context.strokeStyle = "#000000";
            context.lineWidth = 2;
    
            // Mouse Events for drawing
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

        $('[id^="doc-report-dialog-button-"]').on("click", function () {
            const action_id = this.id.substring(25);
            const ajaxData = {
                'action': 'set_doc_report_dialog_data',
            };
            ajaxData['_report_id'] = $("#report-id").val();
            ajaxData['_action_id'] = action_id;
            ajaxData['_proceed_to_todo'] = 1;

            get_doc_report_dialog_data($("#report-id").val(), function (error, response) {
                $.each(response.doc_fields, function(index, value) {
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
                    success: function(response) {
                        get_doc_report_list_data($("#doc-id").val());
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            })
        });

        $('[id^="save-doc-report-"]').on("click", function () {
            const report_id = this.id.substring(16);
            const ajaxData = {
                'action': 'set_doc_report_dialog_data',
            };
            ajaxData['_report_id'] = report_id;

            get_doc_report_dialog_data($("#report-id").val(), function (error, response) {
                $.each(response.doc_fields, function(index, value) {
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
                    success: function(response) {
                        get_doc_report_list_data($("#doc-id").val());
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });    
            })
        });

        $('[id^="del-doc-report-"]').on("click", function () {
            const report_id = this.id.substring(15);
            if (window.confirm("Are you sure you want to delete this record?")) {
                const ajaxData = {
                    'action': 'del_doc_report_dialog_data',
                };                        
                ajaxData['_report_id'] = report_id;
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: ajaxData,
                    success: function (response) {
                        get_doc_report_list_data($("#doc-id").val());
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            }
        });

        $('[id^="duplicate-doc-report-"]').on("click", function () {
            const report_id = this.id.substring(21);
            const ajaxData = {
                'action': 'duplicate_doc_report_data',
            };
            ajaxData['_report_id'] = report_id;

            get_doc_report_dialog_data($("#report-id").val(), function (error, response) {
                $.each(response.doc_fields, function (index, value) {
                    const field_id_tag = '#' + value.field_id;
                    if (value.field_type === 'checkbox' || value.field_type === 'radio') {
                        ajaxData[value.field_id] = $(field_id_tag).is(":checked") ? 1 : 0;
                    } else {
                        ajaxData[value.field_id] = $(field_id_tag).val();
                        if (value.field_type === '_embedded' || value.field_type === '_planning' || value.field_type === '_select') {
                            $.each(response.sub_item_fields, function(index, inner_value) {
                                const embedded_field = value.field_id + inner_value.sub_item_id;
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
                        get_doc_report_list_data($("#doc-id").val());
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });    
            })
        });

        $('[id^="reset-doc-report-"]').on("click", function () {
            const report_id = this.id.substring(17);
            if (window.confirm("Are you sure you want to reset this report?")) {
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'reset_doc_report_todo_status',
                        '_report_id': report_id,
                    },
                    success: function (response) {
                        get_doc_report_list_data($("#doc-id").val());
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            }    
        });

        $("#action-log-button").on("click", function () {
            $("#report-action-log-div").toggle()
        });

        $("#exit-doc-report-dialog").on("click", function () {
            get_doc_report_list_data($("#doc-id").val());
        });

        $(".video-button").on("click", function () {
            $(".video-display").toggle()
            $(".video-url").toggle()
            $(".video-display").empty().append($(".video-url").val())
        });

        $(".image-button").on("click", function () {
            $(".image-display").toggle()
            $(".image-url").toggle()
            $(".image-display").attr("src", $(".image-url").val());
        });

        // sub-line
        $("#new-sub-line").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_sub_line_dialog_data',
                    '_report_id': $("#report-id").val(),
                    '_embedded_id': $("#embedded-id").val(),
                },
                success: function (set_response) {
                    $("#sub-line-list").html(set_response.html_contain);
                    //activate_doc_report_dialog_data(response);
                },
                error: function(error){
                    console.error(error);
                }
            });
        });

        $('[id^="edit-sub-line-"]').on( "click", function() {
            const sub_line_id = this.id.substring(14);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_sub_line_dialog_data',
                    '_sub_line_id': sub_line_id,
                    '_embedded_id': $("#embedded-id").val(),
                },
                success: function (get_response) {
                    $("#sub-line-dialog").html(get_response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#sub-line-dialog").dialog("option", "buttons", {
                            "Save": function() {
                                const ajaxData = {
                                    'action': 'set_sub_line_dialog_data',
                                };
                                ajaxData['_sub_line_id'] = sub_line_id;
                                ajaxData['_report_id'] = $("#report-id").val();
                                ajaxData['_embedded_id'] = $("#embedded-id").val();
                                field_id = $("#embedded-id").val();
                                $.each(get_response.sub_line_fields, function(index, inner_value) {
                                    const sub_line_field = field_id + inner_value.sub_item_id;
                                    const sub_line_field_tag = '#' + field_id + inner_value.sub_item_id;
                                    if (inner_value.sub_item_type === 'checkbox' || inner_value.sub_item_type === 'radio') {
                                        ajaxData[sub_line_field] = $(sub_line_field_tag).is(":checked") ? 1 : 0;
                                    } else {
                                        ajaxData[sub_line_field] = $(sub_line_field_tag).val();
                                    }
                                });
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: ajaxData,
                                    success: function(set_response) {
                                        $("#sub-line-dialog").dialog('close');
                                        $('#sub-line-list').html(set_response.html_contain);
                                        //activate_doc_report_dialog_data(response);
                                    },
                                    error: function(error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });                    
                            },
                            "Delete": function() {
                                if (window.confirm("Are you sure you want to delete this sub-line?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_sub_line_dialog_data',
                                            '_sub_line_id': sub_line_id,
                                            '_report_id': $("#report-id").val(),
                                            '_embedded_id': $("#embedded-id").val(),
                                        },
                                        success: function (del_response) {
                                            $("#sub-line-dialog").dialog('close');
                                            $('#sub-line-list').html(del_response.html_contain);
                                            //activate_doc_report_dialog_data(response);
                                        },
                                        error: function(error){
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            }
                        });
                    }
                    $("#sub-line-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);                
                    alert(error);
                }
            });
        });
    
        $("#sub-line-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });
    }    
});

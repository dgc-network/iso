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
    $("#ask-gemini").on("change", function () {
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Remove or Add the parameters
        urlParams.set("_prompt", $(this).val());
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    })

    $("#save-draft").on("click", function () {
        console.log($("#draft-title").val());
        console.log($("#draft-content").val());
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_iso_start_ai_data',
                _draft_title : $("#draft-title").val(),
                _draft_category : $("#draft-category").val(),
                _draft_content : $("#draft-content").val(),
            },
            success: function (response) {
                console.log(response)

                // Show the custom alert message
                var alertBox = $("<div class='custom-alert'>Generate "+$("#draft-title").val()+" success!</div>");
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
            },
            error: function(error){
                console.error(error); 
                alert(error);
            }
        });
    })

    $("#exit-statement").on("click", function () {
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Remove or Add the parameters
        urlParams.delete("_start_ai");
        urlParams.delete("_prompt");
        urlParams.delete("_paged");
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    })

    $("#statement-page1-next-step").on("click", function () {
        // Initialize an empty array to store the key-value pairs
        const keyValuePairs = [];
        // Select all elements with the specified class and iterate over them
        $('.embedded-item-class').each(function() {
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
                'action': 'set_iso_start_ai_data',
                _keyValuePairs : keyValuePairs,
            },
            success: function (response) {
                console.log(response)
                iso_category_id = $("#iso-category-id").val();
                // Get existing URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                // Remove or Add the parameters
                urlParams.set("_paged", 2);
                urlParams.set("_start_ai", iso_category_id);
                urlParams.delete("_prompt");
                // Redirect to the updated URL
                window.location.href = "?" + urlParams.toString();
            },
            error: function(error){
                console.error(error); 
                alert(error);
            }
        });
    })

    $("#statement-page2-prev-step").on("click", function () {
        iso_category_id = $("#iso-category-id").val();
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Remove or Add the parameters
        urlParams.set("_paged", 1);
        urlParams.set("_start_ai", iso_category_id);
        urlParams.delete("_prompt");
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    })

    $("#proceed-to-copy").on("click", function () {
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
                    'action': 'set_iso_start_ai_data',
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
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or Add the parameters
            urlParams.set("_paged", 1);
            urlParams.set("_start_ai", iso_category_id);
            urlParams.delete("_prompt");
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
        }
    })

    // document
    $("#select-category").on( "change", function() {
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        // Remove or Update the parameters
        urlParams.delete("_search");
        urlParams.set("_category", $(this).val());
        urlParams.set("paged", 1);
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    });

    $("#search-document").on( "change", function() {
        // Get existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        var selectValue = $("#select-category").val();
        // Remove or update the parameters
        if (selectValue) urlParams.set("_category", selectValue);
        urlParams.set("_search", $(this).val());
        urlParams.set("paged", 1);
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
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
                // Get existing URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                // Remove or update the parameters
                urlParams.set("paged", 1);
                // Redirect to the updated URL
                window.location.href = "?" + urlParams.toString();
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
        // Remove or update the parameters
        urlParams.set("_doc_id", doc_id);
        // Redirect to the updated URL
        window.location.href = "?" + urlParams.toString();
    });            

    activate_document_dialog_data();
    function activate_document_dialog_data(){
        doc_id = $('#doc-id').val();
        //activate_doc_action_list_data(doc_id);
        //activate_doc_user_list_data(doc_id);
        activate_doc_field_list_data(doc_id);

        $("#system-doc-label").on("click", function () {
            $("#system-doc-div").toggle();
        });

        $("#doc-content-label").on("click", function () {
            $("#doc-report-div").toggle();
            $("#doc-content-div").toggle();
            $("#is-doc-report").val(1)
        });

        $("#doc-field-label").on("click", function () {
            $("#doc-report-div").toggle();
            $("#doc-content-div").toggle();
            $("#is-doc-report").val(0)
        });

        $("#doc-content-preview").on("click", function () {
            get_doc_content_data(doc_id);
        });

        $("#doc-report-preview").on("click", function () {
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or update the parameters
            urlParams.set("_is_doc_report", 1);
            urlParams.set("paged", 1);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
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
            ajaxData['_job_content'] = $("#job-content").val();
            ajaxData['_department_id'] = $("#department-id").val();
            ajaxData['_doc_number'] = $("#doc-number").val();
            ajaxData['_doc_title'] = $("#doc-title").val();
            ajaxData['_doc_revision'] = $("#doc-revision").val();
            ajaxData['_doc_category'] = $("#doc-category").val();
            ajaxData['_doc_content'] = $("#doc-content").val();
            ajaxData['_is_doc_report'] = $("#is-doc-report").val();
            ajaxData['_api_endpoint'] = $("#api-endpoint").val();
            ajaxData['_is_embedded_doc'] = $("#is-embedded-doc").is(":checked") ? 1 : 0;
            ajaxData['_is_public'] = $("#is-public").is(":checked") ? 1 : 0;
            ajaxData['_not_start_job'] = $("#not-start-job").is(":checked") ? 1 : 0;
            ajaxData['_is_summary_report'] = $("#is-summary-report").is(":checked") ? 1 : 0;

            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: ajaxData,
                success: function (response) {
                    // Get existing URL parameters
                    const urlParams = new URLSearchParams(window.location.search);
                    // Remove or update the parameters
                    urlParams.delete("_doc_id");
                    urlParams.delete("_prompt");
                    // Redirect to the updated URL
                    window.location.href = "?" + urlParams.toString();
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
                        // Get existing URL parameters
                        const urlParams = new URLSearchParams(window.location.search);
                        // Remove or update the parameters
                        urlParams.delete("_doc_id");
                        urlParams.delete("_prompt");
                        // Redirect to the updated URL
                        window.location.href = "?" + urlParams.toString();
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            }
        });

        $("#exit-document-dialog").on("click", function () {
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or Update the parameters
            urlParams.delete("_doc_id");
            urlParams.delete("_prompt");
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
        });
    }

    function get_doc_content_data(doc_id){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_doc_content_data',
                '_doc_id': doc_id,
            },
            success: function (response) {
                $('#result-container').html(response.html_contain);
                activate_doc_content_data(doc_id)
            },
            error: function(error){
                console.error(error);
                alert(error);
            }
        });

    }

    activate_doc_content_data($("#doc-id").val());
    function activate_doc_content_data(doc_id){
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

        $("#exit-doc-content").on("click", function () {
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or Update the parameters
            urlParams.delete("_doc_id");
            //urlParams.delete("_prompt");
            urlParams.set("paged", 1);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
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
                                        '_embedded_doc': $("#embedded-doc").val(),
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

                    if ($("#field-type").val() === '_select' || $("#field-type").val() === '_embedded' || $("#field-type").val() === '_line_list') {
                        $('#embedded-selection').show();
                    }
        
                    $("#field-type").on("change", function() {
                        if ($(this).val() === '_select' || $(this).val() === '_embedded' || $(this).val() === '_line_list') {
                            $('#embedded-selection').show();
                        } else {
                            $('#embedded-selection').hide();
                        }
                        if ($(this).val() === 'heading' || $(this).val() === 'video' || $(this).val() === 'image' || $(this).val() === 'canvas' || $(this).val() === '_embedded' || $(this).val() === '_line_list') {
                            $('#listing-style').val('.');
                        }
                        if ($(this).val() === 'textarea') {
                            $('#listing-style').val('left');
                        }
                    });
                
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
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or Update the parameters
            urlParams.set("_search", $(this).val());
            urlParams.set("paged", 1);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
        });

        $('[id^="edit-doc-report-"]').on("click", function () {
            const report_id = this.id.substring(16);
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or update the parameters
            urlParams.set("_report_id", report_id);
            urlParams.set("paged", 1);
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
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or Update the parameters
            urlParams.delete("_doc_id");
            urlParams.delete("_report_id");
            urlParams.delete("_is_doc_report");
            urlParams.set("paged", 1);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
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
        $('[id^="doc-report-dialog-button-"]').on("click", function () {
            const action_id = this.id.substring(25);
            const ajaxData = {
                'action': 'set_doc_report_dialog_data',
            };
            ajaxData['_report_id'] = $("#report-id").val();
            ajaxData['_action_id'] = action_id;
            ajaxData['_proceed_to_todo'] = 1;

            get_doc_report_dialog_data($("#report-id").val(), function (error, response) {
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
                                const field_embedded = inner_value.embedded_item_id;
                                const field_embedded_tag = '#' + inner_value.embedded_item_id;
                                if (inner_value.field_type === 'checkbox' || inner_value.field_type === 'radio') {
                                    ajaxData[field_embedded] = $(field_embedded_tag).is(":checked") ? 1 : 0;
                                } else {
                                    ajaxData[field_embedded] = $(field_embedded_tag).val();
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
                        // Get existing URL parameters
                        const urlParams = new URLSearchParams(window.location.search);
                        // Remove or Update the parameters
                        urlParams.delete("_report_id");
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
/*
        $('[id^="save-doc-report-"]').on("click", function () {
            const report_id = this.id.substring(16);
            const ajaxData = {
                'action': 'set_doc_report_dialog_data',
            };
            ajaxData['_report_id'] = report_id;

            get_doc_report_dialog_data($("#report-id").val(), function (error, response) {
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
                                const field_embedded = inner_value.embedded_item_id;
                                const field_embedded_tag = '#' + inner_value.embedded_item_id;
                                if (inner_value.field_type === 'checkbox' || inner_value.field_type === 'radio') {
                                    ajaxData[field_embedded] = $(field_embedded_tag).is(":checked") ? 1 : 0;
                                } else {
                                    ajaxData[field_embedded] = $(field_embedded_tag).val();
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
                        // Get existing URL parameters
                        const urlParams = new URLSearchParams(window.location.search);
                        // Remove or Update the parameters
                        urlParams.delete("_report_id");
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
*/
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
                        // Get existing URL parameters
                        const urlParams = new URLSearchParams(window.location.search);
                        // Remove or Update the parameters
                        urlParams.delete("_report_id");
                        // Redirect to the updated URL
                        window.location.href = "?" + urlParams.toString();

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
                $.each(response.doc_field_keys, function (index, value) {
                    const field_id_tag = '#' + value.field_id;
                    if (value.field_type === 'checkbox' || value.field_type === 'radio') {
                        ajaxData[value.field_id] = $(field_id_tag).is(":checked") ? 1 : 0;
                    } else {
                        ajaxData[value.field_id] = $(field_id_tag).val();
                        if (value.field_type === '_embedded') {
                            $.each(response.embedded_item_keys, function(index, inner_value) {
                                const field_embedded = inner_value.embedded_item_id;
                                const field_embedded_tag = '#' + inner_value.embedded_item_id;
                                if (inner_value.field_type === 'checkbox' || inner_value.field_type === 'radio') {
                                    ajaxData[field_embedded] = $(field_embedded_tag).is(":checked") ? 1 : 0;
                                } else {
                                    ajaxData[field_embedded] = $(field_embedded_tag).val();
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
                        urlParams.delete("_report_id");
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
                        // Get existing URL parameters
                        const urlParams = new URLSearchParams(window.location.search);
                        // Remove or Update the parameters
                        urlParams.delete("_report_id");
                        // Redirect to the updated URL
                        window.location.href = "?" + urlParams.toString();
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
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or Update the parameters
            urlParams.delete("_report_id");
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
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
    }    
});

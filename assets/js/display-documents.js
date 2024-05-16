// display documents
jQuery(document).ready(function($) {
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
    
    $("#site-title").on("change", function () {
        new_site_title = $(this).val();
        if (window.confirm("Are you sure you want to use "+new_site_title+" as your new site title?")) {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_new_site_by_title',
                    '_new_site_title': new_site_title,
                },
                success: function (response) {
                    $("#site-id").val(response.new_site_id);
                },
                error: function(error){
                    console.error(error);                    
                }
            });        
        }
    });
    
    $("#initial-next-step").on("click", function () {
        doc_category = $("#doc-category").val();
        count_category = $("#count-category").val();
        if (window.confirm("Are you sure you want to add "+count_category+" "+ doc_category+" new documents?")) {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_initial_iso_document',
                    '_doc_category_id': $("#doc-category-id").val(),
                    '_doc_site_id': $("#doc-site-id").val(),
                },
                success: function (response) {
                    console.log(response)
                    window.location.replace(window.location.href);
                },
                error: function(error){
                    console.error(error);                    
                    //alert(error);
                }
            });    
    
        }
    });

    $("#select-category").on( "change", function() {
        window.location.replace("?_category="+$(this).val());
        $(this).val('');
    });

    $("#search-document").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
        $(this).val('');
    });

    $("#document-setting").on("click", function () {
        $("#document-setting-dialog").dialog('open');
    });

    $("#document-setting-dialog").dialog({
        width: 450,
        modal: true,
        autoOpen: false,
    });

    activate_doc_report_list_data($("#doc-id").val());

    $('[id^="edit-document-"]').on("click", function () {
        const doc_id = this.id.substring(14);
        get_document_dialog_data(doc_id)
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
                window.location.replace(window.location.href);
            },
            error: function(error){
                console.error(error);                    
                //alert(error);
            }
        });    
    });

    function get_document_dialog_data(doc_id){
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_document_dialog_data',
                _doc_id: doc_id,
                _is_admin: $("#is-admin").val()
            },
            success: function (response) {
                if (response.html_contain === undefined || response.html_contain === null) {
                    alert("The document is in To-do process. Please wait for publishing.");
                } else {
                    $('#result-container').html(response.html_contain);
                    //$('#result-container').replaceWith(response.html_contain);
                    $('#is-doc-report').val(response.is_doc_report);
                    $('#doc-report-frequence-setting').val(response.doc_report_frequence_setting);
                    content = 'sequenceDiagram participant Alice participant Bob Alice->>John: Hello John, how are you? loop Healthcheck John->>John: Fight against hypochondria end Note right of John: Rational thoughts <br/>prevail! John-->>Alice: Great! John->>Bob: How about you? Bob-->>John: Jolly good!';
                    $('.mermaid').append(content);
                }
                $("#doc-id").val(doc_id);

                $(".datepicker").datepicker({
                    onSelect: function(dateText, inst) {
                        $(this).val(dateText);
                    }
                });
            
                $('[id^="reset-document-"]').on("click", function () {
                    const doc_id = this.id.substring(15);
                    if (window.confirm("Are you sure you want to reset this document status?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'reset_document_todo_status',
                                '_doc_id': doc_id,
                            },
                            success: function (response) {
                                window.location.replace(window.location.href);
                            },
                            error: function(error){
                                console.error(error);
                                alert(error);
                            }
                        });
                    }
                });            
                        
                if ($('#is-doc-report').val()==1) {
                    $("#doc-report-div").show();
                    $("#doc-report-div1").show();
                } else {
                    $("#doc-frame-div").show();
                }

                $("#doc-frame-label").on("click", function () {
                    $("#doc-report-div").toggle();
                    $("#doc-report-div1").toggle();
                    $("#doc-frame-div").toggle();
                    const is_doc_report = $("#is-doc-report").val() == 1 ? 0 : 1;
                    $("#is-doc-report").val(is_doc_report)
                });
        
                $("#doc-field-label").on("click", function () {
                    $("#doc-report-div").toggle();
                    $("#doc-report-div1").toggle();
                    $("#doc-frame-div").toggle();
                    const is_doc_report = $("#is-doc-report").val() == 1 ? 0 : 1;
                    $("#is-doc-report").val(is_doc_report)
                });

                $("#doc-frame-job-setting").on("click", function () {
                    $("#job-setting-div").toggle();
                });

                $("#doc-report-job-setting").on("click", function () {
                    $("#job-setting-div").toggle();
                });

                if ($("#doc-report-frequence-setting").val()) {
                    $("#frquence-start-time-div").show();
                }

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
                            get_doc_action_list_data(doc_id);
                        },
                        error: function(error){
                            console.error(error);
                            alert(error);
                        }
                    });    
                });
                activate_doc_action_list_data(doc_id);

                $("#doc-report-frequence-setting").on("change", function () {
                    if ($(this).val()) {
                        $("#frquence-start-time-div").show();
                    } else {
                        $("#frquence-start-time-div").hide();
                    }
                });

                $("#save-document-button").on("click", function() {
                    const ajaxData = {
                        'action': 'set_document_dialog_data',
                    };
                    ajaxData['_doc_id'] = doc_id;
                    ajaxData['_job_number'] = $("#job-number").val();
                    ajaxData['_job_title'] = $("#job-title").val();
                    ajaxData['_job_content'] = $("#job-content").val();
                    ajaxData['_department'] = $("#department").val();
                    ajaxData['_doc_number'] = $("#doc-number").val();
                    ajaxData['_doc_title'] = $("#doc-title").val();
                    ajaxData['_doc_revision'] = $("#doc-revision").val();
                    ajaxData['_doc_category'] = $("#doc-category").val();
                    ajaxData['_doc_frame'] = $("#doc-frame").val();
                    ajaxData['_is_doc_report'] = $("#is-doc-report").val();
                    ajaxData['_doc_report_frequence_setting'] = $("#doc-report-frequence-setting").val();
                    ajaxData['_doc_report_frequence_start_date'] = $("#doc-report-frequence-start-date").val();
                    ajaxData['_doc_report_frequence_start_time'] = $("#doc-report-frequence-start-time").val();
                    ajaxData['_prev_start_time'] = $("#prev-start-time").val();
                            
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
                                window.location.replace(window.location.href);
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
                            activate_published_document_data(doc_id);
                        },
                        error: function(xhr, textStatus, errorThrown) {
                            console.error('AJAX request failed:', errorThrown);
                            alert('AJAX request failed. Please try again.');
                        }
                    });
                });

                // doc-field scripts
                activate_doc_field_list_data(doc_id);

                // doc-report scripts
                activate_doc_report_list_data(doc_id);

                activate_doc_report_dialog_data(response)

            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    function activate_published_document_data(doc_id){
        $("#share-document").on("click", function() {
            var homeAddress = window.location.origin;
            var textToCopy = homeAddress + "/display-documents/?_get_shared_doc_id=" + doc_id;
        
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

        $("#signature-record").on("click", function () {
            $("#signature-record-div").toggle()
        });

        $("#doc-unpublished").on("click", function () {
            if (window.confirm("Are you sure you want to unpublish this document?")) {
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'reset_document_todo_status',
                        '_doc_id': doc_id,
                    },
                    success: function (response) {
                        window.location.replace(window.location.href);
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            }    
        });

        $("#exit-button").on("click", function () {
            window.location.replace(window.location.href);
        });        
    }

    // doc-field scripts
    function get_doc_field_list_data(doc_id=false, site_id=false) {
        const ajaxData = {
            'action': 'get_doc_field_list_data',
        };
    
        if (doc_id) ajaxData['_doc_id'] = doc_id;
        if (site_id) ajaxData['_site_id'] = site_id;
    
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: 'json',
            data: ajaxData,
            success: function (response) {
                $('#fields-container').html(response.html_contain);
                activate_doc_field_list_data(doc_id, site_id);
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }
    
    function activate_doc_field_list_data(doc_id=false, site_id=false){
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
                    get_doc_field_list_data(doc_id);
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
                    $("#doc-field-dialog").dialog('open');
                    $("#field-id").val(field_id);
                    $("#field-name").val(response.field_name);
                    $("#field-title").val(response.field_title);
                    $("#field-type").val(response.field_type).change();
                    $("#default-value").val(response.default_value);
                    $("#listing-style").val(response.listing_style).change();
                    $("#order-field").val(response.order_field).change();
                },
                error: function (error) {
                    console.error(error);                
                    alert(error);
                }
            });
        });
    
        $("#doc-field-dialog").dialog({
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
                            'action': 'set_doc_field_dialog_data',
                            '_field_id': $("#field-id").val(),
                            '_field_name': $("#field-name").val(),
                            '_field_title': $("#field-title").val(),
                            '_field_type': $("#field-type").val(),
                            '_default_value': $("#default-value").val(),
                            '_listing_style': $("#listing-style").val(),
                            '_order_field': $("#order-field").val(),
                        },
                        success: function (response) {
                            $("#doc-field-dialog").dialog('close');
                            if (site_id) get_doc_field_list_data(false, site_id);
                            if (doc_id) get_doc_field_list_data(doc_id);
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
                                '_field_id': $("#field-id").val(),
                            },
                            success: function (response) {
                                $("#doc-field-dialog").dialog('close');
                                if (site_id) get_doc_field_list_data(false, site_id);
                                if (doc_id) get_doc_field_list_data(doc_id);
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

    // doc-action scripts
    function activate_doc_action_list_data(doc_id) {
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
            width: 450,
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
                            '_action_id': $("#action-id").val(),
                            '_action_title': $("#action-title").val(),
                            '_action_content': $("#action-content").val(),
                            '_next_job': $("#next-job").val(),
                            '_next_leadtime': $("#next-leadtime").val(),
                        },
                        success: function (response) {
                            $("#doc-action-dialog").dialog('close');
                            get_doc_action_list_data(doc_id);
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
                                '_action_id': $("#action-id").val(),
                            },
                            success: function (response) {
                                $("#doc-action-dialog").dialog('close');
                                get_doc_action_list_data(doc_id);
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

    function get_doc_action_list_data(doc_id) {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_doc_action_list_data',
                '_doc_id': doc_id,
            },
            success: function (response) {
                $("#doc-action-list").html(response.html_contain);
                activate_doc_action_list_data(doc_id);
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    // doc-report scripts
    function activate_doc_report_list_data(doc_id){
        $("#doc-report-setting").on("click", function () {
            $("#doc-report-setting-dialog").dialog('open');
        });
    
        $("#doc-report-setting-dialog").dialog({
            width: 450,
            modal: true,
            autoOpen: false,
        });
    
        activate_published_document_data(doc_id);

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
            get_doc_report_dialog_data(report_id)
        });            

        $("#search-doc-report").on( "change", function() {
            get_doc_report_list_data(doc_id, $(this).val())
            $(this).val('');
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
    
    function activate_doc_report_dialog_data(response){
        $(".datepicker").datepicker({
            onSelect: function(dateText, inst) {
                $(this).val(dateText);
            }
        });
    
        $('[id^="doc-report-dialog-button-"]').on("click", function () {
            const action_id = this.id.substring(25);
            const ajaxData = {
                'action': 'set_doc_report_dialog_data',
            };
            ajaxData['_action_id'] = action_id;
            ajaxData['_report_id'] = $("#report-id").val();
            ajaxData['_proceed_to_todo'] = $("#proceed-to-todo").is(":checked") ? 1 : 0;
        
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
                success: function(response) {
                    get_doc_report_list_data($("#doc-id").val());
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.error('AJAX request failed:', errorThrown);
                    alert('AJAX request failed. Please try again.');
                }
            });
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
            $.each(response.doc_fields, function (index, value) {
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
                    get_doc_report_list_data($("#doc-id").val());
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#doc-report-unpublished").on("click", function () {
            const report_id = this.id.substring(21);
            if (window.confirm("Are you sure you want to unpublish this doc-report?")) {
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'reset_document_todo_status',
                        '_report_id': report_id,
                    },
                    success: function (response) {
                        window.location.replace(window.location.href);
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            }    

            const ajaxData = {
                'action': 'duplicate_doc_report_data',
            };
            ajaxData['_report_id'] = report_id;
            $.each(response.doc_fields, function (index, value) {
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
                    get_doc_report_list_data($("#doc-id").val());
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#signature-record").on("click", function () {
            $("#report-signature-record-div").toggle()
        });

        $("#doc-report-dialog-exit").on("click", function () {
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

    }
    
    function get_doc_report_dialog_data(report_id){
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_doc_report_dialog_data',
                _report_id: report_id,
            },
            success: function (response) {
                if (response.html_contain === undefined || response.html_contain === null) {
                    alert("The report is in To-do process. Please wait for publishing.");
                } else {
                    $('#result-container').html(response.html_contain);
                }
                $("#doc-id").val(response.doc_id);
                
                activate_doc_report_dialog_data(response)
            },
            error: function (error) {
                console.error(error);
            }
        });
    }
});

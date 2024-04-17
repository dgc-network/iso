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
                    alert(error);
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
                    //window.location.replace("/display-profiles/?_initial=true");
                    window.location.replace(window.location.href);
                },
                error: function(error){
                    console.error(error);                    
                    alert(error);
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

    activate_doc_field_list_data(false, $("#site-id").val());
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
                //'_site_id': $("#site-id").val(),
            },
            success: function (response) {
                window.location.replace(window.location.href);
            },
            error: function(error){
                console.error(error);                    
                alert(error);
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
                    $('#is-doc-report').val(response.is_doc_report);
                    $('#doc-report-start-setting').val(response.doc_report_start_setting);
                }
                $("#doc-id").val(doc_id);

                //activate_published_document_data(doc_id);

                $(".datepicker").datepicker({
                    onSelect: function(dateText, inst) {
                        $(this).val(dateText);
                    }
                });
            
                //activate_document_dialog_data(doc_id);
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
                } else {
                    $("#doc-frame-div").show();
                }

                $("#doc-frame-label").on("click", function () {
                    $("#doc-report-div").toggle();
                    $("#doc-frame-div").toggle();
                    const is_doc_report = $("#is-doc-report").val() == 1 ? 0 : 1;
                    $("#is-doc-report").val(is_doc_report)
                });
        
                $("#doc-field-label").on("click", function () {
                    $("#doc-report-div").toggle();
                    $("#doc-frame-div").toggle();
                    const is_doc_report = $("#is-doc-report").val() == 1 ? 0 : 1;
                    $("#is-doc-report").val(is_doc_report)
                });
        
                if ($("#doc-report-start-setting").val()>0) {
                    $("#doc-report-start-setting-div").show();
                    if ($("#doc-report-start-setting").val()=="1") {
                        $("#doc-report-period-time-label1").text("每年");
                        $("#doc-report-period-time-label2").text("月");
                        $("#doc-report-period-time-label3").text("1 日");
                        $("#doc-report-period-time").attr("min", 1);
                        $("#doc-report-period-time").attr("max", 12);
                    }
                    if ($("#doc-report-start-setting").val()=="2") {
                        $("#doc-report-period-time-label1").text("每月");
                        $("#doc-report-period-time-label2").text("日");
                        $("#doc-report-period-time-label3").text("");
                        $("#doc-report-period-time").attr("min", 1);
                        $("#doc-report-period-time").attr("max", 30);
                    }
                    if ($("#doc-report-start-setting").val()=="3") {
                        $("#doc-report-period-time-label1").text("每週");
                        $("#doc-report-period-time-label2").text("");
                        $("#doc-report-period-time-label3").text("");
                        $("#doc-report-period-time").attr("min", 1);
                        $("#doc-report-period-time").attr("max", 7);
                    }
                    if ($("#doc-report-start-setting").val()=="4") {
                        $("#doc-report-period-time-label1").text("每日");
                        $("#doc-report-period-time-label2").text("時");
                        $("#doc-report-period-time-label3").text("0 分");
                        $("#doc-report-period-time").attr("min", 1);
                        $("#doc-report-period-time").attr("max", 24);
                    }
                } else {
                    $("#doc-report-start-setting-div").hide();
                }

                $("#doc-report-start-setting").on("change", function() {            
                    if ($(this).val()=="0") {
                        $("#doc-report-start-setting-div").hide();
                    } else {                
                        $("#doc-report-start-setting-div").show();
                        if ($(this).val()=="1") {
                            $("#doc-report-period-time-label1").text("每年");
                            $("#doc-report-period-time-label2").text("月");
                            $("#doc-report-period-time-label3").text("1 日");
                            $("#doc-report-period-time").attr("min", 1);
                            $("#doc-report-period-time").attr("max", 12);
                        }
                        if ($(this).val()=="2") {
                            $("#doc-report-period-time-label1").text("每月");
                            $("#doc-report-period-time-label2").text("日");
                            $("#doc-report-period-time-label3").text("");
                            $("#doc-report-period-time").attr("min", 1);
                            $("#doc-report-period-time").attr("max", 30);
                        }
                        if ($(this).val()=="3") {
                            $("#doc-report-period-time-label1").text("每週");
                            $("#doc-report-period-time-label2").text("");
                            $("#doc-report-period-time-label3").text("");
                            $("#doc-report-period-time").attr("min", 1);
                            $("#doc-report-period-time").attr("max", 7);
                        }
                        if ($(this).val()=="4") {
                            $("#doc-report-period-time-label1").text("每日");
                            $("#doc-report-period-time-label2").text("時");
                            $("#doc-report-period-time-label3").text("0 分");
                            $("#doc-report-period-time").attr("min", 1);
                            $("#doc-report-period-time").attr("max", 24);
                        }
                    }
                });
                
                //activate_doc_action_list_data(doc_id);
                                
                $("#save-document-button").on("click", function() {
                    const ajaxData = {
                        'action': 'set_document_dialog_data',
                    };
                    ajaxData['_doc_id'] = doc_id;
                    ajaxData['_doc_number'] = $("#doc-number").val();
                    ajaxData['_doc_title'] = $("#doc-title").val();
                    ajaxData['_doc_revision'] = $("#doc-revision").val();
                    ajaxData['_doc_category'] = $("#doc-category").val();
                    ajaxData['_doc_frame'] = $("#doc-frame").val();
                    ajaxData['_is_doc_report'] = $("#is-doc-report").val();
                    ajaxData['_doc_report_start_setting'] = $("#doc-report-start-setting").val();
                    ajaxData['_doc_report_period_time'] = $("#doc-report-period-time").val();
                    ajaxData['_doc_report_start_job'] = $("#doc-report-start-job").val();
                    ajaxData['_start_job'] = $("#start-job").val();
                            
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
                console.log(error);
            }
        });
    }

    // doc-field scripts
/*    
    $("#new-doc-field").on("click", function() {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_doc_field_dialog_data',
                '_site_id': $("#site-id").val(),
            },
            success: function (response) {
                get_doc_field_list_data(false, $("#site-id").val());
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });                                        
*/
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
                    dataType: 'json',
                    data: {
                        action: 'set_doc_unpublished_data',
                        _doc_id: doc_id,
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.replace(window.location.href);
                        } else {
                            console.error('Error updating:', response.error);
                            alert('Error updating. Please try again.');
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        console.error('AJAX request failed:', errorThrown);
                        alert('AJAX request failed. Please try again.');
                    }
                });
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
                        action: 'set_sorted_field_id_data',
                        _field_id_array: field_id_array,
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log('Sorting order updated successfully.');
                        } else {
                            console.error('Error updating sorting order:', response.error);
                            alert('Error updating sorting order. Please try again.');
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        console.error('AJAX request failed:', errorThrown);
                        alert('AJAX request failed. Please try again.');
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

        activate_doc_field_list_data(doc_id);
        
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
                    alert(error);
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
                success: function(response) {
                    if (window.confirm("Are you sure you want to proceed the next doc?")) {
                        const ajaxData = {
                            'action': 'set_next_doc_report_data',
                        };
                        ajaxData['_action_id'] = action_id;
                        ajaxData['_report_id'] = $("#report-id").val();
                            
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
        
                    get_doc_report_list_data($("#doc-id").val());
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.error('AJAX request failed:', errorThrown);
                    alert('AJAX request failed. Please try again.');
                }
            });


        });

/*
        $('[id^="save-doc-report-"]').on("click", function() {
            const report_id = this.id.substring(16);
            const ajaxData = {
                'action': 'set_doc_report_dialog_data',
                '_report_id': report_id
            };
        
            $.each(response.doc_fields, function(index, value) {
                const field_name_tag = '#' + value.field_name;
                if (value.field_type === 'checkbox' || value.field_type === 'radio') {
                    ajaxData[value.field_name] = $(field_name_tag).is(":checked") ? 1 : 0;
                } else {
                    ajaxData[value.field_name] = $(field_name_tag).val();
                }
            });
        
            //ajaxData['_doc_report_start_setting'] = $("#doc-report-start-setting").val();
            //ajaxData['_doc_report_period_time'] = $("#doc-report-period-time").val();
            //ajaxData['_start_job'] = $("#start-job").val();
            //ajaxData['_start_leadtime'] = $("#start-leadtime").val();
            //ajaxData['_prev_doc_report'] = $("#prev-doc-report").val();
            //ajaxData['_next_doc_report'] = $("#next-doc-report").val();
        
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
                        get_doc_report_list_data($("#doc-id").val());
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            }
        });

        $("#signature-record").on("click", function () {
            $("#report-signature-record-div").toggle()
        });

        $('[id^="duplicate-doc-report-"]').on("click", function () {
            const report_id = this.id.substring(21);
            const ajaxData = {
                'action': 'duplicate_doc_report_dialog_data',
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
            //ajaxData['_doc_report_start_setting'] = $("#doc-report-start-setting").val();
            //ajaxData['_doc_report_period_time'] = $("#doc-report-period-time").val();
            ajaxData['_start_job'] = $("#start-job").val();
            //ajaxData['_start_leadtime'] = $("#start-leadtime").val();
            //ajaxData['_prev_doc_report'] = $("#prev-doc-report").val();
            //ajaxData['_next_doc_report'] = $("#next-doc-report").val();
                    
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
                console.log(error);
            }
        });
    }

});

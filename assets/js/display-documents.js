// display documents
jQuery(document).ready(function($) {
    $("#select-category").on( "change", function() {
        window.location.replace("?_category="+$(this).val());
        $(this).val('');
    });

    $("#search-document").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
        $(this).val('');
    });

    $("#document-setting").on("click", function () {
        $("#document-setting-div").toggle();
    });

    $('[id^="edit-document-"]').on("click", function () {
        const doc_id = this.id.substring(14);
        get_document_dialog_data(doc_id)
    });            

    $("#new-document-button").on("click", function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_document_dialog_data',
                '_site_id': $("#site-id").val(),
            },
            success: function (response) {
                window.location.replace("/display-documents/");
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
    });

    function error_get_document_dialog_data(doc_id){
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_document_dialog_data',
                _doc_id: doc_id,
            },
            success: function (response) {
                // First AJAX request
                const firstAjaxData = {
                    'action': 'get_document_dialog_data',
                    '_doc_id': doc_id,
                };
        
                handleAjaxRequest(firstAjaxData, function (response) {
                    $('#result-container').html(response);
                });
        
                // Second AJAX request
                const secondAjaxData = {
                    'action': 'get_document_dialog_data',
                    '_doc_id': doc_id,
                };
        
                handleAjaxRequest(secondAjaxData, function (response) {
                    // Handle the response as needed
                });
            },
            error: function (error) {
                console.log(error);
            }
        });        
    }

    function handleAjaxRequest(data, successCallback) {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: 'json',
            data: data,
            success: successCallback,
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }
    

    function activate_document_dialog_data(doc_id){
        $("#save-document-button").on("click", function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_document_dialog_data',
                    '_doc_id': doc_id,
                    '_doc_title': $("#doc_title").val(),
                    '_doc_number': $("#doc_number").val(),
                    '_doc_revision': $("#doc_revision").val(),
                    '_doc_date': $("#doc_date").val(),
                    '_doc_url': $("#doc_url").val(),
                    '_is_doc_report': $("#is-doc-report").val(),
                    '_start_job': $("#start_job").val(),
                    '_start_leadtime': $("#start_leadtime").val(),
                    '_doc_category': $("#doc_category").val(),
                },
                success: function (response) {
                    window.location.replace("/display-documents/");
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#del-document-button").on("click", function(e) {
            e.preventDefault();
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
                        window.location.replace("/display-documents/");
                    },
                    error: function(error){
                        console.error(error);
                        alert(error);
                    }
                });
            }
        });

        $('[id^="doc-report-preview-"]').on("click", function (e) {
            e.preventDefault();
            //const doc_id = this.id.substring(19);
            const ajaxData = {
                'action': 'open_doc_report_data',
                //'action': 'get_document_dialog_data',
            };
        
            if (doc_id) ajaxData['_doc_id'] = doc_id;
            //if (site_id) ajaxData['_site_id'] = site_id;
        
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: 'json',
                data: ajaxData,
                success: function (response) {
                    $('#result-container').empty();
                    //$('#result-container').html(response);
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        

        });
    
        $("#doc-url-preview").on("click", function () {
            window.location.replace($("#doc_url").val());
        });    
    }

    function get_document_dialog_data(doc_id){
        // AJAX request
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_document_dialog_data',
                _doc_id: doc_id,
            },
            success: function (response) {
                // Display the result
                $('#result-container').html(response);
                $("#doc-id").val(doc_id);
                
                activate_document_dialog_data(doc_id);
        
                // doc-field scripts
                var currentValue = $("#doc-field-setting").text();
                $("#doc-field-setting").on("click", function () {
                    $("#doc_url").toggle();
                    $("#doc-field-list-dialog").toggle();
                    const is_doc_report = $("#is-doc-report").val() == 1 ? 0 : 1;
                    $("#is-doc-report").val(is_doc_report)
                    currentValue = (currentValue === '文件地址') ? '欄位設定' : '文件地址';
                    $(this).text(currentValue);
                });            
        
                $("#new-doc-field").on("click", function(e) {
                    e.preventDefault();
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

                activate_doc_field_list_data();

                // doc-report scripts
                $("#new-doc-report").on("click", function(e) {
                    e.preventDefault();
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
            
                activate_doc_report_list_data();

            },
            error: function (error) {
                console.log(error);
            }
        });
    }

    // doc-field scripts
    $("#new-doc-field").on("click", function(e) {
        e.preventDefault();
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

                for (let index = 0; index < 50; index++) {
                    $(`.doc-field-list-${index}`).hide().empty();
                }
    
                $.each(response, function (index, value) {
                    const $docFieldList = $(`.doc-field-list-${index}`);
                    $docFieldList.attr('id', `edit-doc-field-${value.field_id}`);
                    $docFieldList.attr('data-field-id', value.field_id);
    
                    const isListingChecked = value.is_listing == 1 ? 'checked' : '';
                    const isEditingChecked = value.is_editing == 1 ? 'checked' : '';
    
                    const output = `
                        <td style="text-align:center;">${value.field_name}</td>
                        <td style="text-align:center;">${value.field_title}</td>
                        <td style="text-align:center;">${value.editing_type}</td>
                        <td style="text-align:center;">${value.default_value}</td>
                    `;
    
                    $docFieldList.append(output).show();
                });
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }
    
    activate_doc_field_list_data();

    function activate_doc_field_list_data(){
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
                    $("#listing-style").val(response.listing_style);
                    $("#editing-type").val(response.editing_type);
                    $("#default-value").val(response.default_value);
                    $('#is-listing').prop('checked', response.is_listing == 1);
                    $('#is-editing').prop('checked', response.is_editing == 1);
                },
                error: function (error) {
                    console.error(error);                
                    alert(error);
                }
            });
        });
    
        $("#doc-field-dialog").dialog({
            width: 400,
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
                            '_listing_style': $("#listing-style").val(),
                            '_editing_type': $("#editing-type").val(),
                            '_default_value': $("#default-value").val(),
                            '_is_listing': $('#is-listing').is(":checked") ? 1 : 0,
                            '_is_editing': $('#is-editing').is(":checked") ? 1 : 0,
                        },
                        success: function (response) {
                            $("#doc-field-dialog").dialog('close');
                            if ($("#site-id").length === 0 || $("#site-id").val() === '') {
                                get_doc_field_list_data($("#doc-id").val());
                            } else {
                                get_doc_field_list_data(false, $("#site-id").val());
                            }
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
                                if ($("#site-id").length === 0 || $("#site-id").val() === '') {
                                    get_doc_field_list_data($("#doc-id").val());
                                } else {
                                    get_doc_field_list_data(false, $("#site-id").val());
                                }
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
    function activate_doc_report_list_data(){
    }

    function get_doc_report_list_data(doc_id=false, site_id=false) {
        const ajaxData = {
            'action': 'get_doc_report_list_data',
        };
    
        if (doc_id) ajaxData['_doc_id'] = doc_id;
        if (site_id) ajaxData['_site_id'] = site_id;
    
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: 'json',
            data: ajaxData,
            success: function (response) {

                for (let index = 0; index < 50; index++) {
                    $(`.doc-report-list-${index}`).hide().empty();
                }
    
                $.each(response, function (index, value) {
                    const doc_report_list = $(`.doc-report-list-${index}`);
                    doc_report_list.attr('id', `edit-doc-report-${value.report_id}`);
                    doc_report_list.attr('data-report-id', value.report_id);
                    doc_report_list.append(value.report_contain).show();
                });
/*
                $('#result-container').html(response);
                $("#doc-id").val(doc_id);

                $("#new-doc-report").on("click", function(e) {
                    e.preventDefault();
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
                activate_doc_report_list_data();
*/                
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }
    
    function get_doc_report_dialog_data(report_id){
        // AJAX request
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'get_doc_report_dialog_data',
                _report_id: report_id,
            },
            success: function (response) {
                // Display the result
                $('#result-container').html(response);
                //$("#report-id").val(report_id);
                
                //activate_document_data();
                $("#save-doc-report-button").on("click", function(e) {
                    e.preventDefault();
                    const ajaxData = {
                        'action': 'set_doc_report_list_data',
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
                });

                $("#del-doc-report-button").on("click", function(e) {
                    e.preventDefault();
                    if (window.confirm("Are you sure you want to delete this doc report?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_doc_report_dialog_data',
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
            },
            error: function (error) {
                console.log(error);
            }
        });
    }

});

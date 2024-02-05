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

    $("#btn-document-setting").on("click", function () {
        $("#document-setting-div").toggle();
    });

    $('[id^="edit-document-"]').on("click", function () {
        const doc_id = this.id.substring(14);
        open_doc_dialog_and_buttons(doc_id)
    });            

    // Special for the Document List setting
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
                get_doc_field_list_data_in_site($("#site-id").val());
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });    
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

    function open_doc_dialog_and_buttons(doc_id){
        // AJAX request
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'open_doc_dialog_and_buttons',
                _doc_id: doc_id,
            },
            success: function (response) {
                // Display the result
                $('#result-container').html(response);

                $("#set-document-button").on("click", function(e) {
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
                            '_doc_url': $("#doc_url").val(),
                            '_start_job': $("#start_job").val(),
                            '_start_leadtime': $("#start_leadtime").val(),
                            '_doc_date': $("#doc_date").val(),
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

                // doc-field setting
                var currentValue = $("#doc-field-setting").text();
                $("#doc-field-setting").on("click", function () {
                    $("#doc_url").toggle();
                    $("#doc-field-list-dialog").toggle();
                    get_doc_field_list_data(doc_id);
                    // Toggle the value between 'ABC' and 'XYZ'
                    currentValue = (currentValue === '文件地址') ? '欄位設定' : '文件地址';
                    // Update the text content of the element
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
                                    '_field_title': $("#field-title").val(),
                                    '_field_content': $("#field-content").val(),
                                    '_is_listing': $('#is-listing').is(":checked") ? 1 : 0,
                                    '_is_editing': $('#is-editing').is(":checked") ? 1 : 0,
                                },
                                success: function (response) {
                                    $("#doc-field-dialog").dialog('close');
                                    get_doc_field_list_data(doc_id);
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
                                        get_doc_field_list_data(doc_id);
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
            
            },
            error: function (error) {
                console.log(error);
            }
        });
    }

    function get_doc_field_list_data(doc_id){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_doc_field_list_data',
                '_doc_id': doc_id,
            },
            success: function (response) {            
                for(index=0;index<50;index++) {
                    $(".doc-field-list-"+index).hide().empty();
                }
                $.each(response, function (index, value) {
                    $(".doc-field-list-" + index).attr("id", "edit-doc-field-" + value.field_id);
                    const is_listing_checked = value.is_listing == 1 ? 'checked' : '';
                    const is_editing_checked = value.is_editing == 1 ? 'checked' : '';
                    const output = `
                        <td style="text-align:center;">${value.field_title}</td>
                        <td style="text-align:center;">${value.field_content}</td>
                        <td style="text-align: center;"><input type="checkbox" ${is_listing_checked} /></td>
                        <td style="text-align: center;"><input type="checkbox" ${is_editing_checked} /></td>
                    `;
                    $(".doc-field-list-"+index).append(output).show();
                })

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
                            $("#field-title").val(response.field_title);
                            $("#field-content").val(response.field_content);
                            $('#is-listing').prop('checked', response.is_listing == 1);
                            $('#is-editing').prop('checked', response.is_editing == 1);
                        },
                        error: function (error) {
                            console.error(error);                
                            alert(error);
                        }
                    });
                });
            },
            error: function (error) {
                console.error(error);                
                alert(error);
            }
        });
    }

    function get_doc_field_list_data_in_site(site_id){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_doc_field_list_data',
                '_site_id': site_id,
            },
            success: function (response) {            
                for(index=0;index<50;index++) {
                    $(".doc-field-list-"+index).hide().empty();
                }
                $.each(response, function (index, value) {
                    $(".doc-field-list-" + index).attr("id", "edit-doc-field-" + value.field_id);
                    const is_listing_checked = value.is_listing == 1 ? 'checked' : '';
                    const is_editing_checked = value.is_editing == 1 ? 'checked' : '';
                    const output = `
                        <td style="text-align:center;">${value.field_title}</td>
                        <td style="text-align:center;">${value.field_content}</td>
                        <td style="text-align: center;"><input type="checkbox" ${is_listing_checked} /></td>
                        <td style="text-align: center;"><input type="checkbox" ${is_editing_checked} /></td>
                    `;
                    $(".doc-field-list-"+index).append(output).show();
                })

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
                            $("#field-title").val(response.field_title);
                            $("#field-content").val(response.field_content);
                            $('#is-listing').prop('checked', response.is_listing == 1);
                            $('#is-editing').prop('checked', response.is_editing == 1);
                        },
                        error: function (error) {
                            console.error(error);                
                            alert(error);
                        }
                    });
                });
            },
            error: function (error) {
                console.error(error);                
                alert(error);
            }
        });
    }
});

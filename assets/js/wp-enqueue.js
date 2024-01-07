jQuery(document).ready(function($) {

    activate_document_list_data()

    $('#sortable-documents').sortable({
        update: function() {
            const document_array = [];
            $('.document-array').each(function(index) { 
                document_array.push($(this).val());
            });
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_sorted_documents_data',
                    '_document_array': document_array,
                },
                error: function(error){
                    // Log the error object to the console for debugging
                    console.error(error);                    
                    // Display the responseText if available
                    if (error.responseText) {
                        alert('set_sorted_documents_data Error: ' + error.responseText);
                    } else {
                        // Display a generic error message
                        alert('An error occurred. Please check the console for details.');
                    }
                }
            });
        }
    });

    $("#btn-new-document").on("click", function() {
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'set_document_dialog_data',
                '_site_id': $("#site-id").val(),
            },
            success: function (response) {
                get_document_list_data('');
            },
            error: function(error){
                // Log the error object to the console for debugging
                console.error(error);                    
                // Display the responseText if available
                if (error.responseText) {
                    alert('set_document_dialog_data Error: ' + error.responseText);
                } else {
                    // Display a generic error message
                    alert('An error occurred. Please check the console for details.');
                }
            }
        });    
    });

    function activate_document_list_data(){
        $('[id^="btn-"]').mouseover(function() {
            $(this).css('cursor', 'pointer');
            $(this).css('color', 'red');
        });
            
        $('[id^="btn-"]').mouseout(function() {
            $(this).css('cursor', 'default');
            $(this).css('color', 'black');
        });

        $("#btn-document-preview").on( "click", function() {
            //window.location.replace("/learnings/?_view_course=" + $("#course-id").val());
        });
    
        $("#btn-document-setting").on( "click", function() {
            //get_collaboration_list_data($("#course-id").val());
        });
    
        $('[id^="btn-edit-document-"]').on( "click", function() {
            id = this.id;
            id = id.substring(18);
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_document_dialog_data',
                    '_document_id': id,
                },
                success: function (response) {
                    $("#document-dialog").dialog('open');
                    $("#document-id").val(id);
                    $("#document-title").val(response.document_title);
                    $("#document-number").val(response.document_number);
                    $("#document-revision").val(response.document_revision);
                    $("#document-date").val(response.document_date);
                    $("#document-url").val(response.document_url);
                    for(index=0;index<50;index++) {
                        $("#doc-action-list-"+index).hide();
                        $("#doc-action-list-"+index).empty();
                    }
                    $.each(response.action_array, function (index, value) {
                        output = '';
                        output = output+'<td style="text-align:center;"><span id="btn-edit-doc-action-'+value.action_id+'" class="dashicons dashicons-edit"></span></td>';
                        output = output+'<td style="text-align:center;">'+value.action_title+'</td>';
                        output = output+'<td>'+value.action_content+'</td>';
                        output = output+'<td style="text-align:center;">'+value.action_submit_user+'</td>';
                        output = output+'<td style="text-align:center;">'+value.action_submit_time+'</td>';
                        output = output+'<td style="text-align:center;"><span id="btn-del-doc-action-'+value.action_id+'" class="dashicons dashicons-trash"></span></td>';
                        $("#doc-action-list-"+index).append(output);
                        $("#doc-action-list-"+index).show();
                    })    
                },
                error: function (error) {
                    // Log the error object to the console for debugging
                    console.error(error);                
                    // Display the responseText if available
                    if (error.responseText) {
                        alert('get_document_dialog_data Error: ' + error.responseText);
                    } else {
                        // Display a generic error message
                        alert('An error occurred. Please check the console for details.');
                    }
                }
            });
        });
    
        $('[id^="btn-del-document-"]').on( "click", function() {
            id = this.id;
            id = id.substring(17);
            if (window.confirm("Are you sure you want to delete this document?")) {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'del_document_dialog_data',
                        '_document_id': id,
                    },
                    success: function (response) {
                        get_document_list_data('');
                    },
                    error: function(error){
                        // Log the error object to the console for debugging
                        console.error(error);                    
                        // Display the responseText if available
                        if (error.responseText) {
                            alert('del_document_dialog_data Error: ' + error.responseText);
                        } else {
                            // Display a generic error message
                            alert('An error occurred. Please check the console for details.');
                        }
                    }
                });
            }
        });
        
        $('#document-date').datepicker({
            onSelect: function(dateText, inst) {
                $(this).val(dateText);
            }
        });            
    }

    function get_document_list_data(search){
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_document_list_data',
                '_site_id': $("#site-id").val(),
                '_search': search,
            },
            success: function (response) {
                for(index=0;index<50;index++) {
                    $("#document-list-"+index).hide();
                    $("#document-list-"+index).empty();
                }
                $.each(response, function (index, value) {
                    output = '';
                    output = output+'<td style="text-align:center;"><span id="btn-edit-document-'+value.document_id+'" class="dashicons dashicons-edit"></span></td>';
                    output = output+'<td>'+value.document_title+'</td>';
                    output = output+'<td style="text-align: center;">'+value.document_number+'</td>';
                    output = output+'<td style="text-align: center;">'+value.document_revision+'</td>';
                    output = output+'<td style="text-align: center;">'+value.document_date+'</td>';
                    output = output+'<td style="text-align: center;"><span id="btn-del-document-'+value.document_id+'" class="dashicons dashicons-trash"></span></td>';
                    $("#document-list-"+index).append(output);
                    $("#document-list-"+index).show();
                });

                activate_document_list_data();
            },
            error: function(error){
                // Log the error object to the console for debugging
                console.error(error);                    
                // Display the responseText if available
                if (error.responseText) {
                    alert('get_document_list_data Error: ' + error.responseText);
                } else {
                    // Display a generic error message
                    alert('An error occurred. Please check the console for details.');
                }
            }
        });
    }

    $("#document-dialog").dialog({
        width: 900,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'set_document_dialog_data',
                        '_document_id': $("#document-id").val(),
                        '_document_title': $("#document-title").val(),
                        '_document_number': $("#document-number").val(),
                        '_document_revision': $("#document-revision").val(),
                        '_document_date': $("#document-date").val(),
                        '_document_url': $("#document-url").val(),
                    },
                    success: function (response) {
                        $("#document-dialog").dialog('close');
                        get_document_list_data('');
                    },
                    error: function (error) {
                        // Log the error object to the console for debugging
                        console.error(error);                    
                        // Display the responseText if available
                        if (error.responseText) {
                            alert('set_document_dialog_data Error: ' + error.responseText);
                        } else {
                            // Display a generic error message
                            alert('An error occurred. Please check the console for details.');
                        }
                    }
                });            
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });
});

jQuery(document).ready(function($) {

    activate_user_actions_data()

    $("#btn-new-user-site-action").on("click", function() {
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'new_user_site_action_data',
                '_site_id': $("#site-id").val(),
            },
            success: function (response) {
                get_user_site_action_list($("#site-id").val());
            },
            error: function(error){
                alert(error);
            }
        });    
    });

    function activate_user_actions_data(){
        $('[id^="btn-"]').mouseover(function() {
            $(this).css('cursor', 'pointer');
            $(this).css('color', 'red');
        });
            
        $('[id^="btn-"]').mouseout(function() {
            $(this).css('cursor', 'default');
            $(this).css('color', 'black');
        });

        $('[id^="btn-edit-user-action-"]').on( "click", function() {
            id = this.id;
            id = id.substring(16);
            window.location.replace('/wp-admin/post.php?post='+id+'&action=edit');
        });
    
        $('[id^="btn-del-user-action-"]').on( "click", function() {
            id = this.id;
            id = id.substring(15);
            if (window.confirm("Are you sure you want to delete this site action?")) {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'del_site_action_data',
                        '_action_id': id,
                    },
                    success: function (response) {
                        get_user_site_action_list($("#site-id").val());
                    },
                    error: function(error){
                        alert(error);
                    }
                });
            }
        });        
    }

    function get_user_site_action_list(id){
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_user_site_action_list',
                '_site_id': id,
            },
            success: function (response) {
                for(index=0;index<50;index++) {
                    $("#site-action-list-"+index).hide();
                    $("#site-action-list-"+index).empty();
                }
                $.each(response, function (index, value) {
                    output = '';
                    output = output+'<td style="text-align: center;"><input type="checkbox" id="user-action-'+value.user_action_id+'>" /></td>';
                    output = output+'<td>'+value.action_title+'</td>';
                    output = output+'<td>'+value.action_description+'</td>';
                    output = output+'<td style="text-align:center;"><span id="btn-edit-action-'+value.action_id+'" class="dashicons dashicons-edit"></span></td>';
                    output = output+'<td style="text-align: center;"><span id="btn-del-action-'+value.action_id+'" class="dashicons dashicons-trash"></span></td>';
                    $("#site-action-list-"+index).append(output);
                    $("#site-action-list-"+index).show();
                });

                activate_user_actions_data();
            },
            error: function(error){
                alert(error);
            }
        });
    }
});


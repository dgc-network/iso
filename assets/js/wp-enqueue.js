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
                get_document_list_data($("#site-id").val());
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
                    '_doc_id': id,
                    '_site_id': $("#site-id").val(),
                },
                success: function (response) {
                    $("#document-dialog").dialog('open');
                    $("#doc-id").val(id);
                    $("#doc-title").val(response.doc_title);
                    $("#doc-number").val(response.doc_number);
                    $("#doc-revision").val(response.doc_revision);
                    $("#doc-date").val(response.doc_date);
                    $("#doc-url").val(response.doc_url);

                    for(index=0;index<50;index++) {
                        $("#doc-job-list-"+index).hide();
                        $("#doc-job-list-"+index).empty();
                    }
                    $.each(response.job_array, function (index, value) {
                        output = '';
                        output = output+'<td style="text-align:center;"><span id="btn-edit-doc-job-'+value.job_id+'" class="dashicons dashicons-edit"></span></td>';
                        output = output+'<td style="text-align:center;">'+value.job_title+'</td>';
                        output = output+'<td>'+value.job_content+'</td>';
                        output = output+'<td style="text-align:center;">'+value.job_submit_user+'</td>';
                        output = output+'<td style="text-align:center;">'+value.job_submit_time+'</td>';
                        output = output+'<td style="text-align:center;"><span id="btn-del-doc-job-'+value.job_id+'" class="dashicons dashicons-trash"></span></td>';
                        $("#doc-job-list-"+index).append(output);
                        $("#doc-job-list-"+index).show();
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
            if (window.confirm("Are you sure you want to delete this document?"+id)) {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'del_document_dialog_data',
                        '_doc_id': id,
                    },
                    success: function (response) {
                        get_document_list_data($("#site-id").val());
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
        
        $('#doc-date').datepicker({
            onSelect: function(dateText, inst) {
                $(this).val(dateText);
            }
        });            
    }

    function get_document_list_data(site_id){
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_document_list_data',
                '_site_id': site_id,
            },
            success: function (response) {
                for(index=0;index<50;index++) {
                    $("#document-list-"+index).hide();
                    $("#document-list-"+index).empty();
                }
                $.each(response, function (index, value) {
                    output = '';
                    output = output+'<td style="text-align:center;"><span id="btn-edit-document-'+value.doc_id+'" class="dashicons dashicons-edit"></span></td>';
                    output = output+'<td>'+value.doc_title+'</td>';
                    output = output+'<td style="text-align: center;">'+value.doc_number+'</td>';
                    output = output+'<td style="text-align: center;">'+value.doc_revision+'</td>';
                    output = output+'<td style="text-align: center;">'+value.doc_date+'</td>';
                    output = output+'<td style="text-align: center;"><span id="btn-del-document-'+value.doc_id+'" class="dashicons dashicons-trash"></span></td>';
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
                        '_doc_id': $("#doc-id").val(),
                        '_doc_title': $("#doc-title").val(),
                        '_doc_number': $("#doc-number").val(),
                        '_doc_revision': $("#doc-revision").val(),
                        '_doc_date': $("#doc-date").val(),
                        '_doc_url': $("#doc-url").val(),
                        //'_site_id': $("#site-id").val(),
                    },
                    success: function (response) {
                        $("#document-dialog").dialog('close');
                        get_document_list_data($("#site-id").val());
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

    activate_my_job_list_data()

    $("#btn-new-site-job").on("click", function() {
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'new_site_job_data',
                '_site_id': $("#site-id").val(),
            },
            success: function (response) {
                get_my_job_list_data($("#site-id").val());
            },
            error: function(error){
                alert(error);
            }
        });    
    });

    function activate_my_job_list_data(){
        $('[id^="btn-"]').mouseover(function() {
            $(this).css('cursor', 'pointer');
            $(this).css('color', 'red');
        });
            
        $('[id^="btn-"]').mouseout(function() {
            $(this).css('cursor', 'default');
            $(this).css('color', 'black');
        });

        $('[id^="btn-edit-site-job-"]').on( "click", function() {
            id = this.id;
            id = id.substring(18);
            window.location.replace('/wp-admin/post.php?post='+id+'&action=edit');
        });
    
        $('[id^="btn-del-site-job-"]').on( "click", function() {
            id = this.id;
            id = id.substring(17);
            if (window.confirm("Are you sure you want to delete this site job?")) {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'del_site_job_data',
                        '_job_id': id,
                    },
                    success: function (response) {
                        get_my_job_list_data($("#site-id").val());
                    },
                    error: function(error){
                        alert(error);
                    }
                });
            }
        });        
    }

    function get_my_job_list_data(id){
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_my_job_list_data',
                '_site_id': id,
            },
            success: function (response) {
                for(index=0;index<50;index++) {
                    $("#my-job-list-"+index).hide();
                    $("#my-job-list-"+index).empty();
                }
                $.each(response, function (index, value) {
                    output = '';
                    output = output+'<td style="text-align: center;"><input type="checkbox" id="check-my-job-'+value.job_id+'>" /></td>';
                    output = output+'<td>'+value.job_title+'</td>';
                    output = output+'<td>'+value.job_description+'</td>';
                    output = output+'<td style="text-align:center;"><span id="btn-edit-site-job-'+value.job_id+'" class="dashicons dashicons-edit"></span></td>';
                    output = output+'<td style="text-align: center;"><span id="btn-del-site-job-'+value.job_id+'" class="dashicons dashicons-trash"></span></td>';
                    $("#my-job-list-"+index).append(output);
                    $("#my-job-list-"+index).show();
                });

                activate_my_job_list_data();
            },
            error: function(error){
                alert(error);
            }
        });
    }
});


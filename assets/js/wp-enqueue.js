/*
jQuery(document).ready(function($) {

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
                //get_document_list_data($("#site-id").val());
                alert('Success!');
            },
            error: function(error){
                console.error(error);
                alert(error);

            }
        });    
    });


})
*/
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
                    console.error(error);                    
                    alert(error);
                }
            });
        }
    });

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
                    output = output+'<td style="text-align:center;"><span id="btn-edit-document-'+value.document_id+'" class="dashicons dashicons-edit"></span></td>';
                    output = output+'<td>'+value.doc_title+'</td>';
                    output = output+'<td style="text-align: center;">'+value.doc_number+'</td>';
                    output = output+'<td style="text-align: center;">'+value.doc_revision+'</td>';
                    output = output+'<td style="text-align: center;">'+value.doc_date+'</td>';
                    output = output+'<td style="text-align: center;"><span id="btn-del-document-'+value.document_id+'" class="dashicons dashicons-trash"></span></td>';
                    $("#document-list-"+index).append(output);
                    $("#document-list-"+index).show();
                });

                activate_document_list_data();
            },
            error: function(error){
                console.error(error);                    
                alert(error);
            }
        });
    }

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
                console.error(error);                    
                alert(error);
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
                    '_site_id': $("#site-id").val(),
                },
                success: function (response) {
                    $("#document-dialog").dialog('open');
                    $("#document-id").val(id);
                    $("#doc-title").val(response.doc_title);
                    $("#doc-number").val(response.doc_number);
                    $("#doc-revision").val(response.doc_revision);
                    $("#doc-date").val(response.doc_date);
                    $("#doc-url").val(response.doc_url);
                    // Job list in document
                    for(index=0;index<50;index++) {
                        $("#doc-job-list-"+index).hide();
                        $("#doc-job-list-"+index).empty();
                    }
                    $.each(response.job_array, function (index, value) {
                        output = '';
                        output = output+'<td style="text-align:center;"><input type="checkbox" id="check-doc-job-'+value.job_id+'" /></td>';
                        output = output+'<td style="text-align:center;">'+value.job_title+'</td>';
                        output = output+'<td>'+value.job_content+'</td>';
                        output = output+'<td style="text-align:center;">'+value.job_submit_user+'</td>';
                        output = output+'<td style="text-align:center;">'+value.job_submit_time+'</td>';
                        output = output+'<td style="text-align:center;"><span id="btn-edit-doc-job-'+value.job_id+'" class="dashicons dashicons-edit"></span></td>';
                        $("#doc-job-list-"+index).append(output);
                        $("#doc-job-list-"+index).show();
                    })

                    $('[id^="btn-"]').mouseover(function() {
                        $(this).css('cursor', 'pointer');
                        $(this).css('color', 'red');
                    });
                        
                    $('[id^="btn-"]').mouseout(function() {
                        $(this).css('cursor', 'default');
                        $(this).css('color', 'black');
                    });
            
                    $('[id^="btn-edit-doc-job-"]').on( "click", function() {
                        id = this.id;
                        id = id.substring(17);
                        get_job_action_list_data(id);

                    });
                
                },
                error: function (error) {
                    console.error(error);                
                    alert(error);
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
                        '_document_id': id,
                    },
                    success: function (response) {
                        get_document_list_data($("#site-id").val());
                    },
                    error: function(error){
                        console.error(error);                    
                        alert(error);
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
                        '_doc_title': $("#doc-title").val(),
                        '_doc_number': $("#doc-number").val(),
                        '_doc_revision': $("#doc-revision").val(),
                        '_doc_date': $("#doc-date").val(),
                        '_doc_url': $("#doc-url").val(),
                    },
                    success: function (response) {
                        $("#document-dialog").dialog('close');
                        get_document_list_data($("#site-id").val());
                    },
                    error: function (error) {
                        console.error(error);                    
                        alert(error);
                    }
                });            
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });

    $("#doc-job-dialog").dialog({
        width: 500,
        modal: true,
        autoOpen: false,
    });

    function get_job_action_list_data(id){
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_job_action_list_data',
                '_job_id': id,
            },
            success: function (response) {            
                $("#doc-job-dialog").dialog('open');
                // Action list in job
                for(index=0;index<50;index++) {
                    $("#job-action-list-"+index).hide();
                    $("#job-action-list-"+index).empty();
                }
                $.each(response.action_array, function (index, value) {
                    output = '';
                    output = output+'<td style="text-align:center;"><span id="btn-edit-job-action-'+value.action_id+'" class="dashicons dashicons-edit"></span></td>';
                    output = output+'<td style="text-align:center;">'+value.action_title+'</td>';
                    output = output+'<td>'+value.action_content+'</td>';
                    output = output+'<td style="text-align:center;">'+value.next_job+'</td>';
                    output = output+'<td style="text-align:center;">'+value.next_leadtime+'</td>';
                    output = output+'<td style="text-align:center;"><span id="btn-del-job-action-'+value.action_id+'" class="dashicons dashicons-trash"></span></td>';
                    $("#job-action-list-"+index).append(output);
                    $("#job-action-list-"+index).show();
                })

                $('[id^="btn-"]').mouseover(function() {
                    $(this).css('cursor', 'pointer');
                    $(this).css('color', 'red');
                });
                    
                $('[id^="btn-"]').mouseout(function() {
                    $(this).css('cursor', 'default');
                    $(this).css('color', 'black');
                });
                
                $('[id^="btn-edit-job-action-"]').on( "click", function() {
                    id = this.id;
                    id = id.substring(20);
                    jQuery.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'get_job_action_dialog_data',
                            '_action_id': id,
                            '_job_id': $("#job-id").val(),
                        },
                        success: function (response) {
                            $("#action-dialog").dialog('open');
                            $("#action-id").val(id);
                            $("#action-title").val(response.action_title);
                            $("#action-content").val(response.action_content);
                        },
                        error: function (error) {
                            console.error(error);                
                            alert(error);
                        }
                    });
                });
            
                $('[id^="btn-del-job-action-"]').on( "click", function() {
                    id = this.id;
                    id = id.substring(19);
                    if (window.confirm("Are you sure you want to delete this job action?")) {
                        jQuery.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_job_action_dialog_data',
                                '_job_id': id,
                            },
                            success: function (response) {
                                get_job_action_list_data($("#job-id").val());
                            },
                            error: function(error){
                                alert(error);
                            }
                        });
                    }
                });        
        
            },
            error: function (error) {
                console.error(error);                
                alert(error);
            }
        });

        $('[id^="btn-"]').mouseover(function() {
            $(this).css('cursor', 'pointer');
            $(this).css('color', 'red');
        });
            
        $('[id^="btn-"]').mouseout(function() {
            $(this).css('cursor', 'default');
            $(this).css('color', 'black');
        });
        
        $("#btn-new-job-action").on("click", function() {
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_job_action_dialog_data',
                    '_job_id': $("#job-id").val(),
                },
                success: function (response) {
                    get_job_action_list_data($("#job-id").val());
                },
                error: function(error){
                    console.error(error);                    
                    alert(error);
                }
            });    
        });        

    }

    $("#action-dialog").dialog({
        width: 500,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'set_job_action_dialog_data',
                        '_action_id': $("#action-id").val(),
                        '_action_title': $("#action-title").val(),
                        '_action_content': $("#action-content").val(),
                    },
                    success: function (response) {
                        $("#action-dialog").dialog('close');
                        get_job_action_list_data($("#job-id").val());
                    },
                    error: function (error) {
                        console.error(error);                    
                        alert(error);
                    }
                });            
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });
});

// my-jobs
jQuery(document).ready(function($) {

    activate_my_job_list_data()

    $("#btn-new-site-job").on("click", function() {
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                //'action': 'new_site_job_data',
                'action': 'set_site_job_dialog_data',
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
            jQuery.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_site_job_dialog_data',
                    '_job_id': id,
                    '_site_id': $("#site-id").val(),
                },
                success: function (response) {
                    $("#job-dialog").dialog('open');
                    $("#job-id").val(id);
                    $("#job-title").val(response.job_title);
                    $("#job-content").val(response.job_content);
                },
                error: function (error) {
                    console.error(error);                
                    alert(error);
                }
            });
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
                        'action': 'del_site_job_dialog_data',
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
                    output = output+'<td style="text-align:center;>'+value.job_title+'</td>';
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

    $("#job-dialog").dialog({
        width: 500,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'set_site_job_dialog_data',
                        '_job_id': $("#job-id").val(),
                        '_job_title': $("#job-title").val(),
                        '_job_content': $("#job-content").val(),
                    },
                    success: function (response) {
                        $("#job-dialog").dialog('close');
                        get_my_job_list_data($("#site-id").val());
                    },
                    error: function (error) {
                        console.error(error);                    
                        alert(error);
                    }
                });            
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });

});


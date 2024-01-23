// display documents
jQuery(document).ready(function($) {

    activate_document_list_data()

    $('[id^="btn-"]').mouseover(function() {
        $(this).css('cursor', 'pointer');
        $(this).css('color', 'red');
    });
        
    $('[id^="btn-"]').mouseout(function() {
        $(this).css('cursor', 'default');
        $(this).css('color', 'black');
    });

    $("#btn-new-document").on("click", function() {
        $.ajax({
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

    function get_document_list_data(siteId) {
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_document_list_data',
                '_site_id': siteId,
            },
            success: function (response) {
                for (let index = 0; index < 50; index++) {
                    $(`.document-list-${index}`).hide().empty();
                }    
                $.each(response, function (index, value) {
                    $(`.document-list-${index}`).attr("id", `edit-document-${value.doc_id}`)
                    const output = `
                        <td style="text-align: center;">${value.doc_number}</td>
                        <td>${value.doc_title}</td>
                        <td style="text-align: center;">${value.doc_revision}</td>
                        <td style="text-align: center;">${value.doc_date}</td>`;
                    $(`.document-list-${index}`).append(output).show();
                });
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

    function activate_document_list_data(){
        $('[id^="edit-document-"]').on( "click", function() {
            id = this.id;
            id = id.substring(14);
            $.ajax({
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
                    $("#doc-url").val(response.doc_url);
                    $("#start-job").empty().append(response.start_job);
                    //$("#start-job").append(response.start_job);
                    $("#start-leadtime").val(response.start_leadtime);
                    $("#doc-date").val(response.doc_date);
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });            
        });
    }

    $("#document-dialog").dialog({
        width: 600,
        modal: true,
        autoOpen: false,
        buttons: {
            "Save": function() {
                if (window.confirm("Are you sure you want to proceed this action?")) {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_document_dialog_data',
                            '_doc_id': $("#doc-id").val(),
                            '_doc_title': $("#doc-title").val(),
                            '_doc_number': $("#doc-number").val(),
                            '_doc_revision': $("#doc-revision").val(),
                            '_doc_url': $("#doc-url").val(),
                            '_start_job': $("#start-job").val(),
                            '_start_leadtime': $("#start-leadtime").val(),
                            '_doc_date': $("#doc-date").val(),
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
                }
            },
            "Delete": function() {
                if (window.confirm("Are you sure you want to delete this document?")) {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'del_document_dialog_data',
                            '_doc_id': $("#doc-id").val(),
                        },
                        success: function (response) {
                            $("#document-dialog").dialog('close');
                            get_document_list_data($("#site-id").val());
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
});

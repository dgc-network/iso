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
                    alert(error);
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
            },
            success: function (response) {
                get_document_list_data('');
            },
            error: function(error){
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
                },
                error: function (error) {
                    // Log the error object to the console for debugging
                    console.error(error);                
                    // Display the responseText if available
                    if (error.responseText) {
                        alert('Error: ' + error.responseText);
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
                        alert(error);
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
                    //$("#document-id-"+index).val(value.document_id);
                });

                activate_document_list_data();
            },
            error: function(error){
                alert(error);
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
                            alert('Error: ' + error.responseText);
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
/*
jQuery(document).ready(function($) {
    wp.domReady(() => {
        const editorElement = document.getElementById('frontend-editor');
    
        if (editorElement) {
            const editor = wp.element.createElement(wp.editor.Editor, {
                onChange: (content) => {
                    // Handle content changes
                    console.log(content);
                },
                value: '<!-- Add your initial content here -->',
                allowedBlocks: ['core/paragraph', 'core/image'], // Specify allowed block types
            });
    
            wp.editor.render(editor, editorElement);
        }
    });
    
});
*/
jQuery(document).ready(function($) {
    $('.remove-from-cart').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');

        // Use AJAX to remove the item from the cart on the server
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url, // Use WordPress AJAX URL
            data: {
                action: 'remove_from_cart',
                product_id: productId,
            },
            success: function(response) {
                // Update the cart display on the page
                // (You might need to refresh the entire cart section or update individual elements)
                // Reload the page after successful removal
                location.reload();
                console.log(response);
            }
        });
    });
});

jQuery(document).ready(function($) {
    $('.add-to-cart-form').submit(function(e) {
        e.preventDefault();

        var form = $(this);
        var formDataArray = form.serializeArray();
        
        var formDataObject = {};
        $.each(formDataArray, function(index, field){
            formDataObject[field.name] = field.value;
        });
        
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                action: 'add_to_cart_ajax',
                formData: formDataObject,
                nonce: ajax_object.nonce, // Include nonce in the AJAX request
            },
            dataType: 'json',
            success: function(response) {
                // Handle the JSON response from the server
                if (response.success) {
                    // Display success message or perform other actions
                    console.log(response.message);
                    // Optionally, redirect to the shopping cart page
                    window.location.href = response.cart_url;
                } else {
                    // Display error message or perform other actions
                    console.error('Error adding item to cart:', response.message);
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX errors
                console.error('AJAX request failed:', status, error);
            }
        });
    });
});


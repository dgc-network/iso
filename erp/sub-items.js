jQuery(document).ready(function($) {
    // doc-category scripts
    function activate_doc_category_list_data(){
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

        $("#new-doc-category").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_doc_category_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_doc_category_list_data();
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-doc-category-"]').on("click", function () {
            const category_id = this.id.substring(18);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_doc_category_dialog_data',
                    '_category_id': category_id,
                    //'paged': 1
                },
                success: function (response) {
                    $("#doc-category-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#doc-category-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_doc_category_dialog_data',
                                        '_category_id': $("#category-id").val(),
                                        '_category_title': $("#category-title").val(),
                                        '_category_content': $("#category-content").val(),
                                        '_iso_category': $("#iso-category").val(),
                                    },
                                    success: function (response) {
                                        $("#doc-category-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_doc_category_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this doc category?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_doc_category_dialog_data',
                                            '_category_id': $("#category-id").val(),
                                        },
                                        success: function (response) {
                                            $("#doc-category-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_doc_category_list_data();
                                        },
                                        error: function (error) {
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            },
                        });
                    }
                    $("#doc-category-dialog").dialog('open');
                    activate_audit_item_list_data(category_id)                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#doc-category-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });
    }
    
    // sub-category scripts
    activate_sub_category_list_data()
    function activate_sub_category_list_data(){
        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

        $("#new-sub-category").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_sub_category_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_sub_category_list_data();
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-sub-category-"]').on("click", function () {
            const category_id = this.id.substring(18);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_sub_category_dialog_data',
                    '_category_id': category_id,
                },
                success: function (response) {
                    $("#sub-category-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#sub-category-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_sub_category_dialog_data',
                                        '_category_id': $("#category-id").val(),
                                        '_category_title': $("#category-title").val(),
                                        '_category_code': $("#category-code").val(),
                                        '_iso_category': $("#iso-category").val(),
                                        '_is_privated': $("#is-privated").is(":checked") ? 1 : 0,
                                    },
                                    success: function (response) {
                                        $("#sub-category-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_sub_category_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this doc category?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_sub_category_dialog_data',
                                            '_category_id': $("#category-id").val(),
                                        },
                                        success: function (response) {
                                            $("#sub-category-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_sub_category_list_data();
                                        },
                                        error: function (error) {
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            },
                        });
                    }
                    $("#sub-category-dialog").dialog('open');
                    activate_sub_item_list_data(category_id);
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#sub-category-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });
    }

    // sub-item scripts
    function activate_sub_item_list_data(category_id){
        $("#new-sub-item").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_sub_item_dialog_data',
                    '_category_id': $("#category-id").val(),
                },
                success: function (response) {
                    $("#sub-item-list").html(response.html_contain);
                    activate_sub_item_list_data(category_id);
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('#sortable-sub-item-list').sortable({
            update: function(event, ui) {
                const sub_item_id_array = $(this).sortable('toArray', { attribute: 'data-sub-item-id' });                
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: 'json',
                    data: {
                        action: 'sort_sub_item_list_data',
                        _sub_item_id_array: sub_item_id_array,
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

        $('[id^="edit-sub-item-"]').on("click", function () {
            const sub_item_id = this.id.substring(14);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_sub_item_dialog_data',
                    '_sub_item_id': sub_item_id,
                },
                success: function (response) {
                    $("#sub-item-dialog").html(response.html_contain);
                    $("#sub-item-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#sub-item-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {
                "Save": function () {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_sub_item_dialog_data',
                            '_category_id': $("#category-id").val(),
                            '_sub_item_id': $("#sub-item-id").val(),
                            '_sub_item_title': $("#sub-item-title").val(),
                            '_sub_item_code': $("#sub-item-code").val(),
                            '_sub_item_type': $("#sub-item-type").val(),
                            '_sub_item_default': $("#sub-item-default").val(),
                        },
                        success: function (response) {
                            $("#sub-item-dialog").dialog('close');
                            $("#sub-item-list").html(response.html_contain);
                            activate_sub_item_list_data(category_id)
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this item?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_sub_item_dialog_data',
                                '_category_id': $("#category-id").val(),
                                '_sub_item_id': $("#sub-item-id").val(),
                            },
                            success: function (response) {
                                $("#sub-item-dialog").dialog('close');
                                $("#sub-item-list").html(response.html_contain);
                                activate_sub_item_list_data(category_id)
                            },
                            error: function (error) {
                                console.error(error);
                                alert(error);
                            }
                        });
                    }
                },
            }
        });
    }

    // iso-category scripts
    activate_iso_category_list_data();
    function activate_iso_category_list_data(){

        $("#select-profile").on("change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        });

        $("#new-iso-category").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_iso_category_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_iso_category_list_data();
                    },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-iso-category-"]').on("click", function () {
            const category_id = this.id.substring(18);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_iso_category_dialog_data',
                    '_category_id': category_id,
                    //'paged': 1
                },
                success: function (response) {
/*                    
                    // Get the current URL
                    const currentUrl = new URL(window.location.href);
                    // Get the current search parameters
                    const params = new URLSearchParams(currentUrl.search);                
                    // Add or update the _category_id parameter
                    params.set("_category_id", category_id);
                    // Construct the new URL with the updated parameters
                    // Construct the new URL with the updated parameters
                    const newUrl = `${currentUrl.pathname}?${params.toString()}`;                
                    // Update the URL in the browser without reloading the page
                    window.history.pushState({ path: newUrl }, '', newUrl);                
*/
                    $("#iso-category-dialog").html(response.html_contain);
                    $("#iso-category-dialog").dialog('open');
                    activate_audit_item_list_data(category_id)
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        // Extract category_id from URL
        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);
        const category_id = params.get('_category_id');
        // Extract page number from URL path
        const pathSegments = currentUrl.pathname.split('/');
        let paged = 1;
        const pageIndex = pathSegments.indexOf('page');
        if (pageIndex !== -1 && pathSegments[pageIndex + 1]) {
            paged = parseInt(pathSegments[pageIndex + 1], 10);
        }

        if (category_id) {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_iso_category_dialog_data',
                    '_category_id': category_id,
                    'paged': paged
                },
                success: function(response) {
                    // Update the URL in the browser without reloading the page
                    const newUrl = `${currentUrl.pathname}?${params.toString()}`;
                    window.history.pushState({ path: newUrl }, '', newUrl);
    
                    // Update the dialog with the received content
                    $("#iso-category-dialog").html(response.html_contain);
                    $("#iso-category-dialog").dialog('open');
                    activate_audit_item_list_data(category_id);
                },
                error: function(error) {
                    console.error(error);
                    alert(error);
                }
            });
        }
        
        $("#iso-category-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {
                "Save": function () {
                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_iso_category_dialog_data',
                            '_category_id': $("#category-id").val(),
                            '_category_title': $("#category-title").val(),
                            '_category_content': $("#category-content").val(),
                            '_category_url': $("#category-url").val(),
                            '_parent_category': $("#parent-category").val(),
                        },
                        success: function (response) {
                            $("#iso-category-dialog").dialog('close');
                            $("#result-container").html(response.html_contain);
                            activate_iso_category_list_data();
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this iso category?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_iso_category_dialog_data',
                                '_category_id': $("#category-id").val(),
                            },
                            success: function (response) {
                                $("#iso-category-dialog").dialog('close');
                                $("#result-container").html(response.html_contain);
                                activate_iso_category_list_data();
                            },
                            error: function (error) {
                                console.error(error);
                                alert(error);
                            }
                        });
                    }
                },
            }
        });
    }
});

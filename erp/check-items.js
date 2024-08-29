jQuery(document).ready(function($) {
    // check-category scripts
    activate_check_category_list_data()
    function activate_check_category_list_data(){
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

        $("#new-check-category").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_check_category_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_check_category_list_data();
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-check-category-"]').on("click", function () {
            const category_id = this.id.substring(20);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_check_category_dialog_data',
                    '_category_id': category_id,
                    //'paged': 1
                },
                success: function (response) {
                    $("#check-category-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#check-category-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_check_category_dialog_data',
                                        '_category_id': $("#category-id").val(),
                                        '_category_title': $("#category-title").val(),
                                        '_category_code': $("#category-code").val(),
                                        '_iso_category': $("#iso-category").val(),
                                    },
                                    success: function (response) {
                                        $("#check-category-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_check_category_list_data();
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
                                            'action': 'del_check_category_dialog_data',
                                            '_category_id': $("#category-id").val(),
                                        },
                                        success: function (response) {
                                            $("#check-category-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_check_category_list_data();
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
                    $("#check-category-dialog").dialog('open');
                    activate_check_item_list_data(category_id)                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#check-category-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });
    }

    // check-item scripts
    function activate_check_item_list_data(category_id){
        $("#new-check-item").on("click", function() {
            // Extract page number from URL path
            const currentUrl = new URL(window.location.href);
            const pathSegments = currentUrl.pathname.split('/');
            let paged = 1;
            const pageIndex = pathSegments.indexOf('page');
            if (pageIndex !== -1 && pathSegments[pageIndex + 1]) {
                paged = parseInt(pathSegments[pageIndex + 1], 10);
            }

            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_check_item_dialog_data',
                    '_category_id': $("#category-id").val(),
                    //'paged': paged
                },
                success: function (response) {
                    $("#check-item-list").html(response.html_contain);
                    activate_check_item_list_data(category_id);
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('#sortable-check-item-list').sortable({
            update: function(event, ui) {
                const check_item_id_array = $(this).sortable('toArray', { attribute: 'data-check-item-id' });                
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: 'json',
                    data: {
                        action: 'sort_check_item_list_data',
                        _check_item_id_array: check_item_id_array,
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

        $('[id^="edit-check-item-"]').on("click", function () {
            const check_item_id = this.id.substring(16);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_check_item_dialog_data',
                    '_check_item_id': check_item_id,
                },
                success: function (response) {
                    $("#check-item-dialog").html(response.html_contain);
                    $("#check-item-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#check-item-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {
                "Save": function () {
                    // Extract page number from URL path
                    const currentUrl = new URL(window.location.href);
                    const pathSegments = currentUrl.pathname.split('/');
                    let paged = 1;
                    const pageIndex = pathSegments.indexOf('page');
                    if (pageIndex !== -1 && pathSegments[pageIndex + 1]) {
                        paged = parseInt(pathSegments[pageIndex + 1], 10);
                    }

                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_check_item_dialog_data',
                            '_category_id': $("#category-id").val(),
                            '_check_item_id': $("#check-item-id").val(),
                            '_check_item_title': $("#check-item-title").val(),
                            //'_audit_content': $("#audit-content").val(),
                            '_check_item_code': $("#check-item-code").val(),
                            '_check_item_type': $("#check-item-type").val(),
                            //'_display_on_report_only': $("#is-report-only").is(":checked") ? 1 : 0,
                            //'_is_radio_option': $("#is-checkbox").is(":checked") ? 1 : 0,
                            //'paged': paged
                        },
                        success: function (response) {
                            $("#check-item-dialog").dialog('close');
                            $("#check-item-list").html(response.html_contain);
                            activate_check_item_list_data(category_id)
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this item?")) {
                        // Extract page number from URL path
                        const currentUrl = new URL(window.location.href);
                        const pathSegments = currentUrl.pathname.split('/');
                        let paged = 1;
                        const pageIndex = pathSegments.indexOf('page');
                        if (pageIndex !== -1 && pathSegments[pageIndex + 1]) {
                            paged = parseInt(pathSegments[pageIndex + 1], 10);
                        }

                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_check_item_dialog_data',
                                '_category_id': $("#category-id").val(),
                                '_check_item_id': $("#check-item-id").val(),
                                //'paged': paged
                            },
                            success: function (response) {
                                $("#check-item-dialog").dialog('close');
                                $("#check-item-list").html(response.html_contain);
                                activate_check_item_list_data(category_id)
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
                    'paged': 1
                },
                success: function (response) {
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
    
    // audit-item scripts
    function activate_audit_item_list_data(category_id){
        $("#new-audit-item").on("click", function() {
            // Extract page number from URL path
            const currentUrl = new URL(window.location.href);
            const pathSegments = currentUrl.pathname.split('/');
            let paged = 1;
            const pageIndex = pathSegments.indexOf('page');
            if (pageIndex !== -1 && pathSegments[pageIndex + 1]) {
                paged = parseInt(pathSegments[pageIndex + 1], 10);
            }

            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_audit_item_dialog_data',
                    '_category_id': $("#category-id").val(),
                    'paged': paged
                },
                success: function (response) {
                    $("#audit-item-list").html(response.html_contain);
                    activate_audit_item_list_data(category_id);
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('#sortable-audit-item-list').sortable({
            update: function(event, ui) {
                const audit_id_array = $(this).sortable('toArray', { attribute: 'data-audit-id' });                
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: 'json',
                    data: {
                        action: 'sort_audit_item_list_data',
                        _audit_id_array: audit_id_array,
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

        $('[id^="edit-audit-item-"]').on("click", function () {
            const audit_id = this.id.substring(16);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_audit_item_dialog_data',
                    '_audit_id': audit_id,
                },
                success: function (response) {
                    $("#audit-item-dialog").html(response.html_contain);
                    $("#audit-item-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#audit-item-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {
                "Save": function () {
                    // Extract page number from URL path
                    const currentUrl = new URL(window.location.href);
                    const pathSegments = currentUrl.pathname.split('/');
                    let paged = 1;
                    const pageIndex = pathSegments.indexOf('page');
                    if (pageIndex !== -1 && pathSegments[pageIndex + 1]) {
                        paged = parseInt(pathSegments[pageIndex + 1], 10);
                    }

                    $.ajax({
                        type: 'POST',
                        url: ajax_object.ajax_url,
                        dataType: "json",
                        data: {
                            'action': 'set_audit_item_dialog_data',
                            '_category_id': $("#category-id").val(),
                            '_audit_id': $("#audit-id").val(),
                            '_audit_title': $("#audit-title").val(),
                            '_audit_content': $("#audit-content").val(),
                            '_clause_no': $("#clause-no").val(),
                            '_field_type': $("#field-type").val(),
                            '_display_on_report_only': $("#is-report-only").is(":checked") ? 1 : 0,
                            '_is_radio_option': $("#is-checkbox").is(":checked") ? 1 : 0,
                            'paged': paged
                        },
                        success: function (response) {
                            $("#audit-item-dialog").dialog('close');
                            $("#audit-item-list").html(response.html_contain);
                            activate_audit_item_list_data(category_id)
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this ISO clause?")) {
                        // Extract page number from URL path
                        const currentUrl = new URL(window.location.href);
                        const pathSegments = currentUrl.pathname.split('/');
                        let paged = 1;
                        const pageIndex = pathSegments.indexOf('page');
                        if (pageIndex !== -1 && pathSegments[pageIndex + 1]) {
                            paged = parseInt(pathSegments[pageIndex + 1], 10);
                        }

                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_audit_item_dialog_data',
                                '_category_id': $("#category-id").val(),
                                '_audit_id': $("#audit-id").val(),
                                'paged': paged
                            },
                            success: function (response) {
                                $("#audit-item-dialog").dialog('close');
                                $("#audit-item-list").html(response.html_contain);
                                activate_audit_item_list_data(category_id)
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

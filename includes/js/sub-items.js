jQuery(document).ready(function($) {
    // doc-category scripts
    activate_doc_category_list_data();
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
                    //activate_audit_item_list_data(category_id)                
                },
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
    
    // embedded scripts
    activate_embedded_list_data()
    function activate_embedded_list_data(){
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

        $("#new-embedded").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_embedded_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_embedded_list_data();
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-embedded-"]').on("click", function () {
            const embedded_id = this.id.substring(14);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_embedded_dialog_data',
                    '_embedded_id': embedded_id,
                },
                success: function (response) {
                    $("#embedded-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#embedded-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_embedded_dialog_data',
                                        '_embedded_id': embedded_id,
                                        '_embedded_title': $("#embedded-title").val(),
                                        '_embedded_number': $("#embedded-number").val(),
                                        '_iso_category': $("#iso-category").val(),
                                        '_is_private': $("#is-private").is(":checked") ? 1 : 0,
                                    },
                                    success: function (response) {
                                        $("#embedded-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_embedded_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this sub form?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_embedded_dialog_data',
                                            '_embedded_id': embedded_id,
                                        },
                                        success: function (response) {
                                            $("#embedded-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_embedded_list_data();
                                        },
                                        error: function (error) {
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            },
                            "Duplicate": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'duplicate_embedded_dialog_data',
                                        '_embedded_id': embedded_id,
                                        '_embedded_title': $("#embedded-title").val(),
                                        '_embedded_number': $("#embedded-number").val(),
                                        '_iso_category': $("#iso-category").val(),
                                    },
                                    success: function (response) {
                                        $("#embedded-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_embedded_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                        });
                    }
                    $("#embedded-dialog").dialog('open');
                    activate_sub_item_list_data(embedded_id);
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#embedded-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });
    }

    // sub-item scripts
    function activate_sub_item_list_data(embedded_id){
        $("#new-sub-item").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_sub_item_dialog_data',
                    '_embedded_id': embedded_id,
                },
                success: function (response) {
                    $("#sub-item-list").html(response.html_contain);
                    activate_sub_item_list_data(embedded_id);
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
                            //'_embedded_id': embedded_id,
                            '_embedded_id': $("#embedded-id").val(),
                            '_sub_item_id': $("#sub-item-id").val(),
                            '_sub_item_title': $("#sub-item-title").val(),
                            '_sub_item_type': $("#sub-item-type").val(),
                            '_sub_item_default': $("#sub-item-default").val(),
                            '_sub_item_code': $("#sub-item-code").val(),
                        },
                        success: function (response) {
                            $("#sub-item-dialog").dialog('close');
                            $("#sub-item-list").html(response.html_contain);
                            activate_sub_item_list_data(embedded_id)
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
                                //'_embedded_id': embedded_id,
                                '_embedded_id': $("#embedded-id").val(),
                                '_sub_item_id': $("#sub-item-id").val(),
                            },
                            success: function (response) {
                                $("#sub-item-dialog").dialog('close');
                                $("#sub-item-list").html(response.html_contain);
                                activate_sub_item_list_data(embedded_id)
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
                },
                success: function (response) {
                    $("#iso-category-dialog").html(response.html_contain);
                    $("#iso-category-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

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
                            '_embedded': $("#embedded").val(),
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

    // department-card scripts
    activate_department_card_list_data();
    function activate_department_card_list_data(){
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

        $("#search-department").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
            var searchValue = $("#search-department").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
            // Clear the values of all select elements after redirection
            $("#search-department").val('');
        });

        $("#new-department-card").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_department_card_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_department_card_list_data();    
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-department-card-"]').on("click", function () {
            const department_id = this.id.substring(21);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_department_card_dialog_data',
                    '_department_id': department_id,
                },
                success: function (response) {
                    $("#department-card-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#department-card-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_department_card_dialog_data',
                                        '_department_id': $("#department-id").val(),
                                        '_department_code': $("#department-code").val(),
                                        '_department_title': $("#department-title").val(),
                                        '_department_content': $("#department-content").val(),
                                    },
                                    success: function (response) {
                                        $("#department-card-dialog").dialog('close');
                                        $("#result-container").html(response.html_contain);
                                        activate_department_card_list_data();
                                    },
                                    error: function (error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });
                            },
                            "Delete": function () {
                                if (window.confirm("Are you sure you want to delete this department?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_department_card_dialog_data',
                                            '_department_id': $("#department-id").val(),
                                        },
                                        success: function (response) {
                                            $("#department-card-dialog").dialog('close');
                                            $("#result-container").html(response.html_contain);
                                            activate_department_card_list_data();
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
                    $("#department-card-dialog").dialog('open');
                    activate_department_user_list_data();
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#department-card-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });    
    }

    // department-user scripts
    function activate_department_user_list_data(){
        $("#new-department-user").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_department_user_list_data',
                },
                success: function (response) {
                    $("#department-user-dialog").html(response.html_contain);
                    $("#department-user-dialog").dialog('open');
                    $('[id^="edit-department-user-"]').on("click", function () {
                        if (window.confirm("Are you sure you want to add this user?")) {
                            const user_id = this.id.substring(21);
                            $.ajax({
                                type: 'POST',
                                url: ajax_object.ajax_url,
                                dataType: "json",
                                data: {
                                    'action': 'add_department_user_dialog_data',
                                    '_department_id': $("#department-id").val(),
                                    '_user_id': user_id,
                                },
                                success: function (response) {
                                    $("#department-user-dialog").dialog('close');
                                    $("#department-user-list").html(response.html_contain);
                                    activate_department_user_list_data();
                                },
                                error: function (error) {
                                    console.error(error);
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
        });
    
        $('[id^="edit-department-user-"]').on("click", function () {
            if (window.confirm("Are you sure you want to delete this user?")) {
                const user_id = this.id.substring(21);
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'del_department_user_dialog_data',
                        '_department_id': $("#department-id").val(),
                        '_user_id': user_id,
                    },
                    success: function (response) {
                        $("#department-user-list").html(response.html_contain);
                        activate_department_user_list_data();
                    },
                    error: function (error) {
                        console.error(error);
                        alert(error);
                    }
                });
            }
        });

        $("#department-user-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
        });    
    }    

});

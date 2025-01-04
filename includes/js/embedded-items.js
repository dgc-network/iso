jQuery(document).ready(function($) {
    // embedded
    const prevEmbeddedId = $("#prev-embedded-id").val();
    const nextEmbeddedId = $("#next-embedded-id").val();

    // Function to navigate to the previous or next record
    function navigateToEmbedded(Id) {
        if (Id) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set("_embedded_id", Id);
            window.location.href = currentUrl.toString();
        }
    }

    // Keyboard navigation
    $(document).on("keydown", function (event) {
        if (event.ctrlKey && event.key === "ArrowRight" && nextEmbeddedId) {
            navigateToEmbedded(nextEmbeddedId); // Move to the next record
        } else if (event.ctrlKey && event.key === "ArrowLeft" && prevEmbeddedId) {
            navigateToEmbedded(prevEmbeddedId); // Move to the previous record
        }
    });

    // Touch navigation for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    $(document).on("touchstart", function (event) {
        touchStartX = event.originalEvent.changedTouches[0].screenX;
    });

    $(document).on("touchend", function (event) {
        touchEndX = event.originalEvent.changedTouches[0].screenX;
        handleEmbeddedSwipe();
    });

    function handleEmbeddedSwipe() {
        const swipeThreshold = 50; // Minimum swipe distance
        if (touchEndX < touchStartX - swipeThreshold && nextEmbeddedId) {
            navigateToEmbedded(nextEmbeddedId); // Swipe left: Move to the next record
        } else if (touchEndX > touchStartX + swipeThreshold && prevEmbeddedId) {
            navigateToEmbedded(prevEmbeddedId); // Swipe right: Move to the previous record
        }
    }

    // doc-category
    activate_doc_category_list_data();
    function activate_doc_category_list_data(){
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
    
    // embedded
    activate_embedded_list_data()
    function activate_embedded_list_data(){
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

            // Get the current URL
            const currentUrl = window.location.href;
            // Extract the page number using a regular expression
            const pageMatch = currentUrl.match(/\/page\/(\d+)\//);
            if (pageMatch) {
                const pageNumber = pageMatch[1]; // Extracted page number
                localStorage.setItem('embedded_paged', pageNumber);
            }
            
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or update the parameters
            urlParams.set("_embedded_id", embedded_id);
            urlParams.set("paged", 1);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
        });

        $("#save-embedded-button").on("click", function () {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_embedded_dialog_data',
                    '_embedded_id': $("#embedded-id").val(),
                    '_embedded_title': $("#embedded-title").val(),
                    '_embedded_number': $("#embedded-number").val(),
                    '_embedded_type': $("#embedded-type").val(),
                    '_iso_category': $("#iso-category").val(),
                    '_is_private': $("#is-private").is(":checked") ? 1 : 0,
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    // Get existing URL parameters
                    const urlParams = new URLSearchParams(window.location.search);
                    // Remove or update the parameters
                    urlParams.delete('_embedded_id');
                    urlParams.set("paged", localStorage.getItem('embedded_paged'));
                    // Redirect to the updated URL
                    window.location.href = "?" + urlParams.toString();
                    activate_embedded_list_data();
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#duplicate-embedded-button").on("click", function () {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'duplicate_embedded_dialog_data',
                    '_embedded_id': $("#embedded-id").val(),
                    '_embedded_title': $("#embedded-title").val(),
                    '_embedded_number': $("#embedded-number").val(),
                    '_iso_category': $("#iso-category").val(),
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    // Get existing URL parameters
                    const urlParams = new URLSearchParams(window.location.search);
                    // Remove or update the parameters
                    urlParams.delete('_embedded_id');
                    urlParams.set("paged", localStorage.getItem('embedded_paged'));
                    // Redirect to the updated URL
                    window.location.href = "?" + urlParams.toString();
                    activate_embedded_list_data();
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });

        });

        $("#del-embedded-button").on("click", function () {
            if (window.confirm("Are you sure you want to delete this sub form?")) {
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'del_embedded_dialog_data',
                        '_embedded_id': $("#embedded-id").val(),
                    },
                    success: function (response) {
                        $("#result-container").html(response.html_contain);
                        // Get existing URL parameters
                        const urlParams = new URLSearchParams(window.location.search);
                        // Remove or update the parameters
                        urlParams.delete('_embedded_id');
                        urlParams.set("paged", localStorage.getItem('embedded_paged'));
                        // Redirect to the updated URL
                        window.location.href = "?" + urlParams.toString();
                        activate_embedded_list_data();
                    },
                    error: function (error) {
                        console.error(error);
                        alert(error);
                    }
                });
            }

        });

        $("#exit-embedded-dialog").on("click", function () {
            // Get existing URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            // Remove or update the parameters
            urlParams.delete('_embedded_id');
            urlParams.set("paged", localStorage.getItem('embedded_paged'));
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
        });

    }

    activate_embedded_dialog_data();
    function activate_embedded_dialog_data(){
    }

    // embedded-item
    activate_embedded_item_list_data($("#embedded-id").val());
    function activate_embedded_item_list_data(embedded_id){
        $("#new-embedded-item").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_embedded_item_dialog_data',
                    '_embedded_id': embedded_id,
                },
                success: function (response) {
                    $("#embedded-item-list").html(response.html_contain);
                    activate_embedded_item_list_data(embedded_id);
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('#sortable-embedded-item-list').sortable({
            update: function(event, ui) {
                const embedded_item_id_array = $(this).sortable('toArray', { attribute: 'data-embedded-item-id' });                
                $.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: 'json',
                    data: {
                        action: 'sort_embedded_item_list_data',
                        _embedded_item_id_array: embedded_item_id_array,
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

        $('[id^="edit-item-"]').on("click", function () {
            const embedded_item_id = this.id.substring(10);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_embedded_item_dialog_data',
                    '_embedded_item_id': embedded_item_id,
                },
                success: function (response) {
                    $("#embedded-item-dialog").html(response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#embedded-item-dialog").dialog("option", "buttons", {
                            "Save": function () {
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: {
                                        'action': 'set_embedded_item_dialog_data',
                                        '_embedded_id': $("#embedded-id").val(),
                                        '_embedded_item_id': $("#embedded-item-id").val(),
                                        '_embedded_item_title': $("#embedded-item-title").val(),
                                        '_embedded_item_type': $("#embedded-item-type").val(),
                                        '_embedded_item_default': $("#embedded-item-default").val(),
                                        '_embedded_item_code': $("#embedded-item-code").val(),
                                    },
                                    success: function (response) {
                                        $("#embedded-item-dialog").dialog('close');
                                        $("#embedded-item-list").html(response.html_contain);
                                        activate_embedded_item_list_data(embedded_id)
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
                                            'action': 'del_embedded_item_dialog_data',
                                            '_embedded_id': $("#embedded-id").val(),
                                            '_embedded_item_id': $("#embedded-item-id").val(),
                                        },
                                        success: function (response) {
                                            $("#embedded-item-dialog").dialog('close');
                                            $("#embedded-item-list").html(response.html_contain);
                                            activate_embedded_item_list_data(embedded_id)
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
                    $("#embedded-item-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#embedded-item-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });
    }

    // iso-category
    activate_iso_category_list_data();
    function activate_iso_category_list_data(){
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
                    if ($("#is-site-admin").val() === "1") {
                        $("#iso-category-dialog").dialog("option", "buttons", {
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
                        });
                    }
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
            buttons: {}
        });
    }

    // department
    activate_department_card_list_data();
    function activate_department_card_list_data(){
        $("#search-department").on( "change", function() {
            const urlParams = new URLSearchParams(window.location.search);
            var selectValue = $("#select-profile").val();
            // Remove or Update the parameters
            if (selectValue) urlParams.set("_select_profile", selectValue);
            urlParams.set("_search", $(this).val());
            urlParams.set("paged", 1);
            // Redirect to the updated URL
            window.location.href = "?" + urlParams.toString();
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
                                        '_department_number': $("#department-number").val(),
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

    // department-user
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

    // line-report
    activate_line_report_list_data();
    function activate_line_report_list_data(){
        $("#new-line-report").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_line_report_dialog_data',
                    '_embedded_id': $("#embedded-id").val(),
                },
                success: function (get_response) {
                    $("#line-report-dialog").html(get_response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#line-report-dialog").dialog("option", "buttons", {
                            "Add": function() {
                                const ajaxData = {
                                    'action': 'set_line_report_dialog_data',
                                };
                                ajaxData['_embedded_id'] = $("#embedded-id").val();
                                field_id = $("#embedded-id").val();
                                $.each(get_response.line_report_fields, function(index, inner_value) {
                                    const line_report_field = field_id + inner_value.embedded_item_id;
                                    const line_report_field_tag = '#' + field_id + inner_value.embedded_item_id;
                                    if (inner_value.embedded_item_type === 'checkbox' || inner_value.embedded_item_type === 'radio') {
                                        ajaxData[line_report_field] = $(line_report_field_tag).is(":checked") ? 1 : 0;
                                    } else {
                                        ajaxData[line_report_field] = $(line_report_field_tag).val();
                                    }
                                });
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: ajaxData,
                                    success: function(set_response) {
                                        $("#line-report-dialog").dialog('close');
                                        $('#line-report-list').html(set_response.html_contain);
                                        activate_line_report_list_data();
                                    },
                                    error: function(error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });                    
                            },
                            "Cancel": function() {
                                $("#line-report-dialog").dialog('close');
                            }
                        });
                    }
                    $("#line-report-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);                
                    alert(error);
                }
            });
        });
    
        $('[id^="edit-line-report-"]').on( "click", function() {
            const line_report_id = this.id.substring(17);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_line_report_dialog_data',
                    '_line_report_id': line_report_id,
                    '_embedded_id': $("#embedded-id").val(),
                },
                success: function (get_response) {
                    $("#line-report-dialog").html(get_response.html_contain);
                    if ($("#is-site-admin").val() === "1") {
                        $("#line-report-dialog").dialog("option", "buttons", {
                            "Save": function() {
                                const ajaxData = {
                                    'action': 'set_line_report_dialog_data',
                                };
                                ajaxData['_line_report_id'] = line_report_id;
                                ajaxData['_embedded_id'] = $("#embedded-id").val();
                                field_id = $("#embedded-id").val();
                                $.each(get_response.line_report_fields, function(index, inner_value) {
                                    const line_report_field = field_id + inner_value.embedded_item_id;
                                    const line_report_field_tag = '#' + field_id + inner_value.embedded_item_id;
                                    if (inner_value.embedded_item_type === 'checkbox' || inner_value.embedded_item_type === 'radio') {
                                        ajaxData[line_report_field] = $(line_report_field_tag).is(":checked") ? 1 : 0;
                                    } else {
                                        ajaxData[line_report_field] = $(line_report_field_tag).val();
                                    }
                                });
                                $.ajax({
                                    type: 'POST',
                                    url: ajax_object.ajax_url,
                                    dataType: "json",
                                    data: ajaxData,
                                    success: function(set_response) {
                                        $("#line-report-dialog").dialog('close');
                                        $('#line-report-list').html(set_response.html_contain);
                                        activate_line_report_list_data();
                                    },
                                    error: function(error) {
                                        console.error(error);
                                        alert(error);
                                    }
                                });                    
                            },
                            "Delete": function() {
                                if (window.confirm("Are you sure you want to delete this line-report?")) {
                                    $.ajax({
                                        type: 'POST',
                                        url: ajax_object.ajax_url,
                                        dataType: "json",
                                        data: {
                                            'action': 'del_line_report_dialog_data',
                                            '_line_report_id': line_report_id,
                                            '_embedded_id': $("#embedded-id").val(),
                                        },
                                        success: function (del_response) {
                                            $("#line-report-dialog").dialog('close');
                                            $('#line-report-list').html(del_response.html_contain);
                                            activate_line_report_list_data();
                                        },
                                        error: function(error){
                                            console.error(error);
                                            alert(error);
                                        }
                                    });
                                }
                            }
                        });
                    }
                    $("#line-report-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);                
                    alert(error);
                }
            });
        });
    
        $("#line-report-dialog").dialog({
            width: 390,
            modal: true,
            autoOpen: false,
            buttons: {}
        });
    }
});

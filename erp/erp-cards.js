jQuery(document).ready(function($) {
    // customer-card scripts
    activate_customer_card_list_data();

    function activate_customer_card_list_data(){

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

        $("#search-customer").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
        
            var searchValue = $("#search-customer").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
        
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        
            // Clear the values of all select elements after redirection
            $("#select-profile, #search-customer").val('');
        
        });

        $("#new-customer-card").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_customer_card_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_customer_card_list_data();    
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-customer-card-"]').on("click", function () {
            const customer_id = this.id.substring(19);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_customer_card_dialog_data',
                    '_customer_id': customer_id,
                },
                success: function (response) {
                    $("#customer-card-dialog").html(response.html_contain);
                    $("#customer-card-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#customer-card-dialog").dialog({
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
                            'action': 'set_customer_card_dialog_data',
                            '_customer_id': $("#customer-id").val(),
                            '_customer_code': $("#customer-code").val(),
                            '_customer_title': $("#customer-title").val(),
                            '_customer_content': $("#customer-content").val(),
                        },
                        success: function (response) {
                            $("#customer-card-dialog").dialog('close');
                            $("#result-container").html(response.html_contain);
                            activate_customer_card_list_data();
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this customer?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_customer_card_dialog_data',
                                '_customer_id': $("#customer-id").val(),
                            },
                            success: function (response) {
                                $("#customer-card-dialog").dialog('close');
                                $("#result-container").html(response.html_contain);
                                activate_customer_card_list_data();
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

    // vendor-card scripts
    activate_vendor_card_list_data();

    function activate_vendor_card_list_data(){
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

        $("#search-vendor").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
        
            var searchValue = $("#search-vendor").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
        
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        
            // Clear the values of all select elements after redirection
            $("#select-profile, #search-vendor").val('');
        
        });

        $("#new-vendor-card").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_vendor_card_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_vendor_card_list_data();    
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-vendor-card-"]').on("click", function () {
            const vendor_id = this.id.substring(17);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_vendor_card_dialog_data',
                    '_vendor_id': vendor_id,
                },
                success: function (response) {
                    $("#vendor-card-dialog").html(response.html_contain);
                    $("#vendor-card-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#vendor-card-dialog").dialog({
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
                            'action': 'set_vendor_card_dialog_data',
                            '_vendor_id': $("#vendor-id").val(),
                            '_vendor_code': $("#vendor-code").val(),
                            '_vendor_title': $("#vendor-title").val(),
                            '_vendor_content': $("#vendor-content").val(),
                        },
                        success: function (response) {
                            $("#vendor-card-dialog").dialog('close');
                            $("#result-container").html(response.html_contain);
                            activate_vendor_card_list_data();
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this vendor?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_vendor_card_dialog_data',
                                '_vendor_id': $("#vendor-id").val(),
                            },
                            success: function (response) {
                                $("#vendor-card-dialog").dialog('close');
                                $("#result-container").html(response.html_contain);
                                activate_vendor_card_list_data();
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

    // product-card scripts
    activate_product_card_list_data();

    function activate_product_card_list_data(){
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

        $("#search-product").on( "change", function() {
            // Initialize an empty array to store query parameters
            var queryParams = [];
        
            // Check the selected value for each select element and add it to the queryParams array
            var profileValue = $("#select-profile").val();
            if (profileValue) {
                queryParams.push("_select_profile=" + profileValue);
            }
        
            var searchValue = $("#search-product").val();
            if (searchValue) {
                queryParams.push("_search=" + searchValue);
            }
        
            // Combine all query parameters into a single string
            var queryString = queryParams.join("&");
        
            // Redirect to the new URL with all combined query parameters
            window.location.href = "?" + queryString;
        
            // Clear the values of all select elements after redirection
            $("#search-product").val('');
        
        });

        $("#new-product-card").on("click", function() {
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'set_product_card_dialog_data',
                },
                success: function (response) {
                    $("#result-container").html(response.html_contain);
                    activate_product_card_list_data();    
                },
                error: function(error){
                    console.error(error);
                    alert(error);
                }
            });    
        });
    
        $('[id^="edit-product-card-"]').on("click", function () {
            const product_id = this.id.substring(18);
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                dataType: "json",
                data: {
                    'action': 'get_product_card_dialog_data',
                    '_product_id': product_id,
                },
                success: function (response) {
                    $("#product-card-dialog").html(response.html_contain);
                    $("#product-card-dialog").dialog('open');
                },
                error: function (error) {
                    console.error(error);
                    alert(error);
                }
            });
        });

        $("#product-card-dialog").dialog({
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
                            'action': 'set_product_card_dialog_data',
                            '_product_id': $("#product-id").val(),
                            '_product_code': $("#product-code").val(),
                            '_product_title': $("#product-title").val(),
                            '_product_content': $("#product-content").val(),
                        },
                        success: function (response) {
                            $("#product-card-dialog").dialog('close');
                            $("#result-container").html(response.html_contain);
                            activate_product_card_list_data();
                        },
                        error: function (error) {
                            console.error(error);
                            alert(error);
                        }
                    });
                },
                "Delete": function () {
                    if (window.confirm("Are you sure you want to delete this product?")) {
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'del_product_card_dialog_data',
                                '_product_id': $("#product-id").val(),
                            },
                            success: function (response) {
                                $("#product-card-dialog").dialog('close');
                                $("#result-container").html(response.html_contain);
                                activate_product_card_list_data();
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

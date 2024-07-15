jQuery(document).ready(function($) {
    // customer-card scripts
    activate_customer_card_list_data();

    function activate_customer_card_list_data(){

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
                    //get_customer_card_list_data();
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
                    //activate_notification_list_data(customer_id);

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
                            '_description': $("#description").val(),
                        },
                        success: function (response) {
                            $("#customer-card-dialog").dialog('close');
                            //get_customer_card_list_data();
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
                                //get_customer_card_list_data();
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

    function get_customer_card_list_data(){
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_customer_card_list_data',
            },
            success: function (response) {
                $("#result-container").html(response.html_contain);
                activate_customer_card_list_data();
            },
            error: function (error) {
                console.error(error);
                alert(error);
            }
        });
    }

});

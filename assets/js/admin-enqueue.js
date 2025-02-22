jQuery(document).ready(function($) {
/*    
    // Hide the Custom Fields metabox
    $("#postcustom").hide();

    // Function to check if the string is a valid URL
    function isURL(str) {
        var pattern = /^(http|https):\/\/[^ "]+$/;
        return pattern.test(str);
    }
    
    $("#custom-image-container").on("click", function(e) {
        e.preventDefault();
        $("#custom-image-container").hide();
        $("#image-url-dialog").show();
    });

    $("#set-image-url").on("click", function(e) {
        e.preventDefault();
        $("#custom-image-container").show();
        $("#image-url-dialog").hide();
        if (isURL($('#image-url').val())) {
            $("#custom-image-container").html('<img src="'+$('#image-url').val()+'" style="object-fit:cover; width:250px; height:250px;">');
        } else {
            $("#custom-image-container").html('<a href="#" id="custom-image-href">Set image URL</a>');
        }
    });
});

jQuery(document).ready(function($) {
    //$('#site-url').focus();

    $('#doc-date').datepicker({
        onSelect: function(dateText, inst) {
            $(this).val(dateText);
        }
    });

});

jQuery(document).ready(function($) {

    activate_site_actions_data()

    $("#btn-new-action").on("click", function() {
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'new_site_action_data',
                '_site_id': $("#site-id").val(),
            },
            success: function (response) {
                get_site_action_list($("#site-id").val());
            },
            error: function(error){
                //alert(error);
            }
        });    
    });

    function activate_site_actions_data(){
        $('[id^="btn-"]').mouseover(function() {
            $(this).css('cursor', 'pointer');
            $(this).css('color', 'red');
        });
            
        $('[id^="btn-"]').mouseout(function() {
            $(this).css('cursor', 'default');
            $(this).css('color', '');
        });

        $('[id^="btn-edit-action-"]').on( "click", function() {
            id = this.id;
            id = id.substring(16);
            window.location.replace('/wp-admin/post.php?post='+id+'&action=edit');
        });
    
        $('[id^="btn-del-action-"]').on( "click", function() {
            id = this.id;
            id = id.substring(15);
            if (window.confirm("Are you sure you want to delete this site action?")) {
                jQuery.ajax({
                    type: 'POST',
                    url: ajax_object.ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'del_site_action_data',
                        '_action_id': id,
                    },
                    success: function (response) {
                        get_site_action_list($("#site-id").val());
                    },
                    error: function(error){
                        //alert(error);
                    }
                });
            }
        });        
    }

    function get_site_action_list(id){
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            dataType: "json",
            data: {
                'action': 'get_site_action_list',
                '_site_id': id,
            },
            success: function (response) {
                for(index=0;index<50;index++) {
                    $("#site-action-list-"+index).hide();
                    $("#site-action-list-"+index).empty();
                }
                $.each(response, function (index, value) {
                    output = '';
                    output = output+'<td style="text-align:center;"><span id="btn-edit-action-'+value.action_id+'" class="dashicons dashicons-edit"></span></td>';
                    output = output+'<td>'+value.action_title+'</td>';
                    output = output+'<td>'+value.action_description+'</td>';
                    output = output+'<td style="text-align: center;">'+value.next_action_title+'</td>';
                    output = output+'<td style="text-align: center;">'+value.next_action_leadtime+'</td>';
                    output = output+'<td style="text-align: center;"><span id="btn-del-action-'+value.action_id+'" class="dashicons dashicons-trash"></span></td>';
                    $("#site-action-list-"+index).append(output);
                    $("#site-action-list-"+index).show();
                });

                activate_site_actions_data();
            },
            error: function(error){
                //alert(error);
            }
        });
    }
*/        
});

jQuery(document).ready(function($) {
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
        if (isURL($('#image-url-input').val())) {
            $("#custom-image-container").html('<img src="'+$('#image-url-input').val()+'" style="object-fit:cover; width:250px; height:250px;">');
        } else {
            $("#custom-image-container").html('<a href="#" id="custom-image-href">Set image URL</a>');
        }
    });
});

jQuery(document).ready(function($) {
    $('#site-url').focus();

    $('#document-date').datepicker({
        onSelect: function(dateText, inst) {
            $(this).val(dateText);
        }
    });

});


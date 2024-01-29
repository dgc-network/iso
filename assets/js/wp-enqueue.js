jQuery(document).ready(function($) {

    $("#site-title").on( "change", function() {
        window.location.replace("?_search="+$(this).val());
        $(this).val('');
    });


})

jQuery(document).ready(function(){
    jQuery(document).on('click', '#EventsByEvma_ShowMore a', function() {
        jQuery.ajax({
            url: 'wp-admin/admin-ajax.php',
            type: 'GET',
            data: {
                action : 'loadMore',
                offset: jQuery('#EventsByEvma_ShowMore a').data('offset')
            },
            success: function(data){
                jQuery('#EventsByEvma_EventsList').html(data);
            }
        });
        return false;
    });
});
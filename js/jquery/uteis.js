jQuery.noConflict();

jQuery(function() {
    jQuery('.simple-modal-open').click(function(){
        var target = jQuery(this).attr('href');
        jQuery(target).show()
        return false;
    });
    jQuery('.simple-modal-close').click(function(){
        var target = jQuery(this).attr('href');
        jQuery(target).hide();
        return false;
    });
});
jQuery(window).on("load", function () {
    var isCategoryPage = false;

    if (jQuery('body').hasClass('catalog-category-view')) {
        isCategoryPage = true;
    }

    if (isCategoryPage) {
        insertEdroneBeforePagination();
    }
});

function insertEdroneBeforePagination() {
    jQuery('.edrone-mm-wrapper').each(function () {
        jQuery(this).insertAfter('.toolbar-bottom .toolbar .pager');
    });
}
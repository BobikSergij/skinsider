define([
    'jquery'
], function ($) {
    $.widget('IdeaInYou.headerFixed', {


        _create: function () {
            let $headerClass = $(this.element);
            function HeaderFix() {
                if($(this).scrollTop() > 44) {
                    $headerClass.addClass('fixed');
                } else {
                    $headerClass.removeClass('fixed');
                }
            }
            new HeaderFix();
            $(window).scroll(HeaderFix);
        }
    });

    return $.IdeaInYou.headerFixed;
});

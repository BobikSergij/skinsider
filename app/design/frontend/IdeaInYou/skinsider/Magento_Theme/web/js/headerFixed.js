define([
    'jquery'
], function ($) {
    $.widget('IdeaInYou.headerFixed', {


        _create: function () {
            this.HeaderFix();
        },
        HeaderFix: function () {
            let $headerClass = $(this.element);
            $(window).scroll(function(){
                if ($(window).scrollTop() >= 80) {
                    $headerClass.addClass('fixed');
                }
                else {
                    $headerClass.removeClass('fixed');
                }
            });
        }
});

    return $.IdeaInYou.headerFixed;
});

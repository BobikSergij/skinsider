define([
    'jquery'
], function ($) {
    $.widget('IdeaInYou.headerFixed', {


        _create: function () {
            this.headerFix();
        },
        headerFix: function () {
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

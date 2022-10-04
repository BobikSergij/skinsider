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
                const $scroll = $(window).scrollTop();
                if ($scroll >= 300) {
                    $headerClass.addClass('fixed');
                }
                else if($scroll <= 300) {
                    $headerClass.removeClass('fixed');
                }
            });
        }
});

    return $.IdeaInYou.headerFixed;
});

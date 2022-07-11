define([
    'jquery'
], function ($) {
    $.widget('IdeaInYou.readMore', {


        _create: function () {
            $(this.element).on('click', function (){
                $(this).parent().toggleClass('show');
            });
        }
    });

    return $.IdeaInYou.readMore;
});

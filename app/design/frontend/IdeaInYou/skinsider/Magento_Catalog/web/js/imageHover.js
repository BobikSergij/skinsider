define([
    'jquery'
], function ($) {
    $.widget('IdeaInYou.imageHover', {


        _create: function () {
            this.imageHover();
        },
        imageHover: function () {
            const click = $(this.element);
            click.on('click', function (){
                $('.main-back').removeClass('index');
                if($(click).hasClass('index')){
                    $(this).removeClass('index');
                } else {
                    $(this).addClass('index');
                }
            });
        }
    });

    return $.IdeaInYou.imageHover;
});

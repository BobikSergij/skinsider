define([
    "jquery"
], function ($) {
    $.widget("IdeaInYou.toggleCategoryDescription", {
        _create: function () {
            this._toggle();
        },
        _toggle: function () {

            let extended = $('.category-extended');
            let readMore = this.element;


            readMore.on('click', function () {

                if (readMore.hasClass('open')) {
                    readMore.text('Read more...');
                    $('html, body').animate({scrollTop: '0px'}, 500);
                    // extended.slideUp(500);
                    extended.animate({height: 'hide'}, 500)

                } else {
                    readMore.text('Read less...');
                    // extended.slideDown(500);
                    extended.animate({height: 'show'}, 500)
                }
                readMore.toggleClass('open')

            })

        }
    })

    return $.IdeaInYou.toggleCategoryDescription;
})

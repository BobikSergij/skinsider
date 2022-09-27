define([
    'jquery'
], function ($) {
    $.widget('IdeaInYou.readMore', {


        _create: function () {
            this.clickButton();
        },
        clickButton: function () {
            const self = $(this.element),
                showContent = $('.about-content');
            self.on('click', function () {
                showContent.toggleClass('show');
            });
        }
    });

    return $.IdeaInYou.readMore;
});

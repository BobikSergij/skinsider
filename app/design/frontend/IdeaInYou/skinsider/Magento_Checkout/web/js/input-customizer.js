define([
    "jquery",
    "inputCustomizer"
], function ($) {
    $.widget("IdeaInYou.inputCustomizer", {
        _create: function () {
            let self = this;
            setTimeout( function () {
                self._customizer();
            }, 500)

        },
        _customizer: function () {
            let root = $('.field.street');
            root.find('label').css({'display': 'none'});
            root.find('input').each(function (i) {
                $(this).attr('placeholder', 'Address Line ' + (i + 1))
            })
        }
    })

    return $.IdeaInYou.inputCustomizer;
})

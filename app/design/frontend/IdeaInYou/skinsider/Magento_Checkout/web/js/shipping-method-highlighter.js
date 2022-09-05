define([
    "jquery"
], function ($) {
    $.widget("IdeaInYou.shippingMethodHighlighter", {
        _create: function () {
            this._highlight();
        },

        _highlight: function () {
            let input = $(this.element).find('.radio');

            if ($(input).prop('checked')) {
                $(input).closest('.checkout-shipping-method').addClass('chosen')
            }
        }
    })

    return $.IdeaInYou.shippingMethodHighlighter;
})

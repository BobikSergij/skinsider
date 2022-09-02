define([
    "jquery"
], function ($) {
    $.widget("IdeaInYou.shippingMethodHighlighter", {
        _create: function () {
            let self = this;
            setTimeout(function () {
                self._highlight();
            }, 6000)
        },

        _highlight: function () {
            let input = $('#checkout-step-shipping_method').find('.row').find('.radio');

            if ($(input).prop('checked')) {
                $(input).closest('.checkout-shipping-method').addClass('chosen')
            }
        }
    })

    return $.IdeaInYou.shippingMethodHighlighter;
})

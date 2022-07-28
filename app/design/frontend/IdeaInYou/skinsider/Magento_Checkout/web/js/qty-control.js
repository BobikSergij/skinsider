define([
    "jquery",
    "qtyControl"
], function ($) {
    $.widget("IdeaInYou.qtyControl", {
        _create: function () {
            this._qtyControl();
        },
        _qtyControl: function () {
            let buttonPlus = $(this.element).find('.plus'),
                buttonMinus = $(this.element).find('.minus'),
                elCurrentQty = $(this.element).find('.qty-input'),
                form = $(this.element).closest('form');

            elCurrentQty.on('blur', function () {
                form.submit();
            })

            buttonPlus.on('click', () => {
                elCurrentQty.val(parseInt(elCurrentQty.val()) + 1);

            })

            buttonMinus.on('click', () => {
                if (elCurrentQty.val() > 1) {
                    elCurrentQty.val(parseInt(elCurrentQty.val()) - 1);
                }
            })
        }
    })

    return $.IdeaInYou.qtyControl;
})

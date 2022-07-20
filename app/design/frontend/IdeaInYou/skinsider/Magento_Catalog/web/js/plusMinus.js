define([
    'jquery'
], function ($) {
    $.widget('IdeaInYou.plusMinus', {


        _create: function () {
            this.plusButton();
        },
        plusButton: function () {
            let qty = $(this.element).find('#qty'),
                minus = $(this.element).find('.qty-minus'),
                plus = $(this.element).find('.qty-plus');
            minus.on('click', function (){
                if(Number(qty.val()) > 1 ) {
                    qty.val(Number(qty.val()) - 1 );
                }
            });
            plus.on('click', function (){
                qty.val(Number(qty.val()) + 1 );
            });
        }
    });

    return $.IdeaInYou.plusMinus;
});

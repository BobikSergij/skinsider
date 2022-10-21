/**
 * @category  Extendware
 * @package   Extendware_FreeGift
 * @copyright 2020 Extendware
 */

define([
    'jquery',
    'jquery/ui',
    'Magento_Swatches/js/swatch-renderer'
], function ($) {
    'use strict';

    $.widget('mage.extendwareFreeGiftSwatchRenderer', $.mage.SwatchRenderer, {
        /**
         * Redefine the input by adapting the name and id.
         * @param config
         * @returns {string}
         * @private
         */
        _RenderFormInput: function (config) {
            let productId = this.element.parents('.product-item-details').attr('data-product-id');

            return '<input class="' + this.options.classes.attributeInput + ' super-attribute-select" ' +
                'name="products[' + productId + '][super_attribute][' + config.id + ']" ' +
                'type="text" ' +
                'value="" ' +
                'data-selector="super_attribute[' + config.id + ']" ' +
                'data-validate="{required: true}" ' +
                'aria-required="true" ' +
                'aria-invalid="false">';
        }
    });

    return $.mage.extendwareFreeGiftSwatchRenderer;
});

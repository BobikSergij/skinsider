define([
    'jquery'
], function ($) {
    'use strict';

    let mixin = {
        options: {

        },

        _create: function () {
            this._super();
        },

        /**
         * @private
         */
        _scrollToTopIfNotVisible: function () {
            if (!this._isElementOutOfViewport()) {
                // this.header[0].scrollIntoView();
            }
        },
    };

    return function (targetWidget) {
        $.widget('mage.collapsible', targetWidget, mixin);

        return $.mage.collapsible;
    };
});

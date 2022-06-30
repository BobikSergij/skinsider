define([
    'jquery'
], function ($) {
    'use strict';

    let mixin = {
        options: {
            observeHeight: false,
        },

        _create: function () {
            this._super();
            this._bindChangeHeight();
        },

        _bindChangeHeight: function () {
            if (this.options.observeHeight) {
                let container = $(this.options.container),
                    element = this.element;

                let containerOldHeight = container.height(),
                    elementOldHeight = element.height();

                this._containerHeightChange(containerOldHeight,container);
                this._elementHeightChange(elementOldHeight,element);
            }
        },

        _containerHeightChange: function(containerOldHeight,container) {
            let containerObserver = new MutationObserver( function (mutations) {
                mutations.forEach(function (mutation) {
                    let containerNewHeight = container.height();
                    if (container.length && containerOldHeight !== containerNewHeight) {
                        containerOldHeight = containerNewHeight;
                        this.element.trigger('dimensionsChanged');
                    }
                }.bind(this));
            }.bind(this));

            containerObserver.observe(container.get(0), {
                attributes: true,
                characterData: false,
                childList: true,
                subtree: true,
                attributeOldValue: false,
                characterDataOldValue: false
            });
        },

        _elementHeightChange: function(elementOldHeight,element) {
            let elementObserver = new MutationObserver( function (mutations) {
                mutations.forEach(function (mutation) {
                    let elementNewHeight = element.height();
                    if (element.length && elementOldHeight !== elementNewHeight) {
                        elementOldHeight = elementNewHeight;
                        this.element.trigger('dimensionsChanged');
                    }
                }.bind(this));
            }.bind(this));

            elementObserver.observe(element.get(0), {
                attributes: true,
                characterData: false,
                childList: true,
                subtree: true,
                attributeOldValue: false,
                characterDataOldValue: false
            });
        },
    };

    return function (targetWidget) {
        $.widget('mage.sticky', targetWidget, mixin);

        return $.mage.sticky;
    };
});

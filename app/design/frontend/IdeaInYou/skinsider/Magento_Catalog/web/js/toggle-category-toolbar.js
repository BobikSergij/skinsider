define([
    "jquery",
    "toggleCategoryToolbar"
], function ($) {
    $.widget("IdeaInYou.toggleCategoryToolbar", {
        _create: function () {
            this._toggleTools();
        },
        _toggleTools: function () {
            let trigger = $('.toolbar-trigger'),
             toolTrigger = trigger.closest('.widget.block'),
             filtertrigger = $('.filter-toggler'),
             toolbar = $('.toolbar-products'),
             filter = $('#layered-filter-block');

            toolTrigger.on('click', function () {
                toolbar.css({display: 'block'})
                filter.css({display: 'none'})
                toolTrigger.addClass('clicked');
                filtertrigger.removeClass('clicked');
            })

            filtertrigger.on('click', function () {
                filter.css({display: 'block'})
                toolbar.css({display: 'none'})
                filtertrigger.addClass('clicked');
                toolTrigger.removeClass('clicked');
            })

        }
    })

    return $.IdeaInYou.toggleCategoryToolbar;
})

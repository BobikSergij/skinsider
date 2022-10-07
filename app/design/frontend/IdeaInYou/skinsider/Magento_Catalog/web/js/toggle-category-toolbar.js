define([
    "jquery"
], function ($) {
    $.widget("IdeaInYou.toggleCategoryToolbar", {
        _create: function () {
            this._toggleTools();
        },
        _toggleTools: function () {
            let trigger = $('.toolbar-trigger'),
             toolTrigger = trigger.closest('.widget.block.block-sort-by'),
             filterTrigger = $('.filter-toggler'),
             toolbar = $('.toolbar-products'),
             filter = $('#layered-filter-block');

            toolTrigger.on('click', function () {
                toolbar.css({display: 'block'})
                filter.css({display: 'none'})
                toolTrigger.addClass('clicked');
                filterTrigger.removeClass('clicked');
            })

            filterTrigger.on('click', function () {
                filter.css({display: 'block'})
                toolbar.css({display: 'none'})
                filterTrigger.addClass('clicked');
                toolTrigger.removeClass('clicked');
            })

        }
    })

    return $.IdeaInYou.toggleCategoryToolbar;
})

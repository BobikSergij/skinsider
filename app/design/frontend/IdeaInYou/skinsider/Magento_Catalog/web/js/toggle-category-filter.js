define([
    "jquery",
    "toggleCategoryFilter"
], function ($) {
    $.widget("IdeaInYou.toggleCategoryFilter", {
        _create: function () {
            this._toggleFilter();
        },
        _toggleFilter: function () {
            let opener = $('#layered-filter-block'),
                refineByBlock = $('.refine-by-block'),
                showHideToggler = $('.show-hide-toggle')


            $(window).width() < 768 ? refineByBlock.text('Browse by Brand, Product Types & more') : showHideToggler.css({display: 'none'});


            opener.on('click', function () {
                if ($(window).width() < 768) {
                    showHideToggler.toggleClass('filter-open');
                    showHideToggler.hasClass('filter-open') ? showHideToggler.text('Hide Filters') : showHideToggler.text('Show Filters');
                }
            })
        }
    })

    return $.IdeaInYou.toggleCategoryFilter;
})

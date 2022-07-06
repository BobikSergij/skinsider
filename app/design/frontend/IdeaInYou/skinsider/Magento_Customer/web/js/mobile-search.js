define(
    [
        'jquery',
        'mage/translate',
        'matchMedia'
    ],
    function ($, $t,mediaCheck) {
        'use strict';

        $.widget(
            'IdeaInYou.mobileSearch',
            {
                options: {

                },
                /**
                 * Init
                 *
                 * @private
                 */
                _create: function () {
                    let self = this;

                    mediaCheck(
                        {
                            media: '(max-width: 767px)',
                            entry: function () {
                                self._moveSearch();
                            },
                            exit: function () {
                            }
                        }
                    )
                },

                _moveSearch: function () {
                    let search = $('.block.block-search'),
                        mobileNav = $('.nav-sections'),
                        menu = $(mobileNav).find('#store\\.menu');

                    menu.prepend(search);
                }
            }
        );
        return $.IdeaInYou.mobileSearch;
    }
);

define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'mage/template'
], function ($, $t, modal, mageTemplate) {
    'use strict';

    $.widget('IdeaInYou.myAccountPopup', {
        options: {
            isLoggedIn: {},
            accountlink: {},
            signOutLink: {},
            modalOptions: {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: [],
                modalVisibleClass: '_show account-popup',
            }

        },

        /**
         * Init
         * @private
         */
        _create: function () {
            $(document).on('click', '.authorization-link', (function (e) {
                if ( this.options.isLoggedIn ) {
                    this._initModal();
                }
            }).bind(this));
        },
        _initModal: function () {
            let modalOptions = this.options.modalOptions,
                template = '<div class="logged-account-popup"> ' +
                '<a class="dashboard" href="'+ this.options.accountlink +'"><span>Dashboard</span></a>'+
                '<a class="sign-out" href="'+ this.options.signOutLink +'"><span>Sing Out</span></a>' +
                '</div>';

            this.modalWindow = $('<div class="popup-wrapper"><div/>').html(template);

            $('body').append(this.modalWindow);

            this.modalWindow.modal(modalOptions);
            this.modalWindow.modal('openModal');

            let closeBtn = $('.action-close');
            closeBtn.click(function(){
                let overlay =  $('.modals-wrapper').find('.modals-overlay');
                overlay.remove();
                $('body').removeClass('_has-modal');
            });
        }
    });

    return $.IdeaInYou.myAccountPopup;
})

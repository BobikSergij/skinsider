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
            accountLink: {},
            ordersLink: {},
            wishListLink: {},
            addressBookLink: {},
            accountInfoLink: {},
            paymentMethodsLink: {},
            billingLink: {},
            productReviewsLink: {},
            newsletterSubscrLink: {},
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
                if (this.options.isLoggedIn) {
                    this._initModal();
                }
            }).bind(this));
        },
        _initModal: function () {
            let modalOptions = this.options.modalOptions,
                template = '<div class="logged-account-popup"> ' +
                    '<a class="dashboard" href="' + this.options.accountLink + '"><span>Dashboard</span></a>' +
                    '<a class="orders-link" href="' + this.options.ordersLink + '"><span>Orders</span></a>' +
                    '<a class="wishlist-link" href="' + this.options.wishListLink + '"><span>Wishlist</span></a>' +
                    '<a class="address-book-link" href="' + this.options.addressBookLink + '"><span>Address Book</span></a>' +
                    '<a class="account-info-link" href="' + this.options.accountInfoLink + '"><span>Account Info</span></a>' +
                    '<a class="payment-methods-link" href="' + this.options.paymentMethodsLink + '"><span>Payment Methods</span></a>' +
                    '<a class="billing-link" href="' + this.options.billingLink + '"><span>Billing Agreements</span></a>' +
                    '<a class="product-reviews-link" href="' + this.options.productReviewsLink + '"><span>Reviews</span></a>' +
                    '<a class="newsletter-link" href="' + this.options.newsletterSubscrLink + '"><span>Newsletter Subscriptions</span></a>' +
                    '<a class="sign-out" href="' + this.options.signOutLink + '"><span>Sing Out</span></a>' +
                    '</div>';

            this.modalWindow = $('<div class="popup-wrapper"><div/>').html(template);

            $('body').append(this.modalWindow);

            this.modalWindow.modal(modalOptions);
            this.modalWindow.modal('openModal');

            let closeBtn = $('.action-close');
            closeBtn.click(function () {
                let overlay = $('.modals-wrapper').find('.modals-overlay');
                overlay.remove();
                $('body').removeClass('_has-modal');
            });
        }
    });

    return $.IdeaInYou.myAccountPopup;
})

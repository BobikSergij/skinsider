define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Amasty_MWishlist/js/edit-name-popup',
], function ($, $t, modal, mageTemplate) {
    'use strict';

    $.widget('IdeaInYou.editWishlistNamePopup', {
        options: {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            modalVisibleClass: '_show',
            updateWishlistUrl: {},
            wishlistId: {}
        },

        _create: function () {
            this._initModal();
        },

        _initModal: function () {

            let self = this;
            let modalOptions = this.options,
                template = '<div class="edit-name-' + self.options.wishlistId + '" > ' +
                    '<div class="modal-title">Edit wishlist name</div>' + '<div class="new-name-container">' + '<label for="new-name">Wish List Name:</label>' +
                    '<input name="new-name" class="new-name">' + '</div>' + '<button class="amend-wishlist-name" type="button">Save Wishlist</button>' +
                    '</div>';

            let shareIndicator = $('.' + self.options.wishlistId);
            if (localStorage.getItem('shared-wishlist-id-' + self.options.wishlistId)) {
                $(shareIndicator).text('Yes')
            } else {
                $(shareIndicator).text('No')
            }


            this.element.on('click', function () {

                this.modalWindow = $('<div class="popup-edit-wishlist-wrapper"><div/>').html(template);
                let buttonSaveChanges = $(this.modalWindow).find('.amend-wishlist-name'),
                    toBeShared = $(this.modalWindow).find('.to-be-shared'),
                    newWishlistInput = $(this.modalWindow).find('.new-name'),
                    formKey = $.mage.cookies.get('form_key');

                $('body').append(this.modalWindow);

                if (localStorage.getItem('shared-wishlist-id-' + self.options.wishlistId)) {
                    toBeShared.prop('checked', true);
                } else {
                    toBeShared.prop('checked', false);
                }

                this.modalWindow.modal(modalOptions);
                this.modalWindow.modal('openModal');

                let closeBtn = $('.action-close');
                closeBtn.click(function () {
                    let overlay = $('.modals-wrapper').find('.modals-overlay');
                    overlay.remove();
                    $('body').removeClass('_has-modal');
                });

                buttonSaveChanges.on('click', function () {

                    let newWishlistName = newWishlistInput.val();

                    $.ajax({
                        type: 'POST',
                        url: self.options.updateWishlistUrl,
                        data: {
                            'wishlist[name]': newWishlistName,
                            'form_key': formKey
                        },
                        success: function () {
                            location.reload();
                        }
                    });

                    if (toBeShared.is(":checked")) {
                        localStorage.setItem('shared-wishlist-id-' + self.options.wishlistId, 'true')
                    } else {
                        if (localStorage.getItem('shared-wishlist-id-' + self.options.wishlistId)) {
                            localStorage.removeItem('shared-wishlist-id-' + self.options.wishlistId)
                        }
                    }
                })

            })


        },
    });

    return $.IdeaInYou.editWishlistNamePopup;
})

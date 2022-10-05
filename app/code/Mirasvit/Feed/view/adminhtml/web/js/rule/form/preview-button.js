define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/components/button',
    'uiRegistry',
], function ($, _, Button, Registry) {
    return Button.extend({
        defaults: {
            title: 'Preview Products',
            isPreviewRendered: false,
        },

        initialize: function () {
            this._super();
        },

        action: function () {
            var listing = Registry.get('mst_feed_rule_form.mst_feed_rule_form.general.conditions_serialized.preview_listing');

            if (!this.isPreviewRendered && !listing.isRendered) {
                listing.render();
                this.isPreviewRendered = true;
            } else {
                listing.updateData(listing.params);
            }
        },
    });
});

define([
    'jquery',
    'Magento_Ui/js/form/components/insert-listing',
    'uiRegistry',
    'underscore'
], function ($, InsertListing, Registry, _) {
    'use strict';

    return InsertListing.extend({
        defaults: {
            listens: {
                loading: 'updateButtonLabel',
            }
        },

        updateButtonLabel: function (isLoading) {
            var btn        = $('button.mst-feed_rule_preview-button');
            var loader     = $('.mst_feed_rule_form_mst_feed_rule_form_general_conditions_serialized_preview_listing .admin__data-grid-loading-mask');

            if (isLoading) {
                btn.disabled = true;
                loader.show();
                $('span', btn).text('Loading...');
            } else {
                btn.disabled = false;
                loader.hide();
                $('span', btn).text('Preview Products');
            }
        },

        updateData: function (params) {
            params = this.getParams(params);

            if (params) {
                this.updateDataSource(params)
                return this._super(params);
            }

            return this;
        },

        render: function (params) {
            params = this.getParams(params);

            this.updateDataSource(params);

            return this._super(params);
        },

        getParams: function (params) {
            var rules = {}

            // cleanup condition params
            for (name in params) {
                if (name.indexOf('rule[') >= 0) {
                    delete params[name];
                }
            }

            $('#rule_conditions_fieldset').serializeArray().forEach(function(item) {
                rules[item['name']] = item['value'];
            }.bind(this));

            params = _.extend(params || {}, rules);

            return params;
        },

        updateDataSource: function (params) {
            var dataSource = Registry.get("mst_feed_rule_product_listing.mst_feed_rule_product_listing_data_source");

            if (!dataSource) {
                console.log('no dataSource yet');
                return;
            }

            params = _.extend(dataSource.params || {}, params);

            dataSource.params = params;
        }
    });
});

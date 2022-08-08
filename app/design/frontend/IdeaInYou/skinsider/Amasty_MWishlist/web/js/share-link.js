define([
    'jquery',
    'shareLink'

], function ($) {
    'use strict';

    $.widget('IdeaInYou.shareLink', {

        _create: function () {
            this._initLink();
        },

        _initLink: function () {

        }
    });

    return $.IdeaInYou.shareLink;
})

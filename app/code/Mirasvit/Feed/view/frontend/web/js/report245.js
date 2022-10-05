define([
    'jquery',
    'Mirasvit_Feed/js/report-abstract',
    'js-cookie/js.cookie'
], function ($, reportAbstract, cookie) {
    'use strict';

    $.widget('mirasvit.feedReport245', reportAbstract, {
        getCookie: function (name) {
            return cookie.get(name);
        },

        setCookie: function (name, value, options) {
            cookie.set(name, value, options);
        },
    });

    return $.mirasvit.feedReport245;
});
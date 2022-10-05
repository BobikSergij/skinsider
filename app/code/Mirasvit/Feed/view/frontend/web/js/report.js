define([
    'jquery',
    'Mirasvit_Feed/js/report-abstract',
    'jquery/jquery.cookie'
], function ($, reportAbstract) {
    'use strict';

    $.widget('mirasvit.feedReport', reportAbstract, {
        getCookie: function (name) {
            return $.cookie(name);
        },

        setCookie: function (name, value, options) {
            $.cookie(name, value, options);
        },        
    });

    return $.mirasvit.feedReport;
});

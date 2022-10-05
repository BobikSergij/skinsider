define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mirasvit.feedReportAbstract', {
        options: {
            url: null
        },

        _create: function () {
            var feedId = this.getUrlParam('ff');
            var product = this.getUrlParam('fp');
            var currentDate = new Date();
            var session = this.getCookie('feed_session');

            if (!session) {
                session = '' + Math.floor(currentDate.getTime() / 1000) + Math.floor(Math.random() * 10000001);
            }

            if (session && feedId > 0 && product > 0) {
                this.setCookie('feed_session', session, {expires: 365, path: '/'});
                this.setCookie('feed_id', feedId, {expires: 365, path: '/'});

                var url = this.options.url + '?rnd=' + Math.floor(Math.random() * 10000001) + "&feed=" + feedId + "&session=" + session + "&product=" + product;
                $.ajax(url);
            }
        },
        
        getUrlParam: function (name) {
            let results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
            if (results === null) {
                return '';
            } else {
                return results[1] || 0;
            }
        },

        getCookie: function (name) {
            console.log('error: mirasvit.feedReportAbstract.getCookie() must be overridden');
            return '';
        },

        setCookie: function (name, value, options) {
            console.log('error: mirasvit.feedReportAbstract.setCookie() must be overridden');
        },
    });

    return $.mirasvit.feedReportAbstract;
});
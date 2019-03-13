var $ = require('jquery');
var _ = require('lodash');
var _methodsToRequest = {
    get: 'GET',
    put: 'PUT',
    post: 'POST',
    'delete': 'DELETE'
};

var _handleError = function (xhr, status, errorStr) {
    console.log(status + ':' + errorStr);
};

module.exports = {
    // serverUrl: 'http://dcpss.izaodao.com',
    buildURL: function (module, action, params) {
        params = params || {};
        var parts = [];
        var url;
        // parts.push(this.serverUrl);

        if (module) {
            parts.push(module);
        }

        if (action) {
            parts.push(action);
        }

        url = '/' + parts.join('/');
        return url;
    },
    call: function (method, url, data, callbacks, options) {
        var request, type = _methodsToRequest[method], self = this;
        var params = {
            async: true,
            type: type,
            dataType: 'html',
            headers: {},
            timeout: 10 * 1000//10s
            // contentType: 'application/json'
        };
        options = options || {};
        callbacks = callbacks || {};
        if (data) {
            params.data = data;
            // params.data = JSON.stringify(data);
        }
        params.url = url;
        params.complete = callbacks.complete || {};
        if (options.dataType) {
            params.dataType = options.dataType;
        }
        if (options.async) {
            params.async = options.async;
        }

        this.xhr = $.ajax(params).done(function (response, textStatus, xhr) {
            var ct = xhr.getResponseHeader('content-type') || '';
            if (ct.indexOf('json') > -1 && params.dataType !== 'json') {
                response = JSON.parse(response);
            }
            if (response.code === 401) { //未登录
                window.location.href = response.data.url;
            } else if (callbacks.success) {
                callbacks.success(response, textStatus, xhr);
            }
        }).fail(function (xhr, textStatus, errorThrown) {
            _handleError(xhr, textStatus, errorThrown);
        });
        // request = new HttpRequest(_.extend(params, options), this.debug);
    },
    callJson: function (method, url, data, callbacks, options) {
        options = options || {};
        options.dataType = 'json';
        this.call(method, url, data, callbacks, options);
    },
    getMonth: function (date) {
        var month = date.getMonth() + 1;
        if (month < 10) {
            month = '0' + month;
        }
        return month + '';
    },
    getDay: function (date) {
        var day = date.getDate();
        if (day < 10) {
            day = '0' + day;
        }
        return day;
    },
    /**
     * 格式化日期时间
     * 例如：2000年03月05日 08：04
     * @param timestamp
     * @returns {string}
     */
    formatTimestamp: function (timestamp) {
        var date = new Date(timestamp);
        var year = date.getFullYear();
        var month = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1);
        var day = date.getDate();
        day = day < 10 ? '0' + day : day;
        var hour = date.getHours();
        hour = hour < 10 ? '0' + hour : hour;
        var minute = date.getMinutes();
        minute = minute < 10 ? '0' + minute : minute;

        return year + '年' + month + '月' + day + '日 ' + hour + ':' + minute;
    },
    /**
     * 格式化日期YYYY-MM-DD hh:mm:ss
     * @param date
     * @param format
     * @returns {*}
     */
    formatDate: function (date, format) {
        var o = {
            'M+': date.getMonth() + 1, //month
            'D+': date.getDate(), //day
            'h+': date.getHours(), //hour
            'm+': date.getMinutes(), //minute
            's+': date.getSeconds(), //second
            'q+': Math.floor((date.getMonth() + 3) / 3), //quarter
            S: date.getMilliseconds() //millisecond
        };
        if (/(Y+)/.test(format)) {
            format = format.replace(RegExp.$1, (date.getFullYear() + '').substr(4 - RegExp.$1.length));
        }
        _.forEach(o, function (v, k) {
            if (new RegExp('(' + k + ')').test(format)) {
                format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? v : ('00' + v).substr(('' + v).length));
            }
        });
        return format;
    },
    /**
     * 生成图片地址
     * @param name
     * @param module
     * @returns {string}
     */
    imgUrl: function (name, module) {
        module = module || '';
        if (module.length === 0) {
            return '/static/image/public/' + name;
        } else {
            return '/static/image/' + module + '/' + name;
        }
    },
    /**
     * 验证手机号
     * @param number
     * @returns {boolean}
     */
    validMobile: function (number) {
        return !!number.match(/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/);
    },
    /**
     * 兼容旧浏览器placeholder
     * @param element
     */
    enablePlaceholder: function (element) {
        var text = element.attr('placeholder');
        if (element.val() === '') {
            element.val(text).addClass('color_ccc');
        } else {
            element.addClass('color_333');
        }
        element.focus(function () {
            if (element.val() === text) {
                element.val('').removeClass('color_ccc').addClass('color_333');
            }
        }).blur(function () {
            if (element.val() === '') {
                element.val(text).removeClass('color_333').addClass('color_ccc');
            }
        });
    }
};

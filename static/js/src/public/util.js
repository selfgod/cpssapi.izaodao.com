var $ = require('jquery');
var _ = require('lodash');
var loading = require('./template/loading.tpl');
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
    xhr: {},
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
        if (options.xhrFields) {
            params.xhrFields = options.xhrFields;
        }
        if (typeof options.async !== 'undefined') {
            params.async = options.async;
        }

        this.xhr = $.ajax(params).done(function (response, textStatus, xhr) {
            var ct = xhr.getResponseHeader('content-type') || '';
            if (ct.indexOf('json') > -1 && params.dataType !== 'json') {
                response = JSON.parse(response);
            }
            if (response.code === 401) { //未登录
                if (callbacks.login) {
                    callbacks.login();
                } else {
                    window.location.href = response.data.url;
                }
            } else if (callbacks.success) {
                callbacks.success(response, textStatus, xhr);
            }
        }).fail(function (xhr, textStatus, errorThrown) {
            _handleError(xhr, textStatus, errorThrown);
            if (callbacks.error) {
                callbacks.error(xhr, textStatus);
            }
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
     * 学习系统主题页面dom ID
     * @returns {string}
     */
    lsParentId: function () {
        return '#schedule_body';
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
     * 获取报到倒计时字符串
     * @param end_time
     * @param now
     * @returns {*}
     */
    getCountDownStr: function (end_time, now) {
        var timeDiff = end_time - now - 60 * 30; //倒计时总秒数量
        return this.timeRemainStr(timeDiff);
    },
    /**
     * 剩余秒数转化为时间字符串
     * @param timeDiff
     */
    timeRemainStr: function (timeDiff) {
        var day = 0, hour = 0, min = 0, sec = 0, str = '';
        if (timeDiff > 0) {
            day = Math.floor(timeDiff / (60 * 60 * 24));
            hour = Math.floor(timeDiff / (60 * 60)) - (day * 24);
            min = Math.floor(timeDiff / 60) - (day * 24 * 60) - (hour * 60);
            sec = Math.floor(timeDiff) - (day * 24 * 60 * 60) - (hour * 60 * 60) - (min * 60);

            if (min > 0 && min < 10) {
                min = '0' + min;
            }
            if (sec >= 0 && sec < 10) {
                sec = '0' + sec;
            }
            day && (str += day + '天');
            hour && (str += hour + '小时');
            min && (str += min + '分');
            str += sec + '秒';
            return str;
        } else {
            return 0;
        }
    },
    /**
     * 加载loading图
     * @param method
     * @param url
     * @param data
     * @param callbacks
     * @param options
     * @param selector
     */
    callWithLoading: function (method, url, data, callbacks, options, selector) {
        options = options || {};
        this.showLoading(selector, options.loading);
        this.call(method, url, data, {
            success: function (ret) {
                if (callbacks.success) {
                    setTimeout(function () {
                        callbacks.success(ret);
                    }, 150);
                }
            }
        }, options);
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
     * 隐藏加载图
     * @param selector
     * @param callback
     */
    hideLoading: function (selector, callback) {
        setTimeout(function () {
            callback();
        }, 150);
    },
    /**
     * 显示加载图
     * @param selector
     * @param options
     */
    showLoading: function (selector, options) {
        var style = '';
        options = options || {};
        if (options.top) {
            style += 'margin-top:' + options.top + 'px;';
        }
        $(selector).html(loading({style: style}));
    },
    /**
     * 验证手机号
     * @param number
     * @returns {boolean}
     */
    validMobile: function (number) {
        return !!number.match(/^1[0-9]{10}$/);
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
    },
    /**
     * 检测是否是mac或linux系统
     * @returns {boolean}
     */
    isMacOrLinux: function () {
        var p = navigator.platform;
        var isMac = p.indexOf('Mac') === 0;
        var isLinux = (p === 'X11') || (p.indexOf('Linux') === 0);
        return isMac || isLinux;
    },
    /**
     * 判断浏览器是否是ie8及以下
     * @returns {boolean}
     */
    isOldBrowser: function (v) {
        var def_version = v || 8;
        if ((navigator.userAgent.indexOf('MSIE') >= 0)
            && (navigator.userAgent.indexOf('Opera') < 0)) {
            var b_version = navigator.appVersion;
            var version = b_version.split(';');
            if (version.length > 1) {
                var trim_Version = parseInt(version[1].replace(/[ ]/g, '').replace(/MSIE/g, ''), 10);
                if (trim_Version > def_version) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    },
    /**
     * 滚动到屏幕顶端
     */
    scrollTop: function () {
        $('body,html').animate({scrollTop: 0});
    },
    /**
     * 换算成东8区的当前时间戳
     */
    now: function () {
        var date = new Date();
        var localTime = date.getTime();
        var localOffset = date.getTimezoneOffset() * 60000;//获得当地时间偏移的毫秒数
        var utc = localTime + localOffset;//utc即GMT时间
        var china = utc + (3600000 * 8);
        return (new Date(china)).valueOf();
    },
    /**
     * 启动zdtalk
     */
    launchZDtalk: function (element, download, url) {
        var iframe;
        var zdtalkIframe = 'zdtalk_iframe';
        if ((iframe = document.getElementById(zdtalkIframe)) === null) {
            iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.id = zdtalkIframe;
            document.body.appendChild(iframe);
        }
        iframe.src = url;
    },
    /**
     * 阻止浏览器后退
     */
    preventBack: function () {
        if (!this.isOldBrowser(9)) {
            history.pushState(null, null, document.URL);
            window.addEventListener('popstate', function () {
                history.pushState(null, null, document.URL);
            });
        }
    }
};

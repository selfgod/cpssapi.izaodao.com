module.exports = {
    /**
     * 设置cookie
     * @param name
     * @param value
     * @param expires
     * @param path
     * @param domain
     * @param secure
     */
    set: function (name, value, expires, path, domain, secure) {
        var cookieArr = [];
        path = path || '/';
        domain = domain || '.izaodao.com';
        cookieArr.push(name + '=' + encodeURIComponent(value));
        if (expires) {
            cookieArr.push('expires=' + expires.toUTCString());
        }
        cookieArr.push('path=' + path);
        cookieArr.push('domain=' + domain);
        if (secure) {
            cookieArr.push('secure');
        }
        document.cookie = cookieArr.join(';');
    },
    /**
     * 获取cookie内容
     * @param name
     * @returns {*}
     */
    get: function (name) {
        var search = name + '=';
        if (document.cookie.length > 0) {
            var offset = document.cookie.indexOf(search);
            if (offset !== -1) {
                offset += search.length;
                var end = document.cookie.indexOf(';', offset);
                if (end === -1) {
                    end = document.cookie.length;
                }
                return decodeURIComponent(document.cookie.substring(offset, end));
            }
        }
        return '';
    },
    /**
     * 清除cookie，将有效期设置为已经过期的时间
     * @param name
     * @param path
     * @param domain
     */
    remove: function (name, path, domain) {
        if (this.get(name) !== '') {
            var cookieArr = [];
            path = path || '/';
            domain = domain || '.izaodao.com';
            cookieArr.push(name + '=');
            cookieArr.push('path=' + path);
            cookieArr.push('domain=' + domain);
            cookieArr.push('expires=Thu, 01-Jan-70 00:00:01 GMT');
            document.cookie = cookieArr.join(';');
        }
    }
};

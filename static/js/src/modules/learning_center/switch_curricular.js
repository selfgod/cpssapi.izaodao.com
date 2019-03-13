require('./css/teb_5.1.0.css');
var $ = require('jquery');
var _ = require('lodash');
var _options = require('./options');
module.exports = {
    bind: function (target, callback) {
        $('body').on('click', target + ' ul li', function () {
            if (!$(this).hasClass('off')) {
                var dataHash = $(this).attr('data-hash');
                if (_.indexOf(_options.curricularMap, dataHash) !== -1) {
                    var xHash = $.hash.getHash();
                    if (xHash.indexOf('/') > 0) {
                        var pHash = xHash.split('/');
                        $.hash.go(pHash[0] + '/' + dataHash);
                        if (callback) {
                            callback(dataHash);
                        }
                    }
                }
            }
        });
    },
    bindCurricularType: function (target, callback) {
        $('body').on('click', target + ' .my_curricular_type p', function () {
            if (!$(this).hasClass('current_v5')) {
                $(this).addClass('current_v5').siblings().removeClass('current_v5');
                if (callback) {
                    callback();
                }
            }
        });
    }
};

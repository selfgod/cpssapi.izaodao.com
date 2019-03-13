var $ = require('jquery');
var async = require('async');
var utils = require('public/util.js');
require('public/ga');
var pagination = require('../../public/pagination');

var category = $('#category').val();
var url = utils.buildURL('', 'purchased/curricular/' + category);
async.parallel({
    total: function (callback) {
        var params = {type: 'total'};
        utils.call('get', url, params, {
            success: function (data) {
                callback(null, data);
            }
        });
    },
    detail: function (callback) {
        var params = {type: 'detail'};
        utils.call('get', url, params, {
            success: function (data) {
                callback(null, data);
            }
        });
    }
}, function (err, results) {
    // $('#p_detail_container').prepend(results.detail).prepend(results.total);
    $('#p_detail_container').append(results.total).append(results.detail);
});

pagination.bind('purchased_detail_audit', function (page) {
    var params = {page: page};
    var audituUrl = url + '/audit';
    utils.callWithLoading('get', audituUrl, params, {
        success: function (data) {
            $('#audit_list').html(data);
        }
    }, {}, '#audit_list');
}, 'cpss_page_current');

var $ = require('jquery');
var async = require('async');
var utils = require('public/util');
var popup = require('public/popup');
var activating = require('./template/activating.tpl');
var layerPop = require('public/layer');
var commonPrompt = require('public/template/common_prompt.hbs');
require('public/ga');

async.parallel({
    summary: function (callback) {
        var url = utils.buildURL('', 'purchased/summary');
        utils.call('get', url, {}, {
            success: function (data) {
                callback(null, data);
            }
        });
    },
    detail: function (callback) {
        var url = utils.buildURL('', 'purchased/list');
        utils.call('get', url, {}, {
            success: function (data) {
                callback(null, data);
            }
        });
    }
}, function (err, results) {
    $('#container').prepend(results.detail).prepend(results.summary);
});

var showActivateSucceed = function () {
    var content = commonPrompt({
        title: '激活成功！',
        sub_title: '你已经成功激活，现在可以开始学习啦'
    });
    layerPop.show(content, {
        btn: ['学习中心'],
        yes: function (index) {
            layerPop.layer.close(index);
            window.location.href = '/study/#/myCourse';
            return false;
        },
        cancel: function () {
            window.location.reload();
        }
    });
};
//点击次数
$('#container').on('click', '.history', function () {
    var type = $(this).data('type');
    window.location.href = '/purchased/curricular/' + type;
});

//点击激活按钮
$('#container').on('click', '.activate', function () {
    var activation = require('public/activation');
    var element = $(this);
    var lastDay = element.data('last');
    var date = new Date();
    var today = date.getFullYear() + '/' + utils.getMonth(date) + '/' + utils.getDay(date);
    var content = activating({
        name: element.data('name'),
        today: today,
        lastDay: lastDay,
        image: popup.imgUrl('ff_xihuan.png')
    });
    popup.show(content, {
        title: '提示',
        button: [
            {name: '暂不激活'},
            {
                name: '确定激活',
                focus: true,
                callback: function () {
                    var url = utils.buildURL('', 'goods/activate');
                    var params = {
                        goods_id: element.data('id')
                    };
                    utils.call('post', url, params, {
                        success: function (data) {
                            if (data.code === 200) {
                                showActivateSucceed();
                            } else if (data.code === 284) {
                                activation.notAllowActivateTimePopup(data.data.allow_activate_time);
                            } else {
                                alert('激活失败');
                            }
                        },
                        error: function () {
                            alert('激活失败');
                        }
                    }, {dataType: 'json'});
                }
            }
        ]
    });
});

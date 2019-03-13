var $ = require('jquery');
var _ = require('lodash');
var popup = require('./popup');
var checkInForm = require('./template/checkin_form.tpl');
var utils = require('./util');
var config = require('./config');

var _showPopup = function (data, callback) {
    var score = data.score;
    var layerPop = require('public/layer');
    // var commonPrompt = require('public/template/common_prompt.hbs');
    var commonPromptNew = require('public/template/common_prompt_new.hbs');
    var newScore = 0;
    var tip = '';
    if (window.cpss) {
        newScore = parseInt(window.cpss.user.score, 10) + score;
        //更新头部学分
        $('#ava_score').html('<i class="credit_icon_v400"></i>' + newScore);
    }
    if ((score > 0 || data.over_limit) && !data.no_reward) {
        if (data.over_limit) {
            tip = '今日学分已达上限';
        } else {
            tip = '获得' + score + '学分奖励';

            // tip = '<b class="color_fc7e03">+' + score + ' 学分</b>';
        }
    }
    //暂时取消该弹出层
    /*    var unlockPopup = function () {
            if (_.isFunction(callback)) {
                setTimeout(function () {
                    if (data.auto_unlock === 1) {
                        var unLockContent = commonPrompt({
                            title: '恭喜你！',
                            sub_title: '当前阶段完成率已达80%，获得1个奖杯徽章'
                        });
                        layerPop.show(unLockContent, {
                            btn: ['确 定']
                        });
                    }
                }, 1000);
                callback(data);
            }
        };*/
    var operation = function () {
        if (_.isFunction(callback)) {
            callback(data);
        }
    };
    var string = '';
    if (tip) {
        string = '报到成功！' + tip;
    } else {
        string = '报到成功！';
    }
    var params = {
        title: string,
        first_report: (data.report_num === '1') ? 1 : 0,
        report_num: data.report_num,
        qr_code: decodeURIComponent(data.qr_code),
        full_attendance: Number(data.full_attendance)
    };
    var but = {
        area: ['460px', '512px'],
        cancel: function () {
            operation();
        }
    };
    if (data.live_report === 1) {
        layerPop.show(commonPromptNew(params), but);
    } else {
        params.title = '报到成功';
        layerPop.show(commonPromptNew(params), but);
    }
};

var _submit = function (data, callback) {
    data = data || {};
    var params;
    // var url = utils.buildURL('learningsystem', 'checkin');
    var url = data.url;
    params = {
        lessonId: data.lessonId,
        interactive: data.interactive,
        learn: data.learn,
        teach: data.teacher,
        comment: data.comment
    };
    utils.call('post', url, params, {
        success: function (response) {
            if (response.ret === 200) {
                _showPopup(response.data.data, callback);
            }
        }
    }, {dataType: 'json', xhrFields: {withCredentials: true}});
};

var _bind = function () {
    var self = this;
    var $body = $('body');
    $body.on('click', '.formMenu_v422_list li', function () {
        var element = $(this);
        var radio = element.find('i');
        if (radio.length > 0) {
            radio.addClass('check_v422').removeClass('radio_v422');
            element.siblings().find('i').removeClass('check_v422').addClass('radio_v422');
        }
    });

    $body.on('click', '#commit_checkin', function () {
        var result = {}, invalid = false;
        var isSelectedInterac = $('ul.interactive');
        var isSelectedLearn = $('ul.learn');
        var isSelectedTeacher = $('ul.teacher');
        var form = {
            interactive: isSelectedInterac,
            learn: isSelectedLearn,
            teacher: isSelectedTeacher
        };

        $('.notice').each(function (index, element) {
            $(element).hide();
        });

        _.find(form, function (element, key) {
            var checked = element.find('.check_v422');
            if (checked.length > 0) {
                result[key] = checked.attr('value');
                return false;
            } else {
                element.siblings('p').show();
                invalid = true;
                return true;
            }
        });
        //截取255
        result.comment = $('#checkin_text').val().slice(0, 255);
        if (!invalid) {
            self.formDialog.close();
            _submit(_.assign(self.params, result), self.callback);
        }
    });
};

module.exports = {
    isInit: false,
    params: {},
    formDialog: {},
    callback: null,
    show: function (params, callback) {
        this.params = params || {};
        this.callback = callback;
        if (!this.isInit) {
            this.isInit = true;
            _.bind(_bind, this)();
        }
        var content = checkInForm();
        this.formDialog = popup.show(content, {title: '报到'});
    },
};

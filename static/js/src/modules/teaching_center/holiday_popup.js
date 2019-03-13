var popup = require('public/popup');
var commonPrompt = require('public/template/common_prompt.hbs');
var layerPop = require('public/layer');
module.exports = {
    eleHolidayVal: {
        leave: '请假',
        suspend: '休学'
    },
    popupClose: function () {
        layerPop.layer.closeAll();
    },
    holidayDoingPopup: function (clc, ele) {
        var self = this;
        layerPop.lineShow(commonPrompt({
            title: '你正在' + self.eleHolidayVal[clc] + '中，无法申请' + self.eleHolidayVal[ele] + '~'
        }), {
            btn: ['知道了']
        });
    },
    holidayTimePopup: function (ele) {
        var popval = '';
        if (ele === 'leave') {
            popval = '请假时间不能超过15天哦~';
        }
        if (ele === 'suspend') {
            popval = '休学时间不得少于15天，不得大于180天';
        }
        layerPop.lineShow(commonPrompt({
            title: popval
        }), {
            btn: ['知道了']
        });
    },
    holidaySuccessPopup: function (title) {
        layerPop.lineShow(commonPrompt({
            title: title
        }), {
            btn: ['知道了'],
            yes: function (index) {
                layerPop.layer.close(index);
                location.reload();
            },
            cancel: function (index) {
                layerPop.layer.close(index);
                location.reload();
            }
        });
    },
    holidayFailPopup: function (title) {
        layerPop.lineShow(commonPrompt({
            title: title
        }), {
            btn: ['知道了'],
            yes: function (index) {
                layerPop.layer.close(index);
                location.reload();
            },
            cancel: function (index) {
                layerPop.layer.close(index);
                location.reload();
            }
        });
    },
    suspendCannotPopup: function () {
        layerPop.show(commonPrompt({
            title: '无法申请休学~',
            sub_title: '您有已预约的课程还未完成'
        }), {
            btn: ['知道了']
        });
    },
    suspendBeforPopup: function (callback) {
        layerPop.show(commonPrompt({
            title: '休学期间，将无法进行直播课学习及录播课下载。',
            sub_title: '确定要休学么？'
        }), {
            btn: ['取 消', '确 定'],
            btn2: function (index) {
                layerPop.layer.close(index);
                callback();
            }
        });
    },
    suspendStopBeforPopup: function (callback) {
        layerPop.lineShow(commonPrompt({
            title: '停止休学，就没有机会申请休学啦，确定停止休学么？'
        }), {
            btn: ['取 消', '确 定'],
            btn2: function (index) {
                layerPop.layer.close(index);
                callback();
            }
        });
    }

};

var $ = require('jquery');
var utils = require('public/util.js');
var unitCall = require('../exam/unit_call');
var layerPop = require('public/layer');
require('./css/style.css');

module.exports = {
    generateExam: function (grade_id, cb) {
        var url = utils.buildURL('level_test', 'Grade/generateExam');
        unitCall.callJson('post', url, {grade: grade_id}, {
            success: function (ret) {
                if (ret.code == 200) {
                    window.location.href = '/grade/exam';
                } else {
                    layerPop.showMsg({msg: '请求失败'}, function () {
                        window.location.href = '/grade/main';
                    });
                }
            }
        });
    }
};

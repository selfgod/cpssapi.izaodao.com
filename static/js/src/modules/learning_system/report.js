var $ = require('jquery');
var async = require('async');
var utils = require('../../public/util.js');
var echarts = require('echarts');
var rankList = require('../../public/rank_list');
var schedule = require('./schedule');

var _buildEchart = function (element, params) {
    var myChart = echarts.init(element);
    params = params || {};
    var option = {
        tooltip: {
            trigger: 'item',
            formatter: '{a} <br/>{b}: {c} ({d}%)',
        },
        series: [
            {
                name: params.name,
                type: 'pie',
                radius: ['100%', '96%'],
                avoidLabelOverlap: false,
                label: {
                    normal: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        show: false,
                        textStyle: {
                            fontSize: '10',
                            fontWeight: 'bold'
                        }
                    }
                },
                labelLine: {
                    normal: {show: false}
                },
                data: params.data
            }
        ]
    };
    myChart.setOption(option);
};

var _initTotalCircle = function () {
    var element = document.getElementById('main_item01_v422');
    var params = {
        name: '总出勤率',
        data: [
            {value: element.getAttribute('data-unchecked'), name: '未报到'},
            {value: element.getAttribute('data-checked'), name: '已报到'}
        ]
    };
    _buildEchart(element, params);
};

var _initLiveCircle = function () {
    var element = document.getElementById('main_item02_v422');
    var params = {
        name: '直播出勤率',
        data: [
            {value: element.getAttribute('data-unchecked'), name: '未报到'},
            {value: element.getAttribute('data-checked'), name: '已报到'}
        ]
    };
    _buildEchart(element, params);
};

var _initPracticeCircle = function () {
    var element = document.getElementById('main_item03_v422');
    var params = {
        name: '一课一练完成率',
        data: [
            {value: element.getAttribute('data-unchecked'), name: '未完成'},
            {value: element.getAttribute('data-checked'), name: '已完成'}
        ]
    };
    _buildEchart(element, params);
};

var _initUnitCircle = function () {
    var element = document.getElementById('main_item04_v422');
    var params = {
        name: '单元测试完成率',
        data: [
            {value: element.getAttribute('data-unchecked'), name: '未完成'},
            {value: element.getAttribute('data-checked'), name: '已完成'}
        ]
    };
    _buildEchart(element, params);
};

module.exports = {
    rankData: {},
    init: function () {

    },
    loadPanel: function (params, callback) {
        var self = this;
        async.parallel({
            report: function (cb) {
                var url = schedule.buildUrl('menu/report');
                schedule.load('get', url, params, {
                    success: function (data) {
                        cb(null, data);
                    }
                });
            },
            rank: function (cb) {
                var url = utils.buildURL('learning', 'rank');
                schedule.load('get', url, {}, {
                    success: function (data) {
                        cb(null, data);
                    }
                });
            }
        }, function (error, result) {
            self.rankData = result.rank;
            callback(result.report);
        });
    },
    loadComplete: function () {
        $('#detail_content').html(this.rankData);
        _initTotalCircle();
        _initLiveCircle();
        _initPracticeCircle();
        _initUnitCircle();
        rankList.init(schedule.id);
    }
};

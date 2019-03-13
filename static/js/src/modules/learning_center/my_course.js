require('./css/cpss_major_v5.css');
require('./css/cpss_themes_v5.css');
require('./css/cpss_course_v5.css');
require('./css/radio_button_v5.css');
require('./css/classlearn.css');
var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
var _options = require('./options');
var switch_curricular = require('./switch_curricular');
var my_majors = require('./my_majors');
var my_reservation = require('./my_reservation');
var new_guide_tip = require('./new_guide_tip');
var popup = require('public/popup');
var user = require('public/user');
var history = require('public/history');
require('lib/jquery-circle');
module.exports = {
    /**
     * 阶段课程id
     */
    id: 0,
    /**
     * 是否是录播阶段课程
     */
    isRecord: false,
    planId: 0,
    planStageId: 0,
    roomName: '',
    roomPwd: '',
    /**
     * zdtalk下载地址
     */
    zdtalkDownload: '',
    init: function () {
        var self = this;
        my_majors.init();
        my_reservation.init();
        //切换上课，做题，资料
        $(_options.learnMianClass).on('click', '#switch_schedule_detail > p', function () {
            var element = $(this);
            var cate = element.data('cate');
            if (cate != 'guide') {
                element.addClass('current_teb_v5').removeClass('color_333').siblings().addClass('color_333').removeClass('current_teb_v5');
                $('#new_help').removeClass('current_teb_v5').removeClass('color_333').addClass('new_help').removeClass('new_help_current');
            } else {
                element.siblings().addClass('color_333').removeClass('current_teb_v5');
                $('#new_help').removeClass('current_teb_v5').removeClass('color_333').addClass('new_help_current').removeClass('new_help');
            }
            history.push('#myCourse', {id: self.id, cate: cate});
            self.getDetailList(self.id, cate);
        });
        //下载视频
        $(_options.learnMianClass).on('click', '.cpss_icon_download_ov', function () {
            var element = $(this);
            var lesson_id = element.parents('.cpss_classlearn_operate').data('id');
            self.getDownloadLink(self.id, lesson_id);
        });
        //回顾
        $(_options.learnMianClass).on('click', '.review', function () {
            var element = $(this);
            var lesson_id = element.parents('.cpss_classlearn_operate').data('id');
            var params = {
                plan: self.planId,
                stage: self.planStageId,
                schedule: self.id
            };
            var reviewUrl = utils.buildURL('learningsystem/review', lesson_id) + '?' + $.param(params);
            window.open(reviewUrl);
        });
        //进入教室
        $(_options.learnMianClass).on('click', '.enter_room', function () {
            var element = $(this);
            var date = parseInt(element.data('start'), 10);
            if (element.data('zdtalk') !== '') {
                var curDate = new Date();
                var cookie = require('public/cookie.js');
                var lesson_id = parseInt(element.parents('.cpss_classlearn_operate').data('id'), 10);
                cookie.set('zdtalkLessonId', lesson_id, new Date(curDate.getTime() + 60000));
                popup.downloadZdTalk(element.data('zdtalk'), element, self.zdtalkDownload, date);
            } else {
                var dateStr = utils.formatTimestamp(date);
                popup.showRoomInfo(self.roomName, self.roomPwd, dateStr);
            }
        });
        //做题
        $(_options.learnMianClass).on('click', '.exec', function () {
            var element = $(this);
            if (element.hasClass('over_30min')) {
                user.isOverLimit(function () {
                    popup.handleOver30min(element);
                });
            } else if (element.hasClass('retest')) {
                user.isOverLimit(function () {
                    popup.handleRetest(element);
                });
            } else if (element.hasClass('testRedo')) {
                user.isOverLimit(function () {
                    popup.handleTestRedo(element);
                });
            } else if (element.hasClass('not_start')) {
                popup.handleNotStart(element, parseInt(element.data('start_time'), 10), function () {
                    window.location.reload();
                });
            } else {
                user.isOverLimit(function () {
                    window.open(element.data('url'), '_blank');
                });
            }
        });
        //复制qq群号码
        $(_options.learnMianClass).on('click', '.qq_group', function () {
            var Clipboard = null;
            if (!utils.isOldBrowser()) {
                Clipboard = require('clipboard');
            }
            if (!this.clip && Clipboard) {
                this.clip = new Clipboard('#qq_group_num');
                this.clip.on('success', function (e) {
                    alert('QQ群号已复制:' + e.text);
                    e.clearSelection();
                });
                this.clip.on('error', function () {
                    alert('请使用Ctrl/Cmd + c 复制');
                });
            }
        });
    },
    initMainDom: function () {
        $('.myCourseClass').addClass('active_v5').siblings('.learn_nav').removeClass('active_v5');
        var switch_curricular_html = require('./template/switch_curricular.tpl');
        $(_options.learnMianClass).html(switch_curricular_html({
            option_class: 'cpss_my_curricular',
            major_link: '/study/#/myCourse?active=all',
            oral_link: '/study/#/learnHome',
            elective_link: '/study/#/myCourse?active=elective',
            special_link: '/study/#/myCourse?active=special',
            custom_link: '/study/#/learnHome'
        }));
    },
    handleFunc: function (curricular, args) {
        var self = this;
        var type = null;
        if (args && args.type) {
            type = args.type;
        }
        new_guide_tip._chooseClassGuide();
        utils.scrollTop();
        if (_.indexOf(_options.curricularMap, curricular) !== -1) {
            $(_options.learnMianClass).attr('ga-location', $('.myCourseClass').data('ga_name') + '_' + _options.curricularZhMap[curricular]);
            self.initMainDom();
            $('#cpss_' + curricular).addClass('off').removeClass('link').siblings().addClass('link').removeClass('off');
            if (curricular === 'major') {
                my_majors.requestFunc();
            } else {
                my_reservation.requestFunc(curricular, type);
            }
        }
    },
    /**
     * 我的课程页
     */
    mySchedule: function (args) {
        var self = this;
        utils.scrollTop();
        $(_options.learnMianClass).attr('ga-location', $('.myCourseClass').data('ga_name') + '_主修课_学习页');
        if (['learn', 'exercise', 'datum', 'report', 'giude'].indexOf(args.cate) === -1) {
            args.cate = 'learn';
        }
        $('.myCourseClass').addClass('active_v5').siblings('.learn_nav').removeClass('active_v5');
        var url = utils.buildURL('learning_center/myCourse', 'info');
        utils.callJson('get', url, {schedule_id: args.id}, {
            success: function (ret) {
                if (ret.code === 200) {
                    self.id = args.id;
                    var data = ret.data;
                    self.isRecord = (data.class_mode === '2');
                    self.planId = data.plan_id;
                    self.planStageId = data.plan_stage_id;
                    self.roomPwd = data.room_pwd;
                    self.roomName = data.room_name;
                    self.zdtalkDownload = data.zdtalk_download;
                    var tpl = require('./template/my_schedule.tpl');
                    var content = tpl({
                        isRecord: self.isRecord,
                        title: data.name,
                        time_str: data.start_time + '-' + data.end_time + ' ' + data.class_week_time + ' ' + data.class_start_time + '-' + data.class_end_time,
                        teachers: data.teachers,
                        qq: data.qq_group,
                        books: data.relate_books
                    });
                    var hbs = require('./template/schedule_detail_list.hbs');
                    var live_class = data.class_mode;
                    if (data.first_view == '0' && live_class == '1') {
                        args.cate = 'guide';
                    }
                    var switch_list = hbs({current: args.cate, live: live_class});
                    $(_options.learnMianClass).html(content + switch_list);
                    $('#finished_circle').circleChart({
                        size: 70,
                        value: data.check_in_rate,
                        color: '#3399ff',
                        backgroundColor: '#eee',
                        startAngle: 75,
                        text: data.check_in_count + '/' + data.class_num
                    });
                    self.getDetailList(args.id, args.cate);
                } else {
                    var router = require('public/router');
                    router.goHashUrl('selectCourse/major');
                }
            }
        });
    },
    /**
     * 获取我的课程某个阶段课程课件，做题，资料详细列表
     * @param scheduleId
     * @param cate learn|exercise|datum
     */
    getDetailList: function (scheduleId, cate) {
        var learnHbs;
        var self = this;
        var data = {};
        var emptyTips = {
            learn: '暂无课程',
            exercise: '暂无习题',
            datum: '暂无资料'
        };
        var url = utils.buildURL('learning_center/myCourse', 'detailList');
        cate = cate || 'learn';

        utils.callWithLoading('get', url, {schedule_id: scheduleId, cate: cate}, {
            success: function (ret) {
                if (ret.code === 200) {
                    if (ret.data.length === 0) {
                        learnHbs = require('./template/no_content.hbs');
                        $('#schedule_detail_list').html(learnHbs({title: emptyTips[cate]}));
                        return;
                    }
                    switch (cate) {
                        case 'learn':
                            learnHbs = require('./template/schedule_learn_list.hbs');
                            break;
                        case 'exercise':
                            learnHbs = require('./template/schedule_exercise_list.hbs');
                            data.testPrefix = JP_DOMAIN + 'test.php?mod=doing&';
                            data.scheduleId = self.id;
                            data.planId = self.planId;
                            data.planStageId = self.planStageId;
                            break;
                        case 'datum':
                            learnHbs = require('./template/schedule_datum_list.hbs');
                            break;
                        case 'report':
                            learnHbs = require('./template/schedule_report_detail.hbs');
                            break;
                        case 'guide':
                            learnHbs = require('./template/schedule_guide_detail.hbs');
                            data.Schedule_info = ret.data.Schedule_info;
                            data.schedule_week_cycle = ret.data.schedule_week_cycle;
                            data.schedule_text_books = ret.data.schedule_text_books;
                            data.have_text_books = ret.data.have_text_books;
                            data.schedule_id = scheduleId;
                            break;
                        default:
                            break;
                    }
                    data.list = ret.data;
                    data.isRecord = self.isRecord;
                    $('#schedule_detail_list').html(learnHbs(data));
                    if (cate === 'report') {
                        self.reportDetailCircle(data);
                    }
                    if (cate === 'guide') {
                        self.guideMark(scheduleId);
                    }
                } else if (ret.code === 262) { //休学中
                    learnHbs = require('./template/suspend_tip.hbs');
                    $('#schedule_detail_list').html(learnHbs());
                }
            }
        }, {loading: {top: 50}}, '#schedule_detail_list');
    },
    reportDetailCircle: function (data) {
        var circleArr = [
            {
                dom: '#total_attendance',
                value: data.list.total.percentage,
                color: '#3399ff',
                name: '<br><em>总出勤率</em>'
            },
            {
                dom: '#live_attendance',
                value: data.list.live.percentage,
                color: '#66b3ff',
                name: '<br><em>直播出勤率</em>'
            },
            {
                dom: '#test_finished',
                value: data.list.practice.percentage,
                color: '#46dd6b',
                name: '<br><em>一课一练完成率</em>'
            },
            {
                dom: '#unit_finished',
                value: data.list.unit.percentage,
                color: '#ffd300',
                name: '<br><em>单元测试完成率</em>'
            }
        ];
        $.each(circleArr, function (index) {
            $(circleArr[index].dom).circleChart({
                size: 200,
                value: circleArr[index].value,
                color: circleArr[index].color,
                backgroundColor: '#e5e5e5',
                widthRatio: 0.06,
                startAngle: 75,
                text: 0 + '%',
                onDraw: function (el, circle) {
                    $('.circleChart_text', el).html(Math.round(circle.value) + '%' + circleArr[index].name);
                }
            });
        });
    },
    /**
     * 标记已进入过新手指导
     */
    guideMark: function (scheduleId) {
        var url = utils.buildURL('learning_center/myCourse', 'updateFirstView');
        utils.callJson('get', url, {schedule_id: scheduleId}, {
            success: function (ret) {
                if (ret.code === 200) {
                    console.log('');
                }
            }
        });
    },
    /**
     * 获取阶段课程课件的下载链接
     */
    getDownloadLink: function (scheduleId, lessonId) {
        var url = utils.buildURL('learning_center/myCourse', 'download');
        utils.callJson('get', url, {schedule_id: scheduleId, lesson_id: lessonId}, {
            success: function (ret) {
                if (ret.code === 200) {
                    window.location.href = ret.data.link;
                }
            }
        });
    }
};

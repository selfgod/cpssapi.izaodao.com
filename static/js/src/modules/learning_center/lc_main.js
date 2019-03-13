require('./css/nav_left_5.1.0.css');
require('./css/list_v5_5.1.0.css');
require('./css/cpss_tip_layer.css');
var $ = require('jquery');
var _ = require('lodash');
var _options = require('./options');
var router = require('public/router');
var learn_home = require('./learn_home');
var my_course = require('./my_course');
var select_course = require('./select_course');
var lesson_basic = require('./lesson_basic');
//var new_guide_tip = require('./new_guide_tip');
module.exports = {
    init: function () {
        var self = this;
        $.hash.init();
        router.init();
        learn_home.init();
        my_course.init();
        select_course.init();
        lesson_basic.init();
        //new_guide_tip.init();
        router.route('learnHome', function () {
            self.goLinkFunc('/study/#/learnHome');
            //learn_home.requestFunc();
        });
        router.route('myCourse/major', function (args) {
            self.goLinkFunc('/study/#/learnHome');
            //my_course.handleFunc('major', args);
        });
        router.route('myCourse/oral', function (args) {
            self.goLinkFunc('/study/#/learnHome');
            //my_course.handleFunc('oral', args);
        });
        router.route('myCourse/elective', function (args) {
            self.goLinkFunc('/study/#/learnHome');
            //my_course.handleFunc('elective', args);
        });
        router.route('myCourse/special', function (args) {
            self.goLinkFunc('/study/#/learnHome');
            //my_course.handleFunc('special', args);
        });
        router.route('myCourse/custom', function (args) {
            self.goLinkFunc('/study/#/learnHome');
            //my_course.handleFunc('custom', args);
        });
        router.route('myCourse', function (args) {
            self.goLinkFunc('/study/#/learnHome');
            //my_course.mySchedule(args);
        });
        router.route('selectCourse/major', function () {
            self.goLinkFunc('/study/#/learnHome');
            //select_course.handleFunc('major');
        });
        router.route('selectCourse/oral', function () {
            self.goLinkFunc('/study/#/learnHome');
            //select_course.handleFunc('oral');
        });
        router.route('selectCourse/elective', function () {
            self.goLinkFunc('/study/#/learnHome');
            //select_course.handleFunc('elective');
        });
        router.route('selectCourse/special', function () {
            self.goLinkFunc('/study/#/learnHome');
            //select_course.handleFunc('special');
        });
        router.route('selectCourse/custom', function () {
            self.goLinkFunc('/study/#/learnHome');
            //select_course.handleFunc('custom');
        });
        router.route('selectSchedule', function (args) {
            self.goLinkFunc('/study/#/learnHome');
            //select_course.handleFunc('major', args);
        });
        router.route('oralAssessment', function (args) {
            select_course.handleFunc('oral', args);
        });
        this.routesFunc();
    },
    //路由方法
    routesFunc: function () {
        var xHash = $.hash.getHash();
        var gHash = xHash;
        if (gHash.indexOf('?') > 0) {
            var xHashArr = gHash.split('?');
            gHash = xHashArr[0];
        }
        if (!gHash || typeof (gHash) === 'undefined' || !(router.routes[gHash])) {
            xHash = _options.learnActionMap[0];
        }
        router.goHashUrl(xHash);
    },
    goLinkFunc: function (link) {
        window.location.href = link;
    }
};

require('./css/common.css');
require('./css/unit_exam.css');
var $ = require('jquery');
var util = require('public/util.js');
var swcate = require('./switch_cate');
var mainHbs = require('./template/question_list.hbs');
var cardHbs = require('./template/question_card.hbs');
var baseUrl = util.buildURL('exam', 'unitTest');
var layerPop = require('public/layer');
var exam = require('./exam');

util.preventBack();

if (document.getElementById('over_limit')) {
    layerPop.showOneLine('本场考试人数已满，去参加其他的考试吧！', {
        btn: ['知道了'],
        yes: function () {
            window.location.href = JP_DOMAIN + 'main.php/tiku/exam';
        },
        closeBtn: 0
    });
} else {
    exam.init(baseUrl, function () {
        var lastCate = $('#last_cate').val();
        var part1LastCate = $('#part1_last_cate').val();
        swcate.init(baseUrl + '/subjectDetail', function (cate, data) {
            exam.setCate(cate);
            exam.initAnswerNum();
            var mainContent = mainHbs({list: data.subject});
            $('#detail_content').html(mainContent);
            var questionCard = cardHbs({
                questions: data.question_card,
                cate: cate,
                lastCate: lastCate,
                part1LastCate: part1LastCate
            });
            $('#question_num').html(questionCard);
        });
    });
}


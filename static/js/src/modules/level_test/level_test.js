require('./css/style.css');
var $ = require('jquery');
var util = require('public/util.js');
var swcate = require('./switch_cate');
var layerPop = require('public/layer');
var mainHbs = require('./template/question_list.hbs');
var cardHbs = require('./template/question_card.hbs');
var baseUrl = util.buildURL('level_test', 'grade');
var grade = require('./grade');
util.preventBack();

grade.init(baseUrl, function () {
    var lastCate = $('#last_cate').val();
    swcate.init(baseUrl + '/subjectDetail', function (cate, data) {
        //todo======
        if (!data.subject) {
            layerPop.showMsg({msg: '请求失败'}, function () {
                window.location.href = '/grade/main';
            });
        }
        grade.setCate(cate);
        grade.initAnswerNum();
        var mainContent = mainHbs({list: data.subject});
        // console.log(mainContent);
        $('#detail_content').html(mainContent);  //题
        var questionCard = cardHbs({
            questions: data.question_card,
            cate: cate,
            lastCate: lastCate
            // part1LastCate: part1LastCate
        });
        $('#question_num').html(questionCard); //答题卡
    });
});

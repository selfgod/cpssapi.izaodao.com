require('./css/style.css');
var $ = require('jquery');
var util = require('public/util.js');
var swcate = require('./switch_cate');
var mainHbs = require('./template/review_list.hbs');
var cardHbs = require('./template/review_card.hbs');
var baseUrl = util.buildURL('level_test', 'grade');
var layerPop = require('public/layer');

document.domain = 'izaodao.com';
var detailContent = $('#detail_content');
var topics = {};
swcate.init(baseUrl + '/reviewCate', function (cate, data) {
    console.log(data.subject);
    topics = data.subject;
    var mainContent = mainHbs({list: data.subject, cate: cate});// 主题数据
    detailContent.html(mainContent);
    var questionCard = cardHbs({
        questions: data.question_card
    });
    $('#question_num').html(questionCard);
});

//题目讨论
detailContent.on('click', '.zzd_question', function () {
    var el = $(this);
    if (!el.hasClass('popup')) {
        var id = el.data('relate_id');
        window.open(ZD_KNOW + 'exercises/detail/' + id + '.do', '_blank');
    } else {
        var qid = el.data('question');
        var topic_id = el.data('topic');
        var data = {exerciseId: parseInt(qid, 10)};
        var items = [], rightId;
        data.exerciseTopic = topics[topic_id].title;

        for (var i = 0; i < topics[topic_id].questions.length; i++) {
            var item = topics[topic_id].questions[i];
            if (parseInt(item.question_id, 10) === qid) {
                data.exerciseTitle = item.question;
                rightId = item.right;
                item.option.forEach(function (value, index) {
                    items.push({
                        itemDescription: value.wa_answer,
                        itemId: index + 1,
                        right: rightId === value.wa_id
                    });
                });
                break;
            }
        }
        data.items = items;
        var url = ZD_KNOW + 'exercises/discuss.do?callBack=' + CPSS_DOMAIN + 'api/exercise/mapKnowId';
        layerPop.show([url, 'no'], {
            type: 2,
            area: ['800px', '540px'],
            title: '讨论',
            success: function (layero) {
                var iframeWin = window[layero.find('iframe')[0]['name']];
                var textarea = iframeWin.document.getElementById('exercisesJson');
                if (textarea) {
                    textarea.innerText = encodeURIComponent(JSON.stringify(data));
                }
            }
        });
    }
});

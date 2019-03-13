var $ = require('jquery');
var _element = $('.zdtalk_main');
module.exports = {
    zdtalkClassroom: function () {
        var utils = require('public/util.js');
        var url = utils.buildURL('learning_center/myCourse', 'talkRoomUrl');
        utils.callJson('get', url, {}, {
            success: function (ret) {
                if (ret.code === 200 && ret.data.talkUrl) {
                    utils.launchZDtalk(_element, ret.data.download, ret.data.talkUrl);
                }
            }
        });
        _element.on('click', '.help_view', function () {
            _element.find('.zdtalk_content').show();
        });
    }
};

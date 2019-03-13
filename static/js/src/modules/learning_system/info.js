var schedule = require('./schedule');
module.exports = {
    loadPanel: function (params, callback) {
        var url = schedule.buildUrl('menu/info');
        schedule.load('get', url, params, {
            success: function (data) {
                callback(data);
            }
        });
    }
};

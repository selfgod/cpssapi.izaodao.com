var dir = require('./dir.config');
var path = require('path');
module.exports = {
    lc: path.resolve(dir.modules, 'learning_center/index.js'),
    tc: path.resolve(dir.modules, 'teaching_center/index.js'),
    review: path.resolve(dir.modules, 'review/index.js'),
    purchased: path.resolve(dir.modules, 'purchased/purchased.js'),
    p_detail: path.resolve(dir.modules, 'count_analyze/count_analyze.js'),
    upgrade: path.resolve(dir.modules, 'upgrade/upgrade.js'),
    accounting: path.resolve(dir.modules, 'upgrade/accounting.js'),
    zdtalk: path.resolve(dir.modules, 'zdtalk/index.js'),
    unit_enter: path.resolve(dir.modules, 'exam/unit_enter.js'),
    unit_exam: path.resolve(dir.modules, 'exam/unit_exam.js'),
    unit_rest: path.resolve(dir.modules, 'exam/unit_rest.js'),
    unit_review: path.resolve(dir.modules, 'exam/unit_review.js'),
    unit_result: path.resolve(dir.modules, 'exam/unit_result.js'),
    level_test: path.resolve(dir.modules, 'level_test/level_test.js'),
    level_review: path.resolve(dir.modules, 'level_test/level_review.js'),
    level_result: path.resolve(dir.modules, 'level_test/level_result.js'),
    level_index: path.resolve(dir.modules, 'level_test/level_index.js'),
    level_generate: path.resolve(dir.modules, 'level_test/level_generate.js'),
    level_notice: path.resolve(dir.modules, 'level_test/level_notice.js')
    // vendor: ['./js/src/lib/layer/layer.js']
};

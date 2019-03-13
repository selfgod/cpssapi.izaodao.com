var $ = require('jquery');
var publicJs = require('./public');
require('./css/style.css');

$('#main').on('click', '.generateExam', function () {
    var $el = $(this);
    var grade_id = $el.data('grade');
    publicJs.generateExam(grade_id);
});

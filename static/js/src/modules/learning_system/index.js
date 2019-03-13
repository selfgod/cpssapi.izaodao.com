var switch_schedule = require('./switch_schedule');
var datum = require('./datum');
var exercise = require('./exercise');
var lesson = require('./lesson');
require('public/ga');

switch_schedule.init();
datum.init();
exercise.init();
lesson.init();


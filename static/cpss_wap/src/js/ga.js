var $ = require('jquery');
$('body').on('click', '.ga', function (event) {
    var element = $(event.target);
    var location = element.attr('ga-location') ? element.attr('ga-location') : element.parents('.google_event').attr('ga-location');
    if (location) {
        var type = element.attr('ga-type') ? element.attr('ga-type') : element.parents('.google_event').attr('ga-type');
        var title = element.attr('ga-title') ? element.attr('ga-title') : element.parents('.google_event').attr('ga-title');
        if (window.ga) {
            window.ga('send', 'event', location, type, title);
        }
    }
});

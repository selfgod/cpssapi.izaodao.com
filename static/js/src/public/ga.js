var $ = require('jquery');
$('.ga').on('click', function (event) {
    var element = $(event.target);
    var location = element.attr('ga-location') || element.parents('.google_event').attr('ga-location') || $(this).attr('ga-location');
    var type = element.attr('ga-type') || element.parents('.google_event').attr('ga-type') || $(this).attr('ga-type');
    var label = element.attr('ga-label') || element.parents('.google_event').attr('ga-label') || $(this).attr('ga-label');
    if (location && type) {
        if (label) type = label + type;
        var title = element.attr('ga-title') || element.parents('.google_event').attr('ga-title');
        if (window.ga) {
            window.ga('send', 'event', location, type, title);
        }
    }
});

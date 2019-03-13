var $ = require('jquery');

module.exports = {
    nav: null,
    category: null,
    type: null,
    init: function () {
        this.nav = $('#hide_nav').val();
        this.category = $('#hide_category').val();
        this.type = $('#hide_type').val();
    },
    clear: function () {
        this.nav = this.category = this.type = null;
    }
};

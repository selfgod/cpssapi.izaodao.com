require('./accounts.css');
var $ = require('jquery');
var _ = require('lodash');
var utils = require('public/util.js');
require('public/ga');
var config = require('public/config');
var goodsDetail = require('./template/purchased_detail.tpl');
var upgradDetail = require('./template/upgrade_detail.tpl');
var timeline = require('./img/timeline.png');
var dom = {};
var purchased = {};
var upgradeList = {};
var curricular = ['major', 'oral', 'elective', 'special', 'custom'];
var selectedPur, selectedUpgrade, disable = false;

var appendDropdown = function (element, id, name) {
    element.append('<option value="' + id + '">' + name + '</option>');
};

var formatCout = function (data) {
    _.forEach(curricular, function (value) {
        if (_.isUndefined(data.count_list)) {
            data.count_list = {};
        }
        if (_.isUndefined(data.count_list[value])) {
            data.count_list[value] = {total: 0};
        }
    });
};

/**
 * 加载已购商品列表
 */
var loadPurchased = function () {
    var url = utils.buildURL('upgrade', 'goods');
    utils.call('get', url, {}, {
        success: function (ret) {
            purchased = ret.data;
            _.forEach(purchased, function (value, id) {
                appendDropdown(dom.purchasedList, id, value.name);
                formatCout(value);
            });
            setPreSelected();
        }
    }, {dataType: 'json'});
};

var init = function () {
    dom.purchasedDetail = $('#goods_detail');
    dom.upgradeDetail = $('#upgrade_detail');
    dom.purchasedList = $('#purchased_goods_list');
    dom.upgreadList = $('#upgrade_goods_list');
    dom.purWarning = $('#purchased_warning');
    dom.upgradeWarning = $('#upgrade_warning');
    dom.noneUpgradeWarning = $('#none_warning');
    dom.preSelected = $('#pre_goods');

    $('#accounting').on('click', function () {
        if (!selectedPur) {
            dom.purWarning.show();
            return;
        }
        if (!selectedUpgrade) {
            if (!dom.noneUpgradeWarning.is(':visible')) {
                dom.upgradeWarning.show();
                return;
            }
            return;
        }
        var intentionUrl = utils.buildURL('upgrade', 'intention');
        var param = {
            from: selectedPur,
            to: selectedUpgrade
        };
        if (disable === true) {
            return;
        }
        disable = true;
        utils.call('post', intentionUrl, param, {
            success: function (ret) {
                if (ret.code == 200) {
                    var crmUrl = config.getLink('courseLink') + 'myIntention';
                    utils.call('get', crmUrl, {origin_id: selectedPur}, {
                        complete: function () {
                            disable = false;
                            window.location.href = '/upgrade/accounting?from=' + selectedPur;
                        }
                    }, {dataType: 'jsonp'});
                } else {
                    disable = false;
                }
            }
        }, {dataType: 'json'});
    });
    loadPurchased();
    dom.purchasedList.change(function () {
        dom.purWarning.hide();
        dom.noneUpgradeWarning.hide();
        var id = parseInt($(this).val(), 10);
        updatePurchasedDetail(id);
    });
    dom.upgreadList.change(function () {
        dom.upgradeWarning.hide();
        renderUpgradeDetail();
    });
};

var setPreSelected = function () {
    var pre = parseInt($('#pre_goods').val(), 10);
    dom.purchasedList.val(pre);
    updatePurchasedDetail(pre);
};

var updatePurchasedDetail = function (id) {
    renderPurchasedDetail();
    removeUpgradeList();
    if (id > 0) {
        loadTarget(id);
    }
};

var removeUpgradeList = function () {
    selectedUpgrade = null;
    dom.upgradeWarning.hide();
    dom.upgreadList.html('<option value="0">请选择</option>');
    dom.upgradeDetail.html('');
};

/**
 * 加载可升级商品列表
 * @param id
 */
var loadTarget = function (id) {
    if (upgradeList[id]) {
        if (upgradeList[id].length === 0) {
            dom.noneUpgradeWarning.show();
        }
        _.forEach(upgradeList[id], function (value, rid) {
            appendDropdown(dom.upgreadList, rid, value.name);
        });
        return;
    }
    var url = utils.buildURL('upgrade', 'goods/' + id);
    utils.call('get', url, {}, {
        success: function (ret) {
            upgradeList[id] = ret.data;
            if (ret.data.length === 0) {
                dom.noneUpgradeWarning.show();
            }
            _.forEach(ret.data, function (value, rid) {
                appendDropdown(dom.upgreadList, rid, value.name);
                formatCout(value);
            });
        }
    }, {dataType: 'json'});
};

/**
 * 选择升级目标商品后显示商品详情
 */
var renderUpgradeDetail = function () {
    var purchasedId = dom.purchasedList.val();
    var selectedId = parseInt(dom.upgreadList.val(), 10);
    var detailDom = dom.upgradeDetail;
    var selected = upgradeList[purchasedId][selectedId];

    if (selectedId <= 0 || !selected) {
        selectedUpgrade = null;
        detailDom.html('');
        return;
    }
    selectedUpgrade = selectedId;
    var param = {
        discount_price: selected.discount_price,
        expire_str: selected.expire_str
    };
    _.forEach(selected.count_list, function (value, key) {
        if (value.is_unlimit) {
            param[key] = '不限</b>';
        } else {
            param[key] = value.total + '</b>次';
        }
    });
    detailDom.html(upgradDetail(param));
};

/**
 * 选择商品后显示商品详情
 */
var renderPurchasedDetail = function () {
    var selected, selectedId, detailDom;

    selectedId = parseInt(dom.purchasedList.val(), 10);
    detailDom = dom.purchasedDetail;
    selected = purchased[selectedId];

    if (selectedId <= 0 || !selected) {
        selectedPur = null;
        detailDom.html('');
        return;
    }
    selectedPur = selectedId;
    var param = {
        image: timeline,
        deal_price: selected.deal_price,
        activate_days: selected.activate_days,
        expire_str: selected.expire_str,
        activate_date: selected.activate_date,
        expire_date: selected.expire_date
    };
    _.forEach(selected.count_list, function (value, key) {
        if (value.is_unlimit) {
            param[key] = '不限</b>';
        } else {
            param[key] = value.total + '</b>次';
        }
    });
    detailDom.html(goodsDetail(param));
};

init();

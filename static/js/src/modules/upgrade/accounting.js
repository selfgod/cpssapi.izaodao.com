require('./accounts.css');
var $ = require('jquery');
var _ = require('lodash');
require('public/ga');
var config = require('public/config');
var utils = require('public/util');
var yhq = {}, lq = {}, dom = {}, selected_yhq, selected_lq;
var remain_zy = 0, originId = 0, discount_yuan = 0;
var pre_price = 0, off_price = 0, pay_price = 0, lq_price = 0, yhq_price = 0;
var manjian = null;

var appendDropdown = function (element, id, name, selected) {
    if (selected) {
        element.append('<option value="' + id + '" selected>' + name + '</option>');
    } else {
        element.append('<option value="' + id + '">' + name + '</option>');
    }
};

/**
 * 升级折扣
 */
var renderOffPrice = function () {
    dom.offPriceBody.removeClass('hide');
    dom.offPrice.html('-￥' + off_price);
};

var renderManjian = function () {
    if (manjian) {
        dom.manjian.html('<span>满　减：</span><i>' + manjian.title +
            '</i><b>-￥' + manjian.money + '</b>');
    } else {
        dom.manjian.html('');
    }
};

var renderZaoYuan = function () {
    dom.zy.html('-￥' + discount_yuan);
};

/**
 * 渲染优惠券区域
 */
var renderYhq = function () {
    var temp = [];
    if (_.isEmpty(yhq)) {
        dom.yhqSelector.hide();
    } else {
        dom.yhqSelector.show();
        //按照金额降序
        temp = _.sortBy(yhq, function (o) {
            return 0 - o.money;
        });
        _.forEach(temp, function (value) {
            if (value.id == selected_yhq) {
                appendDropdown(dom.yhqDropDown, value.id, value.money + '优惠券', true);
                dom.yhqMoney.html('-￥' + yhq_price);
            } else {
                appendDropdown(dom.yhqDropDown, value.id, value.money + '优惠券');
            }
        });
    }
};
/**
 * 渲染礼券区域
 */
var renderLq = function () {
    var temp = [];
    if (_.isEmpty(lq)) {
        dom.lqSelector.hide();
    } else {
        dom.lqSelector.show();
        //按照金额降序
        temp = _.sortBy(lq, function (o) {
            return 0 - o.money;
        });
        _.forEach(temp, function (value) {
            if (value.id == selected_lq) {
                appendDropdown(dom.lqDropDown, value.id, value.money + '礼券', true);
                dom.lqMoney.html('-￥' + lq_price);
            } else {
                appendDropdown(dom.lqDropDown, value.id, value.money + '礼券');
            }
        });
    }
};

var render = function () {
    dom.yhqDropDown.html('<option value="0">请选择优惠券</option>');
    dom.lqDropDown.html('<option value="0">请选择礼券</option>');
    renderYhq();
    renderLq();
    renderZaoYuan();
    dom.payPrice.html('￥' + pay_price);
    dom.prePrice.html('￥' + pre_price);
    if (pre_price || discount_yuan) {
        dom.preOrder.removeClass('hide');
        renderManjian();
    } else {
        dom.preOrder.addClass('hide');
    }
    if (pay_price) {
        // renderOffPrice();
    } else {
        dom.offPriceBody.addClass('hide');
    }
};

var loadData = function (yhqId, lqId, init) {
    yhqId = yhqId || 0;
    lqId = lqId || 0;
    var url = config.getLink('courseLink') + 'upgradeCouponDiscount';
    var params = {
        origin_id: originId,
        yhq_id: yhqId,
        lq_id: lqId
    };
    utils.call('get', url, params, {
        success: function (data) {
            if (data.code !== 200) {
                window.location.href = '/upgrade';
            }
            var ret = data.data;
            selected_yhq = ret.coupon_yhq_id;
            selected_lq = ret.coupon_liquan_id;
            lq_price = ret.coupon_liquan_price;
            yhq_price = ret.coupon_yhq_price;
            discount_yuan = ret.use_zy;
            pre_price = ret.should_pay;
            off_price = ret.off_price;
            pay_price = ret.pay_price;
            if (ret.prog1.status) {
                manjian = {
                    title: ret.prog1.title,
                    money: ret.prog1.attribute.give_num
                };
            } else {
                manjian = null;
            }
            if (ret.upgrade_discount.type && init) {
                if (ret.upgrade_discount.type === 'total') {
                    $('#upgrade_discount').html('减免已购商品成交价');
                } else {
                    $('#upgrade_discount').html('减免未消费商品费用');
                }
                $('#upgrade_discount_money').html('-￥' + ret.upgrade_discount.discount);
            }
            _.forEach(ret.yhq, function (value) {
                yhq[value.id] = value;
            });
            _.forEach(ret.lq, function (value) {
                lq[value.id] = value;
            });
            render();
        }
    }, {dataType: 'jsonp'});
};

var createOrder = function (realName, mobile) {
    if (!dom.submit.hasClass('yellow')) {
        return;
    }
    var url = config.getLink('orderLink') + 'createUpgradeOrder';
    var params = {
        real_name: realName,
        mobile: mobile,
        origin_id: originId,
        lq_id: selected_lq,
        yhq_id: selected_yhq,
        back_url: 'http://' + document.domain + '/upgrade'
    };
    dom.submit.removeClass('yellow');
    utils.call('post', url, params, {
        success: function (ret) {
            if (ret.code === 501 || ret.code === 200) {
                window.location.href = config.getLink('payLink') + ret.data.oid;
            }
        }
    }, {dataType: 'jsonp'});
};

/**
 * 是否显示优惠券弹出
 * @param realName
 * @param mobile
 * @returns {boolean}
 */
var showYhqWarning = function (realName, mobile) {
    var name = null;
    if (!_.isEmpty(yhq) && selected_yhq == 0) {
        name = '优惠券';
    } else if (!_.isEmpty(lq) && selected_lq == 0) {
        name = '礼券';
    }
    if (name) {
        var layerPop = require('public/layer');
        var commonPrompt = require('public/template/common_prompt.hbs');
        layerPop.lineShow(commonPrompt({
            title: '您还有' + name + '未使用，确认去支付？'
        }), {
            btn: ['继续支付', '去用卷'],
            yes: function (index) {
                layerPop.layer.close(index);
                createOrder(realName, mobile);
            }
        });
        return true;
    } else {
        return false;
    }
};

/**
 * 绑定下拉列表事件
 */
var bindDataChange = function () {
    utils.enablePlaceholder(dom.realName);
    utils.enablePlaceholder(dom.mobile);
    dom.yhqDropDown.change(function () {
        var item = yhq[$(this).val()];
        if (item) {
            loadData(item.id, selected_lq, false);
        } else {
            selected_yhq = -1;
            loadData(-1, selected_lq, false);
            dom.yhqMoney.html('-￥0');
        }
    });

    dom.lqDropDown.change(function () {
        var item = lq[$(this).val()];
        if (item) {
            loadData(selected_yhq, item.id, false);
        } else {
            selected_lq = -1;
            loadData(selected_yhq, -1, false);
            dom.lqMoney.html('-￥0');
        }
    });

    dom.submit.on('click', function () {
        var realName = dom.realName.val();
        var mobile = dom.mobile.val();
        var reg = /[`~!@#$%\^&*()_+<>?:"{},.\/\\;'\[\]\s]/;
        dom.warning.html('');
        dom.realName.removeClass('error');
        dom.mobile.removeClass('error');
        if (realName.length == 0 || realName === '请输入真实姓名') {
            dom.warning.html('请输入真实姓名！');
            dom.realName.addClass('error');
            return;
        } else if (reg.test(realName)) {
            dom.warning.html('真实姓名不能包含非法字符！');
            dom.realName.addClass('error');
            return;
        }
        if (mobile.length == 0 || mobile === '请填写联系电话，方便老师和您联系') {
            dom.warning.html('请填写联系电话！');
            dom.mobile.addClass('error');
            return;
        } else if (!utils.validMobile(mobile)) {
            dom.warning.html('手机号码格式不正确！');
            dom.mobile.addClass('error');
            return;
        }
        if (!dom.procotol.is(':checked')) {
            dom.warning.html('还没有勾选上课协议！');
            return;
        }
        if (showYhqWarning(realName, mobile)) {
            return;
        }
        createOrder(realName, mobile);
    });
};


var getDoms = function () {
    dom.yhqDropDown = $('#yhq');
    dom.lqDropDown = $('#lq');
    dom.yhqMoney = $('#yhq_money');
    dom.lqMoney = $('#lq_money');
    dom.zy = $('#yuan');
    dom.prePrice = $('#pre_price');
    dom.offPrice = $('#off_price');
    dom.payPrice = $('#pay_price');
    dom.realName = $('#real_name');
    dom.mobile = $('#mobile');
    dom.manjian = $('#manjian');
    dom.warning = $('#warning');
    dom.procotol = $('#protocol');
    dom.preOrder = $('#pre_order');
    dom.offPriceBody = $('#off_price_body');
    dom.yhqSelector = $('#yhq_selector');
    dom.lqSelector = $('#lq_selector');
    dom.submit = $('#submit');
};

var init = function () {
    originId = $('#origin_goods').val();
    remain_zy = $('#remain_zy').val();
    getDoms();
    bindDataChange();
    loadData(0, 0, true);
};
init();

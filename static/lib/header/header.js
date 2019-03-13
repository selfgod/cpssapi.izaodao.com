$(function () {
    $('body').removeAttr('style');

    $(".drop_v41").hover(function () {
        $(this).find("i.icon_country_new").removeClass("icon_down_new");
        $(this).find("i.icon_country_new").addClass("icon_up_new");
        $(this).find(".pull_down_v41").stop().fadeIn(500);
    }, function () {
        $(this).find("i.icon_country_new").removeClass("icon_up_new");
        $(this).find("i.icon_country_new").addClass("icon_down_new");
        $(this).find(".pull_down_v41").stop().hide();
    });

    $('.header_teb_v400 li').hover(function () {
        $(this).find('.selecting_list_v400').stop().fadeIn(500);
        $(this).find('i').removeClass('down_v400').addClass('up_v400');
    }, function () {
        $(this).find('.selecting_list_v400').hide();
        $(this).find('i').removeClass('up_v400').addClass('down_v400');
    });

    $('.header_teb_v400 li').hover(function () {
        $(this).find('.tool_list_v400').stop().fadeIn(500);
        $(this).find('i').removeClass('down_v400').addClass('up_v400');
    }, function () {
        $(this).find('.tool_list_v400').hide();
        $(this).find('i').removeClass('up_v400').addClass('down_v400');
    });

    $('.user_v400').hover(function () {
        $(this).find('.userbar_bg_v400').stop().fadeIn(500);
        $(this).find('i').removeClass('down_v400').addClass('up_v400');
    }, function () {
        $(this).find('.userbar_bg_v400').hide();
        $(this).find('i').removeClass('up_v400').addClass('down_v400');
    }).click(function () {
        $(this).find('.userbar_bg_v400').hide();
        $(this).find('i').removeClass('up_v400').addClass('down_v400');
    });

    $('.phone_v400').hover(function () {
        $(this).find('.tel_list_v400').stop().fadeIn(500);
    }, function () {
        $(this).find('.tel_list_v400').hide();
    });
    //头部高亮
    var highlight = {
        home: 'menu_home',
        openclass: 'menu_experience',
        Experience: 'menu_experience',
        Openclass: 'menu_experience',
        tiku: 'menu_learning_tools',
        'test.php': 'menu_learning_tools',
        cpss: 'menu_learning_system',
        'T/VIPPSS': 'menu_select_class',
        'T/SVIP': 'menu_select_class',
        'T/kouyu': 'menu_select_class',
        'T/xuanxiu': 'menu_select_class',
        Leveltest: 'menu_learning_tools',
        misc: 'menu_learning_tools',
        zd_ask: 'menu_learning_tools',
        grammar: 'menu_learning_tools',
        WXGZPT: 'menu_learning_tools',
        'zdt.php': 'menu_learning_tools'
    };
    var cpss = $('#cpss_domain'), url;
    for (var i in highlight) {
        if (window.location.href.indexOf(i) !== -1) {
            $('#' + highlight[i]).addClass('header_current_v400');
        }
    }
    var logout = function (url, type) {
        var logout;
        $.ajax({
            url: url,
            type: 'get',
            dataType: type,
            data: {},
            success: function (result) {
                if (result.code === 200) {
                    var master = $('#master_domain').val();
                    // $("#To_logout_js").html(result.status);
                    // if (master) {
                    //     logout = master + 'main.php/User/Login/log_out';
                    // } else {
                    //     logout = '/main.php/User/Login/log_out';
                    // }
                    setTimeout(function () {
                        if (master) {
                            window.location = master;
                        } else {
                            window.location.reload();
                        }
                    }, 1000);
                } else {
                    window.location.reload();
                }
            }
        });
    };

    $('#logout').on('click', function () {
        if (cpss.length > 0) {
            url = cpss.val() + 'api/user/logout';
            type = 'jsonp';
        } else {
            url = '/api/user/logout';
            type = 'json';
        }
        logout(url, type);
    });

    var load = function (url, type) {
        $.ajax({
            url: url,
            type: 'get',
            dataType: type,
            data: {},
            success: function (data) {
                if (data.code === 200) {
                    data = data.data;
                    window.cpss = {user: data};
                    var loginPanel = $('.login_v400');
                    loginPanel.show();
                    loginPanel.find('#user_name').html(data.user_name);
                    loginPanel.find('#zao_yuan').html('早元：' + data.currency.yuan);
                    loginPanel.find('#zao_point').html('早点：' + data.currency.point);
                    loginPanel.find('#header_avatar').attr('src', data.avatar);
                    if (data.is_teacher) {
                        loginPanel.find('#is_teacher').show();
                    }
                    var unpaid = parseInt(data.unpaid), total = 0;
                    var sms = parseInt(data.sms);
                    if (unpaid > 0) {
                        total += unpaid;
                        unpaid = unpaid > 99 ? '99' : unpaid;
                        loginPanel.find('#order_unpaid').html('<i id="unpaid_num" class="pop_v400">' + unpaid + '</i>');
                    }
                    if (sms > 0) {
                        total += sms;
                        sms = sms > 99 ? '99' : sms;
                        loginPanel.find('#sms_unread').html('<i id="unread_num" class="pop_v400">' + sms + '</i>');
                    }
                    if (total > 0) {
                        total = total > 99 ? '99' : total;
                        loginPanel.find('#total_tips').addClass('m_news').html(total);
                    }
                    loginPanel.find('#ava_score').html('<i class="credit_icon_v400"></i>' + data.score);
                } else {
                    $('.login_go_v400').show();
                }
            }
        });
    };
    //设置客服电话
    var loadZd = function (url, type) {
        $.ajax({
            url: url,
            type: 'get',
            dataType: type,
            data: {},
            success: function (ret) {
                if (ret.code === 200) {
                    var data = ret.data;
                    var area = data.phone.area;
                    var main = data.phone.main;
                    $('#qc_phone').html(area + '-' + main);
                    $('#area_code').html(area);
                    $('#main_phone').html(main);
                }
            }
        });
    };
    var noticeDom = $('#campus_notice');
    //校园公告数据
    var loadNotice = function (url, type) {
        var noticeLine = '';
        var noticeLineSurplus = '<div class="nav_up_arrow"></div>';
        $.ajax({
            url: url,
            type: 'get',
            dataType: type,
            data: {},
            success: function (ret) {
                if (ret.code === 200) {
                    if (ret.data.length > 0) {
                        $.each(ret.data, function (index) {
                            noticeLine += '<li class="google_event" ga-type="点击广告位" ga-title="' + ret.data[index].title + '"><a target="_blank" href="' + ret.data[index].link + '">' + ret.data[index].title + '</a></li>';
                            noticeLineSurplus += '<a target="_blank" class="google_event" ga-type="点击广告位" ga-title="' + ret.data[index].title + '" title="' + ret.data[index].title + '" href="' + ret.data[index].link + '">' + ret.data[index].title + '</a>';
                        });
                        noticeDom.find('.line').html(noticeLine);
                        noticeDom.find('.line_surplus').html(noticeLineSurplus);
                        noticeDom.show();
                    }
                }
            }
        });
    };
    var userInfo = function () {
        if (cpss.length > 0) {
            url = cpss.val() + 'api/user/info';
            load(url, 'jsonp');
        } else {
            load('/api/user/info', 'json');
        }
    };

    var qcPhone = function () {
        if (cpss.length > 0) {
            url = cpss.val() + 'api/zaodao/info';
            loadZd(url, 'jsonp');
        } else {
            loadZd('/api/zaodao/info', 'json');
        }
    };
    //加载校园公告
    var notice = function () {
        if (cpss.length > 0) {
            url = cpss.val() + 'api/zaodao/notice';
            loadNotice(url, 'jsonp');
        } else {
            loadNotice('/api/zaodao/notice', 'json');
        }
    };
    userInfo();
    qcPhone();
    if (noticeDom.length > 0) {
        notice();
        var noticeParams = {
            li_h: 20,
            time: 2000,
            movetime: 1000
        };
        var autoani = function () {
            if (noticeDom.find('.line li').length > 1) {
                noticeDom.find('.line li:first').animate({'margin-top': -noticeParams.li_h}, noticeParams.movetime, function () {
                    $(this).css('margin-top', 0).appendTo('.line');
                });
            }
        };
        var timers = setInterval(autoani, noticeParams.time);
        //悬停时停止滑动，离开时继续执行
        noticeDom.hover(function () {
            clearInterval(timers);//清除自动滑动动画
            noticeDom.find('.line_surplus').show();
        }, function () {
            timers = setInterval(autoani, noticeParams.time);//继续执行动画
            noticeDom.find('.line_surplus').hide();
        });
    }
});

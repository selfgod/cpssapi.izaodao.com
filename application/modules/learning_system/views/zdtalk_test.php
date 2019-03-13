<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $model['title']; ?></title>
    <link rel='stylesheet' type='text/css' href='<?php echo SS_DOMAIN . 'Public/zd_win8/Korean_up/css/style.css?v=20180823' ?>'>
</head>
<body>
<div class="alert" style="display: none;">
    <div class="alert-main">
        <div class="alert-close">×</div>
        <h1>检测到您的账号没有设置密码！</h1>
        <p>为了您的账号安全，请设置密码并牢记您的登录密码</p>
        <div class="form">
            <input placeholder="设置6-16位的密码，不支持特殊符号" type="password" id="pwd"/>
            <em class="pwd_tip" style="display:none;">密码为6~16位英文&数字，不支持特殊字符及空格！</em>
        </div>
        <div class="form">
            <input placeholder="请再次确认密码" type="password" id="rep_pwd"/>
            <em class="rep_tip" style="display:none;">两次输入密码不一致！</em>
        </div>
        <a href="javascript:;" class="btn submit_pwd">提 交</a>
    </div>
</div>
<iframe id="trigger_protocol_ifrm" style="display:none"></iframe>
<div class="wrap">
    <div class="link"><a href="javascript:;" id="checkBtn" data-zdtalkurl="<?php echo $model['href']; ?>"></a></div>
    <div class="link2"><a target="_blank" href="//service.izaodao.com/download.do?appId=7b198387d5714e75894500a14ec5ac5e&platform=1" id="downloadBtn"></a></div>
</div>
<input type="hidden" id="method_action" value="<?php echo $model['action']; ?>">
<input type="hidden" id="is_pwd" value="<?php echo $model['is_pwd']; ?>">

<script type="text/javascript" src="<?php echo base_url("/static/lib/jquery-1.9.1.min.js"); ?>"></script>
<script>
    function isValidOS() {
        var p = navigator.platform;
        var isMac = p.indexOf("Mac") == 0;
        var isLinux = (p == "X11") || (p.indexOf("Linux") == 0);
        return !(isMac || isLinux);
    }

    function download() {
        window.open('//service.izaodao.com/download.do?appId=7b198387d5714e75894500a14ec5ac5e&platform=1');
    }

    function startZDTalk(data) {
        var url = data;
        launchApplication(url, function () {
        }, function () {
            alert('您的电脑没有安装早道网校上课软件，请下载安装');
        });
    }
    function done() {
        isDone = true;
        assistEl.onblur = null;
        triggerEl.onerror = null;
        clearTimeout(timeout);
    }

    function launchApplication(url, success, fail) {
        if (!isDone)return;
        isDone = false;

        assistEl.focus();
        assistEl.onblur = function () {
            if (document.activeElement && document.activeElement !== assistEl) {
                assistEl.focus();
            } else {
                console.log('start zdtalk success');
                done();
                success();
                return;
            }
        };

        triggerEl.onerror = function () {
            done();
            fail();
        };


        try {
            triggerEl.src = url;
        } catch (e) {
            done();
            fail();
            return;
        }


        timeout = setTimeout(function () {
            console.log('time out');
            done();
            fail();
        }, 4000);
    }
    function getZdTalkUrl() {
        if (!isDone)return;
        $.ajax({
            type: "GET",
            url: "/zdtalk/classroom",
            data: {'action': $('#method_action').val()},
            dataType: "json",
            success: function (obj) {
                if (obj.code === 200) {
                    startZDTalk(obj.href);
                } else {
                    alert('没有即将开始或正在进行的课程！');
                }
            }
        });
    }
    
    function updatePwd(pwd) {
      if (subLock) return false;
      subLock = true;
      $.ajax({
        type: "POST",
        url: "/zdtalk/updatePwd",
        data: { pwd: pwd },
        dataType: "json",
        success: function (obj) {
          if (obj.code === 200) {
            location.replace(location.href)
          } else {
            $('.alert').hide();
            var msg = obj.msg || '提交失败';
            alert(msg);
          }
        }
      });
    }
    var assistEl = document.getElementById('checkBtn');
    var downloadBtn = document.getElementById('downloadBtn');
    var triggerEl = document.getElementById('trigger_protocol_ifrm');
    if (!isValidOS()) {
        assistEl.setAttribute('style', 'display:none');
        downloadBtn.setAttribute('style', 'display:none');
        alert('当前操作系统不支持');
    } else {
        var isDone = true;
        var subLock = false;
        var timeout;
    }
    $(function () {
        $('#checkBtn').on('click', function () {
          if (parseInt($('#is_pwd').val(), 10) === 1) {
            var zdtalkurl = $(this).data('zdtalkurl');
            if (zdtalkurl) {
              startZDTalk(zdtalkurl);
            } else {
              getZdTalkUrl();
            }
          } else {
            //弹出层
            $('.alert').show();
          }
        });
      $('.alert-close').on('click', function () {
        $('.alert').hide();
      });
      $('.submit_pwd').on('click', function () {
        $('.pwd_tip').hide();
        $('.rep_tip').hide();
        var regp = /^[A-Za-z0-9!@#$%^&*()]+$/;
        var pwd = $('#pwd').val();
        if (!regp.test(pwd) || pwd.length < 6 || pwd.length > 16) {
          $('.pwd_tip').show();
          return false;
        }
        var rep_pwd = $('#rep_pwd').val();
        if (rep_pwd !== pwd) {
          $('.rep_tip').show();
          return false;
        }
        updatePwd(pwd);
      });
    });
</script>
</body>
</html>

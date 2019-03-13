(function( window, undefined ) {
    var consulting = {
        init:function () {
            this._bindDom();
        },
        _bindDom:function () {
            var self = this;
            self._createLayerDom();
            $(window).scroll(function(){
                if ($(window).scrollTop()>200){
                    $(".pc_300_toparrow").fadeIn(500);
                }else{
                    $(".pc_300_toparrow").fadeOut(500);
                }
            });
            $(".pc_300_toparrow").on('click',function(){
                $('body,html').animate({scrollTop:0},200);
                return false;
            });
        },
        _createLayerDom:function () {
            $("body").append('<div class="dropRight"></div>');
            var temp_height = 0;
            var temp_top = 415;
            var master_domain = $('#master_domain').val();
            //班主任咨询
            $(".dropRight").append('<a id="dropRight_teacher" href="http://wpa.qq.com/msgrd?v=3&uin=800118811&site=qq&menu=yes"  target="_blank" style="color: #fff; font-size: 14px; line-height: 20px; display: block; position: fixed; z-index: 999; right: 10px; text-align: center; height:50px;"><img src="/static/image/public/class_teacher.png"></a>');
            temp_top += temp_height + 5;
            $("#dropRight_teacher").css("top", temp_top);
            temp_height = $("#dropRight_teacher").height();
            //意见反馈

            $(".dropRight").append('<a id="dropRight_feedback_top" href="'+master_domain+'feedback.php" target="_blank" style="width: 35px; height: 40px; color: #fff; padding: 5px; font-size: 14px; line-height: 20px; display: block; position: fixed; z-index: 999; right: 10px; height:40px; text-align: center; background-color: #4fa64c;">意见反馈</a>');
            temp_top += temp_height + 5;
            $("#dropRight_feedback_top").css("top", temp_top);
            temp_height = $("#dropRight_feedback_top").height();
            //返回顶部
            $(".dropRight").append('<div id="dropRight_toparrow" class="pc_300_toparrow" style=" width: 35px; height: 45px; position: fixed; z-index: 999; height:50px; text-align: center; right: 10px; line-height: 20px; padding: 25px 5px 5px 5px; color: #fff; font-size: 14px; cursor: pointer; display: none; background: #ff570d no-repeat center 10px;">返回顶部</div>');
            temp_top += temp_height + 15;
            $("#dropRight_toparrow").css("top", temp_top);
            $("#dropRight_toparrow").height();
        }
    };
    consulting.init();
})(window);
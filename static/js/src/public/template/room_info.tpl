<div class="alret_v422_write">
    <div id="yyNumber_v422">
        <p class="fz20 taC">本课将在 <%= start_time %> 上课</p>
        <div class="yyNumber_v422_text clearfix">
            <i class="flL"><img src="<%= yyimg %>" alt=""/></i>
            <p class="flL">YY频道号：80121</p>
        </div>
        <div class="yyRoom_v422">
            <ul>
                <li class="clearfix">
                    <i class="yyRoom_v422_icon  yyRoom_v422_room"></i>
                    <p class="color_888">上课房间</p>
                    <p class="color_444 ml20"><%= room_name %></p>
                </li>
                <li class="clearfix">
                    <i class="yyRoom_v422_icon  yyRoom_v422_password"></i>
                    <p class="color_888">房间密码</p>
                    <p class="color_444 ml20"><%= room_pwd %></p>
                    <a href="javascript:void(0);" class="color_888 fz14 ml30" id="copy_pwd" data-clipboard-text="<%= room_pwd %>">复制密码</a>
                </li>
            </ul>
        </div>
        <a href="<%= link %>" target="_blank" class="button_lineGreen yyRoom_v422_btn">如何进入教室?</a>
    </div>
</div>

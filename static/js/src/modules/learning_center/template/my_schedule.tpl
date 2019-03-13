<div class="cpss_classlearn_layout clearfix">
    <div class="myclass_major_plans">
        <div id="finished_circle" title="我的进度"></div>
    </div>
    <div class="cpss_classlearn_right">
        <h4 class="color_555 mb10"><%= title%></h4>
        <span class="cpss_classlearn_line">
            <% if (!isRecord) {%><p><i class="cpss_icon_time"></i>上课时间：<%= time_str %></p><%}%>
            <p class="google_event" ga-type="点击班级信息_主讲老师" ga-title="<%= teachers %>"><i class="cpss_icon_teacher"></i>主讲老师：<%= teachers %></p>
            <% if (!isRecord && qq !== '0') {%><p class="google_event" ga-type="点击班级信息_QQ群号"><i class="cpss_icon_qq"></i>班级Q群：<%= qq%></p><%}%>
        </span>
        <span class="cpss_classlearn_line">
            <p class="textOverflow w840">
                <i class="cpss_icon_book"></i>使用教材：
                <% _.forEach(books, function(book){%><a href="<%= book.buy_link%>" class="link_text google_event" ga-type="点击班级信息_使用教材" ga-title="<%=book.name%>" target="_blank"><%=book.name%></a><%});%>
            </p>
        </span>
    </div>
</div>

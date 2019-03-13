<p class="w150 flL pt20">成交价：￥<%= deal_price %></p>
<p class="w150 flL pt20">已激活<%= activate_days %>天</p>
<div class="w150 flL pt20">
    <p><%= expire_str%></p>
    <img src="<%= image %>" class="flL mt08 mr05">
    <p class="w100 fz12 mt05"><%= activate_date%></p>
    <p class="w100 fz12"><%= expire_date%></p>
</div>
<p class="flL color_999 fz12 pt20">
    主修课：<b><%= major%><br>
    口语课：<b><%= oral%><br>
    选修课：<b><%= elective%><br>
    技能课：<b><%= special%><br>
    定制课：<b><%= custom%><br>
</p>
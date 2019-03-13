<div class="myclassv222_date_list_title">
    <p class="myclassv222_date_list_titleL flL" id="last_month" data-year="<%= last_year %>" data-month="<%= last_month %>"><</p>
    <p class="myclassv222_date_list_titleC flL"><%= current_month_val %></p>
    <p class="myclassv222_date_list_titleR flL" id="next_month" data-year="<%= next_year %>" data-month="<%= next_month %>">></p>
</div>
<div class="myclassv222_date_week">
    <ul class="clearfix pt12">
        <li>日</li>
        <li>一</li>
        <li>二</li>
        <li>三</li>
        <li>四</li>
        <li>五</li>
        <li class="mr00">六</li>
    </ul>
</div>
<div class="myclassv222_date_day">
    <ul class="clearfix">
        <%= month_day %>
    </ul>
</div>
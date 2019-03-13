<!-- 主容器 -->
<div class="main clearfix">
    <p class="fz18 boB_e5e5e5 pb20">升级优惠核算</p>
    <div class="boB_e5e5e5 pb20">
        <div class="main_update mt20 mb10">
            <img src="<?php echo img_url('upgrade', 'cart.png')?>">
            <span>已购商品：<?php echo $model['origin']['name'];?></span>
            <span class="flR pr20">成交价：<i class="fz18">￥<?php echo $model['origin']['deal_price'];?></i></span>
        </div>
        <div class="main_update current">
            <img src="<?php echo img_url('upgrade', 'update.png')?>">
            <span>升级目标：<?php echo $model['target']['name'];?></span>
            <span class="flR pr20">优惠价：<i class="fz18">￥<?php echo $model['target']['discount_price'];?></i></span>
        </div>
    </div>
    <!-- 价格核算 开始 -->
    <div class="main_price mt30 flR pb30">
        <div>
            <p>
                <span>升级福利：</span>
                <i id="upgrade_discount">&nbsp;</i>
                <b class="color_666" id="upgrade_discount_money">-￥0</b>
            </p>
            <p id="yhq_selector" style="display: none">
                <span>活动券：</span>
                <i><select id="yhq">
                        <option value="0">请选择优惠券</option>
                    </select></i>
                <b id="yhq_money">-￥0</b>
            </p>
            <p id="lq_selector" style="display: none">
                <span>礼　券：</span>
                <i><select id="lq">
                        <option value="0">请选择礼券</option>
                    </select></i>
                <b id="lq_money">-￥0</b>
            </p>

            <p id="manjian"></p>
            <div class="mb10 boB_e5e5e5"></div>
        </div>
        <div id="pre_order" class="hide">
            <p class="taR color_666">
                应付：<em id="pre_price" class="fz20 color_fc7e03">￥0</em>
            </p>
            <p class="<?php if($model['user']['currency']['yuan']==0) echo ' hide';?> taR color_666">
                早元抵扣：<em id="yuan" class="color_fc7e03">-￥0</em>
            </p>
            <div class="mb10 boB_e5e5e5"></div>
        </div>
        <div id="off_price_body" class="hide">
            <p>
                <span>升级折扣：</span>
                <i>老学员升级享9折优惠</i>
                <b id="off_price">-￥0</b>
            </p>
            <div class="mb10 boB_e5e5e5"></div>
        </div>
        <p>
            <span>真实姓名：</span>
            <input id="real_name" type="text" name="name" placeholder="请输入真实姓名" value="<?php echo $model['user']['realname'];?>">
        </p>
        <p>
            <span>联系电话：</span>
            <input id="mobile" type="text" name="mobile" placeholder="请填写联系电话，方便老师和您联系" value="<?php echo $model['user']['mobile'];?>">
        </p>
        <div class="mb10 boB_e5e5e5"></div>
        <p class="taR color_888">
            <label><input id="protocol" type="checkbox">我已仔细阅读《<a class="color_444 tdU" href="<?php echo $model['protocol'];?>" target="_blank">上课协议</a>》并同意协议规定内容</label>
        </p>
        <p class="taR color_666">
            实际支付：<em class="fz28 color_fc7e03" id="pay_price">￥0</em>
        </p>
        <p class="clearfix"><a id="submit" class="signUp yellow mt10" target="_blank">确认升级</a></p>
        <!-- 清除浮动 报错开始 -->
        <p id="warning" class="color_ff0000"></p>
        <!-- 报错结束 -->
    </div>
    <!-- 价格核算 结束 -->
</div>
<input type="hidden" id="origin_goods" value="<?php echo $model['origin']['id'];?>">
<input type="hidden" id="remain_zy" value="<?php echo $model['user']['currency']['yuan'];?>">

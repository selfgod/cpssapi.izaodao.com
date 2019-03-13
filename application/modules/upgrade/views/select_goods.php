<div class="main clearfix">
    <p class="fz18 boB_e5e5e5 pb20">升级商品</p>
    <div class="update_th clearfix">
        <p class="w130 flL">&nbsp;</p>
        <p class="w315 flL">商品信息</p>
        <p class="w150 flL">金额</p>
        <p class="w150 flL">激活状态</p>
        <p class="w150 flL">有效期</p>
        <p class="flL">学习次数</p>
    </div>
    <div class="update_td pb20 clearfix">
        <p class="w110 pt20 pl20 flL"><img src="<?php echo img_url('upgrade', 'cart.png');?>" class="pr05">已购商品：</p>
        <div class="w315 flL">
            <select id="purchased_goods_list">
                <option value="0">请选择</option>
            </select>
            <p id="purchased_warning" class="color_fc7e03" style="display: none">请选择现已购商品！</p>
        </div>
        <div id="goods_detail"></div>
    </div>
    <div class="update_td current pb20 clearfix">
        <p class="w110 pt20 pl20 flL"><img src="<?php echo img_url('upgrade', 'update.png');?>" class="pr05">升级目标：</p>
        <div class="w315 flL">
            <select id="upgrade_goods_list">
                <option value="0">请选择</option>
            </select>
            <p id="upgrade_warning" class="color_fc7e03" style="display: none">请选择将要升级的目标商品！</p>
            <p id="none_warning" class="color_fc7e03" style="display: none">没有可升级的目标商品！</p>
        </div>
        <div id="upgrade_detail"></div>
    </div>
    <button id="accounting" class="update_btn change-yellow mt20">查看升级优惠</button>
</div>
<input type="hidden" id="pre_goods" value="<?php echo $model['goodsId'];?>">

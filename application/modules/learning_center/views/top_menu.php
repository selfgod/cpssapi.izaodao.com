<!--<div class="cpss_upgrade_ticp">学习中心将于6月14日11:30进行全新升级 <a href="http://www.izaodao.com/forum.php?mod=viewthread&tid=17836&extra=page%3D1 " target="_blank" class="cpss_upgrade_link">查看详情></a></div>-->
<!-- 用户栏 开始 -->
<div class="cpss_menu green_bg" xmlns="http://www.w3.org/1999/html">
    <div class="cpss_user ga">
        <!-- 左侧用户中心 开始 -->
        <span class="cpss_avatar_pic flL google_event" ga-location="学习中心首页" ga-type="点击头像"><img src="<?php echo $model['user_info']['headimgurl'];?>" alt=""></span>
        <div class="cpss_user_grade mt15 ml20 flL">
            <h1 class="clearfix">
                <p class="flL color_fff google_event" ga-location="学习中心首页" ga-type="点击用户名"><?php echo $model['user_info']['username'];?></p>
                <i class="flL mt09 ml10 google_event" ga-location="学习中心首页" ga-type="点击身份标识">
                <?php if(intval($model['user_level'])==2):?>
                    <img src="<?php echo img_url('learning_center','vip_icon_01.png'); ?>" alt="">
                <?php elseif(intval($model['user_level'])==3):?>
                    <img src="<?php echo img_url('learning_center','svip_icon_01.png'); ?>" alt="">
                <?php elseif(intval($model['user_level'])==4):?>
                    <img src="<?php echo img_url('learning_center','forever_icon_01.png'); ?>" alt="">
                <?php endif;?>
                </i>
            </h1>
            <p class="mt20">你享有早道 <b class="color_4bb866"><?php echo intval($model['user_privilege_count']);?>项</b> 特权</p>
            <!-- 特权icon 开始 -->
            <div class="cpss_power clearfix">
                <!-- 特权图标 -->
                <?php if(!empty($model['privilege'])):
                    foreach($model['privilege'] as $k=>$v):
                ?>
                <a<?php if(!isset($model['is_self']) || !$model['is_self']):?> target="_blank"<?php endif;?> href="/privilege/#point<?php echo $k;?>" title="<?php echo $v['name'];?>" ga-location="学习中心首页" ga-type="点击特权图标" ga-title="<?php echo $v['name'];?>" class="google_event">
                    <?php if(!empty($model['user_privilege_ids']) && in_array($v['id'],$model['user_privilege_ids'])):?>
                        <img src="<?php echo $v['small_img'];?>" alt="">
                    <?php else:?>
                        <img src="<?php echo $v['small_off_img'];?>" alt="">
                    <?php endif;?>
                </a>
                <?php endforeach;endif;?>
            </div>
            <!-- 特权icon 结束 -->
        </div>
    </div>
</div>
<div class="white_bg_borB1px"></div>
<!-- 用户栏 结束 -->
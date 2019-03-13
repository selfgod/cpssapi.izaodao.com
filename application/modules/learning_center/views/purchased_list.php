<div class="cpss_module mt30 clearfix">
    <h2 class="cpss_icon_02">已购商品</h2>
    <?php foreach ($model['goodsInfo'] as $id => $goods):?>
        <div class="cpss_sortItem mt20 mb30">
            <h4><?php echo $goods['name'];?><b class="cpss_tips_right"><?php if(!empty($goods['upgrade_from'])) echo '由【'.$goods['upgrade_from'].'】升级而来';?></b></h4>
            <span class="cpss_msg_table <?php if($goods['status'] != 0 && $goods['status'] != 1) echo 'mb20';?>">
            <p>商品状态：<?php if($goods['status'] == 0):?><b class="color_ff0000">未激活</b>
            <?php elseif($goods['status'] == 1):?><b class="color_ff0000">已过期</b><?php else:?>已激活<?php endif;?></p>
            <p class="ml20">上课协议：<a href="<?php echo $goods['protocol'];?>" class="<?php if($goods['signed']==1) echo 'color_4bb866'; else echo 'color_ff0000';?> tdU" target="_blank"><?php if($goods['signed']==1) echo '已'; else echo '未';?>签订</a></p>
            <p class="ml20"><?php if($goods['status'] == 0):?>报名时间：<?php echo date('Y/m/d', $goods['create_time']);?> 剩余激活时间<?php echo $goods['remain']?>天
                <?php elseif($goods['status'] == 1):?>已于<?php echo date('Y年m月d日', $goods['expire']);?>过期<?php else:?>
                有效期：<?php if(empty($goods['unlimit_expire'])):?>
                        <?php $remainStr = !empty($goods['remain'][0])?$goods['remain'][0].'年':'';if(!empty($goods['remain'][1]))$remainStr.=$goods['remain'][1].'天';echo date('Y/m/d', $goods['activate_time']).'-'.date('Y/m/d', $goods['expire']).' 剩余'.$remainStr;?><?php else:?>终身<?php endif;?>
                    <?php endif;?></p>
            </span><?php if($goods['status'] == 0):?><p class="cpss_msg_red">从报名之日起算，你有<?php echo $model['extension_card_days'];?>天的激活选择期，若<?php echo $model['extension_card_days'];?>天内未激活，系统将为你自动激活。激活后可以开始学习。</p>
                <?php elseif ($goods['status'] == 1):?><p class="cpss_msg_red">过期后，剩余次数无法继续使用，建议重新报名。</p><?php endif;?>
            <div class="cpss_sortTable clearfix">
                <ul>
                    <li class="color_999">统计</li>
                    <li>主修课</li>
                    <li>口语课</li>
                    <li>选修课</li>
                    <li>技能课</li>
                    <li class="borderRight00">定制课</li>
                </ul>
                <ul>
                    <li>总次数</li>
                    <?php for($i=1;$i<6;$i++):?>
                        <li <?php if($i===5) echo 'class="borderRight00"';?>><?php if(isset($goods['count_list'][$i]['is_unlimit'])) echo '不限'; elseif(isset($goods['count_list'][$i]['total'])) echo $goods['count_list'][$i]['total']; else echo 0;?></li>
                    <?php endfor;?>
                </ul>
                <ul>
                    <li>已用次数</li>
                    <?php for($i=1;$i<6;$i++):?>
                        <li <?php if($i===5) echo 'class="borderRight00"';?>><?php if(isset($goods['count_list'][$i]['is_unlimit'])) echo '--'; elseif(isset($goods['count_list'][$i]['used'])) echo $goods['count_list'][$i]['used']; else echo 0;?></li>
                    <?php endfor;?>
                </ul>
                <ul>
                    <li class="borderBottom00">剩余次数</li>
                    <?php for($i=1;$i<6;$i++):?>
                        <li class="borderBottom00<?php if($i===5) echo ' borderRight00';?>"><?php if(isset($goods['count_list'][$i]['is_unlimit'])) echo '不限'; elseif(isset($goods['count_list'][$i]['remain'])) echo $goods['count_list'][$i]['remain']; else echo 0;?></li>
                    <?php endfor;?>
                </ul>
            </div>
            <?php if($goods['formal'] === TRUE):?>
            <div class="cpss_tips_btn clearfix">
                <?php if($goods['status'] == 0 && $goods['can_activate']):?>
                <a href="javascript:void(0);" class="button_ff6e1f activate flR" data-id="<?php echo $id;?>" data-last="<?php echo $goods['last_day'];?>" data-name="<?php echo $goods['name'];?>">现在激活</a>
                <?php if($goods['can_exp']):?>
                    <a href="<?php echo LINK_HOSTS_JP;?>main.php/Course/Renewal?goodsId=<?php echo $id;?>" class="button_ff6e1f flR mr10">续费延期</a>
                <?php endif;?>
                <?php elseif($goods['status'] == 1):?>
                <a target="_blank" href="<?php echo MASTER_DOMAIN;?>" class="button_line_d5d5d5 bg_fff w90 flR">去购买</a>
                <?php if($goods['can_exp']):?>
                    <a href="<?php echo LINK_HOSTS_JP;?>main.php/Course/Renewal?goodsId=<?php echo $id;?>" class="button_ff6e1f flR mr10">续费延期</a>
                <?php endif;?>
                <?php else:?>
                    <?php if(isset($goods['upgradeable'])):?>
                    <a href="/upgrade?goods=<?php echo $id;?>" class="button_ff6e1f flR" target="_blank">去升级</a>
                    <?php endif;?>
                    <?php if($goods['can_exp']):?>
                        <a href="<?php echo LINK_HOSTS_JP;?>main.php/Course/Renewal?goodsId=<?php echo $id;?>" class="button_ff6e1f flR mr10">续费延期</a>
                    <?php endif;?>
                    <a href="/study/#/myCourse" class="button_green mr10 flR">开始学习</a>
                <?php endif;?>
            </div>
            <?php endif;?>
        </div>
    <?php endforeach;?>
</div>
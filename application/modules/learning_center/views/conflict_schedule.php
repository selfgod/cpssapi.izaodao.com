<?php if(!empty($lab)):?>
<ul>
    <li class="pt15 pb15">和以下课程时间冲突！</li>
    <?php foreach ($lab as $key=>$val):?>
    <li class="pt15 pb15 clearfix borderBottom00 conflict_opt" data-id="<?php echo $val['id'];?>">
        <span class="flL">
            <p><?php echo $val['show_name'];?></p>
            <p class="color_999"><?php echo $val['teacher_info'];?></p>
        </span>
        <i class="cpss_delete_icon flR mt05 curP"><img src="<?php echo img_url('public','cpss_delete_icon.png'); ?>" alt=""></i>
    </li>
    <?php endforeach;?>
</ul>
<?php endif;?>
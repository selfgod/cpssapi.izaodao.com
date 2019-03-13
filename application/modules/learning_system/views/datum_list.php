<div class="downloadItem">
    <ul>
        <?php foreach ($model['data'] as $i => $datum):?>
            <li<?php if($i !== 0) echo ' class="borderTop00"';?>>
                    <span class="downloadItem_icon">
                        <i class="<?php echo $meta['icons'][$datum['download_type']]['class']?>"></i>
                        <p class="taC pl10 pr10 textOverflow">
                            <?php echo $meta['icons'][$datum['download_type']]['title'];?>
                        </p>
                    </span>
                <span class="downloadItem_text"><?php echo $datum['names']?></span>
                <span class="downloadItem_primary">
                    <a href="<?php echo $datum['tfp_url'];?>" class="button_green btnPrimary_v422 mt20" ga-type="点击资料下载" ga-title="<?php echo $datum['names']?>">下 载</a>
                    <a target="_blank" class="notOpen_v422" href="<?php echo $meta['help_link'];?>">下载资料打不开</a>
                </span>
            </li>
        <?php endforeach;?>
    </ul>
</div>
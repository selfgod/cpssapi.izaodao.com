<div <?php if ($model['is_reserved']): ?>
        class="content"
    <?php else: ?>
        class="contents"
    <?php endif; ?>
>
    <div class="video" id="v3VadioContainer">
        <div id="video">
            <div id="a1">
                <div class="cyberplayer" id="playercontainer"></div>
            </div>
        </div>
        <?php if($model['show_list']){ ?>
        <div class="side">
            <div class="arrow show"></div>
            <div class="sidebar show">
                <ul style="display: block">
                    <?php
                    foreach ($model['list'] as $k=>$v){
                        echo '<li ';
                        if($v['id'] == $model['lesson_id']){ echo 'class="active"';}
                        echo '><a href="'.LINK_HOST_CPSS_JP.'learningsystem/review/'.$v['id'].'?schedule='.$model['schedule_id'].'">'.$v['name'].'</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
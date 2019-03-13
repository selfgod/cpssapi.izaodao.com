<?php if ($model['is_reserved']): ?>
    <div class="footer">
        <?php if ($model['checkedIn']): ?>
            <a href="javascript:void(0)" class="btn already">已报到</a>
        <?php else: ?>
            <p class="tip"></p>
            <a href="javascript:void(0)" class="btn ga" ga-location="学习中心（日语）_看录播课" ga-type="点击报到"
               ga-title="<?php echo $model['title']; ?>">报到</a>
        <?php endif; ?>
        <a class="questions_btn" href="<?php echo LINK_HOST_KNOW; ?>question/ask.do" target="_blank">我要提问</a>
    </div>
<?php endif; ?>


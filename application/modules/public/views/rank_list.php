<?php foreach ($model['list'] as $uid => $detail): ?>
    <div class="learnMain_v422 clearfix">
        <span class="ranking_student_list">
            <div class="avatar_pic_v422 flL">
                <img src="<?php echo $detail['avatar']; ?>" alt=""/>
            </div>

            <div class="new_student">
                <span class="mb05">
                    <?php if (isset($detail['title'])): ?>
                        <p class="color_f9b600 block mr05 taL flL"><?php echo $detail['title']; ?></p>
                    <?php endif; ?>
                    <?php if (isset($detail['level']) && $detail['level']): ?>
                        <i class="new_student_icon"><img src="<?php echo $detail['level']; ?>" alt=""></i>
                    <?php endif; ?>
                </span>
                <p<?php if (!isset($detail['title'])): ?> class="mt10"<?php endif; ?>>
                    <b class="new_student_text w115">
                         <?php echo $detail['name']; ?>
                    </b>
                </p>
            </div>
        </span>
        <span class="ranking_credit_list">
            <p class="color_ccc">学分</p>
            <p class="mt05"><?php echo $detail['score']; ?><p>
        </span>
    </div>
<?php endforeach; ?>

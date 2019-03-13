<div<?php if (!empty($meta['id'])): ?> id="<?php echo $meta['id']; ?>"<?php endif; ?><?php if (!empty($meta['class'])): ?> class="<?php echo $meta['class']; ?>"<?php endif; ?>>
    <?php if (!empty($meta['image'])): ?><img src="<?php echo $meta['image']; ?>"><?php endif; ?>
    <?php if (!empty($meta['content'])): ?><?php echo $meta['content']; ?><?php endif; ?>
    <?php if (!empty($meta['button_name'])): ?>
        <a href="<?php if (!empty($meta['button_href'])) {
            echo $meta['button_href'];
        } else {
            echo 'javascript:;';
        } ?>"
            <?php if (!empty($meta['button_target'])): ?> target="<?php echo $meta['button_target'];?>"<?php endif; ?>

            <?php if (!empty($meta['button_class'])): ?> class="<?php echo $meta['button_class']; ?>"<?php endif; ?>
            <?php if (!empty($meta['ga_location'])): ?> ga-location="<?php echo $meta['ga_location']; ?>"<?php endif; ?>
            <?php if (!empty($meta['ga_type'])): ?> ga-type="<?php echo $meta['ga_type']; ?>"<?php endif; ?>
            <?php if (!empty($meta['ga_title'])): ?> ga-title="<?php echo $meta['ga_title']; ?>"<?php endif; ?>
        >
            <?php if (!empty($meta['button_name'])) echo $meta['button_name']; ?>
        </a>
    <?php endif; ?>
</div>
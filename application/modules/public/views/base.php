<div <?php if(isset($meta['id'])) echo 'id = "' . $meta['id'].'"';?> <?php if(isset($meta['class'])) echo 'class = "'.$meta['class'].'"';?>>
    <?php echo $content;?>
</div>
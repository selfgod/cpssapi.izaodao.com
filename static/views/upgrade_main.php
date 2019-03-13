<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $meta['title'];?></title>
    <link rel="shortcut icon" href="<?php echo IZAODAO_CONFIG_FAVICON_URL;?>">
    <link rel='stylesheet' type='text/css' href='<?php echo SS_DOMAIN . 'Public/zd_win8/common/css/cpss_base.min.css?v=201708021200' ?>'>
    <link rel='stylesheet' type='text/css' href="<?php echo RESOURCE_QINIU;?>lib/layer/skin/izaodao5/style.css?v=201708140930" id="layuicss-skinizaodao5stylecss">
    <?php if(isset($meta['css'])):?>
    <?php foreach ($meta['css'] as $css):?>
        <link rel='stylesheet' type='text/css' href='<?php echo $css;?>'>
    <?php endforeach;?>
    <?php endif;?>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', '<?php echo GA_ID;?>', 'auto');
        ga('send', 'pageview');
    </script>
    <script type="text/javascript" src="<?php echo base_url('/static/lib/jq_lo_asy_cli_sli_ech.js'); ?>"></script>
</head>
<body>
<?php echo $content;?>
<input type="hidden" id="master_domain" value="<?php echo MASTER_DOMAIN;?>">
<script type="text/javascript" src="<?php echo RESOURCE_QINIU . 'topnav/js/header.js?v=2017051002'; ?>"></script>
<script type="text/javascript" src="<?php echo RESOURCE_QINIU . 'lib/layer/layer.js'; ?>"></script>
<?php if(isset($meta['js'])):?>
    <?php foreach ($meta['js'] as $js):?>
        <script src="<?php echo $js;?>"></script>
    <?php endforeach;?>
<?php endif;?>
</body>
</html>
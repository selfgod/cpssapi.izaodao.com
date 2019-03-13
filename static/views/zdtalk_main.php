<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $meta['title'];?></title>
    <link rel="shortcut icon" href="<?php echo IZAODAO_CONFIG_FAVICON_URL;?>">
    <link rel='stylesheet' type='text/css' href='<?php echo SS_DOMAIN . 'Public/zd_win8/common/css/cpss_base.min.css?v=201708021200' ?>'>
    <link rel='stylesheet' type='text/css' href='<?php echo base_url("/static/lib/header/css/learning_center_header.css?v=2017080201"); ?>'>
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
        window.onload = function () {
            //清除谷歌广告
            var wlanHj = document.getElementById('wlan_hj');
            if (typeof (wlanHj) != 'undefined' && wlanHj != null) {
                document.body.removeChild(wlanHj);
            }
        };
    </script>
</head>
<body class="bg_eee">
<?php echo $content;?>
<script type="text/javascript" src="<?php echo base_url("/static/lib/jq_lo.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url("/static/lib/header/header.js?v=201708101430"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url("/static/lib/dialog/lhgdialog.min.js?skin=white"); ?>"></script>
<?php if(isset($meta['js'])):?>
    <?php foreach ($meta['js'] as $js):?>
        <script src="<?php echo $js;?>"></script>
    <?php endforeach;?>
<?php endif;?>
</body>
</html>
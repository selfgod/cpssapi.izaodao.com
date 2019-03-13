<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $meta['title'];?></title>
    <link rel="shortcut icon" href="<?php echo IZAODAO_CONFIG_FAVICON_URL;?>">
    <link rel='stylesheet' type='text/css' href='<?php echo SS_DOMAIN . 'Public/zd_win8/common/css/cpss_base.min.css?v=201708211010' ?>'>
    <link rel='stylesheet' type='text/css' href='<?php echo base_url("/static/lib/header/css/learning_center_header.css?v=201708241500"); ?>'>
    <link rel='stylesheet' type='text/css' href='<?php echo SS_DOMAIN . 'Public/zd_win8/footer/css/learning_footer.css?v=201708141730'; ?>'>
    <link rel='stylesheet' type='text/css' href="<?php echo RESOURCE_QINIU;?>lib/layer/skin/izaodao5/style.css?v=201708140930" id="layuicss-skinizaodao5stylecss">
    <?php if(isset($meta['css'])):?>
        <?php foreach ($meta['css'] as $css):?>
            <link rel='stylesheet' type='text/css' href='<?php echo $css;?>'>
        <?php endforeach;?>
    <?php endif;?>
    <script>
        (function (window) {
            var def_version = 8;
            if ((navigator.userAgent.indexOf('MSIE') >= 0)
                && (navigator.userAgent.indexOf('Opera') < 0)) {
                var b_version = navigator.appVersion;
                var version = b_version.split(';');
                if (version.length > 1) {
                    var trim_Version = parseInt(version[1].replace(/[ ]/g, '').replace(/MSIE/g, ''), 10);
                    if (trim_Version <= def_version) {
                        window.location.href = '<?php echo LINK_HOST_MAIN;?>' + 'outdatebrowser';
                    }
                }
            }
        })(window);
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
<input type="hidden" id="master_domain" value="<?php echo MASTER_DOMAIN;?>">
<input type="hidden" id="main_domain" value="<?php echo LINK_HOST_MAIN;?>">
<script type="text/javascript" src="<?php echo base_url("/static/lib/jq_lo_asy_cli_sli_ech.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url("/static/lib/header/header.js?v=201708291050"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url("/static/lib/js-hash/Hash.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url("/static/lib/js-hash/jquery.hash.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url("/static/lib/dialog/lhgdialog.min.js?skin=white"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url("/static/lib/jquery.qrcode.min.js"); ?>"></script>
<script type="text/javascript" src="<?php echo RESOURCE_QINIU . 'lib/layer/layer.js'; ?>"></script>
<?php if(isset($meta['js'])):?>
    <?php foreach ($meta['js'] as $js):?>
        <script src="<?php echo $js;?>"></script>
    <?php endforeach;?>
<?php endif;?>
</body>
</html>
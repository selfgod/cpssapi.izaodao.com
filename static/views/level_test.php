<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo $model['grade_exam_name']; ?>_早道日语网校_多语种在线学习平台!</title>
    <link rel="shortcut icon" href="<?php echo IZAODAO_CONFIG_FAVICON_URL;?>">
    <link rel='stylesheet' type='text/css' href='<?php echo SS_DOMAIN . 'Public/zd_win8/common/css/cpss_base.min.css?v=201708071500' ?>'>
    <link rel='stylesheet' type='text/css' href="<?php echo RESOURCE_QINIU;?>lib/layer/skin/izaodao5/style.css?v=201708140930">
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
    </script>
</head>
<body>
<div id="wapper">
<?php echo $content;?>
</div>
<script type="text/javascript" src="<?php echo base_url("/static/lib/jq_lo.js"); ?>"></script>
<script type="text/javascript" src="<?php echo RESOURCE_QINIU . 'topnav/js/header.js?v=2017051002'; ?>"></script>
<script type="text/javascript" src="<?php echo RESOURCE_QINIU . 'lib/layer/layer.js'; ?>"></script>
<?php if(isset($meta['js'])):?>
    <?php foreach ($meta['js'] as $js):?>
        <script src="<?php echo $js;?>"></script>
    <?php endforeach;?>
<?php endif;?>
</body>
</html>
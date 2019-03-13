<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo $model['title']; ?>_早道日语网校，多语种在线学习平台</title>
    <link rel="shortcut icon" href="<?php echo IZAODAO_CONFIG_FAVICON_URL;?>">
    <link rel='stylesheet' type='text/css' href='<?php echo SS_DOMAIN . 'Public/zd_win8/common/css/cpss_base.min.css?v=20170802' ?>'>
    <link rel='stylesheet' type='text/css' href="<?php echo RESOURCE_QINIU;?>lib/layer/skin/izaodao5/style.css?v=201708140930" id="layuicss-skinizaodao5stylecss">
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', '<?php echo GA_ID;?>', 'auto');
        ga('send', 'pageview');
    </script>
</head>
<body>
<?php echo $content;?>
<input type="hidden" id="plan_id" value="<?php if(isset($model['plan_id'])) echo $model['plan_id']; ?>">
<input type="hidden" id="plan_stage_id" value="<?php if(isset($model['plan_stage_id'])) echo $model['plan_stage_id']; ?>">
<input type="hidden" id="lesson_id" value="<?php if(isset($model['lesson_id'])) echo $model['lesson_id']; ?>">
<input type="hidden" id="schedule_id" value="<?php if(isset($model['schedule_id'])) echo $model['schedule_id']; ?>">
<input type="hidden" id="link" value="<?php if(isset($model['link'])) echo $model['link']; ?>">
<input type="hidden" id="is_reserved" value="<?php if(isset($model['is_reserved'])) echo $model['is_reserved']; ?>">
<input type="hidden" id="master_domain" value="<?php echo MASTER_DOMAIN;?>">
<input type="hidden" id="cpssapi_url" value="<?php echo LINK_HOST_CPSSAPI;?>">
<script type="text/javascript" src="<?php echo base_url("/static/lib/jq_lo.js"); ?>"></script>
<script type="text/javascript" src="<?php echo RESOURCE_QINIU.'lib/cyberplayer/cyberplayer.js'; ?>"></script>
<script type="text/javascript" src="<?php echo base_url("/static/lib/dialog/lhgdialog.min.js?skin=white"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url("/static/lib/jquery.qrcode.min.js"); ?>"></script>
<script type="text/javascript" src="<?php echo RESOURCE_QINIU . 'lib/layer/layer.js'; ?>"></script>
</body>
</html>
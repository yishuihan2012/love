<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<title>音乐播放</title>

	<link rel="stylesheet" href="/Public/css/skPlayer.scss">
	<script src="/Public/JS/skPlayer.min.js"></script>
</head>
<body>
	<div id="skPlayer"></div>
</body>
<script type="text/javascript">
	var source="<?php echo ($source); ?>";
	var player = new skPlayer({
    autoplay: true,
    //可选项,自动播放,默认为false,true/false
    listshow: true,
    //可选项,列表显示,默认为true,true/false
    mode: 'listloop',
    //可选项,循环模式,默认为'listloop'
    //'listloop',列表循环
    //'singleloop',单曲循环
    music: {
    //必需项,音乐配置
        type: 'cloud',
        //必需项,网易云方式指定填'cloud'
        source: source
        //必需项,网易云音乐歌单id
        //登录网易云网页版,在网页地址中拿到
        // ... playlist?id=317921676
    }
});
</script>
</html>
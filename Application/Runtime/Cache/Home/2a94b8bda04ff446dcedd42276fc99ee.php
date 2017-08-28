<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Our Love Story</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<style type="text/css">
		@font-face {
			font-family: digit;
			src: url('digital-7_mono.ttf') format("truetype");
		}
	</style>
	<link href="/Public/css/default.css" type="text/css" rel="stylesheet">
	<script type="text/javascript" src="/Public/js/jquery.js"></script>
	<script type="text/javascript" src="/Public/js/garden.js"></script>
    <script type="text/javascript" src="/Public/js/functions.js"></script>
    <link rel="stylesheet" href="/Public/css/skPlayer.scss">
	<script src="/Public/JS/skPlayer.min.js"></script>
</head>

<body style="background:url('/Public/images/bg.jpg');">
<div class="signature" style="text-align: center;color:#b31800;font-size:40px;font-weight:700;margin-top:20px;"><?php echo ($data['title']); ?></div >
	<div id="skPlayer" style="position: fixed;top:0px;right:0px;"></div>
	<div id="mainDiv">
		<div id="content">
			<div id="code">
				<span class="comments">/**</span><br />
				<span class="space"/><span class="comments">* We are both Fudan SSers and programmers,</span><br />
				<span class="space"/><span class="comments">* so I write some code to celebrate our 1st anniversary.</span><br />
				<span class="space"/><span class="comments">*/</span><br />
				Boy i = <span class="keyword">new</span> Boy(<span class="string">"hackerzhou"</span>);<br />
				Girl u = <span class="keyword">new</span> Girl(<span class="string">"MaryNee"</span>);<br />
				<span class="comments">// Nov 2, 2010, I told you I love you. </span><br />
				i.love(u);<br />
				<span class="comments">// Luckily, you accepted and became my girlfriend eversince.</span><br />
				u.accepted();<br />
				<span class="comments">// Since then, I miss u every day.</span><br />
				i.miss(u);<br />
				<span class="comments">// And take care of u and our love.</span><br />
				i.takeCareOf(u);<br />
				<span class="comments">// You say that you won't be so easy to marry me.</span><br />
				<span class="comments">// So I keep waiting and I have confidence that you will.</span><br />
				<span class="keyword">boolean</span> isHesitate = <span class="keyword">true</span>;<br />
				<span class="keyword">while</span> (isHesitate) {<br />
				<span class="placeholder"/>i.waitFor(u);<br />
				<span class="placeholder"/><span class="comments">// I think it is an important decision</span><br />
				<span class="placeholder"/><span class="comments">// and you should think it over.</span><br />
				<span class="placeholder"/>isHesitate = u.thinkOver();<br />
				}<br />
				<span class="comments">// After a romantic wedding, we will live happily ever after.</span><br />
				i.marry(u);<br />
				i.liveHappilyWith(u);<br />
			</div>
			<div id="loveHeart">
				<canvas id="garden"></canvas>
				<div id="words">
					<div id="messages">
						<?php echo ($data['woman']); ?>, I have fallen in love with you for
						<div id="elapseClock"></div>
					</div>
					<div id="loveu">
						<?php echo ($data['tell_to_her']); ?><br/>
						<div class="signature">- <?php echo ($data['man']); ?></div>
					</div>
				</div>
			</div>
		</div>

	</div>
	<script type="text/javascript">
		var offsetX = $("#loveHeart").width() / 2;
		var offsetY = $("#loveHeart").height() / 2 - 55;
		var together = new Date();
		var year="<?php echo ($data['meet_time'][0]); ?>";
		var month="<?php echo ($data['meet_time'][1]); ?>";
		var day="<?php echo ($data['meet_time'][2]); ?>";
		var hour="<?php echo ($data['meet_time'][3]); ?>";
		var minute="<?php echo ($data['meet_time'][4]); ?>";
		var second="<?php echo ($data['meet_time'][5]); ?>";
		together.setFullYear(year, month, day);
		together.setHours(minute);
		together.setMinutes(minute);
		together.setSeconds(second);
		together.setMilliseconds(0);
		
		if (!document.createElement('canvas').getContext) {
			var msg = document.createElement("div");
			msg.id = "errorMsg";
			msg.innerHTML = "Your browser doesn't support HTML5!<br/>Recommend use Chrome 14+/IE 9+/Firefox 7+/Safari 4+"; 
			document.body.appendChild(msg);
			$("#code").css("display", "none")
			$("#copyright").css("position", "absolute");
			$("#copyright").css("bottom", "10px");
		    document.execCommand("stop");
		} else {
			setTimeout(function () {
				startHeartAnimation();
			}, 5000);

			timeElapse(together);
			setInterval(function () {
				timeElapse(together);
			}, 500);

			adjustCodePosition();
			$("#code").typewriter();
		}
	</script>

	<!-- 播放器 -->
	<script type="text/javascript">
		var source="<?php echo ($data['source']); ?>";
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
</body>
</html>
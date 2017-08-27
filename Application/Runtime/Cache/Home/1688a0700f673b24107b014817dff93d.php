<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SKPlayer</title>
    <meta name="viewport" content="width=440,user-scalable=no">
    <link rel="stylesheet" href="./dist/skPlayer.min.css">
    <script src="./dist/skPlayer.min.js"></script>

    <style>
        body{
            font-family:Arial,Helvetica,sans-serif;
            color:#6A6B6F;
            text-align:center;
            padding-top:17px;
            width:470px;
            margin:0 auto;
        }
        h1{
            font-size:36px;
        }
        h2{
            font-size:24px;
        }
        p{
            font-size:18px;
        }
        #skPlayer{
            margin:27px auto 0;
        }
        .container{
            margin-top:200px;
        }
        .doc{
            text-align:left;
        }
        pre{
            padding-left:2em;
            font-size:16px;
            line-height:24px;
            color:#fff;
            background-color:#272822;
            border-radius:3px;
        }
        @media screen and (max-width:768px){
            body{
                width:100%;
            }
            .doc{
                display:none;
            }
        }
    </style>
</head>
<body>
    <div id="skPlayer"></div>
    <div class="container">
        <button onclick="player.play()">player.play()</button>
        <button onclick="player.pause()">player.pause()</button>
        <button onclick="player.toggle()">player.toggle()</button>
        <button onclick="player.prev()">player.prev()</button>
        <button onclick="player.next()">player.next()</button>
        <button onclick="player.switchMusic(7)">player.switchMusic(7)</button>
        <button onclick="player.toggleList()">player.toggleList()</button>
        <button onclick="player.toggleMute()">player.toggleMute()</button>
        <button onclick="player.switchMode()">player.switchMode()</button>
    </div>
    <div class="doc">
        <h2>特点</h2>
        <p>1.支持自配置音乐文件 or 指定网易云音乐歌单两种方式</p>
        <p>2.提供大量API支持</p>
        <h2>下载</h2>
        <pre>npm install skplayer --save</pre>
        <p>或者前往github v3.0分支下载整包</p>
        <h2>使用</h2>
        <p>HTML:</p>
        <pre>&lt;div id=&quot;skPlayer&quot;&gt;&lt;/div&gt;</pre>
        <p>JS:</p>
        <pre>&lt;script src=&quot;./dist/skPlayer.min.js&quot;&gt;&lt;/script&gt;</pre>
        <p>或</p>
        <pre>var skPlayer = require('skPlayer');</pre>
        <h2>配置</h2>
        <p>配置网易云音乐歌单方式</p>
        <pre>var player = new skPlayer({<br>    autoplay: true,<br>    //可选项,自动播放,默认为false,true/false<br>    listshow: true,<br>    //可选项,列表显示,默认为true,true/false<br>    mode: 'listloop',<br>    //可选项,循环模式,默认为'listloop'<br>    //'listloop',列表循环<br>    //'singleloop',单曲循环<br>    music: {<br>    //必需项,音乐配置<br>        type: 'cloud',<br>        //必需项,网易云方式指定填'cloud'<br>        source: 317921676<br>        //必需项,网易云音乐歌单id<br>        //登录网易云网页版,在网页地址中拿到<br>        // ... playlist?id=317921676<br>    }<br>});</pre>
        <p>自配置音乐文件方式</p>
        <pre>var player = new skPlayer({<br>     ... <br>    //可选项配置同上<br>    music: {<br>    //必需项,音乐配置<br>        type: 'file',<br>        //必需项,自配置文件方式指定填'file'<br>        source: [<br>        //必需项,音乐文件数组<br>            {<br>                name: '打呼',<br>                //必需项,歌名<br>                author: '潘玮柏&杨丞琳',<br>                //必需项,歌手<br>                src: 'xxx.mp3',<br>                //必需项,音乐文件<br>                cover: 'xxx.jpg'<br>                //必需项,封面图片<br>            },<br>             ... <br>        ]<br>    }<br>});</pre>
        <h2>API</h2>
        <p>播放</p>
        <pre>player.play();</pre>
        <p>暂停</p>
        <pre>player.pause();</pre>
        <p>播放/暂停切换</p>
        <pre>player.toggle();</pre>
        <p>上一首</p>
        <pre>player.prev();</pre>
        <p>下一首</p>
        <pre>player.next();</pre>
        <p>切歌</p>
        <pre>player.switchMusic(index);<br>//index为列表中歌曲前对应的序号</pre>
        <p>列表显示/隐藏切换</p>
        <pre>player.toggleList();</pre>
        <p>静音/正常切换</p>
        <pre>player.toggleMute();</pre>
        <p>单曲循环/列表循环切换</p>
        <pre>player.switchMode();</pre>
        <p>销毁</p>
        <pre>player.destroy();<br>//skPlayer默认只能存在一个实例<br>//需要重新配置时调用该方法销毁当前实例</pre>
        <h2>最后</h2>
        <p>修复了旧版存在的问题，请及时更新v3.0最新版</p>
        <h2>注：</h2>
        <p>由于兼容性问题，移动端暂不支持自动播放和音量控制，同时toggleMute()方法在移动端可能存在异常，以及其他的一些未知BUG</p>
    </div>
    <script src="./dist/skPlayer.min.js"></script>
    <script>
        var player = new skPlayer({
            autoplay: true,
            music: {
                type: 'cloud',
                source: 317921676
            }
        });
    </script>
</body>
</html>
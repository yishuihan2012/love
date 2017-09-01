<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录</title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="shortcut icon" href="favicon.ico"> <link href="/Public/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/Public/css/animate.min.css" rel="stylesheet">
    <link href="/Public/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style media="screen">
    h1.logo-name{
      font-size:60px;padding:20px 0;
      letter-spacing: 10px;
      color:#000;
    }
    </style>
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->
    <script>if(window.top !== window.self){ window.top.location = window.location;}</script>
</head>

<body class="gray-bg">
    <div class="middle-box text-center loginscreen  animated fadeInDown">
        <div>
            <div>
                <h1 class="logo-name"><?php echo C('SYSTEM_TITLE');?></h1>
            </div>
            <h3>欢迎使用管理平台</h3>
                <div class="form-group">
                    <input type="email" id="email" class="form-control" placeholder="用户名" required="">
                </div>
                <div class="form-group">
                    <input type="password" id="password" class="form-control" placeholder="密码" required="">
                </div>
                <button type="button" class="btn btn-primary block full-width m-b">登 录</button>
        </div>
    </div>
    <script src="/Public/js/jquery.min.js?v=2.1.4"></script>
    <script src="/Public/js/bootstrap.min.js?v=3.3.6"></script>
</body>
<script>
    $(document).ready(function() {
        //测试提交，对接程序删除即可
        $(".btn").click(function(){
            //验证
            var t_user = $.trim($("#email").val());
            var t_pass = $.trim($("#password").val());
            if(t_user == '') {
                alert('请输入用户名');
                return;
            }
            if (t_pass == '') {
                alert('请输入密码');
                return;
            }
            $.ajax({
                url:"<?php echo U('Index/checklogin');?>",
                type:'post',
                dataType:'json',
                data:{t_user:t_user,t_pass:t_pass},
                success:function (e) {
                    if(e == 1) {
                        location.href="<?php echo U('Index/login');?>";
                    }else {
                        alert("用户名或密码错误或者被管理员禁用，请联系管理员")
                    }

                }
            });
        });
        $("#password").keydown(function(e){
            var curKey = e.which;
            if(curKey == 13){
                $(".btn").click();
                return false;
            }
        });
        $("#email").keydown(function(e){
            var curKey = e.which;
            if(curKey == 13){
                $(".btn").click();
                return false;
            }
        });
    });


</script>
</html>
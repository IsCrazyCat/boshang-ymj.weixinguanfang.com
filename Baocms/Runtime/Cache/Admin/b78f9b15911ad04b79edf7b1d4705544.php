<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo ($CONFIG["site"]["title"]); ?>管理后台</title>
        <meta name="description" content="<?php echo ($CONFIG["site"]["title"]); ?>管理后台" />
        <meta name="keywords" content="<?php echo ($CONFIG["site"]["title"]); ?>管理后台" />
        <link href="__TMPL__statics/css/pub.css" rel="stylesheet" type="text/css" />
        <script> var BAO_PUBLIC = '__PUBLIC__';
            var BAO_ROOT = '__ROOT__';</script>
        <script src="__PUBLIC__/js/jquery.js"></script>
        <script src="__PUBLIC__/js/my97/WdatePicker.js"></script>
        <script src="__PUBLIC__/js/admin.js"></script>

    </head>
    
    <style type="text/css">
#ie9-warning{ background:#F00; height:38px; line-height:38px; padding:10px;
position:absolute;top:0;left:0;font-size:12px;color:#fff;width:97%;text-align:left; z-index:9999999;}
#ie6-warning a {text-decoration:none; color:#fff !important;}
</style>

<!--[if lte IE 9]>
<div id="ie9-warning">您正在使用 Internet Explorer 9以下的版本，请用谷歌浏览器访问后台、部分浏览器可以开启极速模式访问！</a>
</div>
<script type="text/javascript">
function position_fixed(el, eltop, elleft){  
       // check if this is IE6  
       if(!window.XMLHttpRequest)  
              window.onscroll = function(){  
                     el.style.top = (document.documentElement.scrollTop + eltop)+"px";  
                     el.style.left = (document.documentElement.scrollLeft + elleft)+"px";  
       }  
       else el.style.position = "fixed";  
}
       position_fixed(document.getElementById("ie9-warning"),0, 0);
</script>
<![endif]-->


    <style>
	body{background-color: #eee;}
	</style>
    <body>
        <iframe id="baocms_frm" name="baocms_frm" style="display:none;"></iframe>
        <div class="main_logon">

            <div class="login">
                <div class="title"><?php echo ($CONFIG["site"]["title"]); ?>总后台管理系统</div>

                <form method="post" action="<?php echo U('login/loging');?>" target="baocms_frm" >


                    <table cellpssssadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td width=""><input type="text" name="username" class="loginInput1" placeholder="请输入用户名"/></td>
                        </tr>
                        <tr>
                            <td><input type="password" name="password" class="loginPass" placeholder="请输入密码" /></td>
                        </tr>
                        <tr>
                            <td><input type="text" name="yzm" class="yzm" placeholder="请输入验证码" />
                                <span class="yzm_code" style="margin:2px 0 0px; display:block; cursor:pointer;"><img style="width:60px; height:30px;"  src="__ROOT__/index.php?g=app&m=verify&a=index&mt=<?php echo time();?>" /></span></td>
                        </tr>
                        <tr>
                            <td><input type="submit" class="loginBtn" value="确认登录" /></td>
                        </tr>
                    </table>
                </form> 
                <div class="title"></div>
            </div>
<!--            <p class="copy">技术支持：<?php echo ($CONFIG["site"]["title"]); ?>，此版本支持分站，分站登录地址:<a style="color:#f00" href="<?php echo ($CONFIG["site"]["host"]); ?>/Substation"><?php echo ($CONFIG["site"]["host"]); ?>/Substation</a></p>-->
        </div>		  	   	
        
<script>     
 window.onbeforeunload = function(){
	 					var admin_id = '<?php echo ($admin['admin_id']); ?>';
                        $.post("<?php echo U('login/close2');?>", {admin_id:admin_id}, function (data) {
                                $('#tuan_id').html(res);
                        }, 'json');

    };  
</script>     
</div>
</body>
</html>
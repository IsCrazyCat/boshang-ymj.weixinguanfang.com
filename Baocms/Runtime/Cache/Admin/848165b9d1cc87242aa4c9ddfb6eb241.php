<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo ($CONFIG["site"]["title"]); ?>管理后台</title>
        <meta name="description" content="<?php echo ($CONFIG["site"]["title"]); ?>管理后台" />
        <meta name="keywords" content="<?php echo ($CONFIG["site"]["title"]); ?>管理后台" />
        <!-- <link href="__TMPL__statics/css/index.css" rel="stylesheet" type="text/css" /> -->
        <link href="__TMPL__statics/css/style.css" rel="stylesheet" type="text/css" />
        <link href="__TMPL__statics/css/land.css" rel="stylesheet" type="text/css" />
        <link href="__TMPL__statics/css/pub.css" rel="stylesheet" type="text/css" />
        <link href="__TMPL__statics/css/main.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/js/jquery-ui.css" rel="stylesheet" type="text/css" />
        <script> var BAO_PUBLIC = '__PUBLIC__'; var BAO_ROOT = '__ROOT__'; </script>
        <script src="__PUBLIC__/js/jquery.js"></script>
        <script src="__PUBLIC__/js/jquery-ui.min.js"></script>
        <script src="__PUBLIC__/js/my97/WdatePicker.js"></script>
        <script src="/Public/js/layer/layer.js"></script>
        <script src="__PUBLIC__/js/admin.js?v=20150409"></script>
        <link rel="stylesheet" type="text/css" href="/static/default/webuploader/webuploader.css">
		<script src="/static/default/webuploader/webuploader.min.js"></script>
    </head>
    
    
    </head>
<style type="text/css">
#ie9-warning{ background:#F00; height:38px; line-height:38px; padding:10px;
position:absolute;top:0;left:0;font-size:12px;color:#fff;width:97%;text-align:left; z-index:9999999;}
#ie6-warning a {text-decoration:none; color:#fff !important;}
</style>

<!--[if lte IE 9]>
<div id="ie9-warning">您正在使用 Internet Explorer 9以下的版本，请用谷歌浏览器访问后台、部分浏览器可以开启极速模式访问！不懂点击这里！ <a href="http://www.fengmiyuanma.com/10478.html" target="_blank">查看为什么？</a>
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


    <body>
         <iframe id="baocms_frm" name="baocms_frm" style="display:none;"></iframe>
   <div class="main">

<div class="mainBt">
    <ul>
        <li class="li1">微信</li>
        <li class="li2">微信O2O</li>
        <li class="li2 li3">微信配置</li>
    </ul>
</div>
<div class="main-cate">
    <p class="attention"><span>注意：</span>这里配置是平台微信相关的， 当然 appid 和 appsecret 也影响商户的微信相关，需要微信发红包</p>
</div>       
<div class="mainScAdd">
    <div class="tableBox">
        <form  target="baocms_frm" action="<?php echo U('setting/weixin');?>" method="post">
            <table  bordercolor="#dbdbdb" cellspacing="0" width="100%" border="1px"  style=" border-collapse: collapse; margin:0px; vertical-align:middle; background-color:#FFF;" >
                <tr>
                    <td class="lfTdBt" width="160" >URL(服务器地址)：</td>
                    <td class="rgTdBt">
                        <?php echo ($CONFIG["site"]["host"]); ?>/weixin/index.php
                    </td>
                </tr>
                <tr>
                    <td class="lfTdBt" >TOKEN：</td>
                    <td class="rgTdBt">
                        <input type="text" name="data[token]" value="<?php echo (($CONFIG["weixin"]["token"])?($CONFIG["weixin"]["token"]):''); ?>" class="scAddTextName " />
                        <code>32位的MD5值最适合！但是一般不限制！可以随意填写！这个需要配置到微信公共帐号那边</code>
                    </td>
                </tr>
                <tr>
                    <td class="lfTdBt" >APPID：</td>
                    <td class="rgTdBt">
                        <input type="text" name="data[appid]" value="<?php echo ($CONFIG["weixin"]["appid"]); ?>" class="scAddTextName " />

                    </td>
                </tr>
                <tr>
                    <td  class="lfTdBt" >appsecret：</td>
                    <td class="rgTdBt">
                        <input type="text" name="data[appsecret]" value="<?php echo ($CONFIG["weixin"]["appsecret"]); ?>" class="scAddTextName " />
                    </td>
                </tr>
                <tr>
                    <td class="lfTdBt" >原始ID：</td>
                    <td class="rgTdBt">
                        <input type="text" name="data[id]" value="<?php echo ($CONFIG["weixin"]["id"]); ?>" class="scAddTextName " />
                    </td>
                </tr>
                <tr>
                    <td class="lfTdBt" >关注时回复内容形式：</td>
                    <td class="rgTdBt">
                        <select name="data[type]" class="seleFl  jq_type" style="display: inline-block;">
                            <option <?php if(($CONFIG["weixin"]["type"]) == "1"): ?>selected="selected"<?php endif; ?> value="1">文字</option>
                            <option  <?php if(($CONFIG["weixin"]["type"]) == "2"): ?>selected="selected"<?php endif; ?> value="2">图片</option>
                        </select>
                        <code>如果是文字的话就不需要填写标题和图片了</code>
                    </td>
                </tr>
                <script>
                    $(document).ready(function () {
                        $(".jq_type").change(function () {
                            if ($(this).val() == 1) {
                                $(".jq_type_1").show();
                                $(".jq_type_2").hide();
                            } else {
                                $(".jq_type_2").show();
                                $(".jq_type_1").hide();
                            }
                        });
                        $(".jq_type").change();
                    });
                </script>
                <tr  class="jq_type_1">
                    <td class="lfTdBt" >回复内容：</td>
                    <td class="rgTdBt">
                        <textarea name="data[description]" cols="50" rows="5"><?php echo ($CONFIG["weixin"]["description"]); ?></textarea>
                    </td>
                </tr>
                <tr class="jq_type_2">
                    <td >回复标题：</td>
                    <td class="rgTdBt">
                        <input type="text" name="data[title]" value="<?php echo ($CONFIG["weixin"]["title"]); ?>" class="scAddTextName " />
                        <code>微信被用户关注后，主动向用户发送一条消息的标题</code>
                    </td>
                </tr>
                <tr class="jq_type_2">
                    <td class="lfTdBt" >链接地址：</td>
                    <td class="rgTdBt">
                        <input type="text" name="data[linkurl]" value="<?php echo ($CONFIG["weixin"]["linkurl"]); ?>" class="scAddTextName " />
                        <code>回复的链接地址</code>
                    </td>
                </tr>
                
                  <tr class="jq_type_2">
                    <td class="lfTdBt">回复图片：</td>
                 <td class="rgTdBt">
                    <div style="width: 300px;height: 100px; float: left;">
                        <input type="hidden" name="data[photo]" value="<?php echo ($CONFIG["weixin"]["photo"]); ?>" id="data_photo" />
                        <div id="fileToUpload" >上传回复图片</div>
                    </div>
                    <div style="width: 300px;height: 100px; float: left;">
                        <img id="photo_img" width="120" height="80"  src="<?php echo config_img($CONFIG[weixin][photo]);?>" />
                        <a href="<?php echo U('setting/attachs');?>">缩略图设置</a>
                        建议尺寸<?php echo ($CONFIG["attachs"]["weixin"]["thumb"]); ?>
                    </div>
                    <script>                                            
						var width_weixin = '<?php echo thumbSize($CONFIG[attachs][weixin][thumb],0);?>';                         
						var height_weixin = '<?php echo thumbSize($CONFIG[attachs][weixin][thumb],1);?>';                         
						var uploader = WebUploader.create({                             
						auto: true,                             
						swf: '/static/default/webuploader/Uploader.swf',                             
						server: '<?php echo U("app/upload/uploadify",array("model"=>"weixin"));?>',                             
						pick: '#fileToUpload',                             
						resize: true,  
						compress : {width: width_weixin,height: height_weixin,quality: 80,allowMagnify: false,crop: true}                       
					});                                                 
					uploader.on( 'uploadSuccess', function( file,resporse) {                             
						$("#data_photo").val(resporse.url);                             
						$("#photo_img").attr('src',resporse.url).show();                         
					});                                                
					uploader.on( 'uploadError', function( file ) {                             
						alert('上传出错');                         
					});                     
                    </script>
                </td>
            </tr>
        
            
              
                </td>
            </table>
            <div class="smtQr"><input type="submit" value="确认添加" class="smtQrIpt" /></div>
        </form>
    </div>
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
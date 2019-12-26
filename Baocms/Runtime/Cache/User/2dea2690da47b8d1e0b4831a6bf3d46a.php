<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<meta charset="utf-8">
		<title><?php if(!empty($seo_title)): endif; echo ($CONFIG["site"]["sitename"]); ?>会员中心</title>
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
		<?php if($CONFIG[site][concat] != 1): ?><link rel="stylesheet" href="/static/default/wap/css/base.css">
		<link rel="stylesheet" href="/static/default/wap/css/mcenter.css"/>
		<script src="/static/default/wap/js/jquery.js"></script>
		<script src="/static/default/wap/js/base.js"></script>
		<script src="/static/default/wap/other/layer.js"></script>
		<script src="/static/default/wap/other/roll.js"></script>
		<script src="/static/default/wap/js/public.js"></script>
		<?php else: ?>
		<link rel="stylesheet" href="/static/default/wap/css/??base.css,mcenter.css" />
		<script src="/static/default/wap/js/??jquery.js,base.js,roll.js,layer.js,public.js"></script><?php endif; ?>
	</head>
	<body>
<script src="/static/default/wap/js/star.js"></script>
<style>.list-media-x {margin-top: 0.0rem;}</style>
<header class="top-fixed bg-yellow bg-inverse">
	<div class="top-back">
		<a class="top-addr" href="javascript:history.back(-1);"><i class="icon-angle-left"></i></a>
	</div>
		<div class="top-title">
			商品点评
		</div>
	<div class="top-signed">
		<?php if($msg_day > 0): ?><a href="<?php echo u('user/message/index');?>">
<i class="icon-envelope"></i>
<span class="badge bg-red jiaofei"><?php echo ($msg_day); ?></span>
</a>
<?php else: ?>
    <?php if(empty($sign_day)): ?><!--    <a href="<?php echo u('wap/sign/signed');?>" class="top-addr icon-plus"> 签到</a>    -->
    <?php else: ?>
    <a href="<?php echo u('wap/passport/logout');?>" class="top-addr icon-sign-out"></a><?php endif; endif; ?>
	</div>
</header>


<form method="post" action="<?php echo U('goods/dianping',array('order_id'=>$detail['order_id']));?>"  target="x-frame">

<div class="line padding">
		<div class="x12">
			<p class="margin-small-bottom text-gray"><span> 商城“<?php echo ($goods["title"]); ?>”的点评</p>
		</div>
	</div>
 <div class="blank-10 bg"></div>   
<div class="list-media-x" id="list-media">
	<ul>
    <div class="line padding border-bottom">
		<div class="x3">
			<img src="<?php echo config_img($goods['photo']);?>" width="90%">
		</div>
		<div class="x9">
			<p><?php echo ($goods["title"]); ?> <?php echo ($detail["d"]); ?></p>
			<p class="text-gray">实付金额：￥<?php echo round($detail['total_price']/100,2);?></p>
			<p class="text-gray">使用积分数：<?php echo ($detail['use_integral']); ?></p>
		</div>
	</div>
   </ul>
</div>
<div class="blank-10 bg"></div>



	
	

	<div class="line padding ">
		<div class="x3">
			评价：
		</div>
		<div class="x9">
			<span id="jq_star"></span>
		</div>
	</div>
<div class="blank-10 bg"></div>
	
	
	<script>
		$(document).ready(function () {
			$("#jq_star").raty({
				numberMax: 5,
				path: '/static/default/wap/image/',
				starOff: 'star-off.png',
				starOn: 'star-on.png',
				scoreName: 'data[score]'
			});
			
		});
	</script>
	
	
    
   
    <div class="blank-10 bg"></div>
    
	
	<div class="line padding ">
		<div class="blank-10"></div>
		<textarea cols="33" rows="5" name="data[contents]" placeholder="还记得这家店吗？写点评记录生活、分享体验" style="border:thin solid #eee;width:100%;resize:none;padding:10px;"></textarea>
		<div class="blank-10"></div>
	</div>
	<div class="blank-10 bg"></div>
    <div class="blank-10"></div>
<link rel="stylesheet" type="text/css" href="/static/default/wap/other/webuploader.css"> 
<script src="/static/default/wap/other/webuploader.js"></script> 
	<div class="Upload-img-box">
   <div  id="fileToUpload">上传图片</div>
   <div class="Upload-img">
   <div class="list-img loading" style="display:none;"><img src=""></div>
   <div class="list-img jq_photo" style="display:none;"></div>
  </div>
</div>
    <script>
    	var width_dianping = '<?php echo thumbSize($CONFIG[attachs][dianping][thumb],0);?>';                     
		var height_dianping = '<?php echo thumbSize($CONFIG[attachs][dianping][thumb],1);?>';  
        var uploader = WebUploader.create({                 
			auto: true,                             
			swf: '/static/default/webuploader/Uploader.swf',                             
			server: '<?php echo U("app/upload/uploadify",array("model"=>"dianping"));?>',                             
			pick: '#fileToUpload',                             
			resize: true,    
			compress : {width:width_dianping,height:height_dianping,quality:60,allowMagnify: false,crop: true}//裁剪       
        });
        //监听文件处理
        uploader.on( 'beforeFileQueued', function( file ) {
            $(".loading").show();
            if(file.size > 1024000){
                uploader.option( 'compress', {
                    width:width_dianping,//这里裁剪长度
                    quality:60
                });
            }
        });
        //上传成功替换页面图片
        uploader.on( 'uploadSuccess', function( file,resporse) {
            $(".loading").hide();
            var str = '<img src="'+resporse.url+'"><input type="hidden" name="photos[]" value="' + resporse.url + '" />';
            $(".jq_photo").show().html(str);
        });
        //上传失败提醒
        uploader.on( 'uploadError', function( file ) {
            alert('上传出错');
        });

        $(document).ready(function () {
            $(document).on("click", ".photo img", function () {
                $(this).parent().remove();
            });
        });
    </script>
                
                
            </div>	
			
	
			
	<div class="container">
		<div class="blank-20"></div>
		<button class="button button-big button-block bg-dot">提交评价</button>
		<div class="blank-20"></div>
	</div>
</form>
    
<div class="blank-20"></div>
 <footer class="foot-fixed">
  <?php if(($ctl == 'member') AND ($act == 'index')): ?><a class="foot-item <?php if(($ctl == 'member') AND ($act != 'fabu')): ?>active<?php endif; ?>" href="<?php echo u('wap/index/index');?>">		
    <span class="icon icon-home"></span>
    <span class="foot-label">首页</span>
    </a>
  <?php else: ?>
  <a class="foot-item" href="<?php echo u('member/index');?>">		
    <span class="icon icon-home"></span>
    <span class="foot-label">首页</span>
    </a><?php endif; ?>
    
    <a class="foot-item" href="<?php echo LinkTo('user/money/index');?>">
    <span class="icon icon-plus"></span><span class="foot-label">充值</span></a>
    
     <a class="foot-item" href="<?php echo LinkTo('goods/index',array('aready'=>1));?>">
    <span class="icon icon-money"></span><span class="foot-label">订单</span></a>
    
    <a class="foot-item  <?php if(($ctl == 'message') ||($act == 'xiaoxizhongxin')): ?>active<?php endif; ?>" href="<?php echo u('message/index');?>">			
    <span class="icon icon-volume-up"></span><span class="foot-label">消息</span></a>
    
    <a class="foot-item  <?php if($ctl == 'information'): ?>active<?php endif; ?>" href="<?php echo u('information/index');?>">			
    <span class="icon icon-gear"></span><span class="foot-label">设置</span></a>
    
   
    </footer>


<iframe id="x-frame" name="x-frame" style="display:none;"></iframe>
<style>
.add-message {margin-top: 30px;}
.add-button{ text-align:center;}
.layui-layer-title {border-bottom: 0px solid #eee;}
</style>
<?php if($is_weixin): ?><!--首先是在微信里面-->
    <?php if(!empty($CONFIG[other][check_connect_uid])): ?><script>
        $(document).ready(function () {
            var check_connect_uid = "<?php echo ($check_connect_uid); ?>";
            if (check_connect_uid == '' || check_connect_uid == null) {
               check_connect_uid_wap();//如果等于空
             }
        })
        
        function check_connect_uid_wap(url) {
            layer.open({
                type: 1,
                title: '是否绑定微信？',
                skin: 'layui-layer-demo', //加上边框
                area: ['90%', '150px'], //宽高
                content: '<div class="add-message"><div class="add-button"><a href="<?php echo U('wap/passport/wxlogin');?>" id="go_mobile" class="button button-small bg-yellow">立即绑定微信</a></div></div>',
            });
            $('.layui-layer-title').css('color', '#ffffff').css('background', '#2fbdaa');
        
        }
    </script><?php endif; endif; ?>
</body>
</html>
<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<meta charset="utf-8">
		<title><?php if(!empty($mobile_title)): echo ($mobile_title); ?>_<?php endif; echo ($CONFIG["site"]["sitename"]); ?>会员专区</title>
        <meta name="keywords" content="<?php echo ($mobile_keywords); ?>" />
        <meta name="description" content="<?php echo ($mobile_description); ?>" />
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="/static/default/wap/css/base.css">
		<link rel="stylesheet" href="/static/default/wap/css/<?php echo ($ctl); ?>.css?V=2"/>
		<script src="/static/default/wap/js/jquery.js"></script>
		<script src="/static/default/wap/js/base.js"></script>
		<script src="/static/default/wap/other/layer.js"></script>
		<script src="/static/default/wap/other/roll.js"></script>
		<script src="/static/default/wap/js/public.js"></script>
	    <script src="/static/default/wap/js/mobile_common.js"></script>
        <script src="/static/default/wap/js/iscroll-probe.js"></script>
        <script>
            function bd_encrypt(gg_lat, gg_lon){
                var x_pi = 3.14159265358979324 * 3000.0 / 180.0;
                var x = gg_lon;
                var y = gg_lat;
                var z = Math.sqrt(x * x + y * y) + 0.00002 * Math.sin(y * x_pi);
                var theta = Math.atan2(y, x) + 0.000003 * Math.cos(x * x_pi);
                var bd_lon = z * Math.cos(theta) + 0.0065;
                var bd_lat = z * Math.sin(theta) + 0.006;
                dingwei('<?php echo U("wap/index/dingwei",array("t"=>$nowtime,"lat"=>"llaatt","lng"=>"llnngg"));?>', bd_lat, bd_lon);
            }
            navigator.geolocation.getCurrentPosition(function (position) {
                bd_encrypt(position.coords.latitude, position.coords.longitude);
            });
         </script>
	</head>
	<body>
	<header class="top-fixed bg-yellow bg-inverse">
		<div class="top-back">
			<a class="top-addr" href="<?php echo ($backurl); ?>"><i class="icon-angle-left"></i></a>
		</div>
		<div class="top-title">
			用户登录
		</div>
		<div class="top-share">
			<a href="<?php echo U('passport/register');?>">注册</a>
		</div>
	</header>
	
	<div class="line">
		<div class="blank-10"></div>
		<form class="login-form" action="<?php echo U('passport/login');?>" method="post" target="x-frame">
			<input type="hidden" name="backurl" value="<?php echo ($backurl); ?>">
			<div class="form-group">
				<div class="field field-icon">
					<span class="icon icon-user"></span>
					<input id="account" name="account" type="text" class="input" placeholder="请输入账号">
				</div>
			</div>
			
			<div class="form-group">
				<div class="field field-icon">
					<span class="icon icon-key"></span>
					<input id="password" type="password" name="password" class="input" placeholder="请输入密码">
				</div>
			</div>
           
                
			<div class="blank-40"></div>
			<div class="container">
				<div class="form-button"><button class="button button-block button-big bg-dot" type="submit">登录</button></div>
			</div>
		</form>

		<div class="blank-40"></div>
		<div class="container login-open">
<!--			<h5>用其他合作平台帐号登录<br/>-->
			<a style="color:#333" href="<?php echo U('passport/forget',array('way'=>2));?>">忘记密码？点击这里找回！</a>
            </h5>
			<div class="blank-10"></div>
<!--			<p>-->
<!--				<a href="<?php echo U('passport/qqlogin');?>">-->
<!--					<div class="txt radius-circle bg-blue"><i class="icon icon-qq"></i></div>-->
<!--				</a>      -->
<!--				<?php if($is_weixin): ?><a href="<?php echo u('wap/index/index');?>"><div class="txt radius-circle bg-green"><i class="icon icon-weixin"></i></div></a><?php endif; ?>-->
<!--				<a href="<?php echo U('passport/wblogin');?>"><div class="txt radius-circle bg-dot"><i class="icon icon-weibo"></i></div></a>-->
<!--			</p>-->
		</div>
		<div class="blank-40"></div>
	</div>
	



<style>
.footer-search{padding:15px;background:#fff;border-bottom:thin solid #eee;padding-bottom:5px}
</style>
   
<div class="footer">
    <a href="<?php echo u('user/member/index');?>">客户端</a> &nbsp;  &nbsp;
    	<?php if(!empty($is_shop)): ?><a href="<?php echo u('distributors/index/index');?>" title="管理商家">管理商家</a>   &nbsp; &nbsp; 
    	<?php else: ?>
    <a href="<?php echo u('user/apply/index');?>" title="商家入驻">商家入驻</a>   &nbsp; &nbsp;<?php endif; ?>
    城市：<a class="button button-small text-yellow" href="<?php echo U('city/index');?>"  title="<?php echo bao_msubstr($city_name,0,4,false);?>"><?php echo bao_msubstr($city_name,0,4,false);?></a>                          
</div>
<div class="blank-20"></div>
<?php if($CONFIG[other][footer] == 1): ?><footer class="foot-fixed">
    <a class="foot-item <?php if(($ctl == 'index') AND ($act != 'more')): ?>active<?php endif; ?>" href="<?php echo U('index/index');?>">		
    <span class="icon icon-home"></span>
    <span class="foot-label">首页</span>
    </a>
    
    <a class="foot-item <?php if(($ctl) == "qianggou"): ?>active<?php endif; ?>" href="<?php echo U('wap/tuan/index');?>">
    <span class="icon icon-paw"></span><span class="foot-label">套餐</span></a>
        
    <a class="foot-item <?php if(($ctl) == "shopping"): ?>active<?php endif; ?>" href="<?php echo U('wap/mall/index');?>">
    <span class="icon icon-umbrella"></span><span class="foot-label">商城</span></a>

<!--    <?php if($is_weixin): ?></a>-->
<!--       <a class="foot-item <?php if(($ctl) == "biz"): ?>active<?php endif; ?>" href="<?php echo U('near/index');?>">			-->
<!--       <span class="icon icon-map-marker"></span><span class="foot-label">附近</span></a>-->
<!--    <?php else: ?>-->
<!--       <a class="foot-item <?php if(($ctl) == "thread"): ?>active<?php endif; ?>" href="<?php echo U('thread/index');?>">			-->
<!--       <span class="icon icon-commenting"></span><span class="foot-label">贴吧</span></a>-->
<!--<?php endif; ?>-->

    <a class="foot-item <?php if(($ctl) == "shop"): ?>active<?php endif; ?>" href="<?php echo U('wap/shop/index');?>">
    <span class="icon icon-user"></span><span class="foot-label">门店</span></a>

    <a class="foot-item <?php if(($ctl) == "user"): ?>active<?php endif; ?>" href="<?php echo U('user/member/index');?>">			
    <span class="icon icon-user"></span><span class="foot-label">我的</span></a>
    
    </footer><?php endif; ?>

<iframe id="x-frame" name="x-frame" style="display:none;"></iframe>
<script> 
	var BAO_PUBLIC = "__PUBLIC__"; 
	var BAO_ROOT = "__ROOT__"; 
	var BAO_SURL = "<?php echo ($CONFIG['site']['host']); ?>__SELF__"
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js" type="text/javascript"></script>
<script>
$(function(){
	var newurl = BAO_SURL.replace(/\?\S+/,'');
	var stitle = "<?php echo ($config['title']); ?>"; 
	var sdesc = "<?php echo ($config['title']); ?>";
	var surl = newurl+'?fuid=<?php echo getuid();?>';	
	var catchpic = $('img');
	var lcatchpic = "<?php echo ($CONFIG['site']['host']); ?>" + $('img').eq(0).attr("src");
	$('img').each(function(){
		if($(this).width() >= 60){
			lcatchpic = $(this).attr("src");
			if(lcatchpic.indexOf("http://") < 0){
				lcatchpic = "<?php echo ($CONFIG['site']['host']); ?>" + lcatchpic;
			}
			return false;
		};
	});
	
	var spic = "<?php echo ($CONFIG['site']['host']); ?>"+BAO_PUBLIC+'/img/logo.jpg';
	if(catchpic.length > 0){
		spic = lcatchpic;
		
	}
	console.log(lcatchpic);
	wx.config({
	debug: false,
	appId: '<?php echo ($signPackage["appId"]); ?>',
    timestamp: '<?php echo ($signPackage["timestamp"]); ?>',
    nonceStr: '<?php echo ($signPackage["nonceStr"]); ?>',
    signature: '<?php echo ($signPackage["signature"]); ?>',
	jsApiList: [
	'checkJsApi',
	'onMenuShareTimeline',
	'onMenuShareAppMessage',
	'onMenuShareQQ',
	'onMenuShareWeibo',
	'onMenuShareQZone'
	]
	});
	wx.ready(function(){
		wx.onMenuShareTimeline({
			title: stitle, 
			link: surl, 
			imgUrl: spic,
			success: function () { 
				alert("分享成功!");
			},
			cancel: function () { 
				alert("分享失败!");
			}
		});
		wx.onMenuShareAppMessage({		
			title: stitle,
			desc: sdesc, // 分享描述
			link: surl, 
			imgUrl: spic,
			type: '', // 分享类型,music、video或link，不填默认为link
			dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
			success: function () { 
				alert("分享成功!");
			},
			cancel: function () { 
				alert("分享失败!");
			}
		});
		wx.onMenuShareQQ({
			title: stitle,
			desc: sdesc, // 分享描述
			link: surl, 
			imgUrl: spic,
			success: function () { 
			   alert("分享成功!");
			},
			cancel: function () { 
			   alert("分享失败!");
			}
		});
		wx.onMenuShareWeibo({
			title: stitle,
			desc: sdesc, // 分享描述
			link: surl, 
			imgUrl: spic,
			success: function () { 
			  alert("分享成功!");
			},
			cancel: function () { 
				alert("分享失败!");
			}
		});	
		wx.onMenuShareQZone({
			title: stitle,
			desc: sdesc, // 分享描述
			link: surl, 
			imgUrl: spic,
			success: function () { 
			   alert("分享成功!");
			},
			cancel: function () { 
				alert("分享失败!");
			}
		});	
	});
})	
</script>	 
</body>
</html>
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
			<a class="top-addr" href="javascript:history.back();"><i class="icon-angle-left"></i></a>
		</div>
		<div class="top-title">
			订单支付
		</div>
	</header>  
<style>
.layui-layer-page .layui-layer-content{ padding:20px;}
.layui-layer-page .layui-layer-content input[type='password']{ width:100%; height:36px; border:1px solid #ccc; background:#fff; margin-bottom:10px; text-indent:10px; font-size:14px;}
.layui-layer-page .layui-layer-content input[type='button']{ width:100%; height:40px; border:none; background:#2FBDAA; color:#fff; font-size:16px;cursor:pointer}
.reset_password{cursor:pointer}
.layui-layer-page .layui-layer-content input.blue{background:#33b095;}
.layui-layer-page .layui-layer-content p{ line-height:28px; color:#999;}
.layui-layer-page .layui-layer-content .check_box{ overflow:hidden; margin-bottom:5px;}
.layui-layer-page .layui-layer-content .check_box label{ display:block; float:left; margin-right:20px; margin-bottom:10px;}
</style>	
	<div class="tuan-order">
		<div class="blank-10 bg"></div>
		<div class="line border-top border-bottom">
			<div class="container">
				<div class="x12 row border-bottom">
					<span class="float-left">
						支付编号
					</span>
					<span class="float-right">
						<?php echo ($logs["log_id"]); ?>
					</span>
				</div>
				<div class="x12 row border-bottom">
					<span class="float-left">
						付款缘由
					</span>
					<span class="float-right">
						<?php echo ($types[$logs['type']]); ?>
					</span>
				</div>
				<div class="x12 row">
					<span class="float-left">
						实际支付金额
					</span>
					<span class="float-right text-dot">
					&yen; <?php echo round($logs['need_pay']/100,2);?>元
					</span>
				</div>
			</div>
		</div>
	</div>
	
	<div class="blank-50"></div>
	<div class="container">
		<?php if($paytype[$logs['code']]['code'] != money): echo ($button); endif; ?>
        <?php if($paytype[$logs['code']]['code'] == money): if(!empty($user_pay_password)): ?><input type="button" class="button button-block bg-dot button-big check_new_password" value=" 立刻支付 ">
        <?php else: ?>
           <input type="button" class="button button-block bg-dot button-big add_pay_password" value=" 设置支付密码 "><?php endif; endif; ?>
	</div>
    
<div class="blank-20"></div>
<?php if($paytype[$logs['code']]['code'] == money): ?><script type="text/javascript" language="javascript">
    $(document).ready(function () {
        var user_pay_password = "<?php echo ($user_pay_password); ?>";
        if (user_pay_password == '' || user_pay_password == null) {
             add_pay_password();
         }else{
			 check_pay_password();
		 }
	    $(".check_new_password").click(function () {
             check_pay_password();
        });
		$(".add_pay_password").click(function () {
             add_pay_password();
        });
    })
	//检测支付密码
	function check_pay_password() {
			layer.open({
						type: 1,
						title:'请输入支付密码',
						skin: 'layui-layer-rim', //加上边框
						area: ['90%', 'auto'], //宽高
						content: '<input type="password" class="pay_password" name="pay_password" placeholder="请输入支付密码" value=""/><input type="button" class="sure_das" value="确认支付"/><p>请先输入支付密码后操作</p><a class="reset_password">忘记密码？</a>',
					  });
			$('.layui-layer-title').css('color', '#ffffff').css('background', '#2fbdaa');
		
		}
		$(document).on('click','.reset_password',function(){
                layer.msg('正在带您去修改密码，请稍后', function(){
					setTimeout(function () {
						window.location.href = "<?php echo U('user/info/pay_password');?>";
					}, 1000);
				});
         })
            $(document).on('click','.sure_das',function(){
                var url = "<?php echo U('payment/check_pay_password');?>";
                var user_id = "<?php echo ($MEMBER['user_id']); ?>";
                var pay_password = $(".pay_password").val();
				
                    $.post(url,{user_id:user_id,pay_password:pay_password},function(data){
                        if(data.status == 'error'){
                            layer.msg(data.msg);
							$('.sure_das').removeClass("blue");
                        }else{
							$('.sure_das').addClass("blue");
							layer.msg('支付密码验证成功，正在为您跳转支付', function(){
								setTimeout(function () {
								   window.location.href = "<?php echo U('user/member/pay',array('logs_id'=>$logs['log_id']));?>";
								}, 1000);
							});
                        }
                    },'json')

            })

	    //添加支付密码
		function add_pay_password() {
			layer.open({
						type: 1,
						title:'设置支付密码',
						skin: 'layui-layer-rim', //加上边框
						area: ['90%', 'auto'], //宽高
						content: '<input type="password" class="pay_password" name="pay_password" placeholder="设置支付密码" value=""/><input type="button" class="sure_pay_password blue" value="确认设置"/><p>您还没有支付密码，请先设置支付密码</p>',
					  });
			$('.layui-layer-title').css('color', '#ffffff').css('background', '#2fbdaa');
		}
		 $(document).on('click','.sure_pay_password',function(){
                var url = "<?php echo U('payment/set_pay_password');?>";
                var user_id = "<?php echo ($MEMBER['user_id']); ?>";
                var pay_password = $(".pay_password").val();
                    $.post(url,{user_id:user_id,pay_password:pay_password},function(data){
                        if(data.status == 'error'){
                            layer.msg(data.msg);
                        }else{
							layer.msg('支付密码设置成功', function(){
								setTimeout(function () {
								    window.location.reload();
								}, 1000);
							});
                        }
                    },'json')
            })
</script><?php endif; ?>

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
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
        <a class="top-addr" href="<?php echo U('mall/index',array('cat'=>$detail['cate_id']));?>"><i class="icon-angle-left"></i></a>
    </div>
    <div class="top-title">
        订单设定
    </div>
</header>

<script>
    $(document).ready(function () {
        $("#pay-method li").click(function () {
            var code = $(this).attr("data-rel");
            $("#code").val(code);
            $("#pay-method li").each(function () {
                $(this).removeClass("active");
            });
            $(this).addClass("active");
        });

    });
</script>

<style>
    .icon-angle-right {
        float: right;
        font-size: 20px;
    }
</style>
<form class="pay-form" action="<?php echo U('mall/pay2',array('order_id'=>$order['order_id']));?>" method="post"
      target="x-frame">
    <div class="row">
        <span class="float-left">购物费用：&yen; <?php echo round($order['total_price']/100,2);?>元</span>
        <span class="float-right">
               		<?php if($order['express_price'] == 0): ?>配送费：免邮<?php else: ?>配送费：&yen;  <?php echo round($order['express_price']/100,2);?> 元<?php endif; ?>
             </span>
    </div>
    <hr/>
    <div class="row">
        <?php $need_pay = $order['total_price'] + $order['express_price']; ?>
        <span class="float-left">总费用：</span>
        <span class="float-right  text-dot">&yen; <?php echo round($need_pay/100,2);?> 元
        </span>
    </div>
    <hr/>
    <?php if(empty($mobile_fan) != true): ?><div class="row">
            <?php $need_pay = $order['total_price'] + $order['express_price']; ?>
            <span class="float-left">手机下单立减：</span>
            <span class="float-right  text-dot">&yen; <?php echo round($mobile_fan/100,2);?> 元
        	</span>
        </div>
        <hr/><?php endif; ?>
    <!--使用优惠劵逻辑开始-->
    <?php if(!empty($coupon)): ?><div class="row">
            <span class="float-left">优惠劵使用：</span>
            <input type="hidden" name="download_id" id="download_id" value="<?php echo ($coupon['download_id']); ?>"
                   checked="checked"/>
            <span class="float-right"> 优惠劵ID：<?php echo ($coupon['download_id']); ?>，抵现 &yen;<?php echo round($coupon['reduce_price']/100,2);?>元</span>
        </div>
        <hr/>
        <?php elseif(!empty($download_id)): ?>
        <div class="row">
            <span class="float-left">优惠劵使用：</span>
            <input type="hidden" name="download_id" id="download_id" value="$download_id" checked="checked"/>
            <span class="float-right"> 当前使用的优惠劵ID：<?php echo ($download_id); ?></span>
        </div>
        <hr/>
        <?php else: endif; ?>
    <!--使用优惠劵逻辑结束-->


    <div class="blank-10 bg"></div>
    <div class="row address_bg">
        <span class="x10">
			<i class="icon-user"></i> <?php echo bao_msubstr($defaultAddress['xm'],0,3,false);?> &nbsp; <i
                class="icon-mobile"></i> <?php echo ($defaultAddress["tel"]); ?>
            <p onclick="location='<?php echo ($changeAddressUrl); ?>'"><?php echo ($defaultAddress["area_str"]); ?> <?php echo ($defaultAddress["info"]); ?></p>
         </span>
        <span class="x2" onclick="location='<?php echo ($changeAddressUrl); ?>'"><i class="icon-angle-right padding-top"></i></span>
    </div>

    <div class="blank-10 bg"></div>

    <ul id="pay-method" class="pay-method">
        <?php if(is_array($payment)): foreach($payment as $key=>$var): ?><li data-rel="<?php echo ($var["code"]); ?>" class="media media-x payment">
                <a class="float-left" href="javascript:;">
                    <img src="/static/default/wap/image/pay/<?php echo ($var["mobile_logo"]); ?>">
                </a>
                <div class="media-body">
                    <div class="line">
                        <div class="x10">
                            <?php echo ($var["name"]); ?><p>推荐已安装<?php echo ($var["name"]); echo ($var["id"]); ?>客户端的用户使用</p>
                        </div>
                        <div class="x2">
                            <span class="radio txt txt-small radius-circle bg-green"><i class="icon-check"></i></span>
                        </div>
                    </div>
                </div>
            </li><?php endforeach; endif; ?>
            <li data-rel="wait" class="media media-x payment">
                <a class="float-left" href="javascript:;">
                    <img src="/static/default/wap/image/pay/dao.png">
                </a>
                <div class="media-body">
                    <div class="line">
                        <div class="x10">
                        到店支付<p>如果您没有网银，可以到店付</p>
                        </div>
                        <div class="x2">
                            <span class="radio txt txt-small radius-circle bg-green"><i class="icon-check"></i></span>
                        </div>
                    </div>
                </div>
            </li>
    </ul>
    <input id="code" type="hidden" name="code" value=""/>

    <div class="text-center padding-left padding-right margin-large-top">
        <button type="submit" class="button button-big button-block bg-yellow">提交订单</button>
    </div>

    <div class="blank-20"></div>
</form>
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
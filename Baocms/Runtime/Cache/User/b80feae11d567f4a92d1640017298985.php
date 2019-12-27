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
<header class="top-fixed bg-yellow bg-inverse">
	<div class="top-back">
		<a class="top-addr" href="<?php echo U('member/index');?>"><i class="icon-angle-left"></i></a>
	</div>
		<div class="top-title">
			余额充值
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

 <!-- 筛选TAB -->
<style>ul{ padding-left:0px;}</style>
<ul id="shangjia_tab">
		<li style="width:33.3333333367%;"><a href="<?php echo u('money/index');?>"  class="on">余额充值</a></li>
        <li style="width:33.3333333367%;"><a href="<?php echo u('cash/index');?>" >申请提现</a></li>
        <li style="width:33.3333333367%;"><a href="<?php echo u('logs/moneylogs');?>" >日志管理</a></li>
</ul>

<div class="blank-40 bg"></div>
<!-- 筛选TAB -->
<div class="line tab-bar">
	<div class="button-toolbar">
		<div class="button-group">
			<a class="block button bg-dot active" href="<?php echo U('money/index');?>">余额充值</a>
			<a class="block button" href="<?php echo U('money/recharge');?>">代金券充值</a>
		</div>
	</div>
</div>
<?php if(!empty($CONFIG[cash][is_recharge])): ?><div class="container">
<div class="blank-10"></div>
	<p>
        <?php if(!empty($CONFIG[cash][recharge_full_1]) && !empty($CONFIG[cash][recharge_give_1]) && ($CONFIG[cash][recharge_full_1] > $CONFIG[cash][recharge_give_1])): ?>单笔充值满：<a style="color:#F00">&yen;<?php echo ($CONFIG[cash][recharge_full_1]); ?> </a> 元,送：<?php echo ($CONFIG[cash][recharge_give_1]); ?> 元<?php endif; ?>
        <hr/>
        <?php if(!empty($CONFIG[cash][recharge_full_2]) && !empty($CONFIG[cash][recharge_give_2]) && ($CONFIG[cash][recharge_full_2] > $CONFIG[cash][recharge_give_2])): ?>单笔充值满：<a style="color:#F00">&yen;<?php echo ($CONFIG[cash][recharge_full_2]); ?> </a> 元,送：<?php echo ($CONFIG[cash][recharge_give_2]); ?> 元<?php endif; ?>
        <hr/>
        <?php if(!empty($CONFIG[cash][recharge_full_3]) && !empty($CONFIG[cash][recharge_give_3]) && ($CONFIG[cash][recharge_full_3] > $CONFIG[cash][recharge_give_3])): ?>单笔充值满：<a style="color:#F00">&yen;<?php echo ($CONFIG[cash][recharge_full_3]); ?> </a> 元,送：<?php echo ($CONFIG[cash][recharge_give_3]); ?> 元<?php endif; ?>
    </p>
</div><?php endif; ?>
<div class="blank-10 bg"></div>

<form method="post" action="<?php echo U('money/moneypay');?>">
<div class="line padding">
    <span class="x3">充值金额：</span>
	<span class="x9">
		<input class="text-input" type="text" name="money" placeholder="请输入充值金额" />
	</span>
</div>
<ul id="pay-method" class="pay-method">
	<?php if(is_array($payment)): foreach($payment as $key=>$var): if($var['code'] != 'money'): ?><li data-rel="<?php echo ($var["code"]); ?>" class="media media-x payment">
		<a class="float-left"  href="javascript:;">
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
	</li><?php endif; endforeach; endif; ?>
</ul>
<input id="code" type="hidden" name="code" value="" />
<script>
	$("#pay-method li").click(function(){
		var code = $(this).attr("data-rel");
		$("#code").val(code);
		$("#pay-method li").each(function(){
			$(this).removeClass("active");
		});
		$(this).addClass("active");
	});
</script>

<div class="blank-30"></div>
<div class="container"><button type="submit" class="button button-block button-big bg-dot">提交充值</button></div>
<div class="blank-30"></div>

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
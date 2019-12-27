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
<style>
.icon-sign-out, .top-fixed .top-back i { font-size: 18px;}
.top-fixed {border-bottom: none;}
.top-fixed .top-search input {border-radius:2px;}
.top-fixed .top-share, .top-fixed .top-back {padding-right: 0px;}
.top-fixed .top-title {font-size: 14px;}
.top-fixed .top-share a {width: 50px !important;}
p, .p {margin-bottom: 0px;}
.member-top { margin-top: 0px;}
</style>

	<header class="top-fixed bg-yellow bg-inverse transparent" id="header">
		<div class="top-back">
			<a id="search-btn" href="javascript:void(0);"><i class="icon-search"></i></a>
		</div>
		<div class="top-title">
			会员中心
		</div>
        <div class="top-search" style="display:none;">
			<form method="post" action="<?php echo U('wap/all/index');?>">
				<input name="keyword" placeholder="输入关键字"  />
				<button type="submit" class="icon-search"></button> 
			</form>
		</div>
		<div class="top-share">
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


<div class="member-top">
<div class="member-info">
<div class="user-avatar"> 
<img src="<?php echo config_img($MEMBER['face']);?>"> 
</div>
<div class="user-name"> 
<span>
				<?php if(!empty($MEMBER['nickname'])): echo ($MEMBER["nickname"]); ?> 
                <?php else: ?>
                <?php echo ($MEMBER["account"]); endif; ?>
                <sup><?php echo ($ranks[$MEMBER['rank_id']]['rank_name']); ?></sup></span> 
</div>
</div>
<div class="member-collect">
<span><a href="<?php echo u('money/index');?>"><em><?php echo round($MEMBER['money']/100,2);?></em><p>我的余额</p></a> </span>
<span><a href="<?php echo u('money/index');?>"><em><?php echo ($MEMBER["integral"]); ?></em><p>我的积分</p></a> </span>
<!--<?php if(!empty($open_running)): ?>-->
<!--<span><a href="<?php echo u('running/index');?>"><i class="goods-browse"></i><p>我的跑腿</p></a></span>-->
<!--<?php endif; ?>-->
</div>
</div>

  <script type="text/javascript">
	$(function(){
		$("#search-btn").click(function(){
			if($(".top-search").css("display")=='block'){
				$(".top-search").hide();
				$(".top-title").show(200);
			}
			else{
				$(".top-search").show();
				$(".top-title").hide(200);
			}
		});
		$("#search-bar li").each(function(e){
			$(this).click(function(){
				if($(this).hasClass("on")){
					$(this).parent().find("li").removeClass("on");
					$(this).removeClass("on");
					$(".serch-bar-mask").hide();
				}
				else{
					$(this).parent().find("li").removeClass("on");
					$(this).addClass("on");
					$(".serch-bar-mask").show();
				}
				$(".serch-bar-mask .serch-bar-mask-list").each(function(i){
					
					if(e==i){
						$(this).parent().find(".serch-bar-mask-list").hide();
						$(this).show();
					}
					else{
						$(this).hide();
					}
					$(this).find("li").click(function(){
						$(this).parent().find("li").removeClass("on");
						$(this).addClass("on");
					});
				});
			});
		});
	});
	

	</script>  
    

<!--<div class="member-center">-->
<!--      <dl class="mt5">-->
<!--        <dd>-->
<!--          <ul id="order_ul">-->
<!--              <li><a href="<?php echo U('tuan/index');?>"><i class="icon-bookmark-o"></i><p>套餐</p></a></li>-->
<!--              <?php if($open_mall == 1): ?>-->
<!--              <li><a href="<?php echo LinkTo('goods/index',array('aready'=>1));?>"><i class="icon-cart-plus"></i><p>购物</p></a></li>-->
<!--<?php endif; ?>-->
<!--              <li><a href="<?php echo U('eleorder/index');?>"><i class="icon-cutlery"></i><p>外卖</p></a></li>-->
<!--              <?php if($open_booking == 1): ?>-->
<!--              <li><a href="<?php echo LinkTo('booking/index');?>"><i class="icon-tty"></i><p>订座</p></a></li>-->
<!--<?php endif; ?>-->
<!--              <li><a href="<?php echo U('breaks/index');?>"><i class="icon-money"></i><p>买单</p></a></li>-->
<!--          </ul>-->
<!--        </dd>-->
<!--      </dl>-->
<!--      -->
<!--</div>-->
    
    
<div class="blank-10 bg"></div>
<div class="panel-list">
	<ul>

<li><a href="<?php echo U('information/index');?>"><span class="icon-gears"></span>我的资料<i class="icon-angle-right"></i></a></li>

<?php if($is_shop != null): ?><li><a href="<?php echo u('distributors/index/index');?>"><span class="icon-home"></span>我的门店<font>（<?php echo ($is_shop_name); ?>）</font><i class="icon-angle-right"></i></a></li><?php endif; ?>
<?php $worker = D('Shopworker')->where(array('user_id'=>$MEMBER['user_id']))->find(); $user_delivery = D('Delivery')->where(array('user_id'=>$MEMBER['user_id'],'audit'=>1,'closed'=>0))->find(); ?>
<?php if(!empty($worker)): ?><li><a href="<?php echo U('worker/index/index');?>"><span class="icon-paper-plane-o"></span>店员中心登录<font>（<?php echo ($worker['name']); ?>）</font><i class="icon-angle-right"></i></a></li><?php endif; ?>

<!--<?php if(!empty($user_delivery)): ?>-->
<!--	<li><a href="<?php echo U('delivery/index/index');?>"><span class="icon-bus"></span>配送员中心登录<font>（<?php echo ($user_delivery['name']); ?>）</font><i class="icon-angle-right"></i></a></li>-->
<!--<?php endif; ?>-->
		<?php if($profit == 1): ?><!--如果开启分销-->
			<li><a href="<?php echo U('distribution/index');?>"><span class="icon-cny"></span>我的分成<i class="icon-angle-right"></i></a></li>
			<?php else: endif; ?>
		<li><a href="<?php echo U('distribution/qrcode');?>"><span class="icon-qrcode"></span>我的二维码<i class="icon-angle-right"></i></a></li>
		<li>
			<a href="<?php echo U('tuancode/index');?>">
				<span class="icon-credit-card"></span>
				我的套餐码&nbsp;
				<?php if($code > 0): ?><font>待消费：(<b><?php echo ($code); ?></b>)</font>
					<?php else: endif; ?>
				<i class="icon-angle-right"></i>
			</a>
		</li>
		<li><a href="<?php echo u('money/index');?>"><span class="icon-money"></span>余额充值<i class="icon-angle-right"></i></a></li>

	</ul>

</div>

<div class="blank-10 bg"></div>



<div class="panel-list">
	<ul>
		<?php if($open_mall == 1): ?><li><a href="<?php echo LinkTo('goods/index',array('aready'=>1));?>"><span class="icon-star-o"></span>我的订单<i class="icon-angle-right"></i></a></li><?php endif; ?>


		
<!--<?php if($open_crowd == 1): ?>-->
<!--	<li><a href="<?php echo U('crowd/index',array('aready'=>1));?>"><span class="icon-users"></span>我的众筹<i class="icon-angle-right"></i></a></li>   -->
<!--<?php endif; ?>-->
<!--<?php if($open_pintuan == 1): ?>-->
<!--	<li><a href="<?php echo U('pintuan/groups');?>"><span class="icon-star-half-o"></span>我的拼团<i class="icon-angle-right"></i></a></li> -->
<!--<?php endif; ?>-->
<!--<?php if($open_hotels == 1): ?>-->
<!--	<li><a href="<?php echo U('hotels/index');?>"><span class="icon-hotel"></span>我的酒店<i class="icon-angle-right"></i></a></li>      -->
<!--<?php endif; ?>-->
<?php if($open_mall == 1): ?><li><a href="<?php echo U('favorites/index');?>"><span class="icon-star-o"></span>我的商品收藏<i class="icon-angle-right"></i></a></li><?php endif; ?>
<!--<?php if($open_pinche == 1): ?>-->
<!--	<li><a href="<?php echo U('pinche/index');?>"><span class="icon-car"></span>我的拼车<i class="icon-angle-right"></i></a></li>        -->
<!--<?php endif; ?>-->
<!--<?php if($open_tieba == 1): ?><li><a href="<?php echo U('tieba/index');?>"><span class="icon-comments"></span>我的贴吧&nbsp; 
        <?php if($tieba > 0): ?><font>(<?php echo ($tieba); ?>)</font>
        <?php else: endif; ?>  
        <?php if($counts['tieba'] != null): ?><font>今日：(<b><?php echo ($counts["tieba"]); ?></b>)</font>  
        <?php else: endif; ?>  
        <i class="icon-angle-right"></i>
        </a>
    </li><?php endif; ?>-->
<!--<?php if($open_community == 1): ?>-->
<!--<li><a href="<?php echo u('user/member/xiaoqu');?>"><span class="icon-ils"></span>我的小区 -->
<!--				<?php if($xiaoqu > 0): ?>-->
<!--                <font>(<?php echo ($xiaoqu); ?>)</font> -->
<!--                <?php else: ?>-->
<!--<?php endif; ?> <i class="icon-angle-right"></i></a>-->
<!--</li>-->
<!--<?php endif; ?> -->
<?php if($open_huodong == 1): ?><li><a href="<?php echo U('activity/index');?>"><span class="icon-star-o"></span>我报名的活动<i class="icon-angle-right"></i></a></li><?php endif; ?>
        <div class="blank-10 bg"></div>

<!--<?php if($open_life == 1): ?>-->
<!--    <li>-->
<!--        <a href="<?php echo U('life/index');?>"><span class="icon-truck"></span>我的同城信息&nbsp; -->
<!--        <?php if($life > 0): ?>-->
<!--        <font>(<?php echo ($life); ?>)</font>  -->
<!--        <?php else: ?>-->
<!--<?php endif; ?>  -->
<!--        <i class="icon-angle-right"></i>-->
<!--        </a>-->
<!--    </li>  -->
<!--<?php endif; ?>   -->
   
<!--<?php if($open_jifen == 1): ?>-->
<!--    <li>-->
<!--        <a href="<?php echo U('exchange/index');?>"><span class="icon-gift"></span>我的礼品&nbsp; -->
<!--        <?php if($lipin > 0): ?>-->
<!--        <font>(<?php echo ($lipin); ?>)</font>-->
<!--        <?php else: ?>-->
<!--<?php endif; ?> -->
<!--        <i class="icon-angle-right"></i>-->
<!--        </a>-->
<!--    </li>-->
<!--<?php endif; ?> -->

<li>
		<a href="<?php echo U('coupon/index');?>">
				<span class="icon-tags"></span>
				我的优惠券&nbsp; 
                <?php if($coupon > 0): ?><font>未使用：(<b><?php echo ($coupon); ?></b>)</font>
                <?php else: endif; ?> 
				<i class="icon-angle-right"></i>
			</a>
		</li>

<!--        <li>-->
<!--			<a href="<?php echo U('yuyue/index',array('status'=>2));?>">-->
<!--				<span class="icon-tty"></span>-->
<!--				我的预约-->
<!--                <?php if($shop_yuyue > 0): ?>-->
<!--                <font>未使用：(<b><?php echo ($shop_yuyue); ?></b>)</font>-->
<!--                <?php else: ?>-->
<!--<?php endif; ?> -->
<!--				<i class="icon-angle-right"></i>-->
<!--			</a>-->
<!--		</li>-->
<!--<?php if($open_cloud == 1): ?>-->
<!--    <li>-->
<!--        <a href="<?php echo U('cloud/index');?>"><span class="icon-send"></span>我的一元云购&nbsp; -->
<!--        <i class="icon-angle-right"></i>-->
<!--        </a>-->
<!--    </li>-->
<!--<?php endif; ?> -->

<!--<?php if($open_appoint == 1): ?>-->
<!--	<li><a href="<?php echo U('appoint/index');?>"><span class="icon-umbrella"></span>我的家政<i class="icon-angle-right"></i></a></li>-->
<!--<?php endif; ?> -->
<li><a href="<?php echo u('user/message/index');?>"><span class="icon-volume-up"></span>消息中心<i class="icon-angle-right"></i></a></li>


<!--<?php if($open_running == 1): ?>-->
<!--<li><a href="<?php echo U('running/index');?>"><span class="icon-plane"></span>我的跑腿&nbsp; <i class="icon-angle-right"></i></a></li>  -->
<!--<?php endif; ?>         -->


	</ul>

</div>
<div class="blank-10"></div>
<div class="container text-center">
		<a class="button button-block button-big bg-dot" href="<?php echo u('wap/passport/logout');?>">退出后台</a>
</div>

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
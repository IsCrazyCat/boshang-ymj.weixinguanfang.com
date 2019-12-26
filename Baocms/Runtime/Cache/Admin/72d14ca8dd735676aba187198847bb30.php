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
<style>

body{background:#F1F1F1;}
.comiis_19ditu_bg{width:100%;margin:0 auto 10px;padding:5px 0 0 5px;overflow: hidden;}		
.comiis_19forum{margin:0 5px 5px 0;float:left;width:210px;height:200px;background:#fff;overflow: hidden;}		
.comiis_19forum .comiis_19forum_div{margin:10px;}
.comiis_19forum .comiis_19forum_title{height:53px;position:relative;}
.comiis_19forum .comiis_19forum_icon{width:48px;height:48px;position:absolute;top:0;right:0px;background:url(comiis_ico.gif) no-repeat 0 top;border-radius:5px;}
.comiis_19forum .comiis_19forum_title h2{height:26px;overflow:hidden;}
.comiis_19forum .comiis_19forum_title h2 a{color:#666;;font:100 22px/24px "Microsoft Yahei","SimHei";text-decoration:none;}
.comiis_19forum .comiis_19forum_title em{color:#999;display:block;line-height:24px;height:24px;overflow: hidden;font-style: normal;}
.comiis_19forum .comiis_19forum_list{height:130px;color:#999;overflow:hidden;}
.comiis_19forum .comiis_19forum_list a{}
.comiis_19forum .comiis_19forum_list h3{line-height:22px;width:100%;margin-right:3px;float:left;height:22px;font-size:14px;overflow:hidden;font-weight:400;    color: #666;}
.comiis_19forum .comiis_19forum_list h3 a{font-size:12px;color: #666;}
.comiis_19forum_style1{width:377px}
.comiis_19forum_style1 .comiis_19forum_div{width:166px;height:142px;float:left;display:inline;}
.comiis_19forum_style1 .comiis_19forum_rightad{width:186px;height:162px;float:right;display:inline;overflow:hidden;}
.comiis_19forum_style2{height:333px;}
.comiis_19forum_style2 .comiis_19forum_div{width:166px;height:142px;}
.comiis_19forum_style2 .comiis_19forum_bottomad{width:186px;height:164px;overflow:hidden;padding-top:5px;}
.comiis_19forum_style3{width:377px;height:333px;}
.comiis_19forum_style3 .comiis_19forum_div{width:357px;height:142px;}
.comiis_19forum_style3 .comiis_19forum_bottomad{width:377px;height:164px;overflow:hidden;padding-top:5px;}
.comiis_19forum_style3 .comiis_19forum_topad{position:absolute;top:0;right:50px;width:150px;height:48px;overflow:hidden;}
.comiis_19forum_style3 .comiis_19forum_list h3{width:86px;margin-right:3px;}
.comiis_19forum_top{border-top:#fff 2px solid;zoom:1;}
.comiis_hover .comiis_19forum_icon{background-position:0 bottom;}
.comiis_hover{box-shadow:0 0 6px rgba(50,50,50,0.3);}
.comiis_19ditu_bg .comiis_19forum .comiis_ad{padding:6px 8px 8px;}
.comiis_19ditu980 .comiis_19ditu_bg {width:975px;}
.comiis_19ditu980 .comiis_19forum{width:190px;}
.comiis_19ditu980 .comiis_19forum .comiis_19forum_list h3{width:82px;}
.red{ color:#F00 !important}
.mainBt ul span{ background:#F00; color:#FFF; padding:5px 15px; margin-right:40px;}
</style>
<div class="mainBt">
    <ul>
        <li class="li1">首页</li>
        <li class="li2">后台首页</li>
        <li class="li2 li3">待办事项</li>
        <?php if($warning['is_ip'] == 1): if(!empty($admin['username'])): ?><span style="float:right">尊敬的&nbsp;<?php echo ($admin["username"]); ?>&nbsp;您上次登录IP跟本次登录IP地址不一致，建议您立即修改密码！</span><?php endif; endif; ?>  
    </ul>
</div>


<div class="main-jsgl main-sc">
<div class="comiis_19ditu ">
<ul class="comiis_19ditu_bg cl masonry" style="position: relative; height: 1014px;">

<li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="">系统概况</a></h2>
            <em>欢迎：<?php echo ($admin["username"]); ?>（<?php echo ($admin["role_name"]); ?>）</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="##">1：上次登录地址：<?php echo ($ad["last_ip"]); ?></a></h3>
            <h3><a href="##">2：更新到<?php echo ($v); ?></a></h3>
            <h3><a href="##">3：php版本：<?php echo phpversion();?></a></h3>

            </div>
    </div>
</div>
</li>

<li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="<?php echo U('user/index');?>">会员数据</a></h2>
            <em>总：<?php echo ($counts["users"]); ?>个会员</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="<?php echo U('user/index');?>" class="dot">1：今日新增<a class="red"><?php echo ($counts["totay_user"]); ?></a>个会员</a></h3>
            <h3><a href="<?php echo U('user/index');?>">2：已有<?php echo ($counts["user_moblie"]); ?>人验证手机号</a></h3>
<!--            <h3><a href="<?php echo U('user/index');?>">3：已有<?php echo ($counts["user_email"]); ?>人绑定邮箱</a></h3>-->
<!--            <h3><a href="<?php echo U('user/index');?>">4：qq注册<?php echo ($counts["user_qq"]); ?>人.</a></h3>-->
<!--            <h3><a href="<?php echo U('user/index');?>">5：微信登录<?php echo ($counts["user_weixin"]); ?>人.</a></h3>-->
<!--            <h3><a href="<?php echo U('user/index');?>">6：微博注册<?php echo ($counts["user_weibo"]); ?>人.</h3>-->

 
            </div>
    </div>
</div>
</li>


<li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="#">资金统计</a></h2>
            <em>会员总资金<?php echo round($counts['money_and']/100,2);?>元</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="<?php echo U('usermoneylogs/index');?>">1：会员总资金<?php echo round($counts['money_and']/100,2);?>元</a></h3>
<!--            <h3><a href="<?php echo U('usermoneylogs/index');?>">2：会员总积分<?php echo ($counts['money_integral']); ?>分</a></h3>-->
            <h3><a href="<?php echo U('usercash/index');?>">2：今日提现<a class="red"><?php echo round($counts['money_cash_day']/100,2);?></a>元，<a class="red"><?php echo ($counts['money_cash_audit']); ?></a>人待审</a></h3>
            </div>
    </div>
</div>



<li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="<?php echo U('shop/index');?>">商家数据</a></h2>
            <em>共<?php echo ($counts["shop"]); ?>个商家</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="##">1：今日<a class="red"><?php echo ($counts["totay_shop"]); ?></a>商家申请入驻</a></h3>
            <h3><a href="<?php echo U('shop/apply');?>">2：待审核<a class="red"><?php echo ($counts["totay_shop_audit"]); ?></a>个商家</a></h3>
            <h3><a href="<?php echo U('shop/recognition');?>">3：待认领<a class="red"><?php echo ($counts["shoprecognition"]); ?></a>个商家</a></h3>
<!--            <h3><a href="<?php echo U('audit/index');?>">4：已有<?php echo ($counts["shop_audit"]); ?>商家已认证</a></h3>-->
<!--            <h3><a href="<?php echo U('weidian/index');?>">5：有<?php echo ($counts["shop_weidian"]); ?>微店,待审核<a class="red"><?php echo ($counts["shop_weidian_audit"]); ?></a></a></h3>-->

            </div>
    </div>
</div>
</li>


<li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="<?php echo U('goods/index');?>">商城数据</a></h2>
            <em>共有<?php echo ($counts['goods']); ?>个商品</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="<?php echo U('goods/index');?>">1：共有<?php echo ($counts['goods']); ?>个商品</a></h3>
            <h3><a href="<?php echo U('goods/index');?>">2：今日新增<a class="red"><?php echo ($counts['goods_day']); ?></a>个，待审核<a class="red"><?php echo ($counts['goods_audit']); ?></a>个</a></h3>
            <h3><a href="<?php echo U('order/index');?>">3：订单<?php echo ($counts['order']); ?>个，今日<a class="red"><?php echo ($counts['order_day']); ?></a>个</a></h3>
            <h3><a href="<?php echo U('order/index');?>">4：商城退款申请：<a class="red"><?php echo ($counts["order_tui"]); ?></a>笔</a></h3>
<!--            <h3><a href="<?php echo U('malldianping/index');?>">5：商城共：<?php echo ($counts["dianping_goods"]); ?>条点评</a></h3>-->
<!--            <h3><a href="<?php echo U('malldianping/index');?>">6：今日商城点评：<a class="red"><?php echo ($counts["totay_dianping_goods"]); ?></a>次</a></h3>-->
            </div>
    </div>
</div>
</li>


<li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="<?php echo U('tuan/index');?>">套餐数据</a></h2>
            <em>共有<?php echo ($counts["tuan"]); ?>个套餐</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="<?php echo U('tuan/index');?>">1：共有<?php echo ($counts["tuan"]); ?>个套餐</a></h3>
            <h3><a href="<?php echo U('tuan/index');?>">2：今日上单<a class="red"><?php echo ($counts["tuan_day"]); ?></a>个，待审核<a class="red"><?php echo ($counts["tuan_audit"]); ?></a>个</a></h3>
            <h3><a href="<?php echo U('tuanorder/index');?>">3：订单<?php echo ($counts["order_tuan"]); ?>个，今日<a class="red"><?php echo ($counts["totay_order_tuan"]); ?></a>个</a></h3>
            <h3><a href="<?php echo U('tuancode/index');?>">4：还有<a class="red"><?php echo ($counts["tuan_code_used"]); ?></a>套餐劵待验证</a></h3>
<!--            <h3><a href="<?php echo U('tuandianping/index');?>">5：团购共<?php echo ($counts["dianping_tuan"]); ?>条点评</a></h3>-->
<!--            <h3><a href="<?php echo U('tuandianping/index');?>">6：今日团购点评<a class="red"><?php echo ($counts["totay_dianping_tuan"]); ?></a>次</a></h3>-->
            </div>
    </div>
</div>
</li>



<!--<li class="comiis_19forum comiis_19forum_style0 masonry-brick">-->
<!--<div class="comiis_19forum_top comiis_19forum_id1">-->
<!--    <div class="comiis_19forum_div">-->
<!--        <div class="comiis_19forum_title">-->
<!--            <span class="comiis_19forum_icon"></span>-->
<!--            <h2><a href="<?php echo U('ele/index');?>">外卖数据</a></h2>-->
<!--            <em>共有<?php echo ($counts["ele"]); ?>个外卖商家</em>-->
<!--        </div>-->
<!--            <div class="comiis_19forum_list">-->
<!--            <h3><a href="<?php echo U('ele/index');?>">1：共有<?php echo ($counts["eleproduct"]); ?>个菜品</a></h3>-->
<!--            <h3><a href="<?php echo U('ele/index');?>">2：今日上单<a class="red"><?php echo ($counts["eleproduct_day"]); ?></a>个，待审核<a class="red"><?php echo ($counts["eleproduct_audit"]); ?></a>个</a></h3>-->
<!--            <h3><a href="<?php echo U('eleorder/index');?>">3：总订单<?php echo ($counts["order_waimai"]); ?>笔，今日外卖：<a class="red"><?php echo ($counts["totay_order_waimai"]); ?></a>单</a></h3>-->
<!--            <h3><a href="<?php echo U('eleorder/index');?>">4：外卖退款申请<a class="red"><?php echo ($counts["order_waimai_tui"]); ?></a>笔</a></h3>-->
<!--            <h3><a href="<?php echo U('eleorder/index');?>">5：外卖总点评<?php echo ($counts["dianping_waimai"]); ?></a></h3>-->
<!--            <h3><a href="<?php echo U('eleorder/index');?>">6：今日外卖点评<a class="red"><?php echo ($counts["totay_dianping_waimai"]); ?></a>次</a></h3>-->
<!--            </div>-->
<!--    </div>-->
<!--</div>-->
<!--</li>-->




</li>
<li class="comiis_19forum comiis_19forum_style0 masonry-brick">
<div class="comiis_19forum_top comiis_19forum_id1">
    <div class="comiis_19forum_div">
        <div class="comiis_19forum_title">
            <span class="comiis_19forum_icon"></span>
            <h2><a href="<?php echo U('coupon/index');?>">优惠劵下载</a></h2>
            <em>共：<?php echo ($counts["coupon"]); ?>单</em>
        </div>
            <div class="comiis_19forum_list">
            <h3><a href="<?php echo U('coupon/index');?>">1：网站共<?php echo ($counts["coupon"]); ?>单优惠劵</a></h3>
            <h3><a href="<?php echo U('coupon/index');?>">2：今日新增<a class="red"><?php echo ($counts["coupon_day"]); ?></a>单，<a class="red"><?php echo ($counts["coupon_audit"]); ?></a>待审</a></h3>
            <h3><a href="<?php echo U('coupon/index');?>">3：优惠劵总下载<?php echo ($counts["coupon_download"]); ?>次</a></h3>
            <h3><a href="<?php echo U('coupondownload/index');?>">4：今日下载优惠劵<a class="red"><?php echo ($counts["coupon_download_day"]); ?></a>次</a></h3>
            </div>
    </div>
</div>
</li>


<!--<li class="comiis_19forum comiis_19forum_style0 masonry-brick">-->
<!--<div class="comiis_19forum_top comiis_19forum_id1">-->
<!--    <div class="comiis_19forum_div">-->
<!--        <div class="comiis_19forum_title">-->
<!--            <span class="comiis_19forum_icon"></span>-->
<!--            <h2><a href="<?php echo U('life/index');?>">分类信息数据</a></h2>-->
<!--            <em>总：<?php echo ($counts["life"]); ?>条分类信息</em>-->
<!--        </div>-->
<!--            <div class="comiis_19forum_list">-->
<!--            <h3><a href="<?php echo U('life/index');?>">1：总：<?php echo ($counts["life"]); ?>条，<?php echo ($counts["life_audit"]); ?>待审核</a></h3>-->
<!--            <h3><a href="<?php echo U('life/index');?>">2：今日<a class="red"><?php echo ($counts["totay_life"]); ?></a>条分类信息</a></h3>-->
<!--            <h3><a href="<?php echo U('life/index');?>">3：分类信息总浏览<?php echo ($counts["life_views"]); ?>次</a></h3>-->
<!--            </div>-->
<!--    </div>-->
<!--</div>-->
<!--</li>-->

<!--<li class="comiis_19forum comiis_19forum_style0 masonry-brick">-->
<!--<div class="comiis_19forum_top comiis_19forum_id1">-->
<!--    <div class="comiis_19forum_div">-->
<!--        <div class="comiis_19forum_title">-->
<!--            <span class="comiis_19forum_icon"></span>-->
<!--            <h2><a href="<?php echo U('community/index');?>">小区数据</a></h2>-->
<!--            <em>总<?php echo ($counts["community"]); ?>个小区</em>-->
<!--        </div>-->
<!--            <div class="comiis_19forum_list">-->
<!--            <h3><a href="<?php echo U('community/index');?>">1：总<?php echo ($counts["community"]); ?>个小区</a></h3>-->
<!--            <h3><a href="<?php echo U('communityposts/index');?>">2：总有<?php echo ($counts["community_bbs"]); ?>篇帖子，<a class="red"><?php echo ($counts["community_bbs_audit"]); ?></a>篇待审核</a></h3>-->
<!--            <h3><a href="<?php echo U('feedback/index');?>">3：小区报修<?php echo ($counts["community_feedback"]); ?>条</a></h3>-->
<!--            <h3><a href="<?php echo U('convenientphone/index');?>">4：小区便民电话<?php echo ($counts["community_phone"]); ?>条</a></h3>-->
<!--            <h3><a href="<?php echo U('communitynews/index');?>">5：共<?php echo ($counts["community_news"]); ?>条通，知今日发送<a class="red"><?php echo ($counts["community_news_day"]); ?></条></a></h3>-->
<!--            <h3><a href="<?php echo U('logs/index');?>">6：还有<a class="red"><?php echo round($counts['community_order']/100,2);?></a>元物业费未缴</a></h3>-->
<!--            </div>-->
<!--    </div>-->
<!--</div>-->
<!--</li>-->



<!--<li class="comiis_19forum comiis_19forum_style0 masonry-brick">-->
<!--<div class="comiis_19forum_top comiis_19forum_id1">-->
<!--    <div class="comiis_19forum_div">-->
<!--        <div class="comiis_19forum_title">-->
<!--            <span class="comiis_19forum_icon"></span>-->
<!--            <h2><a href="<?php echo U('article/index');?>">自媒体</a></h2>-->
<!--            <em>总<?php echo ($counts["article"]); ?>篇文章</em>-->
<!--        </div>-->
<!--            <div class="comiis_19forum_list">-->
<!--            <h3><a href="<?php echo U('article/index');?>">1：共<?php echo ($counts["article"]); ?>篇文章。</a></h3>-->
<!--            <h3><a href="<?php echo U('article/index');?>">2：今日<a class="red"><?php echo ($counts["article_day"]); ?></a>篇文章，<a class="red"><?php echo ($counts["article_audit"]); ?></a>篇待审核</a></h3>-->
<!--            <h3><a href="<?php echo U('article/index');?>">3：总文章回复<?php echo ($counts["article_reply"]); ?>次，点赞<?php echo ($counts["article_zan"]); ?>次</a></h3>-->
<!--            <h3><a href="<?php echo U('article/index');?>">4：总文章浏览<?php echo ($counts["article_vies"]); ?>次</a></h3>-->
<!--            </div>-->
<!--    </div>-->
<!--</div>-->
<!--</li>-->



</ul>
<div class="cl"></div>
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
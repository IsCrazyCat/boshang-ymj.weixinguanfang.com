<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <?php echo ($CONFIG['site']['headinfo']); ?>
        <title><?php if($config['title'])echo $config['title'];else echo $seo_title; ?></title>
        <meta name="keywords" content="<?php echo ($seo_keywords); ?>" />
        <meta name="description" content="<?php echo ($seo_description); ?>" />
        <link href="__TMPL__statics/css/style.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="/themes/default/Home/statics/css/<?php echo ($ctl); ?>.css" />
        <script> var BAO_PUBLIC = '__PUBLIC__'; var BAO_ROOT = '__ROOT__';</script>
        <script src="__TMPL__statics/js/jquery.js"></script>
        <script src="__PUBLIC__/js/layer/layer.js"></script>
        <script src="__TMPL__statics/js/jquery.flexslider-min.js"></script>
        <script src="__TMPL__statics/js/js.js"></script>
        <script src="__PUBLIC__/js/web.js"></script>
        <script src="__TMPL__statics/js/baocms.js"></script>
    </head>
<style>
/*背景*/
.nav, .searchBox .submit, .sy_hottjJd, .cityInfor_nr .cityInfor_list .nr:hover .link .img, .topBackOn, .goods_flListA.on, .nearbuy_hotNum, .sy_sjcpBq1, .spxq_qgjjKk, .spxq_xqT li.on code, .hdxq_ljct, .qg-sp-tab span.on, .dcsy_topLi:hover .dcsy_topLiTu, .sjsy_ljzx, .dui-huan, .locaNr_serAn, .seat-check.on, .spxq_xqMapList li.on, .hdsy_Licj_l em, .hdsy_LicjA, .tBarFabu .sub_btn .btn, .dcsy_topLi.on .dcsy_topLiTu, .liveBtn, .cloudBuy_list .btn, .jfsy_spA, .jfsy_flexslider .flex-control-nav .flex-active, .spxq_xqT li.on a, .subBtn, .cloudBuy_cont_tab ul li.on, .zy_doorSer_detail .nrForm .btn, .basic-info .action .write,  .sy_coupon_tab a.on, .sales-promotion .tag-tuan, .comment_input p .pn, .goods .tm-fcs-panel span.y a, .login_btndz{ background-color: <?php echo ($color); ?>!important; }
/*文字颜色*/
.sy_FloorBtz .bt, .fontcl3, .topOne .nr .left a.on, .liOne:hover .liOneA, .spxq_qgsnum, .nearbuy_zjClear, .zixunList .wz .bt a, .spxq_pjAn, .sjsy_newsList h3, .locaTopDl a, .liOne .list ul li a:hover, .spxq_xqMapT, .spxq_table td a, .hdsy_Licj_l, .hdsy_Libms, .zixunDetail h1, .zixun_hot h3, .liveTab li,.shfw_xq_new li, .jfsy_jffzT, .jfsy_wellcome .blue, .maincl, .m-detail-main-winner-content .user, .pointcl, .liOne_visit_pull .empty a, .liOne_visit_pull .empty a, .goods .tm-price, .intro a, .comment .price{color: <?php echo ($color); ?>!important; }/*边框top上*/
.sy_FloorBt, .qg-sp-tab span.on, .zixun_hot h3, .liveTab {border-bottom: 1px solid <?php echo ($color); ?>!important;}/*边框*/
.spxq_qgjjKk, .hdxq_tgList, .liOne .list ul, .liOne_visit .liOne_visit_pull, .seat-check.on, .liveSearchLeft{border: 1px solid <?php echo ($color); ?>!important;}

/*特殊的*/
.liOne:hover .liOneA {color: <?php echo ($color); ?>; border-left: 1px solid <?php echo ($color); ?>;border-right: 1px solid <?php echo ($color); ?>!important;}
.changeCity_link:after {border-bottom: 2px solid <?php echo ($color); ?>!important;border-right: 2px solid <?php echo ($color); ?>!important;}
.spxq_xqT {border-bottom: 1px solid <?php echo ($color); ?>!important;}
.hdsy_tabLi.on a {border-top: 2px solid <?php echo ($color); ?>!important;}
.spxq_slider .flex-control-thumbs li .flex-active {border-color: <?php echo ($color); ?> !important;}
.zixunRelet { border-top: 2px solid <?php echo ($color); ?>!important;}
.sy_sjcpLi:hover {border-color: <?php echo ($color); ?>!important;}
.navListAll{background-color: #17A994;}
.topTwo .menu {width: 18%; margin-top: 10px;float: right;color: #929292;font-size: 12px;text-align: center;}
.topTwo .ment_left {float: left;width: 33%;}
.topTwo .ment_left .ment_left_img img { width: 36px;height: 36px;}
.navA {position: relative;}
.navA .hot {display: block;width: 27px;height: 18px;background: url(/themes/default/Home/statics/images/header-hot.gif) no-repeat center top;position: absolute;right: -5px;top: 2px;}
.mod .mod-title .current {border-bottom: 2px solid <?php echo ($color); ?>;}
.topTwo .searchBox_r .searchBox {border: 2px solid <?php echo ($color); ?>;}
.superior {border-top: <?php echo ($color); ?> 2px solid;}
.navListAll {background-color: #3ac9aa;}
.navA.on, .navA:hover { background-color: #9C1F4B;}
</style>
<style>
.anchorBL{   display:none !important;  }  
.navListAll {background-color:#2fbdaa;}
.navA.on, .navA:hover { background-color:transparent !important;color:#f60;}
.nav {width: 100%;border-bottom: 2px solid #2fbdaa;background-color:transparent !important;}
.navA {color: #333;}
</style>



    <body>

        <iframe id="baocms_frm" name="baocms_frm" style="display:none;"></iframe> 
<div class="topOne">
    <div class="nr">
        <?php if(empty($MEMBER)): ?><div class="left">您好，欢迎访问<?php echo ($CONFIG["site"]["sitename"]); ?>
        <a href="javascript:void(0);" class="on login_kuaijie" id="login">登陆</a>
        <script>
         $(document).ready(function () {
           $(".login_kuaijie").click(function(){
             ajaxLogin();
           })
         })
        </script>
        |<a href="<?php echo U('passport/register');?>">注册</a>
        <?php else: ?>
        <div class="left">欢迎 <b style="color: red;font-size:14px;"><?php echo ($MEMBER["nickname"]); ?></b> 来到<?php echo ($CONFIG["site"]["sitename"]); ?>&nbsp;&nbsp; 
        <a href="<?php echo u('members/index/index');?>" class="maincl" >个人中心</a>
        <a href="<?php echo u('members/message/index');?>" class="maincl toponeCart" >消息中心
        <?php if(!empty($msg_day)): ?><i id="num" class="radius100"><?php echo ($msg_day); endif; ?></i></a>
        <a href="<?php echo u('home/passport/logout');?>" class="maincl" >退出登录</a><?php endif; ?>
        <a href="<?php echo U('download/index');?>" class="topSm blackcl6">下载手机客户端</a>
    </div>
    <div class="right">
        <ul>
            <li class="liOne"><a class="liOneB" href="<?php echo u('members/order/index');?>" >我的订单</a></li>
            <li class="liOne"><a class="liOneA" href="javascript:void(0);">我的服务<em>&nbsp;</em></a>
                <div class="list">
                    <ul>
                        <li><a href="<?php echo u('members/order/index');?>">我的订单</a></li>
                        <li><a href="<?php echo u('members/ele/index');?>">我的外卖</a></li>
                        <li><a href="<?php echo u('members/yuyue/index');?>">我的预约</a></li>
                        <li><a href="<?php echo u('members/dianping/index');?>">我的评价</a></li>
                        <li><a href="<?php echo u('members/favorites/index');?>">我的收藏</a></li>                                    
                        <li><a href="<?php echo u('members/myactivity/index');?>">我的活动</a></li>
                        <li><a href="<?php echo u('members/life/index');?>">会员服务</a></li>
                        <li><a href="<?php echo u('members/set/nickname');?>">帐号设置</a></li>
                    </ul>
                </div>
            </li>
            <span>|</span>
            <li class="liOne liOne_visit"><a class="liOneA" href="javascript:void(0);">最近浏览<em>&nbsp;</em></a>
                <div class="list liOne_visit_pull">
                    <ul style="border:none !important;">
                        <?php
 $views = unserialize(cookie('views')); $views = array_reverse($views, TRUE); if($views){ foreach($views as $v){ ?>
                        <li class="liOne_visit_pull_li">
                            <a href="<?php echo U('tuan/detail',array('tuan_id'=>$v['tuan_id']));?>"><img src="__ROOT__/attachs/<?php echo ($v["photo"]); ?>" width="80" height="50" /></a>
                            <h5><a href="<?php echo U('tuan/detail',array('tuan_id'=>$v['tuan_id']));?>"><?php echo ($v["title"]); ?></a></h5>
                            <div class="price_box"><a href="<?php echo U('tuan/detail',array('tuan_id'=>$v['tuan_id']));?>"><em class="price">￥<?php echo ($v["tuan_price"]); ?></em><span class="old_price">￥<?php echo ($v["price"]); ?></span></a></div>
                        </li>
                        <?php }?>
                    </ul>
                    <p class="empty"><a href="javascript:;" id="emptyhistory">清空最近浏览记录</a></p>
                    <?php }else{?>
                    <p class="empty">您还没有浏览记录</p>
                    <?php } ?>
                </div>
            </li>
            <span>|</span>
            <li class="liOne"> <a class="liOneA" href="javascript:void(0);">我是商家<em>&nbsp;</em></a>
                <div class="list">
                    <ul>
                        <li><a href="<?php echo u('merchant/login/index');?>">商家登陆</a></li>
                    </ul>
                </div>
            </li>
            <span>|</span>
            
            
            <li class="liOne"> <a class="liOneA" href="javascript:void(0);">网站导航<em>&nbsp;</em></a>
                <div class="list">
                    <ul>
                    <li><a href="<?php echo u('home/hotels/index');?>">酒店频道</a><em class="hot"></em></li>
                    <li><a href="<?php echo u('home/farm/index');?>">农家乐频道</a><em class="hot"></em></li>
                    <li><a href="<?php echo u('home/activity/index');?>">活动频道</a></li>
                    <li><a href="<?php echo u('home/life/index');?>">同城信息</a></li>
                    <li><a href="<?php echo u('home/coupon/index');?>">优惠券</a></li>
                    <li><a href="<?php echo u('home/jifen/index');?>">积分商城</a></li>
                    <li><a href="<?php echo u('home/cloud/index');?>">拼吧</a></li>
                    <li><a href="<?php echo u('home/news/index');?>">文章资讯</a></li>
                    <li><a href="<?php echo u('home/appoint/index');?>">新版家政</a></li>
                    <li><a href="<?php echo u('merchant/index/index');?>">商家中心</a></li>
                    <li><a href="<?php echo u('distributors/index/index');?>">商户管理</a></li>
                    <li><a href="<?php echo u('Property/index/index');?>">物业登录</a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</div>
</div>
<script>
    $(document).ready(function(){
        $("#emptyhistory").click(function(){
            $.get("<?php echo U('tuan/emptyviews');?>",function(data){
                if(data.status == 'success'){
                    $(".liOne_visit_pull ul li").remove();
                    $(".liOne_visit_pull p.empty").html("您还没有浏览记录");
                }else{
                    layer.msg(data.msg,{icon:2});
                }
            },'json')
        })
    });
</script>   
<style>
.liOne {z-index: 999;}
.common-banner--floor {width:1200px;}
.common-banner--newtop {width:1200px; height: 60px;margin:0px auto 0; border: none;padding: 0;overflow: hidden;}
.common-banner {position: relative;text-align: center;}
.common-banner--newtop .common-banner__sheet {width: 100%;}
.common-banner--floor .color--left { left: 0;}
.common-banner--floor .color {position: absolute; width: 50%;height: 60px;margin: 0;padding: 0;border: none;}
.common-banner--floor .color--right {right: 0;}
.common-banner--floor .common-banner__link { position: absolute;top: 0;left: 50%;width: 1170px; height: 60px; margin-left: -585px;}
.common-banner--newtop .common-banner__link { display: block;z-index: 9;}
.common-banner img { vertical-align: top;}
.cf:after {display: block;clear: both;height: 0;overflow: hidden;visibility: hidden;}
.common-banner--floor .close {z-index:10;}
.common-banner .close {position: absolute;top:8px;right:8px;background:url(/themes/default/Pchome/statics/images/tp_54.png) no-repeat center center;}
.common-close--iconfont-small { padding: 8px;}
</style>



<?php  $cache = cache(array('type'=>'File','expire'=> 21600)); $token = md5("Ad, closed=0 AND site_id=67 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ,0,1,21600,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Ad")->where(" closed=0 AND site_id=67 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ")->order("orderby asc")->limit("0,1")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><div class="J-hub J-banner-newtop ui-slider common-banner common-banner--newtop common-banner--floor log-mod-viewed J-banner-stamp-active" >
            <div class="common-banner__sheet cf">
                <div class="color color--left" style="background:#83d8f5"></div>
                <div class="color color--right" style="background:#83d8f5"></div>
                <a class="common-banner__link" target="_blank" href="<?php echo ($item["link_url"]); ?>" >
                     <img  src="<?php echo config_img($item['photo']);?>" width="1200" height="60" >
                </a>
            </div><a href="javascript:void(0)" class="F-glob F-glob-close common-close--iconfont-small close" title="关闭"></a>
</div> <?php endforeach; ?>
<script>
    $(document).ready(function () {
		$(".common-close--iconfont-small").click(function () {
            $(".common-banner").hide();
        });
    });
</script>
<div class="topTwo">
    <div class="left">
        <?php if(!empty($CONFIG['site']['logo'])): if(!empty($city['photo'])): ?><h1><a href="<?php echo u('home/index/index');?>"><img src="<?php echo config_img($city['photo']);?>" width="215" height="65" /></a></h1>
            <?php else: ?>
            <h1><a href="<?php echo u('home/index/index');?>"><img src="<?php echo config_img($CONFIG['site']['logo']);?>" /></a></h1><?php endif; endif; ?> 
        <div class="changeCity">
            <p class="changeCity_name"><?php echo ($city_name); ?></p>
            <a href="<?php echo u('home/city/index');?>" class="graycl changeCity_link">更换城市</a>
        </div>
    </div>
    <div class="right searchBox_r">
    <script>
		$(document).ready(function () {
			$(".selectList li a").click(function () {
				$("#search_form").attr('action', $(this).attr('rel'));
				$("#selectBoxInput").html($(this).html());
				$('.selectList').hide();
			});

			$(".selectList a").each(function(){
				if($(this).attr("cur")){
					$("#search_form").attr('action', $(this).attr('rel'));
					$("#selectBoxInput").html($(this).html());                                
				}
			})
		});
	</script>

        <div class="searchBox">
        	<form id="search_form"  method="post" action="<?php echo u('home/all/index');?>">
            <div class="selectBox"> <span class="select"  id="selectBoxInput">全部</span>
                <div  class="selectList">
                    <ul>
<li><a href="javascript:void(0);" <?php if($ctl == 'all'){?> cur='true'<?php }?> rel="<?php echo u('home/all/index');?>">全部</a></li>
<li><a href="javascript:void(0);" <?php if($ctl == 'shop'){?> cur='true'<?php }?> rel="<?php echo u('home/shop/index');?>">商家</a></li>
<li><a href="javascript:void(0);" <?php if($ctl == 'tuan'){?> cur='true'<?php }?>rel="<?php echo u('home/tuan/index');?>">套餐</a></li>
<li><a href="javascript:void(0);" <?php if($ctl == 'life'){?> cur='true'<?php }?>rel="<?php echo u('home/life/index');?>">生活</a></li>
<li><a href="javascript:void(0);" <?php if($ctl == 'mall'){?> cur='true'<?php }?>rel="<?php echo u('home/mall/index');?>">商品</a></li>
<li><a href="javascript:void(0);" <?php if($ctl == 'news'){?> cur='true'<?php }?>rel="<?php echo u('home/news/index');?>">新闻</a></li>

                    </ul>        

                </div>
            </div>

            <input type="text" class="text" <?php if($ctl != ding): ?>name="keyword" value="<?php echo (($keyword)?($keyword):'输入您要搜索的内容'); ?>"<?php endif; ?> onclick="if (value == defaultValue) {
                        value = '';
                        this.style.color = '#000'
                    }"  onBlur="if (!value) {
                                value = defaultValue;
                                this.style.color = '#999'
                            }" />

            <input type="submit" class="submit" value="搜索" />
            </form>
        </div>

        <div class="hotSearch">
            <?php $a =1; ?>
            <?php  $cache = cache(array('type'=>'File','expire'=> 43200)); $token = md5("Keyword,,0,8,43200,key_id desc,,"); if(!$items= $cache->get($token)){ $items = D("Keyword")->where("")->order("key_id desc")->limit("0,8")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; if($item['type'] == 0 or $item['type'] == 1): ?><a href="<?php echo u('home/shop/index',array('keyword'=>$item['keyword']));?>"><?php echo ($item["keyword"]); ?></a>
                <?php elseif($item['type'] == 2): ?>
                    <a href="<?php echo u('home/tuan/index',array('keyword'=>$item['keyword']));?>"><?php echo ($item["keyword"]); ?></a>
                <?php elseif($item['type'] == 3): ?>
                    <a href="<?php echo u('home/life/index',array('keyword'=>$item['keyword']));?>"><?php echo ($item["keyword"]); ?></a>
                <?php elseif($item['type'] == 4): ?>
                    <a href="<?php echo u('home/mall/index',array('keyword'=>$item['keyword']));?>"><?php echo ($item["keyword"]); ?></a><?php endif; ?> <?php endforeach; ?>
        </div>
    </div>
    
    <?php $pcl__goods = cookie('goods_spec'); $cartnum = (int)array_sum($pcl__goods); ?>
    	<div class="topTwo_cart_box right" id='cart'><em class="ico"></em>购物车<span id="num" class="num"><?php echo (($cartnum)?($cartnum):'0'); ?></span>
            <div class="topTwo_cart_list_box">
                <div class="box"  id='cart_show'>
                </div>
            </div>
        </div>
    
    <div class="clear"></div>
</div>

<script>
$('#cart').hover(function(){
	var link = "<?php echo U('pchome/mall/goods');?>";
	$("#cart_show").load(link);
});
</script>




<div class="nav mb10">
    <div class="navList">
        <ul>
       <li class="navListAll">全部分类</li>                
                
       <?php if(is_array($navigations)): $index = 0; $__LIST__ = $navigations;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$var): $mod = ($index % 2 );++$index; if(($var["parent_id"] == 0)): ?><li class="navLi">
            <a <?php if($var["target"] == 1): ?>target="_blank"<?php endif; ?> <?php if($ctl == $var['title']): ?>class="navA  on"<?php else: ?>class="navA"<?php endif; ?> title="<?php echo ($var['nav_name']); ?>" href="<?php echo ($var['url']); ?>?nav_id=<?php echo ($var['nav_id']); ?>" ><?php echo ($var['nav_name']); ?> 
           		<?php if($var['is_new'] == 1): ?><em class="hot"></em><?php endif; ?> 
            </a>
        </li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
 
        </ul>
    </div>
</div>
<div class="clear"></div>
<script type="text/javascript" src="/themes/default/Home/statics/js/jquery.SuperSlide.2.1.1.js"></script>
<script type="text/javascript" src="__TMPL__statics/js/jquery.qrcode.min.js"></script><!--二维码-->
<script src="__PUBLIC__/js/my97/WdatePicker.js"></script>
<style>
/*首页样式重写*/
.sy_partOne_r {width: 740px;}
.sy_flexslider {width: 730px; margin:10px 10px 0px 10px;}
.homeloginbox {width: 210px;}
.sy_flsx { margin-left: 10px; border-left: 1px solid #dfdfdf;border-right: 1px solid #dfdfdf; border-bottom: 1px solid #dfdfdf;}
.sy_flsx li { height: 130px;}
.sy_flsx li {width: 218px;}
.sy_flsx li a {line-height: 18px;}
.sy_flsx li.bg_3{border-right: none;}
.sy_sjcpwx canvas{width: 102px; height: 102px;margin: 0px auto;padding: 10px; background: #fff; }
.sy_sjcpwx1 {text-align: center;}
.sy_sjcpwx1 canvas{ width: 102px;height: 102px;margin: 0px auto;padding: 10px;background: #fff;}
.sy_mall_tab a{display:inline-block;padding:0 10px;margin-right:5px;color:#333;line-height:24px}
.sy_mall_tab a.on{color:#fff;background-color:#ff611b}
.sy_mall_tab a:hover{color:#fff;background-color:#ff611b}
.sy_coupon_list_box .sy_coupon_list {display: block;}
</style>


<script type="text/javascript">
    $(document).ready(function () {
        $('.sy_flexslider').flexslider({
            directionNav: true,
            pauseOnAction: false
        });
        $('.hotShop_flexslider').flexslider({
            animation: "slide",
            direction: "vertical",
            easing: "swing",
            controlNav: false,
        });
    });//首页轮播js
    $(function () {

        $(".sy_buy_tab a").each(function (e) {
            $(this).mouseover(function (event) {
                $(".sy_buy_tab a").removeClass("on");
                $(this).addClass("on");
                $(".sy_buy_list_box .sy_buy_list").each(function (i) {
                    if (e == i){
                        $(this).parent().find(".sy_buy_list").hide();
                        $(this).show();
                    }
                    else {
                        $(this).hide();
                    }
                });
            });
        });
		//首页套餐部分结束
		$(".sy_mall_tab a").each(function (e) {
            $(this).mouseover(function (event) {
                $(".sy_mall_tab a").removeClass("on");
                $(this).addClass("on");
                $(".sy_mall_list_box .sy_mall_list").each(function (i) {
                    if (e == i){
                        $(this).parent().find(".sy_mall_list").hide();
                        $(this).show();
                    }
                    else {
                        $(this).hide();
                    }
                });
            });
        });
		//首页商城
		
		
        $(".sy_waimai_tab li").each(function (e) {
            $(this).mouseover(function (event) {
                $(this).parent().find("li").removeClass("on");
                $(this).addClass("on");
                $(".sy_waimai_list_box .sy_waimai_list").each(function (i) {
                    if (e == i)
                    {
                        $(this).parent().find(".sy_waimai_list").hide();
                        $(this).show();
                    }
                    else {
                        $(this).hide();
                    }
                });
            });
        });//首页外卖部分结束

		//首页本地商城部分结束
        $(".cityInfor_tab i").each(function (e) {
            $(this).mouseover(function (event) {
                $(this).parent().find("i").removeClass("on");
                $(this).addClass("on");
                $(".cityInfor_nr .cityInfor_list").each(function (i) {
                    if (e == i){
                        $(this).parent().find(".cityInfor_list").hide();
                        $(this).show();
                    }
                    else {
                        $(this).hide();
                    }
                });
            });
        });
		//首页同城信息部分结

        $(".sy_seatSwitch_tab li").each(function (e) {
            $(this).mouseover(function (event) {
                $(this).parent().find("li").removeClass("on");
                $(this).addClass("on");
                $(".sy_seatSwitch_box .sy_seatSwitch1").each(function (i) {
                    if (e == i){
                        $(this).parent().find(".sy_seatSwitch1").hide();
                        $(this).show();
                    }
                    else {
                        $(this).hide();
                    }
                });
            });
        });
		//首页订座部分结
        $(".sy_active_tab a").each(function (e) {
            $(this).mouseover(function (event) {
                $(this).parent().find("a").removeClass("on");
                $(this).addClass("on");
                $(".sy_active_list_box .sy_active_list").each(function (i) {
                    if (e == i){
                        $(this).parent().find(".sy_active_list").hide();
                        $(this).show();
                    }
                    else {
                        $(this).hide();
                    }
                });
            });
        });
		//首页活动部分结
    });

</script>



<!--首页第一部分开始-->

<div class="sy_partOne">
	<div class="left sy_partOne_cate"><div class="menu_fllist2">
    <?php $i=0; ?>             
    <?php if(is_array($tuancates)): foreach($tuancates as $key=>$item): if(($item["parent_id"]) == "0"): $i++;if($i <= 8){ ?>
        <div <?php if($i == 1): ?>class="item2 bo"<?php else: ?>class="item2"<?php endif; ?> >
            <h3>
                <div class="left ico ico_<?php echo ($i); ?>"></div>
                <div class="wz">
                	<a class="bt1" title="<?php echo ($item["cate_name"]); ?>" target="_blank" href="<?php echo U('tuan/index',array('cat'=>$item['cate_id']));?>"><?php echo msubstr($item['cate_name'],0,4,'utf-8',false);?></a>
                    <div class="bt2">
                        <?php $i2=0; ?>
                        <?php if(is_array($tuancates)): foreach($tuancates as $key=>$item2): if(($item2["parent_id"]) == $item["cate_id"]): $i2++;if($i2 <= 2){ ?>
                            <a title="<?php echo ($item2["cate_name"]); ?>" target="_blank" href="<?php echo U('tuan/index',array('cat'=>$item['cate_id'],'cate_id'=>$item2['cate_id']));?>"><?php echo msubstr($item2['cate_name'],0,4,'utf-8',false);?></a>
                            <?php } endif; endforeach; endif; ?>
                    </div>
                </div>
                <div class="clear"></div>
            </h3>
            <div class="menu_flklist2">
                <div class="menu_fl2t"><a title="<?php echo ($item["cate_name"]); ?>" target="_blank" href="<?php echo U('tuan/index',array('cat'=>$item['cate_id']));?>"><?php echo ($item["cate_name"]); ?></a></div>
                <div class="menu_fl2nr">
                    <?php $k=0; ?>
                    <?php if(is_array($tuancates)): foreach($tuancates as $key=>$item2): if(($item2["parent_id"]) == $item["cate_id"]): ?><a title="<?php echo ($item2["cate_name"]); ?>" target="_blank" href="<?php echo U('tuan/index',array('cat'=>$item['cate_id'],'cate_id'=>$item2['cate_id']));?>"><?php echo ($item2['cate_name']); ?></a><?php endif; endforeach; endif; ?>
                </div>
            </div>
        </div>
        <?php } endif; endforeach; endif; ?>
</div>
</div>
    <div class="left sy_partOne_r">
    	<div class="sy_flexslider">
            <ul class="slides">
                <?php  $cache = cache(array('type'=>'File','expire'=> 21600)); $token = md5("Ad, closed=0 AND site_id=1 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ,0,5,21600,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Ad")->where(" closed=0 AND site_id=1 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ")->order("orderby asc")->limit("0,5")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><li class="list" style="background:url('<?php echo config_img($item['photo']);?>') center center no-repeat;">
                     <a target="_blank" href="<?php echo U('app/ad/click',array('ad_id'=>$item['ad_id'],'aready'=>1));?>"></a></li> <?php endforeach; ?>
            </ul>
        </div>
        <ul class="sy_flsx">
            <li class="bg_1">
                <h3><em class="ico ico_1"></em>热门套餐</h3>
            <?php $i=0; ?>
            <?php if(is_array($tuancates)): foreach($tuancates as $key=>$item): if(($item["is_hot"]) == "1"): $i++;if($i<10){ ?>
                <?php if($item['parent_id'] == 0): ?><a title="<?php echo ($item["cate_name"]); ?>" target="_blank" href="<?php echo U('tuan/index',array('cat'=>$item['cate_id']));?>"><?php echo ($item['cate_name']); ?></a>
                    <?php else: ?>
                    <a title="<?php echo ($item["cate_name"]); ?>" target="_blank" href="<?php echo U('tuan/index',array('cat'=>$item['parent_id'],'cate_id'=>$item['cate_id']));?>"><?php echo ($item['cate_name']); ?></a><?php endif; ?>
                <?php } endif; endforeach; endif; ?>
            </li>



            <li class="bg_2">
                <h3><em class="ico ico_2"></em>全部区域</h3>
            <?php $i=0; ?>
            <?php if(is_array($areas)): foreach($areas as $key=>$item): $i++;if($i<=10&&$item['city_id'] == $city_id){ ?>
                <a href="<?php echo U('tuan/index',array('area'=>$item['area_id']));?>"><?php echo ($item['area_name']); ?></a>
                <?php } endforeach; endif; ?>
            </li>



            <li class="bg_3">
                <h3><em class="ico ico_3"></em>热门商圈</h3>
            <?php $i=0; ?>
            <?php if(is_array($bizs)): foreach($bizs as $key=>$item): if(in_array($item['area_id'],$limit_area)&&$i<=8&&$item['is_hot']=='1'){ ?>
                <a href="<?php echo U('tuan/index',array('area'=>$item['area_id'],'business'=>$item['business_id']));?>"><?php echo ($bizs[$item['business_id']]['business_name']); ?></a>
                <?php $i++;} endforeach; endif; ?>
            </li>
        </ul>
    </div>

    

    <div class="right homeloginbox">
    <?php if(empty($MEMBER)): ?><!--未登录-->
            <div class="loginBox">
                <div class="clearfix">
                    <div class="avatar">
                        <img src="<?php echo config_img($user['face']);?>" width="50" height="50" class="img-avatar" />
                    </div>
                    <div class="login">
                        <div><font>hi，
						    <script language="javaScript"> 
								now = new Date(),hour = now.getHours() 
								if(hour < 6){document.write("凌晨好！")} 
								else if (hour < 9){document.write("早上好！")} 
								else if (hour < 12){document.write("上午好！")} 
								else if (hour < 14){document.write("中午好！")} 
								else if (hour < 17){document.write("下午好！")} 
								else if (hour < 19){document.write("傍晚好！")} 
								else if (hour < 22){document.write("晚上好！")} 
								else {document.write("夜里好！")} 
                            </script>
                        </font>
                        </div>
                        <div>欢迎来到<?php echo ($CONFIG["site"]["sitename"]); ?></div>
                    </div>
                </div>
            </div>
            <div class="loginbtnboxx">
               <a href="javascript:void(0);" class="login_btndz loginbtnicon"><i></i><span>登录</span></a>
				<script>
                    $(document).ready(function () {
                        $(".loginbtnicon").click(function(){
                           ajaxLogin();
                        })
                    })
                </script>
                <span class="loginjgline"></span>  
                <a href="<?php echo u('home/passport/register');?>" class="login_btndz registeredbtnicon"><i></i><span>注册</span></a>
            </div>
            <?php else: ?><!--已登录-->
            <div class="loginBox alineof">
        <div class="clearfix">
            <div class="login">
                <font>hi,<em class="C-f60"><?php echo ($MEMBER["nickname"]); ?></em></font>，欢迎来到<?php echo ($CONFIG["site"]["sitename"]); ?>
                    </div>
                </div>
            </div>
            <div class="ghlsmesstab">
                    <div class="clearfix messmartb">
                        <div class="fl insupply_box">
                            <div class="index_money">余额：<?php echo round($MEMBER['money']/100,2);?></div>
                            <a target="_blank" href="<?php echo u('members/money/money');?>" class="index_supplyop">充值</a>
                        </div>
                        <div class="fl kaitongzjxian"></div>
                        <div class="fl instore_box">
                            <div class="index_money">个人中心</div>
                            <a target="_blank" href="<?php echo u('members/index/index');?>" class="index_storesop">进入</a>
                        </div>
                    </div>
                </div><?php endif; ?>


            <dl class="tabRank" id="tabRank">
                <dt class="hd">
                    <h3 class="one on"><a href="javascript:void(0);">新闻</a></h3>
                    <h3 class="two"><a href="javascript:void(0);">信息</a></h3>
                </dt>
                <dd class="bd">
                    <div style="width:209px;" class="listA-box">
                    <?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Article,closed=0,views desc,0,5,600,,"); if(!$items= $cache->get($token)){ $items = D("Article")->where("closed=0")->order("views desc")->limit("0,5")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><a href="<?php echo U('news/detail',array('article_id'=>$item['article_id']));?>" target="_blank">
                        【新闻】<?php echo bao_msubstr($item['title'],0,10,false);?><i class="icons-hot"></i></a> <?php endforeach; ?>	
                    </div>
                    <div style="width:209px;" class="listA-box">
                        <?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Life,closed=0 AND audit=1,views desc,0,5,600,,"); if(!$items= $cache->get($token)){ $items = D("Life")->where("closed=0 AND audit=1")->order("views desc")->limit("0,5")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><a href="<?php echo U('life/detail',array('life_id'=>$item['life_id']));?>" target="_blank">
                        【信息】<?php echo bao_msubstr($item['title'],0,10,false);?><i class="icons-hot"></i></a> <?php endforeach; ?>	
                    </div>
                </dd>
            </dl>  

            

            <div class="superior">
                <div class="clearfix">
                    <div class="fl superboxior">
                        <i class="advcoor1"></i>
                        <span>即买即卖</span>
                    </div>
                    <div class="fl superboxior">
                        <i class="advcoor2"></i>
                        <span>优质供货</span>
                    </div>
                    <div class="fl superboxior">
                        <i class="advcoor3"></i>
                        <span>最惠采购</span>
                    </div>
                </div>
            </div>

   
            <div class="clearfix settledin">
            <a href="<?php echo u('home/shop/apply');?>"  target="_blank" class="fl">免费商家入驻</a>
                <a href="<?php echo U('user/apply/delivery');?>" target="_blank" class="fl lastcb">物流入驻</a>
            </div>

        <script type="text/javascript">
        jQuery("#tabRank").slide({ titCell:"dt h3",mainCell:"dd" });
        </script>
    </div>
    <div class="clear"></div>
</div>



<!--首页第一部分结束-->
<div class="pagewd" id="index-gun">



   

   <!--首页套餐部分开始-->
    <div class="sy_FloorBt" data="top_1" id="floor1">
        <div class="left sy_FloorBtz"><span class="bt cl_1">热门套餐</span>
            <span class="sy_buy_tab">
                <?php $i=0; ?>       
                <?php if(is_array($tuancates)): foreach($tuancates as $key=>$item2): if(($item2["parent_id"]) == "0"): $i++;if($i <= 8){ ?>
                    <a target="_blank" <?php if($i == 1) echo 'class="on"'; ?> title="<?php echo ($item2["cate_name"]); ?>套餐" href="<?php echo U('tuan/index',array('cat'=>$item2['cate_id']));?>"><?php echo ($item2["cate_name"]); ?></a>
                    <?php } endif; endforeach; endif; ?>
            </span>
        </div>
        <div class="right"><a  title="更多套餐" target="_blank" href="<?php echo U('tuan/index');?>">更多&gt;&gt;</a></div>
    </div>




<div class="nearbuy_cpList1 sy_buy_list_box">
			<?php $i2=0; ?>       
            <?php if(is_array($tuancates)): foreach($tuancates as $key=>$cate): if(($cate["parent_id"]) == "0"): $i2++;if($i2 <= 8){ ?>
            <ul class="sy_buy_list" <?php if($i2 == 1) echo 'style=display:block'; else echo 'style=display:none'; ?>>
            <?php $cate_ids = D('Tuancate')->getChildren($cate['cate_id']); $cate_ids = join(',',$cate_ids); ?>
                <?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Tuan,audit = 1 AND closed=0 AND city_id=$city_id AND cate_id IN ($cate_ids),600,orderby asc,sold_num desc,0,8,,"); if(!$items= $cache->get($token)){ $items = D("Tuan")->where("audit = 1 AND closed=0 AND city_id=$city_id AND cate_id IN ($cate_ids)")->order("orderby asc,sold_num desc")->limit("0,8")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><li class="nearbuy_cpLi2">
                        <div class="sy_sjcpLi nearbuy_cpLik">
                            <a href="<?php echo U('tuan/detail',array('tuan_id'=>$item['tuan_id']));?>"><img src="<?php echo config_img($item['photo']);?>" width="273" height="190" /></a>
                            <p class="sy_hottjbt"><?php echo ($item["title"]); ?></p>
                            <p class="sy_hottjp"><?php echo ($item["intro"]); ?></p>
                            <p class="sy_hottjJg"><span class="left">¥<?php echo round($item['tuan_price']/100,2);?><del>¥<?php echo round($item['price']/100,2);?></del></span><span class="right">已售<?php echo ($item["sold_num"]); ?></span></p>
                            <hr style=" border:none 0px; border-bottom: 1px solid #e0e0e0; margin-top:6px;" />
                            <p class="nearbuy_cpLiPf">
							<script>
                            function daojishi(id) {
                            var t = Math.floor($("#" + id).attr('rel'));
                            t--;
                            var d = Math.floor(t / 60 / 60 / 24);
                            var h = Math.floor(t / 60 / 60 % 24);
                            var m = Math.floor(t / 60 % 60);
                            var s = Math.floor(t % 60);
                            $("#" + id).attr('rel', t);
                            $("#" + id).html(d + ':' + h + ':' + m + ':' + s);
                            }
                            </script>  
                        <?php if($item['bg_date'] <= $today && $item['end_date'] > $today){ $tt = strtotime($item['end_date'])-time(); $item['djs_time'] = $tt; $item['djs_str'] = '距结束'; }elseif($item['bg_date'] >$today){ $tt = strtotime($item['bg_date'])-time(); $item['djs_time'] = $tt; $item['djs_str'] = '距开始'; } ?>
                        <script type="text/javascript" language="javascript">
                            setInterval(function () {
                                daojishi("daojishi_<?php echo ($item["tuan_id"]); ?>");
                            }, 1000);
                        </script>                               
                            <span style="cursor: pointer;" title="<?php echo ($shops[$item['shop_id']]['addr']); ?>" class="left"><em></em>
 						<?php $bg_date = strtotime($item['end_date']); $today2 = strtotime($today); ?>
                            <?php if($bg_date > $today2): echo ($item['djs_str']); ?>：<span id="daojishi_<?php echo ($item["tuan_id"]); ?>"  rel="<?php echo ($item['djs_time']); ?>" >00:00:00:00</span>
                            <?php else: ?>
                             已结束<?php endif; ?>
                            </span>                          
                            <span class="right"><a class="sy_hottjJd" target="_blank" href="<?php echo U('tuan/detail',array('tuan_id'=>$item['tuan_id']));?>" ><?php if($item['end_date'] > $today): ?>立即购买<?php else: ?>已结束<?php endif; ?></a></span></p>

                            <div class="sy_sjcpBq"><?php if($item['freebook'] == 1): ?><span class="sy_sjcpBq1">免预约</span><?php endif; if($item['is_new'] == 1): ?><span class="sy_sjcpBq2">新单</span><?php endif; if($item['is_hot'] == 1): ?><span class="sy_sjcpBq3">热门</span><?php endif; if($item['is_chose'] == 1): ?><span class="sy_sjcpBq4">精选</span><?php endif; ?></div>
                            <div class="sy_sjcpLiDw">
                                <div class="sy_sjcpDwBox goods_sjcpDwBox">
                           <script type="text/javascript">
                            $(function () {
                                var str = "<?php echo ($host); echo u('wap/tuan/detail',array('tuan_id'=>$item['tuan_id']));?>";
                                $("#tuan_code_<?php echo ($item["tuan_id"]); ?>").empty();
                                $('#tuan_code_<?php echo ($item["tuan_id"]); ?>').qrcode(str);
                            })
                          </script>
                                    <div class="sy_sjcpDwNr" style="padding-top:10px;">
                                        <?php if($shops[$item['shop_id']]['fans_num'] == null): ?><p><?php echo ($item["views"]); ?>人正在套餐</p>
                                        <?php else: ?>
                                        <p>此商家已有<span><?php echo ($shops[$item['shop_id']]['fans_num']); ?></span>个粉丝 </p><?php endif; ?>
                                        <a href="<?php echo U('tuan/detail',array('tuan_id'=>$item['tuan_id']));?>"><div class="sy_sjcpwx"  id="tuan_code_<?php echo ($item["tuan_id"]); ?>"></div></a>
                                        <div class="sy_sjcpCz">
                       <a class="radius3 sy_sjcpCzA" href="<?php echo U('shop/detail',array('shop_id'=>$item['shop_id']));?>">商家<em>&nbsp;</em></a>
                       <a class="radius3 sy_sjcpCzA" href="<?php echo U('shop/detail',array('shop_id'=>$item['shop_id'],'act'=>'tuan'));?>">套餐<em>&nbsp;</em></a>
                       <a class="radius3 sy_sjcpCzA" href="<?php echo U('shop/detail',array('shop_id'=>$item['shop_id'],'act'=>'ping'));?>">点评<em>&nbsp;</em></a>
                       <a class="radius3 sy_sjcpCzA" href="<?php echo U('shop/detail',array('shop_id'=>$item['shop_id'],'act'=>'map'));?>"><em>&nbsp;</em></a>
                                        </div>
                                    </div>
                                    <div class="sy_sjcpDwBg">&nbsp;</div>
                                </div>
                            </div>
                        </div>
                    </li> <?php endforeach; ?>
            </ul>
            <?php } endif; endforeach; endif; ?>
   </div>             
 <!--首页套餐部分结束-->

    <!--首页商城部分开始-->
    <div class="sy_FloorBt" data="top_1" id="floor1">
        <div class="left sy_FloorBtz"><span class="bt cl_1">热卖商品</span>
            <span class="sy_mall_tab">
                <?php $i=0; ?>       
                <?php if(is_array($goodscates)): foreach($goodscates as $key=>$item3): if(($item3["parent_id"]) == "0"): $i++;if($i <= 10){ ?>
                    <a target="_blank" <?php if($i == 1) echo 'class="on"'; ?> title="<?php echo ($item2["cate_name"]); ?>套餐" href="<?php echo LinkTo('mall/index',array('cat'=>$item3['cate_id']));?>"><?php echo ($item3["cate_name"]); ?></a>
                    <?php } endif; endforeach; endif; ?>
            </span>
        </div>
        <div class="right"><a  title="更多商品" target="_blank" href="<?php echo U('mall/index');?>">更多&gt;&gt;</a></div>
    </div>
<div class="nearbuy_cpList1">
            <ul>
                <?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Goods,audit =1 AND closed=0 AND city_id = $city_id,orderby asc,sold_num desc,0,8,600,,"); if(!$items= $cache->get($token)){ $items = D("Goods")->where("audit =1 AND closed=0 AND city_id = $city_id")->order("orderby asc,sold_num desc")->limit("0,8")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><li class="nearbuy_cpLi2">
                        <div class="sy_sjcpLi nearbuy_cpLik">
                            <a href="<?php echo U('mall/detail',array('goods_id'=>$item['goods_id']));?>"><img src="<?php echo config_img($item['photo']);?>" width="273" height="273" /></a>
                            <p class="sy_hottjbt"><?php echo ($item["title"]); ?></p>
                            <p class="sy_hottjp"><?php echo ($item["intro"]); ?></p>
                            <p class="sy_hottjJg"><span class="left">¥
                            <?php if(($item["max_price"] == $item["min_price"] AND $item["max_price"] > 0 )): echo round($item['max_price']/100,2);?>
                            <?php elseif(($item["max_price"] > 0) AND ($item["min_price"] > 0)): ?>
                            <?php echo round($item['min_price']/100,2);?>-<?php echo round($item['max_price']/100,2);?>
                            <?php else: ?>
                            <?php echo round($item['mall_price']/100,2); endif; ?>
                  <del>¥<?php echo round($item['price']/100,2);?></del></span><span class="right">已售<?php echo ($item["sold_num"]); ?></span></p>
                            <hr style=" border:none 0px; border-bottom: 1px solid #e0e0e0; margin-top:6px;" />
                            <p class="nearbuy_cpLiPf"><span style="cursor: pointer;" title="<?php echo ($shops[$item['shop_id']]['addr']); ?>" class="left"><em></em><?php echo ($item["views"]); ?>人已关注</span><span class="right">
                            <?php if($item['num'] <= 0): ?><a class="sy_hottjJd" target="_blank" href="javascript:void(0)" >没库存了</a>
                            <?php else: ?>
                            <a class="sy_hottjJd" target="_blank" href="<?php echo U('mall/detail',array('goods_id'=>$item['goods_id']));?>" >立即购买</a><?php endif; ?>
                            </span></p>
                            <div class="sy_sjcpBq">
                            <?php if($item['is_vs4'] == 1): ?><span class="sy_sjcpBq1">极速达</span><?php endif; ?>
                            <?php if($item['is_vs5'] == 1): ?><span class="sy_sjcpBq2">包邮</span><?php endif; ?>
                            <?php if($item['is_vs2'] == 1): ?><span class="sy_sjcpBq3">正品</span><?php endif; ?>
                            <?php if($item['is_vs6'] == 1): ?><span class="sy_sjcpBq4">到付</span><?php endif; ?></div>
                            <div class="sy_sjcpLiDw">
                              <div class="sy_sjcpDwBox goods_sjcpDwBox" style="height: 277px;">
						   <script type="text/javascript">
                            $(function () {
                                var str = "<?php echo ($host); echo u('wap/mall/detail',array('goods_id'=>$item['goods_id']));?>";
                                $("#goods_code_<?php echo ($item["goods_id"]); ?>").empty();
                                $('#goods_code_<?php echo ($item["goods_id"]); ?>').qrcode(str);
                            })
                          </script>
                                    <div class="sy_sjcpDwNr" style="padding-top:40px;">
                                    <?php if($item['mobile_fan'] > 0): ?><p>手机下单立减:<a style=" color:#F00"><?php echo round($item['mobile_fan']/100,2);?></a>元</p>
                                    <?php else: ?>
                                    <p>手机扫码购买</p><?php endif; ?>
                            <a href="<?php echo U('mall/detail',array('goods_id'=>$item['goods_id']));?>"><div class="sy_sjcpwx1"  id="goods_code_<?php echo ($item["goods_id"]); ?>"></div></a>
                                    </div>
                                    <div class="sy_sjcpDwBg">&nbsp;</div>
                                </div>
                            </div>
                        </div>
                    </li> <?php endforeach; ?>
            </ul>
        </div>
 <!--首页商城部分结束-->   

 

 

  <!--首页优惠券部分开始-->
    <div class="sy_FloorBt" data="top_8" id="floor8">
        <div class="left sy_FloorBtz"><i class="ico_8"></i><span class="bt cl_8">优惠券</span>
            <span class="sy_coupon_tab">
                <?php $couponcates = D('Shopcate')->fetchAll(); ?> 
                
                <?php if(is_array($couponcates)): foreach($couponcates as $key=>$item3): if(($item3["parent_id"]) == "0"): $i++;if($i <= 10){ ?>
                    <a target="_blank" href="<?php echo LinkTo('coupon/index',array('cat'=>$item3['cate_id']));?>"><?php echo ($item3["cate_name"]); ?></a>
                    <?php } endif; endforeach; endif; ?>
            </span>
        </div>
        <div class="right"><a target="_blank" href="<?php echo U('coupon/index');?>">更多&gt;&gt;</a></div>
    </div>
    <div class="sy_coupon_list_box">
        <ul class="sy_coupon_list">
            <?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Coupon,audit=1 AND closed=0 AND city_id=$city_id,600,downloads desc,views desc,0,5,,"); if(!$items= $cache->get($token)){ $items = D("Coupon")->where("audit=1 AND closed=0 AND city_id=$city_id")->order("downloads desc,views desc")->limit("0,5")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><li>
                    <div class="syPub_list">
                        <a target="_blank" title='<?php echo ($item["title"]); ?>' href="<?php echo U('coupon/detail',array('coupon_id'=>$item['coupon_id']));?>"><img src="<?php echo config_img($item['photo']);?>"  width="204" height="170" /></a>
                        <h3> <a target="_blank" title='<?php echo ($item["title"]); ?>' href="<?php echo U('coupon/detail',array('coupon_id'=>$item['coupon_id']));?>" class="overflow_clear"><?php echo ($item["title"]); ?></a></h3>
                        <div class="btn_box">
                            <div class="left"><p class="graycl">下载：<?php echo ($item["downloads"]); ?>次&nbsp;&nbsp;剩余：<?php echo ($item["num"]); ?>次</p></div>
                            <div class="right"> <a target="_blank" title='<?php echo ($item["title"]); ?>' href="<?php echo U('coupon/detail',array('coupon_id'=>$item['coupon_id']));?>" class="btn">下载</a></div>
                        </div>
                    </div>
                </li> <?php endforeach; ?>
        </ul>
    </div>

 
  <!--首页订座部分开始-->

    <div class="sy_FloorBt" data="top_5" id="floor5">
        <div class="left sy_FloorBtz"><i class="ico_5"></i><span class="bt cl_5">订座</span></div>
        <div class="right"><a target="_blank" href="<?php echo U('booking/index');?>">更多&gt;&gt;</a></div>
    </div>
    <div class="sy_seat_nr">
        <div class="left sy_seat_list">
            <ul>
                <?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Booking,600,audit=1 AND closed=0 AND  city_id=$city_id,0,4,shop_id asc,,"); if(!$items= $cache->get($token)){ $items = D("Booking")->where("audit=1 AND closed=0 AND  city_id=$city_id")->order("shop_id asc")->limit("0,4")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><li>
                        <div class="syPub_list">
                            <div class="img">
                                <a target="_blank" href="<?php echo U('booking/detail',array('shop_id'=>$item['shop_id']));?>">
                                    <img src="<?php echo config_img($item['photo']);?>"  width="205" height="160" />
                                    <p class="overflow_clear"><?php echo ($item["addr"]); ?></p>
                                </a>
                            </div>
                            <h3><a target="_blank" href="<?php echo U('booking/detail',array('shop_id'=>$item['shop_id']));?>" class="overflow_clear"><?php echo ($item["shop_name"]); ?></a></h3>
                            <p class="graycl">地址：<?php echo ($item['addr']); ?></p>
                            <hr style="border:none 0px; border-bottom: 1px solid #e0e0e0; margin-top:6px;" />
                            <div class="btn_box">
                                <div class="left"><p class="graycl">电话：<?php echo ($item["tel"]); ?></p></div>
                                <div class="right"><a target="_blank" href="<?php echo U('booking/detail',array('shop_id'=>$item['shop_id']));?>" class="btn">立即预订</a></div>
                            </div>
                        </div>
                    </li> <?php endforeach; ?>
            </ul>
        </div>

        <div class="right sy_seatSwitch">
            <ul class="sy_seatSwitch_tab">
                <li class="on">帮您找座位</li>
                <li>人气排行榜</li>
            </ul>

            <div class="sy_seatSwitch_box">
                <div class="sy_seatSwitch1" style="display:block;">
                    
                    <form action="<?php echo U('booking/lists');?>" method="post">
                        <div class="num_box">
                            <input class="name" type="text"   name="date" value="<?php echo TODAY; ?>" onfocus="WdatePicker({dateFmt: 'yyyy-MM-dd'});"  placeholder="日期" />
                            <select name="time" class="num">
                                <?php $cfg = D('Bookingsetting')->getCfg(); ?>
                                <?php if(is_array($cfg)): $i = 0; $__LIST__ = $cfg;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($item); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                            <div class="clear"></div>
                        </div>
                        <select name="area_id" class="addr">
                            <?php if(is_array($areas)): $i = 0; $__LIST__ = $areas;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i; if(($item["city_id"]) == $city_id): ?><option value="<?php echo ($item["area_id"]); ?>"><?php echo ($item["area_name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                        <div class="num_box">
                            <?php $room=D('Bookingroom')->getType(); ?>
                            <select name="number" class="num">
                                <?php if(is_array($room)): $i = 0; $__LIST__ = $room;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($item); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                            <input class="name" name="name" type="text" placeholder="商户名" />
                            <div class="clear"></div>
                        </div>
                        <input class="btn" type="submit" value="免费帮您订座" />
                    </form>

                </div>

                <div class="sy_seatSwitch1">
                    <ul class="hotBill">
                        <?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Booking,600,audit=1 AND closed=0 AND  city_id=$city_id,0,3,shop_id desc,,"); if(!$items= $cache->get($token)){ $items = D("Booking")->where("audit=1 AND closed=0 AND  city_id=$city_id")->order("shop_id desc")->limit("0,3")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><li>
                                <a href="<?php echo U('booking/detail',array('shop_id'=>$item['shop_id']));?>"><img src="<?php echo config_img($item['photo']);?>"  width="60" height="40" /></a>
                                <h3 class="overflow_clear"><a href="<?php echo U('booking/detail',array('shop_id'=>$item['shop_id']));?>"><?php echo ($item["shop_name"]); ?></a></h3>
                                <p><span class="spxq_qgpstarBg"><span class="spxq_qgpstar spxq_qgpstar<?php echo ($item["score"]); ?>"></span></span></p>
                            </li> <?php endforeach; ?>   
                    </ul>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <!--首页订座部分结束-->
    
    
    <!--首页同城信息部分开始-->
    <div class="sy_FloorBt" data="top_4" id="floor4">
        <div class="left sy_FloorBtz"><i class="ico_4"></i><span class="bt cl_4">同城信息</span></div>
        <div class="right"><a target="_blank" href="<?php echo U('life/main');?>">更多&gt;&gt;</a></div>
    </div>
    <div class="cityInfor_nr">
        <ul class="cityInfor_list" style="display:block;">
            <li class="nr">
                <h3>二手</h3>
                <div class="link">
                    <div class="left img ico_1"></div>
                    <ul>
                        <li><a  href="<?php echo U('life/index',array('cat'=>1));?>">手机及配件</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>2));?>">数码产品</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>4));?>">家用电器</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>5));?>">日常用品</a></li>
                    </ul>
                </div>
                <a href="<?php echo U('life/index');?>" class="more">more</a>
            </li>
            <li class="nr">
                <h3>车辆</h3>
                <div class="link">
                    <div class="left img ico_2"></div>
                    <ul>
                        <li><a href="<?php echo U('life/index',array('cat'=>21));?>">二手轿车</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>24));?>">电动车</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>25));?>">自行车</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>27));?>">摩托车/燃气车</a></li>
                    </ul>
                </div>
                <a href="<?php echo U('life/index');?>" class="more">more</a>
            </li>
            <li class="nr">
                <h3>房屋</h3>
                <div class="link">
                    <div class="left img ico_3"></div>
                    <ul>
                        <li><a href="<?php echo U('life/index',array('cat'=>47));?>">租房/出租</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>48));?>">个人租房</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>49));?>">二手房出售</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>52));?>">求租/求购</a></li>
                    </ul>
                </div>
                <a href="<?php echo U('life/index');?>" class="more">more</a>
            </li>
            <li class="nr">
                <h3>招聘</h3>
                <div class="link">
                    <div class="left img ico_4"></div>
                    <ul>
                        <li><a href="<?php echo U('life/index',array('cat'=>67));?>">工人/技工</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>68));?>">销售/金融</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>71));?>">人事/行政/文员</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>72));?>">营业员/促销/零售</a></li>
                    </ul>
                </div>
                <a href="<?php echo U('life/index');?>" class="more">more</a>
            </li>
            <li class="nr">
                <h3>服务</h3>
                <div class="link">
                    <div class="left img ico_5"></div>
                    <ul>
                        <li><a href="<?php echo U('life/index',array('cat'=>90));?>">招商加盟</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>95));?>">房屋装修</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>94));?>">公司注册</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>108));?>">搬家服务</a></li>
                    </ul>
                </div>
                <a href="<?php echo U('life/index');?>" class="more">more</a>
            </li>
            <li class="nr">
                <h3>培训</h3>
                <div class="link">
                    <div class="left img ico_6"></div>
                    <ul>
                        <li><a href="<?php echo U('life/index',array('cat'=>58));?>">中小学教育</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>59));?>">职业技能</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>60));?>">学历教育</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>61));?>">电脑培训</a></li>
                    </ul>
                </div>
                <a href="<?php echo U('life/index');?>" class="more">more</a>
            </li>
            <li class="nr">
                <h3>求职</h3>
                <div class="link">
                    <div class="left img ico_7"></div>
                    <ul>
                        <li><a href="<?php echo U('life/index',array('cat'=>38));?>">全职求职意向</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>39));?>">兼职求职意向</a></li>
                    </ul>
                </div>
                <a href="<?php echo U('life/main');?>" class="more">more</a>
            </li>
            <li class="nr">
                <h3>兼职</h3>
                <div class="link">
                    <div class="left img ico_8"></div>
                    <ul>
                        <li><a href="<?php echo U('life/index',array('cat'=>131));?>">家教/老师</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>132));?>">派发/促销</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>134));?>">学生兼职</a></li>
                        <li><a href="<?php echo U('life/index',array('cat'=>135));?>">餐厅/服务员</a></li>
                    </ul>
                </div>
                <a href="<?php echo U('life/index');?>" class="more">more</a>
            </li>
        </ul>
    </div>
    <!--首页同城信息部分结束-->
</div>


<script>
    $(function () {
        $("#fox-food li").each(function (e) {
            $(this).click(function (event) {
                $(".radius3").each(function (i) {
                    if (e == i){
                        $("html,body").animate({scrollTop: $(this).offset().top}, 500);
                        event.preventDefault();
                    }
                });
            });
        });
    });
    $(document).ready(function () {
        $(window).scroll(function () {
            var top = $(document).scrollTop();          //定义变量，获取滚动条的高度
            var menu = $("#fox-food");                      //定义变量，抓取#menu
            var items = $("#index-gun").find(".sy_FloorBt");    //定义变量，查找.item
            var curId = "";                             //定义变量，当前所在的楼层item #id 
            items.each(function () {
                var m = $(this);                        //定义变量，获取当前类
                var itemsTop = m.offset().top;        //定义变量，获取当前类的top偏移量
                if (top > itemsTop - 300) {
                    curId = "#" + m.attr("id");
                } else {
                    return false;
                }
            });
            //给相应的楼层设置cur,取消其他楼层的cur
        	var curLink = menu.find(".cur");
     		if (curId && curLink.attr("href") != curId) {
                curLink.removeClass("cur");
                menu.find("[href=" + curId + "]").addClass("cur");
            }
        });
        $(window).scroll(function () {
            if ($(window).scrollTop() < 220) {
                $(".indexpop").css("top", "220px");
                $(".indexpop").css("bottom", "auto");
            }
            else {
                $(".indexpop").css("top", "40px");
                $(".indexpop").css("bottom", "auto");
            }
        });
    });
</script>
<div class="footerOut">
    <?php if($ctl == index): ?><div class="foot_yqlj">
        
            友情链接：
            <?php  $cache = cache(array('type'=>'File','expire'=> 21600)); $token = md5("Links,audit=1 AND colsed=0 AND city_id=$city_id,0,10,21600,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Links")->where("audit=1 AND colsed=0 AND city_id=$city_id")->order("orderby asc")->limit("0,10")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><a target="_blank" href="<?php echo ($item["link_url"]); ?>"><?php echo ($item["link_name"]); ?></a> <?php endforeach; ?>
            <a target="_blank" href="<?php echo U('home/public/apply_link');?>">申请链接</a>
        </div><?php endif; ?>

    <div class="footer">
        <div class="footNav">
            <div class="left">
                <div class="footNavLi">
                    <ul>

                    	<li class="footerLi foot_contact">
                            <h3><i class="ico_1"></i>联系我们</h3>
                			<div class="nr">
                            	<p>客服电话：<b class="fontcl3"><?php echo ($CONFIG["site"]["tel"]); ?></b></p>
                                <p class="graycl">免费长途</p>
                                <p>在线客服：<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo ($CONFIG["site"]["qq"]); ?>&site=<?php echo ($CONFIG["site"]["host"]); ?>&menu=yes"><img src="__TMPL__statics/images/foot_btn.png"/></a></p>
                                <p>工作时间：周一至周日9:00-22:00</p>
                                <p class="graycl">法定节假日除外</p>
                            </div>
                        </li>

                        <li class="footerLi">
                            <h3><i class="ico_2"></i>公司信息</h3>
                            <ul class="list">
                                <li><a target="_blank" title="关于我们" href="<?php echo u('home/article/system',array('content_id'=>1));?>">关于我们</a></li>
                                <li><a target="_blank" title="联系我们" href="<?php echo u('home/article/system',array('content_id'=>3));?>">联系我们</a></li>
                                <li><a target="_blank" title="人才招聘" href="<?php echo u('home/article/system',array('content_id'=>2));?>">人才招聘</a></li>
                                <li><a target="_blank" title="免责声明" href="<?php echo u('home/article/system',array('content_id'=>6));?>">免责声明</a></li>
                            </ul>
                        </li>

                        <li class="footerLi">
                            <h3><i class="ico_3"></i>商户合作</h3>
                            <ul class="list">
                                <li><a href="<?php echo u('home/shop/apply');?>">商家入驻</a></li>
                                <li><a target="_blank" title="广告合作" href="<?php echo u('home/article/system',array('content_id'=>5));?>">广告合作</a></li>
                                <li><a href="<?php echo u('merchant/login/index');?>">商家后台</a></li>
                            </ul>
                        </li>
                        <li class="footerLi">
                            <h3><i class="ico_4"></i>用户帮助</h3>
                            <ul class="list">
                                <li><a target="_blank" title="服务协议" href="<?php echo u('home/article/system',array('content_id'=>2));?>">服务协议</a></li>
                                <li><a target="_blank" title="退款承诺" href="/">退款承诺</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="right"><p>扫一扫加关注</p><img src="<?php echo config_img($CONFIG['site']['wxcode']);?>" width="149" height="149" /></div>
        </div>
    </div>

	<div class="footerCopy">copyright 2013-2113 <?php echo ($_SERVER['HTTP_HOST']); ?> All Rights Reserved <?php echo ($CONFIG["site"]["sitename"]); ?>版权所有 - <?php echo ($CONFIG["site"]["icp"]); ?> <?php echo ($CONFIG["site"]["tongji"]); ?></div>
</div>  

<div class="topUp">
    <ul>
    	<li class="kefu"><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo ($CONFIG["site"]["qq"]); ?>&site=<?php echo ($CONFIG["site"]["host"]); ?>&menu=yes"><div class="kefu_open maincl">在线客服</div></a></li>
        <li class="topBack"><div class="topBackOn">回到<br />顶部</div></li>
        <li class="topUpWx"><div class="topUpWxk"><img src="<?php echo config_img($CONFIG['site']['wxcode']);?>" width="149" height="149" /><p class="maincl">扫描二维码关注微信</p></div></li>
    </ul>
</div>



<script>
    $(document).ready(function () {
        $(window).scroll(function () {
            if ($(window).scrollTop() > 100) {
                $(".topUp").show();
                $(".indexpop").show();
            } else {
                $(".topUp").hide();
                $(".indexpop").hide();

            }

            var ctl = "<?php echo ($ctl); ?>";
            if(ctl == 'coupon'){
                if ($(window).scrollTop() > 665) {
                    $(".spxq_xqT2").show();
                } else {
                    $(".spxq_xqT2").hide();
                }

            }else{
                if ($(window).scrollTop() > '<?php echo ($height_num); ?>') {
                    $(".spxq_xqT2").show();
                } else {
                    $(".spxq_xqT2").hide();
                }
            }
        });

        $(".topBack").click(function () {
            $("html,body").animate({scrollTop: 0}, 200);
        });

		$(window).scroll(function(){
			var top = $(document).scrollTop();          //定义变量，获取滚动条的高度
			var menu = $(".topUp");                      //定义变量，抓取topUp
			var items = $(".footerOut");    //定义变量，查找footerOut 
			items.each(function(){
				var m=$(this);
				var itemsTop = m.offset().top;      //定义变量，获取当前类的top偏移量
				if(itemsTop-360 <= top-360){
					menu.css('bottom','360px');
					menu.css('top','auto');
				}else{
					menu.css('bottom','0px');
					menu.css('top','auto');
				}
			});
		});
    });
</script>
</body>
</html>
<?php if (!defined('THINK_PATH')) exit(); if(!empty($CONFIG[other][wap_index_addr])): ?><!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<meta charset="utf-8">
		<title>
			<?php if(!empty($mobile_title)): echo ($mobile_title); ?>_<?php endif; ?>
			<?php echo ($CONFIG["site"]["sitename"]); ?>会员专区
		</title>
		<meta name="keywords" content="<?php echo ($mobile_keywords); ?>" />
		<meta name="description" content="<?php echo ($mobile_description); ?>" />
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
		<link rel="stylesheet" href="/static/default/wap/css/base.css">
		<link rel="stylesheet" href="/static/default/wap/css/<?php echo ($ctl); ?>.css?V=2" />
		<script src="/static/default/wap/js/jquery.js"></script>
		<script src="/static/default/wap/js/base.js"></script>
		<script src="/static/default/wap/other/layer.js"></script>
		<script src="/static/default/wap/other/roll.js"></script>
		<script src="/static/default/wap/js/public.js"></script>
		<script src="/static/default/wap/js/mobile_common.js"></script>
		<script src="/static/default/wap/js/iscroll-probe.js"></script>
        <script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=C9613fa45f450daa331d85184c920119"></script>
		<script type="text/javascript" src="http://developer.baidu.com/map/jsdemo/demo/convertor.js"></script>
		<script>
			function bd_encrypt(gg_lat, gg_lon){
				var x_pi = 3.14159265358979324 * 3000.0 / 180.0;
				var x = gg_lon;
				var y = gg_lat;
				var z = Math.sqrt(x * x + y * y) + 0.00002 * Math.sin(y * x_pi);
				var theta = Math.atan2(y, x) + 0.000003 * Math.cos(x * x_pi);
				var bd_lon = z * Math.cos(theta) + 0.0065;
				var bd_lat = z * Math.sin(theta) + 0.006;
				$.ajax({
					type: "GET",
					url: "/wap/near/csdwpl/",
					dataType: "json",
					data: {
						lat: bd_lat,
						lng: bd_lon
					},
					success: function(data) {
						if(data.cityid == 9999) {
							//layer.msg('您当前所在：'+data.city+'站');
						} else if(data.moren == 1) {
							//alert(data.moren);
							layer.open({
								type: 1,
								title: '城市设置',
								skin: 'layer-ext-moon', //加上边框
								area: ['90%', 'auto'], //宽高
								content: '<div class="chengshi"><div class="dingwei">目前定位地区:<b>' + data.city + '</b></div><div class="dyts">您上次访问<b>' + data.mcity + '</b>分站点，是否切换至</div><div class="dybt"><div class="btn1"><a href="/wap/city/change/city_id/' + data.mcityid + '.html">' + data.mcity + '分站</a></div><div class="btn2"><a href="/wap/city/change/city_id/' + data.cityid + '.html">' + data.city + '分站</a></div></div></div>'
							});
						} else if(data.cityid == 0) {
							layer.open({
								type: 1,
								title: '城市设置',
								skin: 'layer-ext-moon', //加上边框
								area: ['90%', 'auto'], //宽高
								content: '<div class="chengshi"><div class="dingwei">目前定位地区:<b>' + data.city + '</b></div><div class="dyts">目前暂时未开通该地区同城配送服务，默认前往主站，快递配送</div><div class="dybt"><div class="btn1"><a href="/wap/city/change/city_id/1.html">确定</a></div><div class="btn2"><a href="/wap/city/">切换城市</a></div></div></div>'
							});
						} else {
							layer.open({
								type: 1,
								title: '城市设置',
								skin: 'layer-ext-moon', //加上边框
								area: ['90%', 'auto'], //宽高
								content: '<div class="chengshi"><div class="dingwei">目前定位地区:<b>' + data.city + '</b></div><div class="dyts">如您要赠送给其他地区亲友，可切换城市免运费配送</div><div class="dybt"><div class="btn1"><a href="/wap/city/change/city_id/' + data.cityid + '.html">确定</a></div><div class="btn2"><a href="/wap/city/">切换城市</a></div></div></div>'
							});
						}
					}
				});
			}
			navigator.geolocation.getCurrentPosition(function(position) {
				bd_encrypt(position.coords.latitude, position.coords.longitude);
			});
		</script>
	</head>

<style>
.chengshi {padding:10px;}
.dingwei {text-align:center;line-height:20px;border-bottom:1px solid #C3C1C1;padding-bottom:10px;margin-bottom:10px;}
.dyts {text-align:center;line-height:20px;font-size:14px;color:#74777B;}
.dybt {background:#fff;width:100%;padding:10px;overflow:hidden;}
.btn1 a,.btn2 a {width:49.8%;line-height:35px;text-align:center;color:#fff;background:#BD1A1F;float:left;border-radius:5px;}
.btn2 a {float:right;}

</style>
<body>
<?php else: ?>
 <!DOCTYPE html>
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
	<body><?php endif; ?>
<script src="/static/default/wap/other/roll.js"></script>
<script src="__TMPL__statics/js/jquery.flexslider-min.js" type="text/javascript" charset="utf-8"></script>
<script src="__TMPL__statics/js/swiper.min.js" type="text/javascript" charset="utf-8"></script>
<style>
	.top-fixed {background: #Fff;}
	.bg-inverse, .bg-inverse a{color:#333}
.top-fixed .top-search{margin: 0 0 0 22%;}
.top-fixed .top-search { width:72%}
.top-fixed .top-search input {border-radius: 20px;background: rgb(228,228,228);}
/* 轮播部分 */
.bd{margin:0 10px}
.focus .bd li img{border-radius: 6px;}
.container1 .icon-angle-right{font-size: 28px;
    margin-top: 4px;
    display: inline-block;
    color: #999;}
	.goods_limit_buy .locatLabel_switch .box{
		padding: 5px;
		box-sizing: border-box;
		border-radius: 8px;
	}
	.goods_limit_buy .locatLabel_switch .box:nth-child(even){
		background:rgb(225,232,248) ;
	
	}
	.goods_limit_buy .locatLabel_switch .box:nth-child(odd){
		background:rgb(224,243,250) ;
		
	}
</style>
<header class="top-fixed bg-yellow bg-inverse">
			<div class="top-local">
				<a href="<?php echo U('city/index');?>" class="top-addr">
					<?php echo bao_msubstr($city_name,0,4,false);?><i class="icon-angle-down"></i></a>
			</div>
			<div class="top-search">
				<form method="post" action="<?php echo U('all/index');?>">
					<input name="keyword" placeholder="<?php echo ($CONFIG[other][wap_search_title]); ?>" />
					<button type="submit" class="icon-search"></button>
				</form>
			</div>
<!--			<div class="top-signed">-->
<!--				<a id="search-btn" href="<?php echo u('wap/sign/signed');?>"><i class="icon-star-half-o"></i> 签到</a>-->
<!--			</div>-->
		</header>
		
		<div id="focus" class="focus">
			<div class="hd"><ul></ul></div>
			<!--下面的limit="0,2"是幻灯的个数，2代表2张图，以此类推，site_id=57是你广告位的ID-->
			<div class="bd">
				<ul>
					<?php  $cache = cache(array('type'=>'File','expire'=> 7200)); $token = md5("Ad, closed=0 AND site_id=57 AND city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ,0,3,7200,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Ad")->where(" closed=0 AND site_id=57 AND city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ")->order("orderby asc")->limit("0,3")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><li>
							<a href="<?php echo U('app/ad/click',array('ad_id'=>$item['ad_id'],'aready'=>2));?>"><img src="<?php echo config_img($item['photo']);?>" /></a>
						</li> <?php endforeach; ?>
				</ul>
			</div>
		</div>
		<script type="text/javascript">
			TouchSlide({
				slideCell: "#focus",
				titCell: ".hd ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
				mainCell: ".bd ul",
				effect: "left",
				autoPlay: true, //自动播放
				autoPage: true, //自动分页
				switchLoad: "_src", //切换加载，真实图片路径为"_src", 
			});
		</script>

<!--		<section class="invote index_house">-->
<!--			<a href="<?php echo U('community/index');?>"><img src="/static/default/wap/image/house.png">我的社区服务</a>-->
<!--		</section>-->
	<style>	
		.navtab{
			padding: 10px;
			border: 1px solid  red;
		}
	</style>

		
        <div id="index" class="page-center-box">
       

        <?php if($CONFIG[other][wap_navigation] == 1): ?><script>
          $(document).ready(function () {
             $('.navigation_index_cate').flexslider({
                directionNav: true,
                pauseOnAction: false,
             });
          });
		</script>
		
        <!-- <div class="banner_navigation"> 
                <div class="navigation_index_cate"> 
                    <ul class="slides">
                        <?php if(is_array($nav)): $i = 0; $__LIST__ = $nav;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i; if($i%8 == 1): ?><li class="list">
                                    <ul class="cate">
                                        <li>
                                            <a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>"><img src="<?php echo config_img($item['photo']);?>">
                                                <p><?php echo ($item["nav_name"]); ?></p></a>
                                        </li>
                                        <?php elseif($i%8 == 0): ?>        

                                        <li>
                                            <a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>"><img src="<?php echo config_img($item['photo']);?>">
                                                <p><?php echo ($item["nav_name"]); ?></p></a>
                                        </li>
                                    </ul>
                                </li>
                                <?php else: ?>
                                <li>
                                    <a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>"><img src="<?php echo config_img($item['photo']);?>">
                                        <p><?php echo ($item["nav_name"]); ?></p></a>
                                </li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                    </ul>  
                </div> 
            </div> -->
         <?php else: ?> 
			<script>
				$(document).ready(function() {
					$('.flexslider_cate').flexslider({
						directionNav: true,
						pauseOnAction: false,
					});
				});
			</script>
			<!-- <div class="banner mb10">
				 <div class="flexslider_cate">
					<ul class="slides">
						<?php if(is_array($nav)): $i = 0; $__LIST__ = $nav;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i; if($i%8 == 1): ?><li class="list">
									<ul class="cate">
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div class="icon <?php echo ($item["ioc"]); ?> <?php echo ($item["colour"]); ?>"></div>
												<p>
													<?php echo ($item["nav_name"]); ?>
												</p>
											</a>
										</li>
										<?php elseif($i%8 == 0): ?>
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div class="icon <?php echo ($item["ioc"]); ?> <?php echo ($item["colour"]); ?>"></div>
												<p>
													<?php echo ($item["nav_name"]); ?>
												</p>
											</a>
										</li>
									</ul>
								</li>
								<?php else: ?>
								<li>
									<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
										<div class="icon <?php echo ($item["ioc"]); ?> <?php echo ($item["colour"]); ?>"></div>
										<p>
											<?php echo ($item["nav_name"]); ?>
										</p>
									</a>
								</li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					</ul>
				</div><?php endif; ?>
			</div> -->
			<div class="banner mb10">
				<div class="flexslider_cate">
					<ul class="slides">
						<?php if(is_array($nav)): $i = 0; $__LIST__ = $nav;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i; if($i%8 == 1): ?><li class="list">
									<ul class="cate">
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div>
													<img src="/static/default/wap/image/indexicon/01.png" alt="">
												</div>
												<p>
													脸部轮廓
												</p>
											</a>
										</li>
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div>
													<img src="/static/default/wap/image/indexicon/02.png" alt="">
												</div>
												<p>
													玻尿酸
												</p>
											</a>
										</li>
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div>
													<img src="/static/default/wap/image/indexicon/03.png" alt="">
												</div>
												<p>
													除皱瘦脸
												</p>
											</a>
										</li>
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div>
													<img src="/static/default/wap/image/indexicon/04.png" alt="">
												</div>
												<p>
													眼部
												</p>
											</a>
										</li>
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div>
													<img src="/static/default/wap/image/indexicon/05.png" alt="">
												</div>
												<p>
													鼻部整形
												</p>
											</a>
										</li>
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div>
													<img src="/static/default/wap/image/indexicon/06.png" alt="">
												</div>
												<p>
													生活美容
												</p>
											</a>
										</li>
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div>
													<img src="/static/default/wap/image/indexicon/07.png" alt="">
												</div>
												<p>
													皮肤管理
												</p>
											</a>
										</li>

										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div>
													<img src="/static/default/wap/image/indexicon/08.png" alt="">
												</div>
												<p>
													美体塑型
												</p>
											</a>
										</li>
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div>
													<img src="/static/default/wap/image/indexicon/09.png" alt="">
												</div>
												<p>
													纹眉睫毛
												</p>
											</a>
										</li>
										<li>
											<a href="<?php echo config_navigation_url($item['url'],2);?>?nav_id=<?php echo ($item['nav_id']); ?>">
												<div>
													<img src="/static/default/wap/image/indexicon/10.png" alt="">
												</div>
												<p>
													医学美肤
												</p>
											</a>
										</li>
									</ul>
								</li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
					</ul>
				</div>
         <style>
			 .newgoods{
				 background: #fff;
				}
		 </style>
		<div>
			
		</div>


		 <!--下一段开始 最新商品-->   
		 <div class="blank-10 bg" style="border-bottom: thin solid #eee;">	</div>
			<div class="newgoods">

			
		
				<div class="goods_title">
					<span class="left">最新商品</span>
				</div>
				<div class="goods_limit_buy mb10">
					<div class="locatLabel_switch swiper-container5">
						<div class="swiper-wrapper">
							<?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Goods,audit =1 AND closed=0 AND city_id = $city_id AND end_date >= '{$today}',orderby asc,sold_num desc,0,9,600,,"); if(!$items= $cache->get($token)){ $items = D("Goods")->where("audit =1 AND closed=0 AND city_id = $city_id AND end_date >= '{$today}'")->order("orderby asc,sold_num desc")->limit("0,9")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?><div class="box swiper-slide">
								<a href="<?php echo U('mall/detail',array('goods_id'=>$item['goods_id']));?>">
									
								    <p class="txt_center overflow_clear"><?php echo bao_msubstr($item['title'],0,4,false);?></p>
									<!-- <p class="txt_center fontcl1">&yen;<?php echo ($item['price']/100); ?></p> -->
									<img src="<?php echo config_img($item['photo']);?>" width="" height="">
								</a> 
							</div> <?php endforeach; ?>
						</div>
					</div>
					
					<script>
						var swiper = new Swiper('.swiper-container5', {
							pagination: '.swiper-pagination5',
							slidesPerView: 3,
							paginationClickable: true,
							spaceBetween: 10,
							autoplay: 3000,
							freeMode: true
						});
					</script>
				</div>
			</div>	
            <!--首页限时套餐结束-->
<!--			<div class="index-ads">-->
<!--				<div class="line border-bottom border-top">-->
<!--					<div class="x5 ad-1">-->
<!--						<?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Ad, closed=0 AND site_id=62 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ,0,1,600,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Ad")->where(" closed=0 AND site_id=62 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ")->order("orderby asc")->limit("0,1")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?>-->
<!--							<a href="<?php echo U('app/ad/click',array('ad_id'=>$item['ad_id'],'aready'=>2));?>"><img src="<?php echo config_img($item['photo']);?>"></a>-->
<!-- <?php endforeach; ?>-->
<!--					</div>-->
<!--					<div class="x7 border-left">-->
<!--						<div class="line">-->
<!--							<div class="x12 border-bottom ad-2">-->
<!--								<?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Ad, closed=0 AND site_id=63 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ,0,1,600,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Ad")->where(" closed=0 AND site_id=63 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ")->order("orderby asc")->limit("0,1")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?>-->
<!--									<a href="<?php echo U('app/ad/click',array('ad_id'=>$item['ad_id'],'aready'=>2));?>"><img src="<?php echo config_img($item['photo']);?>"></a>-->
<!-- <?php endforeach; ?>-->
<!--							</div>-->
<!--							<div class="x6 border-right ad-3">-->
<!--								<?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Ad, closed=0 AND site_id=64 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ,0,1,600,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Ad")->where(" closed=0 AND site_id=64 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ")->order("orderby asc")->limit("0,1")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?>-->
<!--									<a href="<?php echo U('app/ad/click',array('ad_id'=>$item['ad_id'],'aready'=>2));?>"><img src="<?php echo config_img($item['photo']);?>"></a>-->
<!-- <?php endforeach; ?>-->
<!--							</div>-->
<!--							<div class="x6 ad-3">-->
<!--								<?php  $cache = cache(array('type'=>'File','expire'=> 600)); $token = md5("Ad, closed=0 AND site_id=65 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ,0,1,600,orderby asc,,"); if(!$items= $cache->get($token)){ $items = D("Ad")->where(" closed=0 AND site_id=65 AND  city_id IN ({$city_ids}) and bg_date <= '{$today}' AND end_date >= '{$today}' ")->order("orderby asc")->limit("0,1")->select(); $cache->set($token,$items); } ; $index=0; foreach($items as $item): $index++; ?>-->
<!--									<a href="<?php echo U('app/ad/click',array('ad_id'=>$item['ad_id'],'aready'=>2));?>"><img src="<?php echo config_img($item['photo']);?>"></a>-->
<!-- <?php endforeach; ?>-->
<!--							</div>-->
<!--						</div>-->
<!--					</div>-->
<!--				</div>-->
<!--			</div>-->
<!-- 今日资讯 -->
			<div class="blank-10 bg" style="border-bottom: thin solid #eee;">
			</div>
			<div class="tab index-tab" data-toggle="click">
				<div class="tab-head">
					<ul class="tab-nav line">
                        <li class="x4 active"><a href="#tab-active">今日资讯</a></li>
						<li class="x4"><a href="#tab-shop">附近商家</a></li>
<!--						<li class="x4"><a href="#tab-coupon">附近小区</a></li>-->
					</ul>
				</div>
				<div class="tab-body">
                  <div class="tab-panel active" id="tab-active">
						<ul class="index-tuan">
							<?php if(is_array($news)): $index = 0; $__LIST__ = $news;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($index % 2 );++$index;?><div class="container1" onclick="location='<?php echo U('news/detail',array('article_id'=> $item['article_id']));?>'">
									<img class="x2" src="<?php echo config_img($item['photo']);?>">
									<div class="des x8">
										<h5><?php echo bao_msubstr($item['title'],0,14,false);?></h5>
										<p class="info"><span>作者：<?php echo ($item["source"]); ?></span></p>
									</div>
									<i class="icon-angle-right"></i>
									<!-- <div class="des x2">
										<div class="intro2">
											<?php echo ($item["views"]); ?>
										</div>
									</div> -->
								</div><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
						<div class="more">
							<a href="<?php echo U('news/index');?>">查看更多资讯</a>
						</div>
					</div>
                    
                    
					<div class="tab-panel" id="tab-shop">
						<ul class="line index-tuan">
							<?php if(is_array($shoplist)): $index = 0; $__LIST__ = $shoplist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($index % 2 );++$index;?><div class="container1" onclick="location='<?php echo U('shop/detail',array('shop_id'=>$item['shop_id']));?>'">
									<img class="x2" src="<?php echo config_img($item['photo']);?>">
									<div class="des x8">
				<?php $business = D('Business') -> where('business_id ='.$item['business_id']) -> find(); $business_name = $business['business_name']; ?>
										<h5><?php echo bao_msubstr($item['shop_name'],0,10,false);?>
                                        	<a style="color:#999; margin-left:10px;"><?php echo ($business_name); ?>商圈 &nbsp;<?php echo ($item["d"]); ?></a>
                                        </h5>
										<p class="intro">地址：<?php echo bao_msubstr($item[ 'addr'],0,12,false);?></p>
									</div>
								</div><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
						<div class="more">
							<a href="<?php echo U('shop/index');?>">查看更多商家</a>
						</div>
					</div>
					<div class="tab-panel" id="tab-coupon">
						<ul class="index-tuan">
							<?php if(is_array($community)): $index = 0; $__LIST__ = $community;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($index % 2 );++$index;?><div class="container1" onclick="location='<?php echo U('community/detail',array('community_id'=> $item['community_id']));?>'">
									<img class="x2" src="<?php echo config_img($item['pic']);?>">
									<div class="des x8">
										<h5><?php echo bao_msubstr($item['name'],0,10,false);?></h5>
										<p class="intro">地址：<?php echo bao_msubstr($item[ 'addr'],0,12,false);?></p>
									</div>
									<div class="des x2">
										<div class="intro2" style="width: auto; padding:0 3px;"><?php echo ($item["d"]); ?></div>
									</div>
								</div><?php endforeach; endif; else: echo "" ;endif; ?>
						</ul>
						<div class="more">
							<a href="<?php echo U('community/index');?>">查看更多小区</a>
						</div>
					</div>
					
				</div>
			</div>
			<div class="blank-10"></div>

			

			<div class="blank-10 bg"></div>
			<div class="index-title">
				<h4>猜您喜欢</h4>
				<em><a href="<?php echo U('mall/index');?>">更多商品 <i class="icon-angle-right"></i></a></em>
			</div>
			<div class="line index-tuan">
				<ul id="index-tuan">
					<script>
						$(document).ready(function() {
							loaddata('<?php echo U("mall/push",array("t"=>$nowtime,"p"=>"0000"));?>', $("#index-tuan"), true);
						});
					</script>
				</ul>
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
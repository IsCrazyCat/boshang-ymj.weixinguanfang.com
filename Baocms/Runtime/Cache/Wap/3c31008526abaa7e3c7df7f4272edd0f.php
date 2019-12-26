<?php if (!defined('THINK_PATH')) exit(); $mobile_title = $detail['title']; ?>
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
	<body>   
<style>
.top-fixed { background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0))!important;position: absolute;border: none;}
.cartadd_num{ font-size:12px;}
/*属性弹出下拉菜单css*/
.navContent{display: block;}
.ui-sx{ width:100%; overflow:hidden;}
.subNavBox{ overflow:hidden; margin-left:10px; padding-bottom:0px; margin-right:10px; }
.subNav{background: url(../images/arrow_down_off.png) no-repeat right center;background-size: auto 13px;}
.subNav.on{background: url(../images/arrow_down_on.png) no-repeat right center;background-size: auto 14px;}
.subNav strong{ display:block;width:100%; height:40px;border-bottom:1px solid #eeeeee; color:#666; font-weight:normal; font-size:16px; line-height:40px;}
.currentDt{background-image:url(../images/jiantou2.png) no-repeat 97% center;background-size: auto 13px; }

.navContent li{overflow:hidden; width:100%;}
.navContent li .title{ width:100%;font-size:16px; line-height:30px; color:#666; margin-top:5px;}
.navContent li .item{ overflow:hidden; border-bottom:1px solid #eeeeee; padding-bottom:5px;}
.navContent li .item a{ padding-left:10px; padding-right:10px; border:1px solid #CCC; line-height:30px; display:inline-block;	-moz-border-radius:2px;-webkit-border-radius:2px;border-radius:2px; margin-right:5px; margin-bottom:10px;}
.navContent li .item a.hover{ background:#FF0000; color:#FFF; border:1px solid #FF0000;	-moz-border-radius:2px;-webkit-border-radius:2px;border-radius:2px;}

.navContent li .purchase{ font-size:14px; line-height:25px; color:#666;}
.navContent li .purchase_v{font-size:16px; line-height:25px; color:#dd2724; font-weight:bold}
.navContent li h2{ font-size:18px; line-height:30px; color:#333; font-weight:normal}
.navContent li p{ width:95%; overflow:hidden; height:25px; line-height:25px; margin:auto}
.navContent li .key{ display:block; float:left; width:30%; font-size:14px; color:#666; text-align:right;}
.navContent li .p-price-v{display:block;width:70%; float:left; font-size:16px;color:#dd2724;}

.navContent li .title1{ width:100%;font-size:16px; height:20px; line-height:20px; color:#666; padding-top:10px}
.navContent li .item1{ width:100%;height:50px; overflow:hidden; padding-bottom:5px;}
.navContent li .item1 a{ padding-left:10px; padding-right:10px; border:1px solid #CCC; line-height:30px; display:inline-block;	-moz-border-radius:2px;-webkit-border-radius:2px;border-radius:2px; margin-right:5px; margin-bottom:10px;}
.navContent li .item1 a.hover{ background:#FF0000; color:#FFF; border:0;	-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;}
.navContent li .item1 .ui-number{ border:1px solid #ddd9da; overflow:hidden; height:30px; display:block;width:106px; margin-top:10px;}

.navContent li .item1 .ui-number .decrease{ display:inline-block; background:none; font-size:24px; line-height:30px; border:0;width:30px; float:left; height:30px; color:#F8849C; border-right:1px solid #ddd9da;text-indent:-9999px;background: url(__PUBLIC__/images/shop-cart.png) no-repeat 6px -25px; background-size: 60px}
.navContent li .item1 .ui-number .num{display:inline-block;border:0;width:40px; height:30px;float:left; text-align:center; font-size:12px; line-height:30px; color:#666;font-size:20px;text-align:center}
.navContent li .item1 .ui-number .increase{display:inline-block;background:none;border:0;border-left:1px solid #ddd9da;float:right; width:30px; height:30px;font-size:24px; line-height:30px;color:#F8849C;background: url(__PUBLIC__/images/shop-cart.png) no-repeat -23px -25px; background-size: 60px;text-indent:-9999px}
.row {padding: 15px 10px;}
.tuan-detail2 {border-top: 0px solid #eee;}
.txt-small .txt {border: none;}
.margin-small-top2 {margin-top: 5px;}
</style>
<script src="/static/default/wap/other/roll.js"></script>
	<header class="top-fixed bg-yellow bg-inverse">
		<div class="top-back">
			<a class="top-addr inner-back" href="<?php echo U('mall/index');?>"></a>
		</div>
		<div class="top-title">
			商品详情
		</div>
		<div class="top-share">
			<a href="<?php echo U('mall/cart');?>" class="inner-cart" id="share-btn"></a>
		</div>
	</header>
   
    
<div class="tuan-detail">
<div class="line banner">	
	<div id="focus" class="focus">
		<div class="hd">
			<ul></ul>
		</div>
		<div class="bd">
			<ul>
          		<li><a href="javascript:void(0);"><img src="<?php echo config_img($detail['photo']);?>" /></a></li>
                <?php if(is_array($pics)): foreach($pics as $key=>$item): ?><li><a href="javascript:void(0);"><img src="<?php echo config_img($item['photo']);?>" /></a></li><?php endforeach; endif; ?>
			</ul>
		</div>
	</div>
   </div>
</div>             
	<script type="text/javascript">
		TouchSlide({ 
			slideCell:"#focus",
			titCell:".hd ul", 
			mainCell:".bd ul", 
			effect:"left", 
			autoPlay:true,//自动播放
			autoPage:true, //自动分页
			switchLoad:"_src" 
		});
	</script>

<div class="item-detail">
			<div class="detail-row bb">
				<div class="item-price">
                   <h1><?php echo bao_msubstr($detail['title'],0,24);?></h1>
					<p class="price" id="goods_price">&yen; <?php echo round($detail['mall_price']/100,2);?> 
                        <span class="pre-price-tips">
                        <?php $zhe = round($detail['mall_price']/$detail['price']*10,1); ?>
                        <?php if($zhe != 10): ?><span class="dt-icon"><?php echo round($detail['mall_price']/$detail['price']*10,1);?>折</span><?php endif; ?>
                                <?php if(!empty($detail['use_integral'])): ?><span class="dt-icon" style="background-color:#ff9204;">积分抵扣 &yen;<?php echo round($detail['use_integral']/100,2);?></span><?php endif; ?>
                                <?php if(!empty($detail['mobile_fan'])): ?><span class="dt-icon" style="background-color: #F00;">下单立减 &yen;<?php echo round($detail['mobile_fan']/100,2);?></span><?php endif; ?>
                        </span>
                    </p>  
                                    
                    <div class="x12 price-tip">
                        <span class="text-gray">原价：<del>&yen; <?php echo round($detail['price']/100,2);?></del></span>
                        <span class="text-gray text-small">销量：<?php echo ($detail['sold_num']); ?>笔 </span>
                    </div>
                    <?php if(!empty($detail['sold_num']) && !empty($count_goodsfavorites)): ?><div class="x12 price-tip">
                            <?php if(!empty($detail['sold_num'])): ?><span class="dt-icon" style="background-color: #F00;">月售：<?php echo ($detail['sold_num']); ?> 笔</span><?php endif; ?>
                            <?php if(!empty($count_goodsfavorites)): ?><span class="dt-icon" style="background-color: #F00;">收藏人数： <?php echo ($count_goodsfavorites); ?> </span><?php endif; ?>
                        </div><?php endif; ?>
				</div>
			</div>
            <?php if(!empty($is_vs)): ?><div class="detail-row bb">
				<div class="item-tips">
					<?php if(($detail["is_vs1"]) == "1"): ?><em><span class="text-green"><i class="check-circle"></i></span>认证商家</em><?php endif; ?>
                    <?php if(($detail["is_vs2"]) == "1"): ?><em><span class="text-green"><i class="check-circle"></i></span>正品保证</em><?php endif; ?>
                    <?php if(($detail["is_vs3"]) == "1"): ?><em><span class="text-green"><i class="check-circle"></i></span>假一赔十</em><?php endif; ?>
                    <?php if(($detail["is_vs4"]) == "1"): ?><em><span class="text-green"><i class="check-circle"></i></span>当日送达</em><?php endif; ?>
                    <?php if(($detail["is_vs6"]) == "1"): ?><em><span class="text-green"><i class="check-circle"></i></span>货到付款</em><?php endif; ?>
                    <?php if(($detail["is_vs5"]) == "1"): ?><em><span class="text-green"><i class="check-circle"></i></span>免运费</em><?php endif; ?>
				</div>
			</div><?php endif; ?>
            
		</div>
       </div>
			
		<div class="blank-10 bg"></div>
        <!--商品属性-->
<?php if(!empty($filter_spec)): ?><section id="search_ka">
<!---属性---->
<div class="ui-sx bian1"> 
<div class="subNavBox"> 
	<div class="subNav"><strong>选择商品属性</strong></div>
    <ul class="navContent"> 
    <?php if(is_array($filter_spec)): foreach($filter_spec as $key=>$spec): ?><li>   
          <div class="title"><?php echo ($key); ?></div>
          <div class="item">
          <?php if(is_array($spec)): foreach($spec as $k2=>$v2): ?><a href="javascript:;" onclick="switch_spec(this);" title="<?php echo ($v2[item]); ?>" <?php if($k2 == 0): ?>class="hover"<?php endif; ?>>
	          	<input type="radio" style="display:none;" name="goods_spec[<?php echo ($key); ?>]" value="<?php echo ($v2[item_id]); ?>" <?php if($k2 == 0 ): ?>checked="checked"<?php endif; ?>/>
          		<?php echo ($v2[item]); ?>            
          	  </a><?php endforeach; endif; ?>
          </div>                    
    </li><?php endforeach; endif; ?>
       </ul>  
    </div>
    </div>    
</section><?php endif; ?>

    <div class="x12 row">
		<span class="float-left margin-small-top2">购买数量：<span class="text-small text-gray"></span></span>
			<span class="float-right">
				<span class="txt txt-small radius-small bg-dot decrease" onClick="goods_cut();">-</span>
				<span class="txt-border txt-small radius-small border-gray">
                    <input type="text" class="txt txt-small radius-small  bg-white num"  id="goods_num" name="goods_num" value="1" min="1" max="1000"/>
                </span>
                <input type="hidden" name="goods_id" value="<?php echo ($goods["goods_id"]); ?>"/>
				<span class="txt txt-small radius-small bg-dot increase" onClick="goods_add();">+</span>
                <span>&nbsp;&nbsp;（库存<span id="stock"><?php echo ($detail['num']); ?></span>件）</span>
			</span>
	</div>
   
      <div class="blank-10"></div>
        <!--商品属性-->
        
       <div class="tuan-detail2">
       <div class="line status">
			<div class="x6">
				<span class="ui-starbar"><span style="width:<?php echo round($score*10,2);?>%"></span></span>
			</div>
			<div class="x6">
				<span class="float-right"><a href="<?php echo U('mall/dianping',array('goods_id'=>$detail['goods_id']));?>"><?php echo ($pingnum); ?>人评价了该商品 </a><i class="icon-angle-right"></i></span>
			</div>
		</div> 
       </div> 
        
		
		<div class="blank-10 bg"></div>
		
        
        <div class="item-intro">
			<h2>购买须知</h2>
			<div class="intro-bd"><?php echo cleanhtml($detail['instructions']);?></div>
		</div>
        <div class="blank-10 bg"></div>
        
		<div class="item-intro">
			<h2>商品介绍</h2>
            <div id="focus" class="global_focus intro-bd">
             <?php echo ($detail["details"]); ?>   
            </div>
		</div>		
		
        <?php if(!empty($recom)): ?><!--如果看了又看不等于空-->
          <div class="blank-10 bg"></div>
            <div class="item-list item-intro" id="item-list">
                <h2>看了又看</h2>
                <ul>
                <?php if(is_array($recom)): $index = 0; $__LIST__ = $recom;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($index % 2 );++$index;?><li class="line">
                    <a href="<?php echo U('mall/detail',array('goods_id'=>$item['goods_id']));?>">
                    <div class="x3">
                        <img src="<?php echo config_img($item['photo']);?>" />
                    </div>
                    <div class="x9">
                        <h5><?php echo ($item["title"]); ?></h5>
                        <p class="desc"><?php echo bao_Msubstr($item[instructions],0,60);?></p>
                        <p class="info">
                            <span>&yen;<?php echo round($item['mall_price']/100,2);?></span><del>&yen;<?php echo round($item['price']/100,2);?></del>
                            <em>已售<?php echo ($item["sold_num"]); ?></em>
                    </div>
                    </a>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
                </ul>
            </div><?php endif; ?>
        
        
   
<div data-spm="action" class="item-action">
    <?php if(!empty($shop['qq'])): ?><div class="active-contact">
            <a data-spm="dwangwang" href="mqqwpa://im/chat?chat_type=wpa&uin=<?php echo ($shop['qq']); ?>">
            <span class="icon"></span>客服</a>
        </div><?php endif; ?>
    
    <div class="<?php if(!empty($goodsfavorites)): ?>toggle-collect-news<?php else: ?>toggle-collect<?php endif; ?>" >
        <a target="x-frame" href="<?php echo U('mall/favorites',array('goods_id'=>$detail['goods_id']));?>"> 
        <span class="icon"></span>收藏</a>
    </div>
    <div class="go-store">
        <a data-spm="dstore" href="<?php echo U('shop/detail',array('shop_id'=>$detail['shop_id']));?>">
        <span class="icon"></span>店铺</a>
    </div>
    <div id="bottom-cart-entrance" class="add-to-cart cartadd2">加入购物车</div>
     <?php if($detail['num'] <= 0): ?><div class="buy-now "><a>暂无库存</a></div>
     <?php else: ?>
     <div class="buy-now"><a href="javascript:" class="buy_now" >立即预约</a></div><?php endif; ?>
</div>

<script>
        $(document).ready(function () {
        	
            $(".cartadd2").click(function(){
            	
               var url = "<?php echo U('mall/cartadd2');?>";
               var goods_id = "<?php echo ($detail["goods_id"]); ?>" ;
               var num = $("#goods_num").val();
               var shop_id = "<?php echo ($detail["shop_id"]); ?>";
               $.post(url,{goods_id:goods_id,shop_id:shop_id,spec_key:spec_key,num:num},function(data){
                   if(data.status == 'success'){
                       layer.msg(data.msg, function () {
                            setTimeout(function () {
                                window.location.reload(true);
                            }, 1000)
                        });
                   }else{
                       layer.msg(data.msg);
                   }
               },'json')
           })
           
           $(document).on('click', '.buy_now', function () {
                                var num = $("#goods_num").val();
                                var goods_id = "<?php echo ($detail["goods_id"]); ?>";
                                var url = '__ROOT__/index.php?g=wap&m=mall&a=buy&mt=<?php echo time();?>&goods_id=<?php echo ($detail["goods_id"]); ?>&num=' + num<?php if($spec_goods_price): ?>+'&spec_key='+spec_key<?php endif; ?>;
                                $.get(url, function (data) {
                                    if (data.status == 'success') {
                                        layer.msg(data.msg);
                                        setTimeout(function(){
                                            window.location.href=data.url;
                                        },2000);  
                                    } else {
                                       layer.msg(data);
                                    }
                                }, 'json');

                            });
        });
    </script>

<script type="text/javascript">
	$(document).ready(function(){   
    // 更新商品价格
   get_goods_price();
});
function switch_spec(spec)
{
    $(spec).siblings().removeClass('hover');
    $(spec).addClass('hover');
	$(spec).siblings().children('input').prop('checked',false);
	$(spec).children('input').prop('checked',true);	
    //更新商品价格
    get_goods_price();
}

function get_goods_price(){
  var goods_price = <?php echo ($goods["mall_price"]); ?>; // 商品起始价
  //这个先保留了
 var store_count = <?php echo ($detail['num']); ?>; // 商品起始库存   
   var spec_goods_price = '';
  <?php if(!empty($spec_goods_price)): ?>spec_goods_price =<?php echo ($spec_goods_price); ?>;  // 规格 对应 价格 库存表   //alert(spec_goods_price['28_100']['price']);<?php endif; ?>
    // 如果有属性选择项
   // 如果有属性选择项
	if(spec_goods_price != null){
		goods_spec_arr = new Array();
		$("input[name^='goods_spec']:checked").each(function(){
			 goods_spec_arr.push($(this).val());
		});    
		spec_key = goods_spec_arr.sort(sortNumber).join('_');  //排序后组合成 key	 搞一下就成了全局变量了
		goods_price = spec_goods_price[spec_key]['price']; // 找到对应规格的价格		
		store_count = spec_goods_price[spec_key]['store_count']; // 找到对应规格的库存
	}
	var goods_num = parseInt($("#goods_num").val()); 
	// 库存不足的情况
	if(goods_num > store_count)
	{
	   goods_num = store_count;
	   alert('库存仅剩 '+store_count+' 件');
	   $("#goods_num").val(goods_num);
	}	
	$("#goods_price").html('￥'+goods_price+'元'); // 变动价格显示
	    $("#stock").html(store_count);

	}

	function sortNumber(a,b) { 
		return a - b; 
	} 


	function goods_cut(){
  		var num_val=document.getElementById('goods_num');  
  		var new_num=num_val.value;  
  		var Num = parseInt(new_num);  
  		if(Num>1)Num=Num-1;  
  		num_val.value=Num;
  	}  
  	function goods_add(){
  		var num_val=document.getElementById('goods_num'); 
  		var new_num=num_val.value;  
  		var Num = parseInt(new_num);  
  		Num=Num+1;  num_val.value=Num;
  	} 
  	

</script>
<iframe id="x-frame" name="x-frame" style="display:none;"></iframe>	
</body>
</html>

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
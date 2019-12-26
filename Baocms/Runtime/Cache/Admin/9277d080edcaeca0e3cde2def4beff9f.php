<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
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
<div class="mainBt">
    <ul>
        <li class="li1">商城</li>
        <li class="li2">商城订单</li>
        <li class="li2 li3">商城订单列表</li>
    </ul>
</div>
<div class="main-jsgl main-sc">
    <p class="attention"><span>注意：</span>这里只是明细查询的地方，如果发货需要在商家中心进行操作！</p>
    <div class="jsglNr">
        <div class="selectNr" style="margin-top: 0px; border-top:none; padding-bottom: 0px;">
            <div class="right">
                <form class="search_form" method="post" action="<?php echo U('order/index');?>"> 
                    <div class="seleHidden" id="seleHidden">
                        <div class="seleK"> 
                            <label>
                                <input type="hidden" id="user_id" name="user_id" value="<?php echo (($user_id)?($user_id):''); ?>" />
                                <input type="text" name="nickname" id="nickname"  value="<?php echo ($nickname); ?>"   class="text" />
                                <a mini="select"  w="800" h="600" href="<?php echo U('user/select');?>" class="sumit">选择用户</a>
                            </label>
                            <label>
                                <span>状态</span>
                                <select name="st" class="select w100">
                                    <option value="999">请选择</option>
                                    <?php if(is_array($types)): foreach($types as $key=>$item): ?><option <?php if(($st) == $key): ?>selected="selected"<?php endif; ?>  value="<?php echo ($key); ?>"><?php echo ($item); ?></option><?php endforeach; endif; ?>

                                </select>
                            </label>
                            <label>
                                <span>订单编号</span>
                                <input type="text" name="keyword" value="<?php echo ($keyword); ?>" class="inptText" />

                                <input type="submit" value="   搜索"  class="inptButton" />
                            </label>
                        </div>
                    </div> 
                </form>
                <a href="javascript:void(0);" class="searchG">高级搜索</a>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div>
        <form method="post" action="<?php echo U('order/index');?>">
            <div class="selectNr selectNr2">
                <div class="left">
                    <div class="seleK">
                        <label>
                            <input type="hidden" id="user_id" name="user_id" value="<?php echo (($user_id)?($user_id):''); ?>" />
                            <input type="text" name="nickname" id="nickname"  value="<?php echo ($nickname); ?>"   class="text w150" />
                            <a style="margin-right: 5px;" mini="select"  w="800" h="600" href="<?php echo U('user/select');?>" class="sumit">选择用户</a>
                        </label>
                        <label>
                            <span>商家</span>
                            <input type="hidden" id="shop_id" name="shop_id" value="<?php echo (($shop_id)?($shop_id):''); ?>"/>
                            <input type="text"   id="shop_name" name="shop_name" value="<?php echo ($shop_name); ?>" class="text w150" />
                            <a style="margin-right: 5px;"  href="<?php echo U('shop/select');?>" mini='select' w='800' h='600' class="sumit">选择商家</a>
                        </label>
                        <label>
                            <span>状态</span>
                            <select name="st" class="select w80">
                                <option value="999">请选择</option>
                                <?php if(is_array($types)): foreach($types as $key=>$item): ?><option <?php if(($st) == $key): ?>selected="selected"<?php endif; ?>  value="<?php echo ($key); ?>"><?php echo ($item); ?></option><?php endforeach; endif; ?>

                            </select>
                        </label>
                        <label>
                            <span>开始时间</span>
                            <input type="text" name="bg_date" value="<?php echo (($bg_date)?($bg_date):''); ?>"  onfocus="WdatePicker({dateFmt: 'yyyy-MM-dd HH:mm:ss'});"  class="text w150" />
                        </label>
                        <label>
                            <span>结束时间</span>
                            <input type="text" name="end_date" value="<?php echo (($end_date)?($end_date):''); ?>" onfocus="WdatePicker({dateFmt: 'yyyy-MM-dd HH:mm:ss'});"  class="text w150" />
                        </label>
                        <label>
                            <span>订单编号</span>
                            <input type="text" name="keyword" value="<?php echo ($keyword); ?>" class="inptText w100" />
                        </label>
                    </div>
                </div>
                <div class="right">
                    <input type="submit" value="   搜索"  class="inptButton" />
                </div>
                <div class="clear"></div>
            </div>
        </form>
        <div class="tableBox">

            <form  target="baocms_frm" method="post">

                <?php if(is_array($list)): foreach($list as $key=>$order): ?><table bordercolor="#dbdbdb" cellspacing="0" width="100%" border="1px"  style=" border-collapse: collapse; vertical-align:middle; background-color:#FFF; margin-top: 10px;"  >
                        <tr class="no">
                            <td></td>
                            <td>ID</td>
                            <td>姓名</td>
                            <td>电话</td>
                            <td>原价</td>
                            <td>折扣价</td>
                            <td>预约时间</td>
                            <td>状态</td>
                            <td>操作</td>
                        </tr>
                        <tr class="no">
                            <td><input class="child_order_id" name="order_id[]"  type="checkbox" value="<?php echo ($order["order_id"]); ?>" /> </td>
                            <td><?php echo ($order["order_id"]); ?></td>
                            <td><?php echo ($users[$order['user_id']]['ext0']); ?></td>
                            <td><?php echo ($users[$order['user_id']]['mobile']); ?></td>
                            <td><?php echo round($order['total_price']/100,2);?>元</td>
                            <td><?php echo round($order['total_price']/100,2);?>元</td>
                            <td><?php echo (date('Y-m-d H:i:s',$order["create_time"])); ?></td>
                            <td style="color: red;">
                                <?php echo ($types[$order['status']]); ?>
                            </td>
                            <td>
                                <?php if($order['status'] == 0): echo BA('order/pay',array("order_id"=>$order["order_id"],"user_id"=>$order["user_id"]),'确认支付','act','remberBtn'); endif; ?>
                                <?php echo BA('order/backmoney',array("order_id"=>$order["order_id"],"user_id"=>$order["user_id"]),'开始返款','act','remberBtn');?>
                            </td>

                        </tr>


                        <tr class="no">
                            <td colspan="9">
                                <table cellspacing="0" width="60%" border="1px"  style=" border-collapse: collapse; vertical-align:middle; margin-left: 10px;"  >
                                    <tr class="no">
                                        <th>图片</th>
                                        <th>商品名称</th>
                                        <th>数量</th>
                                        <th>单价</th>
                                        <th>总价</th>
                                        <td>预计返款</td>
                                        <th>状态</th>
                                        <th>返款记录</th>
                                    </tr>  
                                    <?php if(is_array($goods)): foreach($goods as $key=>$good): if(($good["order_id"]) == $order["order_id"]): ?><tr class="no">
                                            <td><img width="60" src="<?php echo config_img($products[$good['goods_id']]['photo']);?>" /></td>
                                            <td>
                                            	<?php echo ($products[$good['goods_id']]['title']); ?><br>
                                            	<span style="color:red;"><?php echo ($good[key_name]); ?></span>
                                            	</td>

                                            <td><?php echo ($good["num"]); ?></td>
                                            <td><?php echo round($good['price']/100,2);?></td>
                                            <td><?php echo round($good['total_price']/100,2);?></td>
                                            <td><?php echo round($good['price']/100,2);?>-<?php echo round($good['total_price']/100,2);?></td>
                                            <td  style="color: red;">
                                                <?php echo ($goodtypes[$good['status']]); ?>
                                            </td>
                                            <td>
                                                <?php echo BA('order/backrecord',array("order_id"=>$order["order_id"],"goods_id"=>$good["goods_id"]),'查看','','remberBtn');?>
                                            </td>
                                        </tr><?php endif; endforeach; endif; ?>



                                </table>

                            </td>                    
                        </tr>


                    </table><?php endforeach; endif; ?>

                <?php echo ($page); ?>
            </form>
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
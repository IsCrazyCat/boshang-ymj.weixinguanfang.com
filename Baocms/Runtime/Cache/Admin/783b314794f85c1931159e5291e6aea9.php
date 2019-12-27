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
<div class="mainBt">
    <ul>
        <li class="li1">商家</li>
        <li class="li2">商家管理</li>
        <li class="li2 li3">商家列表</li>
    </ul>
</div>
<div class="main-jsgl main-sc">
    <p class="attention"><span>注意：</span>前台申请 后台审核！后台添加商家后，商家会先出现在入驻申请里面！</p>
    <div class="jsglNr">
        <div class="selectNr" style="margin-top: 0px; border-top:none;">
            <div class="left">
                <?php echo BA('shop/create','','添加申请');?>
            </div>
            <div class="right">
                <form class="search_form" method="post" action="<?php echo U('shop/index');?>">
                    <div class="seleHidden" id="seleHidden">
                        <span>关键字(电话或商户名称)</span>
                        <input type="text" name="keyword" value="<?php echo ($keyword); ?>" class="inptText" /><input type="submit" value="   搜索"  class="inptButton" />
                    </div> 
                </form>
                <a href="javascript:void(0);" class="searchG">高级搜索</a>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div>

        <form method="post" action="<?php echo U('shop/index');?>">
            <div class="selectNr selectNr2">
                <div class="left">
                    <div class="seleK">
                        <label>
                            <span>分类：</span>
                            <select id="cate_id" name="cate_id" class="select w100">
                                <option value="0">请选择...</option>
                                <?php if(is_array($cates)): foreach($cates as $key=>$var): if(($var["parent_id"]) == "0"): ?><option value="<?php echo ($var["cate_id"]); ?>"  <?php if(($var["cate_id"]) == $cate_id): ?>selected="selected"<?php endif; ?> ><?php echo ($var["cate_name"]); ?></option>               
                                    <?php if(is_array($cates)): foreach($cates as $key=>$var2): if(($var2["parent_id"]) == $var["cate_id"]): ?><option value="<?php echo ($var2["cate_id"]); ?>"  <?php if(($var2["cate_id"]) == $cate_id): ?>selected="selected"<?php endif; ?> >&nbsp;&nbsp;<?php echo ($var2["cate_name"]); ?></option><?php endif; endforeach; endif; endif; endforeach; endif; ?>
                            </select>
                        </label>
                        <label>
                            <span>区域：</span>
                             <select name="city_id" id="city_id"  class="select w100"></select>
                            <select name="area_id" id="area_id"  class="select w100"></select>
                        </label>
                <script src="<?php echo U('app/datas/cityarea');?>"></script>
                <script>
                    var city_id = <?php echo (int)$city_id;?>;
                    var area_id = <?php echo (int)$area_id;?>;
                    function changeCity(cid){
                        var area_str = '<option value="0">请选择.....</option>';
                        for(a in cityareas.area){
                           if(cityareas.area[a].city_id ==cid){
                                if(area_id == cityareas.area[a].area_id){
                                    area_str += '<option selected="selected" value="'+cityareas.area[a].area_id+'">'+cityareas.area[a].area_name+'</option>';
                                }else{
                                     area_str += '<option value="'+cityareas.area[a].area_id+'">'+cityareas.area[a].area_name+'</option>';
                                }  
                            }
                        }
                        $("#area_id").html(area_str);
                    }

                    $(document).ready(function(){
                        var city_str = '<option value="0">请选择.....</option>';
                        for(a in cityareas.city){
                           if(city_id == cityareas.city[a].city_id){
                               city_str += '<option selected="selected" value="'+cityareas.city[a].city_id+'">'+cityareas.city[a].name+'</option>';
                           }else{
                                city_str += '<option value="'+cityareas.city[a].city_id+'">'+cityareas.city[a].name+'</option>';
                           }  
                        }
                        $("#city_id").html(city_str);
                        if(city_id){
                            changeCity(city_id);
                        }
                        $("#city_id").change(function(){
                            city_id = $(this).val();
                            changeCity($(this).val());
                        });
                    });
                </script>
                        <label>
                            <span>关键字:</span>
                            <input type="text" name="keyword" value="<?php echo ($keyword); ?>" class="inptText" />
                        </label>
                    </div>
                </div>
                <div class="right">
                    <input type="submit" value="   搜索"  class="inptButton" />
                </div>

        </form>
        <div class="clear"></div>
    </div>
    <form  target="baocms_frm" method="post">
        <div class="tableBox">
            <table bordercolor="#dbdbdb" cellspacing="0" width="100%" border="1px"  style=" border-collapse: collapse; margin:0px; vertical-align:middle; background-color:#FFF;"  >
                <tr>
                    <td class="w50"><input type="checkbox" class="checkAll" rel="shop_id" /></td>
                    <td class="w50">ID</td>
                    <td>管理者(电话)</td>
                    <td>商铺名称(区域商圈)</td>
                    <td>分类(商铺标签)</td>
                    <td>商铺LOGO</td>
                    <td>入住时间</td>
                    <td class="w200">操作</td>
                </tr>
                <?php if(is_array($list)): foreach($list as $key=>$var): ?><tr>
                        <td><input class="child_shop_id" type="checkbox" name="shop_id[]" value="<?php echo ($var["shop_id"]); ?>" /></td>
                        <td><?php echo ($var["shop_id"]); ?></td>
                        <td>
                        <?php if(!empty($var['user_id'])): ?>ID：<?php echo ($users[$var['user_id']]['account']); ?>(<?php echo ($var["user_id"]); ?>)<?php endif; ?>
                        
                        <?php if(!empty($var['tel'])): ?><br/>
                        电话：<?php echo ($var["tel"]); endif; ?>
                        </td>
                        <td>
                        名称：<?php echo ($var["shop_name"]); ?><br/>
                        城市：<?php echo ($citys[$var['city_id']]['name']); ?>
                        <?php if(!empty($areas[$var['area_id']]['area_name'])): ?>-<?php echo ($areas[$var['area_id']]['area_name']); endif; ?>
                        <?php if(!empty($business[$var['business_id']]['business_name'])): ?>-<?php echo ($business[$var['business_id']]['business_name']); endif; ?>
                        </td>
                        <td>
                       分类：<?php echo ($cates[$var['cate_id']]['cate_name']); ?>
                       <?php if(!empty($var['tags'])): ?><br/>
                       TAG: <?php echo ($var["tags"]); endif; ?>
                        </td>
                        <td>
                        <?php if(!empty($var['logo'])): ?><img style="padding:2px; height:60px; width:60px;" src="<?php echo config_img($var['logo']);?>" />
                        <?php else: ?>
                        暂无图片<?php endif; ?>
                        </td>
                        
                    <td><?php echo (date('Y-m-d H:i:s',$var["create_time"])); ?></td>
                    <td class="w150">
                    <?php if(($var["recognition"]) == "0"): ?><a class="remberBtn_small">未认领</a>
                    <?php else: ?>
                      <a  class="remberBtn_small">已认领</a><?php endif; ?> 

<!--                    <?php if(($var["is_pei"]) == "1"): ?>-->
<!--                      <?php echo BA('shop/pei',array("shop_id"=>$var["shop_id"]),'开通配送','act','remberBtn_small');?>-->
<!--                    <?php else: ?>-->
<!--                      <?php echo BA('shop/pei',array("shop_id"=>$var["shop_id"]),'取消配送','act','remberBtn_small_quxiao');?>-->
<!--<?php endif; ?>-->

<!--                    <?php if(($var["is_ding"]) == "1"): ?>-->
<!--                      <?php echo BA('shop/ding',array("shop_id"=>$var["shop_id"]),'取消订座','act','remberBtn_small_quxiao');?>-->
<!--                    <?php else: ?>-->
<!--                      <?php echo BA('shop/ding',array("shop_id"=>$var["shop_id"]),'开通订座','act','remberBtn_small');?>-->
<!--<?php endif; ?>-->

<!--                     <?php if(($var["is_biz"]) == "1"): ?>-->
<!--                      <?php echo BA('shop/biz',array("shop_id"=>$var["shop_id"]),'取消黄页','act','remberBtn_small_quxiao');?>-->
<!--                    <?php else: ?>-->
<!--                      <?php echo BA('shop/biz',array("shop_id"=>$var["shop_id"]),'开通黄页','act','remberBtn_small');?>-->
<!--<?php endif; ?>  -->
<!--                    -->
                    <?php if(($var["is_profit"]) == "1"): echo BA('shop/profit',array("shop_id"=>$var["shop_id"]),'取消分销','act','remberBtn_small_quxiao');?>
                    <?php else: ?>
                      <?php echo BA('shop/profit',array("shop_id"=>$var["shop_id"]),'开通分销','act','remberBtn_small'); endif; ?>                 

                    <?php echo BA('shop/edit',array("shop_id"=>$var["shop_id"]),'编辑','','remberBtn_small');?>
                    <?php echo BA('shop/delete',array("shop_id"=>$var["shop_id"]),'删除','act','remberBtn_small_quxiao');?>
                    <a target="_blank" class="remberBtn_small" href="<?php echo U('shop/login',array('shop_id'=>$var['shop_id']));?>">管理</a>
                    </td>
                    </tr><?php endforeach; endif; ?>
            </table>
            <?php echo ($page); ?>
        </div>
        <div class="selectNr" style="margin-bottom: 0px; border-bottom: none;">
            <div class="left">
                <?php echo BA('shop/delete','','批量删除','list',' a2');?>
            </div>
        </div>
    </form>
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
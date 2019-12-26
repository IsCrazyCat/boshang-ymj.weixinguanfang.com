<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo ($CONFIG["site"]["title"]); ?>管理后台</title>
        <meta name="description" content="<?php echo ($CONFIG["site"]["title"]); ?>管理后台" />
        <meta name="keywords" content="<?php echo ($CONFIG["site"]["title"]); ?>管理后台" />
        <link href="__TMPL__statics/css/style.css" rel="stylesheet" type="text/css" />
        <link href="__TMPL__statics/css/land.css" rel="stylesheet" type="text/css" />
        <link href="__TMPL__statics/css/pub.css" rel="stylesheet" type="text/css" />
        <link href="__TMPL__statics/css/main.css" rel="stylesheet" type="text/css" />
        <link href="__PUBLIC__/js/jquery-ui.css" rel="stylesheet" type="text/css" />
        <script> var BAO_PUBLIC = '__PUBLIC__';
            var BAO_ROOT = '__ROOT__';</script>
        <script src="__PUBLIC__/js/jquery.js"></script>
        <script src="__PUBLIC__/js/jquery-ui.min.js"></script>
        <script src="__PUBLIC__/js/my97/WdatePicker.js"></script>
        <script src="__PUBLIC__/js/admin.js"></script>
    </head>
    <body>
        <iframe id="baocms_frm" name="baocms_frm" style="display:none;"></iframe>
        <script type="text/javascript">
            $(document).ready(function () {
                $(".leftMenu_a").click(function () {

                    if ($(this).hasClass("currents")) {
                        $(this).removeClass("currents");
                    } else {
                        $(this).addClass("currents");
                    }
                    $(this).parent().find(".leftNav2").slideToggle();
                });
                $(".leftNav2 li").click(function () {
                    $(".leftNav2 li a").removeClass('curHover');
                    $(this).find('a').addClass('curHover');
                });
            });
        </script> 
        <div class="top">
            <div class="left">
                <div class="bgx">管理中心</div>
            </div>
            <div class="center">
                
                <div class="t2">
                    <ul>
                        <?php if(is_array($menuList)): foreach($menuList as $key=>$var): if(($var["parent_id"] == 0) and ($var["is_show"] == 1) ): ?><li><a href="javascript:void(0);" class="jq_menu" rel="<?php echo ($var["menu_id"]); ?>" ><?php echo ($var["menu_name"]); ?></a></li><?php endif; endforeach; endif; ?>
                        <div class="clear"></div>
                        <script>
                            $(document).ready(function (e) {
                                $(".jq_menu").click(function () {
                                    var rel = $(this).attr('rel');
                                    $(".jq_menu").removeClass('cur');
                                    $(this).addClass('cur');
                                    $(".leftMenu_ul ul .leftMenu_li").each(function (a) {
                                        if ($(this).attr('rel') == rel) {
                                            $(this).show();
                                        } else {
                                            $(this).hide();
                                        }
                                    });
                                });
                                $(".t2 li").eq(0).find('.jq_menu').click();
                            });
                        </script>
                    </ul>
                </div>
                
                
                <div class="t3">
                <ul>
                     <li><a class="jq_menu" target="_blank" href="">使用教程</a>
                     <li><a href="<?php echo U('login/logout');?>" class="jq_menu">退出</a></li>
                     <li class=" jq_menu2" ><a href="/" target="_blank" class="jq_menu">站点首页</a></li>
                </ul>
                 </div>
                
            </div>
        </div>

        <div class="bottom_box">
            <div class="leftMenu">
                <div class="menuClik">
                
                <div class="side-user">
              <div class="avatar"><img src="__TMPL__statics/images/avatar1_50.jpg" alt="Avatar" width="46" height="46"></div>
              <div class="info">
                <a href="#">您好！<?php echo ($admin["username"]); ?>
                <a target="main_frm" href="<?php echo U('clean/cache');?>">更新缓存</a>
                <img src="__TMPL__statics/images/state_online.png" alt="Status"> <span><?php echo ($admin["role_name"]); ?></span>
              </div>
            </div>              
                </div>
                <div class="leftMenu_ul">
                    <ul>
                        <?php if(is_array($menuList)): foreach($menuList as $key=>$var): if(($var["parent_id"] == 0) and ($var["is_show"] == 1) ): if(is_array($menuList)): foreach($menuList as $key=>$var2): if(($var2["parent_id"]) == $var["menu_id"]): if($var2["is_show"] == 1): ?><li rel="<?php echo ($var["menu_id"]); ?>" class="leftMenu_li"><a class="leftMenu_a" href="javascript:void(0);" ><?php echo ($var2["menu_name"]); ?></a>
                                                <div class="leftNav2">
                                                    <ul>
                                                        <?php if(is_array($menuList)): foreach($menuList as $key=>$var3): if(($var3["parent_id"]) == $var2["menu_id"]): if($var3["is_show"] == 1): ?><li><a href="<?php echo U($var3['menu_action']);?>" target="main_frm"><?php echo ($var3["menu_name"]); ?></a></li><?php endif; endif; endforeach; endif; ?>    
                                                    </ul>
                                                </div>
                                            </li><?php endif; endif; endforeach; endif; endif; endforeach; endif; ?>
                    </ul>
                </div>
            </div>
            <div class="rightMenu">
                <iframe id="main_frm" name="main_frm" src="<?php echo U('index/main');?>" frameborder="0" width="100%" height="100%"></iframe>
            </div>
        </div>
        <script>
            function checkSession() {
                return $.get('<?php echo U("index/check");?>', function (data) {
                }, 'html');
            }
            setInterval('checkSession()', 60000);
        </script>
  
        
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
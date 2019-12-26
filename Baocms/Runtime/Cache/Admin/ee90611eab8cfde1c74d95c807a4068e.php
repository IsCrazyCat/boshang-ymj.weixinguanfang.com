<?php if (!defined('THINK_PATH')) exit();?><div class="listBox clfx">
    <div class="menuManage">
        <form  target="baocms_frm" action="<?php echo U('user/create');?>" method="post">
            <div class="mainScAdd">
                <div class="tableBox">
                    <table  bordercolor="#dbdbdb" cellspacing="0" width="100%" border="1px"  style=" border-collapse: collapse; margin:0px; vertical-align:middle; background-color:#FFF;" >
                        <tr>
                            <td class="lfTdBt">账户：</td>
                            <td class="rgTdBt"><input type="text" name="data[account]" value="<?php echo (($detail["account"])?($detail["account"]):''); ?>" class="scAddTextName w200" />
                                <code>账号只允许手机或邮件</code>
                            </td>
                        </tr>
                       <tr>
                            <td class="lfTdBt">密码：</td>
                            <td class="rgTdBt"><input type="password" name="data[password]" value="******" class="scAddTextName w200" />
                            <code>建议6位以上的密码</code>
                            </td>
                        </tr>
                        <tr>
                            <td class="lfTdBt">支付密码：</td>
                            <td class="rgTdBt"><input type="pay_password" name="data[pay_password]" value="******" class="scAddTextName w200" />
                            <code>当用户忘记支付密码后管理员可在后台重新修改支付密码，但是要手动通知用户已修改支付密码</code>
                            </td>
                        </tr>

                        <tr>
                            <td class="lfTdBt">昵称：</td>
                            <td class="rgTdBt"><input type="text" name="data[nickname]" value="<?php echo (($detail["nickname"])?($detail["nickname"]):''); ?>" class="scAddTextName w200" />
                            </td>
                        </tr>


                          <tr>
                            <td class="lfTdBt">用户手机号：</td>
                            <td class="rgTdBt"><input type="text" name="data[mobile]" value="<?php echo (($detail["mobile"])?($detail["mobile"]):''); ?>" class="scAddTextName w200" />
							<code>一般不要去修改！</code>
                            </td>
                        </tr>

                        <tr>
                            <td class="lfTdBt">用户等级：</td>
                            <td class="rgTdBt">
                                <select name="data[rank_id]" class="seleFl w200">
                                    <?php if(is_array($ranks)): foreach($ranks as $key=>$item): ?><option <?php if(($item["rank_id"]) == $detail["rank_id"]): ?>selected="selected"<?php endif; ?>  value="<?php echo ($item["rank_id"]); ?>"><?php echo ($item["rank_name"]); ?></option><?php endforeach; endif; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>

                            <td class="lfTdBt">别名：</td>
                            <td class="rgTdBt"><input type="text" name="data[ext0]" value="<?php echo (($detail["ext0"])?($detail["ext0"]):''); ?>" class="scAddTextName w200" />
                                <code>一般不要写，兼容UCENTER，如果不整合DZ可以不填写，整合就需要填写用户名</code>
                            </td>
                        </tr>



 				<tr>
                    <td class="lfTdBt">头像：</td>
                 <td class="rgTdBt">
                    <div style="width: 300px;height: 100px; float: left;">
                        <input type="hidden" name="data[face]" value="<?php echo ($detail["face"]); ?>" id="data_face" />
                        <div id="fileToUpload" >上传头像</div>
                    </div>
                    <div style="width: 300px;height: 100px; float: left;">
                        <img id="face_img" width="120" height="80"  src="<?php echo config_img($detail['face']);?>" />
                        <a href="<?php echo U('setting/attachs');?>">头像设置</a>
                        <?php echo ($CONFIG["attachs"]["user"]["thumb"]); ?>
                    </div>
                    <script>                                            
						var width_user = '<?php echo thumbSize($CONFIG[attachs][user][thumb],0);?>';                         
						var height_user = '<?php echo thumbSize($CONFIG[attachs][user][thumb],1);?>';                         
						var uploader = WebUploader.create({                             
						auto: true,                             
						swf: '/static/default/webuploader/Uploader.swf',                             
						server: '<?php echo U("app/upload/uploadify",array("model"=>"user"));?>',                             
						pick: '#fileToUpload',                             
						resize: true,  
						compress : {width: width_user,height: height_user,quality: 80,allowMagnify: false,crop: true}                       
					});                                                 
					uploader.on( 'uploadSuccess', function( file,resporse) {                             
						$("#data_face").val(resporse.url);                             
						$("#face_img").attr('src',resporse.url).show();                         
					});                                                
					uploader.on( 'uploadError', function( file ) {                             
						alert('上传出错');                         
					});                     
                    </script>
                </td>
            </tr>
            
                    </table>
                </div>
                <div class="smtQr"><input type="submit" value="确认添加" class="smtQrIpt" /></div>
            </div>
        </form>
    </div>
</div>
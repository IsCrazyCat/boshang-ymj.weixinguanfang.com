/* 
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：尤哥
 * 官网：www.baocms.com
 * 邮件: 376621340@qq.com
 */
var  lock  = 0;
function loading(){
    var boxHtml = '<div class="baomsgbox"></div>';
    $(".baomsgbox").css('top','300px');
    if($(".baomsgbox").length  ==  0){
        $("body").append(boxHtml);
    }
    $(".baomsgbox").html('<img src="'+BAO_PUBLIC+'/images/loading.gif" /><span  style=" color: blue;">正在加载中...</span>');
    $(".baomsgbox").show();
    lock = 1;
}

function LoginSuccess(){
    $(".baomsgbox").show();
    $(".dialogBox").dialog('close');
    success('登录成功',3000,"loginCallback()");
}

function loginCallback(){
    $.get(BAO_ROOT+"/index.php?m=passport&a=check&mt="+Math.random(),function(data){
        $(".top_left").html(data); 
    },'html');
    return true;
}

function ajaxLogin(){


     hidde();
}

    
function success(msg,timeout,callback){
    var boxHtml = '<div class="baomsgbox"></div>';
    if($(".baomsgbox").length  ==  0){
        $("body").append(boxHtml);
    }
    
    $(".baomsgbox").html('<img src="'+BAO_PUBLIC+'/images/right.gif" /><span  style=" color: green;">'+msg+'</span>');
    $(".baomsgbox").show();
    setTimeout(function(){
        lock = 0;
        $(".baomsgbox").hide();
        eval(callback);
    },timeout ? timeout : 3000);
}
function error(msg,timeout,callback){
    var boxHtml = '<div class="baomsgbox"></div>';
    if($(".baomsgbox").length  ==  0){
        $("body").append(boxHtml);
    }
    $(".baomsgbox").html('<img src="'+BAO_PUBLIC+'/images/wrong.gif" /><span  style=" color: red;">'+msg+'</span>');
    $(".baomsgbox").show();
    setTimeout(function(){
        lock = 0;
        $(".baomsgbox").hide();
        eval(callback);           
    },timeout ? timeout : 3000);
}

function hidde(){
    $(".baomsgbox").hide();
    lock = 0;
}

function jumpUrl(url){
    if(url){
        location.href=url;
    }else{
        history.back(-1);
    }
}
    
function yzmCode(){ //更换验证码
    $(".yzm_code").click();
}  

function dialog(title,content,width,height){
    var dialogHtml = '<div class="dialogBox" title="'+title+'"></div>';
    if($(".dialogBox").length  ==  0){
        $("body").append(dialogHtml);
    }
    $(".dialogBox").attr('title',title);
    $(".dialogBox").html(content);
    $(".dialogBox").dialog({
        zIndex:1000,
        width: width?width:300,
        height:height?height:200,
        modal: true
    });

}

function selectCallBack(id,name,v1,v2){
    $("#"+id).val(v1);
    $("#"+name).val(v2);
    $(".dialogBox").dialog('close');
}


$(document).ready(function(e){
  
    $(document ).on("click","input[type='submit']",function(e){
        e.preventDefault();
        if(!lock){
            loading();
            if($(this).attr('rel')){
                $("#"+$(this).attr('rel')).submit();
            }else{
                $(this).parents('form').submit();    
            }
        }         
    }); 
    $(".yzm_code").click(function(){
        $("#"+$(this).attr('rel')).attr('src',BAO_ROOT+'/index.php?g=app&m=verify&a=index&mt='+Math.random());
    });
    
    $(document).on("click","a[mini='act']",function(e){
        e.preventDefault();
        if(!lock){
            if(confirm("您确定要"+$(this).html())){
                loading();
                $("#baocms_frm").attr('src',$(this).attr('href'));   
            }  
        }  
    });
    
    //全选
    $(document).on("click",".checkAll",function(e){
        var child = $(this).attr('rel');
        $(".child_"+child).prop('checked',$(this).prop("checked"));
    });
    
    
    $(document).on('click',"a[mini='list']",function(e){
        e.preventDefault();
        if(!lock){
            loading();
            $(this).parents('form').attr('action',$(this).attr('href')).submit();
        }  
    });
    

    $(document).on("click","a[mini='load']",function(e){
        e.preventDefault();
        if(!lock){
            loading();
            var obj =$(this);
            $.get(obj.attr('href'),function(data){
                if(data){
                    dialog( obj.text(), data,obj.attr('w'),obj.attr('h')); 
                
                }
                hidde();
            },'html');
            
        }
    });
    $(document).on("click","a[mini='select']",function(e){
        e.preventDefault();
        if(!lock){
            loading();
            var obj =$(this);
            dialog( obj.text(), '<iframe id="select_frm" name="select_frm" src="'+obj.attr('href')+'" style="border:0px;width:'+(obj.attr('w')-30)+'px;height:'+(obj.attr('h')-80)+'px;"></iframe>',obj.attr('w'),obj.attr('h')); 
            hidde();
        }
    });
    
    $('.jq_star_show').each(function(){
        var val = $(this).attr('rel');
        var str = '';
        var num = parseInt(val/10);
        var num2 = 5-num;
        for(i=0;i<num;i++){
            str+= '<img src="'+BAO_PUBLIC+'/images/star1.jpg"/>';
        }
        for(i=0;i<num2;i++){
            str+= '<img src="'+BAO_PUBLIC+'/images/star2.jpg"/>';
        }
        $(this).html(str);
    });
    
    $(".jq_opacity_img img").mouseover(function(){
        $(this).stop().animate({
            opacity:'0.5'
        },300);
    }).mouseout(function(){
        $(this).stop().animate({
            opacity:'1'
        },300);
    });
    
    
    $(".jq_back_top").click(function(e){
        var rel = $(this).attr('rel');
        rel = rel ==undefined ? 200:rel;
        $("html,body").animate({scrollTop:0},rel);
    });
   
});
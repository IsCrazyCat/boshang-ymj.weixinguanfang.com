var lock  = 0;


// 更换验证
function verify() {
    $(".verify").click();
}


// 加载效果
function loading(){
    var index = layer.load(0, {shade: false});
    lock = 1;
}

// 清除提示
function hidde(){
	layer.closeAll(); 
    lock = 0;
}


// 成功提示
function success(msg,timeout,callback){
	hidde();
	layer.msg(msg, {
		icon: 1,
		time: timeout 
	}, function(){
		lock = 0;
		eval(callback);
	}); 
}


// 错误提示
function error(msg,timeout,callback){
	hidde();
	layer.msg(msg, {
		icon: 2,
		time: timeout 
	}, function(){
		lock = 0;
		eval(callback);
	}); 
}


// 页面跳转
function jump(url){
    if(url){
        location.href=url;
    }else{
        history.back(-1);
    }
}


// 选择地区
function latlng(){
	hidde();
	layer.open({
		type: 2,
		title: '选择地区',
		area: ['600px', '400px'],
		content: '/public/district',
		end : function(index, layero){
			lat = getcookie('lat');
			lng = getcookie('lng');
			if(lat == null || lng == null){
				latlng();
			}
		}
	}); 
}

// 获取COOKIE
function getcookie(name){
	var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
	if(arr=document.cookie.match(reg)){
		return unescape(arr[2]);
	}else{
		return null;
	}
}


// 选择回调
function selectCallBack(id, name, v1, v2) {
    $("#" + id).val(v1);
    $("#" + name).val(v2);
    hidde();
}



// 弹窗登录
function ajaxLogin(){
    hidde();
	url = "/index.php?g=pchome&m=passport&a=ajaxloging2&t="+Math.random();
	$.get(url,function(data){
		layer.open({
			type: 1,
			scrollbar:true,
			title: false,
			shadeClose: false,
			closeBtn: 0,
			shade: 0.8,
			area: ['540px', '450px'],
			content:data
		}); 
	},'html');
}

// 登录成功
function ajaxLoginSuccess(){
    hidde(); 
}




//-------------  依赖JQUERY脚本 -------------//

$(function(){
	
    $(".search-tab li").click(function(){
        $(this).siblings().removeClass('active').end().addClass('active');
        $(this).parents('.search-tab').next().find('.search-text').attr('placeholder',$(this).data('placeholder')).end().find('form').attr('action',$(this).data('action'))
    })
    $(".categories-item").hover(function(){
        var top = $(this).position().top;
        var h = $('.categories-body').outerHeight();
        $(this).find('.categories-pop').css({height:h,display:'block',top:-top});
    },function(){
        $(this).find('.categories-pop').css({display:'none'});
    })
	
    $(".ui-beacon-drop").hover(function(){
        $(this).addClass('ui-beacon-drop-hover');
        $(this).find('.ui-beacon-subs').show();
    },function(){
        $(this).removeClass('ui-beacon-drop-hover');
        $(this).find('.ui-beacon-subs').hide();
    })
	
	
    if($("#categories").hasClass('categories-toggle')){
        $("#categories").hoverDelay({
            hoverDuring: 400,
            outDuring: 0,
            hoverEvent: function(){
                $("#categories").addClass('categories-show').addClass('ui-beacon-drop-hover');    
            },
            outEvent: function(){
                $("#categories").removeClass('categories-show').removeClass('ui-beacon-drop-hover')
            }
        });
    }

	 
    $("#scroll").hide();
		
	//当滚动条的位置处于距顶部100像素以下时，跳转链接出现，否则消失

	$(window).scroll(function() {
		if ($(window).scrollTop() > 100) {
			$("#scroll").fadeIn();
		} else {
			$("#scroll").fadeOut();
		}
	});
	//当点击跳转链接后，回到页面顶部位置
	$("#scroll-top").click(function() {
		$('body,html').animate({
			scrollTop: 0
		},
		500);
		return false;
	});
	
	
	/*登录检测*/
    $(document).on("click","a[mini='login']",function(e){
        e.preventDefault();
        if(!lock){
            loading();
			ajaxLogin();
        }
    });
	
		
		
	/*重构弹窗*/
    $(document).on("click","a[mini='load']",function(e){
        e.preventDefault();
        if(!lock){
            loading();
            var href = $(this).attr('href');
			var title = $(this).attr('title');
			var width = parseInt($(this).attr('w'));
			var height = parseInt($(this).attr('h'));
			if(width>0){
				width = width + 'px';
			}else{
				width = '540px';
			}
			if(height>0){
				height = height + 'px';
			}else{
				height = '350px';
			}
            if(href.indexOf('?') >0){
                href+='&mini=load';
            }else{
                href+='?mini=load';
            }
            $.get(href,function(data){
                hidde();
                if(data == 0){
                    ajaxLogin();
                }else{
					$.get(href,function(data){
						layer.open({
							type: 1,
							scrollbar:true,
							title: title,
							area: [width, height],
							content:data
						}); 
					},'html');
                }                
            },'html');
        }
    });
	
	
	
	
	
	
	
	/*重构链接*/
    $(document).on("click","a[mini='act']",function(e){
        e.preventDefault();
        if(!lock){
            loading();
            $("#niu-frame").attr('src',$(this).attr('href'));      
        }  
    });
	
	
	
	/*重构链接*/
    $(document).on("click","a[mini='dig']",function(e){
        e.preventDefault();
		var href = $(this).attr('href');
		var rel = $(this).attr('rel');
		$.get(href,function(data){
			if(data == 0){
				alert("没有成功，您已经操作过啦！");
			}else{
				$('#'+rel).html(data);
			}
		},'html');

    });
	

	

	/* AJAX选择 */ 
	$(document).on("click", "a[mini='select']", function (e) {
		e.preventDefault();
		if (!lock) {
			var obj = $(this);
			hidde();
			win = layer.open({
				type: 2,
				area: [parseInt(obj.attr('w'))+'px', parseInt(obj.attr('h')) +'px'],
				fix: false,
				maxmin: true,
				shade:0.4,
				title: obj.text(),
				content: obj.attr('href')
			});
		}
	});

	/* 选择回调 */ 
	function selectCallBack(id, name, v1, v2) {
		$("#" + id).val(v1);
		$("#" + name).val(v2);
		hidde();
	}

	
	

	//验证码点击
	$(".verify").click(function () {
		$(this).attr('src', '/auth/verify/index/mt/' + Math.random()+'.html');
	});
	



	//订单数量
	$("#niu-add").click(function () {
		var num = $("#niu-num").val();
		if (num < 99) {
			num++;
		}
		$("#niu-num").val(num);
	});
	$("#niu-min").click(function () {
		var num = $("#niu-num").val();
		if (num > 1) {
			num--;
		}
		$("#niu-num").val(num);
	});

						
	
	

	
}); 












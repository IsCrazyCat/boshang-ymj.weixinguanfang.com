// 初始化JS参数
var niulock = 1;
var niunum = 1;


// 自动定位
Location();
function Location(){
	navigator.geolocation.getCurrentPosition(Local);
}
function Local(position) {
	var lng = position.coords.longitude;
	var lat = position.coords.latitude;
	var url = "/wap/index/local/lat/"+lat+"/lng/"+lng+".html";
	$.get(url, function (data) {
	}, 'html');

};

// 手动定位
function getLocation(){
	navigator.geolocation.getCurrentPosition(getLocal);
}

function getLocal(position) {
	var lng = position.coords.longitude;
	var lat = position.coords.latitude;
	var url = "/wap/index/local/lat/"+lat+"/lng/"+lng+".html";

	$.get(url, function (data) {
		$("#local").html(data);
	}, 'html');

};


// 页面跳转
function jump(url){
    if(url){
        location.href=url;
    }else{
        history.back(-1);
    }
}



// 成功提示
function success(msg,timeout,callback){
	$('.ui-actionsheet').removeClass('show');
	el = $.tips({
		content:msg,
		stayTime:timeout,
		type:"success"
	})
	el.on("tips:hide",function(){
		$('.ui-actionsheet').removeClass('show');
		eval(callback);
	})



}


// 错误提示
function error(msg,timeout,callback){
	$('.ui-actionsheet').removeClass('show');
	el = $.tips({
		content:msg,
		stayTime:timeout,
		type:"warn"
	})
	el.on("tips:hide",function(){
		eval(callback);
	});

}


// 更换验证
function verify() {
    $(".verify").click();
}



//加载提示
function showLoader(obj) {
	str = '<div class="ui-loading-wrap"><p>正在加载中...</p><i class="ui-loading"></i></div>';
	obj.append(str);
}

function hideLoader(){
	$(".ui-loading-wrap").remove();
}



$(".ui-scroll-top").hide();
	
//当滚动条的位置处于距顶部100像素以下时，跳转链接出现，否则消失

$(window).scroll(function() {
	if ($(window).scrollTop() > 100) {
		$(".ui-scroll-top").show();
	} else {
		$(".ui-scroll-top").hide();
	}
});
//当点击跳转链接后，回到页面顶部位置
function toTop(){
	$("body").scrollTop(0);
	return false;
}




//滑动加载
function loaddata(page, obj, sc) {
	niulock = 1;
	var link = page.replace('0000', niunum);
	showLoader(obj);
	$.get(link, function (data) {
		if (data != 0) {
			obj.append(data);
		}
		niulock = 0;
		hideLoader();
	}, 'html');
	
	if (sc == true) {
		$(window).scroll(function () {
			var wh = $(window).scrollTop();
			var xh = $(document).height() - $(window).height() -  55;
			if (niulock == 0 && wh >= xh ) {
				niulock = 1;
				niunum++;
				var link = page.replace('0000', niunum);
				showLoader(obj);
				var timeout = setTimeout(function(){
					niulock = 0;
					hideLoader();
				},5000);
				$.get(link, function (data) {
					if (data != 0) {
						if(timeout){
							clearTimeout(timeout);
							timeout = null;
						}
						obj.append(data);
					}
					niulock = 0;
					hideLoader();
				}, 'html');
			}
		});
	}
}


/*复制内容*/
function copytext(str){
	var clipBoardContent= str;
	if(navigator.userAgent.toLowerCase().indexOf('ie') > -1) {
		clipboardData.setData('Text',clipBoardContent);
		return true;
	} else {
		prompt("请复制网址:",clipBoardContent); 
	}
}

/*是否微信*/
function isweixin(){
    var ua = navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i)=="micromessenger") {
        return true;
    } else {
        return false;
    }
}


/*赞踩操作*/
$(document).on('click','a[mini="dig"]', function(e){ 
	e.preventDefault();
	var href = $(this).attr('href');
	var rel = $(this).attr('rel');
	$.get(href,function(data){
		if(data == 0){
			error("请不要重复操作！",2000,'');
		}else{
			$('#'+rel).html(data);
			success("操作成功啦！",2000,'');
		}
	},'html');

});

	
/*重构链接*/
$(document).on("click","a[mini='act']",function(e){
	e.preventDefault();
	url = $(this).attr('href');
	$.get(url, function (data) {
		data = data.replace('<script>','');
		data = data.replace('</script>','');
		eval(data);
		return;
	}, 'html');
});


//验证码点击
$(document).on('click','.verify', function(e){ 
	$(this).attr('src', '/auth/verify/index/mt/' + Math.random()+'.html');
});

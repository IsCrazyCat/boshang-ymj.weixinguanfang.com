//JQUERY 模拟手机上拉下拉效果
/*
程序说明：
1、用于兼容苹果/安卓的上拖下拽的手机效果;
2、开发者龙啸VS猪猪 QQ：182382857;
3、PC端通用，但是PC端使用时需注意要把content的onclick事件单独处理，因为PC端不支持滑屏事件，如这样使用[
	var Top=0;
	function PcSetOclick(n){ //从第N个开始加载
		for(i=n;i<$('.content .Scroll_Element ul li').size();++i){
			var on=$('.content .Scroll_Element ul li:eq('+i+')').attr('onclick');
			$('.content .Scroll_Element ul li:eq('+i+')').removeAttr('onclick');
			$('.content .Scroll_Element ul li:eq('+i+')').attr('onclick-data',on);
		}
		$('.content .Scroll_Element ul li').on('mousedown',function(){Top=$('.content .Scroll_Element').scrollTop();});
		$('.content .Scroll_Element ul li').on('mouseup',function(){
			var t=$('.content .Scroll_Element').scrollTop();
			//alert(t+' '+Top);
			if(t==Top && ( parseInt($('.content .Scroll_Element').css('margin-top'))==0 && parseInt($('.content .Scroll_Element_top').css('margin-top'))==0 ) ){ //此时才执行方法 当滚动条未动 且 上下拖动未发生变化时才执行
				eval($(this).attr('onclick-data'));
			}
		});
	}
	try{ //判断设备是否支持滑动事件
		document.createEvent("TouchEvent"); 
	}catch(e){ //不支持滑屏事件时才执行
		PcSetOclick(0); //从第一个开始加载
	}
];
4、必须先加载JQUERY 再加载 funcion.js 再加载 diyScroll.js;
5、具体程序是怎么执行的，请看默认配置。

例子：
	$('.content').Frame({type:[1,1],background:'#760202',color:'#fca6a6',topfunc:'reFresh()',botfunc:'loadData()'}); //上拉/下拉 刷新/加载 页面效果
	$('.content').Frame({type:[0,1],background:'#760202',color:'#fca6a6',topfunc:'reFresh()',botfunc:'loadData()'}); //上拉/下拉 只弹动/加载 页面效果
	$('.content').Frame({type:[1,0],background:'#760202',color:'#fca6a6',topfunc:'reFresh()',botfunc:'loadData()'}); //上拉/下拉 刷新/只弹动 页面效果
	$('.content').Frame({type:[0,0],background:'#760202',color:'#fca6a6',topfunc:'reFresh()',botfunc:'loadData()'}); //上拉/下拉 只弹动/只弹动 页面效果
备注：程序内部所有时间单位都为毫秒
*/

	$.fn.Frame=function(obj){
		//默认配置
		if(!obj)obj={};	
		if(!obj.type)obj.type=[1,1]; //顶部[0只弹动,1有效果] 底部[0只弹动,1有效果]
		if(!obj.restor)obj.restor=300; //恢复弹动的时间毫秒
		if(!obj.watting)obj.watting=800; //等待刷新动画 [刷新中 加载中] 的效果延迟时间 毫秒
		if(!obj.background)obj.background='#cccccc'; //背景颜色
		if(!obj.color)obj.color='#666666'; //文字颜色
		if(!obj.defaultimg)obj.defaultimg='image/fame_default.png'; //初始图片
		if(!obj.refreshimg)obj.refreshimg='image/refresh.png'; //刷新图片
		if(!obj.fontSize)obj.fontSize='1em'; //字体大小
		if(!obj.toptext)obj.toptext='下拉刷新'; //顶部初始提示文字
		if(!obj.toptext_ok)obj.toptext_ok='释放刷新'; //顶部刷新提示文字
		if(!obj.bottext)obj.bottext='上拉加载';    //底部初始提示文字
		if(!obj.bottext_ok)obj.bottext_ok='释放加载'; //底部加载提示文字
		if(!obj.reFresh)obj.reFresh='刷新中···'; //顶部执行diyfunc后的提示文字
		if(!obj.loading)obj.loading='加载中···'; //底部执行diyfunc后的提示文字
		if(!obj.scrollTime)obj.scrollTime=1600; //快速滑动时滑动整块内容高度的延时(即多少毫秒能从底部回到顶部)
		if(!obj.Scroll)obj.Scroll=0; //是否显示滑动条0有1没有
		if(!obj.ScrollWidth)obj.ScrollWidth=2; //滚动条的宽
		if(!obj.ScrollColor)obj.ScrollColor='#777777'; //滚动条的颜色
		if(!obj.ScrollAlpha)obj.ScrollAlpha=0.7; //滚动条透明度
		if(!obj.ScrollBorder)obj.ScrollBorder='0'; //滚动条边框1px solid #666666
		obj.PC=false; //是否为PC
	
		//取对象值
		obj.self=this; //当前对象
		if(PhoneType()!='Android')obj.ScrollWidth=parseInt(obj.ScrollWidth/2);
		var zb={startX:0,startY:0,X:0,Y:0}; //bl快速滑动时根据页面内容的高度来自动判断滑动的倍率
		var scrid=!obj.id ? 'Scroll_Element' : obj.id,$this=$(obj.self); //加入的滚动层ID
		obj.H=$this.height();
		obj.W=$this.width();
		
		//底部效果加的
		var bot={}; //底部变量
		
		function touchSatrt(evt) { //起始事件
			if(!zb.Animate){
				$this.find('.'+scrid).stop(); //停止滚动的动画 向下推的不停止
				$this.find('.'+scrid+'_scroll').stop(); //滚动条停止
			}
			if(obj.PC)$this.find('.'+scrid).bind('mousemove', touchMove);  //移动时
			$this.find('.'+scrid+'_top').css({marginTop:0});
			$this.find('.'+scrid).css({marginTop:0});
			if(obj.Scroll==0)$this.find('.'+scrid+'_scroll').animate({opacity:obj.ScrollAlpha},250);; //显示滚动条
			$this.find('.'+scrid).height($this.height()).width($this.width());
			obj.H=$this.find('.'+scrid).height();
			obj.W=$this.find('.'+scrid).width();
			obj.htmlH=$this.find('.'+scrid+'_html').outerHeight();
			zb.botPx=parseInt($this.find('.'+scrid+'_bot').css('margin-bottom')); //底部的初始距离
			zb.topPx=parseInt($this.find('.'+scrid+'_top').css('margin-top')); //顶部的初始距离
			zb.htmlPx=parseInt($this.find('.'+scrid).css('margin-top')); //内容滑块区的顶部距离
			zb.ScrollTop=$this.find('.'+scrid).scrollTop(); //初始滚动
			zb.startTime=(new Date()).getTime(); //快速滑动时间判断
			$this.find('.'+scrid+'_scroll').css({height:parseInt(obj.H*(obj.H/obj.htmlH))}); //滚动条高度
			zb.scrTop=parseInt($this.find('.'+scrid+'_scroll').css('margin-top')); //滑动条的距离
			zb.SliderTop=false;zb.SliderBot=false;
			zb.X=0;zb.Y=0;zb.startX=0;zb.startY=0;zb.SliderTop=false;zb.SliderBot=false;
			$this.find('.'+scrid+'_scroll').css({bottom:'auto'}); //对应滚动条随底部上拉效果恢复默认
			
			//底部添加的公共效果
			zb.botml=parseInt($('.bottom .scroll').css('margin-left')); //初始滚动距离
			zb.bot={xy:$('.bottom').offset(),w:$('.bottom').width(),h:$('.bottom').height()}; //坐标
			zb.swipe={xy:$('.swipe').offset(),w:$('.swipe').width(),h:$('.swipe').height()}; //滑动座标
			zb.Newml=parseInt($(".new_lb .scroll").css("margin-left")); //新闻块
			zb.New={self:$(".new_lb"),size:$(".new_lb .one").size(),xy:$('.new_lb').offset(),w:$('.new_lb').width(),h:$('.new_lb').height()}; //新闻总条数
			
			try {
				//evt.preventDefault(); //阻止触摸时浏览器的缩放、滚动条滚等
				var touch = evt.touches[0]; //获取第一个触点  
				zb.startX=Number(touch.pageX); //页面触点X坐标  
				zb.startY=Number(touch.pageY); //页面触点Y坐标
			} catch(e) {
				zb.startX=Number(evt.pageX); //页面触点X坐标  
				zb.startY=Number(evt.pageY); //页面触点Y坐标 
			}
			//记录触点初始位置
			//alert(zb.startX+' '+zb.startY);
			if(zb.X==0 && zb.Y==0)return false;
		}
		function touchMove(evt) { //滑动过程中
			//if(zb.Animate)return false;
			evt.preventDefault();
			try {			 
				var touch = evt.touches[0]; //获取第一个触点
				zb.X=Number(touch.pageX); //页面触点X坐标 
				zb.Y=Number(touch.pageY); //页面触点Y坐标
			} catch(e) {
				zb.X=Number(evt.pageX); //页面触点X坐标
				zb.Y=Number(evt.pageY); //页面触点Y坐标	
			}
			
			if(zb.X==0 && zb.Y==0)return false;
			
			if (zb.X>0 && zb.X - zb.startX != 0) {
				bot.self=$('.bottom');
				if(zb.X<zb.startX){ //往左		
					if(zb.startX-zb.X>=obj.W/8 && (zb.startY>=zb.bot.xy.top && zb.startY<=zb.bot.xy.top+zb.bot.h)){ //在底部坐标范围内
						//evt.preventDefault(); //阻止触摸时浏览器的缩放、滚动条滚等
						zb.bot.slide=true;
						bot.self.find('.scroll').css({marginLeft:zb.botml-(zb.startX-zb.X)});
						return false;
					}
					
					if(zb.startX-zb.X>=obj.W/8 && (zb.startY>=zb.swipe.xy.top && zb.startY<=zb.swipe.xy.top+zb.swipe.h)){ //在滑动动画坐标范围内
						zb.swipe.slide=true;
					}
					
					if(zb.startX-zb.X>=obj.W/8 && (zb.startY>=zb.New.xy.top && zb.startY<=zb.New.xy.top+zb.New.h)){ //在新闻动画坐标范围内
						zb.New.slide=true;
						zb.New.self.find('.scroll').css({marginLeft:zb.Newml-(zb.startX-zb.X)});
						return false;
					}
				}
				
				if(zb.startX<zb.X){ //往右
					if(zb.X-zb.startX>=obj.W/8 && (zb.startY>=zb.bot.xy.top && zb.startY<=zb.bot.xy.top+zb.bot.h)){ //在底部坐标范围内
						//evt.preventDefault(); //阻止触摸时浏览器的缩放、滚动条滚等
						zb.bot.slide=true;
						bot.self.find('.scroll').css({marginLeft:zb.botml+(zb.X-zb.startX)});
						return false;
					}
					
					if(zb.X-zb.startX>=obj.W/8 && (zb.startY>=zb.swipe.xy.top && zb.startY<=zb.swipe.xy.top+zb.swipe.h)){ //在滑动动画坐标范围内
						zb.swipe.slide=true;
					}
					
					if(zb.X-zb.startX>=obj.W/8 && (zb.startY>=zb.New.xy.top && zb.startY<=zb.New.xy.top+zb.New.h)){ //在新闻坐标范围内
						//evt.preventDefault(); //阻止触摸时浏览器的缩放、滚动条滚等
						zb.New.slide=true;
						zb.New.self.find('.scroll').css({marginLeft:zb.Newml+(zb.X-zb.startX)});
						return false;
					}
				}
			} //左右滑动
			if(zb.bot.slide || zb.swipe.slide || zb.New.slide)return false; //如果在两个动画之间都不继续执行
			
			if (zb.Y - zb.startY != 0 && zb.startY!=0) { //上下滑动
				evt.preventDefault(); //阻止滚动条
				if(zb.Y>zb.startY){ //向下拉
					//if(zb.Y-zb.startY<obj.W/10 && parseInt($this.find('.'+scrid+'_top').css('marginTop'))==0)return false; //此处是为了设置上下滑动时又有左右滑动避免同时触发
					//$('.footer li:eq(2)').html(' '+zb.Y+' '+zb.startY+' '+(zb.htmlPx-(zb.startY-zb.Y)));				
					if(zb.ScrollTop>0){
						zb.temp=zb.ScrollTop-(zb.Y-zb.startY)<0 ? 0 : zb.ScrollTop-(zb.Y-zb.startY);
	
						$this.find('.'+scrid).scrollTop(zb.temp);					
						//$this.find('.'+scrid).css({marginTop:0});
						$this.find('.'+scrid+'_scroll').css('margin-top',parseInt( obj.H* ( $this.find('.'+scrid).scrollTop()/(obj.htmlH) ) )+'px');
					}else{ //如果为零（不能再上滚时） 执行拖动刷新效果
						$this.find('.'+scrid).css({marginTop:0});					
						if(zb.ScrollTop==0 && !zb.SliderBot){ //如果为零才执行拖动效果
							$this.find('.'+scrid+'_top').css({marginTop:zb.topPx+(zb.Y-zb.startY)});
							if(zb.Y-zb.startY>obj.H/5*1){ //如果拉动超过1/4则提示文字效果发生变化
								$this.find('.'+scrid+'_top').find('span').html(obj.toptext_ok); //释放刷新
							}else{
								$this.find('.'+scrid+'_top').find('span').html(obj.toptext); //下拉刷新
							}
							if(!zb.SliderTop)zb.SliderTop=true; //当前正执行的上边滑动 则不能正执行下边的了
						}
						$this.find('.'+scrid+'_scroll').css('margin-top',0);
					}
				}else{ //向上拉
					//if(zb.startY-zb.Y<obj.W/10 && parseInt($this.find('.'+scrid+'_scroll').css('margin-top'))==0)return false; //此处是为了设置上下滑动时又有左右滑动避免同时触发
					if(zb.ScrollTop>=obj.htmlH-obj.H && !zb.SliderTop){ //如果滚动到底部才执行滚动加载效果
						$this.find('.'+scrid).css({marginTop:zb.htmlPx-(zb.startY-zb.Y)});
						$this.find('.'+scrid+'_bot').height(obj.botH+(zb.startY-zb.Y));
						$this.find('.'+scrid+'_scroll').css({bottom:$this.find('.'+scrid+'_bot').height()-obj.botH}); //滚动条随底部上拉而变化对应start动作
						if(zb.startY-zb.Y>obj.H/5*1){ //如果拉动超过1/4则提示文字效果发生变化
							$this.find('.'+scrid+'_bot').find('span').html(obj.bottext_ok); //释放加载
						}else{
							$this.find('.'+scrid+'_bot').find('span').html(obj.bottext);  //上拉加载
						}
						$this.find('.'+scrid+'_scroll').css({marginTop:zb.scrTop-(zb.startY-zb.Y)});
						if(!zb.SliderBot)zb.SliderBot=true; //当前正执行的下边滑动 则不能正执行上边的了
					}else{					
						zb.temp=zb.ScrollTop+(zb.startY-zb.Y)>obj.htmlH-obj.H ? obj.htmlH-obj.H : zb.ScrollTop+(zb.startY-zb.Y);
						$this.find('.'+scrid).scrollTop(zb.temp);
						$this.find('.'+scrid+'_top').css({marginTop:0});	
						$this.find('.'+scrid+'_scroll').css('margin-top',parseInt( obj.H* ( $this.find('.'+scrid).scrollTop()/(obj.htmlH) ) )+'px');										
					}				
				}
			}
		}
		function touchEnd(evt) { //滑动结束事件
			//if(zb.Animage)return false;
			if(obj.PC)$this.find('.'+scrid).unbind('mousemove'); //如果为PC解除移动绑定
			//evt.preventDefault(); //不能阻止否则不能点击
					
			if(zb.X==0 && zb.Y==0){
				$this.find('.'+scrid+'_scroll').animate({opacity:0},200);
				return false;
			}
			//alert('结束：'+zb.X+' '+zb.Y+' '+zb.startX+' '+Math.abs(zb.startY-zb.Y));		
			if(zb.X<=0){ //向左中断的情况
				$this.find('.'+scrid+'_scroll').animate({opacity:0},200);
			}
			if(zb.X>0 && zb.X - zb.startX != 0) { //左右滑动				
				bot.self=$('.bottom');
				var n=Number(bot.self.find('.o.over').attr('data-num'));
				if(zb.X<zb.startX){ //往左
					if(zb.bot.slide){
						if(zb.startX-zb.X<obj.W/4){ //小于1/4则恢复初始
							bot.self.find('.scroll').animate({marginLeft:zb.botml},200);
							return false;
						}else{
							if(n<bot.self.find('.o').size()-1){
								bot.self.find('.butt').find('.l').trigger('click');
							}else{
								bot.self.find('.scroll').animate({marginLeft:zb.botml},200);
							}
						}
					}
					if(zb.New.slide){
						if(zb.startX-zb.X<obj.W/4 && zb.New.size>0){ //在新闻动画坐标范围内
							zb.New.self.find('.scroll').animate({marginLeft:zb.Newml},200);
							return false;
						}else{
							n=zb.New.self.find('.one').index(zb.New.self.find('.one.over')); //当前滚动域编号
							if(n<zb.New.size-1){
								zb.New.self.find('.scroll').animate({marginLeft:parseInt(zb.New.self.find('.one:eq('+(n+1)+')').attr('data-px'))},200,'',function(){
									zb.New.self.find('.one').removeClass('over');
									zb.New.self.find('.one:eq('+(n+1)+')').addClass('over');
								});
								
							}else{
								zb.New.self.find('.scroll').animate({marginLeft:zb.Newml},200); //恢复
							}
						}
					}
				}
				
				if(zb.X>zb.startX){ //往右
					if(zb.bot.slide){
						if(zb.X-zb.startX<obj.W/4){ //小于1/4则恢复初始
							bot.self.find('.scroll').animate({marginLeft:zb.botml},200);
							return false;
						}else{
							if(n>0){
								bot.self.find('.butt').find('.r').trigger('click');
							}else{
								bot.self.find('.scroll').animate({marginLeft:zb.botml},200);
							}
						}
					}
					
					if(zb.New.slide){
						if(zb.X-zb.startX<obj.W/4){ //小于1/4则恢复初始
							zb.New.self.find('.scroll').animate({marginLeft:zb.Newml},200);
							return false;
						}else{
							n=zb.New.self.find('.one').index(zb.New.self.find('.one.over')); //当前滚动域编号
							if(n>0){
								zb.New.self.find('.scroll').animate({marginLeft:parseInt(zb.New.self.find('.one:eq('+(n-1)+')').attr('data-px'))},200,'',function(){
									zb.New.self.find('.one').removeClass('over');
									zb.New.self.find('.one:eq('+(n-1)+')').addClass('over');
								});
								
							}else{
								zb.New.self.find('.scroll').animate({marginLeft:zb.Newml},200); //恢复
							}
						}
					}
				}
			}			
			if(zb.bot.slide || zb.swipe.slide || zb.New.slide){
				$this.find('.'+scrid+'_scroll').animate({opacity:0},200);//隐藏滚动条
				zb.bot.slide=false;
				zb.swipe.slide=false;
				zb.New.slide=false;
				return false;
			}
			
			if (zb.Y - zb.startY != 0 && zb.startY!=0) { //上下滑动
				evt.preventDefault();
				//alert($this.find('.'+scrid).scrollTop()+' '+(obj.htmlH-obj.H)+' '+(zb.startY-zb.Y));
				if(zb.Y>zb.startY){ //向下拉 上边执行效果
					//if(zb.Y-zb.startY<obj.W/10 && parseInt($this.find('.'+scrid+'_top').css('marginTop'))==0)return false; //此处是为了设置上下滑动时又有左右滑动避免同时触发
					if($this.find('.'+scrid).scrollTop()<=0){
						if(zb.ScrollTop<=0 && !zb.SliderBot){
							zb.temp=zb.Y-zb.startY>obj.H/5*1 && obj.topfunc && obj.type[0] ? obj.watting : 0; //上边的效果只用一半就行了
							setTimeout(function(){
								$this.find('.'+scrid+'_top').animate({marginTop:0},obj.restor);  //上边恢复初始效果
								$this.find('.'+scrid).css({marginTop:0});
								$this.find('.'+scrid+'_scroll').css({height:parseInt(obj.H*(obj.H/obj.htmlH)),marginTop:0}).animate({opacity:0},200); //滚动条高度 animate隐藏滚动条
							},zb.temp);
							if(zb.Y-zb.startY>obj.H/5*1 && obj.topfunc && obj.type[0]){								
								$this.find('.'+scrid+'_top').find('img').attr('src',obj.refreshimg);
								setTimeout(function(){Rotate('top',(new Date()).getTime(),0,parseInt(obj.watting/2)+obj.restor);},100); //旋转小动画
								$this.find('.'+scrid+'_scroll').animate({opacity:0},200);
								eval(obj.topfunc);
							} //执行diyfunc效果
						}
					}else{
						$this.find('.'+scrid+'_top').css({marginTop:0});
						zb.Time=(new Date()).getTime()-zb.startTime;
						//zb.scrollTime=obj.scrollTime;
						//alert((zb.Y-zb.startY)+' '+obj.H+' '+zb.Time);;
						if(((zb.Y-zb.startY)>obj.H/2 && zb.Time>200) || zb.Time>500){ //超过滑块区的一半和时间超过500毫秒时不滑动
							zb.temp=0;
							//zb.scrollTime=0;
							$this.find('.'+scrid+'_scroll').animate({opacity:0},200);
						}else{ //普通滑动效果
							obj.bl=parseInt((zb.Y-zb.startY)/(zb.Time/550));
							if($this.find('.'+scrid).scrollTop()-obj.bl<0){
								zb.temp=0;
								zb.scrollTime=parseInt($this.find('.'+scrid).scrollTop()*1.1);
							}else{
								zb.temp=$this.find('.'+scrid).scrollTop()-obj.bl;
								zb.scrollTime=parseInt((obj.bl)*1.1);
							}
							zb.temp=zb.Time<200 && zb.Y-zb.startY>obj.H/3 ? 0 : zb.temp; //100毫秒内滑动超过屏的一半则执行快速滑动
							zb.scrollTime=zb.Time<200 && zb.Y-zb.startY>obj.H/3 ? parseInt($this.find('.'+scrid).scrollTop()*0.6) : zb.scrollTime; //滑动的时间
							$this.find('.'+scrid).animate({scrollTop:zb.temp},zb.scrollTime,'easeOutQuad',function(){$this.find('.'+scrid+'_scroll').animate({opacity:0},200);});
							$this.find('.'+scrid+'_scroll').animate({marginTop:parseInt(obj.H*(zb.temp/$this.find('.'+scrid+'_html').height()))},zb.scrollTime);
						}					
					}
				}else{ //向上拉 下边执行效果
					//if(zb.startY-zb.Y<obj.W/10 && parseInt($this.find('.'+scrid+'_scroll').css('margin-top'))==0)return false; //此处是为了设置上下滑动时又有左右滑动避免同时触发
					if(zb.ScrollTop>=obj.htmlH-obj.H){ //如果滚动到底部才执行
						$this.find('.'+scrid+'_top').css({marginTop:0}); //顶部的刷新效果去掉
						if(zb.ScrollTop>=obj.htmlH-obj.H && !zb.SliderTop){
							zb.temp=zb.startY-zb.Y>obj.H/5*1 && obj.type[1] && obj.botfunc ? obj.watting : 0;
							setTimeout(function(){ //延时退出效果
								zb.Animate=true;
								$this.find('.'+scrid).animate({marginTop:0},obj.restor,'easeOutQuad',function(){zb.Animate=false;$this.find('.'+scrid+'_bot').css({height:obj.botH})});
								$this.find('.'+scrid+'_top').css({marginTop:0});
								$this.find('.'+scrid+'_scroll').css({height:parseInt(obj.H*(obj.H/$this.find('.'+scrid+'_html').height()))}).animate({marginTop:parseInt(obj.H*(zb.ScrollTop/$this.find('.'+scrid+'_html').height())),height:parseInt(obj.H*(obj.H/$this.find('.'+scrid+'_html').height()))},obj.restor,'swing',function(){$(this).animate({opacity:0}),200}); //滚动条高度							
							},zb.temp);
							if(zb.startY-zb.Y>obj.H/5*1 && obj.botfunc && obj.type[1]){
								$this.find('.'+scrid+'_bot').find('img').attr('src',obj.refreshimg);
								setTimeout(function(){Rotate('bot',(new Date()).getTime(),0,obj.watting+obj.restor);},100); //旋转小动画
								eval(obj.botfunc);
							} //执行diyfunc效果
						}
					}else{
						$this.find('.'+scrid).css({marginTop:0});
						zb.Time=(new Date()).getTime()-zb.startTime;
						//zb.scrollTime=obj.scrollTime;
						//alert((zb.startY-zb.Y)+' '+obj.H+' '+zb.Time);
						if(((zb.startY-zb.Y)>obj.H/2 && zb.Time>200) || zb.Time>500){ //超过滑块区的一半时和时间超过500毫秒时不滑动
							zb.temp=0;zb.scrollTime=0;
							$this.find('.'+scrid+'_scroll').animate({opacity:0},200); //隐藏滚动条
						}else{ //普通滑动效果
							obj.bl=parseInt((zb.startY-zb.Y)/(zb.Time/550)); //默认是1000毫秒=1秒的意思
							if($this.find('.'+scrid).scrollTop()+obj.bl>obj.htmlH-obj.H){
								zb.temp=obj.htmlH-obj.H;
								zb.scrollTime=parseInt((obj.htmlH-obj.H-$this.find('.'+scrid).scrollTop())*1.1);
							}else{
								zb.temp=$this.find('.'+scrid).scrollTop()+obj.bl;
								zb.scrollTime=parseInt((obj.bl)*1.1);
							}
							zb.temp=zb.Time<200 && zb.startY-zb.Y>obj.H/3 ? obj.htmlH-obj.H : zb.temp; //100毫秒内滑动超过屏的一半则执行快速滑动
							zb.scrollTime=zb.Time<200 && zb.startY-zb.Y>obj.H/3 ? parseInt((obj.htmlH-obj.H-$this.find('.'+scrid).scrollTop())*0.6) : zb.scrollTime; //滑动的时间
							$this.find('.'+scrid).animate({scrollTop:zb.temp},zb.scrollTime,'easeOutQuad',function(){$this.find('.'+scrid+'_scroll').animate({opacity:0},200);});
							$this.find('.'+scrid+'_scroll').animate({marginTop:parseInt(obj.H* (zb.temp/$this.find('.'+scrid+'_html').height()))},zb.scrollTime);
						}					
					}
				}
			}
			zb.bot.slide=false;
			zb.swipe.slide=false;
			zb.New.slide=false;
		}
		
		function Rotate(type,starttime,i,time){ //旋转动画 说明：type=top:bot starttime=开始时间毫秒 i=开始度数0-360 time=最多毫秒数
			var text=type=='top' ? obj.reFresh : obj.loading;
			$this.find('.'+scrid+'_'+type).find('span').html(text);
			var ds=360/36; //每次旋转的度数
			var d=200/ds; //用100毫秒旋转完
			if((new Date()).getTime()-starttime<time){
				i=i>=ds*35 ? 0 : i;
				$this.find('.'+scrid+'_'+type).find('img').css({'transform':'rotate('+i+'deg)','-moz-transform':'rotate('+i+'deg)','-ms-transform':'rotate('+i+'deg)','-o-transform':'rotate('+i+'deg)','-webkit-transform':'rotate('+i+'deg)'});
				i=i+ds;
				setTimeout(function(){Rotate(type,starttime,i,time)},d);
			}else{
				$this.find('.'+scrid+'_bot').find('img').css({'transform':'rotate(0deg)','-moz-transform':'rotate(0deg)','-ms-transform':'rotate(0deg)','-o-transform':'rotate(0deg)','-webkit-transform':'rotate(0deg)'}).attr('src',obj.defaultimg);
				$this.find('.'+scrid+'_top').find('img').css({'transform':'rotate(180deg)','-moz-transform':'rotate(180deg)','-ms-transform':'rotate(180deg)','-o-transform':'rotate(180deg)','-webkit-transform':'rotate(180deg)'}).attr('src',obj.defaultimg);
			}
		}
		
		function bindEvent(){//初始化事件			
			var str='<div class="'+scrid+'" style="-webkit-transform:translate3d(0,0,0);-moz-transform:translate3d(0,0,0);-ms-transform:translate3d(0,0,0);-o-transform:translate3d(0,0,0);-webkit-transform:translate3d(0,0,0);background-image:url(about:blank);"><div class="'+scrid+'_html" style="overflow:hidden;background-image:url(about:blank);">'+$this.html()+'</div></div>';
			$this.css('overflow','hidden').empty().html(str); //加入新层
			$this.find('.'+scrid).css({height:obj.H+1,width:obj.W,overflowX:'hidden',overflowY:'hidden',zIndex:1,position:'relative',background:$this.css('background'),backgroundColor:$this.css('background-color')}); //初始滚动区域
					
			$this.find('.'+scrid).before('<div class="'+scrid+'_to_top" style="z-index:0;position:relative;overflow:hidden;clear:both;background-color:'+obj.background+';color:'+obj.color+';font-size:'+obj.fontSize+';margin-bottom:1px;clear:both;background-image:url(image/ref_logo.png);background-repeat:no-repeat; background-position:center center;background-size:auto 30%;"><div style="text-align:center;vertical-align:middle;clear:both;padding:0.5em;" class="'+scrid+'_top"><img src="'+obj.defaultimg+'" style="vertical-align:middle;transform:rotate(180deg);-ms-transform:rotate(180deg);-moz-transform:rotate(180deg);-webkit-transform:rotate(180deg);-o-transform:rotate(180deg);margin-right:0px;height:1.5em;margin-right:0.2em;"><span style="display:inline-block;vertical-align:middle;margin:0;padding:0;align:left;height:auto;width:auto;float:none;">'+obj.toptext+'</span></div></div>'); //顶部效果
			
			$this.find('.'+scrid).after('<div class="'+scrid+'_bot" style="z-index:0;position:relative;overflow:hidden;background:'+obj.background+';color:'+obj.color+';font-size:'+obj.fontSize+';margin-top:1px;"><div style="text-align:center;vertical-align:middle;padding:0.5em;"><img src="'+obj.defaultimg+'" style="vertical-align:middle;height:1.5em;margin-right:0.2em;"><span style="display:inline-block;vertical-align:middle;margin:0;padding:0;align:left;height:auto;width:auto;float:none;">'+obj.bottext+'</span></div></div>'); //底部效果
			
			$this.find('.'+scrid+'_to_top').css({marginTop:-$this.find('.'+scrid+'_to_top').outerHeight()-2});
			obj.H=$this.find('.'+scrid).height(); //重新获得高
			obj.W=$this.find('.'+scrid).width(); //重新获得宽
			obj.htmlH=$this.find('.'+scrid+'_html').outerHeight(); //内容的总高
			obj.botH=$this.find('.'+scrid+'_bot').outerHeight(); //底部的高
			
			var o=$this.find('.'+scrid).offset(),radius=(Math.ceil(obj.ScrollWidth/2)+2).toString()+'px'; //圆角
			//初始滚动条
			$this.find('.'+scrid).before('<div class="'+scrid+'_scroll" style="width:'+obj.ScrollWidth+'px;font-size:'+obj.ScrollWidth+'px;overflow:hidden;z-index:2;position:absolute;left:'+(obj.W-obj.ScrollWidth-3)+'px;background:'+obj.ScrollColor+';border-radius:'+radius+';-moz-border-radius:'+radius+';-ms-border-radius:'+radius+';-o-border-radius:'+radius+';-webkit-border-radius:'+radius+';opacity:'+obj.ScrollAlpha+';-moz-opacity:'+obj.ScrollAlpha+';-webkit-opacity:'+obj.ScrollAlpha+';filter:alpha(opacity='+(obj.ScrollAlpha*100)+');border:'+obj.ScrollBorder+';"></div>'); //插入滚动条
			$this.find('.'+scrid+'_scroll').css({height:parseInt(obj.H*(obj.H/obj.htmlH)),opacity:obj.ScrollAlpha}); //滚动条高度
			if(obj.Scroll==1){
				$this.find('.'+scrid+'_scroll').css({visibility:'hidden'}); //是否显示滑动条0有1没有
			}else{
				$this.find('.'+scrid+'_scroll').animate({opacity:0},200); //隐藏滚动条
			}
			
			//判断是否取消顶部和底部效果
			if(obj.type[0]==0)$this.find('.'+scrid+'_top').css('visibility','hidden');
			if(obj.type[1]==0)$this.find('.'+scrid+'_bot').find('div').css('visibility','hidden');
			
			try{ //判断设备是否支持滑动事件
				document.createEvent("TouchEvent"); 
				$this.find('.'+scrid).bind('touchmove', function(){touchMove(event)});  //移动时
				$this.find('.'+scrid).bind('touchend', function(){touchEnd(event)});   //移动结束时
				$this.find('.'+scrid).bind('touchcancel', function(){touchEnd(event)});   //移动中断时
				$this.find('.'+scrid).bind('touchstart', function(){touchSatrt(event)});  //开始坐标*/		
			}catch(e){ //其它[PC等]
				$this.find('.'+scrid).bind('mousemove', touchMove);  //移动时
				$this.find('.'+scrid).bind('mouseup', touchEnd);   //移动结束时
				$this.find('.'+scrid).bind('mousedown', touchSatrt);  //开始坐标*/
				obj.PC=true;
			}
		}
		bindEvent(); //绑定事件
	}
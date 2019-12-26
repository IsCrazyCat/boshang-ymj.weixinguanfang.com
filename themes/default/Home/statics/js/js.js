// JavaScript Document
$(document).ready(function (e) {
	/*input-label标签*/
	$(".seat-check").click(function(){
		if($(this).hasClass("on")){
			$(this).removeClass("on");
		}
		else{
			$(this).addClass("on");
		}
	});	
	/*input-label标签end*/
    $('.menu_fllist2 > .item2').hover(function () {
        /*var eq = $('.menu_fllist2 > .item2').index(this), //获取当前滑过是第几个元素
                h = $('.menu_fllist2').offset().top, //获取当前下拉菜单距离窗口多少像素
                s = $(window).scrollTop(), //获取游览器滚动了多少高度
                i = $(this).offset().top, //当前元素滑过距离窗口多少像素
                item = $(this).children('.menu_flklist2').height(), //下拉菜单子类内容容器的高度
                sort = $('.menu_fllist2').height();                     //父类分类列表容器的高度

        if (item > sort) {                                              //如果子类的高度小于父类的高度
            if (eq == 0) {
                $(this).children('.menu_flklist2').css('top', (i - h));
            } else {
                $(this).children('.menu_flklist2').css('top', (i - h) + 1);
            }
        } else {
            if (s > h) {                                                //判断子类的显示位置，如果滚动的高度大于所有分类列表容器的高度
                if (i- s > 0) {                                            //则 继续判断当前滑过容器的位置 是否有一半超出窗口一半在窗口内显示的Bug,
                    $(this).children('.menu_flklist2').css('top', (s-h) + 2);
                } else {
                    $(this).children('.menu_flklist2').css('top', (s-h)- (-(i-s)) + 2);
                }
            } else {
                $(this).children('.menu_flklist2').css('top', 0);
            }
        }*/

        $(this).addClass('on');
        $(this).children('.menu_flklist2').css('display', 'block');
    }, function () {
        $(this).removeClass('on');
        $(this).children('.menu_flklist2').css('display', 'none');
    });//导航菜单js

    //支付方式切换
    $(".mode_zx li").each(function (e) {
        $(this).click(function () {
            $(".mode_zx li").removeClass("on");
            $(this).addClass("on");
            $(".table1").each(function (i) {
                if (e == i) {
                    $(".table1").hide();
                    $(this).show();
                }
            });

        });
    });
    $(".inp").each(function () {
        $(this).click(function () {
            $(".inp").removeClass("ol");
            $(this).addClass("ol");
        });
    });

    $('#selectBoxInput').on("mouseleave", function () {
        $('.selectList').stop().hide();
    }).on("mouseenter", function(){
        $('.selectList').stop().slideDown();
    });
    $('.selectList').on("mouseleave", function(){
        $(this).stop().hide();
    }).on("mouseenter", function(){
        $(this).stop().show();
    });
    $(".selectList li a").click(function () {
        $("#selectBoxInput").html($(this).html());
        $('.selectList').hide();
    });
    //头部搜索框js
    $(function () {
		$('.changeCity_list_box ul li').each(function(e){
			$(this).hover(function(){
				$(this).parent().find("li").removeClass("on");
				$(this).addClass("on");
				$(".changeCity_list_pull .list").each(function(i){
					if(e==i){
						$(this).parent().find(".list").hide();
						$(this).show();
					}
					else{
						$(this).hide();
					}
				});
			});
		});
	});//头部城市部分代码
    $('.sy_hottjTab li').each(function (e) {
        $(this).hover(function () {
            $(".sy_hottjTab li").removeClass("on");
            $(this).addClass("on");
            $(".sy_hottj").each(function (i) {
                if (e == i) {
                    $(".sy_hottj").hide();
                    $(this).show();
                }
                else {
                    $(this).hide();
                }
            });
        });
    });

    var href = window.location.href;
    var param = href.split('#');
    if (param[1] != undefined && param[1] !=null && param[1] != "") {
        var _targetTop2 = $('#' + param[1]).offset().top;//获取位置
        jQuery("html,body").animate({scrollTop: _targetTop2}, 300);//跳转
    }
    $(".spxq_xqT2 ul li").click(function () {
        $(".spxq_xqT2 ul li").removeClass("on");
        $(this).addClass("on");
        var _targetTop = $('.' + $(this).find('code').attr('rel')).offset().top;//获取位置
        jQuery("html,body").animate({scrollTop: _targetTop}, 300);//跳转
    });
    $(".spxq_xqT li").click(function () {
        $(".spxq_xqT2 ul li").removeClass("on");
        $(".jq_" + $(this).find('code').attr('rel')).addClass("on");
        var _targetTop = $('.' + $(this).find('code').attr('rel')).offset().top;//获取位置
        jQuery("html,body").animate({scrollTop: _targetTop}, 300);//跳转
    });
    $(".spxq_setTsG").click(function () {
        $(this).parent(".spxq_setTs").hide();
    });

	$(window).scroll(function () {
		if ($(window).scrollTop() <220) {
			$("#cart_waimai").css("top","220px");
			$("#cart_waimai").css("bottom","auto");
		}
		else{
			$("#cart_waimai").css("top","auto");
			$("#cart_waimai").css("bottom","260px");
		}
	});

})

//晒图评价-弹出大图
	$(function(){
		$(".spxq_pjLi a").click(function(){
                    $(".mask_spxq_pjLi_img img").attr("src", $(this).attr('rel')); 
			$(".mask_spxq_pjLi_img_mask").show();
		});
		$(".mask_spxq_pjLi_img_mask").click(function(){
			$(this).hide();
		});
	});


/**/
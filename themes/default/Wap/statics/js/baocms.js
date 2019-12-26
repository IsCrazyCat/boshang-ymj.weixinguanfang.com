var baolock = 1;
var baonum = 1;
var myScroll = null;

function showLoader(msg) {
    $("#loading").show();
    $(".bao_loading").html(msg).show();
}

function hideLoader()
{
    $("#loading").hide();
    $(".bao_loading").hide();
}

function dingwei(page, lat, lng) {
    page = page.replace('llaatt', lat);
    page = page.replace('llnngg', lng);
    $.get(page, function (data) {
    }, 'html');
}

/* 公用 */

$(function () {

    if ($('#search-bar').length > 0)
    {
        $('#search-bar li').width(100 / $('#search-bar li').length + '%');
        $('.page-center-box').css('top', '0.9rem');
    }
    if ($('#tab-bar').length > 0)
    {
        $('.page-center-box').css('top', '1rem');
    }
    if ($('footer').length == 0)
    {
        $('.page-center-box').css('bottom', 0);
    }

});




function loaddata(page, obj, sc) {
    var link = page.replace('0000', baonum);
    showLoader('正在加载中....');

    $.get(link, function (data) {
        if (data != 0) {
            obj.append(data);
        }
        baolock = 0;
        hideLoader();
    }, 'html');
    if (sc === true) {
        $(".page-center-box").scroll(function () {
            if (!baolock && $(this).scrollTop() == $("#scroll").height() - $(this).height()+50) {
                baolock = 1;
                baonum++;
                var link = page.replace('0000', baonum);
                showLoader('正在加载中....');
                $.get(link, function (data) {
                    if (data != 0) {
                        obj.append(data);
                    }
                    baolock = 0;
                    hideLoader();
                }, 'html');
            }
        });
    }
	
	
}

function set_bar(line_num, num) { //line,每行个数   num,总个数   多行也可以自伸展。
    var line = 0;//行数，也是百分比个数
    var mo = num % line_num; //求出余数
    if (mo == 0) {
        line = parseInt(num / line_num);
    } else {
        line = parseInt(num / line_num) + 1;
    }

    var arr = new Array();
    if (mo == 0) {
        for (var i = 0; i < (line) * line_num; i++) {
            arr[i] = (100 / line_num) + '%';
        }
    } else {
        for (var i = 0; i < (line - 1) * line_num; i++) {
            arr[i] = (100 / line_num) + '%';
        }
        for (var ii = i; ii < mo + i; ii++) {
            arr[ii] = (100 / mo) + '%';
        }
    }

    var p = 0;
    $.each(arr, function (i, val) {
        p = p + 1;
        $('#all-bar').find('#l' + p).width(val);
    });

    var top_num = 0;
    if (line == 1) {
        top_num = 0.9;
    } else {
        top_num = 0.9 + 0.4 * (line - 1);
    }

    $('.page-center-box').css('top', top_num + 'rem');
}



function check_user_mobile(url1,url2){


	// 	layer.open({
	// 		type: 1,
	// 		title:'请绑定手机后支付',
	// 		skin: 'layui-layer-demo', //加上边框
	// 		area: ['100%', '2.8rem'], //宽高
	// 		content: '<p class="form">手机号：<br><input name="mobile" id="mobile" type="text" /> <input type="button""  id="jq_send" value="获取验证码"/><br>验证码：<br><input  name="yzm" id="yzm" type="text" /> 输入验证码<br><input type="submit" value="立刻认证" id="go_mobile" /></p>'
	// 	});
	// //获取验证码
    //     var mobile_timeout;
    //     var mobile_count = 100;
    //     var mobile_lock = 0;
    //     $(function () {
    //         $("#jq_send").click(function () {
    //
    //             if (mobile_lock == 0) {
    //                 mobile_lock = 1;
    //                 $.ajax({
    //                     url: url1,
    //                     data: 'mobile=' + $("#mobile").val(),
    //                     type: 'post',
    //                     success: function (data) {
    //                         if (data.status == 'success') {
    //                             mobile_count = 60;
    //                             layer.msg(data.msg,{icon:1});
    //                             BtnCount();
    //                         } else {
    //                             mobile_lock = 0;
    //                             layer.msg(data.msg,{icon:2});
    //                         }
    //                     }
    //                 });
    //             }
    //
    //         });
    //     });
    //     BtnCount = function () {
    //         if (mobile_count == 0) {
    //             $('#jq_send').val("重新发送");
    //             mobile_lock = 0;
    //             clearTimeout(mobile_timeout);
    //         }
    //         else {
    //             mobile_count--;
    //             $('#jq_send').val("重新发送(" + mobile_count.toString() + ")秒");
    //             mobile_timeout = setTimeout(BtnCount, 1000);
    //         }
    //     };
	// 	//提交
	// 	$('#go_mobile').click(function(){
	// 		var ml = $('#mobile').val();
	// 		var y = $('#yzm').val();
	// 		$.post(url2,{mobile:ml,yzm:y},function(result){
	// 			if(result.status == 'success'){
	// 				layer.msg(result.msg);
	// 				setTimeout(function(){
	// 					location.reload(true);
	// 				},3000);
	// 			}else{
	// 				layer.msg(result.msg,{icon:2});
	// 			}
	// 		},'json');
	// 	})
	//
	//
	// 	$('.layui-layer-title').css('color','#ffffff').css('background','#2fbdaa');
	
}


function change_user_mobile(url1,url2){


	// 	layer.open({
	// 		type: 1,
	// 		title:'请绑定手机后支付',
	// 		skin: 'layui-layer-demo', //加上边框
	// 		area: ['100%', '2.8rem'], //宽高
	// 		content: '<p class="form">手机号：<br><input name="mobile" id="mobile" type="text" /> <input type="button""  id="jq_send" value="获取验证码"/><br>验证码：<br><input  name="yzm" id="yzm" type="text" /> 输入验证码<br><input type="submit" value="立刻认证" id="go_mobile" /></p>'
	// 	});
	// //获取验证码
    //     var mobile_timeout;
    //     var mobile_count = 100;
    //     var mobile_lock = 0;
    //     $(function () {
    //         $("#jq_send").click(function () {
    //
    //             if (mobile_lock == 0) {
    //                 mobile_lock = 1;
    //                 $.ajax({
    //                     url: url1,
    //                     data: 'mobile=' + $("#mobile").val(),
    //                     type: 'post',
    //                     success: function (data) {
    //                         if (data.status == 'success') {
    //                             mobile_count = 60;
    //                             layer.msg(data.msg,{icon:1});
    //                             BtnCount();
    //                         } else {
    //                             mobile_lock = 0;
    //                             layer.msg(data.msg,{icon:2});
    //                         }
    //                     }
    //                 });
    //             }
    //
    //         });
    //     });
    //     BtnCount = function () {
    //         if (mobile_count == 0) {
    //             $('#jq_send').val("重新发送");
    //             mobile_lock = 0;
    //             clearTimeout(mobile_timeout);
    //         }
    //         else {
    //             mobile_count--;
    //             $('#jq_send').val("重新发送(" + mobile_count.toString() + ")秒");
    //             mobile_timeout = setTimeout(BtnCount, 1000);
    //         }
    //     };
	// 	//提交
	// 	$('#go_mobile').click(function(){
	// 		var ml = $('#mobile').val();
	// 		var y = $('#yzm').val();
	// 		$.post(url2,{mobile:ml,yzm:y},function(result){
	// 			if(result.status == 'success'){
	// 				layer.msg(result.msg,{icon:1});
	// 				setTimeout(function(){
	// 					location.reload(true);
	// 				},3000);
	// 			}else{
	// 				layer.msg(result.msg,{icon:2});
	// 			}
	// 		},'json');
	// 	})
	//
	//
	// 	$('.layui-layer-title').css('color','#ffffff').css('background','#2fbdaa');
	
}


//获取城市、地区、商圈的下拉菜单
function get_option(){

		var city_id = 0;
		var area_id = 0;
		var business_id = 0;
	
		var city_str = '<option value="0">请选择...</option>';
		for (a in cityareas.city) {
			if (city_id == cityareas.city[a].city_id) {
				city_str += '<option selected="selected" value="' + cityareas.city[a].city_id + '">' + cityareas.city[a].name + '</option>';
			} else {
				city_str += '<option value="' + cityareas.city[a].city_id + '">' + cityareas.city[a].name + '</option>';
			}
		}

		$("#city_id").html(city_str);

		$("#city_id").change(function () {
			if ($("#city_id").val() > 0) {
				city_id = $("#city_id").val();
				var area_str = ' <option value="0">请选择...</option>';
				for (a in cityareas.area) {
					if (cityareas.area[a].city_id == city_id) {
						if (area_id == cityareas.area[a].area_id) {
							area_str += '<option selected="selected" value="' + cityareas.area[a].area_id + '">' + cityareas.area[a].area_name + '</option>';
						} else {
							area_str += '<option value="' + cityareas.area[a].area_id + '">' + cityareas.area[a].area_name + '</option>';
						}
					}
				}
				$("#area_id").html(area_str);
				$("#business_id").html('<option value="0">请选择...</option>');
			} else {
				$("#area_id").html('<option value="0">请选择...</option>');
				$("#business_id").html('<option value="0">请选择...</option>');
			}

		});

		if (city_id > 0) {
			var area_str = ' <option value="0">请选择...</option>';
			for (a in cityareas.area) {
				if (cityareas.area[a].city_id == city_id) {
					if (area_id == cityareas.area[a].area_id) {
						area_str += '<option selected="selected" value="' + cityareas.area[a].area_id + '">' + cityareas.area[a].area_name + '</option>';
					} else {
						area_str += '<option value="' + cityareas.area[a].area_id + '">' + cityareas.area[a].area_name + '</option>';
					}
				}
			}
			$("#area_id").html(area_str);
		}


		$("#area_id").change(function () {
			if ($("#area_id").val() > 0) {
				area_id = $("#area_id").val();
				var business_str = ' <option value="0">请选择...</option>';
				for (a in cityareas.business) {
					if (cityareas.business[a].area_id == area_id) {
						if (business_id == cityareas.business[a].business_id) {
							business_str += '<option selected="selected" value="' + cityareas.business[a].business_id + '">' + cityareas.business[a].business_name + '</option>';
						} else {
							business_str += '<option value="' + cityareas.business[a].business_id + '">' + cityareas.business[a].business_name + '</option>';
						}
					}
				}
				$("#business_id").html(business_str);
			} else {
				$("#business_id").html('<option value="0">请选择...</option>');
			}

		});

		if (area_id > 0) {
			var business_str = ' <option value="0">请选择...</option>';
			for (a in cityareas.business) {
				if (cityareas.business[a].area_id == area_id) {
					if (business_id == cityareas.business[a].business_id) {
						business_str += '<option selected="selected" value="' + cityareas.business[a].business_id + '">' + cityareas.business[a].business_name + '</option>';
					} else {
						business_str += '<option value="' + cityareas.business[a].business_id + '">' + cityareas.business[a].business_name + '</option>';
					}
				}
			}
			$("#business_id").html(business_str);
		}
		$("#business_id").change(function () {
			business_id = $(this).val();
		});

}




function changeCAB(c,a,b){
            $("#city_ids").unbind('change');
            $("#area_ids").unbind('change');
            var city_ids = c;
            var area_ids = a;
            var business_ids = b;
            var city_str = ' <option value="0">请选择...</option>';
            for (b in cityareas.city) {
                if (city_ids == cityareas.city[b].city_id) {
                    city_str += '<option selected="selected" value="' + cityareas.city[b].city_id + '">' + cityareas.city[b].name + '</option>';
                } else {
                    city_str += '<option value="' + cityareas.city[b].city_id + '">' + cityareas.city[b].name + '</option>';
                }
            }
            $("#city_ids").html(city_str);

            $("#city_ids").change(function () {
                if ($("#city_ids").val() > 0) {
                    city_ids = $("#city_ids").val();
                    var area_str = ' <option value="0">请选择...</option>';
                    for (b in cityareas.area) {
                        if (cityareas.area[b].city_id == city_ids) {
                            if (area_ids == cityareas.area[b].area_id) {
                                area_str += '<option selected="selected" value="' + cityareas.area[b].area_id + '">' + cityareas.area[b].area_name + '</option>';
                            } else {
                                area_str += '<option value="' + cityareas.area[b].area_id + '">' + cityareas.area[b].area_name + '</option>';
                            }
                        }
                    }
               
                    $("#area_ids").html(area_str);
                    $("#business_ids").html('<option value="0">请选择...</option>');
                  
                    
                } else {
                    $("#area_ids").html('<option value="0">请选择...</option>');
                    $("#business_ids").html('<option value="0">请选择...</option>');
                }

            });

            if (city_ids > 0) {
                var area_str = ' <option value="0">请选择...</option>';
                for (b in cityareas.area) {
                    if (cityareas.area[b].city_id == city_ids) {
                        if (area_ids == cityareas.area[b].area_id) {
                            area_str += '<option selected="selected" value="' + cityareas.area[b].area_id + '">' + cityareas.area[b].area_name + '</option>';
                        } else {
                            area_str += '<option value="' + cityareas.area[b].area_id + '">' + cityareas.area[b].area_name + '</option>';
                        }
                    }
                }
                $("#area_ids").html(area_str);
            }


            $("#area_ids").change(function () {
                if ($("#area_ids").val() > 0) {
                    area_ids = $("#area_ids").val();
                    var business_str = ' <option value="0">请选择...</option>';
                    for (b in cityareas.business) {
                        if (cityareas.business[b].area_id == area_ids) {
                            if (business_ids == cityareas.business[b].business_id) {
                                business_str += '<option selected="selected" value="' + cityareas.business[b].business_id + '">' + cityareas.business[b].business_name + '</option>';
                            } else {
                                business_str += '<option value="' + cityareas.business[b].business_id + '">' + cityareas.business[b].business_name + '</option>';
                            }
                        }
                    }
                    $("#business_ids").html(business_str);
                } else {
                    $("#business_ids").html('<option value="0">请选择...</option>');
                }

            });

            if (area_ids > 0) {
                var business_str = ' <option value="0">请选择...</option>';
                for (b in cityareas.business) {
                    if (cityareas.business[b].area_id == area_ids) {
                        if (business_ids == cityareas.business[b].business_id) {
                            business_str += '<option selected="selected" value="' + cityareas.business[b].business_id + '">' + cityareas.business[b].business_name + '</option>';
                        } else {
                            business_str += '<option value="' + cityareas.business[b].business_id + '">' + cityareas.business[b].business_name + '</option>';
                        }
                    }
                }
                $("#business_ids").html(business_str);
            }
            $("#business_ids").change(function () {
                business_ids = $(this).val();
            });
        }
        
function get_night(stime,ltime){
    var  aDate,  oDate1,  oDate2,  iDays  
    aDate  =  stime.split("-")  
    oDate1  =  new  Date(aDate[1]  +  '-'  +  aDate[2]  +  '-'  +  aDate[0])    //转换为12-18-2006格式  
    aDate  =  ltime.split("-")  
    oDate2  =  new  Date(aDate[1]  +  '-'  +  aDate[2]  +  '-'  +  aDate[0])  
    iDays  =  parseInt(Math.abs(oDate1  -  oDate2)  /  1000  /  60  /  60  /24)    //把相差的毫秒数转换为天数  
    return  iDays  
}
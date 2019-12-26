/* 
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：尤哥
 * 官网：www.baocms.com
 * 邮件: 376621340@qq.com
 */
$.ajaxSetup({
    cache: false
});
var lock = 0;

function loading() {
    layer.msg('正在加载中...');
    //lock = 1;
}

function LoginSuccess() {
    $(".baodialog").remove();
    success('登录成功', 3000, "loginCallback()");
}

function loginCallback() {
    $.get(BAO_ROOT + "/index.php?m=passport&a=check&mt=" + Math.random(), function (data) {
        $(".topOne").find('.left').html(data);
    }, 'html');
    return true;
}

function ajaxLogin() {
    lock = 0;
    layer.closeAll();
    var boxHtml = '<div class="baodialog"></div>';
    if ($(".baodialog").length == 0) {
        $("body").append(boxHtml);
        $(".baodialog").css('height', document.body.scrollHeight + 'px');
    }
    var url = BAO_ROOT + '/index.php?g=pchome&m=passport&a=ajaxloging&t=' + Math.random();
    var width = document.body.clientWidth
    $.get(url, function (data) {

        $(".baodialog").html('<div class="baodialog_bg"></div>' + data);
        var left = (width - 616) / 2;
        var top = $(window).scrollTop() + 200;
        $(".loginPop").css({
            'left': left + 'px',
            'top': top + 'px'
        });

        $(".baodialog").show();
    }, 'html');

}

function success(msg, timeout, callback) {
    layer.msg(msg);
    setTimeout(function () {
        lock = 0;
        $(".baomsgbox").hide();
        eval(callback);
    }, timeout ? timeout : 3000);
}


function error(msg, timeout, callback) {
    layer.msg(msg);
    setTimeout(function () {
        lock = 0;
        eval(callback);
    }, timeout ? timeout : 3000);
}


function jumpUrl(url) {
    if (url) {
        location.href = url;
    } else {
        history.back(-1);
    }
}

function yzmCode() { //更换验证码
    $(".yzm_code").click();
}



//layer begin
function bmsg(msg, url, timeout, callback) { //信息,跳转地址,时间
    layer.msg(msg);
    if (url) {
        setTimeout(function () {
            window.location.href = url;
        }, timeout ? timeout : 3000);
    } else if (url === 0) {
        setTimeout(function () {
            location.reload(true);
        }, timeout ? timeout : 3000);
    } else {
        eval(callback);
    }

}

function bopen(msg, close, style) {
    layer.open({
        type: 1,
        skin: style, //样式类名
        closeBtn: close, //不显示关闭按钮
        shift: 2,
        shadeClose: true, //开启遮罩关闭
        content: msg
    });

}


//layer end

function dialog(title, content, width, height) {
    var dialogHtml = '<div class="dialogBox" title="' + title + '"></div>';
    if ($(".dialogBox").length == 0) {
        $("body").append(dialogHtml);
    }

    $(".dialogBox").attr('title', title);
    $(".dialogBox").html(content);
    $(".dialogBox").dialog({
        zIndex: 1000,
        width: width ? width : 300,
        height: height ? height : 200,
        modal: true
    });

}

var input_array = Array();
$(document).ready(function (e) {
    
    $(".tips").click(function () {
        var tipnr = $(this).attr('rel');
        layer.tips(tipnr, $(this), {
            tips: [4, '#1ca290'],
            time: 4000
        });
    })

    $(document).on('click', '.yzm_code', function () {
        $("#" + $(this).attr('rel')).attr('src', BAO_ROOT + '/index.php?g=app&m=verify&a=index&mt=' + Math.random());
    });

    $(document).on("click", "a[mini='act']", function (e) {
        e.preventDefault();
        if (!lock) {
            //loading();
            $("#baocms_frm").attr('src', $(this).attr('href'));
        }
    });

    $(document).on('click', "a[mini='confirm']", function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (!lock) {
            layer.confirm("您确定要" + $(this).html() + "吗？", {area: '150px', btn: ['是的', '不'], shade: false}, function () {
                $("#baocms_frm").attr('src', url);
            })
        }
    });

    $(document).on("click", "a[mini='buy']", function (e) { //购买的算法
        e.preventDefault();
        if (!lock) {
            loading();
            var url = $(this).attr('href');
            if (url.indexOf('?') > 0) {
                url += '&num=' + $('#' + $(this).attr('rel')).val();
            } else {
                url += '?num=' + $('#' + $(this).attr('rel')).val();
            }
            $("#baocms_frm").attr('src', url);
        }
    });

    $(document).on('click', "a[mini='list']", function (e) {
        e.preventDefault();
        if (!lock) {
            if (confirm("您确定要" + $(this).html())) {
                loading();
                $(this).parents('form').attr('action', $(this).attr('href')).submit();
            }
        }
    });

    $(document).on("click", "a[mini='tuan']", function (e) { //购买的算法
        e.preventDefault();
        if (!lock) {
            lock = 1;
            var url = $(this).attr('href');
            if (url.indexOf('?') > 0) {
                url += '&num=' + $('#' + $(this).attr('rel')).val();
            } else {
                url += '?num=' + $('#' + $(this).attr('rel')).val();
            }
            layer.msg("操作成功，正在跳转中...");
            setTimeout(function () {
                location.href = url;
            }, 2000)

        }
    });

    $(document).on("click", "a[mini='load']", function (e) {
        e.preventDefault();
        if (!lock) {
            loading();
            var obj = $(this);
            $.get(obj.attr('href'), function (data) {
                if (data == 0) {
                    ajaxLogin();
                } else {
                    dialog(obj.text(), data, obj.attr('w'), obj.attr('h'));

                }
                lock = 0;
                ;
            }, 'html');

        }
    });


    //全选
    $(document).on("click", ".checkAll", function (e) {
        var child = $(this).attr('rel');
        $(".child_" + child).prop('checked', $(this).prop("checked"));
    });


    $(document).on("click",".bao_closed",function(){
         $('.baodialog').hide();
    })



    $('.jq_star_show').each(function () {
        var val = $(this).attr('rel');
        var str = '';
        var num = parseInt(val / 10);
        var num2 = 5 - num;
        for (i = 0; i < num; i++) {
            str += '<img src="' + BAO_PUBLIC + '/images/star1.jpg"/>';
        }
        for (i = 0; i < num2; i++) {
            str += '<img src="' + BAO_PUBLIC + '/images/star2.jpg"/>';
        }
        $(this).html(str);
    });

    $(".jq_opacity_img img").mouseover(function () {
        $(this).stop().animate({
            opacity: '0.5'
        }, 300);
    }).mouseout(function () {
        $(this).stop().animate({
            opacity: '1'
        }, 300);
    });


    $(".jq_back_top").click(function (e) {
        var rel = $(this).attr('rel');
        rel = rel == undefined ? 200 : rel;
        $("html,body").animate({
            scrollTop: 0
        }, rel);
    });



});
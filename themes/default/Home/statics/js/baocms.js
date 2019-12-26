//幻灯片
(function ($) {
    $.fn.needui_slide = function (options) {
        //配置
        var defaults = {
            //宽度
            width: 300,
            //高度
            height: 300,
            //数据
            data: '',
            //触发幻灯片切换事件,mouseover、click
            action: 'mouseover',
            //是否开启自动切换
            auto: true,
            //自动切换间隔, 1-5秒
            time: 5
        }
        //合并配置
        var param = $.fn.extend(defaults, options || {});
        //对象
        var obj = $(this);
        //变量
        var title, //幻灯片标题
                checked, //是否是当前显示的图片
                alpha, //透明区域
                num_box;	//滑块区域
        //方法
        var _fn = {
            //执行
            _init_run: function () {
                var _html = '';
                //定义缩略图宽度
                var slide_height = param.height * 0.25 - 10;
                //判断是否有幻灯片
                if (param.data.length > 0)
                {
                    //默认显示第一张图片
                    _html += '<div class="needui_slide_box">';
                    _html += '<div class="needui_slide_pic_box">';
                    _html += '<img src="' + param.data[0].pic + '">';
                    _html += '</div>';
                    _html += '<div class="needui_slide_num_box"><ul style="width:' + (param.width + 10) + 'px; overflow:hidden;">';
                    var width = param.width / param.data.length - 9;
                    var style = 'style="height:' + (slide_height - 2) + 'px; width:' + width + 'px;"';
                    //遍历缩略图
                    for (i = 0; i < param.data.length; i++)
                    {
                        checked = i == 0 ? ' class="checked"' : '';
                        _html += '<li' + checked + ' ' + style + '></li>';
                    }
                    _html += '</ul></div></div>';
                }
                obj.html(_html);
                //定义宽度高度
                obj.children('.needui_slide_box').width(param.width)
                        .height(param.height)
                        .find('img')
                        .width(param.width)
                        .height(param.height * 0.75);
                //定义缩略图
                slide_box = obj.find('.needui_slide_num_box');
                //切换效果
                slide_box.height(slide_height).find('li').on(param.action, function () {
                    _fn._init_change($(this).index());
                });
                //自动切换
                if (param.auto)
                {
                    //获取当前显示的图片索引
                    _fn._init_play(slide_box);
                }
            },
            //自动播放
            _init_play: function (num_box) {
                param.time = (param.time > 5) ? 5000 : param.time * 1000;
                Timer = setInterval(_fn._init_change, param.time);
                //鼠标移入停止自动播放
                obj.children('.needui_slide_box').hover(function () {
                    clearInterval(Timer);
                }, function () {
                    Timer = setInterval(_fn._init_change, param.time);
                });
            },
            //切换图片、标题、滑块选中样式
            _init_change: function (index) {
                if (index == undefined)
                {
                    index = slide_box.find('.checked').index();
                    index = (index < param.data.length - 1) ? index + 1 : 0;
                }
                //重置当前滑块样式
                slide_box.find('li').eq(index).addClass('checked').siblings('.checked').removeAttr('class');
                //切换图片
                obj.find('.needui_slide_pic_box img')
                        .attr('src', param.data[index].pic);
            }
        }
        _fn._init_run();
    }
})(jQuery);
/* 锚点跳转 */
(function ($) {
    $.fn.anchor = function () {
        var obj = $(this);
        var top = obj.offset().top;
        var clone;
        var top_1 = $('#position_site').offset().top;
        var top_2 = $('#position_introduce').offset().top - 50;
        var top_3 = $('#position_food').offset().top - 50;
        var top_4 = $('#position_place').offset().top - 50;
        var top_5 = $('#position_guest').offset().top - 50;
        var _fn = {
            _init_run: function () {
                obj.find('li').click(function () {
                    var anc = $(this).attr('anchor');
                    $("html,body").animate({
                        scrollTop: $('#' + anc).offset().top - 40
                    }, 800, function () {
                        _fn._init_jump();
                    });
                });
                $(document).scroll(function () {
                    if ($(document).scrollTop() >= top)
                    {
                        _fn._init_jump();
                        if ($(document).scrollTop() >= top_1)
                        {
                            obj.find('li').removeClass('active').eq(0).addClass('active');
                        }
                        if ($(document).scrollTop() >= top_2)
                        {
                            obj.find('li').removeClass('active').eq(1).addClass('active');
                        }
                        if ($(document).scrollTop() >= top_3)
                        {
                            obj.find('li').removeClass('active').eq(2).addClass('active');
                        }
                        if ($(document).scrollTop() >= top_4)
                        {
                            obj.find('li').removeClass('active').eq(3).addClass('active');
                        }
                        if ($(document).scrollTop() >= top_5)
                        {
                            obj.find('li').removeClass('active').eq(4).addClass('active');
                        }
                    }
                    else
                    {
                        if (clone != undefined)
                        {
                            obj.removeAttr('style');
                            clone.remove();
                        }
                    }
                });
            },
            _init_jump: function () {
                if ($('#clone').length == 0)
                {
                    clone = obj.clone();
                    clone.attr('id', 'clone');
                    obj.after(clone).css({
                        'position': 'fixed',
                        'top': 0,
                        'margin-top': 0,
                        'border-bottom': '1px #DBDBDB solid',
                        'z-index': 999
                    })
                            .animate({'width': '1200px'}, 200);
                }
            }
        }
        _fn._init_run();
    }
})(jQuery);
/* 重置单选复选  */
$.extend({
    reset_input: function (top, func) {
        top = (top == undefined) ? 0 : top;
        $("input[type='radio'],input[type='checkbox']").each(function () {
            if ($(this).attr('type') == 'radio')
            {
                $(this).parent().addClass('radio');
                if ($(this).is(':checked'))
                    $(this).parent().addClass('radio-active');
            }
            else
            {
                $(this).parent().addClass('checkbox');
                if ($(this).is(':checked'))
                    $(this).parent().addClass('checkbox-active');
            }
        });
        $("input[type='radio']").click(function () {
            if ($(this).parent().attr('class') != 'radio-disabled')
                $(this).parent().addClass('radio-active').siblings().removeClass('radio-active');
        });
        $("input[type='checkbox']").click(function () {
            if ($(this).is(':checked'))
            {
                $(this).parent().addClass('checkbox-active');
            }
            else
            {
                $(this).parent().removeClass('checkbox-active');
            }
        });
        $('.baocms_select').each(function () {
            var _option_html = '<div id="baocms_select' + $(this).index() + '"';
            _option_html += 'style="width:' + ($(this).outerWidth() - 1) + 'px;';
            _option_html += 'top:' + ($(this).offset().top + $(this).outerHeight() + top) + 'px;';
            _option_html += 'left:' + ($(this).offset().left - 1) + 'px;"';
            _option_html += ' class="baocms-select-option"><ul>';
            var ops = $(this).attr('option').split('|');
            var val = $(this).attr('value').split('|');
            for (i = 0; i < ops.length; i++)
            {
                _option_html += '<li value="' + val[i] + '">' + ops[i] + '</li>';
            }
            _option_html += '</ul></div>';
            $('body').append(_option_html);
        });
        $('.baocms_select').click(function () {
            var obj = $(this);
            var id = obj.attr('id');
            obj.attr('tabindex', 0).focus();
            var option = $('#baocms_select' + $(this).index());
            if (option.is(':hidden'))
            {
                option.show();
            }
            else
            {
                option.hide();
            }
            option.find('li').off('click').on('click', function () {
                obj.children('label').html($(this).html());
                obj.children('input').val($(this).attr('value'));
                option.hide();
                if (func != undefined)
                {
                    func(id, $(this).attr('value'));
                }
            });
        }).blur(function () {
            var obj = $(this);
            setTimeout(function () {
                $('#baocms_select' + obj.index()).hide();
            }, 150);
        });
    }
});
/* 弹窗 */
$.extend({
    _alert: function (msg, callback) {
        if ($('._alert').length == 0)
        {
            var _html = '<div class="_alert">';
            _html += '<div class="title">提示信息<i></i></div>';
            _html += '<div class="box">';
            _html += '<div class="msg">' + msg + '</div>';
            _html += '<div class="btnbar">';
            _html += '<div class="btn">确定</div>';
            _html += '</div></div></div>';
            if ($('#shade').length == 0)
            {
                _html += '<div id="shade" style="height:' + _inner_h() + 'px;"></div>';
            }
            else
            {
                $('#shade').show();
            }
            $('body').append(_html);
        }
        else
        {
            $('#shade').show();
            $('._alert .msg').html(msg);
            $('._alert').show();
        }
        var obj = $('._alert');
        var _close = obj.find('.title i');
        var _btn = obj.find('.btn');
        _close.off().on('click', function () {
            $('#shade').hide();
            obj.hide();
            return;
        });
        _btn.off().on('click', function () {
            $('#shade').hide();
            obj.hide();
            if (callback != undefined)
            {
                callback();
                return;
            }
        });
    },
    _confirm: function (option) {
        if ($.cookie(option.confirm_id, '') == 1)
        {
            if (option.no != undefined)
            {
                option.no();
                return;
            }
        }
        if ($('._confirm').length == 0)
        {
            var _html = '<div class="_confirm">';
            _html += '<div class="title">提示信息<i></i></div>';
            _html += '<div class="box">';
            _html += '<div class="msg">' + option.msg + '</div>';
            _html += '<div class="btnbar">';
            _html += '<label><input type="radio" name="do" value="1" checked="checked">立刻点菜</label>';
            _html += '<label><input type="radio" name="do" value="2">否</label>';
            _html += '<label><input type="radio" name="do" value="3">不再提示</label>';
            _html += '<span class="btn">确定</span>';
            _html += '</div></div></div>';
            if ($('#shade').length == 0)
            {
                _html += '<div id="shade" style="height:' + _inner_h() + 'px;"></div>';
            }
            else
            {
                $('#shade').show();
            }
            $('body').append(_html);
        }
        else
        {
            $('#shade').show();
            $('._confirm').show();
        }
        var obj = $('._confirm');
        var _close = obj.find('.title i');
        var _btn = obj.find('.btn');
        _close.off().on('click', function () {
            $('#shade').hide();
            obj.hide();
            return;
        });
        _btn.off().on('click', function () {
            $('#shade').hide();
            obj.hide();
            var _val = obj.find('input[name="do"]:checked').val();
            if (_val == 1)
            {
                if (option.yes != undefined)
                    option.yes();
            }
            else if (_val == 2)
            {
                if (option.no != undefined)
                    option.no();
            }
            else
            {
                $.cookie(option.confirm_id, 1, 30);
                if (option.no != undefined)
                    option.no();
            }
            return;
        });
    }
});
/* 点菜 */
$.extend({
    food: function (obj, form) {
        var cart;
        if ($('#cart').length == 0)
        {
            var _html = '<div id="cart" class="cart">';
            _html += '<div class="title">电脑下单不享优惠了哦，优惠手机下单专享</div>';
            _html += '<div class="box">';
            _html += '<table width="100%">';
            _html += '<tr class="tit">';
            _html += '<td width="60%" class="food">菜名</td>';
            _html += '<td width="20%">份数</td>';
            _html += '<td width="20%">价格</td>';
            _html += '</tr></table>';
            _html += '<div class="center"><table width="100%" id="food_list"></table></div>';
            _html += '<div class="count">共 <span class="c" id="food_count">0</span> 份,总计<span class="c price">￥</span><span class="c price" id="food_money">0</span></div>';
            _html += '</div>';
            _html += '<div class="btn"><input type="submit" value="立即订座" /></div></div>';
            $('#' + form).append(_html);
            cart = $('#cart');
        }
        else
        {
            cart = $('#cart');
            if (cart.is(':hidden'))
                cart.show().animate({'right': '20px'}, 200);
        }
        var li = obj.parent().parent().parent().parent();
        var table = $('#food_list')
        var count = $('#food_count');
        var money = $('#food_money');
        var id = obj.attr('id');
        if (obj.is(':checked'))
        {
            li.addClass('active');
            if ($('#seat_check_food' + id).length == 0)
            {
                var tr = '<tr id="seat_check_food' + id + '">';
                tr += '<input type="hidden" name="product_name[]" value="' + obj.attr('food') + '">';
                tr += '<input type="hidden" name="product_num[]" value="1">';
                tr += '<input type="hidden" name="product_id[]" value="' + id + '">';
                tr += '<td width="60%" class="food">' + obj.attr('food') + '</td>';
                tr += '<td width="20%" class="numinput">';
                tr += '<div class="cut" food_id="' + id + '">-</div>';
                tr += '<div><input type="text" value="1"></div>';
                tr += '<div class="add">+</div>';
                tr += '</td>';
                tr += '<td width="20%">' + obj.attr('price') + '</td>';
                tr += '</tr>';
                table.prepend(tr);
                count.html(parseInt(count.html()) + 1);
                var m = parseFloat(money.html()) + parseFloat(obj.attr('price'));
                money.html(m.toFixed(2));
            }
        }
        else
        {
            li.removeClass('active');
            var tr = $('#seat_check_food' + id);
            var num = tr.find("input[type='text']").val();
            var price = tr.children('td:last').html();
            price = (parseInt(num) * parseFloat(price)).toFixed(2);
            money.html((parseFloat(money.html()) - price).toFixed(2));
            var now_count = parseInt(count.html()) - num;
            count.html(now_count);
            tr.remove();
            if (now_count == 0)
                cart.animate({'right': '-300px'}, 200, function () {
                    cart.hide()
                });
        }
        $('#cart_num').html(table.find('tr').length);
        var input;
        cart.find('.add,.cut').off().on('click', function () {
            var food_price = parseFloat($(this).parent().next().html());
            if ($(this).attr('class') == 'add')
            {
                input = $(this).prev().children("input[type='text']");
                input.val(parseInt(input.val()) + 1);
                $(this).parent().parent().children("input[type='hidden']").eq(1).val(input.val());
                count.html(parseInt(count.html()) + 1);
                money.html((parseFloat(money.html()) + food_price).toFixed(2));
            }
            else
            {
                var now_count = parseInt(count.html()) - 1;
                count.html(now_count);
                money.html((parseFloat(money.html()) - food_price).toFixed(2));
                input = $(this).next().children("input[type='text']");
                var now_num = parseInt(input.val()) - 1;
                if (now_num == 0)
                {
                    $(this).parent().parent().remove();
                    var food_id = $(this).attr('food_id');
                    var checkbox = $('#' + food_id);
                    checkbox.attr('checked', false).parent().removeClass('checkbox-active');
                    checkbox.parent().parent().parent().parent().removeClass('active');
                    $('#cart_num').html(table.find('tr').length);
                }
                else
                {
                    input.val(now_num);
                    $(this).parent().parent().children("input[type='hidden']").eq(1).val(now_num);
                }
                if (now_count == 0)
                    cart.animate({'right': '-300px'}, 200, function () {
                        cart.hide()
                    });
            }
        });
    }
});
/* 日期选择 */
(function ($) {
    $.fn.needui_datepicker = function (start_day, use) {
        //用到的变量
        var _html = '<table class="needui_datepicker">';
        _html += '<tr>';
        _html += '<td colspan="7" class="toolbar">';
        _html += '<span class="prev_year" title="上一年"><<</span>';
        _html += '<span class="prev_month" title="上一个月"><</span>';
        var date = new Date();
        var obj, y, m, d, input = $(this);
        //方法
        var _fn = {
            _init_run: function () {
                $('body').append('<div id="' + input.attr('id') + '_needui_datepicker" class="needui_datepicker_box"></div>');
                obj = $('#' + input.attr('id') + '_needui_datepicker');
                //获取当前日期
                y = date.getFullYear();
                m = date.getMonth();
                var show_m = parseInt(m + 1);
                if (show_m < 10)
                    show_m = '0' + show_m;
                d = date.getDate();
                if (d < 10)
                    d = '0' + d;
                _html += '<span class="y">' + y + '</span>/<span class="m">' + show_m + '</span>/' + d;
                _html += '<span class="next_month" title="下一个月">></span>';
                _html += '<span class="next_year" title="下一年">>></span></td></tr>';
                _html += '<tr class="day">';
                _html += '<td>日</td>';
                _html += '<td>一</td>';
                _html += '<td>二</td>';
                _html += '<td>三</td>';
                _html += '<td>四</td>';
                _html += '<td>五</td>';
                _html += '<td>六</td></tr>';
                _html += '<tbody class="date">';
                _html += _fn._init_create_html(y, m, d);
                _html += '</tbody>';
                _html += '<tr><td colspan="7">X 点击关闭日期选择';
                _html += '</td></tr></table>';
                obj.html(_html);
                //获取文本框的实际宽度，加上padding
                var input_real_width = (use == 'content') ? 200 : input.outerWidth() - 4;
                //obj.children('table').css('border-top',0);
                //设置容器的样式
                if (use == 'content')
                    obj.css({'display': 'none', 'position': 'absolute', 'top': input.offset().top + input.outerHeight() + 10, 'left': input.offset().left - 1, 'z-index': 999});
                else
                    obj.css({'display': 'none', 'position': 'absolute', 'top': input.offset().top + input.outerHeight(), 'left': input.offset().left, 'z-index': 999});
                //设置表格宽度
                obj.children('table').width(input_real_width + 2);
                //文本框获取焦点后则显示日期选择器
                input.click(function () {
                    $('.needui_datepicker_box').hide();
                    obj.show();
                });
                //选择日期事件
                obj.find('.date').on('click', 'td', function () {
                    if ($(this).attr('class') != 'disabled')
                    {
                        var week = obj.find('tbody').eq(0).children('.day').children('td').eq($(this).index()).html();
                        input.children('label').html(obj.find('.m').html() + '-' + $(this).html() + ' 周' + week);
                        input.children('input').val(obj.find('.y').html() + obj.find('.m').html() + $(this).html());
                        obj.hide();
                    }
                });
                obj.find('.toolbar').hover(function () {
                    //禁止选择文本
                    document.onselectstart = function () {
                        return false;
                    }
                    $('body').css('-moz-user-select', 'none'); //兼容火狐
                }, function () {
                    //允许选择文本
                    document.onselectstart = function () {
                        return true;
                    }
                    $('body').css('-moz-user-select', ''); //兼容火狐	
                });
            },
            //根据年月生成HTML
            _init_create_html: function (y, m, d) {
                var return_html = '';
                //获取当月第一天是星期几
                var first_day = new Date(y, m, 1);
                var fd = first_day.getDay();
                //获取当月天数
                var month_day_num = _fn._init_get_month_day(y, m);
                //获取上个月的天数
                var prev_month_day_num = _fn._init_get_month_day(y, m - 1);
                //实际的天数
                var real_day;
                //td的样式，用于控制不能选中的日期
                var class_name;
                //循环天数
                for (i = 0; i < 42; i++)
                {
                    class_name = '';
                    if (i == 0)
                    {
                        return_html += '<tr>';
                    }
                    else
                    {
                        if (i % 7 == 0)
                            return_html += '</tr><tr>';
                    }
                    //如果i小于第一天的天数则用上个月的天数补足
                    if (i < fd)
                    {
                        //因为i从0开始，所有最后要加1
                        real_day = prev_month_day_num - (fd - i) + 1;
                        class_name = ' class="disabled"';
                    }
                    if (i == fd)
                    {
                        real_day = 1;
                        class_name = '';
                    }
                    if (i - fd + 1 == d)
                    {
                        class_name = ' class="checked"';
                    }
                    var show_day = y.toString();
                    if (m + 1 < 10)
                    {
                        show_day += '0' + (m + 1).toString();
                    }
                    else
                    {
                        show_day += (m + 1).toString();
                    }
                    if (i < 10)
                    {
                        show_day += '0' + i.toString();
                    }
                    else
                    {
                        show_day += i.toString();
                    }
                    if (parseInt(show_day) - parseInt(fd) + 1 < start_day)
                    {
                        class_name = ' class="disabled"';
                    }
                    if (i - fd + 1 > month_day_num)
                    {
                        if (i - fd - month_day_num == 0)
                            real_day = 1;
                        class_name = ' class="disabled"';
                    }
                    if (real_day < 10)
                        real_day = '0' + real_day;
                    return_html += '<td' + class_name + '>' + real_day + '</td>';
                    real_day++;
                }
                return return_html;
            },
            //获取当前月份的天数
            _init_get_month_day: function (y, m) {
                m = (m < 0) ? 11 : m;
                //判断当前年是不是闰年
                var Feb = (y % 4 == 0 && y % 100 != 0 || y % 400 == 0) ? 29 : 28;
                var month_day = new Array();
                month_day[0] = 31;
                month_day[1] = Feb;
                month_day[2] = 31;
                month_day[3] = 30;
                month_day[4] = 31;
                month_day[5] = 30;
                month_day[6] = 31;
                month_day[7] = 31;
                month_day[8] = 30;
                month_day[9] = 31;
                month_day[10] = 30;
                month_day[11] = 31;
                return month_day[m];
            },
            //移入效果
            _init_mouseover: function () {
                obj.find('.date').on('mouseover', 'td', function () {
                    if ($(this).attr('class') != 'disabled')
                        $(this).addClass('hover');
                }).on('mouseout', 'td', function () {
                    if ($(this).attr('class') != 'disabled')
                        $(this).removeClass('hover');
                });
            },
            //日期跳转
            _init_jump_date: function () {
                //上一个月
                obj.find('.prev_month').on('click', function () {
                    _fn._init_date_result('prev', 'm');
                });
                //下一个月
                obj.find('.next_month').on('click', function () {
                    _fn._init_date_result('next', 'm');
                });
                //上一年
                obj.find('.prev_year').on('click', function () {
                    _fn._init_date_result('prev', 'y');
                });
                //下一年
                obj.find('.next_year').on('click', function () {
                    _fn._init_date_result('next', 'y');
                });
            },
            //计算上一个月或者下一个月，上一年下一年的结果
            _init_date_result: function (action, type) {
                var m_box = obj.find('.m');
                var y_box = obj.find('.y');
                //获取当前月份
                var now_month = parseInt(m_box.html());
                //获取当前年费
                var now_year = parseInt(y_box.html());
                //计算月份
                if (type == 'm')
                {
                    //向上
                    if (action == 'prev')
                    {
                        var prev_month = now_month - 1;
                        if (prev_month == 0)
                        {
                            y_box.html(now_year - 1);
                            prev_month = 12;
                        }
                        if (prev_month < 10)
                            prev_month = '0' + prev_month;
                        m_box.html(prev_month);
                    }
                    //向下
                    else
                    {
                        var next_month = now_month + 1;
                        if (next_month > 12)
                        {
                            y_box.html(now_year + 1);
                            next_month = 1;
                        }
                        if (next_month < 10)
                            next_month = '0' + next_month;
                        m_box.html(next_month);
                    }
                }
                //计算年份
                else
                {
                    //向上
                    if (action == 'prev')
                    {
                        y_box.html(now_year - 1);
                    }
                    //向下
                    else
                    {
                        y_box.html(now_year + 1);
                    }
                }
                obj.find('.date').html(_fn._init_create_html(parseInt(y_box.html()), parseInt(m_box.html()) - 1, d));
            },
            //关闭
            _init_close: function () {
                obj.find('tr:last').on('click', function (event) {
                    event.stopPropagation();
                    obj.hide();
                });
            }
        }
        _fn._init_run();
        _fn._init_mouseover();
        _fn._init_jump_date();
        _fn._init_close();
    }
})(jQuery);
/* COOKIE操作 */
$.extend({
    cookie: function (name, val, expires) {
        //读取cookie
        if (val == '')
        {
            //COOKIE开始位置
            var S = document.cookie.indexOf(name);
            //COOKIE结束位置
            var E = document.cookie.indexOf(';', S);
            //长度
            var L = document.cookie.length;
            //返回COOKIE
            return S == -1 ? '' : unescape(document.cookie.substring(S + name.length + 1, (E > S ? E : L)));
        }
        //删除cookie
        else if (val == null)
        {
            //获取当前时间
            var D = new Date();
            //设置COOKIE过期时间
            D.setTime(D.getTime() - 1);
            //设置COOKIE
            document.cookie = escape(name) + '=' + escape(val) +
                    (D ? '; expires=' + D.toGMTString() : '');
        }
        //设置cookie
        else
        {
            //获取当前时间
            var D = new Date();
            //设置COOKIE过期时间
            D.setTime(D.getTime() + expires * 24 * 60 * 60 * 1000);
            //设置COOKIE
            document.cookie = escape(name) + '=' + escape(val) +
                    (D ? '; expires=' + D.toGMTString() : '');
        }
    }
});
//兼容浏览器获取innerHeight
function _inner_h() {
    if (window.innerHeight)
    {
        return window.innerHeight;
    }
    else if (document.documentElement.clientHeight)
    {
        return document.documentElement.clientHeight;
    }
    else if (document.body.clientHeight)
    {
        return document.body.clientHeight;
    }
}

/*js分页*/

function setpage()
{
    if (totalpage <= 5) {        //总页数小于十页 
        for (count = 1; count <= totalpage; count++)
        {
            if (count != cpage)
            {
                outstr = outstr + "<a href='javascript:void(0)' onclick='gotopage(" + count + ")'>" + count + "</a>";
            } else {
                outstr = outstr + "<span class='current' >" + count + "</span>";
            }
        }
    }
    if (totalpage > 5) {        //总页数大于十页 
        if (parseInt((cpage - 1) / 5) == 0)
        {
            for (count = 1; count <= 5; count++)
            {
                if (count != cpage)
                {
                    outstr = outstr + "<a href='javascript:void(0)' onclick='gotopage(" + count + ")'>" + count + "</a>";
                } else {
                    outstr = outstr + "<span class='current'>" + count + "</span>";
                }
            }
            outstr = outstr + "<a href='javascript:void(0)' onclick='gotopage(" + count + ")'> 下一页 </a>";
        }
        else if (parseInt((cpage - 1) / 5) == parseInt(totalpage / 5))
        {
            outstr = outstr + "<a href='javascript:void(0)' onclick='gotopage(" + (parseInt((cpage - 1) / 5) * 5) + ")'>上一页</a>";
            for (count = parseInt(totalpage / 5) * 5 + 1; count <= totalpage; count++)
            {
                if (count != cpage)
                {
                    outstr = outstr + "<a href='javascript:void(0)' onclick='gotopage(" + count + ")'>" + count + "</a>";
                } else {
                    outstr = outstr + "<span class='current'>" + count + "</span>";
                }
            }
        }
        else
        {
            outstr = outstr + "<a href='javascript:void(0)' onclick='gotopage(" + (parseInt((cpage - 1) / 5) * 5) + ")'>上一页</a>";
            for (count = parseInt((cpage - 1) / 5) * 5 + 1; count <= parseInt((cpage - 1) / 5) * 5 + 5; count++)
            {
                if (count != cpage)
                {
                    outstr = outstr + "<a href='javascript:void(0)' onclick='gotopage(" + count + ")'>" + count + "</a>";
                } else {
                    outstr = outstr + "<span class='current'>" + count + "</span>";
                }
            }
            outstr = outstr + "<a href='javascript:void(0)' onclick='gotopage(" + count + ")'> 下一页 </a>";
        }
    }
    if(totalpage > 5){
        document.getElementById("setpage").innerHTML = "<div class='x'><span>" + total + "&nbsp;&nbsp;条记录&nbsp;&nbsp;" + cpage + "/" + totalpage + "页<\/span>" + outstr + "<\/div>";
    }
    outstr = "";
}




function check_user_mobile_for_pc(url1, url2) {
    // layer.open({
    //     type: 1,
    //     title: '请绑定手机后操作',
    //     skin: 'layui-layer-demo', //加上边框
    //     area: ['450px', '280px'], //宽高
    //     content: '<div class="add-message"><p><span>*</span> 手机号：<input type="text" id="mobile" name="mobile" class="add-text add_mobile"><input class="send_button" type="button" id="jq_send" value="获取验证码"/></p><p><span>*</span> 验证码：<input type="text" id="yzm" name="yzm" class="add-text add_yzm">请输入手机获取的验证码</p></div> <div class="add-button"><input type="submit" id="go_mobile" class="add-hold" value="立刻认证"/></div>',
    // });
    //
    //
    // //获取验证码
    // var mobile_timeout;
    // var mobile_count = 100;
    // var mobile_lock = 0;
    // $(function () {
    //     $("#jq_send").click(function () {
    //
    //         if (mobile_lock == 0) {
    //             mobile_lock = 1;
    //             $.post(url1, {mobile: $("#mobile").val()}, function (data) {
    //                 if (data.status == 'success') {
    //                     //alert(data.code);
    //                     mobile_count = 60;
    //                     layer.msg(data.msg,{icon: 1});
    //                     BtnCount();
    //                 } else {
    //                     mobile_lock = 0;
    //                     layer.msg(data.msg,{icon: 2});
    //                 }
    //             }, 'json');
    //         }
    //
    //     });
    // });
    // BtnCount = function () {
    //     if (mobile_count == 0) {
    //         $('#jq_send').val("重新发送");
    //         mobile_lock = 0;
    //         clearTimeout(mobile_timeout);
    //     }
    //     else {
    //         mobile_count--;
    //         $('#jq_send').val("重新发送(" + mobile_count.toString() + ")秒");
    //         mobile_timeout = setTimeout(BtnCount, 1000);
    //     }
    // };
    // //提交
    // $('#go_mobile').click(function () {
    //     var ml = $('#mobile').val();
    //     var y = $('#yzm').val();
    //     $.post(url2, {mobile: ml, yzm: y}, function (result) {
    //         if (result.status == 'success') {
    //             layer.msg(result.msg);
    //             setTimeout(function () {
    //                 location.reload(true);
    //             }, 3000);
    //         } else {
    //             layer.msg(result.msg, {icon: 2});
    //         }
    //     }, 'json');
    // })
    //
    //
    // $('.layui-layer-title').css('color', '#ffffff').css('background', '#2fbdaa');

}


function change_user_mobile_for_pc(url1, url2) {
    layer.open({
        type: 1,
        title: '更换绑定手机号',
        skin: 'layui-layer-demo', //加上边框
        area: ['450px', '280px'], //宽高
        content: '<div class="add-message"><p><span>*</span> 手机号：<input type="text" id="mobile" name="mobile" class="add-text add_mobile"><input class="send_button" type="button" id="jq_send" value="获取验证码"/></p><p><span>*</span> 验证码：<input type="text" id="yzm" name="yzm" class="add-text add_yzm">请输入手机获取的验证码</p></div> <div class="add-button"><input type="submit" id="go_mobile" class="add-hold" value="立刻认证"/></div>',
    });


    //获取验证码
    var mobile_timeout;
    var mobile_count = 100;
    var mobile_lock = 0;
    $(function () {
        $("#jq_send").click(function () {

            if (mobile_lock == 0) {
                mobile_lock = 1;
                $.post(url1, {mobile: $("#mobile").val()}, function (data) {
                    if (data.status == 'success') {
                        mobile_count = 60;
                        layer.msg(data.msg,{icon: 1});
                        BtnCount();
                    } else {
                        mobile_lock = 0;
                        layer.msg(data.msg,{icon: 2});
                    }
                }, 'json');
            }

        });
    });
    BtnCount = function () {
        if (mobile_count == 0) {
            $('#jq_send').val("重新发送");
            mobile_lock = 0;
            clearTimeout(mobile_timeout);
        }
        else {
            mobile_count--;
            $('#jq_send').val("重新发送(" + mobile_count.toString() + ")秒");
            mobile_timeout = setTimeout(BtnCount, 1000);
        }
    };
    //提交
    $('#go_mobile').click(function () {
        ml = $('#mobile').val();
        y = $('#yzm').val();
        $.post(url2, {mobile: ml, yzm: y}, function (result) {
            if (result.status == 'success') {
                layer.msg(result.msg,{icon: 1});
                setTimeout(function () {
                    location.reload(true);
                }, 3000);
            } else {
                layer.msg(result.msg, {icon: 2});
            }
        }, 'json');
    })


    $('.layui-layer-title').css('color', '#ffffff').css('background', '#2fbdaa');

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
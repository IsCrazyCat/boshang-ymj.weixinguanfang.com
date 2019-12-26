/**
 * jQuery插件 展示类似淘宝的那种多城市选择
 * @param {type} $
 * @returns {Function|window.$.fn.areapanel|$.fn.areapanel|jquery_areapanel_L4.$.fn.areapanel}
 */
(function($) {  
  // 插件的定义  
  return $.fn.cityselect = function(options,callback) {
    // 插件的defaults
    $.fn.cityselect.defaults = {
        index :{
            'ABCDE':{field:'ABCDE',value:['A','B','C','D','E']},
            'FGHJ':{field:'FGHJ',value:['F','G','H','J']},
            'KLMNP':{field:'KLMNP',value:['K','L','M','N','P']},
            'QRSTW':{field:'QRSTW',value:['Q','R','S','T','W']},
            'XYZ':{field:'XYZ',value:['X','Y','Z']}
        }
    }
    var opts = $.extend({}, $.fn.cityselect.defaults, options);
    $(this).each(function(){
        var div = create();
        callback.call(this,div);
    })
    function create(){
        var select = $('<div class="cityselect"></div>');
        if(typeof(Area) === 'object'){
            var hot_area = create_hot_area();
            var area = create_area();
            select.append(hot_area);
            select.append(area);
        }else{
            debug('缺少Area')
        }
        return select;
    }
    function create_hot_area(){
        var dl = $('<dl class="cityselect-hot"><dt>'+AreaHot.name+'</dt></dl>');
        var dd = $('<dd></dd>');
        dl.append(dd);
        $.each(AreaHot.group,function(){
            var a = $('<a></a>').html(this.title).data('city_id',this.id);
            dd.append(a);
            a.on('click',{city_id:this.id},selected);
        })
        return dl;
    }
    function create_area(){
        var cities = $('<div><div>');
        var tab = $('<ul class="nav nav-tabs nav-justified cityselect-tabs"></ul>')
        cities.append(tab);
        var j = 0;
        $.each(opts.index,function(i){
                var li = $('<li></li>');
            var a = $('<a href="javascript:;" rel="nofollow"></a>').html(this.field);
            li.append(a);
            li.on('mouseover click',{},toggle_tabs)
            
            var areas = create_areas(this.value);
            cities.append(areas);
            tab.append(li);
            if(j == 0){
                li.addClass('active')
                areas.addClass('active')
            }
            j++;
        })
        return cities;
    }
    function create_areas(areas){
        var div = $('<div class="cityselect-areas"></div>');
        $.each(areas,function(){
            var result = find_city_by_word(this);
            var dl = $('<dl><dt>'+this+'</dt></dl>');
            var dd = $('<dd></dd>');
            $.each(result,function(){
                var a = $('<a></a>').html(this.name).data('city_id',this.id);
                a.on('click',{city_id:this.id},selected);
                dd.append(a);
            })
            dl.append(dd)
            div.append(dl);
        })
        return div;
    }
    function find_city_by_word(word){
        var result = [];
        $.each(Area,function(i){
            if(this.word == word && this.type == 3){
                result.push(this)
            }
        })
        return result;
    }
    function selected(params){
        var city_id = params.data.city_id;
        set_city(city_id);
    }
    
    function set_city(city_id){
        $.cookie('local',city_id, { expires: 30, path: '/',domain:SITE_DOMAIN});
        window.location.reload();
    }
    
    function toggle_tabs(params){
        var _this = $(this);
        var index = _this.index();
        if(params.type == 'mouseover'){
            _this.siblings('li').removeClass('active').end().addClass('active');
            _this.parent().siblings('.cityselect-areas').removeClass('active').eq(index).addClass('active');
        }
    }
    
}})(jQuery)

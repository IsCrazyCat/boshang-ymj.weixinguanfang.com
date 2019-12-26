(function($){
	$.fn.citypicker = function(options){
		//配置
		var defaults = {
			level : 1,
			city_name : 'city',
			area_name : 'area',
			business_name : 'business',
			city_id   : 0,
			area_id   : 0,
			business_id : 0
		};
		//合并配置参数
		var param = $.fn.extend(defaults,options||{});
		//对象
		var obj = $(this);
		//方法
		var _fn = {
			_init_run : function(){
				if (param.level > 3)
				{
					alert('城市选择器目前只支持三级');
					return false;
				}
				if (param.level < 1)
				{
					param.level = 1;	
				}
				var _html = '';
				for(i = 1; i <= param.level; i++)
				{
					if (i == 1)
					{
						_html += '<select class="selects" style="margin-right:5px;" name="'+param.city_name+'">';
						_html += '<option value="0">选择城市</option>';
						var selected = '';
						$.each(city_data, function(i, item){
							selected = (item.city_id == param.city_id) ? ' selected="selected"' : '';
							_html += '<option value="'+item.city_id+'"'+selected+'>'+item.name+'</option>';
						});
						_html += '</select>';
					}
					else if (i == 2)
					{
						_html += '<select class="selects" style="margin-right:5px;" name="'+param.area_name+'">';
						if (param.city_id > 0)
						{
							var data = city_data[param.city_id].area;
							var selected = '';
							$.each(data, function(i, item){
							selected = (item.area_id == param.area_id) ? ' selected="selected"' : '';
							_html += '<option value="'+item.area_id+'"'+selected+'>'+item.area_name+'</option>';
							});
						}
						else
						{
							_html += '<option value="0">选择区域</option>';
						}
						_html += '</select>';
					}
					else
					{
						_html += '<select class="selects" name="'+param.business_name+'">';
						if (param.area_id > 0)
						{
							var data = city_data[param.city_id].area[param.area_id].business;
							var selected = '';
							$.each(data, function(i, item){
							selected = (item.business_id == param.business_id) ? ' selected="selected"' : '';
							_html += '<option value="'+item.business_id+'"'+selected+'>'+item.business_name+'</option>';
							});
						}
						else
						{
							_html += '<option value="0">选择商圈</option>';
						}
						_html += '</select>';
					}
				}
				obj.append(_html);
				var city = obj.find('select:first');
				var area = obj.find('select').eq(1);
				if (area.length > 0)
				{
					var data = {};
					city.change(function(){
						area.empty();
						var city_id = city.find('option:selected').val();
						data = city_data[city_id].area;
						var option = '';
						if (data == null)
						{
							option += '<option value="0">暂无区域</option>';
						}
						else
						{
							$.each(data, function(i, item){
							option += '<option value="'+item.area_id+'">'+item.area_name+'</option>';
							});
						}
						area.append(option);
						if (param.level == 3)
						{
							area.find('option:first').trigger('change');
						}
					});
				}
				var busi = obj.find('select').eq(2);
				if (busi.length > 0)
				{
					var data = {};
					area.change(function(){
						busi.empty();
						data = city_data[city.find('option:selected').val()].area[area.find('option:selected').val()].business;
						var option = '';
						if (data == null)
						{
							option += '<option value="0">暂无商圈</option>';
						}
						else
						{
							$.each(data, function(i, item){
							option += '<option value="'+item.business_id+'">'+item.business_name+'</option>';
							});
						}
						busi.append(option);
					});
				}
			}
		}
		_fn._init_run();
	}
})(jQuery);
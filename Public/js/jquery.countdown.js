/**
Copyright (c) 2012 Longhao Luo, http://www.kindsoft.net/

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

(function($) {
	$.fn.countdown = function(options) {
		// default options
		var defaults = {
			attrName : 'data-diff',
			tmpl : '<span class="hour">%{h}</span>小时<span class="minute">%{m}</span>分钟<span class="second">%{s}</span>秒',
			end : '已结束',
			afterEnd : null
		};
		options = $.extend(defaults, options);

		// trim zero
		function trimZero(str) {
			return parseInt(str.replace(/^0/g, ''));
		}
		// convert string to time
		function getDiffTime(str) {
			var m;
			if ((m = /^(\d{4})[^\d]+(\d{1,2})[^\d]+(\d{1,2})\s+(\d{2})[^\d]+(\d{1,2})[^\d]+(\d{1,2})$/.exec(str))) {
				var year = trimZero(m[1]),
					month = trimZero(m[2]) - 1,
					day = trimZero(m[3]),
					hour = trimZero(m[4]),
					minute = trimZero(m[5]),
					second = trimZero(m[6]);
				return Math.floor((new Date(year, month, day, hour, minute, second).getTime() - new Date().getTime()) / 1000);
			}
			return parseInt(str);
		}
		// format time
		function format(diff) {
			var tmpl = options.tmpl, day, hour, minute, second;
			day = /%\{d\}/.test(tmpl) ? Math.floor(diff / 86400) : 0;
			hour = Math.floor((diff - day * 86400) / 3600);
			minute = Math.floor((diff - day * 86400 - hour * 3600) / 60);
			second = diff - day * 86400 - hour * 3600 - minute * 60;
			tmpl = tmpl.replace(/%\{d\}/ig, day)
				.replace(/%\{h\}/ig, hour)
				.replace(/%\{m\}/ig, minute)
				.replace(/%\{s\}/ig, second);
			return tmpl;
		}
		// main
		return this.each(function() {
			var el = this,
				diff = getDiffTime($(el).attr(options.attrName));
			function update() {
				if (diff <= 0) {
					$(el).html(options.end);
					if (options.afterEnd) {
						options.afterEnd();
					}
					return;
				}
				$(el).html(format(diff));
				setTimeout(function() {
					diff--;
					update();
				}, 1000);
			}
			update();
		});
	};
})(jQuery);


/**
 * Created by zhuxun37 on 15/2/11.
 */

function onBridgeReady() {
	WeixinJSBridge.invoke("getNetworkType", {}, function(e) {
		WeixinJSBridge.log(e.err_msg);
	});
}

// 回退
function wx_history_go(index) {
	/**if ("undefined" == typeof(document.referer)) {
		wx_close_window();
		return true;
	}*/

	window.history.go(index);
}

// 关闭微信浏览器
function wx_close_window() {
	WeixinJSBridge.invoke("closeWindow",{});
}

// 解析 url
function parseURL(url) {
	var a =  document.createElement("a");
	a.href = url;
	return {
		source: url,
		protocol: a.protocol.replace(":", ""),
		host: a.hostname,
		port: a.port,
		query: a.search,
		params: (function() {
			var ret = {},
				seg = a.search.replace(/^\?/, "").split("&"),
				len = seg.length, i = 0, s;
			for (; i < len; i ++) {
				if (!seg[i]) continue;
				s = seg[i].split("=");
				ret[s[0]] = s[1];
			}

			return ret;
		})(),
		file: (a.pathname.match(/\/([^\/?#]+)$/i) || [, ""])[1],
		hash: a.hash.replace("#", ""),
		path: a.pathname.replace(/^([^\/])/, "/$1"),
		relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [, ""])[1],
		segments: a.pathname.replace(/^\//, "").split("/")
	};
}

/**
 * 继承操作
 * @param {object} child 子类
 * @param {object} parent 父类
 */
function extend(child, parent) {

	var F = function() {};
	F.prototype = parent.prototype;
	child.prototype = new F();
	child.prototype.constructor = child;
	// 调用父类方法的方法
	child.prototype.super = function() {
		var method = arguments.callee.caller;
		var f;
		for (var fn in this) {
			if (this[fn] == method) {
				f = fn;break;
			}
		}

		return parent.prototype[f].apply(this, arguments);
	};

	if (parent.prototype.constructor == Object.prototype.constructor) {
		parent.prototype.constructor = parent;
	}
}

// 如果是debug
if (window._debug) {
	require["urlArgs"] = "ts=" + (new Date()).getTime();
}

var ajaxFileName='my_ajax.php',
	Public='',
	aler=new Array('<span style="display:inline-block;color:#ffffff;background:black;opacity:.9;filter:alpha(opacity=90);-moz-opacity:.9;-khtml-opacity:.9;-webkit-opacity:.9;padding:0.5em 1em 0.5em 1em;line-height:1.3em;border-radius:0.3em;-moz-border-radius:0.3em;-khtml-border-radius:0.3em;-webkit-border-radius:0.3em;z-index:102;font-size:1.1em;">','</span>');
	
var ht=new Array(
	'&amp;', //0
	'&quot;', //1
	'&lt;', //2
	'&gt;', //3
	'&#39;', //4
	'‘', //5
	'’', //6
	'“', //7
	'”', //8
	'，', //9
	'&#039;'    //10
);
var ht_=new Array(
	'&',         //0
	'"',         //1
	'<',         //2
	'>',         //3
	"\\'",       //4
	"\\'",       //5
	"\\'",       //6
	'"',         //7
	'"',         //8
	',',         //9
	"\\'"        //10
);
var cfg=new Array();
cfg['timeout']=15000;
function by(id){
	if(document.getElementById){
		return document.getElementById(id);
	}else if(document.all){
		return document.all[id];
	}else if(document.layers){
		return document.layers[id];
	}else{
		return false;
	}
}
function AddFavorite(sURL, sTitle) {
    try {
        window.external.addFavorite(sURL, sTitle);
    } catch (e) {
        try {
            window.sidebar.addPanel(sTitle, sURL, "");
        } catch (e) {
            alert("加入收藏失败,请手动添加.");
        }
    }
}

function setHomepage(pageURL) {
    if (document.all) {
        document.body.style.behavior='url(#default#homepage)';
        document.body.setHomePage(pageURL);
    }
    else if (window.sidebar) {
        if(window.netscape) {
            try {
                netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
            }
            catch (e) {
                alert( "该操作被浏览器拒绝，如果想启用该功能，请在地址栏内输入 about:config,然后将项signed.applets.codebase_principal_support 值该为true" );
            }
        }
        var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components. interfaces.nsIPrefBranch);
        prefs.setCharPref('browser.startup.homepage',pageURL);
    }
}

function encode(str_text)
{
		var htmlEncode=function(str) {//HTML des encode.
		var res=[];
		for(var i=0;i < str.length;i++)
			res[i]=str.charCodeAt(i);
		return "&#"+res.join(";&#")+";";
		};
		var htmlEncode2=function(s) {
			var r = "", c;
			for (var i = 0; i < s.length; i++) {
				c = s.charCodeAt(i);
				r += (c < 32 || c == 38 || c > 127) ? ("&#" + c + ";") : s.charAt(i);
			}
			return r;
		};
		//s.replace(/([/u4e00-/u9fa5]+)/g,function($,$1) {
		//    return htmlEncode($1);
		//})
		var htmlHexEncode=function(str) {//HTML hex encode.
			var res=[];
			for(var i=0;i < str.length;i++)
				res[i]=str.charCodeAt(i).toString(16);
			return "&#"+String.fromCharCode(0x78)+res.join(";&#"+String.fromCharCode(0x78))+";";//x ，防止ff下&#x 转义
		};
		var htmlDecode = function(str) {
			return str.replace(/&#(x)?([^&]{1,5});?/g,function($,$1,$2) {
				return String.fromCharCode(parseInt($2 , $1 ? 16:10));
			});
		};

var s1=htmlEncode(str_text)+"/n/n只对双字节和&编码："+htmlEncode2(str_text);
var s2=htmlDecode(s1);
alert("编码前："+str_text);
alert("编码后："+s2);
}


//图片按比例显示
function AutoResizeImage(maxWidth, maxHeight, objImg) {
    var img = new Image();
    img.src = objImg.src;
    var hRatio;
    var wRatio;
    var Ratio = 1;
    var w = img.width;
    var h = img.height;
    wRatio = maxWidth / w;
    hRatio = maxHeight / h;
    if (maxWidth == 0 && maxHeight == 0) {
        Ratio = 1
    } else if (maxWidth == 0) {
        if (hRatio < 1) Ratio = hRatio
    } else if (maxHeight == 0) {
        if (wRatio < 1) Ratio = wRatio
    } else if (wRatio < 1 || hRatio < 1) {
        Ratio = (wRatio <= hRatio ? wRatio: hRatio)
    }
    if (Ratio < 1) {
        w = w * Ratio;
        h = h * Ratio
    }
    objImg.height = h;
    objImg.width = w
	if(objImg.width<maxWidth){ //自动左右居中
		objImg.style.marginLeft=parseInt((maxWidth-w)/2)+'px';
	}
	if(objImg.height<maxHeight){ //自动垂直居中
		objImg.style.marginTop=parseInt((maxHeight-h)/2)+'px';
	}
}
//图片按比例显示 可按宽和高固定显示

function dyniframesize(down) {  //ifram 自适应高;
var pTar = null; 
if (document.getElementById){ 
pTar = document.getElementById(down); 
} 
else{ 
eval('pTar = ' + down + ';'); 
} 
if (pTar && !window.opera){ 
//begin resizing iframe 
pTar.style.display="block" 
if (pTar.contentDocument && pTar.contentDocument.body.offsetHeight){ 
//ns6 syntax 
pTar.height = pTar.contentDocument.body.offsetHeight +20; 
pTar.width = pTar.contentDocument.body.scrollWidth+20; 
} 
else if (pTar.Document && pTar.Document.body.scrollHeight){ 
//ie5+ syntax 
pTar.height = pTar.Document.body.scrollHeight; 
pTar.width = pTar.Document.body.scrollWidth; 
} 
} 
} //ifram end

function getTimeToSeconds(date) { //时间转为秒
    if (!date) {
        date = new Date();
        return date.getTime()
    }
    if (isNaN(Number(date.replace(/ /g, '').replace(/:/g, '').replace(/-/g, '')))) {
        AlertMessage('', aler[0] + '日期不正确!' + aler[1], 'center', 3000, 'black', 10);
        return false
    }
    var d = new Date(),dat=date,dat1,dat2;
    if (dat.indexOf(' ') == -1) date = dat + ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();
	
    date = date.split(/ /);
	
	dat1=(date[0]).split('-');
	dat2=(date[1]).split(':');
    d.setFullYear(dat1[0]);
    d.setMonth(dat1[1]-1);
    d.setDate(dat1[2]);
    d.setHours(dat2[0]);
    d.setMinutes(dat2[1]);
    d.setSeconds(dat2[2]);
    return d.getTime();
}

function GB2312UTF8(){
	this.Dig2Dec=function(s){
		var retV = 0;
		if(s.length == 4){
		for(var i = 0; i < 4; i ++){
		retV += eval(s.charAt(i)) * Math.pow(2, 3 - i);
		}
		return retV;
		}
		return -1;
		}
		this.Hex2Utf8=function(s){
		var retS = "";
		var tempS = "";
		var ss = "";
		if(s.length == 16){
		tempS = "1110" + s.substring(0, 4);
		tempS += "10" +  s.substring(4, 10);
		tempS += "10" + s.substring(10,16);
		var sss = "0123456789ABCDEF";
		for(var i = 0; i < 3; i ++){
		retS += "%";
		ss = tempS.substring(i * 8, (eval(i)+1)*8);
		retS += sss.charAt(this.Dig2Dec(ss.substring(0,4)));
		retS += sss.charAt(this.Dig2Dec(ss.substring(4,8)));
		}
		return retS;
		}
		return "";
		}
		this.Dec2Dig=function(n1){
		var s = "";
		var n2 = 0;
		for(var i = 0; i < 4; i++){
		n2 = Math.pow(2,3 - i);
		if(n1 >= n2){
		s += '1';
		n1 = n1 - n2;
		}
		else
		s += '0';
		}
		return s;     
	}

this.Str2Hex=function(s){
var c = "";
var n;
var ss = "0123456789ABCDEF";
var digS = "";
for(var i = 0; i < s.length; i ++){
c = s.charAt(i);
n = ss.indexOf(c);
digS += this.Dec2Dig(eval(n));
}
return digS;
}
this.Gb2312ToUtf8=function(s1){
var s = escape(s1);
var sa = s.split("%");
var retV ="";
if(sa[0] != ""){
retV = sa[0];
}
for(var i = 1; i < sa.length; i ++){
if(sa[i].substring(0,1) == "u"){
retV += this.Hex2Utf8(this.Str2Hex(sa[i].substring(1,5)));
if(sa[i].length){
retV += sa[i].substring(5);
}
}
else{
retV += unescape("%" + sa[i]);
if(sa[i].length){
retV += sa[i].substring(5);
}
}
}
return retV;
}
this.Utf8ToGb2312=function(str1){
var substr = "";
var a = "";
var b = "";
var c = "";
var i = -1;
i = str1.indexOf("%");
if(i==-1){
return str1;
}
while(i!= -1){
if(i<3){
substr = substr + str1.substr(0,i-1);
str1 = str1.substr(i+1,str1.length-i);
a = str1.substr(0,2);
str1 = str1.substr(2,str1.length - 2);
if(parseInt("0x" + a) & 0x80 == 0){
substr = substr + String.fromCharCode(parseInt("0x" + a));
}
else if(parseInt("0x" + a) & 0xE0 == 0xC0){ //two byte
b = str1.substr(1,2);
str1 = str1.substr(3,str1.length - 3);
var widechar = (parseInt("0x" + a) & 0x1F) << 6;
widechar = widechar | (parseInt("0x" + b) & 0x3F);
substr = substr + String.fromCharCode(widechar);
}
else{
b = str1.substr(1,2);
str1 = str1.substr(3,str1.length - 3);
c = str1.substr(1,2);
str1 = str1.substr(3,str1.length - 3);
var widechar = (parseInt("0x" + a) & 0x0F) << 12;
widechar = widechar | ((parseInt("0x" + b) & 0x3F) << 6);
widechar = widechar | (parseInt("0x" + c) & 0x3F);
substr = substr + String.fromCharCode(widechar);
}
}
else {
substr = substr + str1.substring(0,i);
str1= str1.substring(i);
}
i = str1.indexOf("%");
}

return substr+str1;
}
}

function js_include($script){ //加载JS文件代码
	$js_path = "http://localhost/js/"; 
	var script = document.createElement('script'); 
	script.src = $js_path + $script; 
	script.type = 'text/javascript'; 
	var head = document.getElementsByTagName('head').item(0); 
	head.appendChild(script); 
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function Date_Sj(dd)
{
	var date = new Date(); //日期对象
	var now = "";
	//now = date.getFullYear()+"-"; //读英文就行了
	//now = now + (date.getMonth()+1)+"-";//取月的时候取的是上月-1如果想取当前月+1就可以了
	//now = now + date.getDate()+" ";
	var $a=date.getHours();
	var $b;
	if($a>=0 && $a<=5)   $b="午夜";
	if($a>=6 && $a<=8)   $b="早上";
	if($a>=9 && $a<=11)  $b="上午";
	if($a==12)           $b="中午";
	if($a>=13 && $a<=17) $b="下午";
	if($a>=18 && $a<=23) $b="晚上";
	now=$b+" ";
	now = now + date.getHours()+":";
	now = now + date.getMinutes()+":";
	now = now + date.getSeconds();
	by(dd).innerHTML=now; //div的html是now这个字符串
	//var aa="Date_Sj("+dd+")";
	setTimeout(function(){Date_Sj(dd)},1000); //设置过1000毫秒就是1秒，调用show方法
}
function DateFormat(date,type,cn,day){ //date=必须为长时间2015-1-2 13:13:34 类型 Y-m-d cn=cn||en  !day=星期    //JS获取时间
	var T=new Date();
	if(date){
		date=date.split(' ');
		var d=date[0].split('-');
		var hm=date[1].split(':');
		T.setFullYear(d[0],d[1]-1,d[2]);
		T.setHours(hm[0],hm[1],hm[2],0);
	}
	var Y,M,D,H,M_,S,t,y_,m_,d_,h_,m__,s_,Day,DAY=new Array('星期日','星期一','星期二','星期三','星期四','星期五','星期六');
	Y=T.getFullYear(); //当前年
	M=T.getMonth()+1;M=M<10 ? '0'+M : M;
	D=T.getDate();D=D<10 ? '0'+D : D;
	H=T.getHours();H=H<10 ? '0'+H : H;
	M_=T.getMinutes();M_=M_<10 ? '0'+M_ : M_;
	S=T.getSeconds();S=S<10 ? '0'+S : S;
	Day=T.getDay();	
	if(cn.toLowerCase()=='cn'){y_='年';m_='月';d_='日';h_=':';m__=':';s_='';}else{y_='-';m_='-';d_='';h_=':';m__=':';s_='';}
	switch(type.toLowerCase())
	{
		default :t=Y+y_+M+m_+D+d_+(day ? ' '+DAY[Day] : '')+' '+H+h_+M_+m__+S+s_;break;
		case 'y-m-d h:i':t=Y+y_+M+m_+D+d_+(day ? ' '+DAY[Day] : '')+' '+H+h_+M_;break;
		case 'y-m-d':t=Y+y_+M+m_+D+d_+(day ? ' '+DAY[Day] : '');break;
		case 'y-m':t=Y+y_+M+m_+(day ? ' '+DAY[Day] : '');break;
		case 'm-d':t=M+m_+D+d_+(day ? ' '+DAY[Day] : '');break;
		case 'h:i:s':t=(day ? ' '+DAY[Day] : '')+H+h_+M_+m__+S+s_;break;
		case 'h:i':t=(day ? ' '+DAY[Day] : '')+H+h_+M_;break;
		case 'i:s':t=(day ? ' '+DAY[Day] : '')+M_+m__+S+s_;break;
		case 'day':t=DAY[Day];break;
	}
	//t=!day ?t : t+' '+DAY[Day];
	return t;
}
function Now_Date()
{
	var date = new Date(); //日期对象
	var now = "";
	now = date.getFullYear()+"年"; //读英文就行了
	now = now + (date.getMonth()+1)+"月";//取月的时候取的是上月-1如果想取当前月+1就可以了
	now = now + date.getDate()+"日 ";
	//var $a=date.getHours();
	//now=$b+" ";
	now = now + date.getHours()+":";
	now = now + date.getMinutes()+":";
	now = now + date.getSeconds();
	return now //document.getElementById(dd).innerHTML=now; //div的html是now这个字符串
	//var aa="Date_Sj("+dd+")";
	//setTimeout(function(){Date_Sj(dd)},1000); //设置过1000毫秒就是1秒，调用show方法
}


function Check_Browser() //检测客户端阅读器的版本 用法:alert(Check_Browser())  if(Check_Browser()=='MSIE')
{  
   if(navigator.userAgent.indexOf("MSIE")>0) {  
        return "MSIE";  
   }  
   if(isFirefox=navigator.userAgent.indexOf("Firefox")>0){   //火狐阅读器
        return "Firefox";  
   }  
   if(navigator.userAgent.indexOf("Safari")>-1 && navigator.userAgent.indexOf("Chrome/")>-1) {  //谷歌
        return "Chrome";  
   }   
   if(isCamino=navigator.userAgent.indexOf("Camino")>0){  
        return "Camino";  
   }  
   if(isMozilla=navigator.userAgent.indexOf("Gecko/")>0){  
        return "Gecko";  
   }
    if(isChrome=navigator.userAgent.indexOf("Safari/")>0){  //苹果阅读器
        return "Safari";  
   }    
}

//返回val的字节长度
function getByteLen(val) {
	var len = 0;
	val=val.split(""); //IE7 等其它兼容模式下使用 需转换成数组
	//alert(val[i].match(/[^\x00-\xff]/ig));
	for (var i = 0; i < val.length; ++i) {
		//alert(val[i]);
		if(val[i]!="undefined")
		{
			if (val[i].match(/[^\x00-\xff]/ig)!= null) //全角 汉字
				len += 1;  //如果用来判断中文请改为len += 2;
			else
				len += 1;
		}
	}
	return len;
}
//返回val在规定字节长度max内的值
function getByteVal(val,max_) { //自动截取长度为max_的字符串
	var returnValue = '';
	var byteValLen = 0;
	for (var i = 0; i < val.length; i++) {
		if (val[i].match(/[^\x00-\xff]/ig) != null)
			byteValLen += 2;
		else
			byteValLen += 1;

		if (byteValLen > max_)
			break;

		returnValue += val[i];
	}
	return returnValue;
}

function CheckEnterLen(obj,Max,Min) //检查输入长度 obj=输入域 Max=最小 Min=最长
{
	var sp=" ";
	var str=$(obj).val(),id=obj.id;
	str=eval("str.replace(/"+sp+"/ig,'')"); //去除空格
	//document.getElementById('att_comm_backup').value=str; //将去除空格后的值重新赋值
	
	var value_len=getByteLen(str);
	if(value_len>0){
		if(value_len>Min){
			by(id+"_ERR").innerHTML='<font style="color:red;">(^-^) 您已输入 ['+value_len+'] 个字符，规定'+Max+'-'+Min+'个字符! 请截取一段：)</font>';
			//by(id).innerHTML=0;
		}else{
			if(value_len>=Max){
				by(id+'_ERR').innerHTML='<font style="color:green;">√ 您已输入 ['+value_len+'] 个字符，符合要求：)</font>';
			}else{
				by(id+'_ERR').innerHTML='<font style="color:red;">× 您已输入 ['+value_len+'] 个字符，至少'+Max+'个字符：)</font>';
			}
			//by(id).innerHTML=Min-value_len;
		}
	}
}

//过滤所有HTML代码
function Replace_HTML(str,rep_l,rep_r) //str字符串 rep_l 左边替换成内容 rep_r右边替换成内容 //过滤所有HTML代码
{
	var Str;
	if(rep_l==""){rep_l='';}if(rep_r==""){rep_r=='';}
	Str=str.replace(/<*?[^>]*?>/,rep_l); 
	Str=Str.replace(/<\/*?[^>]*?>/g,rep_r); //换掉所有的HTML代码
	//兼容其它阅读器
	Str=Str.replace(/<*[^>]*>/,rep_l);
	Str=Str.replace(/<\/*[^>]*>/,rep_r);
	Str=Str.replace(/\n/g,''); //所有换行
	Str=Str.replace(/ /g,''); //所有空格
	Str=Str.replace(/&nbsp;/g,''); //所有空格
	Str=Str.replace(/	/g,''); //所有TAB
	return Str;
}

//JS  操作COOKIE 开始
function setCookie(name,value,time) //name=COOKIE名称 value=cookie的值 time=s30,h2,d1为秒时天
{ 
        var strsec = getsec(time); 
        var expA = new Date(); 
        expA.setTime(expA.getTime() + strsec*1); 
        document.cookie = name + "="+ value + ";expires=" + expA.toGMTString();  //默认UTF8编码 escape(value)
} 
function getsec(str){ 
	//这是有设定过期时间的使用示例： 
	//s20是代表20秒 
	//h是指小时，如12小时则是：h12 
	//d是天数，30天则：d30 
   //alert(str); 
   var str1=str.substring(1,str.length)*1;  //S后面的值
   var str2=str.substring(0,1);  //取S前面的值
   if (str2=="s"){ 
        return str1*1000; 
   }else if (str2=="h"){ 
        return str1*60*60*1000; 
   }
   else if (str2=="d")
   { 
        return str1*24*60*60*1000; 
   } 
} 

//读取cookies 
function getCookie(name) { 
	var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");

	if(arr=document.cookie.match(reg))

			return unescape(arr[2]); 
	else 
			return null; 
} 
function getCookie1(name) 
{ 
        var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
 
        if(arr=document.cookie.match(reg))
 
                return unescape(arr); 
        else 
                return null; 
} 

//删除cookies 
function delCookie(name) 
{ 
        var exp = new Date(); 
        exp.setTime(exp.getTime() - 1); 
        var cval=getCookie(name); 
        if(cval!=null) 
                document.cookie= name + "="+cval+";expires="+exp.toGMTString(); 
}
//使用 setCookie("name","hayden","s20");
//JS  操作COOKIE 结束

function Read_Font_Size(mode,id) //mode 字体大小 id 要操作的对象容器
{
	//alert(mode);
	if(mode=='default')
	{
		by(id).style.fontSize="";
		by(id).style.lineHeight="";
	}else{
		by(id).style.fontSize=mode+'px';
		by(id).style.lineHeight=(parseInt(mode)*2-2)+'px';
		//alert(by(id).style);
	}
}

//js暂停函数
function Pause(obj,iMinSecond)
{
	if (window.eventList==null)
		window.eventList=new Array();
		var ind=-1;
		for(var i=0;i<window.eventList.length;i++){
			if (window.eventList[i]==null) {
				window.eventList[i]=obj; 
				ind=i;
				break;
			}
		}
		if (ind==-1){
			ind=window.eventList.length; 
			window.eventList[ind]=obj;
		}
	setTimeout("GoOn(" + ind + ")",iMinSecond);
}
//js继续函数
function GoOn(ind)
{
	var obj=window.eventList[ind];
	window.eventList[ind]=null;
	if (obj.NextStep)
		obj.NextStep();
	else
		obj();
}


function openWin(u, w, h) {
              var l = (screen.width - w) / 2;
              var t = (screen.height - h) / 2;
               var s = 'width=' + w + ', height=' + h + ', top=' + t + ', left=' + l;
                  s += ', toolbar=no, scrollbars=no, menubar=no, location=no, resizable=no';
               open(u, 'oWin', s);
       }
function openIt(page,w,h){
    //window.open(page,w,h);
	openWin(page,w,h);
	//alert(h);
	//var se=prompt("请在此输入一个数字！",123);
	//alert(se);
	//window.showModalDialog(page,'','dialogHeight:'+w+'px;dialogWidth:'+h+'px;resizable:no;status:no;help:no;center:yes;');
}

function drag(o){var a="onmousemove",b="setCapture",c="releaseCapture",f="clientY",g="clientX",d=document,z=d.documentElement,x,y,t,l,w,h;o=d.getElementById(o);o.onmousedown=function(e){e=e||event;x=e[g]-o.offsetLeft;y=e[f]-o.offsetTop;d[a]=function(e){e=e||event;t=e[f]-y;l=e[g]-x;w=z.clientWidth-o.offsetWidth;h=z.clientHeight-o.offsetHeight;l<0&&(l=0);t<0&&(t=0);l>w&&(l=w);t>h&&(t=h);with(o.style){top=t+"px";left=l+"px"}};d.onmouseup=function(){d[a]=null;o[c]&&o[c]()};o[b]&&o[b]();return false}};

/**
 *glide.layerGlide((oEventCont,oSlider,sSingleSize,sec,fSpeed,point);
 *@param auto type:bolean 是否自动滑动 当值是true的时候 为自动滑动
 *@param oEventCont type:object 包含事件点击对象的容器
 *@param oSlider type:object 滑动对象
 *@param sSingleSize type:number 滑动对象里单个元素的尺寸（width或者height）  尺寸是有point 决定
 *@param second type:number 自动滑动的延迟时间  单位/秒
 *@param fSpeed type:float   速率 取值在0.05--1之间 当取值是1时  没有滑动效果
 *@param point type:string   left or top
 */
var glide =new function(){
	function $id(id){return document.getElementById(id);};
	this.layerGlide=function(auto,oEventCont,oSlider,sSingleSize,second,fSpeed,point){
		var oSubLi = $id(oEventCont).getElementsByTagName('li');
		var interval,timeout,oslideRange;
		var time=1; 
		var speed = fSpeed 
		var sum = oSubLi.length;
		var a=0;
		var delay=second * 900; 
		var setValLeft=function(s){
			return function(){
				oslideRange = Math.abs(parseInt($id(oSlider).style[point]));	
				$id(oSlider).style[point] =-Math.floor(oslideRange+(parseInt(s*sSingleSize) - oslideRange)*speed) +'px';		
				if(oslideRange==[(sSingleSize * s)]){
					clearInterval(interval);
					a=s;
				}
			}
		};
		var setValRight=function(s){
			return function(){	 	
				oslideRange = Math.abs(parseInt($id(oSlider).style[point]));							
				$id(oSlider).style[point] =-Math.ceil(oslideRange+(parseInt(s*sSingleSize) - oslideRange)*speed) +'px';
				if(oslideRange==[(sSingleSize * s)]){
					clearInterval(interval);
					a=s;
				}
			}
		}
		
		function autoGlide(){
			for(var c=0;c<sum;c++){oSubLi[c].className='';};
			clearTimeout(interval);
			if(a==(parseInt(sum)-1)){
				for(var c=0;c<sum;c++){oSubLi[c].className='';};
				a=0;
				oSubLi[a].className="active";
				interval = setInterval(setValLeft(a),time);
				timeout = setTimeout(autoGlide,delay);
			}else{
				a++;
				oSubLi[a].className="active";
				interval = setInterval(setValRight(a),time);	
				timeout = setTimeout(autoGlide,delay);
			}
		}
	
		if(auto){timeout = setTimeout(autoGlide,delay);};
		for(var i=0;i<sum;i++){	
			oSubLi[i].onmouseover = (function(i){
				return function(){
					for(var c=0;c<sum;c++){oSubLi[c].className='';};
					clearTimeout(timeout);
					clearInterval(interval);
					oSubLi[i].className="active";
					if(Math.abs(parseInt($id(oSlider).style[point]))>[(sSingleSize * i)]){
						interval = setInterval(setValLeft(i),time);
						this.onmouseout=function(){if(auto){timeout = setTimeout(autoGlide,delay);};};
					}else if(Math.abs(parseInt($id(oSlider).style[point]))<[(sSingleSize * i)]){
							interval = setInterval(setValRight(i),time);
						this.onmouseout=function(){if(auto){timeout = setTimeout(autoGlide,delay);};};
					}
				}
			})(i)			
		}
	}
}

function onclick_a(id,i,gettemplate)
{
	//alert(id);
	var a=by(id).getElementsByTagName('a');
	var h=by(id).getElementsByTagName('h3');
	//var span=by(id).document.getElementsByTagName('a');
	var ai=h.length;
	//alert(span.length)
	var aa=id+"_a"+i;
	//alert(by(aa).innerHTML);
		for(var ii=0;ii<ai;++ii)
		{
			if(ii==i-1)
			{
				//span[i-1].style.color='#ff0000';
				//by(aa).style.borderBottom='1px solid #ff0000';
				by(aa).style.color='#ff0000';
				h[i-1].style.background='url('+gettemplate+'image/sj.gif) no-repeat center 15px';
			}else{
				//span[i-1].style.color='';
				h[ii].style.background='';
				by(id+"_a"+(ii+1)).style.color='';
				//by(aa).style.borderBottom='';
			}
		
	}
}

//飘浮广告 window.setInterval("heartBeat('id')",1);
lastScrollY=0;
function heartBeat(id)
{
	var diffY;
	if (typeof window.pageYOffset != 'undefined') {   
        	diffY = window.pageYOffset;   
        }else if (typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') {   
        	diffY = document.documentElement.scrollTop;   
        }else if (typeof document.body != 'undefined') {   
        	diffY = document.body.scrollTop;   
       	}
	percent=.1*(diffY-lastScrollY);
	if(percent>0)percent=Math.ceil(percent);
	else percent=Math.floor(percent); 
									//document.getElementById("left_to_bot_top").style.top=parseInt(document.getElementById("left_to_bot_top").style.top)+percent+"px";
		by(id).style.top=parseInt(by(id).style.top)+percent+"px";
		by('pf_left_1').style.top=parseInt(by(id).style.top)+percent+"px";
		//by('left_pf_msg').style.top=parseInt(by('left_pf_msg').style.top)+percent+"px";
		lastScrollY=lastScrollY+percent;
}
//飘浮广告

function isIE() //检测IE版本
{
	if(navigator.userAgent.indexOf("MSIE 6.0")>0)
	{
		return 6.0;
	}else if(navigator.userAgent.indexOf("MSIE 5.0")>0){
		return 5.0;
	}else if(navigator.userAgent.indexOf("MSIE 7.0")>0){
		return 7.0;
	}else if(navigator.userAgent.indexOf("MSIE 8.0")>0){
		return 8.0;
	}else if(navigator.userAgent.indexOf("MSIE 9.0")>0){
		return 9.0;
	}else if(navigator.userAgent.indexOf("MSIE 10.0")>0){
		return 10.0;
	}
}

function getScreen() //获取客户端分辨率
{
	var w=screen.width;
	var h=screen.height;
	var se=Array();
	se['w']=w;
	se['h']=h;
	return se;
}

function SearchSubmit(idd,str,len) //搜索提交框
{
	id=idd+'_gjz';
	var v=by(id).value;
	if(v=='' || v==str)
	{
		alert("请输入搜索关键字!");
		by(id).focus();
		return false;
	}else if(v.length>len){
		alert("搜索关键字在"+len+"个字符以内!");
		by(id).focus();
		return false;
	}else{
		var type;
		if(by(idd+'_value') && by(idd+'_value').value!='')
		{
			type='&type='+by(idd+'_value').value
		}else{
			type='';
		}
		location="index.php?tag=search&gjz="+encodeURIComponent(v)+type;
	}
}

function BodyTop() //滚动后到顶部的距离
{
	var bodyTop;
	if (typeof window.pageYOffset != 'undefined') {   
		bodyTop = window.pageYOffset;   
	}else if (typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') {   
		bodyTop = document.documentElement.scrollTop;   
	}else if (typeof document.body != 'undefined') {   
		bodyTop = document.body.scrollTop;   
	}
	return bodyTop;
}

///////通用AJAX读取内容后取数据的提示框 背景层 提示 开始
function CreateAjax(URL,get,msgtype,id,w,form,template){
	//传递的URL GET=提交类型POST GET 必须大写 //创建AJAX并提后提示或返回消息 msgtype=消息类型 id=如果要改变HTML消息则必须使用ID  w=消息框的宽度  用法 ：CreateAjax(url,'POST',2,'msgid',w,form,'image/');  //FORM=表单名称 template=主题路径
	get=get.toUpperCase(); //强制转为大写 toLowerCase=小写
	var ser='';
	var html='';
	switch(get){
		case 'POST': html=$.ajax({url:URL,type:get,data:$("#"+form).serialize(),cache:false,async:false}); break;
		case 'GET':  html=$.ajax({url:URL,type:get,async:false,cache:false}); break;
	}
	ser=html.responseText; //获取AJAX后返回的数据
	switch(msgtype){
		case 0: alert(ser); break; //直接ALERT提示消息		
		case 1: by(id).innerHTML=ser; break; //改变页面HTML提示
		case 3: return ser; break; //返回信息
		case null:break; //无提示
		default: CreateMessage(ser,id,msgtype,w,template); break; //创建HTML窗口提示	break;
	}
}

//通用消息提示窗口  ser='消息内容' id=显示消息的ID标识  msgtype=消息提示类型 w=消息框宽
function CloseDiv_Msg(i,id) //关闭创建的消息框
{
	var id1='#'+id+'_bj',id2='#'+id+'_button',id3='#'+id+'_txt_str',id4='#'+id+'_content';
	$(id2).css({visibility:'hidden'}); //BUTTON close
	$(id3).animate({opacity:0},300); //txt close width:0,height:0,
	setTimeout(function(){if(by(id+"_div"))$('#'+id+'_div').remove();},650);
	setTimeout(function(){
		$(id3).css({visibility:'hidden'}); //txt close
		$(id4).css({visibility:'hidden'}); //txt close
		$(id1).animate({opacity:0},300);//width:0,height:0,
	},300); //bjclose
	setTimeout(function(){
		$(id1).css({visibility:'hidden'});
	},600);
}

function CreateMessage(str,id,msgtype,w,template,sure)
{ //通用消息提示窗口  str='消息内容[txt/html]' id=显示消息的ID标识  msgtype=消息提示类型 w=消息框宽 template=模版路径 sure=有确定和取消的对话框
	switch(msgtype)
	{
		case 0: alert(str); break; //直接ALERT提示消息		
		case 1: by(id).innerHTML=str; break; //改变页面HTML提示	
		default : //创建HTML窗口提示 这里MSGTYPE=3不能调用前置AJAX已经有了布属
			var H=document.body.offsetHeight<window.screen.availHeight?window.screen.availHeight:document.body.offsetHeight;
			var bodyTop = BodyTop();
			//H=H+bodyTop;
			var W=document.body.scrollWidth<window.screen.availWidth?window.screen.availWidth:document.body.clientWidth; //网页的宽
			var Scroll=isIE()<7 ? 16 : 21;//阅读器兼容的问题为了让底部不出现滚动条
			if(document.documentElement){
				W=document.documentElement.scrollWidth;
			}else if(document.body){
				W=document.body.scrollWidth;
			}else{
				if(Check_Browser()!='MSIE')Scroll=15;//除IE外的其它阅读器
				W=W-Scroll;
			}		
			var h1=window.screen.availHeight; //可见区域高
			var content_w=parseInt(w)||225; //消息框的内容宽
			var left=parseInt((W-content_w)/2)-1; //左边的距离
			var but_w=25; //按钮的宽
			var top_h,close_src,bj_style,content_style,button_style,txt_style,closeimg='',txt_w,content_w1,txt;//content到顶部的高	
			id=!id?'ALERT_MESSAGE':id; //给个默认的消息ID
			template=!template?'image/':template;
			switch(msgtype){
				case 2: //无背景无边框的提示  全由str返回的值决定输出提示框的效果
					close_src=template+'ico/close.png';
					txt_w=content_w-but_w;
					content_w1=content_w;
					bj_style="visibility:hidden;position:absolute;width:100%;height:"+H+"px;top:0;left:0;opacity:.3;filter:Alpha(opacity=30);z-index:100000;";
					content_style="visibility:hidden;position:absolute;width:"+content_w1+"px;margin-left:auto;margin-right:auto;left:"+left+"px;z-index:100001;";
					button_style="visibility:hidden;text-align:right;position:relative;cursor:hand;cusor:pointer;z-index:2;";
					txt_style="visibility:hidden;line-height:22px;margin:-20px 0 0 44px;width:"+txt_w+"px;border:0px solid #999999;overflow:hidden;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;color:#454545;z-index:1;";
					txt="<div id='"+id+"_txt_str' style='"+txt_style+"'><div id='"+id+"_txt'></div></div>";
				break;
				
				case 4: //灰色的有边框有背景的提示
					close_src=template+'ico/close_001.png';
					but_w=35;
					txt_w=content_w-but_w
					content_w1=content_w
					bj_style="visibility:hidden;position:absolute;width:100%;height:"+H+"px;background:#000000;top:0;left:0;opacity:.3;filter:Alpha(opacity=30);z-index:100000;";
					content_style="visibility:hidden;position:absolute;width:"+content_w1+"px;margin-left:auto;margin-right:auto;left:"+left+"px;z-index:100001;";
					button_style="visibility:hidden;text-align:right;position:relative;cursor:hand;cusor:pointer;z-index:2;";
					txt_style="visibility:hidden;line-height:22px;margin:-20px 0 0 44px;width:"+txt_w+"px;border:1px solid #999;background:#ffffff;overflow:hidden;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;color:#454545;z-index:1;";
					var ser='<div style="filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorstr=#ffffff,EndColorstr=#e3e3e3);background:-moz-linear-gradient(top,#ffffff,#e3e3e3);background:-webkit-gradient(linear,0 0, 0 100%, from(#ffffff), to(#e3e3e3));display:block;border-bottom:1px solid #cccccc;padding-left:10px;overflow:hidden;height:30px;line-height:30px;">! 提示</div>';
					ser+='<div style="float:left;background:url('+template+'ico/err_001.gif) no-repeat center center;height:28px;width:32px;padding-top:10px;margin-top:3px;padding-left:10px;padding-right:10px;padding-bottom:15px;"></div>';
					ser+='<div style="float:left;padding-top:10px;padding-bottom:15px;margin-top:3px;padding-right:10px;width:'+(content_w-110)+'px;word-wrap:break-word;word-break:break-all;" id="'+id+'_txt"></div>';
					ser+='<div style="clear:both;padding-left:10px;padding-right:10px;text-align:right;filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorstr=#ffffff,EndColorstr=#e3e3e3);background:-moz-linear-gradient(top,#ffffff,#e3e3e3);background:-webkit-gradient(linear,0 0, 0 100%, from(#ffffff), to(#e3e3e3));display:block;border-top:1px solid #e3e3e3;height:28px;line-height:28px;"><span style="float:left;color:#999999;"></span><a href="javascript:void(0);" style="filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorstr=#f5f4f4,EndColorstr=#d6d4d4);background:-moz-linear-gradient(top,#f5f4f4,#d6d4d4);background:-webkit-gradient(linear,0 0, 0 100%, from(#f5f4f4), to(#d6d4d4));display:inline-block;border-top:0px solid #e3e3e3;color:#454545;height:24px;line-height:24px;padding-left:10px;padding-right:8px;border:0px solid #cccccc;margin-top:2px;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;float:right;" onclick=javascript:CloseDiv_Msg(3,"'+id+'");>确定</a></div>';
					txt="<div style='"+txt_style+"' id='"+id+"_txt_str'>"+ser+"</div>";
				break;
				
				case 5: //灰色的有边框无背景的提示
					close_src=template+'ico/close_001.png';
					but_w=35;
					txt_w=content_w-but_w
					content_w1=content_w
					bj_style="visibility:hidden;position:absolute;width:100%;height:"+H+"px;background:none;top:0;left:0;opacity:.3;filter:Alpha(opacity=30);z-index:100000;";
					content_style="visibility:hidden;position:absolute;width:"+content_w1+"px;margin-left:auto;margin-right:auto;left:"+left+"px;z-index:100001;";
					button_style="visibility:hidden;text-align:right;position:relative;cursor:hand;cusor:pointer;z-index:2;";
					txt_style="visibility:hidden;line-height:24px;margin:-20px 0 0 44px;width:"+txt_w+"px;border:1px solid #cfcece;background:#ffffff;overflow:hidden;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;color:#454545;z-index:1;font-size:15px;";
					var ser='<div style="filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorstr=#ffffff,EndColorstr=#f9f9f9);background:-moz-linear-gradient(top,#ffffff,#f9f9f9);background:-webkit-gradient(linear,0 0, 0 100%, from(#ffffff), to(#f9f9f9));display:block;border-bottom:1px solid #cfcece;padding-left:10px;overflow:hidden;height:30px;line-height:30px;font-size:15px;">! 提示</div>';
					ser+='<div style="float:left;background:url('+template+'ico/err_001.gif) no-repeat center center;height:28px;width:32px;padding-top:10px;margin-top:3px;padding-left:10px;padding-right:10px;padding-bottom:15px;"></div>';
					ser+='<div style="float:left;padding-top:10px;padding-bottom:15px;margin-top:3px;padding-right:10px;width:'+(content_w-110)+'px;word-wrap:break-word;word-break:break-all;" id="'+id+'_txt"></div>';
					ser+='<div style="clear:both;padding-left:10px;padding-right:10px;text-align:right;filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorstr=#ffffff,EndColorstr=#f9f9f9);background:-moz-linear-gradient(top,#ffffff,#f9f9f9);background:-webkit-gradient(linear,0 0, 0 100%, from(#ffffff), to(#f9f9f9));display:block;border-top:1px solid #e3e3e3;height:28px;line-height:28px;"><span style="float:left;color:#999999;"></span><a href="javascript:void(0);" style="filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorstr=#ffffff,EndColorstr=#f9f9f9);background:-moz-linear-gradient(top,#ffffff,#f9f9f9);background:-webkit-gradient(linear,0 0, 0 100%, from(#ffffff), to(#f9f9f9));display:inline-block;color:#454545;height:22px;line-height:21px;padding-left:10px;padding-right:8px;border:1px solid #e3e3e3;margin-top:2px;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;float:right;" onclick=javascript:CloseDiv_Msg(3,"'+id+'");>确定</a></div>';
					txt="<div style='"+txt_style+"' id='"+id+"_txt_str'>"+ser+"</div>";
				break;
				
				case 6: //按钮黑在右上角的提示
					close_src=template+'ico/close.png';
					txt_w=content_w-but_w*2+1;
					content_w1=content_w-but_w+1;
					bj_style="visibility:hidden;position:absolute;width:50%;height:"+H+"px;background:#000000;top:0;left:0;opacity:.3;filter:Alpha(opacity=30);z-index:100000;";
					content_style="visibility:hidden;position:absolute;width:"+content_w1+"px;margin-left:auto;margin-right:auto;left:"+left+"px;z-index:100001;";
					button_style="visibility:hidden;text-align:right;position:relative;cursor:hand;cusor:pointer;z-index:2;";
					txt_style="visibility:hidden;line-height:22px;padding:10px;margin:-15px 0 0 -10px;width:"+txt_w+"px;border:1px solid #999999;background:#ffffff;overflow:hidden;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;color:#454545;z-index:1;";
					txt="<div id='"+id+"_txt_str' style='"+txt_style+"'><div id='"+id+"_txt'></div></div>";
				break;
				
				case 7: //灰色的有边框有背景 左边无叹号 的提示
					close_src=template+'ico/close_001.png';
					but_w=35;
					txt_w=content_w-but_w;
					content_w1=content_w;
					var padding=str.indexOf('<div>')>-1 || str.indexOf('<span>')>-1 || str.indexOf('<p>')>-1 ? '' : 'font-size:14px;padding-left:10px;padding-right:0px;text-align:center;';
					bj_style="visibility:hidden;position:absolute;width:100%;height:"+H+"px;background:#000000;top:0;left:0;opacity:.3;filter:Alpha(opacity=30);z-index:100000;";
					content_style="visibility:hidden;position:absolute;width:"+content_w1+"px;margin-left:auto;margin-right:auto;left:"+left+"px;z-index:100001;";
					button_style="visibility:hidden;text-align:right;position:relative;cursor:hand;cusor:pointer;z-index:2;";
					txt_style="visibility:hidden;line-height:22px;margin:-20px 0 0 44px;width:"+txt_w+"px;border:1px solid #ccc;background:#ffffff;overflow:hidden;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;color:#454545;z-index:1;";
					var ser='<div style="filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorstr=#ffffff,EndColorstr=#f9f9f9);background:-moz-linear-gradient(top,#ffffff,#f9f9f9);background:-webkit-gradient(linear,0 0, 0 100%, from(#ffffff), to(#f9f9f9));display:block;border-bottom:1px solid #e3e3e3;padding-left:10px;overflow:hidden;height:30px;line-height:30px;font-size:14px;">! 提示</div>';
					//ser+='<div style="float:left;background:url('+template+'ico/err_001.gif) no-repeat center center;height:28px;width:32px;padding-top:10px;margin-top:3px;padding-left:10px;padding-right:10px;padding-bottom:15px;"></div>';
					ser+='<div style="clear:both;display:bock;padding-top:10px;padding-bottom:15px;'+padding+'margin-top:3px;padding-right:10px;word-wrap:break-word;word-break:break-all;" id="'+id+'_txt"></div>';
					ser+='<div style="filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorstr=#ffffff,EndColorstr=#f9f9f9);background:-moz-linear-gradient(top,#ffffff,#f9f9f9);background:-webkit-gradient(linear,0 0, 0 100%, from(#ffffff), to(#f9f9f9));clear:both;padding-left:10px;padding-right:10px;display:block;border-top:1px solid #e3e3e3;height:28px;line-height:28px;"><span style="float:left;color:#999999;"></span><a href="javascript:void(0);" style="display:inline-block;border:1px solid #e3e3e3;color:#454545;height:22px;line-height:22px;padding-left:10px;padding-right:8px;margin-top:2px;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;float:right;" onclick=javascript:CloseDiv_Msg(3,"'+id+'");>确定</a></div>';
					txt="<div style='"+txt_style+"' id='"+id+"_txt_str'>"+ser+"</div>";
				break;
				
				case 'confirm': //有确定按钮的提示
					if(!sure){alert('缺少SURE函数!');return;}
					close_src=template+'ico/close_001.png';
					but_w=35;
					txt_w=content_w-but_w
					content_w1=content_w
					bj_style="visibility:hidden;position:absolute;width:100%;height:"+H+"px;background:none;top:0;left:0;opacity:.3;filter:Alpha(opacity=30);z-index:100000;";
					content_style="visibility:hidden;position:absolute;width:"+content_w1+"px;margin-left:auto;margin-right:auto;left:"+left+"px;z-index:100001;";
					button_style="visibility:hidden;text-align:right;position:relative;cursor:hand;cusor:pointer;z-index:2;";
					txt_style="visibility:hidden;line-height:24px;margin:-20px 0 0 44px;width:"+txt_w+"px;border:1px solid #cfcece;background:#ffffff;overflow:hidden;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;color:#454545;z-index:1;font-size:14px;";
					var ser='<div style="filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorstr=#ffffff,EndColorstr=#f2f2f2);background:-moz-linear-gradient(top,#ffffff,#f2f2f2);background:-webkit-gradient(linear,0 0, 0 100%, from(#ffffff), to(#f2f2f2));display:block;border-bottom:1px solid #cfcece;padding-left:10px;overflow:hidden;height:30px;line-height:30px;font-size:14px;">! 提示</div>';
					ser+='<div style="float:left;background:url('+template+'ico/err_001.gif) no-repeat center center;height:28px;width:32px;padding-top:10px;margin-top:3px;padding-left:10px;padding-right:10px;padding-bottom:15px;"></div>';
					ser+='<div style="float:left;padding-top:10px;padding-bottom:15px;margin-top:3px;padding-right:10px;width:'+(content_w-110)+'px;word-wrap:break-word;word-break:break-all;" id="'+id+'_txt"></div>';
					ser+='<div style="clear:both;padding-left:10px;padding-right:10px;text-align:right;filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorstr=#ffffff,EndColorstr=#f2f2f2);background:-moz-linear-gradient(top,#ffffff,#f2f2f2);background:-webkit-gradient(linear,0 0, 0 100%, from(#ffffff), to(#f2f2f2));display:block;border-top:1px solid #e3e3e3;height:28px;line-height:28px;"><span style="float:left;color:#999999;">&nbsp;</span>';					
					ser+='<a href="javascript:void(0);" style="filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorstr=#f5f4f4,EndColorstr=#d6d4d4);background:-moz-linear-gradient(top,#f5f4f4,#d6d4d4);background:-webkit-gradient(linear,0 0, 0 100%, from(#f5f4f4), to(#d6d4d4));display:inline-block;border-top:0px solid #e3e3e3;color:#454545;height:24px;line-height:22px;padding-left:10px;padding-right:8px;border:0px solid #cccccc;margin-top:2px;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;float:right;" onclick=javascript:CloseDiv_Msg(3,"'+id+'");>关闭</a>';
					ser+='<a href="javascript:'+sure+';CloseDiv_Msg(3,'+"'"+id+"'"+');" style="filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorstr=#fc6262,EndColorstr=#ca0202);background:-moz-linear-gradient(top,#fc6262,#ca0202);background:-webkit-gradient(linear,0 0, 0 100%, from(#fc6262), to(#ca0202));display:inline-block;border-top:0px solid #e3e3e3;color:#ffffff;height:24px;line-height:22px;padding-left:10px;padding-right:8px;border:0px solid #cccccc;margin-top:2px;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;float:right;margin-right:10px;">确定</a></div>';
					txt="<div style='"+txt_style+"' id='"+id+"_txt_str'>"+ser+"</div>";
				break;
			}		
			if(by(id+"_div"))$('#'+id+'_div').remove();
			if(!by(id+"_bj")){
				if(close_src!=''){
					closeimg=(navigator.userAgent.indexOf("MSIE 6.0")>0) ? "<span style=display:inline-block;cursor:hand;cursor:pointer;width:25px;line-height:28px;height:28px;;font-size:28px;overflow:hidden;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+close_src+"', sizingMethod='scale'); title='关闭'></span>" :  "<img src='"+close_src+"' alt='关闭'>";
				}
				var bj="<div id='"+id+"_bj' style='"+bj_style+"' onclick=javascript:CloseDiv_Msg(3,'"+id+"'); title='点击关闭'></div>"; //背景
				bj+="<div id='"+id+"_content' style='"+content_style+"'>";
					bj+="<div id='"+id+"_button' style='"+button_style+"'><a href='javascript:;' onclick=javascript:CloseDiv_Msg(2,'"+id+"');>"+closeimg+"</a></div>"; //按钮 end
					bj+=txt; //txt end 
				bj+="</div>"; //contend end
				//document.body.insertAdjacentHTML("beforeEnd",bj);
				var d=document.createElement('div');	
				d.innerHTML=bj;		
				d.id=id+'_div';
				document.body.appendChild(d); //插入BODY
				//loadJs('js/curv.src.js'); 重载JS
				//drag(id+"_content");//可移动窗口
			}
			by(id+"_txt").innerHTML=str;			
			var txt_h=$('#'+id+"_content").outerHeight(); //提示信息DIV_TXT高度
			top_h=(h1>txt_h) ? parseInt((h1-txt_h)/2)+bodyTop : bodyTop+30; //上边的距离
			/*by(id+"_txt_str").style.width="0px";
			by(id+"_txt_str").style.height="0px";
			by(id+"_content").style.width='0px';*/
			by(id+"_content").style.left=left+'px';
			//alert('top_h='+top_h+' top='+bodyTop+' txt_h='+txt_h+' h1='+h1);
			by(id+"_content").style.top=top_h+"px"; //设置内容区到上面的距离
			//by(id+"_bj").style.height='0px';
			H=(top_h+txt_h)<H ? H : top_h+txt_h; //重设背景高
			
			$('#'+id+'_bj').css({visibility:'visible',opacity:0,width:W,height:H});
			$('#'+id+'_bj').animate({opacity:.3},300);
			setTimeout(function(){
				$('#'+id+'_content').css({visibility:'visible',opacity:0});
				$('#'+id+'_content').animate({opacity:1,left:left+'px'},500); //width:content_w1+'px',
			},300);
			setTimeout(function(){
				$('#'+id+'_txt_str').css({visibility:'visible',height:'auto'});
				$('#'+id+'_txt_str').animate({width:txt_w+"px",opacity:1},500);
			},300);
			setTimeout(function(){
				$('#'+id+'_button').css({visibility:'visible',opacity:0});
				$('#'+id+'_button').animate({opacity:1},300);
			},600);
			if(isIE()<7){
				$(window).scroll(function(){
					$('#'+id+'_content').css({top:$(document).scrollTop()+parseInt(($(window).height()-$('#'+id+'_content').outerHeight())/2)});
				});
			}else if(h1>txt_h){
				$('#'+id+'_content').css({position:'fixed',top:top_h});
			}else{
				$('#'+id+'_content').css({top:$(document).scrollTop()+30});
			}
		break; //default : //创建HTML窗口提示 end
	}
}
function AlertMessage(id,str,loca,time,bj,index){ //通用提示消息自动关闭	  id=唯一标识 str=内容 loca=位置 top,left,right,bottom,center time=自动关闭的时间 bj=true false 是否要背景 index=层编号 一般不指定
	id=!id ? 'AlertMessage_id' : id;
	index=index ? index : 100; //层编号
	var html='',scroL=isIE()<7 && $(window).height()<$(document).height() ? 16 : 0,bj_color=bj ? bj : 'black'; //默认背景
	if(bj)html='<div id="'+id+'_bj" style="position:absolute;visibility:hidden;top:0;left:0;opacity:0;filter:alpha(opacity=0);-moz-opacity:0;-khtml-opacity:0;-webkit-opacity:0;clear:both;display:block;background:'+bj_color+';z-index:'+index+';" onclick="CloseAlert(\''+id+'\');">&nbsp;</div>'; //创建背景
	if($('#'+id+'_bj').size()==0)$('body').append(html); //加入页面
	if($('#'+id+'_bj').size()>0){$('#'+id+'_bj').height($(document).outerHeight());$('#'+id+'_bj').width($(document).outerWidth()-scroL);} //背景层的高
	
	html='<span style="display:inline-block;visibility:hidden;z-index:'+index+';position:absolute;top:0;left:0;opacity:0;filter:alpha(opacity=0);-moz-opacity:0;-khtml-opacity:0;-webkit-opacity:0;" id="'+id+'">'+str+'</span>'; //内容代码	
	if($('#'+id).size()>0)$('#'+id).html(str);
	if($('#'+id).size()==0)$('body').append(html); //加入页面
	
	switch(loca){
		case 'left': //左居中
			$('#'+id).css({marginTop:$(document).scrollTop()+parseInt(($(window).height()-$('#'+id).height())/2)});
			$(window).scroll(function(){if($('#'+id).size()>0)$('#'+id).css({marginTop:$(document).scrollTop()+parseInt(($(window).height()-$('#'+id).height())/2)});});
		break;
		case 'right': //右居中
			$('#'+id).css({marginTop:$(document).scrollTop()+parseInt(($(window).height()-$('#'+id).height())/2),right:0});
			$(window).scroll(function(){if($('#'+id).size()>0)$('#'+id).css({marginTop:$(document).scrollTop()+parseInt(($(window).height()-$('#'+id).height())/2),right:0});});
		break;
		case 'top': //顶部
			$('#'+id).css({marginTop:$(document).scrollTop(),marginLeft:parseInt(($(window).width()-$('#'+id).width())/2)});
			$(window).scroll(function(){if($('#'+id).size()>0)$('#'+id).css({marginTop:$(document).scrollTop(),marginLeft:parseInt(($(window).width()-$('#'+id).width())/2)});});
		break;
		case 'bottom': //底部
			$('#'+id).css({bottom:$(document).scrollTop()+$(window).height()-$('#'+id).outerHeight(),marginLeft:parseInt(($(window).width()-$('#'+id).width())/2)});
			$(window).scroll(function(){if($('#'+id).size()>0)$('#'+id).css({bottom:$(document).scrollTop()+$(window).height()-$('#'+id).outerHeight(),marginLeft:parseInt(($(window).width()-$('#'+id).width())/2)});});
		break;
		default : //中部居中
			$('#'+id).css({marginTop:$(document).scrollTop()+parseInt(($(window).height()-$('#'+id).height())/2),marginLeft:parseInt(($(window).width()-$('#'+id).width())/2)});
			$(window).scroll(function(){if($('#'+id).size()>0)$('#'+id).css({marginTop:$(document).scrollTop()+parseInt(($(window).height()-$('#'+id).height())/2),marginLeft:parseInt(($(window).width()-$('#'+id).width())/2)});});
		break;
	}
	//显示层
	if($('#'+id+'_bj').size()>0){ //显示背景层
		$('#'+id+'_bj').css('visibility','visible');
		$('#'+id+'_bj').animate({opacity:.3},500);
	}
	if(isIE()<7){
		$('#'+id).css({visibility:'visible',marginTop:$('#'+id).height()+parseInt($('#'+id).css('marginTop')),top:0});
	}else{
		$('#'+id).css({visibility:'visible',marginTop:$('#'+id).height()+parseInt($('#'+id).css('marginTop')),top:0});
	}
	$('#'+id).animate({opacity:1,marginTop:parseInt($('#'+id).css('marginTop'))-$('#'+id).height()},500);
	if(time){setTimeout(function(){CloseAlert(id)},time);} //自动关闭
}
function CloseAlert(id){ //关闭提示消息
	id=id ? '#'+id : '#AlertMessage_id';
	$(id).animate({opacity:0},400,function(){$(id).remove();});
	$(id+'_bj').animate({opacity:0},400,function(){$(id+'_bj').remove();});
}
///////通用消息提示框 背景层 提示 结束

function Round(v,e){ //四舍五入
var t=1;

for(;e>0;t*=10,e--);

for(;e<0;t/=10,e++);

return Math.round(v*t)/t;

}

function GetRandomNum(Min,Max) //随机数
{   
	var Range = Max - Min;   
	var Rand = Math.random();   
	return(Min + Math.round(Rand * Range));   
}

function generateMixed(n) { //随机数
	var chars = ['0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
     var res = "";
     for(var i = 0; i < n ; i ++) {
         var id = Math.ceil(Math.random()*35);
         res += chars[id];
     }
	return res;
}

//图片轮播器
function ProImg(type,n,id,template,content) //type=类型 n=当前操作的域 id=当前轮播器的唯一ID template=当前程序模版 content=是否显示详细信息
{//初始化开始
	var imgBack='#'+id+' .ProImg_backup'; //大图显示域CLASS
	var imgTitle='#'+id+' .ProImg_title'; //大图标题CLASS
	var imgList='#'+id+' .ProImg_list .ProImg_list_cen li'; //小图域
	var imgL=$(imgList).size(); //小图总个数	
	var obj=$(imgList).eq(n); //当前点击域
	switch(type){
		default :
			if($(imgBack+' img').attr('src')!=$(imgList+' a span img').eq(n).attr('src')){
				$(imgBack).fadeOut(100,function(){
					$(imgBack).stop();
					$(imgBack).css({visibility:'hidden',opacity:0});
					$(imgBack+' img').attr('src',$(imgList+' a span img').eq(n).attr('src')); //改变值
					$(imgTitle).html($(imgList+' a span img').eq(n).attr('alt')); //改变标题
					$(imgBack).css('visibility','visible');
					$(imgBack).fadeIn(500,function(){
						for(i=0;i<imgL;++i){
							if(i==n){
								$(imgList).eq(n).addClass('ProImg_list_select'); //改变当前点击样式
							}else{
								$(imgList).eq(i).removeClass(); //移除其它样式
							}
						}
						var imgListUL='#'+id+' .ProImg_list .ProImg_list_cen'; //中间UL域
						var imgListW=$(imgListUL).width(); //中间UL域宽
						var liNum=Math.ceil(imgListW/($(imgList).width()+parseInt($(imgList).css('marginLeft')+parseInt($(imgList).css('marginRight'))))); //能同时显示多少张图片
						var liLeftNum=parseInt((liNum-1)/2); //左边应该保持几张图片
						if(n>=liLeftNum && n<imgL-1){ //在滚动范围之间才执行左右移动
							margin=(n-liLeftNum)*$(imgList).width()+(n+1-liLeftNum)*(parseInt($(imgList).css('marginLeft'))+parseInt($(imgList).css('marginRight')));
							$(imgListUL+' table').animate({marginLeft:-margin},1000);
						}else if(n<liLeftNum){
							$(imgListUL+' table').animate({marginLeft:0},1000);
						}
						imgW=$(imgBack+' img').width(); //改变后的图片宽
						imgH=$(imgBack+' img').height(); //改变后的图片高
						img_w_2=parseInt(imgW/2);
						link_prev=n>0 ? 'javascript:ProImg("",'+(n-1)+',"'+id+'","'+template+'","'+content+'");' : "'"+'javascript:CreateMessage("<font color=red>对不起，没有了，已到第一条信息!</font>","fdsaf",5,350,"'+template+'");'+"'";
						title_prev=n>0 ? '上一张：'+$(imgList+' img').eq(n-1).attr('alt') : '没有了';					
						link_next=n<imgL-1 ? 'javascript:ProImg("",'+(n+1)+',"'+id+'","'+template+'","'+content+'");' : "'"+'javascript:CreateMessage("<font color=red>对不起，没有了，已到最后一条信息!</font>","fdsaf",5,350,"'+template+'");'+"'";
						title=n<imgL-1 ? '下一张：'+$(imgList+' img').eq(n+1).attr('alt') : '没有了';
						area="<area shape='rect' coords='0,0,"+img_w_2+","+imgH+"' href="+link_prev+" title='"+title_prev+"' onmouseover="+'"'+"javascript:by('"+id+"_img').style.cursor='url("+template+"image/prev.cur),auto';"+'"'+" style='cursor:url("+template+"image/prev.ani)'><area shape='rect' coords='"+img_w_2+",0,"+imgW+","+imgH+"' href="+link_next+" title='"+title+"' onmouseover="+'"'+"javascript:by('"+id+"_img').style.cursor='url("+template+"image/next.cur),auto';"+'"'+" style='cursor:url("+template+"image/next.ani)'>";
						var Mtop=parseInt($('#'+id+' .ProImg_img_left span').css('marginTop')); //原来的顶部距离
						$('#'+id+'_img_map').html(area);
						$('#'+id+' .ProImg_img_left').html('<span onclick='+link_prev+' title="'+title_prev+'" style="margin-top:'+Mtop+'px;"></span>');
						$('#'+id+' .ProImg_img_right').html('<span onclick='+link_next+' title="'+title+'" style="margin-top:'+Mtop+'px;"></span>');
						var mTop=parseInt(imgH/2-40); //移动左右翻页按钮						
						var M=Mtop-mTop;
						if(Mtop<mTop){
							$('#'+id+' .ProImg_img_left span').animate({marginTop:mTop},500);
							$('#'+id+' .ProImg_img_right span').animate({marginTop:mTop},500);
						}else{
							$('#'+id+' .ProImg_img_left span').animate({marginTop:--mTop},500);
							$('#'+id+' .ProImg_img_right span').animate({marginTop:--mTop},500);
						}
						$(imgBack).css({opacity:1});
						if(content){
							var str=CreateAjax('my_ajax.php?act=get_db_backup&send='+$(imgList+' span').eq(n).attr('title'),'get',3,'','','',template);
							str=str.split('||');
							$('#'+id+' .ProImg_content').html(str[1]); //获取详细信息
							$('#'+id+' .ProImg_money').html(str[0]); //获取详细信息
						}
					}); //再显示			
				}); //先隐藏
			}
		break;
		case 'down': //右滚动 
			for(i=0;i<imgL;++i){ //ProImg_list_select
				if(typeof $(imgList).eq(i).attr('class')!='undefined' && $(imgList).eq(i).attr('class')!=''){
					if(i<imgL-1){n=i+1;}else{CreateMessage('<font color="red">对不起，没有了，已到最后一条信息!</font>','fdsaf',5,350,template);return;}//执行选择
				}
			}
			ProImg('',n,id,template);
		break;
		case 'up': //左滚动
			if($(imgList).eq(0).attr('class')=='ProImg_list_select'){CreateMessage('<font color="red">对不起，没有了，已到第一条信息!</font>','fdsaf',5,350,template);return;}
			for(i=0;i<imgL;++i){ //ProImg_list_select
				if(typeof $(imgList).eq(i).attr('class')!='undefined' && $(imgList).eq(i).attr('class')!=''){
					if(i>0){n=i-1;ProImg('',n,id,template);}//执行选择
				}
			}
		break;
	}
}
//图片轮播器
function Select(thi,type) //通用下拉菜单 选择域要有id 下拉菜单的ID为选项域+_menu 如：abcd  abcd_menu type=下拉类型  例：Select(this,null); type=down{下拉菜单覆盖this域}
{
	var idd=thi.id,id='#'+idd,lid=id+'_menu'; //点击域id和对应列表的ID
	var thisW=$(id).outerWidth(),thisH=$(id).outerHeight(),thisXY=$(id).offset(); //点击触发域的宽高 和XY坐标
	//alert(thisXY.left+' '+thisXY.top+' '+thisW+' '+thisH);
	var docW=$(document).outerWidth()-26,docH=$(document).height(); //页面的宽高
	var menuW=$(lid).outerWidth(),menuH=$(lid).outerHeight(); //下拉菜单的宽高
	var toTop;
	switch(type) //左右对齐时调用位置
	{
		default :toTop=thisXY.top+thisH-1;break;
		case 'down':toTop=thisXY.top-1;break;
		case 'up':toTop=-(thisH);break; //如果菜单是向上拉时
	}	
	//先判断左右
	if(docW-thisXY.left<menuW && docH-(thisXY.top+menuH)>menuH && type=='down'){ //MENU右对齐向下	
		$(lid).css({left:thisXY.left-(menuW-thisW),top:toTop});
		$(lid).slideDown(350);
	}else if(docW-thisXY.left>menuW && docH-(thisXY.top+menuH)>menuH && type=='down'){ //menu左对齐向下		
		$(lid).css({left:thisXY.left,top:toTop});
		$(lid).slideDown(350);
	}else{
		if(docW-thisXY.left<menuW){
			var left_=thisXY.left-(menuW-thisW);
		}else{
			var left_=thisXY.left;
		}
		if(docH-(thisXY.top+menuH)<10 && thisXY.top>=menuH){ //向上 底部的距离小于差值10px  且 上面的距离大于列表的高		
			$(lid).css({left:left_,top:thisXY.top-(menuH-1)});
			$(lid).slideDown(350);
		}else{ //如果上面的距离小于列表的高，那么还是向下
			$(lid).css({left:left_,top:thisXY.top+thisH-1});
			$(lid).slideDown(350);
		}
	}
	menuXY=$(id).offset(); //下边两个方法公用
	lX=menuXY.left; //左X坐标
	topY=menuXY.top; //上Y坐标
	rX=menuXY.left+thisW; //右X坐标
	botY=topY+thisH; //下Y坐标
	$(id).live('mouseout',function(event){
		$(document).mousemove(function(e){ //by('search_gjz').value='mouseX='+mX+' mouseY='+mY+' botY='+botY+' rX='+rX; //即时显示鼠标坐标
			mX=e.pageX; //列表的x y（鼠标）坐标
			mY=e.pageY;
			Lm=$(lid).offset();//下拉菜单页面位置
			LmTrue=mX<Lm.left || mX>$(lid).width()+Lm.left || mY<Lm.top || mY>Lm.top+$(lid).height() ? true : false; //下拉列表范围之外 为真
			ThisTrue=mX<lX || mX>rX || mY<topY || mY>botY ? true : false; //点击域的范围之外
			if(LmTrue && ThisTrue){ //在列表范围之外			
				if($(lid).css('display')!='none'){
					$(lid).slideUp(350);
					$(document).unbind('mousemove'); //删除绑定事件
				}
			}	
		});		
		$(document).click(function(e){
			mX=e.pageX; //列表的x y（鼠标）坐标
			mY=e.pageY;
			LmTrue=mX<Lm.left || mX>$(lid).width()+Lm.left || mY<Lm.top || mY>Lm.top+$(lid).height() ? true : false; //下拉列表范围之外 为真
			ThisTrue=mX<lX || mX>rX || mY<topY || mY>botY ? true : false; //点击域的范围之外
			if($(lid) && $(lid).css('display')!='none' && LmTrue && ThisTrue){
				$(document).unbind('click');$(lid).slideUp(350);				
			}//删除绑定事件			
		}); //点击隐藏
	});
}
function SelectTypeValue(fid,thi,value,inputid){
	$(fid+' .span').html($(thi).html()); //改变显示值
	$(inputid).val(value); //取INPUT值
	$(fid+'_menu').css({display:'none'}); //隐藏
}

function CheckDate(date) { //日期格式判断
	var result = date.match(/^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2})(\ {0,1})(\d{1,2})(\:{1,2})(\d{1,2})$/); //date.match(/^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2})(\ {0,1})(\d{1,2})(\:{1,2})(\d{1,2})(\:{1,2})(\d{1,2})$/); //2013-12-12 12:04:34 全验证
	if (result == null)
		return false;
	var d = new Date(result[1], result[3] - 1, result[4]);
	return (d.getFullYear() == result[1] && (d.getMonth() + 1) == result[3] && d.getDate() == result[4]);
}

function CheckInput(form,idname,type,Max,Min,msgtype,msg,retu,temp) //form=表单名 idname=input ID和NAME名称 type=len|select|tel|yzm .. 类型 max=最少长度 min=最大长度 msgtype=提示类型 msg=消息前缀  通用表单检测提示
{
	var amid='alert_message',amtime=3000,index=10000,ambj='';
	var aler=new Array('<span style="display:inline-block;color:#ffffff;background:black;opacity:.9;filter:alpha(opacity=90);-moz-opacity:.9;-khtml-opacity:.9;-webkit-opacity:.9;padding:5px 8px 5px 8px;line-height:1.5em;border-radius:5px;-moz-border-radius:5px;-khtml-border-radius:5px;-webkit-border-radius:5px;z-index:102;font-size:1.2em;margin-left:10px;margin-right:10px;">','</span>');

	if(!temp){temp='image/';}
	var f,message=idname+'_MSG';
	if(document.forms[form].idname)
	{
		f=document.forms[form].idname;
		if(type=='repeat')f1=document.forms[form].Max;
	}else{
		f=by(idname);
		if(type=='repeat')f1=by(Max);
	}
	by(form+'_submit').innerHTML='error'; //默认是没有检查的	
	if(!retu){retu='';}//后缀消息	
	switch(type)
	{
		case null:
			if(f.value==''){
				if(retu=='msg'){retu=' <font color="red">请填写'+msg+'!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="请填写'+msg+'!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+"请填写"+msg+'!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
		break;
		
		case 'enter': //检查输入内容只能是 Max
			if(f.value==''){
				if(retu=='msg'){retu=' <font color="red">请填写'+msg+'!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="请填写'+msg+'!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+"请填写"+msg+'!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}			
			var Letters=Max;   //可以自己增加可输入值
			var i,c,retur=false,m='';
			var v=f.value;
			if(v.charAt(0)==Min){retur=false;}else{retur=true;} //如果第一个字符是Min 则不允许
			if(retur==true){
				if(v.charAt(v.length-1)==Min){retur=false;}else{retur=true;} //如果最后一个字符是Min 则也不允许
				if(retur==true){
					for(i=0;i<v.length;i++){  
						c=v.charAt(i);
						if(Letters.indexOf(c)<0){retur=false; break;}else{retur=true;}
					}
				}
			}
			if(retur==false){
				if(Min!='')m='且首位和末位一个字符不能有"'+Min+'"';
				if(retu=='msg'){retu=' <font color="red">'+msg+'不符合要求，只能为"'+Max+'"任意字符'+m+'!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'不符合要求，只能为"'+Max+'"任意字符'+m+'!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+msg+'不符合要求!\n\n只能为"'+Max+'"任意字符'+m+'!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
		break;
		
		case 'ip': //检查IP
			var val = /([0-9]{1,3}\.{1}){3}[0-9]{1,3}/;
            var vald = val.exec(f.value);
            if (vald == null){
				if(retu=='msg'){retu=' <font color="red">'+msg+'不符合要求，如：202.98.96.68 !</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'不符合要求，如：202.98.96.68">'+retu;break;
					case 0:CreateMessage('<font color="red">'+msg+'不符合要求，如：202.98.96.68 !</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
			f=f.value.split('.');
			for(i=0;i<f.length;++i){
				if(i==0 || i==f.length-1){
					if(f[i]>255 || f[i]<=0){
						if(retu=='msg'){retu=' <font color="red">'+msg+'不符合要求，IP第一段和最后一段在0<256之间，如：202.98.96.68 !</font>';}
						switch(msgtype){
							default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'不符合要求，IP第一段和最后一段在0-256之间，如：202.98.96.68">'+retu;break;
							case 0:CreateMessage('<font color="red">'+msg+'不符合要求，IP第一段和最后一段在0-256之间，如：202.98.96.68 !</font>','alert_msg',5,350,temp);break;
						}
						return false;break;
					}
				}
				if(f[i]>255){
					if(retu=='msg'){retu=' <font color="red">'+msg+'不符合要求，IP中间段在0=<=255之间，如：202.98.96.68 !</font>';}
						switch(msgtype){
							default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'不符合要求，IP中间段在0=<=255之间，，如：202.98.96.68">'+retu;break;
							case 0:CreateMessage('<font color="red">'+msg+'不符合要求，IP中间段在0=<=255之间，如：202.98.96.68 !</font>','alert_msg',5,350,temp);break;
						}
						return false;break;
				}
			}
		break;
		
		case 'len':				
			if(f.value.length<Max || f.value.length>Min){
				if(retu=='msg'){retu=' <font color="red">'+msg+'长度不符合要求，规定必须在：'+Max+'-'+Min+'个字符之间!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'长度不符合要求，规定'+msg+'必须在：'+Max+'-'+Min+'个字符之间!">'+retu;break;
					case 0:AlertMessage(amid,aler[0]+'<font>'+msg+'长度不符合要求!\n\n规定'+msg+'必须在：'+Max+'-'+Min+'位字符之间!</font>'+aler[1],'center',amtime,ambj,index);break;
				}
				return false;
			}
		break;
		
		case 'link': //检查链接 //var url=/http:\/\/.+/;
			if(f.value==''){
				if(retu=='msg'){retu=' <font color="red">请填写'+msg+'!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="请填写'+msg+'!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+"请填写"+msg+'!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
			var strRegex = "^((https|http|ftp|rtsp|mms)?://)"  
         + "?(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?" //ftp的user@  
         + "(([0-9]{1,3}\.){3}[0-9]{1,3}" // IP形式的URL- 199.194.52.184  
         + "|" // 允许IP和DOMAIN（域名） 
         + "([0-9a-z_!~*'()-]+\.)*" // 域名- www.  
         + "([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\." // 二级域名  
        + "[a-z]{2,6})" // first level domain- .com or .museum  
        + "(:[0-9]{1,4})?" // 端口- :80  
        + "((/?)|" // a slash isn't required if there is no file name  
        + "(/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+/?)$"; 
			var re=new RegExp(strRegex);
			if(!re.test(f.value)){
				if(retu=='msg'){retu=' <font color="red">'+msg+'格式不正确，如(全小写)：http://www.tanbo.com/ </font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'格式不正确，如(全小写)：http://www.tanbo.com/">'+retu;break;
					case 0:CreateMessage('<font color="red">'+msg+'格式不正确，\n\n如(全小写)：http://www.tanbo.com/</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
		break;
		
		case 'cnlink': //中文域名
			var url=/http:\/\/.+/;
			if(f.value==''){
				if(retu=='msg'){retu=' <font color="red">请填写'+msg+'!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="请填写'+msg+'!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+"请填写"+msg+'!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
			if(f.value.match(url)==null){
				if(retu=='msg'){retu=' <font color="red">'+msg+'格式不正确，如：http://www.中国.com/</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'格式不正确，如：http://www.中国.com/">'+retu;break;
					case 0:CreateMessage('<font color="red">'+msg+'格式不正确，\n\n如：http://www.中国.com/</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
		break;
		
		case 'yzm':
			if(f.value==''){
				if(retu=='msg'){retu=' <font color="red">请填写'+msg+'!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="请填写'+msg+'!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+"请填写"+msg+'!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
			if(f.value!=by(idname+'_').innerHTML.toLowerCase()){
				if(retu=='msg'){retu=' <font color="red">'+msg+'不正确，如果看不清请点击更换!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'不正确，如果看不清请点击更换一个试试!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+msg+'验证码不正确!\n\n如果看不清请点击更换一个试试!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
		break;
		
		case 'email':
			if(f.value==''){
				if(retu=='msg'){retu=' <font color="red">请填写'+msg+'!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="请填写'+msg+'!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+"请填写"+msg+'!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
			var emailRegExp=/^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/;
			if(!emailRegExp.exec(f.value)){
				if(retu=='msg'){retu=' <font color="red">'+msg+'格式不正确，如：182382857@qq.com</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'格式不正确，如：182382857@qq.com">'+retu;break;
					case 0:CreateMessage('<font color="red">'+msg+'格式不正确，\n\n如：182382857@qq.com</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
		break;
		
		case 'repeat':
			if(f.value==''){
				if(retu=='msg'){retu=' <font color="red">请重复输入一次!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="请重复输入一次!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+'请重复输入一次!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
			if(f.value!=f1.value){
				if(retu=='msg'){retu=' <font color="red">'+msg+'输入不一致!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'输入不一次!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+msg+'输入不一致!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
		break;
		
		case 'tel': //通用号码验证
			if(f.value==''){
				if(retu=='msg'){retu=' <font color="red">请输入'+msg+'!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="请输入'+msg+'!">'+retu;break;
					case 0:AlertMessage(amid,aler[0]+'<font>'+'请输入'+msg+'!</font>'+aler[1],'center',amtime,ambj,index);break;
				}
				return false;
			}
			if(f.value.length<Max || f.value.length>Min){
				if(retu=='msg'){retu=' <font color="red">'+msg+'长度不符合要求，规定必须在：'+Max+'-'+Min+'个字符之间!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'长度不符合要求，规定'+msg+'必须在：'+Max+'-'+Min+'个字符之间!">'+retu;break;
					case 0:AlertMessage(amid,aler[0]+'<font>'+msg+'长度不符合要求!\n\n规定'+msg+'必须在：'+Max+'-'+Min+'位字符之间!</font>'+aler[1],'center',amtime,ambj,index);break;
				}
				return false;
			}
			if(isNaN(f.value.replace('-',''))){
				if(retu=='msg'){retu=' <font color="red">'+msg+'不符合要求，规定必须由：0-9、- 组成!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'不符合要求，规定必须由：0-9、- 组成!">'+retu;break;
					case 0:AlertMessage(amid,aler[0]+'<font>'+msg+'内容不符合要求，\n\n规定必须由：0-9、- 组成!</font>'+aler[1],'center',amtime,ambj,index);break;
				}
				return false;
			}
		break;
		
		case 'phone': //手机验证
			if(f.value==''){
				if(retu=='msg'){retu=' <font color="red">请输入'+msg+'!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="请输入'+msg+'!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+'请输入'+msg+'!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
			if(f.value.length<Max || f.value.length>Min){
				if(retu=='msg'){retu=' <font color="red">'+msg+'长度不符合要求!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'长度不符合要求!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+msg+'长度不符合要求!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
			var arrText="'13','14','15','18'";
			var arr=new Array('13','14','15','18'); //以开头
			var phonelen2=f.value.substr(0,2); //获取开头两位
			if(!getarraykey(arr,phonelen2.toString())){
				if(retu=='msg'){retu=' <font color="red">'+msg+'输入不正确!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="'+msg+'输入不正确!>'+retu;break;
					case 0:CreateMessage('<font color="red">'+msg+'输入不正确!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
		break;
		
		case 'select':
			if(f.value==''){
				if(retu=='msg'){retu=' <font color="red">请选择'+msg+'!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="请选择'+msg+'!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+'请选择'+msg+'!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}
		break;
		
		case 'dbrepeat': //检查数据库是否有重复
			if(f.value==''){
				if(retu=='msg'){retu=' <font color="red">请输入'+msg+'!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="请输入'+msg+'!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+'请输入'+msg+'!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}//idname,type,Max,Min,msgtype,msg,retu,temp
			var url=ajaxFileName+'?act=dbrepeat&type='+Max+'&code='+idname+'&value='+f.value;
			if(Min && Min!="")url+='&send='+Min;
			var str=CreateAjax(url,'get',3,'',1,'',temp);
			if(str!=''){ //有重复
				if(retu=='msg'){retu=' <font color="red">您输入的'+msg+' ['+f.value+'] 被使用，请更换后再试!</font>';}
				switch(msgtype){
					default:by(message).innerHTML='<img src="'+temp+'ico/err.gif" title="您输入的'+msg+' ['+f.value+'] 被使用，请更换后再试!">'+retu;break;
					case 0:CreateMessage('<font color="red">'+'您输入的'+msg+' ['+f.value+'] 被使用，请更换后再试!</font>','alert_msg',5,350,temp);break;
				}
				return false;
			}else{
				if(retu=='msg'){retu=' <font color="green">恭喜：'+msg+' 可以使用!</font>';}
				by(message).innerHTML='<img src="'+temp+'ico/ok.gif" title="恭喜：'+msg+' 可以使用!">'+retu;
				by(form+'_submit').innerHTML='ok'; //通过检查
				return true;
			}
		break;
	}
	var aler='输入';
	switch(type){case 'select':aler='选择';break;}
	if(retu=='msg'){retu=' <font color="green">恭喜：'+msg+' '+aler+'正确!</font>';}
	if($('#'+message).size()>0)by(message).innerHTML='<img src="'+temp+'ico/ok.gif" title="恭喜：'+msg+' '+aler+'正确!">'+retu;
	by(form+'_submit').innerHTML='ok'; //通过检查
	return true;
}
function getarraykey(s,v) { /* 返回数组中某一值的对应项数 如 a=new [1,2,4,6,0] getarraykey(a,6)=3  var a=new Array();a['f']='fdasfdsa';a['b']='fda';a['c']='fd'; alert(getarraykey(a,'fd'))==c; */
	for(k in s) {
		if(s[k] == v){return k;}
	}
	return false;
}
function DelCookieUser(form)
{
	var f=document.forms[form];
	setCookie('username',f.username.value,'s1');
	setCookie('password',f.password.value,'s1');
	f.username.value='';
	f.password.value='';
}
//通用Ajax账号登陆和读取 结束

function getRequest(name) //获取地址栏参数
{
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
	var r = window.location.search.substr(1).match(reg);
	if (r != null)
	return unescape(r[2]);
	return null;
	//alert(getRequest('参数名'));
}
function GetRequest() { 
/*var Request = new Object();
Request = GetRequest();*/
   var url = location.search; //获取url中"?"符后的字串 
   var theRequest = new Object(); 
   if (url.indexOf("?") != -1) { 
      var str = url.substr(1); 
      strs = str.split("&"); 
      for(var i = 0; i < strs.length; i ++) { 
         theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]); 
      } 
   } 
   return theRequest; 
}

function AttrTitle(name,id) //通用获取TITLE的值显示
{
	$(name).unbind('mouseover'); //先删除绑定的事件  必须
	$(name).unbind('mouseout'); //先删除绑定的事件
	//if($(name).data('mouseover')){return;}
	$(name).mouseover(function(event){ //再重新绑定 以使多次绑定调用
		var n=$(name).index(this);
		var title=$(name).eq(n).attr('title')
		var alt=$(name).eq(n).attr('alt');
		var margin=$(name).eq(n).offset();
		var t=$(name).eq(n);
		if(typeof title!='undefined' || typeof alt!='undefined')
		{
			var title1=typeof title!='undefined' ? title : alt;
			var str='<div id="'+id+'" class=""><span class="sj"><span class="sj_border">&#9670;</span><span class="sj_background">&#9670;</span></span><span class="more radius3"><span class="backup infront inbrown">'+title1+'</span></span></div>';
			if(!by(id))
			{			
				var span=document.createElement('span');
				span.id=id+'_span';
				span.innerHTML=str;
				document.body.appendChild(span);
			}
			//$('#'+id+' .backup').html(title);
			if(typeof title!='undefined')t.attr('title','');
			if(typeof alt!='undefined')t.attr('alt','');
			var marleft=parseInt(t.outerWidth()/2); //默认为对像的一半位置显示箭头
			if($('#'+id).outerWidth()<=t.outerWidth())marleft=parseInt($('#'+id).outerWidth()/2-15); //下拉菜单小于对像时下拉的一半位置显示箭头
			$('#'+id+' .sj').css({marginLeft:marleft});
			$('#'+id).css({left:margin.left,top:margin.top+$(name).eq(n).outerHeight()+22});
			$('#'+id).fadeIn(250);
			
		}
	});
	
	$(name).mouseout(function(event){
		var n=$(name).index(this);
		var title=$(name).eq(n).attr('title');
		var alt=$(name).eq(n).attr('alt');
		var margin=$(name).eq(n).offset();
		var t=$(name).eq(n);
		if(typeof title!='undefined' || typeof alt!='undefined')
		{
			if(typeof title!='undefined')t.attr('title',$('#'+id+' .backup').html());
			if(typeof alt!='undefined')t.attr('alt',$('#'+id+' .backup').html());
			$('#'+id).fadeOut(250);
			$('#'+id+' .backup').html();
			$('#'+id+'_span').remove();
		}
	});	
}
function LookStr(obj,id,type,str,conW,Margin,BjMouse){ //obj=当前点击对象 type=left right top bot str=显示的内容 conW=自定义宽  Margin=左右的边距 BjMouse=true ? false //是否要背景
	var obj_={w:$(obj).outerWidth(),h:$(obj).outerHeight(),x:$(obj).offset().left,y:$(obj).offset().top}; //OBJ对象的宽高和 XY起始标价
	var doc={w:$(window).width(),h:$(window).height()};
	Margin=!Margin ? 40 : Margin; //左/右的边距
	id=id ? id : 'message_str_id'; //创建的ID
	var objX=obj_.x<doc.w-(obj_.w+obj_.x) ? doc.w-(obj_.w+obj_.x) : obj_.x; //content 的宽自动判断  右边的宽 ： 左边的宽
	var Div=document.createElement('span');
	Div.id=id;
	with(Div){
		className='lookspan_bj';
		style.width=doc.w+'px';
		style.height=doc.h+'px';
	};
	if($('#'+id).size()<1){
		var s='<span class="lookspan_content" id="'+id+'_content">'; //内容块
			s+='<span class="lookspan_sj" id="'+id+'_sj"><span class="sj1">◆</span><span class="sj2">◆</span></span>';			
			s+='<span class="lookspan_text" id="'+id+'_text">';
				s+='<span class="lookspan_button" id="'+id+'_button"><a href="javascript:void(0);" onclick="LookStrOut(\''+id+'\');">×</a></span>';
				s+='<span class="text">'+str+'</span>';
			s+='</span>';
		s+='</span>'; //中间的内容
		$('body').append(Div); //加入
		$('body').append(s);		
	}
	if(!type){ //如果未设置type则自动判断位置
		var conH=$('#'+id+'_content').outerHeight();
		if(doc.w-(obj_.w+obj_.x)>conW){ //默认在右边
			type='right';
		}else if(doc.h-obj_.h-obj_.y<conH){ //如果底部小于content的高 则在上边
			type='top';
		}else{ //否则在下边
			type='bot';
		}
	}
	var Margin_=0;
	Margin_=conW ? Margin : 0;
	switch(type){
		case 'left'  :conW=conW ? conW : obj_.x-Margin;break;
		case 'right' : conW=conW ? conW : doc.w-(obj_.w+obj_.x)-Margin;break;
		case 'top'   : conW=conW ? conW : doc.w-Margin; break;
		case 'bot'   : conW=conW ? conW : doc.w-Margin; break;
		default      : if(!conW){alert('[type]为空时，必须设置参数[conW]的宽!');return;}break;
	}
	var con={w:conW}; //objX-Margin 这里未用自动判断
	$('#'+id).fadeIn(250,function(){
		setTimeout(function(){
			con.h=$('#'+id+'_content').outerHeight();
			var sj={w:$('#'+id+'_sj').outerWidth(),h:$('#'+id+'_sj').outerHeight()}; //三角的宽高
			switch(type){
				case 'left':  //内容在左边
					$('#'+id+'_content').offset({left:Math.ceil(obj_.x-con.w-3-sj.w/2)-Margin_,top:Math.floor(obj_.h/2)+obj_.y-Math.ceil(con.h/2)});
					$('#'+id+'_sj').css({left:Math.ceil($('#'+id+'_text').outerWidth())+Math.ceil(sj.w/5),top:Math.ceil(con.h/2-sj.h/2)});
					$('#'+id+'_sj .sj2').css({marginTop:0,marginLeft:-1});
				break;
				case 'right': //内容在右边
					$('#'+id+'_content').offset({left:Math.ceil(obj_.x+obj_.w+3+Margin_),top:Math.floor(obj_.h/2)+obj_.y-Math.ceil(con.h/2)});
					$('#'+id+'_sj').css({left:8,top:Math.ceil(con.h/2-sj.h/2)});
					$('#'+id+'_sj .sj2').css({marginTop:0,marginLeft:1});
				break;
				case 'top':
					var marginBottom=$('#'+id+'_content').css('marginBottom')!='auto' ? parseInt($('#'+id+'_content').css('marginBottom')) : 0;
					$('#'+id+'_content').offset({left:Math.ceil(obj_.x-(con.w/2)+obj_.w/2),top:obj_.y-con.h+marginBottom-Margin_});
					var topJr=isIE() ? 2 : -1;
					$('#'+id+'_sj').css({left:Math.ceil(con.w/2-sj.w/2),top:Math.ceil(con.h-sj.h)+topJr});
					$('#'+id+'_sj .sj2').css({marginTop:-1,marginLeft:0});
				break;
				case 'bot':
					var marginTop=$('#'+id+'_content').css('marginTop')!='auto' ? parseInt($('#'+id+'_content').css('marginTop')) : 0;
					$('#'+id+'_content').offset({left:Math.ceil(obj_.x-(con.w/2)+obj_.w/2),top:Math.floor(obj_.y+obj_.h/2+sj.h/2)-marginTop+Margin_});
					$('#'+id+'_sj').css({left:Math.ceil(con.w/2-sj.w/2),top:0});
					$('#'+id+'_sj .sj2').css({marginTop:1,marginLeft:0});
				break;			
			}
		},500);
		$('#'+id+'_content').fadeIn(350); //显示内容出来
	});	
	if(BjMouse){ //要背景
		$('#'+id).width(doc.w); //总ID宽
		$('#'+id).height(doc.h);
	}else{
		$('#'+id).width(0); //0
		$('#'+id).height(0);
	}
	$('#'+id).bind('click',function(){
		$('#'+id).fadeOut(300,function(){$('#'+id).remove();});
		$('#'+id+'_content').fadeOut(300,function(){$('#'+id+'_content').remove();});		
	});
	$('#'+id+'_content').width(con.w);
	$('#'+id+'_text').width(con.w-parseInt($('#'+id+'_text').css('paddingLeft')+$('#'+id+'_text').css('paddingRight'))-$('#'+id+'_sj').outerWidth());	
	//Scroll=new TouchScroll({id:id+'_text','width':5,'opacity':0.7,color:'#555',minLength:20});
}
function LookStrOut(id){ //配套的移开 关闭 并删除对象
	id=!id ? 'message_str_id' : id;
	$('#'+id).fadeOut(300,function(){$('#'+id).remove();});
	$('#'+id+'_content').fadeOut(300,function(){$('#'+id+'_content').remove();});
}
function Watter(el,tag){
	var h = [],width=$('#'+el).width(),n,boxLeft;
	var FBox=[]; //父盒子的高
	tag = tag ? tag : 'li';
	var boxId='#'+el+' '+tag; //小盒子ID
	var fboxId='#'+el; //父盒子ID
	el=by(el);
	var box = el.getElementsByTagName(tag);
	var minH = $(boxId).eq(0).outerHeight(),
	boxW = $(boxId).eq(0).outerWidth(),
	boxL = parseInt($(boxId).eq(0).css('marginLeft')), //小盒子左边距离
	boxR = parseInt($(boxId).eq(0).css('marginRight')), //小盒子右边距离
	boxW = boxW+boxR+boxL, //小盒子的实际宽
	boxH,
	elL = parseInt($(fboxId).css('paddingLeft')),
	elR = parseInt($(fboxId).css('paddingRight')),
	boxTop;
	boxTop=parseInt($(boxId).eq(0).css('marginTop'))>0 ? parseInt($(boxId).eq(0).css('marginTop')) : parseInt($(boxId).eq(0).css('marginBottom')); //计算 tag 小盒子 的上下间距
	n = Math.floor((width-elL-elR)/boxW); //计算页面能排下多少个 tag
	el.style.width = n * boxW + "px"; //设置父域el id 的绝对宽度 避免左右有太大的间隙  如果加了垂直滚动条 (n * boxW+30) + "px" 30=滚动条的宽
	boxLeft=$(boxId).eq(0).offset().left; //必须在此获取
	var el_offsetL=$(fboxId).offset().left; //必须在此获取
	//alert(n+' '+el.style.width+' '+boxLeft+' '+el_offsetL+" "+$(fboxId).height()+' '+boxR);
	for(var i = 0; i < box.length; i++) {
		height=$(boxId).eq(i).outerHeight();
		boxH = height; //获取每个Pin的高度
		if(i < n) { //第一行Pin以浮动排列，不需绝对定位
			h[i]=boxH+boxTop;
			box[i].style.position ='';
			FBox[i]=$(boxId).eq(i).height()+boxTop;
		}else{
			minH=Math.min.apply({},h); //取得各列累计高度最低的一列
			minKey = getarraykey(h,minH);
			h[minKey]+=boxH+boxTop; //加上新高度后更新高度值
			box[i].style.position='absolute';
			$(boxId).eq(i).offset({top:minH+parseInt($(boxId).eq(minKey).offset().top)});
			$(boxId).eq(i).offset({left:boxLeft-(boxR+boxL)+boxR+(minKey*boxW)}); //如果小盒子TAG设置了左右间路请设置为 (boxR+boxL)+boxR 否则 设置 (boxR+boxL)
			//box[i].innerHTML='box[0].left='+boxLeft+'<br>'+box[i].innerHTML+'<br> POSITION='+box[i].style.position+'<br>Top='+box[i].style.top+'<br>Left='+box[i].style.left+'<br>+h[minKey]='+h[minKey]+'<br>minKey='+minKey+'<br>minH='+minH;			
			if(isNaN(FBox[minKey])) FBox[minKey]=0; else FBox[minKey]+=($(boxId).eq(i).height()+boxTop);			
		}
		//box[i].innerHTML+='<br>'+($(boxId).eq(i).height()+boxTop);
	}
	
	//设置父BOX的高
	//for(i=0;i<n;++i){ FBox[i]+=$(boxId).eq(i).height()+boxTop; } //Math.min.apply({},FBox)+' '+Math.max.apply({},FBox) //FBox[]中最小值和最大值//alert(FBox[0]+' '+FBox[1]+' '+FBox[2]+' '+FBox[3]+' '+FBox[4]+' '+FBox[5]+' '+Math.max.apply({},FBox));
	//var endN=[],ii=0; //取最后n个的高度数组
	//for(i=$(boxId).size()-n;i<$(boxId).size(); ++i){ endN[ii]=$(boxId).eq(i).height(); ++ii; } //alert(Math.max.apply({},FBox)+' '+Math.max.apply({},endN));	
	$(fboxId).height(Math.max.apply({},FBox)); //重设父BOX的高度
}
function getAlertReturn(mark){
	return CreateAjax(ajaxFileName+'?act=get_edit_alert&type='+mark,'get',3,'','','','');
}
function MemberLeftMenu(obj,id){
	var o={n:$(id).find('.dt').size(),i:$(id).find('.dt').index(obj)};
	for(a=0;a<o.n;++a){
		if(a==o.i && $(id).find('dl:eq('+o.i+')').css('display')!='block'){
			$(id).find('dl:eq('+o.i+')').slideDown(300);
		}else{
			$(id).find('dl:eq('+a+')').slideUp(300);
		}
	}
}
function CheckDelAll(qx,box,form){ //通用全选 qx=全选按钮 box=所有待选域 form=表单名
	qx='#'+qx;
	var obj=$('form[name="'+form+'"] input[name="'+box+'"]');
	if(obj.size()>0){
		obj.prop('checked',$(qx).prop('checked'));
		return true;
	}else{ //如果没有
		$(qx).prop('checked',false);
		return false;
	}
}
function DelAll(qx,box,form,submittype,url){ //通用删除选中项 	qx=全选按钮 box=所有待选域 form=表单名 submittype=提交类型 url=提交地址
	if(!url){CreateMessage('缺少必要参数url!','',7,350,'','');return;}
	qx='#'+qx;
	var obj=$('form[name="'+form+'"] input[name="'+box+'"]');
	var Sub=false;
	if(obj.size()>0){
		for(i=0;i<obj.size();++i){
			if(obj.eq(i).prop('checked')==true){Sub=true;break;}
		}
		if(!Sub){CreateMessage('至少选择一条数据!','',7,350,'','');return;}
		submittype=!submittype ? 'GET' : submittype;
		var str=CreateAjax(url,submittype,3,'','',form,'');
		if(str!=''){
			str=$.parseJSON(str);
			CreateMessage(str.msg,'',7,350,'','');//提示
			if(str.loca){setTimeout(function(){eval(str.loca);},str.time);}//刷新页面
		}else{
			CreateMessage('发生错误!','',7,350,'','');
		}
	}
}
//通用选项卡切换
function MenuCard(thi,classid,id) //当前序号第几个H3，id=要操作的类ID 例：MenuCard(this,'.menu_card','#fid_1') id=当前操作的域DOM
{
	var mouseThis=$(thi).index(); //当前鼠标移动第几个
	var dl=id+' '+classid+'_dl';
	var h3=id+' '+classid+'_h3';
	var backupN=$(dl); //获取显示内容[object]
	var mouseN=$(h3); //获取选项卡内容[object]
	var h3_class=classid+'_h3 '+classid+'_up';
	h3_class=h3_class.replace(/\./g,'');
	var dl_class=classid+'_dl '+classid+'_block';
	dl_class=dl_class.replace(/\./g,'');
	var h3_=classid+'_h3';h3_=h3_.replace(/\./g,'');
	var dl_=classid+'_dl';dl_=dl_.replace(/\./g,'');
	if(mouseN.size()!=backupN.size()){CreateMessage('设置选项卡和内容[DOM]不相等，请检查代码! <span style="color:green">当前选项卡：Class['+h3_+']<span style="color:red">['+mouseN.size()+']</span>个DOM 内容：Class['+dl_+']<span style="color:red">['+backupN.size()+']</span>个DOM!</span>','',5,350,'','');return;}	
	mouseN.removeClass();
	mouseN.addClass(h3_);
	backupN.removeClass();
	backupN.css({display:'none'});
	backupN.addClass(dl_);
	var i=mouseThis-1;
	if($(classid+'_up_menu').size()>0){ //如果出现菜单动画效果层
		backupN.eq(i).removeClass();
		backupN.eq(i).addClass(dl_class);
		$(classid+'_up_menu').animate({width:mouseN.eq(i).outerWidth(),height:mouseN.eq(i).outerHeight(),left:mouseN.eq(i).offset().left,top:mouseN.eq(i).offset().top},200,'',function(){
			mouseN.eq(i).removeClass();
			mouseN.eq(i).addClass(h3_class);
			backupN.eq(i).css({display:'block',margin:0});					
		}); //动画菜单宽高初始设置
	}else{
		mouseN.eq(i).removeClass();
		mouseN.eq(i).addClass(h3_class);
		backupN.eq(i).removeClass();
		backupN.eq(i).addClass(dl_class);
		backupN.eq(i).css({display:'block'});
	}
			
}
function Ajax(act,methd,form,index){ //通用AJAX操作
	if(!act)return false;
	methd=methd ? methd.toUpperCase() : 'GET';
	index=index ? index : 10001;
	var URL=ajaxFileName+'?act='+act;//CreateAjax(ajaxFileName+'?act='+act,methd,3,'',0,form,''); 
	switch(methd){
		case 'POST':
			$.ajax({
				url:URL,type:methd,data:$("#"+form).serialize(),cache:false,async:true,dataType:"html",success: function(str){
					str=$.parseJSON(str);
					if(str.err)AlertMessage('',aler[0]+str.msg+aler[1],'center',str.time,'',index);
					if(str.loca)setTimeout(function(){eval(str.loca);},str.time);
				}
			});
		break;
		case 'GET':
			$.ajax({
				url:URL,type:methd,async:true,cache:false,dataType:"html",success: function(str){
					str=$.parseJSON(str);
					if(str.err)AlertMessage('',aler[0]+str.msg+aler[1],'center',str.time,'',index);
					if(str.loca)setTimeout(function(){eval(str.loca);},str.time);
				}
			});
		break;
	}
}
function AjaxData(act,method,data,index){ //AjaxData(\'yingye_set_save\',\'POST\',getFormData(\'form2\'),10000)
	index=index ? index : 10000;
	method=method ? method.toUpperCase() : 'POST';
	data=data ? data : {};
	url=ajaxFileName+'?act='+act;
	$.ajax({
		url:url,cache:false,type:method,dataType:"json",async:true,data:data,timeout:15000,
		success:function(str){
			if(str.err)AlertMessage('',aler[0]+str.msg+aler[1],'center',str.time,'black',index);
			if(str.loca)setTimeout(function(){eval(str.loca);},str.time);
		}
	});
}
function getJsonLength(jsonData){ //统计JSON的长度
	var jsonLength = 0;
	for(var item in jsonData){ jsonLength++; }
	return jsonLength;
}
function AutoImgCenter(obj,li,img){ //自动判断图片的垂直和左右居中效果 obj=父域对象的 li=父域OBJ下<IMG>的父域对象 img=<IMG>的类或TAG名
	obj=$(obj);
	for(i=0;i<obj.size();++i){
		//alert(obj.eq(i).find(li).height()+' '+obj.eq(i).find(img).height());
		obj.eq(i).find(img).css({marginTop:parseInt((obj.eq(i).find(li).height()-obj.eq(i).find(img).height())/2)}); //垂直居中
		obj.eq(i).find(img).css({marginLeft:parseInt((obj.eq(i).find(li).width()-obj.eq(i).find(img).width())/2)}); //左右居中
	}
}
function setstorage(objName,objValue){ //设置SESSION变量
	var str=getstorage(objName);
	if(str!=null && str!='' && typeof str!='undefined')clearstorage(objName); //如果存在则先清除
	var sto = window.localStorage;
	if(sto) sto.setItem(objName,objValue);
}
function getstorage(objName){ //获取变量
	var ret = '';
	var sto = window.localStorage;
	if(sto) ret=sto.getItem(objName);
	return ret;
}
function clearstorage(objName){ //清除变量
	var sto = window.localStorage;
	if(sto){
		if(objName) sto.removeItem(objName); //如果存在才清除，如果不存在则所有的都清除
		else storage.clear();
	}
}
function setStorJson(objName, json){ //设置JSON对象
	if(json) setstorage(objName,JSON.stringify(json));
}
function getStorJson(objName){ //获取JSON对象
	var ret = {};
	var str = getstorage(objName);
	if(str) ret=JSON.parse(str);
	return ret;
}
function lenecho(str,len,end){ //截取字符串 str=字符串 len=截取的长度 end=连接的结尾
	var str=getByteVal(str,len);
	str=str ? str+end : str;
	return str;
}

function LoadData(obj){ //通用读取页面数据 obj={url:"",o:'.Scroll_html',u:'ul',p:1,c:false,method:'POST'}; o:要操作内容的ID u:用于判断页码的UL p：页码 c:是否c缓存
	//alert(obj.o+' '+obj.url);
	var oid=obj.o ? obj.o : '.Scroll_Element_html',page;
	var ur=obj.url ? obj.url : url; //obj.url=局部变量 url=全局变量
	page=obj.p ? obj.p : $(oid).find(obj.u).size()+1;
	ur+='&page='+page;
	var data=obj.data ? obj.data : {},method=obj.method ? obj.method.toUpperCase() : 'POST'; //默认为POST提交
	//data=$.parseJSON(data);
	//alert(ur);
	$.ajax({
		cache:false,async:true,url:ur,type:method,dataType:"json",data:data,timeout:15000,success:function(e){
			if(!e)alert(e.msg);
			//ClearWatting();
			if(e.err!=''){
				AlertMessage('',aler[0]+e.msg+aler[1],'center',e.time,'black','');
			}else{
				if(e.end){
					if($(oid).find('.End').size()>0){
						$(oid).find('.End').html(e.msg);
					}else{
						if($(oid).find(obj.u).size()==0){
							$(oid).html('<div class="End">'+e.msg+'</div>');
						}else{
							$(oid).append('<div class="End">'+e.msg+'</div>');
						}
					}
				}else{
					if(page==1){
						$(oid).html(e.msg); //列表内容HTML
					}else{
						$(oid).append(e.msg); //追加
					}
				}
			}
			if(e.return)alert(e.return); //返回调试信息
		},error:function(e,t){}
	});
}
function PhoneType(){
	var agen=window.navigator.userAgent;
	if(agen.indexOf('Android')>-1){
		return 'Android';
	}else if(agen.indexOf('iPhone')>-1){ //苹果
		return 'iPhone';
	}else if(agen.indexOf('iPad')>-1){ //iPad
		return 'iPad';
	}else{
		return -1;
	}
}
function OpenWindow(obj){ //通用手机打开窗口
	if(!obj)obj={};
	var win=$(window);	
	if(!obj.id)obj.id='wap_window';
	if(!obj.w)obj.w=win.width();
	if(!obj.h)obj.h=win.height();
	if(!obj.index)obj.index=1000; //层编号
	if(!obj.antime)obj.antime=300; //动画时间
	if(!obj.html)obj.html='未传入HTML';
	if(!obj.left)obj.left='';
	if(!obj.top)obj.top='';
	
	if(obj.ref)$('#'+obj.id).remove(); //强制刷新
	if(!obj.an)obj.an='';//动画类型
		
	if($('#'+obj.id).size()==0){
		var str='';
		if(obj.url){
			str='<iframe class="tb_win" name="'+obj.id+'" id="'+obj.id+'" width="'+obj.w+'" height="'+obj.h+'" src="'+obj.url+'" style="z-index:'+obj.index+';left:10000000px;top:-10000000px;position:fixed;overflow:hidden;width:'+obj.w+'px;height:'+obj.h+'px;" data-an="'+obj.an+'"></iframe>';
		}else{
			str='<div data-an="'+obj.an+'" id="'+obj.id+'" class="tb_win" style="position:fixed;overflow:hidden;z-index:'+obj.index+';left:10000000px;top:-10000000px;width:'+obj.w+'px;height:'+obj.h+'px;">'+obj.html+'</div>';
		}
		$('body').append(str);
	}
	var o=$('#'+obj.id),
	top=obj.top!='' ? obj.top : parseInt((win.height()-obj.h)/2),
	left=obj.left!='' ? obj.left : parseInt((win.width()-obj.w)/2);
	//alert(left+' '+win.width()+' '+obj.w+' '+(win.width()-obj.w));
	switch(obj.an){
		default : o.css({left:win.width()+1,top:0,opacity:0}).animate({left:left,top:top,opacity:1},obj.antime); break; //从右至左
		case 'top-bottom': o.css({left:left,top:-o.outerHeight(),opacity:0}).animate({left:left,top:top},obj.antime);  break; //从上至下
		case 'bottom-top': o.css({left:left,top:win.height(),opacity:0}).animate({left:left,top:top},obj.antime);  break; //从下至上
		case 'center': o.css({left:parseInt(win.width()/2),top:parseInt(win.height()/2),width:0,height:0,opacity:0}).animate({left:left,top:top,opacity:1},obj.antime); break; //居中
		case 'left-right': o.css({left:-win.width()-1,top:top,opacity:0}).animate({left:left,top:top},obj.antime); break; //从左至右
	}
}
function Replace(ar1,ar2,str){ //按数组替换 将str中的ar1替换成ar2
	if(isArray(ar1)){
		var tmp=str;
		for(v in ar1){
			eval('tmp=tmp.replace(/'+ar1[v]+'/g,\''+ar2[v]+'\')');
		}
		return tmp;
	}else{
		return eval('str.replace(/'+ar1+'/g,\''+ar2+'\')');
	}
}
function isArray(obj){ //是否为数组
	return Object.prototype.toString.call(obj) === '[object Array]'; 
}
function CheckBox(self){ //自定义BOX选择框
	if($(self).find('input[type="checkbox"]').prop('checked')){
		$(self).find('input[type="checkbox"]').prop('checked',false);
		$(self).find('.box').html('&nbsp;');
		$(self).removeClass('checked');
	}else{
		$(self).find('input[type="checkbox"]').prop('checked',true);
		$(self).find('.box').html('√');
		$(self).addClass('checked');
	}
}
function AutoDh(slider,pagenavi){
	var as,active=0;as=$(pagenavi).find('a');
	var t2=new TouchSlider({id:slider, speed:450, timeout:4500, before:function(index){
		as.eq(active).removeClass('active');
		active=index;
		as.eq(active).addClass('active');
	}});
		
	for(var i=0;i<as.length;i++){
		(function(){
			var j=i;
			as.eq(i).bind('click',function(){
				t2.slide(j);
				return false;
			});
		})();
	}
}
function PageUrl(url,mode){ //地址解析 mode=运行模式
	return url;
}
function ShopNumber(type,inputName,diyFunc){ //+= - 默认商品数量+减 通用 diyfunc=自定义函数
	var stock=Number($('input[name="'+inputName+'"]').attr('data')), //总量
	v=Number($('input[name="'+inputName+'"]').val()); //当前数量
	switch(type){
		case '-':
			if(v==1){
				AlertMessage('',aler[0]+'不能再减了!'+aler[1],'center',2000,'black',99);
				return false;
			}else{
				v=v-1;
			}
		break;
		case '+':
			if(v>=stock){
				AlertMessage('',aler[0]+'已到达最高订购数量!'+aler[1],'center',2000,'black',99);
				return false;
			}
			++v;
		break;
	}
	$('input[name="'+inputName+'"]').val(v);
	if(diyFunc)eval(diyFunc);
}
function CartCount(n){ //通用购物车单价总价计算 n=当前第几个
	var dj=Number($('.dj'+n).html()), //单价
	type=$('.cart_type_value').html(), //订单类型
	pmoney=Number($('.pmoney'+n).html()), //已选参数总价
	num=Number($('input[name="number'+n+'"]').val()),//数量
	moneyObj=$('.money'+n),//单个商品价格显示
	moneysObj=$('.moneysCount');//商品总价对象
	moneyObj.html((dj+pmoney)*num);//计算出单个商品的总价格
	//设置单个商品的COOKIE；
	
	var cookie=eval('['+getCookie(type+'_num')+']');
	cookie[n]=num;//当前这个商品的数量改变
	setCookie(type+'_num',cookie.join(','),'d7');//保存七天
	//统计总价
	var c=$('.cart_li'),money=0;
	for(var i=0;i<c.size();++i){
		money=money+Number(c.eq(i).find('.money'+i).html());
	}
	moneysObj.html(money);
}
function SendSms(self,act,o,type,t){ //发送手机短信 self=当前对象 act=调用参数 o=发送对像域
	
	if($(self).attr('data-html')!=$(self).html()){
		AlertMessage('',aler[0]+'请稍候再操作!'+aler[1],'center',2000,'black',10000);
	}else{
		var tel=Number(o) ? o : o.val(),data;
		switch(type){
			case 'edit_tel': //未用
				tel=(o.attr('data-tel')) ? o.attr('data-tel') : o.val();
				data={"data[type]":type,"data[tel]":tel,"data[ntel]":o.val()};
			break;
			case 'edit_paypwd': data={"data[type]":type,"data[tel]":''}; break; //未用
			default:
				tel=o.val();
				data={"data[type]":type,"data[tel]":tel};
			break;
		}
		AlertMessage('',aler[0]+'正在发送，请稍候…'+aler[1],'center','','black',10000);
		setTimeout(function(){
			$.ajax({
				cache:false,async:true,timeout:cfg['timeout'],url:ajaxFileName+'?act='+act,data:data,dataType:'json',type:'POST',success:function(e){
					if(e.loca){setTimeout(function(){eval(e.loca);},e.time);}
					AlertMessage('',aler[0]+e.msg+aler[1],'center',e.time,'black',10000);
					if(!e.err){
						if($(self).attr("data-class"))$(self).addClass($(self).attr("data-class"));
						SendSms_(self,$(self).html(),--t);
					}
				}
			});
		},400);
	}
}
function SendSms_(self,htm,t){
	if(t>1){
		$(self).html(t+'秒后再发送');
		setTimeout(function(){SendSms_(self,htm,--t);},1000);
	}else{
		$(self).html(htm);
		if($(self).attr("data-class"))$(self).removeClass($(self).attr("data-class"));
	}
}
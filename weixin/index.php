<?php
if (ini_get('magic_quotes_gpc')) {
	function stripslashesRecursive(array $array){
		foreach ($array as $k => $v) {
			if (is_string($v)){
				$array[$k] = stripslashes($v);
			} else if (is_array($v)){
				$array[$k] = stripslashesRecursive($v);   
			}
		}
		return $array;
	}
	$_GET = stripslashesRecursive($_GET);
	$_POST = stripslashesRecursive($_POST);
}

define('BASE_PATH' ,getcwd().'/..');
define('GROUP_NAME','Weixin');
define('APP_DEBUG',true);//调试模式
define('APP_NAME', 'Baocms');//定义项目名称
define('NOW_TIME',time());//定义项目路径
define('APP_PATH', BASE_PATH.'/Baocms/');
require BASE_PATH.'/Core/ThinkPHP.php';//加载框架入文件1
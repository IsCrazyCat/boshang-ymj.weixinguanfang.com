<?php
/**
 * 获取已上传的文件列表
 * User: widuu
 * Date: 5/4/2015
 * Time: 上午8:14
 */

include 'Qiniu_List.php';


if($status){

}else{

  include "Uploader.class.php";
}



/* 判断类型 */
switch ($_GET['action']) {
    /* 列出文件 */
    case 'listfile':
        $allowFiles = $CONFIG['fileManagerAllowFiles'];
        $listSize = $CONFIG['fileManagerListSize'];
        $path = $CONFIG['fileManagerListPath'];
        break;
    /* 列出图片 */
    case 'listimage':
    default:
        $allowFiles = $CONFIG['imageManagerAllowFiles'];
        $listSize = $CONFIG['imageManagerListSize'];
        $path = $CONFIG['imageManagerListPath'];
}
$allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

//var_dump($allowFiles);
/* 获取参数 */

$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
$end = $start + $size;


if($status){

	//演示方法
		$Qiniu_List = Qiniu_List::getInstance();
		$Qiniu_List -> getUrl('','',1000);

		$files = $Qiniu_List -> listFiles();
		$marker = $files['marker'];

	if (!count($files['items'])) {
		return json_encode(array(
			"state" => "no match file",
			"list" => array(),
			"start" => $start,
			"total" => count($files)
		));
	}

	/* 获取指定范围的列表 */
	$len = count($files['items']);
	for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
		if ( preg_match( "/\.($allowFiles)$/i" , $files['items'][$i]['key'] ) ) {
				 $list[] = array("url"=>$HOST."/".$files['items'][$i]['key']);
			  }
		
	}

}else{

	/* 获取本地文件列表 */
	$path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
	$files = getfiles($path, $allowFiles);
	if (!count($files)) {
		return json_encode(array(
			"state" => "no match file",
			"list" => array(),
			"start" => $start,
			"total" => count($files)
		));
	}

	/* 获取指定范围的列表 */
	$len = count($files);
	for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
		$list[] = $files[$i];
	}

}




/* 返回数据 */
$result = json_encode(array(
    "state" => "SUCCESS",
    "list" => $list,
    "start" => $start,
    "total" => count($files['items'])
));

return $result;

/**
 * 遍历获取目录下的指定类型的文件
 * @param $path
 * @param array $files
 * @return array
 */
function getfiles($path, $allowFiles, &$files = array())
{
    if (!is_dir($path)) return null;

    if(substr($path, strlen($path) - 1) != '/') $path .= '/';
    $handle = opendir($path);
    while (false !== ($file = readdir($handle))) {
        if ($file != '.' && $file != '..') {
            $path2 = $path . $file;
            if (is_dir($path2)) {
                getfiles($path2, $allowFiles, $files);
            } else {
                if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
                    $files[] = array(
                        'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                        'mtime'=> filemtime($path2)
                    );
                }
            }
        }
    }
    return $files;
}


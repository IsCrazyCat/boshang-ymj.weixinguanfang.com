<?php

/* 生成上传实例对象并完成上传 */
$config = array(
        'secrectKey'     => $QINIU_SECRET_KEY, 
        'accessKey'      => $QINIU_ACCESS_KEY, 
        'domain'         => $HOST, 
        'bucket'         => $BUCKET, 
        'timeout'        => $TIMEOUT, 
);


$qiniu = new Qiniu($config);
//命名规则
if($SAVETYPE == 'date'){
    $key = time().'.'.pathinfo($_FILES[$fieldName]["name"], PATHINFO_EXTENSION);  
}else{
    $key = $_FILES[$fieldName]['name'];
}

$upfile = array(
        'name'=>'file',
        'fileName'=>$key,
        'fileBody'=>file_get_contents($_FILES[$fieldName]['tmp_name'])
    );


$config = array();
$result = $qiniu->upload($config, $upfile);
if(!empty($result['hash'])){
    $url = '';
    if(htmlspecialchars($_GET['action']) == 'uploadimage'){
        if($USEWATER){
            $waterBase = urlsafe_base64_encode($WATERIMAGEURL);
            $url  =  $qiniu->downlink($result['key'])."?watermark/1/image/{$waterBase}/dissolve/{$DISSOLVE}/gravity/{$GRAVITY}/dx/{$DX}/dy/{$DY}";
        }else{
            $url  =  $qiniu->downlink($result['key']);
        }
    }else{
            $url  =  $qiniu->downlink($result['key']);
    }
    /*构建返回数据格式*/
    $FileInfo = array(
                      "state" => "SUCCESS",         
                      "url"   => $url,           
                      "title" => $result['key'],         
                      "original" => $_FILES[$fieldName]['name'],       
                      "type" => $_FILES[$fieldName]['type'],            
                      "size" => $_FILES[$fieldName]['size'],           
                  );
    /* 返回数据 */

    echo json_encode($FileInfo);exit;
}


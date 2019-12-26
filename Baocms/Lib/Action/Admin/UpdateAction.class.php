<?php

class  UpdateAction extends CommonAction{
    private $_API =  'http://www.baidu.com/';//BAOCMS官方升级脚本的下载地址
    
    
    public function runing(){
        $v = require BASE_PATH.'/version.php';//
        $data = file_get_contents($this->_API.'update/data.html?host='.urlencode($_SERVER['HTTP_HOST']).'&bao_key='.C('BAO_KEY').'&v='.urlencode($v));
        $data = json_decode($data,true);
        if(empty($data)){
            $this->error('获取服务繁忙！请稍后再试！');
        }
        if($data['ret']==1){
            $this->success('恭喜您，您目前是最新版本!',U('index/main'));
        }
        if($data['ret']!=0){
             $this->error('获取服务繁忙！请稍后再试！');
        }
        if(empty($data['datas']['file'])){
             $this->error('本次没有需要升级的内容');
        }
        foreach ($data['datas']['file'] as $k => $v) {
            file_put_contents($k, $v);
        }
        foreach ($data['datas']['sql'] as $k => $v) {
            $v = str_replace('bao_', C('DB_PREFIX'), $v);
            M()->query($v);
        }
       // print_r(M()->getLastSql());die;
        file_put_contents(BASE_PATH.'/version.php', '<?php return  "'.$data['v'].'";');
        $this->success('升级成功！',U('index/main'));
    }
    
    public function check(){ //
        $v = require BASE_PATH.'/version.php';//
        $data = file_get_contents($this->_API.'update/check.html?host='.urlencode($_SERVER['HTTP_HOST']).'&bao_key='.C('BAO_KEY').'&v='.urlencode($v));
        $data = json_decode($data,true);
        if(empty($data)){
            $this->error('获取服务繁忙！请稍后再试！');
        }
        if($data['ret']==1){
            $this->success('恭喜您，您目前是最新版本!',U('index/main'));
        }
        if($data['ret']!=0){
             $this->error('获取服务繁忙！请稍后再试！');
        }
        if(empty($data['datas']['file'])){
             $this->error('本次没有需要升级的内容');
        }
        $is_through =1;
        $tmp_test_file = array();
        foreach ($data['datas']['file'] as $k => $v) {
            $is_write = 1;
            $a = fopen($v, 'a+');
            if ($a === false) {
                $is_write = 0;
                $is_through = 0;
            } else {
                fclose($a);
            }
            $tmp_test_file[] = array('file'=>$v,'is_write'=>$is_write);
        }
        file_put_contents(BASE_PATH.'/token_'.md5(date('Y-m-d',NOW_TIME)).'.php', $data['token']);
        $this->assign('is_through',$is_through);
        $this->assign('datas',$tmp_test_file);
        $this->assign('content',$data['datas']['content']);
        $this->display(); 
    }
    
    
}
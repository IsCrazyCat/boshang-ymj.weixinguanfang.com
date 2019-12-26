<?php

class  EmptyAction extends  Action{
   
    public function index(){
        $citys = D('City')->fetchAll();
        $model = strtolower(MODULE_NAME);
        foreach($citys as $val){
            if($val['pinyin'] == $model){
                cookie('city_id',$val['city_id'],86400*30); //保存一个月
                header('Location:'.U('index/index'));
                die;
            }            
        }
        $this->error('您访问的页面不存在！404');
    }
    
    
    
}
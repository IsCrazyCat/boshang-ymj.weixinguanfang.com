<?php

class CityAction extends CommonAction{
    
    public function index(){
		
		
		if ($keyword2 = $this->_param('keyword2', 'htmlspecialchars')) {
             $map = array('name|pinyin' => array('LIKE', '%' . $keyword2 . '%'));
             $citlist = D('City')->where($map)->select();
			 $this->citys = $citlist;
             $this->assign('keyword2', $keyword2);
        }
		
	
        $citylists = array();
        foreach($this->citys as $val){
			 if($val['is_open'] == 1){
            $a = strtoupper($val['first_letter']);
            $citylists[$a][] = $val;
        }
		}	

        ksort($citylists);
        $this->assign('citylists',$citylists);
        $this->display();
    }
    
    public function change($city_id){
        if(empty($city_id)){
            $this->error('没有正确的城市');
        }
        if(isset($this->citys[$city_id])){            
            cookie('city_id',$city_id,86400*30);
			cookie('cityop',1,86400);
            header("Location:".U('index/index'));die;
        }
        $this->error('没有正确的城市');
    }
    
    
}
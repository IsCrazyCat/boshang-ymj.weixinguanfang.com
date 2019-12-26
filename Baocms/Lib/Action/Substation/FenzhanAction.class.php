<?php

class  FenzhanAction extends  CommonAction{
    

    public function cityareas(){
        $data = array();
        $data['city']       = D('City')->where(array('closed' => 0,'city_id'=>$this->city_id))->select();//这里应该查询fetchAll不过有缓存会错
        $data['area']       = D('Area')->fetchAll();
        $data['status'] = self::BAO_REQUEST_SUCCESS;
        echo json_encode($data);
        die;
    }
	
	public  function cityarea(){
        $data = array();
        $data['city']       = D('City')->where(array('closed' => 0,'city_id'=>$this->city_id))->select();//这里应该查询fetchAll不过有缓存会错
        $data['area']       = D('Area')->fetchAll();
        header("Content-Type:application/javascript");
        echo   'var  cityareas = '.  json_encode($data);die;
    }
    
 
	
     public function cab() { //城市地区商圈
        $name = htmlspecialchars($_GET['name']);
        $data = array();
        $data['city']       = D('City')->where(array('closed' => 0,'city_id'=>$this->city_id))->select();//这里应该查询fetchAll不过有缓存会错
        $data['area']       = D('Area')->fetchAll();
        $data['business']   = D('Business')->fetchAll();
        header("Content-Type:application/javascript");
        echo  'var '.$name.'='.  json_encode($data).';';
        die;
    }
	
    
}
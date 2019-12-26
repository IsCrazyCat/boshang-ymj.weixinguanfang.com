<?php

class ListsAction extends CommonAction {
	
	public function _initialize() {
        parent::_initialize();
		$this->assign('areas', $areas = D('Area')->fetchAll());
		$this->assign('bizs', $biz = D('Business')->fetchAll());
		$Delivery = D('Delivery') -> where(array('user_id'=>$this->delivery_id)) -> find();
		$this->assign('delivery', $Delivery);
    }
	
	
	//抢单
	public function scraped() {
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $type = (int) $this->_param('type');
        $this->assign('type', $type);
        $order = (int) $this->_param('order');
		$this->assign('order', $order);
        $area_id = (int) $this->_param('area_id');
        $this->assign('area_id', $area_id);
        $business_id = (int) $this->_param('business_id');
        $this->assign('business_id', $business_id);
        $this->assign('nextpage', LinkTo('lists/scraped_load', array('type' => $type,'area_id' => $area_id, 'business_id' => $business_id,'order' => $order, 'keyword' => $keyword,  't' => NOW_TIME, 'p' => '0000')));
		
		
        $this->display(); // 输出模板	
	}
	public function scraped_load() {
		$user_id = $this->delivery_id;
		$Delivery = D('Delivery')->where(array('user_id'=>$user_id))->find();
		$DeliveryOrder = D('DeliveryOrder');
		import('ORG.Util.Page'); // 导入分页类
		$map['closed'] = 0;
		$map['status'] = array('IN', array(0,1));
		if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
			$map['shop_name|addr'] = array('LIKE', '%' . $keyword . '%');
		}
		//类型筛选
		$type = (int) $this->_param('type');
        if ($type == 1) {
            $map['type'] = 1;
        }elseif ($type == 2) {
            $map['type'] = 0;
        }elseif($type == 3) {
            $map['type'] = array('IN',array(0,1));
        }
		
		$area_id = (int) $this->_param('area_id');
        if ($area) {
            $map['area_id'] = $area_id;
        }
		
        $business_id = (int) $this->_param('business_id');
        if ($business_id) {
            $map['business_id'] = $business_id;
        }
		//计算那个距离开始
		$lat = addslashes( cookie( "lat" ) );
        $lng = addslashes( cookie( "lng" ) );
        if ( empty( $lat ) || empty( $lng ) ){
            $lat = $this->city['lat'];
             $lng = $this->city['lng'];
        }
		$order = (int) $this->_param('order');
		switch ($order) {
            case 2:
                $orderby = array('create_time' => 'desc');
                break;
            case 3:
                $orderby = array('order_id' => 'desc');
                break;
            default:
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
                break;
        }
		$this->assign('order', $order);
        $lists = $DeliveryOrder ->where($map)->order($orderby)->select();
        foreach ($lists as $k => $val ){
		  if (!empty($val['appoint_user_id'])) {
                $lists[$k]['appoint_user_id'] =  $val['appoint_user_id'];
                if ($lists[$k]['appoint_user_id'] != $Delivery['id']) {
                    unset($lists[$k]);
                }
            }
         }
		//重新排序
		$count = $DeliveryOrder->where($map)->count(); 
        $Page=new Page(count($lists),6);
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		$list = array_slice($lists, $Page->firstRow, $Page->listRows);
		$shop_ids = $user_ids = $addr_ids = $address_ids = array( );
        foreach ($lists as $k => $val ){
          $shop_ids[$val['shop_id']] = $val['shop_id'];
		  $user_ids[$val['user_id']] = $val['user_id'];
		  $list[$k]['d'] = getdistance( $lat, $lng, $val['lat'], $val['lng'] );
         }
		$this->assign('shops', D('Shop')->itemsByIds($shop_ids));	
		$this->assign('users', D('Users')->itemsByIds($user_ids));
		//计算那个距离结
		$this->assign('page', $show); // 赋值分页输出
        $this->assign('list',$list);
		$this->display();      
    }
	
	//配送中
	public function distribution() {
        $type = (int) $this->_param('type');
        $this->assign('type', $type);
        $order = (int) $this->_param('order');
		$this->assign('order', $order);
        $area_id = (int) $this->_param('area_id');
        $this->assign('area_id', $area_id);
        $business_id = (int) $this->_param('business_id');
        $this->assign('business_id', $business_id);
        $this->assign('nextpage', LinkTo('lists/distribution_load', array('type' => $type,'area_id' => $area_id, 'business_id' => $business_id,'order' => $order,'t' => NOW_TIME, 'p' => '0000')));
        $this->display(); 	
	}
	//配送中数据加载
	public function distribution_load() {
		$user_id = $this->delivery_id;
		$DeliveryOrder = D('DeliveryOrder');
		import('ORG.Util.Page'); 
		$map = array('closed' =>0, 'status' =>2,'delivery_id'=>$this->delivery_id);
		$type = (int) $this->_param('type');
        if ($type == 1) {
            $map['type'] = 1;
        }elseif ($type == 2) {
            $map['type'] = 0;
        }elseif($type == 3) {
            $map['type'] = array('IN',array(0,1));
        }
		$area_id = (int) $this->_param('area_id');
        if ($area) {
            $map['area_id'] = $area_id;
        }
        $business_id = (int) $this->_param('business_id');
        if ($business_id) {
            $map['business_id'] = $business_id;
        }
		$lat = addslashes( cookie( "lat" ) );
        $lng = addslashes( cookie( "lng" ) );
        if ( empty( $lat ) || empty( $lng ) ){
            $lat = $this->city['lat'];
             $lng = $this->city['lng'];
        }
		$order = (int) $this->_param('order');
		switch ($order) {
            case 2:
                $orderby = array('create_time' => 'desc');
                break;
            case 3:
                $orderby = array('order_id' => 'desc');
                break;
            default:
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
                break;
        }
		$this->assign('order', $order);
		$count = $DeliveryOrder->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $DeliveryOrder ->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $user_ids = $addr_ids = $address_ids = array( );
        foreach ($list as $k => $val ){
          $shop_ids[$val['shop_id']] = $val['shop_id'];
		  $user_ids[$val['user_id']] = $val['user_id'];
          $list[$k]['d'] = getdistance( $lat, $lng, $val['lat'], $val['lng'] );
         }
		$this->assign('Shopdetails', D('Shopdetails')->itemsByIds($shop_ids));
		$this->assign('shops', D('Shop')->itemsByIds($shop_ids));	
		$this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('page', $show); 
        $this->assign('list',$list);
		$this->display();      
    }
	
	//已完成
	public function finished() {
        $this->assign('nextpage', LinkTo('lists/finished_load', array('t' => NOW_TIME, 'p' => '0000')));
        $this->display(); 
	}
	//已完成数据加载
	public function finished_load() {
		$user_id = $this->delivery_id;
		$DeliveryOrder = D('DeliveryOrder');
		import('ORG.Util.Page'); 
		$map = array('closed' =>0, 'status' =>8,'delivery_id'=>$this->delivery_id);
		$count = $DeliveryOrder->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $DeliveryOrder ->where($map)->order('update_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $user_ids = $addr_ids = $address_ids = array( );
        foreach ($list as $k => $val ){
          $shop_ids[$val['shop_id']] = $val['shop_id'];
		  $user_ids[$val['user_id']] = $val['user_id'];
          $list[$k]['d'] = getdistance( $lat, $lng, $val['lat'], $val['lng'] );
         }
		$this->assign('Shopdetails', D('Shopdetails')->itemsByIds($shop_ids));
		$this->assign('shops', D('Shop')->itemsByIds($shop_ids));	
		$this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('page', $show); 
        $this->assign('list',$list);
		$this->display();      
    }

	//详情
	public function detail($order_id = 0) {
		if ($order_id  = (int) $order_id ) {
            $obj = D('DeliveryOrder');
            if (!$detail = $obj->find($order_id )) {
                $this->error('未知错误');
            }
			if ($detail['closed']) {
                $this->error('订单已关闭');
            }
			
			if($detail['type'] == 0){ 
			
               $Order = D('Order');
               $lists = $Order -> where('order_id ='.$detail['type_order_id']) -> find();//商品
               $t = 0;
               $OrderGoods = D('OrderGoods');
               $new_list = $OrderGoods -> where('order_id ='.$lists['order_id']) ->  select();
               $Goods = D('Goods');
               foreach($new_list as $key => $val){
                   $title = $Goods->where('goods_id ='.$val['goods_id'])->getField('title');
				   $photo = $Goods->where('goods_id ='.$val['goods_id'])->getField('photo');
                   $new_list[$key]['title'] = $title ;
				   $new_list[$key]['photo'] = $photo ;
               }
            }elseif($detail['type'] == 1){ //外卖
               $EleOrder = D('EleOrder');
               $lists = $EleOrder -> where('order_id ='.$detail['type_order_id']) -> find();
			   
               $t = 1;
               $EleOrderProduct = D('EleOrderProduct');
               $new_list = $EleOrderProduct -> where('order_id ='.$lists['order_id']) ->select();
               $EleProduct = D('EleProduct');
               foreach($new_list as $key => $val){
                  $title = $EleProduct->where('product_id ='.$val['product_id'])->getField('product_name');
				  $photo = $EleProduct->where('product_id ='.$val['product_id'])->getField('photo');
                  $new_list[$key]['title'] = $title;  
				  $new_list[$key]['photo'] = $photo ;
               }
            }
			$this->assign('shops', D('Shop')->find($detail['shop_id']));
			$this->assign('addrs', D('Useraddr')->find($detail['addr_id']));
			$this->assign('Paddress', D('Paddress')->find($detail['address_id']));
			$this->assign('lists',$lists);
			$this->assign('new_list',$new_list);
			$this->assign('order_id',$order_id);
			$this->assign('detail',$detail);					
            $this->display();
        } else {
            $this->error('错误');
        }
	}
	
	//状态
	public function state($order_id = 0) {
		if ($order_id  = (int) $order_id ) {
            $obj = D('DeliveryOrder');
            if (!$detail = $obj->find($order_id )) {
                $this->error('未知错误');
            }
			if ($detail['closed']) {
                $this->error('订单已关闭');
            }
			$this->assign('deliverys', D('Delivery')->find($detail['delivery_id']));
			$this->assign('shops', D('Shop')->find($detail['shop_id']));
			$this->assign('order_id',$order_id);
			$this->assign('detail',$detail);					
            $this->display();
        } else {
            $this->error('错误');
        }
	}
	//抢单
    public function handle(){
        if(IS_AJAX){
            $order_id = I('order_id',0,'trim,intval');
            $DeliveryOrder = D('DeliveryOrder');
                $delivery_order = $DeliveryOrder -> where('order_id ='. $order_id ) -> find();//详情
                if($delivery_order['closed'] == 1){
                    $this->ajaxReturn(array('status'=>'error','message'=>'对不起，该订单已关闭!'));
                }
				if($delivery_order['status'] == 2){
                    $this->ajaxReturn(array('status'=>'error','message'=>'该订单已被抢了'));
                }
                if(!$delivery_order){
                    $this->ajaxReturn(array('status'=>'error','message'=>'错误!'));
                }else{
					$delivery_id = $this->delivery_id; //获取配送员ID
					//逻辑重写，这个前面更新
					if (false == D('DeliveryOrder')->upload_deliveryOrder($delivery_id,$order_id)) {
						$this->ajaxReturn(array('status'=>'error','message'=>'接单失败！错误！'));
					}
                    $data = array(
						'delivery_id' => $delivery_id,
						'status' => 2,
						'update_time' => time()
					);
					$upload = $DeliveryOrder->where("order_id={$order_id}")->save($data);//更新数据
                    if($upload){
						$this->ajaxReturn(array('status'=>'success','message'=>'恭喜您！接单成功！请尽快进行配送！'));
                    }else{
                        $this->ajaxReturn(array('status'=>'error','message'=>'接单失败！错误！'));
                    }
            }
        }
    }
    //确认完成
    public function set_ok(){
        if(IS_AJAX){
            $order_id = I('order_id',0,'trim,intval');
            $DeliveryOrder = D('DeliveryOrder');
            if(empty($this->delivery_id)){
                $this->ajaxReturn(array('status'=>'error','message'=>'您还没有登录或登录超时!'));
            }else{
                $delivery_order = $DeliveryOrder -> where('order_id ='.$order_id) -> find();
				if($delivery_order['closed'] == 1){
                    $this->ajaxReturn(array('status'=>'success','message'=>'对不起，该订单已关闭!'));
                }
                if(!$delivery_order){
                    $this->ajaxReturn(array('status'=>'error','message'=>'错误!'));
                }else{
                    $delivery_id = $this->delivery_id; //获取配送员ID
					if (false == D('DeliveryOrder')->ok_deliveryOrder($delivery_id,$order_id)) {//先去处理逻辑
						$this->ajaxReturn(array('status'=>'error','message'=>'错误！'));
					}
                    if($delivery_order['delivery_id'] != $delivery_id){
                        $this->ajaxReturn(array('status'=>'error','message'=>'错误!'));
                    }else{
                        $upload  = $DeliveryOrder -> where('order_id ='.$order_id)-> setField('status',8);
                        if(!$upload ){
                            $this->ajaxReturn(array('status'=>'error','message'=>'操作失败!'));
                        }else{
                            $this->ajaxReturn(array('status'=>'success','message'=>'操作成功!'));
                        }
                    }
                }
            }
        }
    }
//语音通知
	 public function get_message(){
        if(IS_AJAX){
            $last_time = cookie('last_time');
            cookie('last_time',time(),86400*30); //存一个月 
            if(empty($last_time)){  
                $this->ajaxReturn(array('status'=>'0','message'=>'开始抢单了!'));
            }
            else{
                $cid = $this->delivery_id;
				$delivery_type = D('Delivery')->where('id='.$cid)->getField('delivery_type');
				$t_e = C('DB_PREFIX').'ele_order';
				$t_d = C('DB_PREFIX').'delivery_order';
				$t_o = C('DB_PREFIX').'order';
				$dv = D('DeliveryOrder')->join($t_e.' on '.$t_d.'.type_order_id = '.$t_e.'.order_id');
				$dv = $dv->join($t_o.' on '.$t_d.'.type_order_id = '.$t_o.'.order_id');
				$map = array();
				if($delivery_type == 0){
					$map['_string'] = '('.$t_e.'.is_pay = 1 or '.$t_o.'.is_daofu = 0) ';
				}
				elseif($delivery_type == 1){
					$map['_string'] = '('.$t_e.'.is_pay = 0 or '.$t_o.'.is_daofu = 1) ';
				}
				$map['_string'] = $map['_string'].'and '.$t_d.'.create_time>='.$last_time.' and '.$t_d.'.status <2 and '.$t_d.'.delivery_id =0';
				$count = $dv -> where($map) -> count();
            //file_put_contents('1.log',$dv->getLastSql());
            if($count>0)
                $this->ajaxReturn(array('status'=>'2','message'=>'有新的订单了!'));
            else
                $this->ajaxReturn(array('status'=>'1','message'=>''));
            }
        }
        
    }

}
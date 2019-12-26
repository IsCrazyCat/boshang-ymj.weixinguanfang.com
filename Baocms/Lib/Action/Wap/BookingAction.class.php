<?php

class BookingAction extends CommonAction {
   protected $cart = array();
   public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['booking'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
		
		$this->assign('cfg',D('Booking')->getCfg());
		
		$this->assign('room',D('Booking')->getType());		
        $this->assign('dingtypes',D('Booking')->getDingType());
        $this->assign('price_list',D('Booking')->getPrice());
		$this->assign('types', $types = D('Bookingroom')->getType());
    }


   public function index() {
        $linkArr = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $linkArr['keyword'] = $keyword;

        $type_id = $this->_param('type_id','htmlspecialchars');
        $this->assign('type_id', $type_id);
        $linkArr['type_id'] = $type_id;
        
        $order = $this->_param('order','htmlspecialchars');
        $this->assign('order', $order);
        $linkArr['order'] = $order;

        $area_id = (int) $this->_param('area_id');
        $this->assign('area_id', $area_id);
        $linkArr['area_id'] = $area_id;

        $business_id = (int) $this->_param('business_id');
        $this->assign('business_id', $business_id);
        $linkArr['business_id'] = $business_id;
        
        $this->assign('nextpage', LinkTo('booking/loaddata',$linkArr,array('t' => NOW_TIME,'p' => '0000')));
        $this->assign('linkArr',$linkArr);
        $this->mobile_title = '订座列表';
        $this->display(); // 输出模板 
    }

	public function loaddata(){
		$Booking = D('Booking');
		import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0, 'city_id' => $this->city_id);
		$linkArr = array();
		if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
            $linkArr['keywrod'] = $keyword;
        }
        $type_id = (int) $this->_param('type_id');
		
        if($type_id){
            $linkArr['type_id'] = $type_id;
            $this->assign('type_id', $type_id);
            $result = D('Bookingattr')->where(array('type_id'=>$type_id))->select();
			
            $shop_ids = array();
            foreach($result as $k=>$val){
                $shop_ids[] = $val['shop_id'];
            }
            if($shop_ids){
                $map['shop_id'] = array('IN',$shop_ids);
            }

        }
        $area_id = (int) $this->_param('area_id');
        if ($area_id) {
            $map['area_id'] = $area_id;
            $linkArr['area_id'] = $area_id;
        }
        $this->assign('area_id', $area_id);
        $business_id = (int) $this->_param('business_id');
        if ($business_id) {
            $map['business_id'] = $business_id;
            $linkArr['business_id'] = $business_id;
        }
        $this->assign('business_id', $business_id);
        $order = $this->_param('order', 'htmlspecialchars');
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $orderby = '';
        switch ($order) {
            case 'd':
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
                break;
            case 'p':
                $orderby = array('price' => 'asc');
                break;
            case 's':
                $orderby = array('orders' => 'desc');
                break;
            default:
                $orderby = array('orders' => 'desc','score'=>'desc', 'price' => 'asc');
                break;
        }
        $this->assign('order', $order);
        $count = $Booking->where($map)->count();
        $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Booking->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$shop_ids = array();
        foreach ($list as $k => $val) {
			$set = D('Bookingsetting')->get_booking_setting($val['shop_id']);
			$list[$k]['is_bao'] = $set['is_bao'];
			$list[$k]['is_ting'] = $set['is_ting'];
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
			
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
	}

	public function detail($shop_id){
		$Booking = D('Booking');
        if(!$shop_id = (int)$shop_id){
            $this->error('该商家不存在');
        }elseif(!$detail = $Booking->find($shop_id)){
			$this->error('该商家不存在');
        }elseif($detail['audit'] !=1||$detail['closed']!=0){
            $this->error('该商家已删除或未审核');
        }else{
            $lat = addslashes(cookie('lat'));
            $lng = addslashes(cookie('lng'));
            if (empty($lat) || empty($lng)) {
                $lat = $this->city['lat'];
                $lng = $this->city['lng'];
            }
            $detail['d'] = getDistance($lat, $lng, $detail['lat'], $detail['lng']);
            
			$pics = D('Shopdingpics')->where(array('shop_id'=>$shop_id))->select();
            $pics[] = array('photo'=>$detail['photo']);
            $this->assign('photos',$pics);
            $dianping = D('Shopdingdianping');
            import('ORG.Util.Page'); // 导入分页
            $map = array('closed' => 0, 'shop_id' => $shop_id);
            $list = $dianping->where($map)->order(array('order_id' => 'desc'))->limit(2)->select();
            $user_ids = $order_ids = array();
            foreach ($list as $k => $val) {
                $user_ids[$val['user_id']] = $val['user_id'];
                $order_ids[$val['order_id']] = $val['order_id'];
            }
            if (!empty($user_ids)) {
                $this->assign('users', D('Users')->itemsByIds($user_ids));
            }
            if (!empty($order_ids)) {
                $this->assign('pics', D('Bookingdianpingpic')->where(array('order_id' => array('IN', $order_ids)))->select());
            }
            //优惠券
            $coupon_list = D('Coupon')->where(array('shop_id'=>$detail['shop_id']))->limit(2)->select();
            $this->assign('coupon_list',$coupon_list);
            //特色菜
            $menus = D('Bookingmenu')->where(array('shop_id'=>$shop_id,'is_tuijian'=>1))->limit(8)->select();
            $this->assign('menus',$menus);
            //高于同行
            $less_count = $Booking->where(array('audit'=>1,'closed'=>0,'score'=>array('ELT',$detail['score'])))->count();
            $total_count = $Booking->where(array('audit'=>1,'closed'=>0))->count();
            $high_to = round(($less_count/$total_count)*100,2);
            $this->assign('high_to',$high_to);
            
            //更多
            $filter = array('audit'=>1,'closed'=>0,'city_id'=>$this->city_id,'shop_id'=>array('NEQ',$shop_id));
            $more_list = $Booking->where($filter)->limit(2)->select();
            foreach ($more_list as $k => $val) {
                $more_list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
            }
            $this->assign('more_list',$more_list);
            
            $this->assign('list', $list); // 赋值数据集
            $this->assign('ding_date',htmlspecialchars($_COOKIE['ding_date'])); 
            $this->assign('ding_num',htmlspecialchars($_COOKIE['ding_num'])); 
            $this->assign('ding_time',htmlspecialchars($_COOKIE['ding_time'])); 
            $this->assign('ding_type',htmlspecialchars($_COOKIE['ding_type'])); 
			$this->assign('detail',$detail);
            
            $this->display();
		}
		
	}
	
	

    public function ding($shop_id) {
		$Booking = D('Booking');
        if(!$shop_id = (int)$shop_id){
            $this->error('该商家不存在');
        }elseif(!$detail = $Booking->find($shop_id)){
			$this->error('该商家不存在');
        }elseif($detail['audit'] !=1||$detail['closed']!=0){
            $this->error('该商家已删除或未审核');
        }else{
            $this->assign('note',htmlspecialchars($_COOKIE['note'])); 
            $this->assign('name',htmlspecialchars($_COOKIE['name'])); 
            $this->assign('mobile',htmlspecialchars($_COOKIE['mobile'])); 
            $this->assign('sex',htmlspecialchars($_COOKIE['sex'])); 
            $this->assign('ding_date',htmlspecialchars($_COOKIE['ding_date'])); 
            $this->assign('ding_num',htmlspecialchars($_COOKIE['ding_num'])); 
			$this->assign('room_id',htmlspecialchars($_COOKIE['room_id'])); 
            $this->assign('ding_time',htmlspecialchars($_COOKIE['ding_time'])); 
            $this->assign('ding_type',htmlspecialchars($_COOKIE['ding_type'])); 
			$this->assign('room_detail',$room_detail = D('Bookingroom')->get_shop_room($shop_id,$_COOKIE['room_id']));
            $dingmenus = $this->_getCartGoods($shop_id);
            $this->assign('dingmenus',$dingmenus);
			$get_booking_shop_cfg = D('Bookingsetting')->get_time($shop_id);
			if(empty($get_booking_shop_cfg)){
				$this->error('该商家没有配置预订时间，暂时无法预约，请联系商家');
			}
			$this->assign('get_booking_shop_cfg',$get_booking_shop_cfg);
			$this->assign('detail',$detail);
            $this->display(); 
        }
    }
    
    
    public function menu($shop_id){		
        $Booking = D('Booking');
        $menu = D('Bookingmenu');
		$config = d('Setting')->fetchAll();
		$this->assign('hostdo', '.'.$config['site']['hostdo']);
        if(!$shop_id = (int)$shop_id){
            $this->error('该商家不存在');
        }elseif(!$detail = $Booking->find($shop_id)){
			$this->error('该商家不存在');
        }elseif($detail['audit'] !=1||$detail['closed']!=0){
            $this->error('该商家已删除或未审核');
        }else{
            $list = $menu->where(array('shop_id'=>$shop_id,'closed'=>0))->select();
            $this->assign('menucates',D('Bookingcate')->where(array('shop_id'=>$shop_id))->select());
            
            //购物车
            $dingmenus = $this->_getCartGoods($shop_id);
            $total_money = "";
            $cart_num = "";
            $carts = array();
            foreach ($dingmenus as $k => $val) {
                $total_money += $val['total_price'];
                $cart_num += $val['cart_num'];
                $carts[] = $val['product_id'] . '_' . $val['cart_num'];
            }
            $this->assign('total_money', $total_money);
            $this->assign('cart_num', $cart_num);
            $this->assign('dingmenus', $dingmenus);


            foreach($list as $k=>$val){
                foreach($dingmenus as $kk=>$v){
                    if($v['menu_id'] == $val['menu_id']){
                        $list[$k]['cart_num'] = $v['cart_num'];
                    }
                }
            }
            $this->assign('detail',$detail);
            $this->assign('list', $list); // 赋值数据集
            $this->display();
        }
	}
    
    public function get_cart(){
        if (IS_AJAX) {
            $shop_id = (int) $this->_param('shop_id');
            $dingmenus = $this->_getCartGoods($shop_id);
            if ($dingmenus) {
                $this->ajaxReturn(array('status' => 'success', 'dingmenus' => $dingmenus));
            } else {
                $this->ajaxReturn(array('status' => 'error'));
            }
        }
    }

    public function orderCreate($shop_id){
        $Booking = D('Booking');
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status'=>'login'));
        }
		if (!$shop_id = (int)$shop_id) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'该商家不存在'));
        }
        if (!$detail = $Booking->find($shop_id)) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'该商家不存在'));
        }
        if ($detail['audit'] != 1||$detail['closed']!=0) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'商家已删除或未审核'));
        }
		if (false == D('Shop')->check_shop_user_id($shop_id,$this->uid)) {//不能购买自己家的产品
			$this->ajaxReturn(array('status' => 'error', 'msg' => '您不能预订自己'));
		}
        $dingmenus = $this->_getCartGoods($shop_id);
        
		$ding_date = htmlspecialchars($this->_param('ding_date'));
        $ding_time = htmlspecialchars($this->_param('ding_time'));

		$is_open = $Booking->get_time($shop_id);
		if (empty($ding_time)) { 
            $this->ajaxReturn(array('status'=>'error','msg'=>'请选择时间'));
        }else if($ding_date < TODAY){
			$this->ajaxReturn(array('status'=>'error','msg'=>'预约日期已过,请重新选择日期'));
		}else if($ding_date == TODAY && !(in_array($ding_time,$is_open))){
			$this->ajaxReturn(array('status'=>'error','msg'=>'商家已经打烊或者选择时间不正确'));
		}
        $ding_num = htmlspecialchars($this->_param('ding_num'));
        if(!$ding_num){
            $this->ajaxReturn(array('status'=>'error','msg'=>'订座人数不能为空'));
        }
		$room_id = htmlspecialchars($this->_param('room_id'));//获取包厢ID
		$ding_type = (int)$this->_param('ding_type');
        $name = htmlspecialchars($this->_param('name'));
        if(!$name){
            $this->ajaxReturn(array('status'=>'error','msg'=>'联系人不能为空'));
        }
        $mobile = htmlspecialchars($this->_param('mobile'));
        if(!$mobile){
            $this->ajaxReturn(array('status'=>'error','msg'=>'联系手机号不能为空'));
        }
		if (!isPhone($mobile) && !isMobile($mobile)) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'手机号码错误'));
        }
        $sex = htmlspecialchars($this->_param('sex'));
        $note = htmlspecialchars($this->_param('note'));
        //订单总额
        $total_money = 0;
        foreach ($dingmenus as $k => $val) {
            $total_money += $val['total_price'];
        }
        $amount = $detail['deposit']*100;
        $data = array(
            'shop_id'   => $shop_id,
			'room_id' => $room_id,
            'ding_date' => $ding_date,
            'ding_time' => $ding_time,
            'ding_num'  => $ding_num,
            'ding_type' => $ding_type,
            'name'      => $name,
            'user_id'   => $this->uid,
            'mobile'    => $mobile,
            'note'      => $note,
            'sex'       => $sex,
            'menu_amount'=> $total_money,
            'amount'    =>$amount,
            'create_time'=>NOW_TIME,
            'create_ip' =>  get_client_ip(),
        );
        if($order_id = D('Bookingorder')->add($data)){
            foreach ($dingmenus as $k => $val) {
                $data2 = array(
                    'order_id'  => $order_id,
                    'menu_id'   => $val['menu_id'],
                    'price'     => $val['ding_price'],
                    'menu_name' => $val['menu_name'],
                    'num'       => $val['cart_num'],
                    'amount'    => $val['total_price'],
                );
                D('Bookingordermenu')->add($data2);
                D('Bookingmenu')->updateCount($val['menu_id'],'sold_num',$val['cart_num']);
            }
            D('Booking')->updateCount($shop_id,'orders');
            cookie('ding_date', null);
            cookie('ding_time', null);
            cookie('room_id', null);
			cookie('ding_num', null);
            cookie('ding_type', null);
            cookie('ding_'.$shop_id, null);
            cookie('note',null);
            cookie('sex',null);
            cookie('mobile',null);
            cookie('name',null);
            $this->ajaxReturn(array('status'=>'success','msg'=>'下单成功！去支付','order_id'=>$order_id));
        }else{
            $this->ajaxReturn(array('status'=>'error','msg'=>'创建订单失败'));
        }
    }
    
    
    private function _getCartGoods($shop_id) {
		
        $carts = cookie('ding_'.$shop_id);
        if (empty($carts))
            return null;
        $carts = explode('|', $carts);
        $ids = $nums = array();
        foreach ($carts as $key => $val) {
            $local = explode('_', $val);
            $local[0] = (int) $local[0];
            $local[1] = (int) $local[1];
            if (!empty($local[0]) && !empty($local[1]) && $local[1] > 0) {
                $ids[$local[0]] = $local[0];
                $nums[$local[0]] = $local[1];
            }
        }
		$menu = D('Bookingmenu');
        $dingmenus = $menu->itemsByIds($ids);
        foreach ($dingmenus as $k => $val) {
            $dingmenus[$k]['cart_num'] = $nums[$val['menu_id']];
            $dingmenus[$k]['total_price'] = $nums[$val['menu_id']] * $val['ding_price'];    
        }
        $cookies = array();
        foreach ($nums as $k => $v) {
            $cookies[] = $k . '_' . $v;
        }
        $cookiestr = join('|', $cookies);
        cookie('ding_'.$shop_id, join('|', $cookies),array('expire'=>NOW_TIME+604800));
        $_COOKIE['ding_'.$shop_id] = $cookiestr; //因为页面不刷新所以要赋值一下
        return $dingmenus;
    }
    
	public function dianping() {
        $shop_id = (int) $this->_get('shop_id');
        if (!$detail = D('Shop')->find($shop_id)) {
            $this->error('没有该商家');
            die;
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
            die;
        }
        $this->assign('detail', $detail);
        $this->mobile_title = '商家点评';
        $this->display();
    }

	public function dianpingloading() {
        $shop_id = (int) $this->_get('shop_id');
        if (!$detail = D('Shop')->find($shop_id)) {
            die('0');
        }
        if ($detail['closed']) {
            die('0');
        }
        $Bookingdianping = D('Bookingdianping');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'shop_id' => $shop_id);
        $count = $Bookingdianping->where($map)->count(); 
        $Page = new Page($count, 25);

        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $show = $Page->show(); 
        $list = $Bookingdianping->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = $order_ids = array();
		foreach ($list as $k => $val) {
			$list[$k] = $val;
			$user_ids[$val['user_id']] = $val['user_id'];
			$order_ids[$val['order_id']] = $val['order_id'];
		}
		if (!empty($user_ids)) {
			$this->assign('users', D('Users')->itemsByIds($user_ids));
		}
		if (!empty($order_ids)) {
			$this->assign('pics', D('Bookingdianpingpic')->where(array('order_id' => array('IN', $order_ids)))->select());
		}
        $this->assign('totalnum', $count);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('detail', $detail);
        $this->display();
    }
	public function screen($shop_id){
	    $shop_id = (int) $this->_param('shop_id');
		$ding_date = htmlspecialchars($_COOKIE['ding_date']); 
		$ding_time = htmlspecialchars($_COOKIE['ding_time']); 
		$ding_num = htmlspecialchars($_COOKIE['ding_num']); 
		if (empty($ding_num)) {
            $this->fengmiMsg('请选择预约人数');
        }elseif(empty($ding_date)){
			 $this->fengmiMsg('先选择预日期约');
		}elseif(empty($ding_time)){
			 $this->fengmiMsg('请选择预约时间');
		}else{
			 $this->fengmiMsg('选择成功，正在为您选择筛选包厢',U('booking/rooms',array('shop_id' => $shop_id)));
		}	
    }
	//选择包间
	public function rooms(){
		$shop_id = (int) $this->_param('shop_id');

        if (!$detail = D('shop')->where('audit=1,closed=0,is_ding=1,city_id='.$this->city_id)->find($shop_id)) {
            $this->error('该餐厅不存在');
        }
		$ding_date = htmlspecialchars($_COOKIE['ding_date']); 
		$ding_time = htmlspecialchars($_COOKIE['ding_time']); 
		$ding_num = htmlspecialchars($_COOKIE['ding_num']); 
		$room = D('Bookingroom');
		$room_detail = $room->getrooms($shop_id,$ding_date,$ding_time,$ding_num );//商家ID，预约日期，预约时间，预约人数
		$getType = $room->getType();
		foreach($getType as $k => $v){
			if($v == $reson){
				$tt = $k;
			}
		}
		$tem = '';
		if($tt){
			$this->assign('tt',$tt);
		}else{
			foreach($room_detail as $k => $v){
				if(!$tem){
					$tem = $k;
					$this->assign('tt',$k);
				}
			}
		}
		$this->assign('room',$room->shoptype($shop_id));
		$this->assign('room_detail',$room_detail);
		$this->assign('shop_id',$shop_id);
		$this->assign('order_id',$order_id);
		$this->display();
	}
	
		
	//点评详情
    public function room_detail(){
        $room_id = (int) $this->_get('room_id');
        if (!($detail = D('Bookingroom')->where(array('room_id'=>$room_id))->find())){
            $this->error('没有该包厢');
            die;
        }
        if ($detail['closed']) {
            $this->error('该包厢已经被删除');
            die;
        }	
        $list =  D('Bookingroom')->get_room_thumb($room_id);
		$this->assign('list', $list);
		$booking =  D('Booking')->find($detail['shop_id']);
		$this->assign('booking', $booking);
		$bookingsetting =  D('Bookingsetting')->find($detail['shop_id']);
		$this->assign('bookingsetting', $bookingsetting);
		$this->assign('cfg',$cfg = D('Bookingsetting')->getCfg());
        $this->assign('detail', $detail);
        $this->display();
    }
	

	public function load(){
		$menu = D('Bookingmenu');
		$shop_id = (int) $this->_get('shop_id');
		$yuyue_id = (int) $this->_get('yuyue_id');
		$cat = (int) $this->_get('cat');

        $detail = D('shop')->where('audit=1,closed=0,is_ding=1,city_id='.$this->city_id)->find($shop_id);
        $Bookingmenu = D('Bookingmenu');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'shop_id' => $shop_id);
       
        $cat = (int) $this->_param('cat');
        if ($cat) {
            $map['cate_id'] = $cat;
        }
        $count = $Bookingmenu->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Bookingmenu->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->assign('detail', $detail);
		$this->assign('yuyue_id', $yuyue_id);
        $this->assign('cates', $menu->get_cate($shop_id));
        $this->assign('shop', $shop);
        if (!empty($this->cart)) {
            $ids = array_keys($this->cart);
            $total = array(
                'num' => 0, 'money' => 0
            );
            $menus = $Bookingmenu->itemsByIds($ids);
            foreach ($menus as $k => $val) {
                $menus[$k]['cart_num'] = $this->cart[$val['menu_id']];
                $total['num'] += $this->cart[$val['menu_id']];
                $total['money'] +=( $this->cart[$val['menu_id']] * $val['ding_price']);
            }
            $this->assign('total', $total);
            $this->assign('cartgoods', $menus);
        }
        $this->display();
	}

	public function add($menu_id) {

        $menu_id = (int) $menu_id;
        if (empty($menu_id)) {
            die('参数错误');
        }
        if (!$detail = D('Bookingmenu')->find($menu_id)) {
            die('该产品不存在');
        }
        if (!empty($this->cart)) {
            foreach ($this->cart as $k => $v) {
                $data = D('Bookingmenu')->find($k);
                if ($data['shop_id'] != $detail['shop_id']) {
                    die('一次只能订购一家的外卖，您可以清空购物车重新定！');
                }
                break;
            }
        }
        if (isset($this->cart[$menu_id])) {
            $this->cart[$menu_id]+=1;
        } else {
            $this->cart[$menu_id] = 1;
        }
		$S = $detail['shop_id'].'dingproduct';
        cookie($S, $this->cart);
        die('0');
    }

	 public function cart() {
        if (!empty($this->cart)) {
            $ids = array_keys($this->cart);
            $total = array(
                'num' => 0, 'money' => 0
            );
			$yuyue_id = (int) $this->_param('yuyue_id');
            $menus = D('Bookingmenu')->itemsByIds($ids);
            foreach ($menus as $k => $val) {
                $menus[$k]['cart_num'] = $this->cart[$val['menu_id']];
                $total['num'] += $this->cart[$val['menu_id']];
                $total['money'] +=( $this->cart[$val['menu_id']] * $val['ding_price']);
				$shop_id = $val['shop_id'];
            }
			$yuyue_id = (int) $this->_param('yuyue_id');
			if (!$yuyue_id) {
				$this->error('请按正确的流程操作');
			}
			$this->assign('yuyue_id', $yuyue_id);
            $this->assign('detail', D('shop')->find($shop_id));
            $this->assign('total', $total);
            $this->assign('cartgoods', $menus);
			$this->assign('shop_id', $shop_id);
			$obj = D('Bookingsetting');
			$this->assign('setting',$obj->find($shop_id));
        }

        $this->display();
    }

	 public function delete2($menu_id) {
        $menu_id = (int) $menu_id;
		$yuyue_id = (int) $this->_param('yuyue_id');
		$shop_id = (int) $this->_param('shop_id');
        if (!$detail = D('Bookingmenu')->find($menu_id)) {
            $this->error('该产品不存在');
        }else{
			if (isset($this->cart[$menu_id])) {
				unset($this->cart[$menu_id]);
				cookie($shop_id.'dingproduct', $this->cart);
			}
			$this->success('删除成功', U('booking/cart', array('shop_id' => $shop_id,'yuyue_id' => $yuyue_id)));
		}
    }
	

	public function order() {

		$yuyue_id = (int) $this->_post('yuyue_id');
		$shop_id = (int) $this->_post('shop_id');
        if (empty($this->uid)) {
           header('Location:' . U('passport/login'));
            die;
        }
		if (empty($shop_id)) {
           $this->error('该商家不存在');
        }
		$shop = D('shop')->find($shop_id);
        if (empty($shop)) {
            $this->error('该商家不存在');
        }
        $obj = D('Bookingsetting');
		$data = array();
        $num = $this->_post('num', false);

        $total = array(
            'money' => 0,
            'num' => 0,
        );
		if(!empty($num)){
			foreach ($num as $key => $val) {
				$key = (int) $key;
				$val = (int) $val;
				if ($val < 1 || $val > 99) {
					$this->error('请选择正确的购买数量');
				}
				$menu = D('Bookingmenu')->where('shop_id='.$shop_id)->find($key);
				if (empty($menu)) {
					$this->error('产品不正确');
				}
				$menu_str .= $menu['menu_id'].':'.$val.'|';
				$menu['buy_num'] = $val;
				$products[$key] = $menu;
				$total['money'] += ($menu['ding_price'] * $val);

				$total['num'] += $val;
			}
		}

		$order = D('Bookingorder');
		$order_no = $order->create_order_no();
        $ding_money = $obj->field('money')->where('shop_id ='.$shop_id)->find();

		

        if (D('Bookingyuyue')->where('user_id = '.$this->uid.' and ding_id = '.$yuyue_id)->save(array(
			'menu' => $menu_str,
			'order_no' => $order_no,
            'is_pay' => 0,
         ))) {
			cookie($shop_id."dingproduct", null);
			$order_id = $order->add(array(
				'order_no' => $order_no,
                'shop_id'   => $shop_id,
				'is_dianping' => 0,
				'status' => 0,
				'total_price' => $total['money'],
				'need_price' => $ding_money['money'],
				'user_id' => $this->uid,
				'ding_id' => $yuyue_id,
				'create_time' => NOW_TIME,
				'create_ip' => get_client_ip()
			 ));
			D('Shop')->updateCount($shop_id, 'ding_num');
			$this->success('下单成功！去支付', U('booking/pay', array('order_id' => $order_id)));
        }
        $this->error('创建订单失败！');
    }



    public function pay(){
        if (empty($this->uid)) {
          header('Location:' . U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Bookingorder')->find($order_id);
        if (empty($order) || $order['order_status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
        $this->assign('shop',D('Booking')->find($order['shop_id']));
		$this->assign('room',D('Bookingroom')->find($order['room_id']));
        $this->assign('payment', D('Payment')->getPayments_booking(true));
        $this->assign('order', $order);
        $this->display();
    }
    
    
    public function pay2(){
        if (empty($this->uid)) {
             $this->error('您还未登录');
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Bookingorder')->find($order_id);
        if (empty($order) || $order['order_status'] != 0 || $order['user_id'] != $this->uid) {
            $this->fengmiMsg('该订单不存在');
            die;
        }
        if (!$code = $this->_post('code')) {
            $this->fengmiMsg('请选择支付方式！');
        }
        $shop = D('Booking')->find($order['shop_id']);
        $payment = D('Payment')->checkPayment($code);
        if (empty($payment)) {
            $this->fengmiMsg('该支付方式不存在');
        }
        $logs = D('Paymentlogs')->getLogsByOrderId('ding', $order_id);
        if (empty($logs)) {
            $logs = array(
                'type' => 'booking',
                'user_id' => $this->uid,
                'order_id' => $order_id,
                'code' => $code,
                'need_pay' => $order['amount'],
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
                'is_paid' => 0
            );
            $logs['log_id'] = D('Paymentlogs')->add($logs);
        } else {
            $logs['need_pay'] = $order['amount'];
            $logs['code'] = $code;
            D('Paymentlogs')->save($logs);
        }
		D('Weixintmpl')->weixin_notice_booking_user($order_id,$this->uid,1);//订座微信通知用户
        $this->fengmiMsg('选择支付方式成功！下面请进行支付！', U('payment/payment',array('log_id' => $logs['log_id'])));
    }
    
}

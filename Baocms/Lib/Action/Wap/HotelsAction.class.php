<?php


class HotelsAction extends CommonAction {

    protected $types = array();

    public function _initialize() {
        parent::_initialize();
		 if ($this->_CONFIG['operation']['hotels'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $this->types = D('Hotelbrand')->fetchAll();
        $this->assign('types', $this->types);
        $this->cates = D('Hotel')->getHotelCate();
        $this->assign('cates', $this->cates);
        $this->stars = D('Hotel')->getHotelStar();
        $this->assign('stars', $this->stars);
        $this->assign('roomtype',D('Hotelroom')->getRoomType());
    }

    public function index() {
        $linkArr = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $linkArr['keyword'] = $keyword;
        
        $cate_id = (int) $this->_param('cate_id');
        $this->assign('cate_id', $cate_id);
        $linkArr['cate_id'] = $cate_id;
        
        $area_id = (int) $this->_param('area_id');
        $this->assign('area_id', $area_id);
        $linkArr['area_id'] = $area_id;
        
        $business_id = (int) $this->_param('business_id');
        $this->assign('business_id', $business_id);
        $linkArr['business_id'] = $business_id;
        
        $order = $this->_param('order', 'htmlspecialchars');
        $this->assign('order', $order);
        $linkArr['order'] = $order;

        $this->assign('nextpage', LinkTo('hotels/loaddata',$linkArr,array('t' => NOW_TIME,'p' => '0000')));
        $this->assign('linkArr',$linkArr);
        $this->mobile_title = '酒店首页';
        $this->display(); // 输出模板
    }
    
    public function loaddata() {
        $hotel = D('Hotel');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array('audit' => 1, 'closed' => 0, 'city_id' => $this->city_id);
        $linkArr = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['hotel_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
            $linkArr['keywrod'] = $keyword;
        }
        $cate_id = (int) $this->_param('cate_id');
        if($cate_id){
            $map['cate_id'] = $cate_id;
            $linkArr['cate_id'] = $cate_id;
        }
        $this->assign('cate_id', $cate_id);
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
                $orderby = array('sold_num' => 'desc');
                break;
            default:
                $orderby = array('sold_num' => 'desc', 'hotel_id' => 'desc');
                break;
        }
        $this->assign('order', $order);
		$lists = $hotel->where($map)->order($orderby)->select();
        $shops = array();
        foreach ($lists as $k => $val) {
            if($shops[$val['shop_id']]){
                unset($lists[$k]);
            }else{
                $shop = D('Shop')->find($val['shop_id']);
                if($shop['audit'] == 0 && $shop['closed'] == 1){
                    $shops[$val['shop_id']] = $val['shop_id'];
                    unset($lists[$k]);
                }
            }
            $lists[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $count = count($lists);
        $Page = new Page($count, 20); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

    
    public function detail($hotel_id){
        $obj = D('Hotel');
        if(!$hotel_id = (int)$hotel_id){
            $this->error('该酒店不存在');
        }elseif(!$detail = $obj->find($hotel_id)){
            $this->error('该酒店不存在');
        }elseif($detail['closed'] == 1||$detail['audit'] == 0){
            $this->error('该酒店已删除或未通过审核');
        }else{
            $lat = addslashes(cookie('lat'));
            $lng = addslashes(cookie('lng'));
            if (empty($lat) || empty($lng)) {
                $lat = $this->city['lat'];
                $lng = $this->city['lng'];
            }
            $detail['d'] = getDistance($lat, $lng, $detail['lat'], $detail['lng']);
            $pics = D('Hotelpics')->where(array('hotel_id'=>$hotel_id))->select();
            $pics[] = array('photo'=>$detail['photo']);
            $into_time = htmlspecialchars($_COOKIE['into_time']);
            $out_time = htmlspecialchars($_COOKIE['out_time']); 
            //房间
            $room_list = D('Hotelroom')->where(array('hotel_id'=>$hotel_id))->select();
            $room_count = D('Hotelroom')->where(array('hotel_id'=>$hotel_id))->count();
            $this->assign('room_list',$room_list);
            $this->assign('room_count',$room_count);
            //套餐
            $tuan_list = D('Tuan')->where(array('audit' => 1, 'closed' => 0,'bg_date' => array('ELT', NOW),'shop_id'=>$detail['shop_id']))->limit(3)->select();
            $this->assign('tuan_list',$tuan_list);
            $this->assign('into_time',$into_time); 
            $this->assign('out_time',$out_time);
            $this->assign('detail',$detail);
            $this->assign('pics',$pics);
            $this->display();
        }
    }

    public function info($hotel_id){
        $obj = D('Hotel');
        if(!$hotel_id = (int)$hotel_id){
            $this->error('该酒店不存在');
        }elseif(!$detail = $obj->find($hotel_id)){
            $this->error('该酒店不存在');
        }elseif($detail['closed'] == 1||$detail['audit'] == 0){
            $this->error('该酒店已删除或未通过审核');
        }else{
            $this->assign('detail',$detail);
            $this->display();
        }
    }
    
    public function order($room_id){
		$into_time = htmlspecialchars($_COOKIE['into_time']);
        $out_time = htmlspecialchars($_COOKIE['out_time']); 
        $obj = D('Hotelroom');
        if(!$room_id = (int)$room_id){
            $this->error('房间不存在');
        }elseif(!$detail = $obj->find($room_id)){
            $this->error('房间不存在');
        }elseif($detail['sku'] == 0){
            $this->error('房间已经预订完了');
        }elseif(empty($into_time)){
            $this->error('预订起始时间不能为空，请选择时间后下单');
        }elseif(empty($out_time)){
            $this->ferror('预订结束时间不能为空，请选择时间后下单');
        }
		$hotel = D('Hotel')->find($detail['hotel_id']);
        $this->assign('hotel',$hotel);
        $this->assign('detail',$detail);
        $this->assign('into_time',$into_time); 
        $this->assign('out_time',$out_time);
        $this->display();
    }

    
    public function orderCreate(){
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        $room_id = (int) $_POST['room_id'];
        $detail = D('Hotelroom')->find($room_id);
        if (empty($detail)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该房间不存在'));
        }
		$Hotel = D('Hotel')->find($detail['hotel_id']);
		if (false == D('Shop')->check_shop_user_id($Hotel['shop_id'],$this->uid)) {//不能购买自己家的产品
			 $this->ajaxReturn(array('status' => 'error', 'msg' => '您不能订购自己的酒店'));
		}
        if (IS_AJAX) {
            $num = (int) $_POST['num'];
            if (empty($num)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '房间数不能为空'));
            }
            if ($num > $detail['sku'] ) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '房间不够了'));
            }
            $data = array(
                'room_id' => $room_id,
                'num' => $num,
                'price'=> $detail['price'],
                'user_id' => $this->uid,
                'hotel_id'=>$detail['hotel_id'],
                'create_time'=>NOW_TIME,
                'create_ip'=>  get_client_ip(),
            );
            $data['online_pay'] = (int) $_POST['online_pay'];
            $data['stime'] = htmlspecialchars($_POST['stime']);
            $data['ltime'] = htmlspecialchars($_POST['ltime']);
            if(!$data['stime'] || !$data['ltime']){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '选择时间不能为空'));
            }
            if($data['stime'] > $data['ltime']){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '选择时间不正确'));
            }
            $data['name'] = htmlspecialchars($_POST['realname']);
            if(!$data['name']){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '入住人姓名不能为空'));
            }
            $data['mobile'] = htmlspecialchars($_POST['mobile']);
            if(!$data['mobile']){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '入住人手机号不能为空'));
            }
            $data['note'] = htmlspecialchars($_POST['note']);
            $data['last_time'] = htmlspecialchars($_POST['last_time']);
            $night_num = $this->diffBetweenTwoDays($data['stime'],$data['ltime']);
            $data['amount'] = $night_num*$num*$detail['price'];
            $data['jiesuan_amount'] = $night_num*$num*$detail['settlement_price'];
            if($order_id = D('Hotelorder')->add($data)){
                D('Hotel')->updateCount($detail['hotel_id'],'sold_num');
                D('Hotelroom')->updateCount($room_id,'sku','-1');
                cookie('into_time', null);
                cookie('out_time', null);
                $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜下单成功','order_id'=>$order_id,'online_pay'=>$data['online_pay']));
            }else{
                $this->ajaxReturn(array('status' => 'error', 'msg' => '下单失败'));
            }
        }
    }

    function diffBetweenTwoDays ($day1, $day2){
          $second1 = strtotime($day1);
          $second2 = strtotime($day2);

          if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
          }
          return ($second1 - $second2) / 86400;
    }

    public function pay(){
        if (empty($this->uid)) {
           header('Location:' . U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Hotelorder')->find($order_id);
        if (empty($order) || $order['order_status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
        $room = D('Hotelroom')->find($order['room_id']);
        if (empty($room) || $room['hotel_id'] != $order['hotel_id'] ) {
            $this->error('该房间不存在');
            die;
        }
        $this->assign('payment', D('Payment')->getPayments_booking(true));
        $this->assign('room', $room);
        $this->assign('order', $order);
        $this->display();
    }
    
    
    public function pay2(){
        if (empty($this->uid)) {
            header('Location:' . U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Hotelorder')->find($order_id);
        if (empty($order) || $order['order_status'] != 0 || $order['user_id'] != $this->uid) {
            $this->fengmiMsg('该订单不存在');
            die;
        }
        if (!$code = $this->_post('code')) {
            $this->fengmiMsg('请选择支付方式！');
        }
        
        $payment = D('Payment')->checkPayment($code);
        if (empty($payment)) {
            $this->fengmiMsg('该支付方式不存在');
        }
        $room = D('Hotelroom')->find($order['room_id']);
        if (empty($room) || $room['hotel_id'] != $order['hotel_id'] ) {
            $this->fengmiMsg('该房间不存在');
        }
        $logs = D('Paymentlogs')->getLogsByOrderId('hotel', $order_id);
        if (empty($logs)) {
            $logs = array(
                'type' => 'hotel',
                'user_id' => $this->uid,
                'order_id' => $order_id,
                'code' => $code,
                'need_pay' => $order['amount']*100,
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
                'is_paid' => 0
            );
            $logs['log_id'] = D('Paymentlogs')->add($logs);
        } else {
            $logs['need_pay'] = $order['amount']*100;
            $logs['code'] = $code;
            D('Paymentlogs')->save($logs);
        }
        D('Weixintmpl')->weixin_notice_hotel_user($order_id,$this->uid,1);//酒店微信通知用户
        $this->fengmiMsg('选择支付方式成功！下面请进行支付！', U('payment/payment',array('log_id' => $logs['log_id'])));
    }

}

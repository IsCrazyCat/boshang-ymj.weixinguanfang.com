<?php
class HotelAction extends CommonAction {
    
    public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['hotels'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $this->cates = D('Hotel')->getHotelCate();
        $this->assign('cates', $this->cates);
        $this->stars = D('Hotel')->getHotelStar();
        $this->assign('stars', $this->stars);
        $this->assign('roomtype',D('Hotelroom')->getRoomType());
    }

    
    private function check_hotel(){
        
        $hotel = D('Hotel');
        $res =  $hotel->where(array('shop_id'=>$this->shop_id))->find();
        if(!$res){
            $this->error('请先完善酒店资料！',U('hotel/set_hotel'));
        }elseif($res['audit'] == 0){
            $this->error('您的酒店申请正在审核中，请耐心等待！',U('hotel/set_hotel'));
        }elseif($res['audit'] == 2){
            $this->error('您的酒店申请未通过审核！',U('hotel/set_hotel'));
        }else{
            return $res['hotel_id'];
        }
        
    }
    
    public function index(){
        $hotel_id = $this->check_hotel();
        $hotelorder = D('Hotelorder');
        $hotelorder->plqx($hotel_id);
        import('ORG.Util.Page'); 
        $map = array('hotel_id' => $hotel_id);
        $map['closed'] = 0;
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars') ) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }

        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        
        $count = $hotelorder->where($map)->count(); 
        $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $list = $hotelorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $room_ids = array();
        foreach($list as $k=>$val){
            $room_ids[$val['room_id']] = $val['room_id'];
        }
        $this->assign('rooms',D('Hotelroom')->itemsByIds($room_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show);
        $this->display(); 
    }
    
    
    public function set_hotel(){
        $obj = D('Hotel');
        $hotel = $obj->where(array('shop_id'=>$this->shop_id))->find();
        if ($this->isPost()) { 
           $data = $this->createCheck();
           $thumb = $this->_param('thumb', false);
            foreach ($thumb as $k => $val) {
                if (empty($val)) {
                    unset($thumb[$k]);
                }
                if (!isImage($val)) {
                    unset($thumb[$k]);
                }
            }
            if (empty($hotel)) {
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                if($hotel_id = $obj->add($data)){
                    foreach($thumb as $k=>$val){
                        D('Hotelpics')->add(array('hotel_id'=>$hotel_id,'photo'=>$val));
                    }
                     $this->baoSuccess('设置成功', U('hotel/index'));
                }else{
                    $this->baoError('设置失败');
                }
            }else{
                $data['update_time'] = NOW_TIME;
                $data['update_ip'] = get_client_ip();
                $data['audit'] = 0;
                $data['hotel_id'] = $hotel['hotel_id'];
                if(false !== $obj->save($data)){
                    D('Hotelpics')->where(array('hotel_id'=>$hotel['hotel_id']))->delete();
                    foreach($thumb as $k=>$val){
                        D('Hotelpics')->add(array('hotel_id'=>$hotel['hotel_id'],'photo'=>$val));
                    }
                    $this->baoSuccess('修改成功', U('hotel/index'));
                }else{
                    $this->baoError('修改失败');
                }
            }
        } else {
            $this->assign('hotel',$hotel);
            $thumb = D('Hotelpics')->where(array('hotel_id'=>$hotel['hotel_id']))->select();
            $this->assign('thumb', $thumb);
            $this->assign('types',D('Hotelbrand')->fetchAll());
            $this->display();
        }
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), array('hotel_name', 'addr', 'city_id', 'area_id','business_id','cate_id', 'type','price','star', 'tel', 'details', 'photo', 'lng', 'lat','is_wifi','is_kt','is_nq','is_tv','is_xyj','is_ly','is_bx','is_base','is_rsh','in_time','out_time'));
        $data['hotel_name'] = htmlspecialchars($data['hotel_name']);
        if (empty($data['hotel_name'])) {
            $this->baoError('酒店名称不能为空');
        }$data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('酒店地址不能为空');
        }$data['cate_id'] = (int)$data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('酒店级别没有选择');
        }$data['star'] = (int)$data['star'];
        if (empty($data['star'])) {
            $this->baoError('酒店星级不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->baoError('酒店起价不能为空');
        }$data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->baoError('酒店联系电话不能为空');
        }
        $data['type'] = (int)$data['type'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
                $this->baoError('酒店坐标没有选择');
            }
        $data['shop_id'] = $this->shop_id;
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
        $data['city_id'] = $this->shop['city_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        } 
        
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('酒店详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('酒店详情含有敏感词：' . $words);
        }
        $data['in_time'] = htmlspecialchars($data['in_time']);
        $data['out_time'] = htmlspecialchars($data['out_time']);
        $data['is_wifi'] = (int)$data['is_wifi'];
        $data['is_kt'] = (int)$data['is_kt'];
        $data['is_nq'] = (int)$data['is_nq'];
        $data['is_tv'] = (int)$data['is_tv'];
        $data['is_xyj'] = (int)$data['is_xyj'];
        $data['is_ly'] = (int)$data['is_ly'];
        $data['is_bx'] = (int)$data['is_bx'];
        $data['is_base'] = (int)$data['is_base'];
        $data['is_rsh'] = (int)$data['is_rsh'];
        return $data;
    }
    
    
    public function room(){ 
        $hotel_id = $this->check_hotel();
        $room = D('Hotelroom');
        import('ORG.Util.Page'); 
        $map = array('hotel_id' => $hotel_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $room->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $room->where($map)->order(array('room_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

    

    public function setroom(){ //添加房间
        $this->check_hotel();
        if ($this->isPost()) {
            $data = $this->roomCreateCheck();
            $obj = D('Hotelroom');
            if ($room_id = $obj->add($data)) {
                $this->baoSuccess('添加成功', U('hotel/room'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    
    
    private function roomCreateCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title', 'price','settlement_price', 'type', 'photo','hotel_id','is_zc', 'is_kd','is_cancel','sku'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('房间名称不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->baoError('房间价格不能为空');
        }$data['settlement_price'] = (int)$data['settlement_price'];
        if (empty($data['settlement_price'])) {
            $this->baoError('房间结算价格不能为空');
        }if ($data['settlement_price'] >=$data['price']) {
            $this->baoError('结算价格不能大于房间价格');
        }$data['type'] = (int)$data['type'];
        if (empty($data['type'])) {
            $this->baoError('房间类型不能为空');
        }
        $data['type'] = (int)$data['type'];
        $hotel = D('Hotel')->where(array('shop_id'=>$this->shop_id))->find();
        $data['hotel_id'] = $hotel['hotel_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传房间图片');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('房间图片格式不正确');
        } 
        $data['sku'] = (int) $data['sku'];
        $data['is_zc'] = (int)$data['is_zc'];
        $data['is_kd'] = (int)$data['is_kd'];
        $data['is_cancel'] = (int)$data['is_cancel'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    
    public function editroom($room_id=null){
        $hotel_id = $this->check_hotel();
        if ($room_id = (int) $room_id) {
            $obj = D('Hotelroom');
            if (!$detail = $obj->find($room_id)) {
                $this->baoError('请选择要编辑的房间');
            }
            if ($detail['hotel_id'] != $hotel_id) {
                $this->baoError('请不要操作别人的房间');
            }
            if ($this->isPost()) {
                $data = $this->roomEditCheck();
                $data['room_id'] = $room_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('hotel/room'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail',$detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的房间');
        }
    }

    

    private function roomEditCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title', 'price','settlement_price', 'type', 'photo','is_zc', 'is_kd','is_cancel','sku'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('房间名称不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->baoError('房间价格不能为空');
        }$data['settlement_price'] = (int)$data['settlement_price'];
        if (empty($data['settlement_price'])) {
            $this->baoError('房间结算价格不能为空');
        }if ($data['settlement_price'] >=$data['price']) {
            $this->baoError('结算价格不能大于房间价格');
        }$data['type'] = (int)$data['type'];
        if (empty($data['type'])) {
            $this->baoError('房间类型不能为空');
        }
        $data['type'] = (int)$data['type'];
        $hotel = D('Hotel')->where(array('shop_id'=>$this->shop_id))->find();
        $data['hotel_id'] = $hotel['hotel_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传房间图片');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('房间图片格式不正确');
        } 
        $data['sku'] = (int) $data['sku'];
        $data['is_zc'] = (int)$data['is_zc'];
        $data['is_kd'] = (int)$data['is_kd'];
        $data['is_cancel'] = (int)$data['is_cancel'];
        return $data;
    }
   
    
    public function cancel($order_id){
        $hotel_id = $this->check_hotel();
        if($order_id = (int) $order_id){
            if(!$order = D('Hotelorder')->find($order_id)){
                $this->baoError('订单不存在');
            }elseif($order['hotel_id'] != $hotel_id){
                $this->baoError('非法操作订单');
            }elseif($order['order_status'] == -1){
                $this->baoError('该订单已取消');
            }else{
                if(false !== D('Hotelorder')->cancel($order_id)){
                    $this->baoSuccess('订单取消成功',U('hotel/index'));
                }else{
                    $this->baoError('订单取消失败');
                }
            }
        }else{
            $this->baoError('请选择要取消的订单');
        }
    }
    
    
    public function complete($order_id){
        $hotel_id = $this->check_hotel();
        if($order_id = (int) $order_id){
            if(!$order = D('Hotelorder')->find($order_id)){
                $this->baoError('订单不存在');
            }elseif($order['hotel_id'] != $hotel_id){
                $this->baoError('非法操作订单');
            }elseif(($order['online_pay'] == 1&&$order['order_status'] != 1)||($order['online_pay'] == 0&&$order['order_status'] != 0)){
                $this->baoError('该订单无法完成');
            }else{

                if(false !== D('Hotelorder')->complete($order_id)){
                    $this->baoSuccess('订单操作成功',U('hotel/index'));
                }else{
                    $this->baoError('订单操作失败');
                }
            }
        }else{
            $this->baoError('请选择要完成的订单');
        }
    }
    
    
    public function delete($order_id){
        $hotel_id = $this->check_hotel();
        if($order_id = (int) $order_id){
            if(!$order = D('Hotelorder')->find($order_id)){
                $this->baoError('订单不存在');
            }elseif($order['hotel_id'] != $hotel_id){
                $this->baoError('非法操作订单');
            }elseif($order['order_status'] != -1){
                $this->baoError('订单状态不正确');
            }else{
                if(false !== D('Hotelorder')->save(array('order_id'=>$order_id,'closed'=>1))){
                    $this->baoSuccess('订单删除成功',U('hotel/index'));
                }else{
                    $this->baoError('订单删除失败');
                }
            }
        }else{
            $this->baoError('请选择要删除的订单');
        }
    }
    
    public function detail($order_id=null){
        $hotel_id = $this->check_hotel();
        if(!$order_id = (int)$order_id){
            $this->error('订单不存在');
        }elseif(!$detail = D('Hotelorder')->find($order_id)){
             $this->error('订单不存在');
        }elseif($detail['closed'] == 1){
             $this->error('订单已删除');
        }elseif($detail['hotel_id'] != $hotel_id){
             $this->error('非法的订单操作');
        }else{
            $detail['night_num'] = $this->diffBetweenTwoDays($detail['stime'],$detail['ltime']); 
            $detail['room'] = D('Hotelroom')->find($detail['room_id']); 
            $detail['hotel'] = D('Hotel')->find($detail['hotel_id']);
            $this->assign('detail',$detail);
            $this->display();
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

  
}

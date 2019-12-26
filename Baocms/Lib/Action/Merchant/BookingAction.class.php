<?php


class BookingAction extends CommonAction {
    
    public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['booking'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $dingtypes = D('Booking')->getDingType();
        $this->assign('dingtypes',$dingtypes);
        $cates = D('Bookingcate')->where(array('shop_id' => $this->shop_id))->select();
        foreach($cates as $k=>$val){
            $dingcates[$val['cate_id']] = $val;
        }
        $this->assign('dingcates', $dingcates);   
		$this->assign('types', $types = D('Bookingroom')->getType());
    }

    private function check_booking(){
        
        $Booking = D('Booking');
        $res =  $Booking->find($this->shop_id);
        if(!$res){
            $this->error('请先完善订座资料！',U('booking/set_booking'));
        }elseif($res['audit'] == 0){
            $this->error('您的订座服务申请正在审核中，请耐心等待！',U('booking/set_booking'));
        }elseif($res['audit'] == 2){
            $this->error('您的订座服务申请未通过审核！',U('booking/set_booking'));
        }
    }
     
    public function index(){
        $this->check_booking();
        $Bookingorder = D('Bookingorder');
        import('ORG.Util.Page'); 
        $map = array('shop_id' => $this->shop_id,'closed'=>0);
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
            $map['order_id|name|mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        
        $count = $Bookingorder->where($map)->count(); 
        $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $list = $Bookingorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$shop_ids = $room_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$room_ids[$val['room_id']] = $val['room_id'];
        }
        if (!empty($shop_ids)) {
            $this->assign('shops', D('Booking')->itemsByIds($shop_ids));
        }
		if (!empty($room_ids)) {
            $this->assign('room', D('Bookingroom')->itemsByIds($room_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show); 
        $this->display();
    }
	
	//订座房间
    public function room(){
        $obj = D('Bookingroom');
        import('ORG.Util.Page'); 
        $map = array('shop_id'=>  $this->shop_id);
        $keyword = trim($this->_param('keyword', 'htmlspecialchars'));
        if ($keyword) {
            $map['name'] = array('LIKE', '%'.$keyword.'%');
        }
        $this->assign('keyword',$keyword);
        if($type_id = (int)$this->_param('type_id')){
            $map['type_id'] = $type_id;
            $this->assign('type_id',$type_id);
        } else{
			$this->assign('type_id',0);
		}       
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('types',$obj->getType());
        $this->assign('list', $list); 
        $this->assign('page', $show);
       
        $this->display();
    }
    
    //订座房间添加
    public function roomcreate(){
         $obj = D('Bookingroom');
         if(IS_POST){
             $data['name'] = htmlspecialchars($_POST['data']['name']);
             if(empty($data['name'])){
                 $this->baoError('包厢名称不能为空');
             }
             $data['type_id'] = (int)($_POST['data']['type_id']);
             if(empty($data['type_id'])){
                 $this->baoError('请选择房间大小');
             }
             $data['photo'] = htmlspecialchars($_POST['data']['photo']);
             if(empty($data['photo'])){
                 $this->baoError('请上传图片');
             }
			 $thumb = $this->_param('thumb', false);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isImage($val)) {
                        unset($thumb[$k]);
                    }
                }
             $data['thumb'] = serialize($thumb);
             $data['intro'] = htmlspecialchars($_POST['data']['intro']);
             $data['money'] = (int)($_POST['data']['money']*100);
             $data['closed'] = (int)($_POST['data']['closed']);
             
             $data['shop_id'] = $this->shop_id;
             if($obj->add($data)){
                 $this->baoSuccess('恭喜你操作成功',U('booking/roomcreate'));
             }
             $this->baoError('操作失败');
         }else{             
             $this->assign('types',$obj->getType());
             $this->display();
         }
    }
    //订座房间编辑
    public function roomedit($room_id){
        $obj = D('Bookingroom');
        if(!$detail = $obj->find($room_id)){
            $this->error('参数错误');
        }
        if($detail['shop_id']!= $this->shop_id){
            $this->error('参数错误');
        }
        $obj = D('Bookingroom');
         if(IS_POST){
             $data['name'] = htmlspecialchars($_POST['data']['name']);
             if(empty($data['name'])){
                 $this->baoError('包厢名称不能为空');
             }
             $data['type_id'] = (int)($_POST['data']['type_id']);
             if(empty($data['type_id'])){
                 $this->baoError('请选择房间大小');
             }
             $data['photo'] = htmlspecialchars($_POST['data']['photo']);
             if(empty($data['photo'])){
                 $this->baoError('请上传图片');
             }
			 $thumb = $this->_param('thumb', false);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isImage($val)) {
                        unset($thumb[$k]);
                    }
                }
             $data['thumb'] = serialize($thumb);
             $data['intro'] = htmlspecialchars($_POST['data']['intro']);
             $data['money'] = (int)($_POST['data']['money']*100);
             $data['closed'] = (int)($_POST['data']['closed']);
             $data['room_id'] = $room_id;
             $data['shop_id'] = $this->shop_id;
             if(false !== $obj->save($data)){
                 $this->baoSuccess('恭喜你操作成功',U('booking/roomedit',array('room_id'=>$room_id)));
             }
             $this->baoError('操作失败');
         }else{ 
		     $thumb = unserialize($detail['thumb']);
             $this->assign('thumb', $thumb);            
             $this->assign('types',$obj->getType());
             $this->assign('detail',$detail);
             $this->display();
         }
    }
    //订座房间删除
    public function roomdelete($room_id){
         $obj = D('Bookingroom');
        if($room_id = (int)$room_id){
            if(!$detail = $obj->find($room_id)){
                $this->baoError('参数错误');
            }
            if($detail['shop_id']!= $this->shop_id){
                $this->baoError('参数错误');
            }
            $data['closed'] = $detail['closed'] ? 0 : 1;
            $data['room_id'] = $room_id;
            if(false != $obj->save($data)){
                $this->baoSuccess('操作成功',U('booking/room'));
            }
            $this->baoError('操作失败');
        }else{
            $this->baoError('参数错误');
        }        
    }
    
    
    public function set_booking(){
        $obj = D('Booking');
        $booking = $obj->find($this->shop_id);
        if ($this->isPost()) { 
           $data = $this->createCheck();
           $thumb = $this->_param('thumb', false);
           $type = $this->_param('type',false);
            foreach ($thumb as $k => $val) {
                if (empty($val)) {
                    unset($thumb[$k]);
                }
                if (!isImage($val)) {
                    unset($thumb[$k]);
                }
            }
            if (empty($booking)) {
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                $data['shop_id'] = $this->shop_id;
                if($obj->add($data)){
                    foreach($thumb as $k=>$val){
                        D('Bookingpics')->add(array('shop_id'=>$this->shop_id,'photo'=>$val));
                    }
                    foreach($type as $k=>$val){
                        D('Bookingattr')->add(array('shop_id'=>$this->shop_id,'type_id'=>$val));
                    }
                    
                     $this->baoSuccess('设置成功', U('booking/index'));
                }else{
                    $this->baoError('设置失败');
                }
            }else{
                $data['update_time'] = NOW_TIME;
                $data['update_ip'] = get_client_ip();
                $data['audit'] = 0;
                $data['shop_id'] = $this->shop_id;
                if(false !== $obj->save($data)){
                    D('Bookingpics')->where(array('shop_id'=>$this->shop_id))->delete();
                    foreach($thumb as $k=>$val){
                        D('Bookingpics')->add(array('shop_id'=>$this->shop_id,'photo'=>$val));
                    }
                    D('Bookingattr')->where(array('shop_id'=>$this->shop_id))->delete();
                    foreach($type as $k=>$val){
                        D('Bookingattr')->add(array('shop_id'=>$this->shop_id,'type_id'=>$val));
                    }
                    $this->baoSuccess('修改成功', U('booking/index'));
                }else{
                    $this->baoError('修改失败');
                }
            }
        } else {
            $this->assign('booking',$booking);
            $thumb = D('Bookingpics')->where(array('shop_id'=>$this->shop_id))->select();
            $this->assign('thumb', $thumb);
            $have_type = D('Bookingattr')->where(array('shop_id'=>$this->shop_id))->select();
            $typess = array();
            foreach ($have_type as $k=>$val){
                $typess[$val['type_id']] = $val['type_id'];
            }
            $this->assign('have_type',$typess);
            $this->display();
        }
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), array('shop_name', 'addr', 'city_id', 'area_id','business_id','price','mobile','deposit','tel', 'details', 'photo', 'lng', 'lat','business_time'));
        $data['shop_name'] = htmlspecialchars($data['shop_name']);
        if (empty($data['shop_name'])) {
            $this->baoError('名称不能为空');
        }$data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('地址不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->baoError('评价价格不能为空');
        }$data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->baoError('联系电话不能为空');
        }$data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('手机号不能为空');
        }if(!isMobile($data['mobile'])){
            $this->baoError('手机号格式不正确');
        }
        $data['deposit'] = (int)$data['deposit'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
            $this->baoError('坐标没有选择');
        }
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
        $data['business_time'] = htmlspecialchars($data['business_time']);
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('详情含有敏感词：' . $words);
        }
        return $data;
    }
    
    public function cate() {  
        $this->check_booking();
        $Bookingcate = D('Bookingcate');
        import('ORG.Util.Page'); 
        $map = array('shop_id'=>$this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['cate_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Bookingcate->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Bookingcate->where($map)->order(array('cate_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

    public function catecreate() {
        if ($this->isPost()) {
            $data = $this->cateCreateCheck();
            $obj = D('Bookingcate');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('booking/cate'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function cateCreateCheck() {
        $data = $this->checkFields($this->_post('data', false), array('cate_name','orderby'));
        $data['shop_id'] = $this->shop_id;
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类名称不能为空');
        }
        $data['orderby'] = (int)$data['orderby'];
        return $data;
    }

    public function cateedit($cate_id = 0) {
        if ($cate_id = (int) $cate_id) {
            $obj = D('Bookingcate');
            if (!$detail = $obj->find($cate_id)) {
                $this->error('请选择要编辑的菜品分类');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('请不要操作其他商家的菜品分类');
            }
            if ($this->isPost()) {
                $data = $this->cateEditCheck();
                $data['cate_id'] = $cate_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('booking/cate'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('请选择要编辑的菜品分类');
        }
    }

    private function cateEditCheck() {
        $data = $this->checkFields($this->_post('data', false), array('cate_name','orderby'));
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类名称不能为空');
        }
        $data['orderby'] = (int)$data['orderby'];
        return $data;
    }

    public function catedelete($cate_id = 0) {
        if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
            $obj = D('Bookingcate');
            if (!$detail = $obj->where(array('shop_id' => $this->shop_id, 'cate_id' => $cate_id))->find()) {
                $this->baoError('请选择要删除的菜品分类');
            }
            $obj->delete($cate_id);
            $this->baoSuccess('删除成功！',U('booking/cate'));
        }
        $this->baoError('请选择要删除的菜品分类');
    }
    
    //菜品配置 
    
    public function menu(){
        $Bookingmenu = D('Bookingmenu');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['menu_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = $this->shop_id) {
            $map['shop_id'] = $shop_id;
            $this->assign('shop_id', $shop_id);
        }
        $count = $Bookingmenu->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Bookingmenu->where($map)->order(array('menu_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display();
    }

    public function menucreate() {
        if ($this->isPost()) {
            $data = $this->menuCreateCheck();
            $obj = D('Bookingmenu');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('booking/menu'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function menuCreateCheck() {
        $data = $this->checkFields($this->_post('data', false), array('menu_name', 'cate_id', 'photo', 'price', 'ding_price', 'is_new', 'is_sale', 'is_tuijian'));
        $data['menu_name'] = htmlspecialchars($data['menu_name']);
        if (empty($data['menu_name'])) {
            $this->baoError('菜品名称不能为空');
        }
        $data['shop_id'] = $this->shop_id;
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('菜品分类不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->baoError('价格不能为空');
        }
        $data['ding_price'] = (int) ($data['ding_price'] * 100);
        if (empty($data['ding_price'])) {
            $this->baoError('优惠价格不能为空');
        }
        $data['is_new'] = (int) $data['is_new'];
        $data['is_sale'] = (int) $data['is_sale'];
        $data['is_tuijian'] = (int) $data['is_tuijian'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }

    public function menuedit($menu_id = 0) {
        if ($menu_id = (int) $menu_id) {
            $obj = D('Bookingmenu');
            if (!$detail = $obj->find($menu_id)) {
                $this->baoError('请选择要编辑的菜品设置');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->baoError('请不要操作其他商家的菜品设置');
            }
            if ($this->isPost()) {
                $data = $this->menuEditCheck();
                $data['menu_id'] = $menu_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('booking/menu'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的菜品设置');
        }
    }

    private function menuEditCheck() {
        $data = $this->checkFields($this->_post('data', false), array('menu_name', 'cate_id', 'photo', 'price', 'ding_price', 'is_new', 'is_sale', 'is_tuijian'));
        $data['product_name'] = htmlspecialchars($data['product_name']);
        if (empty($data['menu_name'])) {
            $this->baoError('菜品名称不能为空');
        }$data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('菜品分类不能为空');
        } $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->baoError('价格不能为空');
        }
        $data['ding_price'] = (int) ($data['ding_price'] * 100);
        if (empty($data['ding_price'])) {
            $this->baoError('优惠价格不能为空');
        }
        $data['is_new'] = (int) $data['is_new'];
        $data['is_sale'] = (int) $data['is_sale'];
        $data['is_tuijian'] = (int) $data['is_tuijian'];
        return $data;
    }

    public function menudelete($menu_id = 0) {
        if (is_numeric($menu_id) && ($menu_id = (int) $menu_id)) {
            $obj = D('Bookingmenu');
            if (!$detail = $obj->where(array('shop_id' => $this->shop_id, 'menu_id' => $menu_id))->find()) {
                $this->baoError('请选择要删除的菜品设置');
            }
            $obj->save(array('menu_id' => $menu_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('booking/menu'));
        }
        $this->baoError('请选择要删除的菜品设置');
    }
    
    
    public function cancel($order_id){
        $this->check_booking();
        if($order_id = (int) $order_id){
            if(!$order = D('Bookingorder')->find($order_id)){
                $this->baoError('订单不存在');
            }elseif($order['shop_id'] != $this->shop_id){
                $this->baoError('非法操作订单');
            }elseif($order['order_status'] == -1){
                $this->baoError('该订单已取消');
            }else{
                if(false !== D('Bookingorder')->cancel($order_id)){
                    $this->baoSuccess('订单取消成功',U('booking/index'));
                }else{
                    $this->baoError('订单取消失败');
                }
            }
        }else{
            $this->baoError('请选择要取消的订单');
        }
    }
    
    
    public function complete($order_id){
        $this->check_booking();
        if($order_id = (int) $order_id){
            if(!$order = D('Bookingorder')->find($order_id)){
                $this->baoError('订单不存在');
            }elseif($order['shop_id'] != $this->shop_id){
                $this->baoError('非法操作订单');
            }elseif($order['order_status'] != 0&&$order['order_status'] != 1){
                $this->baoError('该订单无法完成');
            }else{
                if(false !== D('Bookingorder')->complete($order_id)){
                    $this->baoSuccess('订单操作成功',U('booking/index'));
                }else{
                    $this->baoError('订单操作失败');
                }
            }
        }else{
            $this->baoError('请选择要完成的订单');
        }
    }
    
    
    public function delete($order_id){
        $this->check_booking();
        if($order_id = (int) $order_id){
            if(!$order = D('Bookingorder')->find($order_id)){
                $this->baoError('订单不存在');
            }elseif($order['shop_id'] != $this->shop_id){
                $this->baoError('非法操作订单');
            }elseif($order['order_status'] != -1){
                $this->baoError('订单状态不正确');
            }else{
                if(false !== D('Bookingorder')->delete($order_id)){
                    $this->baoSuccess('订单删除成功',U('booking/index'));
                }else{
                    $this->baoError('订单删除失败');
                }
            }
        }else{
            $this->baoError('请选择要删除的订单');
        }
    }
    
    public function detail($order_id=null){
        $this->check_booking();
        if(!$order_id = (int)$order_id){
            $this->error('订单不存在');
        }elseif(!$detail = D('Bookingorder')->find($order_id)){
             $this->error('订单不存在');
        }elseif($detail['closed'] == 1){
             $this->error('订单已删除');
        }elseif($detail['shop_id'] != $this->shop_id){
             $this->error('非法的订单操作');
        }else{
            $logs = D('Paymentlogs')->getLogsByOrderId('booking', $order_id);
            $payments = D('Payment')->getPayments();
            $list = D('Bookingordermenu')->where(array('order_id'=>$order_id))->select();
            $menu_ids = array();
            foreach($list as $k=>$val){
                $menu_ids[$val['menu_id']] = $val['menu_id'];
            }
            if($menu_ids){
                $this->assign('menus',D('Bookingmenu')->itemsByIds($menu_ids));
            }
            $this->assign('list',$list);
            $this->assign('shop',$shop);
            
            $this->assign('type',$payments[$logs['code']]);
			$this->assign('room', D('Bookingroom')->find($detail['room_id']));
            $this->assign('detail',$detail);
            $this->display();
        }
    }
	
	 //订座配置
    public function setting(){
        $obj = D('Bookingsetting');
        if(IS_POST){
            $data['shop_id'] = $this->shop_id;
            $data['mobile'] = htmlspecialchars($_POST['data']['mobile']);
            if(!isMobile($data['mobile'])){
                $this->error('请填写正确的手机号码！');
            }
            $data['money'] = (int)($_POST['data']['money']* 100);
			if(empty($data['money'])){
				$this->baoError('定金不能为空或者为0');
			}
            $data['bao_time'] = (int)$_POST['data']['bao_time'];
            $data['start_time'] = (int)$_POST['data']['start_time'];
	
            $data['end_time'] = (int)$_POST['data']['end_time'];
			
            $data['is_bao'] = (int)$_POST['data']['is_bao'];
            $data['is_ting'] = (int)$_POST['data']['is_ting'];
            $obj->save($data);
            $this->baoSuccess('设置成功！',U('booking/setting'));
        }  else {
            $this->assign('cfg',$obj->getCfg());
            $this->assign('detail',$obj->detail($this->shop_id));
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

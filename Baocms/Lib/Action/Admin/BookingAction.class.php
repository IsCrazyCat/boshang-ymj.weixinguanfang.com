<?php

class BookingAction extends CommonAction {

    public function _initialize() {
        parent::_initialize();
        $this->types = D('Booking')->getDingType();
        $this->assign('dingtypes',$this->types);
    }
    
    
    public function index() {
        $Booking = D('Booking');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array('closed' => 0, 'audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        $count = $Booking->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Booking->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
    
    public function order() {
        $Bookingorder = D('Bookingorder');
        import('ORG.Util.Page'); 
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id|name|mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['order_status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
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
        $count = $Bookingorder->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Bookingorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k=>$val){
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('shops',D('Booking')->itemsByIds($shop_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->assign('status', D('Bookingorder')->getStatus());
        $this->display(); 
    }


    public function noaudit(){
        $Booking = D('Booking');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'audit' => array('IN',array(0,2)));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        
        $count = $Booking->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Booking->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show);
        $this->display(); 
    }
    
    public function create() {
        $obj = D('Booking');
        if ($this->isPost()) {
            $data = $this->createCheck();
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
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
            
            if ($obj->add($data)) {
                foreach($thumb as $k=>$val){
                        D('Bookingpics')->add(array('shop_id'=>$data['shop_id'],'photo'=>$val));
                    }
                    foreach($type as $k=>$val){
                        D('Bookingattr')->add(array('shop_id'=>$data['shop_id'],'type_id'=>$val));
                    }
                $this->baoSuccess('操作成功', U('booking/index'));
            }
            $this->baoError('操作失败');
        } else {
            $this->display();
        }
       
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), array('shop_id','shop_name', 'addr', 'city_id', 'area_id','business_id','price', 'tel','mobile','deposit','details', 'photo', 'lng', 'lat','business_time'));
        $data['shop_name'] = htmlspecialchars($data['shop_name']);
        if (empty($data['shop_name'])) {
            $this->baoError('商家名称不能为空');
        }$data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('商家地址不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->baoError('平均消费不能为空');
        }
        $data['tel'] = htmlspecialchars($data['tel']);
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
                $this->baoError('商家坐标没有选择');
            }
        $data['shop_id'] = (int)$data['shop_id'];
        if(empty($data['shop_id'])){
            $this->baoError('商家不能为空');
        }elseif(!$shop = D('Shop')->find($data['shop_id'])){
            $this->baoError('商家不存在');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if(!isMobile($data['mobile'])){
            $this->baoError('手机号格式不正确');
        }
        $data['deposit'] = (int)$data['deposit'];
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
        $data['city_id'] = $shop['city_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        } 
        
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('商家详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('商家详情含有敏感词：' . $words);
        } 
        $data['business_time'] = htmlspecialchars($data['business_time']);
        $data['audit'] = 1;
        return $data;
    }
    
    
    public function edit($shop_id = 0) {

        if ($shop_id = (int) $shop_id) {
            $obj = D('Booking');
            if (!$detail = $obj->find($shop_id)) {
                $this->baoError('请选择要编辑的订座商家');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
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
                $data['shop_id'] = $shop_id;
                $data['update_time'] = NOW_TIME;
                $data['update_ip'] = get_client_ip();
                if (false !== $obj->save($data)) {
                    D('Bookingpics')->where(array('shop_id'=>$shop_id))->delete();
                    foreach($thumb as $k=>$val){
                        D('Bookingpics')->add(array('shop_id'=>$shop_id,'photo'=>$val));
                    }
                    D('Bookingattr')->where(array('shop_id'=>$shop_id))->delete();
                    foreach($type as $k=>$val){
                        D('Bookingattr')->add(array('shop_id'=>$shop_id,'type_id'=>$val));
                    }
                    $this->baoSuccess('操作成功', U('booking/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail',$detail);
                $thumb = D('Bookingpics')->where(array('shop_id'=>$shop_id))->select();
                $this->assign('thumb', $thumb);
                $have_type = D('Bookingattr')->where(array('shop_id'=>$shop_id))->select();
                $typess = array();
                foreach ($have_type as $k=>$val){
                    $typess[$val['type_id']] = $val['type_id'];
                }
                $this->assign('have_type',$typess);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的订座商家');
        }
    }
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), array('shop_id','shop_name', 'addr', 'city_id', 'area_id','business_id','price', 'tel','mobile','deposit', 'details', 'photo', 'lng', 'lat','business_time'));
        $data['shop_name'] = htmlspecialchars($data['shop_name']);
        if (empty($data['shop_name'])) {
            $this->baoError('商家名称不能为空');
        }$data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('商家地址不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->baoError('平均消费不能为空');
        }$data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->baoError('联系电话不能为空');
        }
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
                $this->baoError('商家坐标没有选择');
            }
        $data['shop_id'] = (int)$data['shop_id'];
        if(empty($data['shop_id'])){
            $this->baoError('商家不能为空');
        }elseif(!$shop = D('Shop')->find($data['shop_id'])){
            $this->baoError('商家不存在');
        }$data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('手机号不能为空');
        }if(!isMobile($data['mobile'])){
            $this->baoError('手机号格式不正确');
        }
        $data['deposit'] = (int)$data['deposit'];
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
        $data['city_id'] = $shop['city_id'];
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
        $data['business_time'] = htmlspecialchars($data['business_time']);
        $data['audit'] = 1;
        return $data;
    }
    
    
    public function cancel($order_id=0){
        $obj = D('Bookingorder');
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj->cancel($order_id);
            $this->baoSuccess('取消成功！', U('booking/order'));
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                foreach ($order_id as $id) {
                    $obj->cancel($id);
                }
                $this->baoSuccess('取消成功！', U('booking/order'));
            }
            $this->baoError('请选择要取消的订单');
        }
    }
    
    public function complete($order_id=0){
        $obj = D('Bookingorder');
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj->complete($order_id);
            $this->baoSuccess('订单确认完成！', U('booking/order'));
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                foreach ($order_id as $id) {
                    $obj->complete($id);
                }
                $this->baoSuccess('订单确认完成！', U('booking/order'));
            }
            $this->baoError('请选择要完成的订单');
        }
    }

    public function orderdelete($order_id = 0) {
        $obj = D('bookingorder');
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj->delete($order_id);
            $this->baoSuccess('删除成功！', U('booking/order'));
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                foreach ($order_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('booking/order'));
            }
            $this->baoError('请选择要删除的订单');
        }
    }
    

    public function delete($shop_id = 0) {
        $obj = D('Booking');
        if (is_numeric($shop_id) && ($shop_id = (int) $shop_id)) {
            $obj->save(array('shop_id' => $shop_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('booking/index'));
        } else {
            $shop_id = $this->_post('shop_id', false);
            if (is_array($shop_id)) {
                foreach ($shop_id as $id) {
                    $obj->save(array('shop_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('booking/index'));
            }
            $this->baoError('请选择要删除的商家');
        }
    }

    public function audit($shop_id = 0) {
        $obj = D('Booking');
        if (is_numeric($shop_id) && ($shop_id = (int) $shop_id)) {
            $obj->save(array('shop_id' => $shop_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('booking/index'));
        } else {
            $shop_id = $this->_post('shop_id', false);
            if (is_array($shop_id)) {
                foreach ($shop_id as $id) {
                    $obj->save(array('shop_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('booking/index'));
            }
            $this->baoError('请选择要审核的订座商家');
        }
    }

    public function refuse($shop_id){
        $obj = D('Booking');
         if (is_numeric($shop_id) && ($shop_id = (int) $shop_id)) {
            if ($this->isPost()) {
                $reason = htmlspecialchars($this->_param('reason'));
                if(!$reason){
                    $this->baoError('拒绝理由不能为空');
                }
                $obj->save(array('shop_id' => $shop_id, 'audit' => 2,'reason'=>$reason));
                $this->baoSuccess('操作成功！', U('booking/index'));
            }else{
                $this->assign('shop_id',$shop_id);
                $this->display();
            }
         }
    }
    
}

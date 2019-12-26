<?php


class HotelAction extends CommonAction {

    public function _initialize() {
        parent::_initialize();
        $this->cates = D('Hotel')->getHotelCate();
        $this->assign('cates', $this->cates);
        $this->types = D('Hotelbrand')->fetchAll();
        $this->assign('hoteltypes',$this->types);
        $this->stars = D('Hotel')->getHotelStar();
        $this->assign('stars', $this->stars);
    }
    
    
    public function index() {
        $hotel = D('Hotel');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0, 'audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['hotel_name'] = array('LIKE', '%' . $keyword . '%');
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
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $hotel->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $hotel->where($map)->order(array('hotel_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }


    public function noaudit(){
        $hotel = D('Hotel');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'audit' => array('IN',array(0,2)));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['hotel_name'] = array('LIKE', '%' . $keyword . '%');
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
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $hotel->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $hotel->where($map)->order(array('hotel_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

    public function create() {
        $obj = D('Hotel');
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
            if ($hotel_id = $obj->add($data)) {
                foreach($thumb as $k=>$val){
                    D('Hotelpics')->add(array('hotel_id'=>$hotel_id,'photo'=>$val));
                }
                $this->baoSuccess('操作成功', U('hotel/index'));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
       
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), array('hotel_name','shop_id','addr', 'city_id', 'area_id','business_id','cate_id', 'type','price','star', 'tel', 'details', 'photo', 'lng', 'lat','is_wifi','is_kt','is_nq','is_tv','is_xyj','is_ly','is_bx','is_base','is_rsh','in_time','out_time'));
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
        $data['shop_id'] = (int)$data['shop_id'];
        if(empty($data['shop_id'])){
            $this->baoError('商家不能为空');
        }elseif(!$shop = D('Shop')->find($data['shop_id'])){
            $this->baoError('商家不存在');
        }
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
        $data['in_time'] = htmlspecialchars($data['in_time']);
        $data['out_time'] = htmlspecialchars($data['out_time']);
        $data['is_wifi'] = (int)$data['is_wifi'];
        $data['is_wifi'] = (int)$data['is_wifi'];
        $data['is_kt'] = (int)$data['is_kt'];
        $data['is_nq'] = (int)$data['is_nq'];
        $data['is_tv'] = (int)$data['is_tv'];
        $data['is_xyj'] = (int)$data['is_xyj'];
        $data['is_ly'] = (int)$data['is_ly'];
        $data['is_bx'] = (int)$data['is_bx'];
        $data['is_base'] = (int)$data['is_base'];
        $data['is_rsh'] = (int)$data['is_rsh'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 1;
        return $data;
    }
    
    
    public function edit($hotel_id = 0) {

        if ($hotel_id = (int) $hotel_id) {
            $obj = D('Hotel');
            if (!$detail = $obj->find($hotel_id)) {
                $this->baoError('请选择要编辑的酒店');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $thumb = $this->_param('thumb', false);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isImage($val)) {
                        unset($thumb[$k]);
                    }
                }
                $data['hotel_id'] = $hotel_id;
                if (false !== $obj->save($data)) {
                    D('Hotelpics')->where(array('hotel_id'=>$hotel_id))->delete();
                    foreach($thumb as $k=>$val){
                        D('Hotelpics')->add(array('hotel_id'=>$hotel_id,'photo'=>$val));
                    }
                    $this->baoSuccess('操作成功', U('hotel/index'));
                }
                $this->baoError('操作失败');
            } else {
                $thumb = D('Hotelpics')->where(array('hotel_id'=>$hotel_id))->select();
                $this->assign('thumb', $thumb);
                $this->assign('shop',D('Shop')->find($detail['shop_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的酒店');
        }
    }
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), array('hotel_name','shop_id','addr', 'city_id', 'area_id','business_id','cate_id', 'type','price','star', 'tel', 'details', 'photo', 'lng', 'lat','is_wifi','is_kt','is_nq','is_tv','is_xyj','is_ly','is_bx','is_base','is_rsh','in_time','out_time'));
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
        $data['shop_id'] = (int)$data['shop_id'];
        if(empty($data['shop_id'])){
            $this->baoError('商家不能为空');
        }elseif(!$shop = D('Shop')->find($data['shop_id'])){
            $this->baoError('商家不存在');
        }
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
        $data['update_time'] = NOW_TIME;
        $data['update_ip'] = get_client_ip();
        return $data;
    }
    
    
    public function delete($hotel_id = 0) {
        $obj = D('Hotel');
        if (is_numeric($hotel_id) && ($hotel_id = (int) $hotel_id)) {
            $obj->save(array('hotel_id' => $hotel_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('hotel/index'));
        } else {
            $hotel_id = $this->_post('hotel_id', false);
            if (is_array($hotel_id)) {
                foreach ($hotel_id as $id) {
                    $obj->save(array('hotel_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('hotel/index'));
            }
            $this->baoError('请选择要删除的酒店');
        }
    }

    public function audit($hotel_id = 0) {
        $obj = D('Hotel');
        if (is_numeric($hotel_id) && ($hotel_id = (int) $hotel_id)) {
            $obj->save(array('hotel_id' => $hotel_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('hotel/index'));
        } else {
            $hotel_id = $this->_post('hotel_id', false);
            if (is_array($hotel_id)) {
                foreach ($hotel_id as $id) {
                    $obj->save(array('hotel_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('hotel/index'));
            }
            $this->baoError('请选择要审核的酒店');
        }
    }

    public function refuse($hotel_id){
        $obj = D('Hotel');
         if (is_numeric($hotel_id) && ($hotel_id = (int) $hotel_id)) {
            if ($this->isPost()) {
                $reason = htmlspecialchars($this->_param('reason'));
                if(!$reason){
                    $this->baoError('拒绝理由不能为空');
                }
                $obj->save(array('hotel_id' => $hotel_id, 'audit' => 2,'reason'=>$reason));
                $this->baoSuccess('操作成功！', U('hotel/index'));
            }else{
                $this->assign('hotel_id',$hotel_id);
                $this->display();
            }
         }
    }
    
}

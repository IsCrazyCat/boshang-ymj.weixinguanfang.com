<?php

class FarmAction extends CommonAction {

   public function _initialize() {
        parent::_initialize();
        $this->group = D('Farm')->getFarmGroup();
        $this->assign('group', $this->group);
        $this->cate = D('Farm')->getFarmCate();
        $this->assign('cate', $this->cate);
    }

    public function index() {
        $farm = D('Farm');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['farm_name'] = array('LIKE', '%' . $keyword . '%');
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
        $count = $farm->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $farm->where($map)->order(array('farm_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }


    public function noaudit(){
        $farm = D('Farm');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array('closed' => 0, 'audit' => array('IN',array(0,2)));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['farm_name'] = array('LIKE', '%' . $keyword . '%');
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
        $count = $farm->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $farm->where($map)->order(array('farm_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show);
        $this->display(); 
    }

    public function create() {
        $obj = D('Farm');
        if ($this->isPost()) {
            $data = $this->createCheck();
            $thumb = $this->_param('thumb', false);
            $cate_id = $this->_param('cate_id',false);
            $group_id = $this->_param('group_id',false);

            foreach ($thumb as $k => $val) {
                if (empty($val)) {
                    unset($thumb[$k]);
                }
                if (!isImage($val)) {
                    unset($thumb[$k]);
                }
            }
            if ($farm_id = $obj->add($data)) {
                foreach($thumb as $k=>$val){
                    D('FarmPics')->add(array('farm_id'=>$farm_id,'photo'=>$val));
                }
                foreach($group_id as $key=>$val){
                    D('FarmGroupAttr')->add(array('shop_id'=>$data['shop_id'],'attr_id'=>$val));
                }
                foreach($cate_id as $k=>$v){
                    D('FarmPlayAttr')->add(array('shop_id'=>$data['shop_id'],'attr_id'=>$v));
                }
                $this->baoSuccess('操作成功', U('farm/index'));
            }
            $this->baoError('操作失败');
        }else{
            $this->display();
        }
       
    }
    
    private function createCheck() {
        
        $data = $this->checkFields($this->_post('data', false), array('shop_id', 'farm_name','intro', 'tel', 'photo', 'addr', 'city_id', 'area_id', 'business_id','price','lat', 'lng', 'business_time', 'details','notice','environmental', 'have_room', 'have_washchange', 'have_wifi', 'have_shower', 'have_tv', 'have_ticket', 'have_toiletries', 'have_hotwater'));

        $data['farm_name'] = htmlspecialchars($data['farm_name']);
        if (empty($data['farm_name'])) {
            $this->baoError('名称不能为空');
        }$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('简介不能为空');
        }$data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('地址不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->baoError('起价不能为空');
        }$data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->baoError('联系电话不能为空');
        }
        $data['type'] = (int)$data['type'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
                $this->baoError('坐标没有选择');
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
            $this->baoError('详情不能为空');
        }
        $data['notice'] = SecurityEditorHtml($data['notice']);
        if (empty($data['notice'])) {
            $this->baoError('须知不能为空');
        }
        $data['environmental'] = SecurityEditorHtml($data['environmental']);
        if (empty($data['environmental'])) {
            $this->baoError('环境不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('详情含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 1;
        return $data;
    }
    
    
    public function edit($farm_id = 0) {

        if ($farm_id = (int) $farm_id) {
            $obj = D('Farm');
            if (!$detail = $obj->where(array('farm_id'=>$farm_id))->find()) {
                $this->baoError('请选择要编辑的农家乐');
            }
            if ($this->isPost()) {
                
                $data = $this->editCheck();
                $thumb = $this->_param('thumb', false);
                $cate_id = $this->_param('cate_id',false);
                $group_id = $this->_param('group_id',false);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isImage($val)) {
                        unset($thumb[$k]);
                    }
                }
                $data['farm_id'] = $farm_id;
                if (false !== $obj->save($data)) {
                    D('FarmPics')->where(array('farm_id'=>$farm_id))->delete();
                    foreach($thumb as $k=>$val){
                        D('FarmPics')->add(array('farm_id'=>$farm_id,'photo'=>$val));
                    }
                    D('FarmGroupAttr')->where(array('shop_id'=>$data['shop_id']))->delete();
                    foreach($group_id as $key=>$val){
                        D('FarmGroupAttr')->add(array('shop_id'=>$data['shop_id'],'attr_id'=>$val));
                    }
                    D('FarmPlayAttr')->where(array('shop_id'=>$data['shop_id']))->delete();
                    foreach($cate_id as $k=>$v){
                        D('FarmPlayAttr')->add(array('shop_id'=>$data['shop_id'],'attr_id'=>$v));
                    }
                    $this->baoSuccess('操作成功', U('farm/index'));
                }
                $this->baoError('操作失败');
            } else {
    
                $thumb = D('FarmPics')->where(array('farm_id'=>$farm_id))->select();
               
                $cates = D('Farm')->getFarmCate();
                $groups = D('Farm')->getFarmGroup();
                $new_cates = $new_groups = array();
                
                $cate_id = M('FarmGroupAttr')->where(array('shop_id'=>$detail['shop_id']))->select();
                $group_id = M('FarmPlayAttr')->where(array('shop_id'=>$detail['shop_id']))->select();
          
                foreach($cates as $k => $v){
                    foreach($cate_id as $kk => $vv){
                        $new_cates[$k]['name'] = $v;
                       if($vv['attr_id'] == $k){
                           $new_cates[$k]['sel'] = 1;
                       }
                    }
                }

                foreach($groups as $key => $val){
                    foreach($group_id as $kkey => $vval){
                        $new_groups[$key]['name'] = $val;
                       if($vval['attr_id'] == $key){
                           $new_groups[$key]['sel'] = 1;
                       }
                    }
                }

                $this->assign('thumb', $thumb);
                $this->assign('new_cates', $new_cates);
                $this->assign('new_groups', $new_groups);
                $this->assign('shop',D('Shop')->find($detail['shop_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的农家乐');
        }
    }
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), array('shop_id', 'farm_name','intro', 'tel', 'photo', 'addr', 'city_id', 'area_id', 'business_id','price','lat', 'lng', 'business_time', 'details','notice','environmental', 'have_room', 'have_washchange', 'have_wifi', 'have_shower', 'have_tv', 'have_ticket', 'have_toiletries', 'have_hotwater'));
        $data['have_room'] = $data['have_room'] ? $data['have_room'] : 0;
        $data['have_washchange'] = $data['have_washchange'] ? $data['have_washchange'] : 0;
        $data['have_wifi'] = $data['have_wifi'] ? $data['have_wifi'] : 0;
        $data['have_shower'] = $data['have_shower'] ? $data['have_shower'] : 0;
        $data['have_tv'] = $data['have_tv'] ? $data['have_tv'] : 0;
        $data['have_ticket'] = $data['have_ticket'] ? $data['have_ticket'] : 0;
        $data['have_toiletries'] = $data['have_toiletries'] ? $data['have_toiletries'] : 0;
        $data['have_hotwater'] = $data['have_hotwater'] ? $data['have_hotwater'] : 0;

        
        $data['farm_name'] = htmlspecialchars($data['farm_name']);
        if (empty($data['farm_name'])) {
            $this->baoError('名称不能为空');
        }$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('简介不能为空');
        }$data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('地址不能为空');
        }$data['cate_id'] = (int)$data['cate_id'];
        if (empty($data['price'])) {
            $this->baoError('起价不能为空');
        }$data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->baoError('联系电话不能为空');
        }
        $data['type'] = (int)$data['type'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
            $this->baoError('坐标没有选择');
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
            $this->baoError('详情不能为空');
        }
        $data['notice'] = SecurityEditorHtml($data['notice']);
        if (empty($data['notice'])) {
            $this->baoError('须知不能为空');
        }
        $data['environmental'] = SecurityEditorHtml($data['environmental']);
        if (empty($data['environmental'])) {
            $this->baoError('环境不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('详情含有敏感词：' . $words);
        }
        
        
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 1;
        return $data;
    }
    
    
    public function delete($farm_id = 0) {
        $obj = D('Farm');
        if (is_numeric($farm_id) && ($farm_id = (int) $farm_id)) {
            $obj->save(array('farm_id' => $farm_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('farm/index'));
        } else {
            $farm_id = $this->_post('farm_id', false);
            if (is_array($farm_id)) {
                foreach ($farm_id as $id) {
                    $obj->save(array('farm_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('farm/index'));
            }
            $this->baoError('请选择要删除的农家乐');
        }
    }

    public function audit($farm_id = 0) {
        $obj = D('Farm');
        if (is_numeric($farm_id) && ($farm_id = (int) $farm_id)) {
            $obj->save(array('farm_id' => $farm_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('farm/index'));
        } else {
            $farm_id = $this->_post('farm_id', false);
            if (is_array($farm_id)) {
                foreach ($farm_id as $id) {
                    $obj->save(array('farm_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('farm/index'));
            }
            $this->baoError('请选择要审核的农家乐');
        }
    }

    public function refuse($farm_id){
        $obj = D('Farm');
         if (is_numeric($farm_id) && ($farm_id = (int) $farm_id)) {
            if ($this->isPost()) {
                $reason = htmlspecialchars($this->_param('reason'));
                if(!$reason){
                    $this->baoError('拒绝理由不能为空');
                }
                $obj->save(array('farm_id' => $farm_id, 'audit' => 2,'reason'=>$reason));
                $this->baoSuccess('操作成功！', U('farm/index'));
            }else{
                $this->assign('farm_id',$farm_id);
                $this->display();
            }
         }
    }
    
}

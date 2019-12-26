<?php

class FarmAction extends CommonAction {
    
    public function _initialize() {
        parent::_initialize();
        $this->group = D('Farm')->getFarmGroup();
        $this->assign('group', $this->group);
        $this->cate = D('Farm')->getFarmCate();
        $this->assign('cate', $this->cate);

    }

    
    private function check_farm(){
        
        $farm = D('Farm');
        $res =  $farm->where(array('shop_id'=>$this->shop_id))->find();
        if(!$res){
            $this->error('请先完善农家乐资料！',U('farm/set_farm'));
        }elseif($res['audit'] == 0){
            $this->error('您的农家乐申请正在审核中，请耐心等待！',U('farm/set_farm'));
        }elseif($res['audit'] == 2){
            $this->error('您的农家乐申请未通过审核！',U('farm/set_farm'));
        }else{
            return $res['farm_id'];
        }
        
    }
    
    public function index(){
        
        $farm_id = $this->check_farm();
        $farm = D('farm')->where(array('shop_id'=>$this->shop_id))->find();

        $fo = M('FarmOrder'); 
        $f = $fo->where(array('farm_id'=>$farm['farm_id']))->find();

        $map = array();
        $map['farm_id'] = $f['farm_id'];
        
        if($gotime = $this->_param('gotime', 'htmlspecialchars')){
            $gotime = strtotime($gotime);
            $map['gotime'] = array(array('ELT', $gotime+86399), array('EGT', $gotime));
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        import('ORG.Util.Page');
        $count      = $fo->where($map)->count();
        $Page       = new Page($count,25);
        $show       = $Page->show();
        $list = $fo->where($map)->order('farm_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        
        foreach($list as $k => $v){
            $p = D('FarmPackage') -> where(array('pid'=>$v['pid'])) -> find();
            $list[$k]['package'] = $p;
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display(); 
    }
    
    
    public function set_farm(){
        $obj = D('Farm');
        $Farm = $obj->where(array('shop_id'=>$this->shop_id))->find();

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
            if (empty($Farm)) {
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                if($farm_id = $obj->add($data)){
                     foreach($thumb as $k=>$val){
                        D('FarmPics')->add(array('farm_id'=>$farm_id,'photo'=>$val));
                    }
                    foreach($group_id as $key=>$val){
                        D('FarmGroupAttr')->add(array('shop_id'=>$data['shop_id'],'attr_id'=>$val));
                    }
                    foreach($cate_id as $k=>$v){
                        D('FarmPlayAttr')->add(array('shop_id'=>$data['shop_id'],'attr_id'=>$v));
                    }
                     $this->baoSuccess('设置成功', U('farm/index'));
                }else{
                    $this->baoError('设置失败');
                }
            }else{

                $data['update_time'] = NOW_TIME;
                $data['update_ip'] = get_client_ip();
                $data['audit'] = 0;

                if(false !== $obj->save($data)){
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
                    $this->baoSuccess('修改成功', U('farm/index'));
                }else{
                    $this->baoError('修改失败');
                }
            }
        } else {
            $this->assign('farm',$Farm);
            $thumb = D('FarmPics')->where(array('farm_id'=>$Farm['farm_id']))->select();
    

            $cates = D('Farm')->getFarmGroup();
            $groups = D('Farm')->getFarmCate();
            $new_cates = $new_groups = array();

            $cate_id = M('FarmGroupAttr')->where(array('shop_id'=>$this->shop_id))->select();
            $group_id = M('FarmPlayAttr')->where(array('shop_id'=>$this->shop_id))->select();

            
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
            $this->assign('cates', $cates);
            $this->assign('groups', $groups);
            $this->assign('new_cates', $new_cates);
            $this->assign('new_groups', $new_groups);
            $this->assign('shop',D('Shop')->find($detail['shop_id']));
            $this->assign('detail', $detail);

            $this->display();
        }
    }
    
    private function createCheck() {
        
        $data = $this->checkFields($this->_post('data', false), array('shop_id', 'farm_name','intro', 'tel', 'photo', 'addr', 'city_id', 'area_id', 'business_id','price','lat', 'lng', 'business_time', 'details','notice','environmental', 'have_room', 'have_washchange', 'have_wifi', 'have_shower', 'have_tv', 'have_ticket', 'have_toiletries', 'have_hotwater','audit'));

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
        $data['shop_id'] = $this->shop_id;
       
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
        $data['city_id'] = $this->shop['city_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        
        $data['have_room'] = $data['have_room'];
        $data['have_washchange'] = $data['have_washchange'];
        $data['have_wifi'] = $data['have_wifi'];
        $data['have_shower'] = $data['have_shower'];
        $data['have_tv'] = $data['have_tv'];
        $data['have_ticket'] = $data['have_ticket'];
        $data['have_toiletries'] = $data['have_toiletries'];
        $data['have_hotwater'] = $data['have_hotwater'];

        
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        } 
        
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('商家简介不能为空');
        }
        $data['notice'] = SecurityEditorHtml($data['notice']);
        if (empty($data['notice'])) {
            $this->baoError('预约须知不能为空');
        }
        $data['environmental'] = SecurityEditorHtml($data['environmental']);
        if (empty($data['environmental'])) {
            $this->baoError('店铺环境不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('详情含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 0;
        
        return $data;
    }
    
    //套餐列表
    public function package(){ 
        $farm_id = $this->check_farm();
        $fp = D('FarmPackage');
        import('ORG.Util.Page'); 
        $fo = M('FarmOrder'); 
        $f = $fo->where(array('farm_id'=>$farm['farm_id']))->find();
        $map = array('farm_id' => $farm_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $fp->where($map)->count();
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $fp->where($map)->order(array('pid' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display();
    }

    
//添加套餐
    public function setpackage(){ 
        $this->check_farm();
        if ($this->isPost()) {
            $data = $this->roomCreateCheck();
            $fp = D('FarmPackage');
            if ($farm_id = $fp->add($data)) {
                $this->baoSuccess('添加成功', U('farm/package'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    
    
    private function roomCreateCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title', 'price','jiesuan_price'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('套餐名称不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->baoError('套餐价格不能为空');
        }$data['jiesuan_price'] = (int)$data['jiesuan_price'];
        if (empty($data['jiesuan_price'])) {
            $this->baoError('套餐结算价格不能为空');
        }if ($data['jiesuan_price'] >=$data['price']) {
            $this->baoError('结算价格不能大于套餐价格');
        }

        $farm = D('Farm')->where(array('shop_id'=>$this->shop_id))->find();
        $data['farm_id'] = $farm['farm_id'];
    
        return $data;
    }
    
     
    
    public function editpackage($pid=null){
        $farm_id = $this->check_farm();
        if ($pid = (int) $pid) {
            $obj = D('FarmPackage');
            if (!$detail = $obj->find($pid)) {
                $this->baoError('请选择要编辑的套餐');
            }
            if ($detail['farm_id'] != $farm_id) {
                $this->baoError('非法操作');
            }
            if ($this->isPost()) {
                $data = $this->packageEditCheck();
                $data['pid'] = $pid;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('保存成功', U('farm/package'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail',$detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的套餐');
        }
    }

    public function deletepackage($pid=null){
        $farm_id = $this->check_farm();
        if ($pid = (int) $pid) {
            $obj = D('FarmPackage');
            if (!$detail = $obj->find($pid)) {
                $this->baoError('请选择要删除的套餐');
            }
            if ($detail['farm_id'] != $farm_id) {
                $this->baoError('非法操作');
            }
            if (false !== $obj->delete($pid)) {
                $this->baoSuccess('删除成功', U('farm/package'));
            }else {
                $this->baoError('删除失败');
            }
        } else {
            $this->baoError('请选择要删除的套餐');
        }
    }    
    
    private function packageEditCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title', 'price','jiesuan_price'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('套餐名称不能为空');
        }$data['price'] = (int)$data['price'];
        if (empty($data['price'])) {
            $this->baoError('套餐价格不能为空');
        }$data['jiesuan_price'] = (int)$data['jiesuan_price'];
        if (empty($data['jiesuan_price'])) {
            $this->baoError('套餐结算价格不能为空');
        }if ($data['jiesuan_price'] >=$data['price']) {
            $this->baoError('结算价格不能大于套餐价格');
        }
        $farm = D('Farm')->where(array('shop_id'=>$this->shop_id))->find();
        $data['farm_id'] = $farm['farm_id'];
        return $data;
    }


    public function cancel($order_id){
        $farm_id = $this->check_farm();
        if($order_id = (int) $order_id){
            if(!$order = D('FarmOrder')->find($order_id)){
                $this->baoError('订单不存在');
            }elseif($order['farm_id'] != $farm_id){
                $this->baoError('非法操作订单');
            }elseif($order['order_status'] == -1){
                $this->baoError('该订单已取消');
            }else{
                if(false !== D('FarmOrder')->cancel($order_id)){
                    $this->baoSuccess('订单取消成功',U('farm/index'));
                }else{
                    $this->baoError('订单取消失败');
                }
            }
        }else{
            $this->baoError('请选择要取消的订单');
        }
    }
    
    
    public function complete($order_id){
        $farm_id = $this->check_farm();
        if($order_id = (int) $order_id){
            if(!$order = D('FarmOrder')->find($order_id)){
                $this->baoError('订单不存在');
            }elseif($order['farm_id'] != $farm_id){
                $this->baoError('非法操作订单');
            }elseif($order['order_status'] != 1){
                $this->baoError('该订单无法完成');
            }else{
                if(false !== D('FarmOrder')->complete($order_id)){
                    $this->baoSuccess('订单操作成功',U('farm/index'));
                }else{
                    $this->baoError('订单操作失败');
                }
            }
        }else{
            $this->baoError('请选择要完成的订单');
        }
    }
    
    
    public function delete($order_id){
        $farm_id = $this->check_farm();
        if($order_id = (int) $order_id){
            if(!$order = D('FarmOrder')->find($order_id)){
                $this->baoError('订单不存在');
            }elseif($order['farm_id'] != $farm_id){
                $this->baoError('非法操作订单');
            }elseif($order['order_status'] != -1){
                $this->baoError('订单状态不正确');
            }else{
                if(false !== D('FarmOrder')->save(array('order_id'=>$order_id,'closed'=>1))){
                    $this->baoSuccess('订单删除成功',U('farm/index'));
                }else{
                    $this->baoError('订单删除失败');
                }
            }
        }else{
            $this->baoError('请选择要删除的订单');
        }
    }
    
    public function detail($order_id=null){
        $farm_id = $this->check_farm();
        if(!$order_id = (int)$order_id){
            $this->error('订单不存在');
        }elseif(!$detail = D('FarmOrder')->find($order_id)){
             $this->error('订单不存在');
        }elseif($detail['closed'] == 1){
             $this->error('订单已删除');
        }elseif($detail['farm_id'] != $farm_id){
             $this->error('非法的订单操作');
        }else{
            $f = D('Farm')->where(array('farm_id'=>$detail['farm_id']))->find();
            $detail['farm'] = $f;
            $this->assign('detail',$detail);            
            $this->display();
        }
    }


  
}

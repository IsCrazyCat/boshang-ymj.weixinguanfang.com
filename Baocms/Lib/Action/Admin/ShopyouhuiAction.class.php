<?php
class ShopyouhuiAction extends CommonAction{
    private $create_fields = array('yh_id','shop_id', 'type_id', 'discount','deduction', 'min_amount', 'amount', 'is_open','vacuum');
    public function index(){
        $Shopyouhui = D('Shopyouhui');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $user = D('Users')->find($user_id);
            $this->assign('nickname', $user['nickname']);
            $this->assign('user_id', $user_id);
        }
        $count = $Shopyouhui->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Shopyouhui->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($shop_ids)) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

	 public function edit($yh_id = 0) {
		$yh_id = (int) $yh_id;
		$obj = D('Shopyouhui');
		if (!($detail = $obj->find($yh_id))) {
                $this->baoError('请选择要编辑的商家优惠');
        }
        if ($this->isPost()) {
            $data = $this->createCheck();
            if(!$data['yh_id']){
                if ($obj->add($data)) {
                    $this->baoSuccess('添加成功', U('shopyouhui/index'));
                    if($this->shop['is_breaks'] == 0){
                        D('Shop')->save(array('shop_id'=>$this->shop_id,'is_breaks'=>1));//可要可不要
                    }
                }
                $this->baoError('操作失败！');
            }else{
                if (false !== $obj->save($data)) {
                   $this->baoSuccess('修改成功', U('shopyouhui/index'));
                }
                $this->baoError('操作失败！');
            }
        }else{
            $this->assign('detail', $detail);
			$this->assign('shops', D('Shop')->find($detail['shop_id']));
            $this->display(); // 输出模板
        }
    }
	
	
	
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['yh_id'] = (int)$data['yh_id'];
        $data['is_open'] = 1;
        $data['shop_id'] = $data['shop_id'];
        if($data['type_id'] == 0){ //折扣优惠
            $data['discount'] = $data['discount'];
            if (empty($data['discount'])) {
                $this->baoError('折扣不能为空');
            }elseif(!is_numeric($data['discount'])){
                $this->baoError('折扣格式不正确');
            }
			
			$data['deduction'] = $data['deduction'];
            if (!empty($data['deduction'])) {
                if(!is_numeric($data['deduction'])){
					$this->baoError('扣除点数格式不正确');
				}elseif($data['deduction'] > $data['deduction']){
					$this->baoError('扣除点数不能大于折扣');
				}
            }
			
        }else{
            $data['min_amount'] = $data['min_amount'];
            if (empty($data['min_amount'])) {
                $this->baoError('满多少不能为空');
            }elseif(!is_numeric($data['discount'])){
                $this->baoError('满多少格式不正确');
            }
            $data['amount'] = $data['amount'];
            if (empty($data['amount'])) {
                $this->baoError('减多少不能为空');
            }elseif(!is_numeric($data['discount'])){
                $this->baoError('减多少格式不正确');
            }
			
			$data['vacuum'] = $data['vacuum'];
            if (!empty($data['vacuum'])) {
                if(!is_numeric($data['vacuum'])){
					$this->baoError('网站抽成格式不正确');
				}elseif($data['vacuum'] > $data['amount']){
					$this->baoError('网站抽成不能大于减多少钱');
				}
            }
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
	
	 public function audit($yh_id) {
        $obj = D('Shopyouhui');
        if (!($detail = $obj->find($yh_id))) {
            $this->error('请选择要审核的优惠商家');
        }
        $data = array('audit' => 0, 'yh_id' => $yh_id);
        if ($detail['audit'] == 0) {
            $data['audit'] = 1;
        }
        $obj->save($data);
        $this->baoSuccess('操作成功', U('Shopyouhui/index'));
    }
	
	
    public function delete($yh_id = 0){
            $yh_id = (int) $yh_id;
			if(!empty($yh_id)){
				D('Shopyouhui')->delete($yh_id);
				$this->baoSuccess('删除成功！', U('Shopyouhui/index'));
			}else{
				$this->baoError('操作失败');	
		   }
     }
}
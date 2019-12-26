<?php
class SmsshopAction extends CommonAction{
    private $create_fields = array('user_id', 'type', 'shop_id', 'num');
    private $edit_fields = array('user_id', 'type', 'shop_id', 'num');
    public function index(){
        $Smsshop = D('Smsshop');
        import('ORG.Util.Page');
        $map = array('closed' => array('IN', '0,-1'));
       if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['user_id|shop_id|log_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);

        }
        $count = $Smsshop->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Smsshop->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
				$user_ids[$val['user_id']] = $val['user_id'];
            }
        }
		if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
		if ($shop_ids) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Smsshop');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('smsshop/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('shops', D('Shop')->find($detail['shop_id']));
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('账户不能为空');
        }if (D('Users')->getUserByAccount($data['account'])) {
            $this->baoError('该会员账户已经存在！');
        }$data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('账户不能为空');
        }
		$data['type'] = shop;
        if (empty($data['type'])) {
            $this->baoError('类型不能为空');
        }$data['num'] = htmlspecialchars($data['num']);
        if (empty($data['num'])) {
            $this->baoError('数量不能为空');
        }if ($data['num'] % 100 != 0) {
            $this->baoError('总需人次必须为100的倍数');
        }
		$data['status'] = 0;	
        $data['create_ip'] = get_client_ip();
        $data['create_time'] = NOW_TIME;
        return $data;
    }
    public function edit($log_id = 0){
        if ($log_id = (int) $log_id) {
            $obj = D('Smsshop');
            if (!($detail = $obj->find($log_id))) {
                $this->baoError('请选择要编辑的会员');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['log_id'] = $log_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('smsshop/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
				$this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的会员');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('账户不能为空');
        }if (D('Users')->getUserByAccount($data['account'])) {
            $this->baoError('该会员账户已经存在！');
        }$data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('账户不能为空');
        }
		$data['type'] = shop;
        if (empty($data['type'])) {
            $this->baoError('类型不能为空');
        }$data['num'] = htmlspecialchars($data['num']);
        if (empty($data['num'])) {
            $this->baoError('数量不能为空');
        }if ($data['num'] % 100 != 0) {
            $this->baoError('总需人次必须为100的倍数');
        }
		$data['status'] = 0;	
        $data['create_ip'] = get_client_ip();
        $data['create_time'] = NOW_TIME;
        return $data;
    }
    public function delete($log_id = 0){
        if (is_numeric($log_id) && ($log_id = (int) $log_id)) {
            $obj = D('Smsshop');
            $obj->delete($log_id);
            $this->baoSuccess('删除成功！', U('smsshop/index'));
        } else {
            $log_id = $this->_post('user_id', false);
            if (is_array($log_id)) {
                $obj = D('Smsshop');
                foreach ($log_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('smsshop/index'));
            }
            $this->baoError('请选择要删除的会员');
        }
    }
	//增加短信
    public function num(){
       $log_id = (int)$this->_get('log_id'); 
       if(empty($log_id)) {
		   $this->baoError ('请选择id');
	   }
	   if(!$detail = D('Smsshop')->find($log_id)){
           $this->baoError('没有记录！');
       }
       if($this->isPost()){
           $num = (int)  $this->_post('num');
		   
		  
		   
           if($num == 0){
               $this->baoError('请输入正确的短信数');
           }
           if($detail['num'] + $num < 0){
			   $this->baoError('短信操作错误！');
		   }
           D('Smsshop')->save(array('log_id'=>$log_id,'num'=> $detail['num'] + $num));
           $this->baoSuccess('操作成功',U('smsshop/index'));
       }else{
           $this->assign('log_id',$log_id);
           $this->display();
       }       
    }
}
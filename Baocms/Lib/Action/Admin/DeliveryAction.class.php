<?php
class DeliveryAction extends CommonAction{
	
	private $create_fields = array('city_id', 'user_id','photo', 'name', 'mobile', 'addr');
	private $edit_fields = array('city_id', 'user_id','photo', 'name', 'mobile', 'addr');
	
    public function index(){
        $Delivery = D('Delivery');
        import('ORG.Util.Page');
		$map = array('closed' => 0);
		if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['user_id|name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Delivery->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Delivery->where($map)->order('create_time')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $user_ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
		$obj = D('Delivery');
        if ($this->isPost()) {
            $data = $this->createCheck();
            if ($id = $obj->add($data)) {
                $this->baoSuccess('添加成功', U('delivery/index'));
            }
            $this->baoError('申请失败！');
        } else {
			$this->assign('user_delivery', $user_delivery);
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传身份证');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('身份证格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('姓名不能为空');
        }
		$data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('电话不能为空');
        }
        if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->baoError('电话应该为13位手机号码');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('地址不能为空');
        } 
		$data['audit'] = 1;       
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
     public function edit($id = 0){
        if ($id = (int) $id) {
            $obj = D('Delivery');
            if (!($detail = $obj->find($id))) {
                $this->baoError('请选择要编辑的配送员');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('delivery/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的配送员');
        }
    }
	
	 private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传身份证');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('身份证格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('姓名不能为空');
        }
		$data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('电话不能为空');
        }
        if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->baoError('电话应该为13位手机号码');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('地址不能为空');
        } 
		$data['audit'] = 1;       
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function lists(){
        $id = I('id', '', 'intval,trim');
        if (!$id) {
            $this->baoError('没有选择！');
        } else {
			$Delivery = D('Delivery')->where('id =' . $id)->find();
			$users = D('Users')->find($Delivery['user_id']);
            $this->assign('delivery', D('Delivery')->where('id =' . $id)->find());
            $dvo = D('DeliveryOrder');
            import('ORG.Util.Page');
			
			if ($order_id = (int) $this->_param('order_id')) {
				$map['order_id'] = $order_id;
				$this->assign('order_id', $order_id);
			}
		
		
			if (isset($_GET['st']) || isset($_POST['st'])) {
				$st = (int) $this->_param('st');
				if ($st != 999) {
					$map['status'] = $st;
				}
				$this->assign('st', $st);
			} else {
				$this->assign('st', 999);
			}
			
		
            $count = $dvo->where('delivery_id =' . $users['user_id'])->count();
            $Page = new Page($count, 25);
            $show = $Page->show();
            $list = $dvo->where('delivery_id =' . $users['user_id'])->order('order_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->display();
        }
    }
	
	// 新增选择配送员
	public function select(){
        $Delivery = D('Delivery');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array();
        if($name = $this->_param('name','htmlspecialchars')){
            $map['name'] = array('LIKE','%'.$name.'%');
            $this->assign('name',$name);
        }
        $count = $Delivery->where($map)->count(); 
        $Page = new Page($count, 8); 
        $pager = $Page->show(); // 分页显示输出
        $list = $Delivery->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
        $this->assign('list', $list);
        $this->assign('page', $pager); 
        $this->display(); 
        
    }
	
	public function delete($id = 0){
        if (is_numeric($id) && ($id = (int) $id)) {
            $obj = D('Delivery');
            $obj->save(array('id' => $id,'closed'=>1));
            $this->baoSuccess('删除成功！', U('delivery/index'));
        } else {
            $id = $this->_post('id', false);
            if (is_array($id)) {
                $obj = D('Delivery');
                foreach ($id as $id) {
                    $obj->save(array('id'=>$id, 'closed'=>1));
                }
                $this->baoSuccess('删除成功！', U('delivery/index'));
            }
            $this->baoError('请选择要删除的配送员');
        }
    }
    public function audit($id = 0){
        if (is_numeric($id) && ($id = (int) $id)) {
            $obj = D('Delivery');
            $obj->save(array('id' => $id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('delivery/index'));
        } else {
            $id = $this->_post('id', false);
            if (is_array($id)) {
                $obj = D('Delivery');
                foreach ($id as $id) {
                    $obj->save(array('id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('delivery/index'));
            }
            $this->baoError('请选择要审核的配送员');
        }
    }
}
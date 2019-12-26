<?php

class BillAction extends CommonAction
{
    private $create_fields = array('bill_type_name', 'bill_fields', 'bill_fields_label', 'memo', 'areas', 'enable', 'sms_notify', 'fee_rate', 'integral');
    private $edit_fields = array('bill_type_name', 'bill_fields', 'bill_fields_label', 'memo', 'areas', 'enable', 'sms_notify', 'fee_rate', 'integral');

    public function billtype()
    {
        $model = D('Billtype');
        $list = $model->select();
        $this->assign('list', $list);
        $this->display();
    }

    public function createtype()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Billtype');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('bill/billtype'));
            }
            $this->baoError('操作失败！');
        } else {
            $menu = D('Billtype')->fetchAll();
            $this->assign('datas', $menu);
			$this->assign('citys', D('City')->fetchAll());
			$this->assign('areas', D('Area')->fetchAll());			
            $this->display();
        }
    }
	
	 private function deletetype($id = 0){
     
        if (is_numeric($bill_type_id) && ($bill_type_id = (int) $bill_type_id)) {
            $obj = D('Billtype');
            $obj->delete($bill_type_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('bill/billtype'));
        } else {
            $bill_type_id = $this->_post('bill_type_id', false);
            if (is_array($bill_type_id)) {
                $obj = D('Billtype');
                foreach ($bill_type_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('bill/billtype'));
            }
            $this->baoError('请选择要删除的缴费类型');
        } 
    }
	

    public function edittype($id = 0)
    {
        if ($id = (int)$id) {
            $obj = D('Billtype');
            $menu = $obj->fetchAll();
            if (!isset($menu[$id])) {
                $this->baoError('请选择要编辑的缴费类型');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['bill_type_id'] = $id;
                if ($obj->save($data)) {
                    $obj->cleanCache();
                }
                $this->baoSuccess('操作成功', U('bill/billtype'));
            } else {
                $this->assign('detail', $menu[$id]);
				$this->assign('citys', D('City')->fetchAll());
				$this->assign('areas', D('Area')->fetchAll());
                $this->display('createtype');
            }
        } else {
            $this->baoError('请选择要编辑的缴费类型');
        }
    }
	
	
	
	

    public function billorder()
    {
        $model = D('Billorder');
        import('ORG.Util.Page');// 导入分页类
        $map = array();
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
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
        if ($user_id = (int)$this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['memo'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($bill_type_id = (int)$this->_param('bill_type_id')) {
            $map['bill_type_id'] = $bill_type_id;
            $this->assign('bill_type_id', $bill_type_id);
        }
        if (isset($_POST['status'])) {
            $status = (int)$this->_param('status');
            if ($status != -1) {
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        }
        $count = $model->where($map)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 15);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        $list = $model->where($map)->order(array('bill_order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }

        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('billtypes', D('Billtype')->fetchAll());
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->display(); // 输出模板
    }

    public function process() {
        $status = (int) $_POST['status'];
        if (!in_array($status, array(1, 2))) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'状态不对'));
        }
        $id = (int)$_POST['id'];
        $value = $this->_param('value', 'htmlspecialchars');
        $oderModel = D('Billorder');
        if(empty($value)){
            $this->ajaxReturn(array('status'=>'error','msg'=>'请填写处理说明'));
        }
        if(empty($id)|| !$detail = $oderModel->find($id)){
            $this->ajaxReturn(array('status'=>'error','msg'=>'参数错误'));
        }
        $billType = D('Billtype')->find($detail['bill_type_id']);
        if (!$billType) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'缴费类型不存在'));
        }
        $money = $detail['money'];
        $Users = D('Users');
        if($status == 1){
            if ($billType['sms_notify']) {
                $user = $Users->find($detail['user_id']);
                if ($user['mobile']) {
                    D('Sms')->sendSms('sms_bill_order_confirm', $user['mobile'], array(
                        'billtype' => $billType['bill_type_name']
                    ));
                }
            }
            if ($billType['integral']) {
                $Users->addIntegral($detail['user_id'], $billType['integral'], $billType['bill_type_name'] . '缴费奖励');
            }
			
        }
        if($status == 2){
            $intro = $billType['bill_type_name'] . '缴费失败,退款,处理说明:' . $value;
			$obj = D('Users');
            $obj->addMoney($detail['user_id'], $money, $intro);
            if ($detail['interest']) {
                $Users->addIntegral($detail['user_id'], 0, $detail['interest'], $intro);
                $Users->updateCount($detail['user_id'], 'used_interest', -$detail['interest']);
            }
            if ($billType['sms_notify']) {
                $user = $Users->find($detail['user_id']);
                if ($user['mobile']) {
                    D('Sms')->sendSms('sms_bill_order_refund', $user['mobile'], array(
                        'billtype' => $billType['bill_type_name'],
                        'memo' => $value
                    ));
                }
            }
			
        }
        $oderModel->save(array('bill_order_id' => $id, 'status' => $status,'admin_memo' => $value, 'admin_time' => NOW_TIME, 'admin_id' => $this->_admin['admin_id']));
        $this->ajaxReturn(array('status'=>'success','msg'=>'操作成功','url'=>U('bill/billorder')));
    }

    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['bill_fields'] = implode(',', $data['bill_fields']);
		$data['bill_fields_label'] = implode(',', $data['bill_fields_label']);
        $data['fee_rate'] = (float)$data['fee_rate'];
        $data['integral'] = (int)$data['integral'];
        $data['sms_notify'] = (int)$data['sms_notify'];
        $data['enable'] = (int)$data['enable'];
		$data['memo'] = trim($data['memo']);
		$data['areas'] = implode(',', $data['areas']);
        if (empty($data['bill_type_name'])) {
            $this->baoError('请输入缴费类型');
        }
        $data['bill_type_name'] = htmlspecialchars($data['bill_type_name'], ENT_QUOTES, 'UTF-8');
        return $data;
    }

    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['bill_fields'] = implode(',', $data['bill_fields']);
		$data['bill_fields_label'] = implode(',', $data['bill_fields_label']);
        $data['fee_rate'] = (float)$data['fee_rate'];
        $data['integral'] = (int)$data['integral'];
        $data['sms_notify'] = (int)$data['sms_notify'];
        $data['enable'] = (int)$data['enable'];
		$data['memo'] = trim($data['memo']);
		$data['areas'] = implode(',', $data['areas']);
        if (empty($data['bill_type_name'])) {
            $this->baoError('请输入缴费类型');
        }
        $data['bill_type_name'] = htmlspecialchars($data['bill_type_name'], ENT_QUOTES, 'UTF-8');
        return $data;
    }
	
	
}

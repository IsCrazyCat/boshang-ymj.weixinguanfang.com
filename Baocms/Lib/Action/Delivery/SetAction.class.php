<?php
class SetAction extends CommonAction {
	private $edit_fields = array('city_id', 'user_id','photo', 'name', 'mobile', 'addr');
	
    public function index() {
		$detail = D('Delivery') ->where(array('user_id'=>$this->delivery_id))->find();
		if(empty($detail)){
			 $this->error('未知错误');
		}elseif($detail['audit'] !=1){
			 $this->error('您的信息未审核');	
		}elseif($detail['closed'] !=0){
			 $this->error('状态不正确');		
		}
		$this->assign('detail', $detail); 
        $this->display();
    }
	
	public function edit($id = 0){
		 if ($id = (int) $id) {
            $obj = D('Delivery');
            if (!($detail = $obj->find($id))) {
                $this->error('请选择要编辑的资料');
            }
			if($detail['user_id'] !=$this->delivery_id){
				$this->error('不能修改其他人的资料');
			}
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
                    $this->fengmiMsg('操作成功', U('set/index'));
                }
                $this->fengmiMsg('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->fengmiMsg('请选择要编辑的资料');
        }
    }
	
	 private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		$data['user_id'] = $this->delivery_id;
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->fengmiMsg('请上传身份证');
        }
        if (!isImage($data['photo'])) {
            $this->fengmiMsg('身份证格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->fengmiMsg('姓名不能为空');
        }
		$data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->fengmiMsg('电话不能为空');
        }
        if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->fengmiMsg('电话应该为13位手机号码');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->fengmiMsg('地址不能为空');
        }        
        return $data;
    }
	
	public function sms($id){
        $obj = D('Delivery');
        if (!($detail = $obj->find($id))) {
            $this->error('请选择要编辑的配送员资料');
        }
		if($detail['user_id'] !=$this->delivery_id){
			$this->error('不能修改其他人的资料');
		}
        $data = array('is_sms' => 0, 'id' => $id);
        if ($detail['is_sms'] == 0) {
            $data['is_sms'] = 1;
        }
        $obj->save($data);
        $this->fengmiMsg('修改短信通知状态成功', U('set/index'));
    }
	public function weixin($id){
        $obj = D('Delivery');
        if (!($detail = $obj->find($id))) {
            $this->error('请选择要编辑的配送员资料');
        }
		if($detail['user_id'] !=$this->delivery_id){
			$this->error('不能修改其他人的资料');
		}
        $data = array('is_weixin' => 0, 'id' => $id);
        if ($detail['is_weixin'] == 0) {
            $data['is_weixin'] = 1;
        }
        $obj->save($data);
        $this->fengmiMsg('修改微信通知状态成功', U('set/index'));
    }
	public function music($id){
        $obj = D('Delivery');
        if (!($detail = $obj->find($id))) {
            $this->error('请选择要编辑的配送员资料');
        }
		if($detail['user_id'] !=$this->delivery_id){
			$this->error('不能修改其他人的资料');
		}
        $data = array('is_music' => 0, 'id' => $id);
        if ($detail['is_music'] == 0) {
            $data['is_music'] = 1;
        }
        $obj->save($data);
        $this->fengmiMsg('修改语音通知状态成功', U('set/index'));
    }
	
}
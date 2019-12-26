<?php
class OwnerAction extends CommonAction{
    public function index(){
        $owner = D('Communityowner');
        import('ORG.Util.Page');
        $map = array('community_id' => $this->community_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['number|location'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $owner->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $owner->order(array('owner_id' => 'desc'))->where($map)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function audit($owner_id){
        $owner_id = (int) $owner_id;
        if (empty($owner_id)) {
            $this->error('该业主不存在');
        }
        if (!($detail = D('Communityowner')->find($owner_id))) {
            $this->error('该业主不存在');
        }
        if ($detail['community_id'] != $this->community_id) {
            $this->error('不能操作其他小区业主');
        }
        if ($this->isPost()) {
            $data['number'] = (int) $_POST['number'];
            if (empty($data['number'])) {
                $this->fengmiMsg('户号不能为空');
            }
            $data['owner_id'] = $owner_id;
            $data['audit'] = 1;
            $obj = D('Communityowner');
            if (false !== $obj->save($data)) {
                $this->fengmiMsg('审核成功', U('owner/index'));
            }
            $this->fengmiMsg('操作失败！');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    public function delete(){
        $owner_id = (int) $this->_get('owner_id');
        $obj = D('Communityowner');
        $detail = $obj->find($owner_id);
        if (!empty($detail) && $detail['community_id'] == $this->community_id) {
			$Communityorder = D('Communityorder')->where(array('user_id'=>$detail['user_id']))->find();
			if(!empty($Communityorder)){
				$this->ajaxReturn(array('status' => 'error', 'msg' => '该用户还有账单暂时无法删除'));
			}
            $obj->delete($owner_id);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功', U('owner/index')));
        }
        $this->ajaxReturn(array('status' => 'error', 'msg' => '非法操作'));
    }
}
<?php
class MsgAction extends CommonAction
{
    private $create_fields = array('user_id', 'type', 'title', 'intro', 'link_url', 'create_time', 'create_ip', 'details');
    private $edit_fields = array('user_id', 'type', 'title', 'intro', 'link_url', 'details');
    public function index()
    {
        $Msg = D('Msg');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Msg->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Msg->where($map)->order(array('msg_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('types', $Msg->getType());
        $this->display();
        // 输出模板
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Msg');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('msg/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('types', D('Msg')->getType());
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        $data['type'] = htmlspecialchars($data['type']);
        if (empty($data['type'])) {
            $this->baoError('类型不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('描述不能为空');
        }
        $data['link_url'] = htmlspecialchars($data['link_url']);
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('详细内容含有敏感词：' . $words);
        }
        return $data;
    }
    public function edit($msg_id = 0)
    {
        if ($msg_id = (int) $msg_id) {
            $obj = D('Msg');
            if (!($detail = $obj->find($msg_id))) {
                $this->baoError('请选择要编辑的手机消息');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['msg_id'] = $msg_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('msg/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('types', D('Msg')->getType());
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的手机消息');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        $data['type'] = htmlspecialchars($data['type']);
        if (empty($data['type'])) {
            $this->baoError('类型不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('描述不能为空');
        }
        $data['link_url'] = htmlspecialchars($data['link_url']);
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('详细内容含有敏感词：' . $words);
        }
        return $data;
    }
    public function delete($msg_id = 0)
    {
        if (is_numeric($msg_id) && ($msg_id = (int) $msg_id)) {
            $obj = D('Msg');
            $obj->delete($msg_id);
            $this->baoSuccess('删除成功！', U('msg/index'));
        } else {
            $msg_id = $this->_post('msg_id', false);
            if (is_array($msg_id)) {
                $obj = D('Msg');
                foreach ($msg_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('msg/index'));
            }
            $this->baoError('请选择要删除的手机消息');
        }
    }
    public function detail($msg_id)
    {
       $msg_id = (int) $msg_id;
        D('Msg')->updateCount($msg_id, 'views');
        if (!($detail = D('Msg')->find($msg_id))) {
            $this->error('消息不存在');
        }

		if ($detail['cate_id'] != 3) {
            $this->error('类型错误');
        }
		
		if (!empty($detail['city_id'])) {//如果表里面会员不为空那么判断ID正常不
            if ($detail['city_id'] != $this->city_id) {
            $this->error('您没有权限查看该消息');
        	}
        }
     
		//连接不等于空自动跳转
        if ($detail['link_url']) {
            header("Location:" . $detail['link_url']);
            die;
        }
        $this->assign('detail', $detail);
        $this->display();
    }
}
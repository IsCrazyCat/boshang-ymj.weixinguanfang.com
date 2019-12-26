<?php
class MsgAction extends CommonAction
{
    private $create_fields = array('city_id', 'cate_id', 'user_id', 'shop_id', 'admin_id', 'community_id', 'delivery_id', 'worker_id', 'village_id', 'type', 'title', 'intro', 'link_url', 'details', 'create_time', 'create_ip');
    private $edit_fields = array('city_id', 'cate_id', 'user_id', 'shop_id', 'admin_id', 'community_id', 'delivery_id', 'worker_id', 'village_id', 'type', 'title', 'intro', 'link_url', 'details', 'create_time', 'create_ip');
    protected function _initialize()
    {
        parent::_initialize();
        $getMsgCate = D('Msg')->getMsgCate();
        $this->assign('getMsgCate', $getMsgCate);
    }
    public function index() {
        $Msg = D('Msg');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('closed'=>0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $cate_id = (int) $this->_param('cate_id');
        if ($cate_id) {
            $map['cate_id'] = $cate_id;
        }
        $count = $Msg->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Msg->where($map)->order(array('msg_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $msg_ids = array();
        foreach ($list as $k => $val) {
            if ($val['msg_id']) {
                $msg_ids[$val['msg_id']] = $val['msg_id'];
            }
        }
        $this->assign('msg', D('Msg')->itemsByIds($msg_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('types', $Msg->getType());
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Msg');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('msg/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('areas', D('Area')->select());
            $this->assign('communitys', D('Community')->select());//不查询缓存
            $this->assign('villages', D('Village')->select());//不查询缓存
            $this->assign('business', D('Business')->fetchAll());
            $this->assign('user', D('Users')->find($detail['user_id']));
            $this->assign('types', D('Msg')->getType());//通知用户类型
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('通知类型不能为空');
        }
        $data['city_id'] = (int) $data['city_id'];
        $data['user_id'] = (int) $data['user_id'];
        $data['shop_id'] = (int) $data['shop_id'];
        $data['admin_id'] = (int) $data['admin_id'];
        $data['community_id'] = (int) $data['community_id'];
        $data['delivery_id'] = (int) $data['delivery_id'];
        $data['worker_id'] = (int) $data['worker_id'];
        $data['village_id'] = (int) $data['village_id'];
        $data['type'] = htmlspecialchars($data['type']);
        //普通商家发送的时候选择type
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
                $this->assign('areas', D('Area')->select());
                $this->assign('business', D('Business')->fetchAll());
                $this->assign('types', D('Msg')->getType());//通知用户类型
                $this->assign('user', D('Users')->find($detail['user_id']));//查会员
                $this->assign('shop', D('Shop')->find($datail['shop_id']));//查商家
                $this->assign('community', D('Community')->find($datail['community_id']));//查小区
                $this->assign('village', D('Village')->find($datail['village_id']));//查乡村
                $this->assign('delivery', D('Delivery')->find($datail['delivery_id']));//查配送员
				$this->assign('worker', D('Users')->find($datail['worker_id']));
                //查员工
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的手机消息');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('通知类型不能为空');
        }
        $data['city_id'] = (int) $data['city_id'];
        $data['user_id'] = (int) $data['user_id'];
        $data['shop_id'] = (int) $data['shop_id'];
        $data['admin_id'] = (int) $data['admin_id'];
        $data['community_id'] = (int) $data['community_id'];
        $data['delivery_id'] = (int) $data['delivery_id'];
        $data['worker_id'] = (int) $data['worker_id'];
        $data['village_id'] = (int) $data['village_id'];
        $data['type'] = htmlspecialchars($data['type']);
        //普通商家发送的时候选择type
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
    public function delete($msg_id = 0){
        if (is_numeric($msg_id) && ($msg_id = (int) $msg_id)) {
            $obj = D('Msg');
            $obj->save(array('msg_id' => $msg_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('msg/index'));
        } else {
            $msg_id = $this->_post('msg_id', false);
            if (is_array($msg_id)) {
                $obj = D('Msg');
                foreach ($msg_id as $id) {
                    $obj->save(array('msg_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('批量删除成功！', U('msg/index'));
            }
            $this->baoError('请选择要批量删除的手机消息');
        }
    }
    public function detail($msg_id){
        $Msg = D('Msg');
        $detail = $Msg->find($msg_id);
        if (empty($detail)) {
            $this->error('没有该信息或该信息已被删除！', U('Substation/index/main'));
        }
        $this->assign('user', D('Users')->find($detail['send_id']));
        $this->assign('detail', $detail);
        $this->display();
    }
    //选择通知小区
    public function child_community($area_id = 0){
        $datas = D('Community')->select();
        $str = '<option value="0">请选择</option>';
        foreach ($datas as $val) {
            if ($val['area_id'] == $area_id) {
                $str .= '<option value="' . $val['community_id'] . '">' . $val['name'] . '</option>';
            }
        }
        echo $str;
        die;
    }
    //选择通知商家
    public function child_shop($area_id = 0)
    {
        $datas = D('Shop')->select();
        $str = '<option value="0">请选择</option>';
        foreach ($datas as $val) {
            if ($val['area_id'] == $area_id) {
                $str .= '<option value="' . $val['shop_id'] . '">' . $val['shop_name'] . '</option>';
            }
        }
        echo $str;
        die;
    }
    //选择通知乡村
    public function child_village($area_id = 0)
    {
        $datas = D('Village')->select();
        $str = '<option value="0">请选择</option>';
        foreach ($datas as $val) {
            if ($val['area_id'] == $area_id) {
                $str .= '<option value="' . $val['village_id'] . '">' . $val['name'] . '</option>';
            }
        }
        echo $str;
        die;
    }
}
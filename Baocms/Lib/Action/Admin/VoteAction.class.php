<?php
class VoteAction extends CommonAction
{
    private $create_fields = array('title', 'is_select', 'num', 'banner', 'orderby', 'is_pic', 'shop_id', 'end_date');
    private $edit_fields = array('title', 'shop_id', 'num', 'banner', 'orderby', 'is_select', 'is_pic', 'end_date');
    public function index()
    {
        $Vote = D('Vote');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        $count = $Vote->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Vote->where($map)->order(array('vote_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Vote');
            if ($vote_id = $obj->add($data)) {
                $this->baoSuccess('添加成功', U('vote/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('投票标题不能为空');
        }
        $data['banner'] = htmlspecialchars($data['banner']);
        if (empty($data['banner'])) {
            $this->baoError('投票banner不能为空');
        }
        if (!isImage($data['banner'])) {
            $this->baoError('投票banner格式不正确');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家ID不能为空');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->baoError('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->baoError('结束时间格式不正确');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['is_select'] = (int) $data['is_select'];
        $data['is_pic'] = (int) $data['is_pic'];
        $data['num'] = (int) $data['num'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($vote_id = 0)
    {
        if ($vote_id = (int) $vote_id) {
            $obj = D('Vote');
            if (!($detail = $obj->find($vote_id))) {
                $this->baoError('请选择要编辑的投票管理');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['vote_id'] = $vote_id;
                $options = $this->_post('options', 'htmlspecialchars');
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('vote/index'));
                }
                $this->baoError('操作失败');
            } else {
                $shops = D('Shop')->find($detail['shop_id']);
                $this->assign('shops', $shops);
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的投票管理');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('投票标题不能为空');
        }
        $data['banner'] = htmlspecialchars($data['banner']);
        if (empty($data['banner'])) {
            $this->baoError('投票banner不能为空');
        }
        if (!isImage($data['banner'])) {
            $this->baoError('投票banner格式不正确');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家ID不能为空');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->baoError('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->baoError('结束时间格式不正确');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['is_select'] = (int) $data['is_select'];
        $data['is_pic'] = (int) $data['is_pic'];
        $data['num'] = (int) $data['num'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function delete($vote_id = 0)
    {
        if (is_numeric($vote_id) && ($vote_id = (int) $vote_id)) {
            $obj = D('Vote');
            $obj->delete($vote_id);
            $this->baoSuccess('删除成功！', U('vote/index'));
        } else {
            $vote_id = $this->_post('vote_id', false);
            if (is_array($vote_id)) {
                $obj = D('Vote');
                foreach ($vote_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('vote/index'));
            }
            $this->baoError('请选择要删除的投票');
        }
    }
    public function setting($vote_id)
    {
        if (!($detail = D('Vote')->find($vote_id))) {
            $this->error('请选择正确的投票');
        }
        $obj = D('Voteoption');
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $obj->delete(array("where" => array('vote_id' => $vote_id)));
            foreach ($data as $val) {
                if (!empty($val['title'])) {
                    $obj->add(array('vote_id' => $vote_id, 'title' => $val['title'], 'photo' => $val['photo'], 'orderby' => $val['orderby']));
                }
            }
            $this->baoSuccess('操作成功！', U('vote/index'));
        } else {
            $this->assign('detail', $detail);
            $this->assign('options', D('Voteoption')->order(array('orderby' => 'asc'))->where(array('vote_id' => $vote_id))->select());
            if ($detail['is_pic']) {
                $this->display('settingphoto');
            } else {
                $this->display();
            }
        }
    }
    public function result($vote_id)
    {
        if (!($detail = D('Vote')->find($vote_id))) {
            $this->error('请选择正确的投票');
        }
        $total = D('Voteoption')->where(array('vote_id' => $vote_id))->sum('number');
        $this->assign('total', $total);
        $this->assign('detail', $detail);
        $this->assign('options', D('Voteoption')->order(array('orderby' => 'asc'))->where(array('vote_id' => $vote_id))->select());
        $this->display();
    }
    public function work($vote_id = 0)
    {
        if (is_numeric($vote_id) && ($vote_id = (int) $vote_id)) {
            $obj = D('Vote');
            $detail = D('Voteoption')->where(array('vote_id' => $vote_id))->count();
            if (empty($detail)) {
                $this->baoError('您还未设置投票选项！');
            }
            $obj->save(array('vote_id' => $vote_id, 'is_work' => 1));
            $this->baoSuccess('启用成功！', U('vote/index'));
        } else {
            $vote_id = $this->_post('vote_id', false);
            if (is_array($vote_id)) {
                $obj = D('Vote');
                foreach ($vote_id as $id) {
                    $obj->save(array('vote_id' => $id, 'is_work' => 1));
                }
                $this->baoSuccess('启用成功！', U('vote/index'));
            }
            $this->baoError('请选择要启用的投票');
        }
    }
}
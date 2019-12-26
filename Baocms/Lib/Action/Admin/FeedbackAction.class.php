<?php
class FeedbackAction extends CommonAction
{
    private $create_fields = array('feed_id', 'user_id', 'community_id', 'title', 'details', 'reply', 'create_time', 'create_ip', 'reply_time', 'reply_ip');
    private $edit_fields = array('feed_id', 'user_id', 'community_id', 'title', 'details', 'reply', 'create_time', 'create_ip', 'reply_time', 'reply_ip');
    public function index()
    {
        $Post = D('Feedback');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($community_id = (int) $this->_param('community_id')) {
            $map['community_id'] = array('IN', D('Community')->getChildren($community_id));
            $this->assign('community_id', $community_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $Post->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Post->where($map)->order(array('feed_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = $communitys = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
                $communitys[$val['community_id']] = $val['community_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('communitys', D('Community')->itemsByIds($communitys));
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('sharecate', $list2);
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->display();
        // 输出模板
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Feedback');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('feedback/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('sharecate', D('Sharecate')->fetchAll());
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['orderby'] = (int) $data['orderby'];
        $data['is_fine'] = (int) $data['is_fine'];
        return $data;
    }
    public function edit($feed_id = 0)
    {
        if ($feed_id = (int) $feed_id) {
            $obj = D('Feedback');
            if (!($detail = $obj->find($feed_id))) {
                $this->baoError('请选择要编辑小区服务台1');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['feed_id'] = $feed_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('feedback/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                // $this->assign('sharecate', D('Sharecate')->fetchAll());
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('community', D('Community')->find($detail['community_id']));
                //查询小区名字
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的小区服务台2');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        ////////
        $data['details'] = htmlspecialchars($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        }
        //  $data['content'] = (int)$data['content'];
        return $data;
    }
    public function delete($feed_id = 0)
    {
        if (is_numeric($feed_id) && ($feed_id = (int) $feed_id)) {
            $obj = D('Feedback');
            $obj->delete($feed_id);
            $this->baoSuccess('删除成功！', U('feedback/index'));
        } else {
            $post_id = $this->_post('feed_id', false);
            if (is_array($feed_id)) {
                $obj = D('Feedback');
                foreach ($feed_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('feedback/index'));
            }
            $this->baoError('请选择要删除的小区服务台');
        }
    }
    public function audit($feed_id = 0)
    {
        if (is_numeric($feed_id) && ($feed_id = (int) $feed_id)) {
            $obj = D('Feedback');
            $detail = $obj->find($feed_id);
            $obj->save(array('feed_id' => $feed_id, 'audit' => 1));
            D('Users')->integral($detail['user_id'], 'share');
            // print_r($detail);die;
            $this->baoSuccess('审核成功！', U('feedback/index'));
        } else {
            $post_id = $this->_post('feed_id', false);
            if (is_array($feed_id)) {
                $obj = D('Feedback');
                foreach ($post_id as $id) {
                    $detail = $obj->find($id);
                    $obj->save(array('feed_id' => $id, 'audit' => 1));
                    D('Users')->integral($detail['user_id'], 'share');
                }
                $this->baoSuccess('审核成功！', U('feedback/index'));
            }
            $this->baoError('请选择要审核的小区服务台');
        }
    }
}
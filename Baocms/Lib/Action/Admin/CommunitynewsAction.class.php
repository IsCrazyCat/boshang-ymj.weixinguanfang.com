<?php
class CommunitynewsAction extends CommonAction
{
    private $create_fields = array('title', 'community_id', 'details', 'intro', 'views');
    private $edit_fields = array('title', 'community_id', 'details', 'intro', 'views');
    public function index()
    {
        $Communitynews = D('Communitynews');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Shopcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $Communitynews->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Communitynews->where($map)->order(array('news_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = $communitys = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
                $communitys[$val['community_id']] = $val['community_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        $this->assign('communitys', D('Community')->itemsByIds($communitys));
        $this->assign('users', D('Users')->itemsByIds($ids));
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
            $obj = D('Communitynews');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('communitynews/index'));
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
        $data['intro'] = SecurityEditorHtml($data['intro']);
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['views'] = (int) $data['views'];
        return $data;
    }
    public function edit($news_id = 0)
    {
        if ($news_id = (int) $news_id) {
            $obj = D('Communitynews');
            if (!($detail = $obj->find($news_id))) {
                $this->baoError('请选择要编辑的小区通知');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['news_id'] = $news_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('communitynews/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('community', D('Community')->find($detail['community_id']));
                //查询小区名字
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的小区通知');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['intro'] = SecurityEditorHtml($data['intro']);
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['views'] = (int) $data['views'];
        return $data;
    }
    public function delete($news_id = 0)
    {
        if (is_numeric($news_id) && ($news_id = (int) $news_id)) {
            $obj = D('Communitynews');
            $obj->save(array('news_id' => $news_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('communitynews/index'));
        } else {
            $news_id = $this->_post('news_id', false);
            if (is_array($news_id)) {
                $obj = D('Communitynews');
                foreach ($news_id as $id) {
                    $obj->save(array('news_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('communitynews/index'));
            }
            $this->baoError('请选择要删除的小区通知');
        }
    }
    public function audit($news_id = 0)
    {
        if (is_numeric($news_id) && ($news_id = (int) $news_id)) {
            $obj = D('Communitynews');
            $detail = $obj->find($news_id);
            $obj->save(array('news_id' => $news_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('communitynews/index'));
        } else {
            $news_id = $this->_post('news_id', false);
            if (is_array($news_id)) {
                $obj = D('Communitynews');
                foreach ($news_id as $id) {
                    $detail = $obj->find($id);
                    $obj->save(array('news_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('communitynews/index'));
            }
            $this->baoError('请选择要审核的小区通知');
        }
    }
}
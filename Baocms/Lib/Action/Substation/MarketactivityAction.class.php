<?php
class MarketactivityAction extends CommonAction
{
    private $create_fields = array('market_id', 'title', 'details', 'views');
    private $edit_fields = array('market_id', 'title', 'details', 'views');
    public function index()
    {
        $marketactivity = D('Marketactivity');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $marketactivity->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $marketactivity->order(array('id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $market_ids = array();
        foreach ($list as $k => $val) {
            $market_ids[$val['market_id']] = $val['market_id'];
        }
        $this->assign('market', D('Market')->itemsByIds($market_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
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
            $obj = D('Marketactivity');
            if ($id = $obj->add($data)) {
                $this->baoSuccess('添加成功', U('marketactivity/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('areas', D('Area')->fetchAll());
            $this->assign('business', D('Business')->fetchAll());
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['market_id'] = (int) $data['market_id'];
        if (empty($data['market_id'])) {
            $this->baoError('商场不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('活动标题不能为空');
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('活动内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('活动内容含有敏感词：' . $words);
        }
        $data['views'] = $data['views'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($id = 0)
    {
        if ($id = (int) $id) {
            $obj = D('Marketactivity');
            if (!($detail = $obj->find($id))) {
                $this->baoError('请选择要编辑的商场活动');
            }
            if ($this->isPost()) {
                $data = $this->editCheck($id);
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('marketactivity/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('areas', D('Area')->fetchAll());
                $this->assign('business', D('Business')->fetchAll());
                $this->assign('detail', $detail);
                $this->assign('market', D('Market')->find($detail['market_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商场活动');
        }
    }
    private function editCheck($id)
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['market_id'] = (int) $data['market_id'];
        if (empty($data['market_id'])) {
            $this->baoError('商场不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('活动标题不能为空');
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('活动内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('活动内容含有敏感词：' . $words);
        }
        $data['views'] = $data['views'];
        return $data;
    }
    public function delete($id = 0)
    {
        if (is_numeric($id) && ($id = (int) $id)) {
            $obj = D('Marketactivity');
            $obj->save(array('id' => $id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('marketactivity/index'));
        } else {
            $id = $this->_post('id', false);
            if (is_array($id)) {
                $obj = D('Marketactivity');
                foreach ($id as $mid) {
                    $obj->save(array('id' => $mid, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('marketactivity/index'));
            }
            $this->baoError('请选择要删除的商场活动');
        }
    }
}
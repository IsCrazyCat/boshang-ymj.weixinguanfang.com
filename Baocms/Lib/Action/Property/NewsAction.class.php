<?php
class NewsAction extends CommonAction
{
    public function index()
    {
        $news = D('Communitynews');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'community_id' => $this->community_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title|intro'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $news->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $news->order(array('news_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->checkCreate();
            $obj = D('Communitynews');
            if ($obj->add($data)) {
                $this->baoSuccess('物业通知发布成功', U('news/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    public function checkCreate()
    {
        $data = $this->checkFields($this->_post('data', false), array('title', 'intro', 'details'));
        $data['community_id'] = $this->community_id;
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['title'])) {
            $this->baoError('物业通知标题含有敏感词：' . $words);
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('物业通知简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->baoError('物业通知简介含有敏感词：' . $words);
        }
        $data['details'] = htmlspecialchars($data['details']);
        if (empty($data['details'])) {
            $this->baoError('物业通知内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('物业通知内容含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['closed'] = 0;
        $data['audit'] = $this->_CONFIG['site']['xiaoqu_news_audit'];
        return $data;
    }
    public function edit($news_id)
    {
        $news_id = (int) $news_id;
        $obj = D('Communitynews');
        if (!($detail = $obj->find($news_id))) {
            $this->error('该通知不存在');
        }
        if ($detail['closed'] != 0) {
            $this->error('该通知已被删除');
        }
        if ($detail['community_id'] != $this->community_id) {
            $this->error('请不要操作别人的物业管理');
        }
        if ($this->isPost()) {
            $data = $this->editCheck($news_id);
            $data['news_id'] = $news_id;
            if (false !== $obj->save($data)) {
                $this->baoSuccess('操作成功', U('news/index'));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    public function editCheck($news_id)
    {
        $data = $this->checkFields($this->_post('data', false), array('title', 'intro', 'details'));
        $data['community_id'] = $this->community_id;
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->baoError('简介含有敏感词：' . $words);
        }
        $data['details'] = htmlspecialchars($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('详情含有敏感词：' . $words);
        }
        return $data;
    }
    public function delete($news_id = 0){
        if (is_numeric($news_id) && ($news_id = (int) $news_id)) {
            $obj = D('Communitynews');
            $obj->save(array('news_id' => $news_id, 'closed' => 1));
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功', U('news/index')));
        }
    }
}
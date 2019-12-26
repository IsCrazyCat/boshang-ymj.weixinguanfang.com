<?php
class FeedbackAction extends CommonAction
{
    public function index(){
        $feedback = D('Feedback');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'community_id' => $this->community_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $feedback->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $feedback->order(array('feed_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function reply($feed_id)
    {
        $feed_id = (int) $feed_id;
        $feedback = D('Feedback');
        if (!($detail = $feedback->find($feed_id))) {
            $this->baoError('该问题不存在');
        }
        if ($detail['closed'] != 0) {
            $this->baoError('该问题已被删除');
        }
        if ($detail['community_id'] != $this->community_id) {
            $this->baoError('请不要回复其他物业的反馈问题');
        }
        if ($this->isPost()) {
            $data = $this->replyCheck($feed_id);
            $data['feed_id'] = $feed_id;
            if (false !== $feedback->save($data)) {
                $this->baoSuccess('回复成功', U('feedback/index'));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    public function delete()
    {
        if (IS_AJAX) {
            $feed_id = (int) $_POST['feed_id'];
            $obj = D('Feedback');
            if (empty($feed_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '反馈问题不存在'));
            }
            if (!($detail = $obj->find($feed_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '反馈问题不存在'));
            }
            if ($detail['community_id'] != $this->community_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '不是您小区的'));
            }
            if (false !== $obj->save(array('feed_id' => $feed_id, 'closed' => 1))) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功'));
            }
        }
    }
    public function replyCheck($feed_id)
    {
        $data['community_id'] = (int) $this->community_id;
        $data['reply'] = htmlspecialchars($_POST['reply']);
        if (empty($data['reply'])) {
            $this->error('回复内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['reply'])) {
            $this->error('回复内容含有敏感词：' . $words);
        }
        $data['reply_time'] = NOW_TIME;
        $data['reply_ip'] = get_client_ip();
        return $data;
    }
}
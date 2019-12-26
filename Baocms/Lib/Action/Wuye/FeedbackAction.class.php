<?php
class FeedbackAction extends CommonAction
{
    public function index()
    {
        $feedback = D('Feedback');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0, 'community_id' => $this->community_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $feedback->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 10);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $feedback->order(array('feed_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function reply($feed_id)
    {
        $feed_id = (int) $feed_id;
        $feedback = D('Feedback');
        if (!($detail = $feedback->find($feed_id))) {
            $this->error('该问题不存在');
        }
        if ($detail['closed'] != 0) {
            $this->error('该问题已被删除');
        }
        if ($detail['community_id'] != $this->community_id) {
            $this->error('请不要回复其他物业的反馈问题');
        }
        if ($this->isPost()) {
            $data = $this->replyCheck($feed_id);
            $data['feed_id'] = $feed_id;
            if (false !== $feedback->save($data)) {
                $this->fengmiMsg('回复成功', U('feedback/index'));
            }
            $this->fengmiMsg('操作失败');
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
            $this->fengmiMsg('回复内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['reply'])) {
            $this->fengmiMsg('回复内容含有敏感词：' . $words);
        }
        $data['reply_time'] = NOW_TIME;
        $data['reply_ip'] = get_client_ip();
        return $data;
    }
    public function detail()
    {
        $feed_id = (int) $this->_param('feed_id');
        $news = D('Feedback');
        if (!($detail = $news->find($feed_id))) {
            $this->error('该通知不存在');
        }
        if ($detail['closed'] != 0) {
            $this->error('该通知已被删除');
        }
        if ($detail['community_id'] != $this->community_id) {
            $this->error('非法操作');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
}
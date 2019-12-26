<?php
class TuisongemailAction extends CommonAction{
    private $create_fields = array('tuisong_id', 'title', 'content', 'create_time', 'rank_id', 'user_id');
    private $edit_fields = array('tuisong_id', 'title', 'content', 'create_time', 'rank_id', 'user_id');
    public function _initialize(){
        parent::_initialize();
        $this->assign('ranks', D('Userrank')->fetchAll());
    }
    public function index(){
        $Tuisongemail = D('Tuisongemail');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Tuisongemail->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Tuisongemail->where($map)->order(array('tuisong_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Tuisongemail');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('Tuisongemail/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['user_id'] = (int) $data['user_id'];
        $data['rank_id'] = (int) $data['rank_id'];
        $data['create_time'] = NOW_TIME;
        $data['content'] = SecurityEditorHtml($data['content']);
        if (empty($data['content'])) {
            $this->baoError('详细内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['content'])) {
            $this->baoError('详细内容含有敏感词：' . $words);
        }
        return $data;
    }
    public function delete($tuisong_id = 0)
    {
        if (is_numeric($tuisong_id) && ($tuisong_id = (int) $tuisong_id)) {
            $obj = D('Tuisongemail');
            $obj->delete($tuisong_id);
            $this->baoSuccess('删除成功！', U('Tuisongemail/index'));
        } else {
            $tuisong_id = $this->_post('tuisong_id', false);
            if (is_array($tuisong_id)) {
                $obj = D('Tuisongemail');
                foreach ($tuisong_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('Tuisongemail/index'));
            }
            $this->baoError('请选择要删除的手机消息');
        }
    }
    public function tuisong($tuisong_id){
        $tuisong_id = (int) $this->_param('tuisong_id');
        $Tuisongemail = D('Tuisongemail');
        if (!($detail = $Tuisongemail->find($tuisong_id))) {
            $this->error('请选择要推送的消息');
        }
        $db = D("Users");
        $map['email'] = array('neq', '');
        if (!empty($detail['rank_id'])) {
            $map['rank_id'] = array('eq', $detail['rank_id']);//用户等级发送
        }
        if (!empty($detail['user_id'])) {
            $map['user_id'] = array('eq', $detail['user_id']);//后台选择单条推送就显示执行
        }
        $users = $db->where($map)->field('email')->select();
        //取得所有用户的邮箱 Email 字段对应你的 数据库邮箱的那
        foreach ($users as $k => $value) {
            D('Email')->sendMail('email_tuisongemail', $value['email'], $detail['title'], array(
				'title' => $detail['title'], 
				'content' => $detail['content']
			));
        }
        $Tuisongemail->where("{$tuisong_id}=%d", $detail['tuisong_id'])->save(array('is_tuisong' => 1, 'create_time' => NOW_TIME));//更新数据库
        $this->baoSuccess('推送邮件成功', U('tuisongemail/index'));
    }
}
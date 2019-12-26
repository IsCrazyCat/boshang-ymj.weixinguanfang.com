<?php
class TuisongweixinAction extends CommonAction{
    private $create_fields = array('tuisong_id', 'title','url', 'content', 'create_time', 'rank_id', 'user_id');
    private $edit_fields = array('tuisong_id', 'title', 'url','content', 'create_time', 'rank_id', 'user_id');
    public function _initialize() {
        parent::_initialize();
        $this->assign('ranks', D('Userrank')->fetchAll());
    }
    public function index(){
        $Tuisongweixin = D('Tuisongweixin');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Tuisongweixin->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Tuisongweixin->where($map)->order(array('tuisong_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Tuisongweixin');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('Tuisongweixin/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
		$data['url'] = htmlspecialchars($data['url']);
		if (empty($data['url'])) {
            $this->baoError('连接不能为空');
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
            $obj = D('Tuisongweixin');
            $obj->delete($tuisong_id);
            $this->baoSuccess('删除成功！', U('Tuisongweixin/index'));
        } else {
            $tuisong_id = $this->_post('tuisong_id', false);
            if (is_array($tuisong_id)) {
                $obj = D('Tuisongweixin');
                foreach ($tuisong_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('Tuisongweixin/index'));
            }
            $this->baoError('请选择要删除的手机消息');
        }
    }
    public function tuisong($tuisong_id){
        $tuisong_id = (int) $this->_param('tuisong_id');
        $Tuisongweixin = D('Tuisongweixin');
        if (!($detail = $Tuisongweixin->find($tuisong_id))) {
            $this->error('请选择要推送的消息');
        }
        $db = D('Users');

        if (!empty($detail['rank_id'])) {
            $map['rank_id'] = array('eq', $detail['rank_id']);
        }
        if (!empty($detail['user_id'])) {
            $map['user_id'] = array('eq', $detail['user_id']);
        }
		
        $users = $db->where($map)->select();
		$stringtime = date("Y-m-d H:i:s",time()); 
		
		 //====================微信通知===========================
							
           foreach ($users as $k => $value) { 
			    if(!empty($value['nickname'])){
				   $nickname = $value['nickname'];
			    }else{
				   $nickname = $value['account '];   
			    }
				
				include_once "Baocms/Lib/Net/Wxmesg.class.php";
				$_data_tuisongweixin = array(//整体变更
					'url'       =>  $detail['url'],
					'topcolor'  =>  '#F55555',
					'first'     =>  '您好！'.$nickname.'有新的通知！'.$stringtime.'',
					'remark'    =>  '更多信息,请登录'.$this->CONFIG['site']['sitename'].',将为您提供更多信息服务！',
					'nickname'  =>  $nickname,
					'title'     =>  $detail['title'],
	
				);
				$tuisongweixin_data = Wxmesg::tuisongweixin($_data_tuisongweixin);
				$return   = Wxmesg::net($value['user_id'], 'OPENTM201606061', $tuisongweixin_data);//结束
           } 
			

		$Tuisongweixin->where("{$tuisong_id}=%d", $detail['tuisong_id'])->save(array('is_tuisong' => 1, 'create_time' => NOW_TIME));		
        $this->baoSuccess('推送微信成功', U('Tuisongweixin/index'));
    }
}
<?php
class MessageAction extends CommonAction {
    protected  $sharecates = array();
    public function _initialize() {
        parent::_initialize();

    }

    public function index(){
		if (empty($this->uid)) {
			$this->error('您还没有登录！', U('passport/login'));
		}
		$Message = D('Message');
		$this->assign('nextpage', LinkTo('message/loaddata', array('t' => NOW_TIME, 'p' => '0000')));
        $this->display(); // 输出模板
    }
	
	public function loaddata(){
		if (empty($this->uid)) {
			$this->error('您还没有登录！', U('passport/login'));
		}
		$Message = D('Message');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array('user_id' => $this->uid ,'parent_id' => 0);
        $count = $Message->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Message->where($map)->order('msg_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['send_id']) {
                $ids[$val['send_id']] = $val['send_id'];
            }
        }
		$users = D('Users')->itemsByIds($ids);
        $this->assign('users',$users );
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
	

 
	
	//我发出的信件
	public function famessage(){
		if (empty($this->uid)) {
			$this->error('您还没有登录！', U('passport/login'));
		}
		$Message_fa = D('Message');
		$this->assign('nextpage', LinkTo('message/loadfa', array('t' => NOW_TIME, 'p' => '0000')));
        $this->display(); // 输出模板
    }
	
	   public function loadfa(){
		if (empty($this->uid)) {
			$this->error('您还没有登录！', U('passport/login'));
		}
		$Message_fa = D('Message');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array('send_id' => $this->uid ,'parent_id' => 0);
        $count = $Message_fa->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Message_fa->where($map)->order('msg_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['send_id']) {
                $ids[$val['send_id']] = $val['send_id'];
            }
        }
		$users = D('Users')->itemsByIds($ids);
        $this->assign('users',$users );
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
	
	
	
	public function detail($msg_id){
		if (empty($this->uid)) {
			$this->error('您还没有登录！', U('passport/login'));
		}
		$Message = D('Message');
		$detail = $Message->find($msg_id);
		if(empty($detail)){
			$this->error('没有该信息或该信息已被删除！', U('message/index'));
		}
		//如果收件人不等于当前ID就。可是同意是发件人怎么样处理呢？
		if($detail['user_id'] != $this->uid){
			$this->error('不能阅读他人的信息！', U('message/index'));
		}
		
		$Message->save(array('read_time'=>time(),'msg_id'=>$msg_id));
		$this->assign('user', D('Users')->find($detail['send_id'])); // 赋值数据集
		$this->assign('detail', $detail); // 赋值数据集
		$this->assign('nextpage', LinkTo('message/loadchat', array('msg_id'=>$msg_id,'t' => NOW_TIME, 'p' => '0000')));
		$this->display(); // 输出模板
	}
	
	public function xiangxi($msg_id){
		if (empty($this->uid)) {
			$this->error('您还没有登录！', U('passport/login'));
		}
		$Message = D('Message');
		$detail = $Message->find($msg_id);
		if(empty($detail)){
			$this->error('没有该信息或该信息已被删除！', U('message/famessage'));
		}
		//如果收件人不等于当前ID就。可是同意是发件人怎么样处理呢？
		if($detail['send_id'] != $this->uid){
			$this->error('不能阅读他人的信息！', U('message/famessage'));
		}
		
		$Message->save(array('read_time'=>time(),'msg_id'=>$msg_id));
		$this->assign('user', D('Users')->find($detail['send_id'])); // 赋值数据集
		$this->assign('detail', $detail); // 赋值数据集
		$this->assign('nextpage', LinkTo('message/loadxiangxi', array('msg_id'=>$msg_id,'t' => NOW_TIME, 'p' => '0000')));
		$this->display(); // 输出模板
	}
	
	
	
    public function loadchat(){
		$msg_id = $this->_param('msg_id');
		if (empty($this->uid)) {
			$this->error('您还没有登录！', U('passport/login'));
		}
		$Message = D('Message');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array('parent_id'=>$msg_id);
        $count = $Message->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Message->where($map)->order('msg_id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['send_id']) {
                $ids[$val['send_id']] = $val['send_id'];
            }
        }
		$user = $this->uid;
		$users = D('Users')->itemsByIds($ids);
        $this->assign('users',$users);
		$this->assign('user',$user);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
	
	 public function loadxiangxi(){
		$msg_id = $this->_param('msg_id');
		if (empty($this->uid)) {
			$this->error('您还没有登录！', U('passport/login'));
		}
		$Message = D('Message');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array('parent_id'=>$msg_id);
        $count = $Message->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Message->where($map)->order('msg_id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['send_id']) {
                $ids[$val['send_id']] = $val['send_id'];
            }
        }
		$user = $this->uid;
		$users = D('Users')->itemsByIds($ids);
        $this->assign('users',$users);
		$this->assign('user',$user);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
	
	public function post($msg_id){
		if (empty($this->uid)) {
			$this->error('您还没有登录！', U('passport/login'));
		}
		$Message = D('Message');
		$detail = $Message->find($msg_id);
		if(empty($detail)){
			$this->error('没有该信息或该信息已被删除！', U('message/index'));
		}
		
		/*if($detail['user_id'] != $this->uid){
			$this->error('不能查看他人的信息！', U('message/index'));
		}*/
		
		if ($this->isPost()) {
			$data['parent_id'] = $detail['msg_id'];
			$data['user_id'] = $detail['send_id'];
			$data['send_id'] = $this->uid;
			$data['content'] = $this->_param('content');
			$data['create_time'] = time();
			
			if($data['content']==''){
				$this->fengmiMsg('信息内容不能为空！');
				die;
			}
			$Message->add($data);
			$this->error('信息发送成功！', U('message/detail',array('msg_id'=>$detail['msg_id'])));
		}else{
			$this->assign('detail', $detail); // 赋值数据集
			$this->display(); // 输出模板
		}
	}
	
	public function delete($msg_id){
		if (empty($this->uid)) {
			$this->error('您还没有登录！', U('passport/login'));
		}
		$Message = D('Message');
		$detail = $Message->find($msg_id);
		if(empty($detail)){
			$this->error('没有该信息或该信息已被删除！', U('message/index'));
		}
		if($detail['user_id'] != $this->uid){
			$this->error('不能删除他人的信息！', U('message/index'));
		}
		$Message->where(array('parent_id'=>$msg_id))->delete();
		$Message->where(array('msg_id'=>$msg_id))->delete();
		header("location:".U('message/index'));
	}
	
	
	public function send_news($user_id){
		if (empty($this->uid)) {
			$this->fengmiMsg('您还没有登录！', U('passport/login'));
		}
		$user = D('Users')->find($user_id);

		if (empty($user)) {
			$this->error('接收对象没找到！');
		}
		$Message = D('Message');
		if ($this->isPost()) {
			$data['user_id'] = $user['user_id'];
			$data['send_id'] = $this->uid;
			$data['content'] = $this->_param('content');
			$data['create_time'] = time();
			
			if($data['content']==''){
				$this->error('信息内容不能为空！');
				die;
			}
			$last = $Message->add($data);
			if($last){
				$this->error('信息发送成功！', U('message/index'));
			}else{
				$this->error('信息发送失败！');
			}
		}else{
			$this->assign('user', $user); // 赋值数据集
			$this->display(); // 输出模板
		}
	}
	
	public function send($msg_id){
		
		if (empty($this->uid)) {
			$this->error('您还没有登录！', U('passport/login'));
		}
		$Message = D('Message');
		$detail = $Message->find($msg_id);
		
		if(empty($detail)){
			$this->error('没有该信息或该信息已被删除！', U('message/index'));
		}
		//p($msg_id); die;
		
		/*if($detail['user_id'] != $this->uid){
			$this->error('不能查看他人的信息！', U('message/index'));
		}*/
		
		if ($this->isPost()) {
			$data['parent_id'] = $detail['msg_id'];
			$data['user_id'] = $detail['user_id'];
			$data['send_id'] = $detail['send_id'];
			$data['content'] = $this->_param('content');
			$data['create_time'] = time();
			
			if($data['content']==''){
				$this->fengmiMsg('信息内容不能为空！');
				die;
			}
			$Message->add($data);
			$this->error('信息发送成功！', U('message/xiangxi',array('msg_id'=>$detail['msg_id'])));
		}else{
			$this->assign('detail', $detail); // 赋值数据集
			$this->display(); // 输出模板
		}

	}
	
	
	
	
}

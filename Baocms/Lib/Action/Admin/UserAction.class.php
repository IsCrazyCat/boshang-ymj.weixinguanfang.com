<?php
class UserAction extends CommonAction{
    private $create_fields = array('account', 'password', 'pay_password','rank_id', 'face', 'mobile', 'email', 'nickname', 'face', 'ext0');
    private $edit_fields = array('account', 'password','pay_password', 'rank_id', 'face', 'mobile', 'email', 'nickname', 'face', 'ext0');
   
    public function index(){
        $User = D('Users');
        import('ORG.Util.Page');
        $map = array('closed' => array('IN', '0,-1'));
        if ($account = $this->_param('account', 'htmlspecialchars')) {
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
        if ($nickname = $this->_param('nickname', 'htmlspecialchars')) {
            $map['nickname'] = array('LIKE', '%' . $nickname . '%');
            $this->assign('nickname', $nickname);
        }
        if ($mobile = $this->_param('mobile', 'htmlspecialchars')) {
            $map['mobile'] = array('LIKE', '%' . $mobile . '%');
            $this->assign('mobile', $mobile);
        }
        if ($rank_id = (int) $this->_param('rank_id')) {
            $map['rank_id'] = $rank_id;
            $this->assign('rank_id', $rank_id);
        }
        if ($ext0 = $this->_param('ext0', 'htmlspecialchars')) {
            $map['ext0'] = array('LIKE', '%' . $ext0 . '%');
            $this->assign('ext0', $ext0);
        }
        $count = $User->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $User->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$rank_ids = array();
        foreach ($list as $k => $val) {
			$rank_ids[$val['rank_id']] = $val['rank_id'];
            $val['reg_ip_area'] = $this->ipToArea($val['reg_ip']);
            $val['last_ip_area'] = $this->ipToArea($val['last_ip']);
            $val['is_shop'] = $User->get_is_shop($val['user_id']);
			$val['is_delivery'] = $User->get_is_delivery($val['user_id']);
			$list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('ranks', D('Userrank')->fetchAll());
		$this->assign('rank', D('Userrank')->itemsByIds($rank_ids));
        $this->display();
    }
    public function select(){
        $User = D('Users');
        import('ORG.Util.Page');
        $map = array('closed' => array('IN', '0,-1'));
        if ($account = $this->_param('account', 'htmlspecialchars')) {
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
        if ($nickname = $this->_param('nickname', 'htmlspecialchars')) {
            $map['nickname'] = array('LIKE', '%' . $nickname . '%');
            $this->assign('nickname', $nickname);
        }
        if ($ext0 = $this->_param('ext0', 'htmlspecialchars')) {
            $map['ext0'] = array('LIKE', '%' . $ext0 . '%');
            $this->assign('ext0', $ext0);
        }
        $count = $User->where($map)->count();
        $Page = new Page($count, 8);
        $pager = $Page->show();
        $list = $User->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $pager);
        $this->display();
    }
    public function selectapp(){
        $User = D('Users');
        import('ORG.Util.Page');
        $map = array('closed' => array('IN', '0,-1'));
        if ($account = $this->_param('account', 'htmlspecialchars')) {
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
        if ($nickname = $this->_param('nickname', 'htmlspecialchars')) {
            $map['nickname'] = array('LIKE', '%' . $nickname . '%');
            $this->assign('nickname', $nickname);
        }
        if ($ext0 = $this->_param('ext0', 'htmlspecialchars')) {
            $map['ext0'] = array('LIKE', '%' . $ext0 . '%');
            $this->assign('ext0', $ext0);
        }
        $join = ' inner join ' . C('DB_PREFIX') . 'app_user a on a.user_id = ' . C('DB_PREFIX') . 'users.user_id';
        $count = $User->where($map)->join($join)->count();
        $Page = new Page($count, 8);
        $pager = $Page->show();
        $list = $User->where($map)->join($join)->order(array(C('DB_PREFIX') . 'users.user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $pager);
        $this->display();
    }
    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Users');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('user/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('ranks', D('Userrank')->fetchAll());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['account'] = htmlspecialchars($data['account']);
        if (empty($data['account'])) {
            $this->baoError('账户不能为空');
        }
        if (D('Users')->getUserByAccount($data['account'])) {
            $this->baoError('该账户已经存在！');
        }
        $data['password'] = htmlspecialchars($data['password']);
        if (empty($data['password'])) {
            $this->baoError('密码不能为空');
        }
        $data['password'] = md5($data['password']);
		$data['pay_password'] = htmlspecialchars($data['pay_password']);
        if (empty($data['pay_password'])) {
            $this->baoError('支付密码不能为空');
        }
        $data['pay_password'] = md5(md5($data['pay_password']));
        $data['nickname'] = htmlspecialchars($data['nickname']);
        if (empty($data['nickname'])) {
            $this->baoError('昵称不能为空');
        }
        $data['rank_id'] = (int) $data['rank_id'];
        $data['email'] = htmlspecialchars($data['email']);
        $data['face'] = htmlspecialchars($data['face']);
        $data['ext0'] = htmlspecialchars($data['ext0']);
        $data['reg_ip'] = get_client_ip();
        $data['reg_time'] = NOW_TIME;
        return $data;
    }
    public function edit($user_id = 0){
        if ($user_id = (int) $user_id) {
            $obj = D('Users');
            if (!($detail = $obj->find($user_id))) {
                $this->baoError('请选择要编辑的会员');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['user_id'] = $user_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('user/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('ranks', D('Userrank')->fetchAll());
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的会员');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['account'] = htmlspecialchars($data['account']);
        if (empty($data['account'])) {
            $this->baoError('账户不能为空');
        }
        if ($data['password'] == '******') {
            unset($data['password']);
        } else {
            $data['password'] = htmlspecialchars($data['password']);
            if (empty($data['password'])) {
                $this->baoError('密码不能为空');
            }
            $data['password'] = md5($data['password']);
        }
		
		if ($data['pay_password'] == '******') {
            unset($data['pay_password']);
        } else {
            $data['pay_password'] = htmlspecialchars($data['pay_password']);
            if (empty($data['pay_password'])) {
                $this->baoError('支付密码不能为空');
            }
            $data['pay_password'] = md5(md5($data['pay_password']));
        }
		
        $data['nickname'] = htmlspecialchars($data['nickname']);
		if (empty($data['nickname'])) {
            $this->baoError('昵称不能为空');
        }
        $data['face'] = htmlspecialchars($data['face']);
        $data['email'] = htmlspecialchars($data['email']);
        $data['ext0'] = htmlspecialchars($data['ext0']);
        $data['rank_id'] = (int) $data['rank_id'];
        
        return $data;
    }
    public function delete($user_id = 0){
        if (is_numeric($user_id) && ($user_id = (int) $user_id)) {
            $obj = D('Users');
            $obj->delete($user_id);
            $this->baoSuccess('删除成功！', U('user/index'));
        } else {
            $user_id = $this->_post('user_id', false);
            if (is_array($user_id)) {
                $obj = D('Users');
                foreach ($user_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('user/index'));
            }
            $this->baoError('请选择要删除的会员');
        }
    }
    public function audit($user_id = 0){
        if (is_numeric($user_id) && ($user_id = (int) $user_id)) {
            $obj = D('Users');
            $obj->save(array('user_id' => $user_id, 'closed' => 0));
            $this->baoSuccess('审核成功！', U('user/index'));
        } else {
            $user_id = $this->_post('user_id', false);
            if (is_array($user_id)) {
                $obj = D('Users');
                foreach ($user_id as $id) {
                    $obj->save(array('user_id' => $id, 'closed' => 0));
                }
                $this->baoSuccess('审核成功！', U('user/index'));
            }
            $this->baoError('请选择要审核的会员');
        }
    }
    public function integral(){
        $user_id = (int) $this->_get('user_id');
        if (empty($user_id)) {
            $this->baoError('请选择用户');
        }
        if (!($detail = D('Users')->find($user_id))) {
            $this->baoError('没有该用户！');
        }
        if ($this->isPost()) {
            $integral = (int) $this->_post('integral');
            if ($integral == 0) {
                $this->baoError('请输入正确的积分数');
            }
            $intro = $this->_post('intro', 'htmlspecialchars');
			if (empty($intro)) {
                $this->baoError('积分说明不能为空');
            }
            if ($detail['integral'] + $integral < 0) {
                $this->baoError('积分余额不足！');
            }
            D('Users')->save(array('user_id' => $user_id, 'integral' => $detail['integral'] + $integral));
            D('Userintegrallogs')->add(array(
				'user_id' => $user_id, 
				'integral' => $integral, 
				'intro' => $intro, 
				'create_time' => NOW_TIME, 
				'create_ip' => get_client_ip()
			));
            $this->baoSuccess('操作成功', U('userintegrallogs/index'));
        } else {
            $this->assign('user_id', $user_id);
            $this->display();
        }
    }
    //设置商户冻结金 
 	public function frozen_gold(){
       $user_id = (int)$this->_get('user_id'); 
       if(!$detail = D('Users')->find($user_id)){
           $this->baoError('没有该用户！');
       }
       if($this->isPost()){
		   $gold = (int) ($this->_post('gold') * 100);
           if($gold == 0){
               $this->baoError('请输入正确商户冻结金');
           }
           $intro =  $this->_post('intro',  'htmlspecialchars');
		   if(empty($intro)){
               $this->baoError('商户冻结金说明不能为空');
           }
		   if (!D('Users')->set_frozen_gold($user_id,$gold,$intro)) {//入账
			  $this->baoError(D('Users')->getError(), 3000, true);	  
		   }
           $this->baoSuccess('操作成功',U('user/index'));
       }else{
           $this->assign('user_id',$user_id);
           $this->display();
       }       
   }
   
   //设置会员冻结金 
 	public function frozen_money(){
       $user_id = (int)$this->_get('user_id'); 
       if(!$detail = D('Users')->find($user_id)){
           $this->baoError('没有该用户！');
       }
       if($this->isPost()){
		   $money = (int)  ($this->_post('money') * 100);
           if($money == 0){
               $this->baoError('请输入正确的会员冻结金');
           }
           $intro =  $this->_post('intro', 'htmlspecialchars');
		   if(empty($intro)){
               $this->baoError('会员冻结金说明不能为空');
           }
		   if (!D('Users')->set_frozen_money($user_id,$money,$intro)) {//入账
			  $this->baoError(D('Users')->getError(), 3000, true);	  
		   }
           $this->baoSuccess('操作成功',U('user/index'));
       }else{
           $this->assign('user_id',$user_id);
           $this->display();
       }       
   }
   
   
    public function manage(){
        $user_id = (int) $this->_get('user_id');
        if (empty($user_id)) {
            $this->error('请选择用户');
        }
        if (!($detail = D('Users')->find($user_id))) {
            $this->error('没有该用户！');
        }
        setUid($user_id);
        header("Location:" . U('members/index/index'));
        die;
    }
    public function money(){
        $user_id = (int) $this->_get('user_id');
        if (empty($user_id)) {
            $this->baoError('请选择用户');
        }
        if (!($detail = D('Users')->find($user_id))) {
            $this->baoError('没有该用户！');
        }
        if ($this->isPost()) {
            $money = (int) ($this->_post('money') * 100);
            if ($money == 0) {
                $this->baoError('请输入正确的余额数');
            }
            $intro = $this->_post('intro', 'htmlspecialchars');
			if (empty($intro)) {
                $this->baoError('添加余额必须输入说明');
            }
            if ($detail['money'] + $money < 0) {
                $this->baoError('余额不足！');
            }
            D('Users')->save(array('user_id' => $user_id, 'money' => $detail['money'] + $money));
            D('Usermoneylogs')->add(array(
				'user_id' => $user_id, 
				'money' => $money, 
				'intro' => $intro, 
				'create_time' => NOW_TIME, 
				'create_ip' => get_client_ip()
			));
            $this->baoSuccess('操作成功', U('usermoneylogs/index'));
        } else {
            $this->assign('user_id', $user_id);
            $this->display();
        }
    }
}
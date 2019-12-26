<?php
class UserAction extends CommonAction
{
    private $create_fields = array('account', 'password', 'rank_id', 'face', 'mobile', 'nickname', 'face', 'ext0');
    private $edit_fields = array('account', 'password', 'rank_id', 'face', 'mobile', 'nickname', 'face', 'ext0');
    //推广金奖励
    public function fzmoney()
    {
        $EX = D('Usersex');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('frozen_money' => array('GT', 0));
        if ($is_no_frozen = (int) $this->_param('is_no_frozen')) {
            if ($is_no_frozen == 1) {
                $map['is_no_frozen'] = 1;
            } else {
                $map['is_no_frozen'] = 0;
            }
            $this->assign('is_no_frozen', $is_no_frozen);
        }
        if ($is_tui_money = (int) $this->_param('is_tui_money')) {
            if ($is_tui_money == 1) {
                $map['is_tui_money'] = 1;
            } else {
                $map['is_tui_money'] = 0;
            }
            $this->assign('is_tui_money', $is_tui_money);
        }
        $count = $EX->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $EX->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $invites_id = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $users = D('Users')->itemsByIds($user_ids);
        foreach ($users as $v) {
            if (!empty($v['invite1'])) {
                $invites_id[$v['invite1']] = $v['invite1'];
            }
            if (!empty($v['invite2'])) {
                $invites_id[$v['invite2']] = $v['invite2'];
            }
            if (!empty($v['invite3'])) {
                $invites_id[$v['invite3']] = $v['invite3'];
            }
            if (!empty($v['invite4'])) {
                $invites_id[$v['invite4']] = $v['invite4'];
            }
            if (!empty($v['invite5'])) {
                $invites_id[$v['invite5']] = $v['invite5'];
            }
            if (!empty($v['invite6'])) {
                $invites_id[$v['invite6']] = $v['invite6'];
            }
        }
        $inviteUsers = D('Users')->itemsByIds($invites_id);
        $inviteUsersex = $EX->itemsByIds($invites_id);
        $this->assign('inviteUsers', $inviteUsers);
        $this->assign('inviteUsersex', $inviteUsersex);
        $this->assign('users', $users);
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    //结算
    public function fzmoneyyes()
    {
        $user_id = (int) $this->_param('user_id');
        if (!($detail = D('Usersex')->find($user_id))) {
            $this->error('没有要发放奖励的记录');
        }
        if (empty($detail['frozen_money']) || $detail['is_tui_money'] == 1) {
            $this->error('没有要发放奖励的记录');
        }
        $user = D('Users')->find($user_id);
        if (empty($user)) {
            $this->error('没有要发放奖励的用户');
        }
        $userids = array();
        if (!empty($user['invite1'])) {
            $userids[] = $user['invite1'];
        }
        if (!empty($user['invite2'])) {
            $userids[] = $user['invite2'];
        }
        if (!empty($user['invite3'])) {
            $userids[] = $user['invite3'];
        }
        if (!empty($user['invite4'])) {
            $userids[] = $user['invite4'];
        }
        if (!empty($user['invite5'])) {
            $userids[] = $user['invite5'];
        }
        if (!empty($user['invite6'])) {
            $userids[] = $user['invite6'];
        }
        if (empty($userids)) {
            D('Usersex')->save(array('user_id' => $user_id, 'is_tui_money' => 1));
        } else {
            $ids = array();
            $userexs = D('Usersex')->itemsByIds($userids);
            foreach ($userexs as $k => $v) {
                if (!empty($v['frozen_money'])) {
                    $ids[$v['user_id']] = $v['user_id'];
                }
            }
            if (!empty($ids)) {
                if (D('Usersex')->save(array('user_id' => $user_id, 'is_tui_money' => 1))) {
                    if ($this->_CONFIG['quanming']['money6'] && $user['invite6'] && isset($ids[$user['invite6']])) {
                        D('Users')->addMoney($user['invite6'], $this->_CONFIG['quanming']['money6'] * 100, '推广员提成');
                    }
                    if ($this->_CONFIG['quanming']['money5'] && $user['invite5'] && isset($ids[$user['invite5']])) {
                        D('Users')->addMoney($user['invite5'], $this->_CONFIG['quanming']['money5'] * 100, '推广员提成');
                    }
                    if ($this->_CONFIG['quanming']['money4'] && $user['invite4'] && isset($ids[$user['invite4']])) {
                        D('Users')->addMoney($user['invite4'], $this->_CONFIG['quanming']['money4'] * 100, '推广员提成');
                    }
                    if ($this->_CONFIG['quanming']['money3'] && $user['invite3'] && isset($ids[$user['invite3']])) {
                        D('Users')->addMoney($user['invite3'], $this->_CONFIG['quanming']['money3'] * 100, '推广员提成');
                    }
                    if ($this->_CONFIG['quanming']['money2'] && $user['invite2'] && isset($ids[$user['invite2']])) {
                        D('Users')->addMoney($user['invite2'], $this->_CONFIG['quanming']['money2'] * 100, '推广员提成');
                    }
                    if ($this->_CONFIG['quanming']['money1'] && $user['invite1'] && isset($ids[$user['invite1']])) {
                        D('Users')->addMoney($user['invite1'], $this->_CONFIG['quanming']['money1'] * 100, '推广员提成');
                    }
                }
            } else {
                D('Usersex')->save(array('user_id' => $user_id, 'is_tui_money' => 1));
            }
        }
        $this->success('发放奖励成功', U('user/fzmoney'));
    }
    public function index()
    {
        $User = D('Users');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('closed' => array('IN', '0,-1'));
        if ($account = $this->_param('account', 'htmlspecialchars')) {
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
        if ($nickname = $this->_param('nickname', 'htmlspecialchars')) {
            $map['nickname'] = array('LIKE', '%' . $nickname . '%');
            $this->assign('nickname', $nickname);
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
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $User->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $val['reg_ip_area'] = $this->ipToArea($val['reg_ip']);
            $val['last_ip_area'] = $this->ipToArea($val['last_ip']);
            $list[$k] = $val;
        }
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('ranks', D('Userrank')->fetchAll());
        $this->display();
        // 输出模板
    }
    public function select()
    {
        $User = D('Users');
        import('ORG.Util.Page');
        // 导入分页类 
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
        // 查询满足要求的总记录数
        $Page = new Page($count, 8);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $pager = $Page->show();
        // 分页显示输出
        $list = $User->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $pager);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function selectapp()
    {
        $User = D('Users');
        import('ORG.Util.Page');
        // 导入分页类 
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
        // 查询满足要求的总记录数
        $Page = new Page($count, 8);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $pager = $Page->show();
        // 分页显示输出
        $list = $User->where($map)->join($join)->order(array(C('DB_PREFIX') . 'users.user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $pager);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function create()
    {
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
    private function createCheck()
    {
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
        $data['nickname'] = htmlspecialchars($data['nickname']);
        if (empty($data['nickname'])) {
            $this->baoError('昵称不能为空');
        }
        $data['rank_id'] = (int) $data['rank_id'];
        $data['face'] = htmlspecialchars($data['face']);
        $data['ext0'] = htmlspecialchars($data['ext0']);
        $data['reg_ip'] = get_client_ip();
        $data['reg_time'] = NOW_TIME;
        return $data;
    }
    public function edit($user_id = 0)
    {
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
    private function editCheck()
    {
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
        $data['nickname'] = htmlspecialchars($data['nickname']);
        $data['face'] = htmlspecialchars($data['face']);
        $data['ext0'] = htmlspecialchars($data['ext0']);
        $data['rank_id'] = (int) $data['rank_id'];
        if (empty($data['nickname'])) {
            $this->baoError('昵称不能为空');
        }
        return $data;
    }
    public function delete($user_id = 0)
    {
        if (is_numeric($user_id) && ($user_id = (int) $user_id)) {
            $obj = D('Users');
            //$obj->save(array('user_id'=>$user_id,'closed'=>1));
            $obj->delete($user_id);
            $this->baoSuccess('删除成功！', U('user/index'));
        } else {
            $user_id = $this->_post('user_id', false);
            if (is_array($user_id)) {
                $obj = D('Users');
                foreach ($user_id as $id) {
                    //$obj->save(array('user_id'=>$id,'closed'=>1));
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('user/index'));
            }
            $this->baoError('请选择要删除的会员');
        }
    }
    public function audit($user_id = 0)
    {
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
    //积分操作
    public function integral()
    {
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
            if ($detail['integral'] + $integral < 0) {
                $this->baoError('积分余额不足！');
            }
            D('Users')->save(array('user_id' => $user_id, 'integral' => $detail['integral'] + $integral));
            D('Userintegrallogs')->add(array('user_id' => $user_id, 'integral' => $integral, 'intro' => $intro, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip()));
            $this->baoSuccess('操作成功', U('userintegrallogs/index'));
        } else {
            $this->assign('user_id', $user_id);
            $this->display();
        }
    }
    public function gold()
    {
        $user_id = (int) $this->_get('user_id');
        if (empty($user_id)) {
            $this->baoError('请选择用户');
        }
        if (!($detail = D('Users')->find($user_id))) {
            $this->baoError('没有该用户！');
        }
        if ($this->isPost()) {
            $gold = (int) $this->_post('gold');
            if ($gold == 0) {
                $this->baoError('请输入正确的金块数');
            }
            $intro = $this->_post('intro', 'htmlspecialchars');
            if ($detail['gold'] + $gold < 0) {
                $this->baoError('金块余额不足！');
            }
            D('Users')->save(array('user_id' => $user_id, 'gold' => $detail['gold'] + $gold));
            D('Usergoldlogs')->add(array('user_id' => $user_id, 'gold' => $gold, 'intro' => $intro, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip()));
            $this->baoSuccess('操作成功', U('usergoldlogs/index'));
        } else {
            $this->assign('user_id', $user_id);
            $this->display();
        }
    }
    public function manage()
    {
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
    public function money()
    {
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
            if ($detail['money'] + $money < 0) {
                $this->baoError('余额不足！');
            }
            D('Users')->save(array('user_id' => $user_id, 'money' => $detail['money'] + $money));
            D('Usermoneylogs')->add(array('user_id' => $user_id, 'money' => $money, 'intro' => $intro, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip()));
            $this->baoSuccess('操作成功', U('usermoneylogs/index'));
        } else {
            $this->assign('user_id', $user_id);
            $this->display();
        }
    }
}
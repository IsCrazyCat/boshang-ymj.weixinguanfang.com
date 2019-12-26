<?php
class AdminAction extends CommonAction{
    private $create_fields = array('username', 'password', 'role_id', 'mobile', 'city_id');
    private $edit_fields = array('password', 'role_id', 'mobile', 'city_id');
    public function index(){
        $Admin = D('Admin');
        import('ORG.Util.Page');
        $keyword = trim($this->_param('keyword', 'htmlspecialchars'));
        $map = array('closed' => 0);
        if ($keyword) {
            $map['username'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $Admin->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Admin->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $val['last_ip_area'] = $this->ipToArea($val['last_ip']);
            $list[$k] = $Admin->_format($val);
        }
        $this->assign('citys', D('City')->fetchAll());
        $Page->parameter .= 'keyword=' . urlencode($keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Admin');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('admin/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('roles', D('Role')->fetchAll());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['username'] = htmlspecialchars($data['username']);
        if (empty($data['username'])) {
            $this->baoError('用户名不能为空');
        }
        if (D('Admin')->getAdminByUsername($data['username'])) {
            $this->baoError('用户名已经存在');
        }
        $data['password'] = md5($data['password']);
        if (empty($data['password'])) {
            $this->baoError('密码不能为空');
        }
        $data['role_id'] = (int) $data['role_id'];
        if (empty($data['role_id'])) {
            $this->baoError('角色不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('手机不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->baoError('手机格式不正确');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($admin_id = 0){
        if ($admin_id = (int) $admin_id) {
            $obj = D('Admin');
            if (!($detail = $obj->find($admin_id))) {
                $this->baoError('请选择要编辑的管理员');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['admin_id'] = $admin_id;
                if ($obj->save($data)) {
                    $this->baoSuccess('操作成功', U('admin/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('roles', D('Role')->fetchAll());
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的管理员');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        if ($data['password'] === '******') {
            unset($data['password']);
        } else {
            $data['password'] = htmlspecialchars($data['password']);
            if (empty($data['password'])) {
                $this->baoError('密码不能为空');
            }
            $data['password'] = md5($data['password']);
        }
        if ($this->_admin['role_id'] != 1) {
            unset($data['role_id']);
        } else {
            $data['role_id'] = (int) $data['role_id'];
            if (empty($data['role_id'])) {
                $this->baoError('角色不能为空');
            }
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('手机不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->baoError('手机格式不正确');
        }
        return $data;
    }
    public function delete($admin_id = 0){
        if (is_numeric($admin_id) && ($admin_id = (int) $admin_id)) {
            $obj = D('Admin');
            $obj->save(array('admin_id' => $admin_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('admin/index'));
        } else {
            $admin_id = $this->_post('admin_id', false);
            if (is_array($admin_id)) {
                $obj = D('Admin');
                foreach ($admin_id as $id) {
                    $obj->save(array('admin_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('admin/index'));
            }
            $this->baoError('请选择要删除的管理员');
        }
    }
    public function is_username_lock($admin_id){
        $obj = D('Admin');
        if (!($detail = $obj->find($admin_id))) {
            $this->error('请选择要编辑的账户');
        }
        $data = array('is_username_lock' => 0, 'admin_id' => $admin_id);
        if ($detail['is_username_lock'] == 0) {
            $data['is_username_lock'] = 1;
        }
        $obj->save($data);
        $this->baoSuccess('操作成功', U('admin/index'));
    }
}


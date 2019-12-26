<?php
class RoleAction extends CommonAction
{
    private $create_fields = array('role_name');
    private $edit_fields = array('role_name');
    public function index()
    {
        $Role = D('Role');
        import('ORG.Util.Page');
        // 导入分页类
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $map = array();
        if ($keyword) {
            $map['role_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('keyword', $keyword);
        $count = $Role->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Role->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function auth($role_id = 0)
    {
        if (($role_id = (int) $role_id) && ($detail = D('role')->find($role_id))) {
            if ($this->isPost()) {
                $menu_ids = $this->_post('menu_id');
                $obj = D('RoleMaps');
                $obj->delete(array('where' => " role_id = '{$role_id}' "));
                foreach ($menu_ids as $val) {
                    if (!empty($val)) {
                        $data = array('role_id' => $role_id, 'menu_id' => (int) $val);
                        $obj->add($data);
                    }
                }
                $this->baoSuccess('授权成功！', U('role/auth', array('role_id' => $role_id)));
            } else {
                $this->assign('menus', D('Menu')->fetchAll());
                $this->assign('menuIds', D('RoleMaps')->getMenuIdsByRoleId($role_id));
                $this->assign('role_id', $role_id);
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('请选择正确的角色');
        }
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Role');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('role/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    public function edit($role_id = 0)
    {
        if ($role_id = (int) $role_id) {
            $obj = D('Role');
            $role = $obj->fetchAll();
            if (!isset($role[$role_id])) {
                $this->baoError('请选择要编辑的角色');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['role_id'] = $role_id;
                if ($obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('role/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $role[$role_id]);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的角色');
        }
    }
    public function delete($role_id = 0)
    {
        if ($role_id = (int) $role_id) {
            $obj = D('Role');
            $obj->delete($role_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('role/index'));
        }
        $this->baoError('请选择要删除的组');
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        if (empty($data['role_name'])) {
            $this->baoError('请输入角色名称');
        }
        $data['role_name'] = htmlspecialchars($data['role_name'], ENT_QUOTES, 'UTF-8');
        return $data;
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        if (empty($data['role_name'])) {
            $this->baoError('请输入角色名称');
        }
        $data['role_name'] = htmlspecialchars($data['role_name'], ENT_QUOTES, 'UTF-8');
        return $data;
    }
}
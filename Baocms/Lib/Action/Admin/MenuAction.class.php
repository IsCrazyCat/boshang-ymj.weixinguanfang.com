<?php
class MenuAction extends CommonAction
{
    private $create_fields = array('parent_id', 'menu_name');
    private $edit_fields = array('parent_id', 'menu_name');
    public function index()
    {
        $menu = D('Menu')->fetchAll();
        $this->assign('datas', $menu);
        $this->display();
    }
    public function create($parent_id = 0)
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Menu');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('menu/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $menu = D('Menu')->fetchAll();
            $this->assign('datas', $menu);
            $this->assign('parent_id', (int) $parent_id);
            $this->display();
        }
    }
    public function action($parent_id = 0)
    {
        if (!($parent_id = (int) $parent_id)) {
            $this->baoError('请选择正确的父级菜单');
        }
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $new = $this->_post('new', false);
            $obj = D('Menu');
            foreach ($data as $k => $val) {
                $local = array();
                $local['menu_id'] = (int) $k;
                $local['menu_name'] = htmlspecialchars($val['menu_name'], ENT_QUOTES, 'UTF-8');
                $local['orderby'] = (int) $val['orderby'];
                $local['menu_action'] = htmlspecialchars($val['menu_action'], ENT_QUOTES, 'UTF-8');
                $local['is_show'] = (int) $val['is_show'];
                if (!empty($local['menu_name']) && !empty($local['menu_id']) && !empty($val['menu_action'])) {
                    $obj->save($local);
                }
            }
            if (!empty($new)) {
                foreach ($new as $k => $val) {
                    $local = array();
                    $local['menu_name'] = htmlspecialchars($val['menu_name'], ENT_QUOTES, 'UTF-8');
                    $local['orderby'] = (int) $val['orderby'];
                    $local['menu_action'] = htmlspecialchars($val['menu_action'], ENT_QUOTES, 'UTF-8');
                    $local['is_show'] = (int) $val['is_show'];
                    $local['parent_id'] = $parent_id;
                    if (!empty($local['menu_name']) && !empty($val['menu_action'])) {
                        $obj->add($local);
                    }
                }
            }
            $obj->cleanCache();
            $this->baoSuccess('更新成功', U('menu/index'));
        } else {
            $menu = D('Menu')->fetchAll();
            $this->assign('datas', $menu);
            $this->assign('parent_id', $parent_id);
            $this->display();
        }
    }
    public function update()
    {
        $orderby = $this->_post('orderby', false);
        $obj = D('Menu');
        foreach ($orderby as $key => $val) {
            $data = array('menu_id' => (int) $key, 'orderby' => (int) $val);
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->baoSuccess('更新成功', U('menu/index'));
    }
    public function edit($menu_id = 0)
    {
        if ($menu_id = (int) $menu_id) {
            $obj = D('Menu');
            $menu = $obj->fetchAll();
            if (!isset($menu[$menu_id])) {
                $this->baoError('请选择要编辑的菜单');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['menu_id'] = $menu_id;
                if ($obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('menu/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $menu[$menu_id]);
                $this->assign('datas', $menu);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的菜单');
        }
    }
    public function delete($menu_id = 0)
    {
        if ($menu_id = (int) $menu_id) {
            $obj = D('Menu');
            $menu = $obj->fetchAll();
            foreach ($menu as $val) {
                if ($val['parent_id'] == $menu_id) {
                    $this->baoError('该菜单下还有其他子菜单');
                }
            }
            $obj->delete($menu_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('menu/index'));
        }
        $this->baoError('请选择要删除的菜单');
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['parent_id'] = (int) $data['parent_id'];
        if (empty($data['menu_name'])) {
            $this->baoError('请输入菜单名称');
        }
        $data['menu_name'] = htmlspecialchars($data['menu_name'], ENT_QUOTES, 'UTF-8');
        $data['is_show'] = 1;
        return $data;
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['parent_id'] = (int) $data['parent_id'];
        if (empty($data['menu_name'])) {
            $this->baoError('请输入菜单名称');
        }
        $data['menu_name'] = htmlspecialchars($data['menu_name'], ENT_QUOTES, 'UTF-8');
        $data['is_show'] = 1;
        return $data;
    }
}
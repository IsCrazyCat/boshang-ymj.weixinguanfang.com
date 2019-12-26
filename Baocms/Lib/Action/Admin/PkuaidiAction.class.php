<?php
class PkuaidiAction extends CommonAction
{
    private $create_fields = array('name', 'tel');
    private $edit_fields = array('name', 'tel');
    private $listcreate_fields = array('name', 'tel', 'shouzhong', 'xuzhong', 'province_id');
    private $listedit_fields = array('name', 'tel', 'shouzhong', 'xuzhong', 'province_id');
    public function index()
    {
        $Pkuaidi = D('Pkuaidi');
        import('ORG.Util.Page');
        $map = array('type' => pintuan);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $list = $Pkuaidi->order(array('id' => 'desc'))->where($map)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Pkuaidi');
            $data['id'] = $id;
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('pkuaidi/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('id', $id);
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['type'] = pintuan;
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('快递不能为空');
        }
        $data['tel'] = (int) $data['tel'];
        return $data;
    }
    public function edit($kuaidi_id = 0)
    {
        if ($kuaidi_id = (int) $kuaidi_id) {
            $obj = D('Pkuaidi');
            if (!($detail = $obj->find($kuaidi_id))) {
                $this->baoError('请选择要编辑的快递');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['id'] = $kuaidi_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('pkuaidi/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的快递');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['type'] = pintuan;
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('快递名不能为空');
        }
        $data['tel'] = (int) $data['tel'];
        return $data;
    }
    public function delete($kuaidi_id = 0)
    {
        if (is_numeric($kuaidi_id) && ($kuaidi_id = (int) $kuaidi_id)) {
            $obj = D('Pkuaidi');
            $obj->delete($kuaidi_id);
            $this->baoSuccess('删除成功！', U('pkuaidi/index'));
        } else {
            $kuaidi_id = $this->_post('kuaidi_id', false);
            if (is_array($kuaidi_id)) {
                $obj = D('Pkuaidi');
                foreach ($kuaidi_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('pkuaidi/index'));
            }
            $this->baoError('请选择要删除的快递');
        }
    }
    public function lists($kuaidi_id = 0)
    {
        if ($kuaidi_id = (int) $kuaidi_id) {
            $lists = D('Pyunfei');
            import('ORG.Util.Page');
            $map = array('type' => pintuan);
            if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
                $map['name'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }
            $map['kuaidi_id'] = $kuaidi_id;
            $list = $lists->order(array('id' => 'desc'))->where($map)->select();

            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->assign('kuaidi_id', $kuaidi_id);
            $this->display();
        } else {
            $this->baoError('请选择快递');
        }
    }
    public function listcreate($kuaidi_id = 0)
    {
        if ($this->isPost()) {
            $data = $this->listcreateCheck();
            $obj = D('Pyunfei');
            $kuaidi_id = (int) $kuaidi_id;
            $data['kuaidi_id'] = $kuaidi_id;
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('pkuaidi/lists', array('kuaidi_id' => $kuaidi_id)));
            }
            $this->baoError('操作失败！');
        } else {
            $provinceList = D('Paddlist')->where(array('level' => 1))->select();
            $this->assign('provinceList', $provinceList);
            $this->assign('kuaidi_id', $kuaidi_id);
            $this->display();
        }
    }
    private function listcreateCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->listcreate_fields);
        $data['type'] = pintuan;
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('名称不能为空');
        }
        $data['province_id'] = (int) $data['province_id'];
        if (empty($data['province_id'])) {
            $this->baoError('请选择区域');
        }
        $data['shouzhong'] = (int) ($data['shouzhong'] * 100);
        if (empty($data['shouzhong'])) {
            $this->baoError('首重价格不能为空');
        }
        $data['xuzhong'] = (int) ($data['xuzhong'] * 100);
        if (empty($data['xuzhong'])) {
            $this->baoError('续重价格不能为空');
        }
        return $data;
    }
    public function listedit($yunfei_id = 0)
    {
        if ($yunfei_id = (int) $yunfei_id) {
            $obj = D('Pyunfei');
            if (!($detail = $obj->find($yunfei_id))) {
                $this->baoError('请选择要编辑的运费设置');
            }
            if ($this->isPost()) {
                $data = $this->listeditCheck();
                $data['id'] = $yunfei_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('pkuaidi/lists', array('kuaidi_id' => $detail['kuaidi_id'])));
                }
                $this->baoError('操作失败');
            } else {
                $provinceList = D('Paddlist')->where(array('level' => 1))->select();
                $this->assign('provinceList', $provinceList);
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的运费设置');
        }
    }
    private function listeditCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->listedit_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('名称不能为空');
        }
        $data['province_id'] = (int) $data['province_id'];
        if (empty($data['province_id'])) {
            $this->baoError('请选择区域');
        }
        $data['shouzhong'] = (int) ($data['shouzhong'] * 100);
        if (empty($data['shouzhong'])) {
            $this->baoError('首重价格不能为空');
        }
        $data['xuzhong'] = (int) ($data['xuzhong'] * 100);
        if (empty($data['xuzhong'])) {
            $this->baoError('续重价格不能为空');
        }
        return $data;
    }
    public function listdelete($yunfei_id = 0)
    {
        if (is_numeric($yunfei_id) && ($yunfei_id = (int) $yunfei_id)) {
            $obj = D('Pyunfei');
            $obj->delete($yunfei_id);
            $this->baoSuccess('删除成功！', U('pkuaidi/lists', array('kuaidi_id' => $detail['kuaidi_id'])));
        } else {
            $yunfei_id = $this->_post('yunfei_id', false);
            if (is_array($yunfei_id)) {
                $obj = D('Pyunfei');
                foreach ($yunfei_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('pkuaidi/lists', array('kuaidi_id' => $detail['kuaidi_id'])));
            }
            $this->baoError('请选择要删除的运费选项');
        }
    }
    public function address()
    {
        $Paddress = D('Paddlist');
        import('ORG.Util.Page');
        // 导入分页类
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Paddress->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 20);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Paddress->order(array('id' => 'asc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
}
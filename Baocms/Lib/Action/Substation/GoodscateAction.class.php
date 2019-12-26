<?php
class GoodscateAction extends CommonAction
{
    private $create_fields = array('cate_name', 'rate', 'select1', 'select2', 'select3', 'select4', 'select5', 'orderby');
    private $edit_fields = array('cate_name', 'rate', 'select1', 'select2', 'select3', 'select4', 'select5', 'orderby');
    public function index()
    {
        $Goodscate = D('Goodscate');
        $list = $Goodscate->fetchAll();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function create($parent_id = 0)
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Goodscate');
            $data['parent_id'] = $parent_id;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('goodscate/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('parent_id', $parent_id);
            $this->display();
        }
    }
    public function child($parent_id = 0)
    {
        $datas = D('Goodscate')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str .= '<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '</option>' . "\n\r";
                        foreach ($datas as $var3) {
                            if ($var3['parent_id'] == $var2['cate_id']) {
                                $str .= '<option value="' . $var3['cate_id'] . '">&nbsp;&nbsp;--' . $var3['cate_name'] . '</option>' . "\n\r";
                            }
                        }
                    }
                }
            }
        }
        echo $str;
        die;
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类不能为空');
        }
        $data['select1'] = htmlspecialchars($data['select1']);
        $data['select2'] = htmlspecialchars($data['select2']);
        $data['select3'] = htmlspecialchars($data['select3']);
        $data['select4'] = htmlspecialchars($data['select4']);
        $data['select5'] = htmlspecialchars($data['select5']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function edit($cate_id = 0)
    {
        if ($cate_id = (int) $cate_id) {
            $obj = D('Goodscate');
            if (!($detail = $obj->find($cate_id))) {
                $this->baoError('请选择要编辑的商家分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('goodscate/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商家分类');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类不能为空');
        }
        $data['select1'] = htmlspecialchars($data['select1']);
        $data['select2'] = htmlspecialchars($data['select2']);
        $data['select3'] = htmlspecialchars($data['select3']);
        $data['select4'] = htmlspecialchars($data['select4']);
        $data['select5'] = htmlspecialchars($data['select5']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function createone($parent_id = 0)
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Goodscate');
            $data['parent_id'] = $parent_id;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('goodscate/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('parent_id', $parent_id);
            $this->display();
        }
    }
    private function createoneCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类不能为空');
        }
        $data['select1'] = htmlspecialchars($data['select1']);
        $data['select2'] = htmlspecialchars($data['select2']);
        $data['select3'] = htmlspecialchars($data['select3']);
        $data['select4'] = htmlspecialchars($data['select4']);
        $data['select5'] = htmlspecialchars($data['select5']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function editone($cate_id = 0)
    {
        if ($cate_id = (int) $cate_id) {
            $obj = D('Goodscate');
            if (!($detail = $obj->find($cate_id))) {
                $this->baoError('请选择要编辑的商家分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('goodscate/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商家分类');
        }
    }
    private function editoneCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类不能为空');
        }
        $data['select1'] = htmlspecialchars($data['select1']);
        $data['select2'] = htmlspecialchars($data['select2']);
        $data['select3'] = htmlspecialchars($data['select3']);
        $data['select4'] = htmlspecialchars($data['select4']);
        $data['select5'] = htmlspecialchars($data['select5']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function delete($cate_id = 0)
    {
        if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
            $obj = D('Goodscate');
            $obj->delete($cate_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('goodscate/index'));
        } else {
            $cate_id = $this->_post('cate_id', false);
            if (is_array($cate_id)) {
                $obj = D('Goodscate');
                foreach ($cate_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('goodscate/index'));
            }
            $this->baoError('请选择要删除的商家分类');
        }
    }
    public function delattr($attr_id)
    {
        if (empty($attr_id)) {
            $this->baoError('操作失败！');
        }
        if (!($detail = D('Goodscateattr')->find($attr_id))) {
            $this->baoError('操作失败');
        }
        D('Goodscateattr')->delete($attr_id);
        $this->baoSuccess('删除成功！', U('goodscate/setting', array('cate_id' => $detail['cate_id'])));
    }
    public function ajax($cate_id, $goods_id = 0)
    {
        if (!($cate_id = (int) $cate_id)) {
            $this->error('请选择正确的分类');
        }
        if (!($detail = D('Goodscate')->find($cate_id))) {
            $this->error('请选择正确的分类');
        }
        $this->assign('cate', $detail);
        $this->assign('attrs', D('Goodscateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
        if ($goods_id) {
            $this->assign('detail', D('Goods')->find($goods_id));
            $this->assign('maps', D('GoodsCateattr')->getAttrs($goods_id));
        }
        $this->display();
    }
    public function setting($cate_id)
    {
        if (!($cate_id = (int) $cate_id)) {
            $this->error('请选择正确的分类');
        }
        if (!($detail = D('Goodscate')->find($cate_id))) {
            $this->error('请选择正确的分类');
        }
        if ($this->isPost()) {
            $obj = D('Goodscateattr');
            $data = $this->_post('data', false);
            foreach ($data as $key => $val) {
                foreach ($val as $k => $v) {
                    if (!empty($v['attr_name'])) {
                        $obj->add(array('cate_id' => $cate_id, 'type' => htmlspecialchars($key), 'attr_name' => htmlspecialchars($v['attr_name']), 'orderby' => (int) $v['orderby']));
                    }
                }
            }
            $old = $this->_post('old', false);
            foreach ($old as $key => $val) {
                $obj->save(array('attr_id' => (int) $key, 'attr_name' => htmlspecialchars($val['attr_name']), 'orderby' => (int) $val['orderby']));
            }
            $this->baoSuccess('操作成功！', U('goodscate/setting', array('cate_id' => $cate_id)));
        } else {
            $this->assign('detail', $detail);
            $this->assign('attrs', D('Goodscateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
            $this->display();
        }
    }
    public function update()
    {
        $orderby = $this->_post('orderby', false);
        $obj = D('Goodscate');
        foreach ($orderby as $key => $val) {
            $data = array('cate_id' => (int) $key, 'orderby' => (int) $val);
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->baoSuccess('更新成功', U('goodscate/index'));
    }
}
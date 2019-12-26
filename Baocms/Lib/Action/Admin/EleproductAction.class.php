<?php
class EleproductAction extends CommonAction{
    private $create_fields = array('product_name', 'shop_id', 'cate_id', 'photo', 'price', 'is_new', 'is_hot', 'is_tuijian', 'sold_num', 'month_num', 'create_time', 'create_ip');
    private $edit_fields = array('product_name', 'shop_id', 'cate_id', 'photo', 'price', 'is_new', 'is_hot', 'is_tuijian', 'sold_num', 'month_num');
    public function index(){
        $Eleproduct = D('Eleproduct');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['product_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        $count = $Eleproduct->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Eleproduct->where($map)->order(array('product_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $cate_ids = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            if ($val['cate_id']) {
                $cate_ids[$val['cate_id']] = $val['cate_id'];
            }
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        if ($cate_ids) {
            $this->assign('cates', D('Elecate')->itemsByIds($cate_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Eleproduct');
            if ($obj->add($data)) {
                D('Elecate')->updateNum($data['cate_id']);
                $this->baoSuccess('添加成功', U('eleproduct/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['product_name'] = htmlspecialchars($data['product_name']);
        if (empty($data['product_name'])) {
            $this->baoError('菜名不能为空');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->baoError('价格不能为空');
        }
        $data['is_new'] = (int) $data['is_new'];
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_tuijian'] = (int) $data['is_tuijian'];
        $data['sold_num'] = (int) $data['sold_num'];
        $data['month_num'] = (int) $data['month_num'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($product_id = 0)
    {
        if ($product_id = (int) $product_id) {
            $obj = D('Eleproduct');
            if (!($detail = $obj->find($product_id))) {
                $this->baoError('请选择要编辑的菜单管理');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['product_id'] = $product_id;
                if (false !== $obj->save($data)) {
                    D('Elecate')->updateNum($data['cate_id']);
                    $this->baoSuccess('操作成功', U('eleproduct/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的菜单管理');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['product_name'] = htmlspecialchars($data['product_name']);
        if (empty($data['product_name'])) {
            $this->baoError('菜名不能为空');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->baoError('价格不能为空');
        }
        $data['is_new'] = (int) $data['is_new'];
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_tuijian'] = (int) $data['is_tuijian'];
        $data['sold_num'] = (int) $data['sold_num'];
        $data['month_num'] = (int) $data['month_num'];
        return $data;
    }
    public function delete($product_id = 0){
        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
            $obj = D('Eleproduct');
            $obj->delete($product_id);
            $this->baoSuccess('删除成功！', U('eleproduct/index'));
        } else {
            $product_id = $this->_post('product_id', false);
            if (is_array($product_id)) {
                $obj = D('Eleproduct');
                foreach ($product_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('eleproduct/index'));
            }
            $this->baoError('请选择要删除的菜单管理');
        }
    }
    public function audit($product_id = 0)
    {
        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
            $obj = D('EleProduct');
            $r = $obj->where('product_id =' . $product_id)->find();
            $obj->save(array('product_id' => $product_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('eleproduct/index'));
        } else {
            $product_id = $this->_post('product_id', false);
            if (is_array($product_id)) {
                $obj = D('EleProduct');
                foreach ($product_id as $id) {
                    $r = $obj->where('product_id =' . $id)->find();
                    $obj->save(array('product_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('eleproduct/index'));
            }
            $this->baoError('请选择要审核的商品');
        }
    }
}
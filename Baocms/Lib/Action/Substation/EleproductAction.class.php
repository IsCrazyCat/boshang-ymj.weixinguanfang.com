<?php
class EleproductAction extends CommonAction
{
    private $create_fields = array('product_name', 'shop_id', 'cate_id', 'photo', 'price', 'is_new', 'is_hot', 'is_tuijian', 'sold_num', 'month_num', 'create_time', 'create_ip');
    private $edit_fields = array('product_name', 'shop_id', 'cate_id', 'photo', 'price', 'is_new', 'is_hot', 'is_tuijian', 'sold_num', 'month_num');
    public function index()
    {
        $mapss = array('city_id' => $this->city_id);
        //查询城市ID为当前登录账户的ID
        $shop_city = D('Shop')->where($mapss)->order(array('shop_id' => 'desc'))->select();
        //查询所在城市的商家
        foreach ($shop_city as $val) {
            $cityids[$val['shop_id']] = $val['shop_id'];
            //对比shop_id
        }
        $maps['shop_id'] = array('in', $cityids);
        //取得当前商家ID，给下面的maps查询
        $Eleproduct = D('Eleproduct');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $maps['product_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $maps['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        $count = $Eleproduct->where($maps)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Eleproduct->where($maps)->order(array('product_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function create()
    {
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
            //查询上级ID编辑处代码开始
            $shop_ids = D('Eleproduct')->find($product_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('eleproduct/index'));
            }
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
    public function delete($product_id = 0)
    {
        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Eleproduct')->find($product_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('eleproduct/index'));
            }
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
            //查询上级ID编辑处代码开始
            $shop_ids = D('Eleproduct')->find($product_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('eleproduct/index'));
            }
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
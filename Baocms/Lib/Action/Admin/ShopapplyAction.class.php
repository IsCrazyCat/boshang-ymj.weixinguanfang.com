<?php
class ShopapplyAction extends CommonAction
{
    private $create_fields = array('cate_id', 'name', 'shop_name', 'contact', 'create_time', 'create_ip');
    public function index()
    {
        $Shopapply = D('Shopapply');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        if ($name = $this->_param('name', 'htmlspecialchars')) {
            $map['name'] = array('LIKE', '%' . $name . '%');
            $this->assign('name', $name);
        }
        if ($shop_name = $this->_param('shop_name', 'htmlspecialchars')) {
            $map['shop_name'] = array('LIKE', '%' . $shop_name . '%');
            $this->assign('shop_name', $shop_name);
        }
        if ($contact = $this->_param('contact', 'htmlspecialchars')) {
            $map['contact'] = array('LIKE', '%' . $contact . '%');
            $this->assign('contact', $contact);
        }
        $count = $Shopapply->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Shopapply->order(array('apply_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $key => $val) {
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $val = $Shopapply->_format($val);
            $list[$key] = $val;
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
            $obj = D('Shopapply');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('shopapply/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Shopcate')->fetchAll());
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('店主名称不能为空');
        }
        $data['shop_name'] = htmlspecialchars($data['shop_name']);
        if (empty($data['shop_name'])) {
            $this->baoError('店铺名称不能为空');
        }
        $data['contact'] = htmlspecialchars($data['contact']);
        if (empty($data['contact'])) {
            $this->baoError('联系方式不能为空');
        }
        if (!isPhone($data['contact']) && !isMobile($data['contact'])) {
            $this->baoError('联系方式格式不正确');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function audit($apply_id = 0)
    {
        if (is_numeric($apply_id) && ($apply_id = (int) $apply_id)) {
            $obj = D('Shopapply');
            $obj->save(array('apply_id' => $apply_id, 'audit' => 1));
            $this->baoSuccess('确认成功！', U('shopapply/index'));
        } else {
            $apply_id = $this->_post('apply_id', false);
            if (is_array($apply_id)) {
                $obj = D('Shopapply');
                foreach ($apply_id as $id) {
                    $obj->save(array('apply_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('确认成功！', U('shopapply/index'));
            }
            $this->baoError('请选择要确认的商家申请');
        }
    }
}
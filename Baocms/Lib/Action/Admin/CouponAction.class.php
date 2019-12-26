<?php
class CouponAction extends CommonAction
{
    private $create_fields = array('shop_id','title', 'photo', 'full_price', 'reduce_price', 'expire_date', 'num', 'limit_num', 'intro');
    private $edit_fields = array('shop_id','title', 'photo', 'full_price', 'reduce_price', 'expire_date', 'num', 'limit_num', 'intro');
    public function index(){
        $Coupon = D('Coupon');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $Coupon->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Coupon->where($map)->order(array('coupon_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Coupon');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('coupon/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $shop = D('Shop')->find($data['shop_id']);
        if (empty($shop)) {
            $this->baoError('请选择正确的商家');
        }
        $data['cate_id'] = $shop['cate_id'];
        $data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传优惠券图片');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('优惠券图片格式不正确');
        }
        $data['expire_date'] = htmlspecialchars($data['expire_date']);
        if (empty($data['expire_date'])) {
            $this->baoError('过期日期不能为空');
        }
        if (!isDate($data['expire_date'])) {
            $this->baoError('过期日期格式不正确');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('优惠券描述不能为空');
        }
		
        $data['full_price'] = (int) ($data['full_price'] * 100);
        if (empty($data['full_price'])) {
            $this->baoError('满多少钱不能为空');
        }
        $data['reduce_price'] = (int) ($data['reduce_price'] * 100);
        if (empty($data['reduce_price'])) {
            $this->baoError('减多少钱不能为空');
        }
        if ($data['reduce_price'] >= $data['full_price']) {
            $this->baoError('减多少钱不能大于减多少钱');
        }
        $data['num'] = (int) $data['num'];
        $data['limit_num'] = (int) $data['limit_num'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($coupon_id = 0)
    {
        if ($coupon_id = (int) $coupon_id) {
            $obj = D('Coupon');
            if (!($detail = $obj->find($coupon_id))) {
                $this->baoError('请选择要编辑的优惠券');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['coupon_id'] = $coupon_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('coupon/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的优惠券');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $shop = D('Shop')->find($data['shop_id']);
        if (empty($shop)) {
            $this->baoError('请选择正确的商家');
        }
        $data['cate_id'] = $shop['cate_id'];
        $data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传优惠券图片');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('优惠券图片格式不正确');
        }
        $data['expire_date'] = htmlspecialchars($data['expire_date']);
        if (empty($data['expire_date'])) {
            $this->baoError('过期日期不能为空');
        }
        if (!isDate($data['expire_date'])) {
            $this->baoError('过期日期格式不正确');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('优惠券描述不能为空');
        }
         $data['full_price'] = (int) ($data['full_price'] * 100);
        if (empty($data['full_price'])) {
            $this->baoError('满多少钱不能为空');
        }
        $data['reduce_price'] = (int) ($data['reduce_price'] * 100);
        if (empty($data['reduce_price'])) {
            $this->baoError('减多少钱不能为空');
        }
        if ($data['reduce_price'] >= $data['full_price']) {
            $this->baoError('减多少钱不能大于减多少钱');
        }
        $data['num'] = (int) $data['num'];
        $data['limit_num'] = (int) $data['limit_num'];
        return $data;
    }
    public function delete($coupon_id = 0)
    {
        if (is_numeric($coupon_id) && ($coupon_id = (int) $coupon_id)) {
            $obj = D('Coupon');
            $obj->save(array('coupon_id' => $coupon_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('coupon/index'));
        } else {
            $coupon_id = $this->_post('coupon_id', false);
            if (is_array($coupon_id)) {
                $obj = D('Coupon');
                foreach ($coupon_id as $id) {
                    $obj->save(array('coupon_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('coupon/index'));
            }
            $this->baoError('请选择要删除的优惠券');
        }
    }
    public function audit($coupon_id = 0)
    {
        if (is_numeric($coupon_id) && ($coupon_id = (int) $coupon_id)) {
            $obj = D('Coupon');
            $obj->save(array('coupon_id' => $coupon_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('coupon/index'));
        } else {
            $coupon_id = $this->_post('coupon_id', false);
            if (is_array($coupon_id)) {
                $obj = D('Coupon');
                foreach ($coupon_id as $id) {
                    $obj->save(array('coupon_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('coupon/index'));
            }
            $this->baoError('请选择要审核的优惠券');
        }
    }
}
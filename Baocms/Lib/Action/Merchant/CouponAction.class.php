<?php
class CouponAction extends CommonAction
{
    private $create_fields = array('title', 'photo', 'full_price', 'reduce_price', 'expire_date', 'num', 'limit_num', 'intro');
    private $edit_fields = array('title', 'photo', 'full_price', 'reduce_price','expire_date', 'num', 'limit_num', 'intro');
    public function index()
    {
        $Coupon = D('Coupon');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Coupon->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Coupon->where($map)->order(array('coupon_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function used()
    {
        if ($this->isPost()) {
            $code = $this->_post('code', false);
            $res = array();
            foreach ($code as $k => $val) {
                if (!empty($val)) {
                    $res[$k] = $val;
                }
            }
            if (empty($res)) {
                $this->baoMsg('请输入电子优惠券！');
            }
            $obj = D('Coupondownload');
            $return = array();
            $ip = get_client_ip();
            foreach ($code as $var) {
                if (!empty($var)) {
                    $data = $obj->find(array('where' => array('code' => $var)));
                    if (!empty($data) && $data['shop_id'] == $this->shop_id && $data['is_used'] == 0) {
                        $obj->save(array('download_id' => $data['download_id'], 'is_used' => 1, 'used_time' => NOW_TIME, 'used_ip' => $ip));
                        $return[$var] = $var;
                    }
                }
            }
            if (empty($return)) {
                $this->baoMsg('没有可消费的电子优惠券！');
            }
            if (NOW_TIME - $this->shop['ranking'] < 86400) {
                //更新排名
                D('Shop')->save(array('shop_id' => $this->shop_id, 'ranking' => NOW_TIME));
            }
            $message = "恭喜您，您成功消费的优惠券如下：" . join(',', $return);
            $this->baoOpen($message, true, "layui-layer-demo");
        } else {
            $this->display();
        }
    }
    public function download()
    {
      
        $Coupondownload = D('Coupondownload');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id);
        $count = $Coupondownload->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Coupondownload->where($map)->order(array('download_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $coupons = array();
        foreach ($list as $k => $val) {
            if ($val['coupon_id']) {
                $coupons[$val['coupon_id']] = $val['coupon_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        if ($coupons) {
            $this->assign('coupons', D('Coupon')->itemsByIds($coupons));
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
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['shop_id'] = $this->shop_id;
        $data['cate_id'] = $this->shop['cate_id'];
        $data['area_id'] = $this->shop['area_id'];
        $data['city_id'] = $this->shop['city_id'];
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
                $this->error('请选择要编辑的优惠券');
                die;
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('请选择要编辑的优惠券');
                die;
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
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的优惠券');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['shop_id'] = $this->shop_id;
        $data['cate_id'] = $this->shop['cate_id'];
        $data['area_id'] = $this->shop['area_id'];
        $data['city_id'] = $this->shop['city_id'];
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
}
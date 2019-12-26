<?php
class CoupondownloadAction extends CommonAction{
    public function index(){
        $Coupondownload = D('Coupondownload');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($is_used = (int) $this->_param('is_used')) {
            $map['is_used'] = $is_used === 1 ? 1 : 0;
            $this->assign('is_used', $is_used);
        }
        $count = $Coupondownload->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Coupondownload->where($map)->order(array('download_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $coupons = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            if ($val['coupon_id']) {
                $coupons[$val['coupon_id']] = $val['coupon_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $val['used_ip_area'] = $this->ipToArea($val['used_ip']);
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        if ($coupons) {
            $this->assign('coupons', D('Coupon')->itemsByIds($coupons));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}
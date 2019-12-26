<?php
class UserAction extends CommonAction
{
    public function myactivity()
    {
        $Activity = D('Activity');
        $Activitysign = D('Activitysign');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('user_id' => $this->uid);
        $count = $Activitysign->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Activitysign->where($map)->order(array('sign_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $activitys_ids = array();
        foreach ($list as $k => $val) {
            $activitys_ids[$val['activity_id']] = $val['activity_id'];
        }
        $this->assign('activity', $Activity->itemsByIds($activitys_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function usercard()
    {
        $Usercard = D('Usercard');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Usercard->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Usercard->where($map)->order(array('card_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $cards_ids = array();
        $shop_ids = $user_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $Usercard->_format($val);
            $cards_ids[$val['card_id']] = $val['card_id'];
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
                $user_ids[$val['user_id']] = $val['user_id'];
            }
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('shopdetails', D('Shopdetails')->itemsByIds($shop_ids));
        $this->display();
    }
}
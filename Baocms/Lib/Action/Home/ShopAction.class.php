<?php
class ShopAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        $this->shopcates = D('Shopcate')->fetchAll();
        $this->assign('shopcates', $this->shopcates);
        $this->assign('host', __HOST__);
    }

    public function index(){
        $Shop = D('Shop');
        import('ORG.Util.Page');
        $cates = D('Shopcate')->fetchAll();
        $linkArr = array();
        $map = array('closed' => 0, 'audit' => 1, 'city_id' => $this->city_id);
        $cat = (int) $this->_param('cat');
        $cate_id = (int) $this->_param('cate_id');
        if ($cat) {
            if (!empty($cate_id)) {
                $map['cate_id'] = $cate_id;
                $this->seodatas['cate_name'] = $cates[$cate_id]['cate_name'];
                $linkArr['cat'] = $cat;
                $linkArr['cate_id'] = $cate_id;
            } else {
                $catids = D('Shopcate')->getChildren($cat);
                if (!empty($catids)) {
                    $map['cate_id'] = array('IN', $catids);
                }
                $this->seodatas['cate_name'] = $cates[$cat]['cate_name'];
                $linkArr['cat'] = $cat;
            }
        }
        $this->assign('cat', $cat);
        $this->assign('cate_id', $cate_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name|tags'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $this->assign('searchindex', 0);
        $area = (int) $this->_param('area');
        if ($area) {
            $map['area_id'] = $area;
            $this->seodatas['area_name'] = $this->areas[$area]['area_name'];
            $linkArr['area'] = $area;
        }
        $this->assign('area_id', $area);
        $business = (int) $this->_param('business');
        if ($business) {
            $map['business_id'] = $business;
            $this->seodatas['business_name'] = $this->bizs[$business]['business_name'];
            $linkArr['business'] = $business;
        }
        $this->assign('business_id', $business);
        $areas = D('Area')->fetchAll();
        $this->assign('areas', $areas);
        $order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 't':
                $orderby = array('shop_id' => 'desc');
                break;
            case 'x':
                $orderby = array('score' => 'desc');
                break;
            case 'h':
                $orderby = array('view' => 'desc');
                break;
            default:
                $orderby = array('orderby' => 'asc');
                break;
        }
        if (empty($order)) {
            $order = 'd';
        }
        $this->assign('order', $order);
        $count = $Shop->where($map)->count();
        
        $Page = new Page($count, 10);
        
        $show = $Page->show();
        
        $list = $Shop->order($orderby)->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $tuan = D('Tuan');
        $coupon = D('Coupon');
        $dianping = D('Shopdianping');
        $huodong = D('Activity');
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $list[$k]['tuan'] = $tuan->order('tuan_id desc ')->find(array('where' => array('shop_id' => $val['shop_id'], 'city_id' => $this->city_id, 'audit' => 1, 'closed' => 0, 'end_date' => array('EGT', TODAY))));
            $list[$k]['coupon'] = $coupon->order('coupon_id desc ')->find(array('where' => array('shop_id' => $val['shop_id'], 'city_id' => $this->city_id, 'audit' => 1, 'closed' => 0, 'expire_date' => array('EGT', TODAY))));
            $list[$k]['huodong'] = $huodong->order('activity_id desc ')->find(array('where' => array('shop_id' => $val['shop_id'], 'city_id' => $this->city_id, 'audit' => 1, 'closed' => 0, 'bg_date' => array('ELT', TODAY), 'end_date' => array('EGT', TODAY))));
            $list[$k]['dianping'] = $dianping->order('show_date desc')->find(array('where' => array('shop_id' => $val['shop_id'], 'closed' => 0, 'show_date' => array('ELT', TODAY))));
            if (!($fav = D('Shopfavorites')->where(array('shop_id' => $val['shop_id']))->find())) {
                $list[$k]['favorites'] = 0;
            } else {
                $list[$k]['favorites'] = 1;
            }
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('details', D('Shopdetails')->itemsByIds($shop_ids));
        $this->assign('total_num', $count);
        $this->assign('areas', $areas);
        $this->assign('cates', $cates);
        $this->assign('list', $list);
        
        $this->assign('page', $show);
        
        $this->assign('linkArr', $linkArr);
        $this->display();
    }
    public function detail()
    {
        $shop_id = (int) $this->_get('shop_id');
        $act = $this->_get('act');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该商家');
            die;
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
            die;
        }
        if ($favo = D('Shopfavorites')->where(array('shop_id' => $shop_id))->find()) {
            $detail['favorites'] = 1;
        } else {
            $detail['favorites'] = 0;
        }
        $Shopdianping = D('Shopdianping');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY));
        $count = $Shopdianping->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Shopdianping->where($map)->order(array('dianping_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $dianping_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $dianping_ids[$val['dianping_id']] = $val['dianping_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($dianping_ids)) {
            $this->assign('pics', D('Shopdianpingpics')->where(array('dianping_id' => array('IN', $dianping_ids)))->select());
        }
		$this->assign('totalnum', $count);
        $maps = array('closed' => 0, 'shop_id' => $shop_id, 'audit' => 1);
        $branchs = D('Shopbranch')->where($maps)->order(array('orderby' => 'asc'))->select();
        $shop_arr = array('name' => '总店', 'score' => $detail['score'], 'score_num' => $detail['score_num'], 'lng' => $detail['lng'], 'lat' => $detail['lat'], 'telephone' => $detail['tel'], 'addr' => $detail['addr']);
        if (!empty($lists)) {
            array_unshift($lists, $shop_arr);
        } else {
            $lists[] = $shop_arr;
        }
        $counts = count($lists);
        if ($counts % 5 == 0) {
            $num = $counts / 5;
        } else {
            $num = (int) ($counts / 5) + 1;
        }
        $this->assign('count', $counts);
        $this->assign('totalnum', $num);
        $this->assign('branchs', $branchs);
        $this->assign('list', $list);
        
        $this->assign('page', $show);
        
        $this->assign('detail', $detail);
        $ex = D('Shopdetails')->find($shop_id);
        $this->assign('ex', $ex);
        $tuan = D('Tuan')->where(array('shop_id' => $shop_id, 'audit' => 1, 'city_id' => $this->city_id, 'closed' => 0, 'end_date' => array('EGT', TODAY)))->order(' tuan_id desc ')->limit(0, 6)->select();
        $this->assign('tuan', $tuan);
        $goods = D('Goods')->where(array('shop_id' => $shop_id, 'audit' => 1, 'city_id' => $this->city_id, 'closed' => 0, 'end_date' => array('EGT', TODAY)))->order('goods_id desc')->limit(0, 6)->select();
        $this->assign('goods', $goods);
        $coupon = D('Coupon')->order('coupon_id desc ')->where(array('shop_id' => $shop_id, 'audit' => 1, 'city_id' => $this->city_id, 'closed' => 0, 'expire_date' => array('EGT', TODAY)))->limit(0, 6)->select();
        $this->assign('coupon', $coupon);
        $huodong = D('Activity')->order('activity_id desc ')->where(array('shop_id' => $shop_id, 'city_id' => $this->city_id, 'audit' => 1, 'closed' => 0, 'end_date' => array('EGT', TODAY), 'bg_date' => array('ELT', TODAY)))->limit(0, 6)->select();
        $this->assign('huodong', $huodong);
        D('Shop')->updateCount($shop_id, 'view');
        if ($this->uid) {
            D('Userslook')->look($this->uid, $shop_id);
        }
        /** 修复评论用户等级不显示 */
        $userrank = D('user_rank')->select();
        $this->assign('userrank', $userrank);
        $favnum = D('Shopfavorites')->where(array('shop_id' => $shop_id))->count();
        $this->assign('favo', $favo);
        $this->assign('favnum', $favnum);
        ///以上是我修复发其他问题
        $this->assign('shoppic', D('Shoppic')->order('orderby asc')->limit(0, 8)->where(array('shop_id' => $shop_id))->select());
        $this->assign('cate', $this->shopcates[$detail['cate_id']]);
        $this->assign('host', __HOST__);
        $this->assign('height_num', 700);
        $this->assign('act', $act);
        $file = D('Weixin')->getCode($shop_id, 1);
        $this->assign('file', $file);

        $this->Shopcates = D('Shopcate')->fetchAll();
        $this->seodatas['cate_name'] = $this->Shopcates[$detail['cate_id']]['cate_name'];//分类
        $this->seodatas['cate_area'] = $this->areas[$detail['area_id']]['area_name'];//地区
        $this->seodatas['cate_business'] = $this->bizs[$detail['business_id']]['business_name'];//商圈
        $this->seodatas['shop_name'] = $detail['shop_name'];
        if (!empty($detail['mobile'])) {
            $this->seodatas['shop_tel'] = $detail['mobile'];
        } else {
            $this->seodatas['shop_tel'] = $detail['tel'];
        }
        if (!empty($ex['details'])) {
            $this->seodatas['details'] = bao_Msubstr($detail['details'], 0, 200, false);
        } else {
            $this->seodatas['details'] = $detail['shop_name'];
        }
        $this->assign('pic', $pic = D('Shoppic')->where(array('shop_id' => $shop_id))->order(array('pic_id' => 'desc'))->count());
        $this->display();
    }
    public function favorites(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->baoError('没有该商家');
        }
        if ($detail['closed']) {
            $this->baoError('该商家已经被删除');
        }
        if (D('Shopfavorites')->check($shop_id, $this->uid)) {
            $this->baoError('您已经关注过该商家了！');
        }
        $data = array('shop_id' => $shop_id, 'user_id' => $this->uid, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
        if (D('Shopfavorites')->add($data)) {
            D('Shop')->updateCount($shop_id, 'fans_num');
            $this->baoSuccess('恭喜您关注成功！');
        }
        $this->baoError('关注失败！');
    }
    public function cancel()
    {
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->baoError('没有该商家');
        }
        if ($detail['closed']) {
            $this->baoError('该商家已经被删除');
        }
        if (!($favo = D('Shopfavorites')->where(array('shop_id' => $shop_id, 'user_id' => $this->uid))->find())) {
            $this->baoError('您还未关注该商家！');
        }
        if (false !== D('Shopfavorites')->save(array('favorites_id' => $favo['favorites_id'], 'closed' => 1))) {
            $this->baoSuccess('恭喜您成功取消关注！');
        }
        $this->baoError('取消关注失败！');
    }
    public function apply(){
        if (empty($this->uid)) {
            header('Location:' . U('passport/login'));
            die;
        }
        if (D('Shop')->find(array('where' => array('user_id' => $this->uid)))) {
            $this->error('您已经拥有一家店铺了！', U('Merchant/index/index'));
        }
        if ($this->isPost()) {
            $yzm = $this->_post('yzm');
            if (strtolower($yzm) != strtolower(session('verify'))) {
                session('verify', null);
                $this->baoError('验证码不正确!', 2000, true);
            }
            $data = $this->createCheck();
            $obj = D('Shop');
            $details = $this->_post('details', 'htmlspecialchars');
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->baoError('商家介绍含有敏感词：' . $words, 2000, true);
            }
            $ex = array('details' => $details, 'near' => $data['near'], 'price' => $data['price'], 'business_time' => $data['business_time']);
            unset($data['near'], $data['price'], $data['business_time']);
            if ($shop_id = $obj->add($data)) {
                $wei_pic = D('Weixin')->getCode($shop_id, 1);
                $ex['wei_pic'] = $wei_pic;
                D('Shopdetails')->upDetails($shop_id, $ex);
                $this->baoSuccess('恭喜您申请成功！', U('shop/index'));
            }
            $this->baoError('申请失败！');
        } else {
            $areas = D('Area')->fetchAll();
            $this->assign('cates', D('Shopcate')->fetchAll());
            $this->assign('areas', $areas);
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), array('cate_id', 'tel', 'qq', 'logo', 'photo', 'shop_name', 'contact', 'details', 'business_time', 'city_id', 'area_id', 'business_id', 'addr', 'lng', 'lat', 'recognition','is_pei'));
        $data['shop_name'] = htmlspecialchars($data['shop_name']);
        if (empty($data['shop_name'])) {
            $this->baoError('店铺名称不能为空', 2000, true);
        }
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
            $this->baoError('店铺坐标需要设置', 2000, true);
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空', 2000, true);
        }
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空', 2000, true);
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空', 2000, true);
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->baoError('商圈不能为空', 2000, true);
        }
        $data['contact'] = htmlspecialchars($data['contact']);
        if (empty($data['contact'])) {
            $this->baoError('联系人不能为空', 2000, true);
        }
        $data['business_time'] = htmlspecialchars($data['business_time']);
        if (empty($data['business_time'])) {
            $this->baoError('营业时间不能为空', 2000, true);
        }
        if (!isImage($data['logo'])) {
            $this->baoError('请上传正确的LOGO', 2000, true);
        }
        if (!isImage($data['photo'])) {
            $this->baoError('请上传正确的店铺图片', 2000, true);
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('地址不能为空', 2000, true);
        }
        $data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->baoError('联系方式不能为空', 2000, true);
        }
        $data['qq'] = htmlspecialchars($data['qq']);
        $detail = D('Shop')->where(array('user_id' => $this->uid))->find();
        if (!empty($detail)) {
            $this->baoError('您已经是商家了', 2000, true);
        }
        $data['recognition'] = 1;
		$data['is_pei'] = 1;
        $data['user_id'] = $this->uid;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function tui(){
        if (empty($this->uid)) {
            header('Location:' . U('passport/login'));
        }
        if ($this->isPost()) {
            $yzm = $this->_post('yzm');
            if (strtolower($yzm) != strtolower(session('verify'))) {
                session('verify', null);
                $this->baoError('验证码不正确!', 2000, true);
            }
            $account['account'] = htmlspecialchars($this->_post('account'));
            if (!isMobile($account['account']) && !isEmail($account['account'])) {
                session('verify', null);
                $this->baoError('用户名只允许手机号码或者邮件!', 2000, true);
            }
            $account['password'] = trim(htmlspecialchars($this->_post('password')));
            //整合UC的时候需要
            if (empty($account['password']) || strlen($account['password']) < 6) {
                session('verify', null);
                $this->baoError('请输入正确的密码!密码长度必须要在6个字符以上', 2000, true);
            }
            $data = $this->tuiCheck();
            $account['nickname'] = $data['shop_name'];
            if (isEmail($account['account'])) {
                //如果邮件的@前面超过15就不好了
                $local = explode('@', $account['account']);
                $account['ext0'] = $local[0];
            } else {
                $account['ext0'] = $account['account'];
            }
            $account['reg_ip'] = get_client_ip();
            $account['reg_time'] = NOW_TIME;
            $obj = D('Shop');
            $details = $this->_post('details', 'SecurityEditorHtml');
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->baoError('商家介绍含有敏感词：' . $words, 2000, true);
            }
            $ex = array('details' => $details, 'near' => $data['near'], 'price' => $data['price'], 'business_time' => $data['business_time']);
            unset($data['near'], $data['price'], $data['business_time']);
            if (!D('Passport')->register($account)) {
                $this->baoError('创建帐号失败！');
            }
            $token = D('Passport')->getToken();
            $data['user_id'] = $token['uid'];
            if ($shop_id = $obj->add($data)) {
                D('Shopdetails')->upDetails($shop_id, $ex);
                $this->baoSuccess('恭喜您申请成功！', U('shop/index'));
            }
            $this->baoError('申请失败！');
        } else {
            $areas = D('Area')->fetchAll();
            $this->assign('cates', D('Shopcate')->fetchAll());
            $this->assign('areas', $areas);
            $this->display();
        }
    }
    private function tuiCheck(){
        $data = $this->checkFields($this->_post('data', false), array('cate_id', 'tel', 'logo', 'photo', 'shop_name', 'contact', 'details', 'business_time', 'area_id', 'addr', 'lng', 'lat'));
        $data['shop_name'] = htmlspecialchars($data['shop_name']);
        if (empty($data['shop_name'])) {
            $this->baoError('店铺名称不能为空', 2000, true);
        }
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
            $this->baoError('店铺坐标需要设置', 2000, true);
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空', 2000, true);
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空', 2000, true);
        }
        $data['contact'] = htmlspecialchars($data['contact']);
        if (empty($data['contact'])) {
            $this->baoError('联系人不能为空', 2000, true);
        }
        $data['business_time'] = htmlspecialchars($data['business_time']);
        if (empty($data['business_time'])) {
            $this->baoError('营业时间不能为空', 2000, true);
        }
        if (!isImage($data['logo'])) {
            $this->baoError('请上传正确的LOGO', 2000, true);
        }
        if (!isImage($data['photo'])) {
            $this->baoError('请上传正确的店铺图片', 2000, true);
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('地址不能为空', 2000, true);
        }
        $data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->baoError('联系方式不能为空', 2000, true);
        }
        if (!isPhone($data['tel']) && !isMobile($data['tel'])) {
            $this->baoError('联系方式格式不正确', 2000, true);
        }
        $data['tui_uid'] = $this->uid;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function dianping(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->baoError('没有该商家');
        }
        if ($detail['closed']) {
            $this->baoError('该商家已经被删除');
        }
        if (D('Shopdianping')->check($shop_id, $this->uid)) {
            $this->baoError('不可重复评价一个商户');
        }
        $data = $this->checkFields($this->_post('data', false), array('score', 'd1', 'd2', 'd3', 'cost', 'contents'));
        $data['user_id'] = $this->uid;
        $data['shop_id'] = $shop_id;
        $data['score'] = (int) $data['score'];
        if (empty($data['score'])) {
            $this->baoError('评分不能为空');
        }
        if ($data['score'] > 5 || $data['score'] < 1) {
            $this->baoError('评分不能为空');
        }
        $cate = $this->shopcates[$detail['cate_id']];
        $data['d1'] = (int) $data['d1'];
        if (empty($data['d1'])) {
            $this->baoError($cate['d1'] . '评分不能为空');
        }
        if ($data['d1'] > 5 || $data['d1'] < 1) {
            $this->baoError($cate['d1'] . '评分不能为空');
        }
        $data['d2'] = (int) $data['d2'];
        if (empty($data['d2'])) {
            $this->baoError($cate['d2'] . '评分不能为空');
        }
        if ($data['d2'] > 5 || $data['d2'] < 1) {
            $this->baoError($cate['d2'] . '评分不能为空');
        }
        $data['d3'] = (int) $data['d3'];
        if (empty($data['d3'])) {
            $this->baoError($cate['d3'] . '评分不能为空');
        }
        if ($data['d3'] > 5 || $data['d3'] < 1) {
            $this->baoError($cate['d3'] . '评分不能为空');
        }
        $data['cost'] = (int) $data['cost'];
        $data['contents'] = htmlspecialchars($data['contents']);
        if (empty($data['contents'])) {
            $this->baoError('评价内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['contents'])) {
            $this->baoError('评价内容含有敏感词：' . $words);
        }
        $data['show_date'] = date('Y-m-d', NOW_TIME + ($this->_CONFIG['mobile']['data_shop_dianping'] * 86400));
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        if ($dianping_id = D('Shopdianping')->add($data)) {
            $photos = $this->_post('photos', false);
            $local = array();
            foreach ($photos as $val) {
                if (isImage($val)) {
                    $local[] = $val;
                }
            }
            if (!empty($local)) {
                D('Shopdianpingpics')->upload($dianping_id, $data['shop_id'], $local);
            }
            D('Users')->prestige($this->uid, 'dianping');
            D('Shop')->updateCount($shop_id, 'score_num');
            D('Users')->updateCount($this->uid, 'ping_num');
            D('Shopdianping')->updateScore($shop_id);
			D('Users')->prestige($this->uid, 'dianping_shop');
            $this->baoSuccess('恭喜您点评成功!', U('shop/detail', array('shop_id' => $shop_id)));
        }
        $this->baoError('点评失败！');
    }
    public function yuyue2()
    {
        $shop_id = (int) $this->_get('shop_id');
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '没有该商家'));
        }
        if ($detail['closed']) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商家已经被删除'));
        }
        if (IS_AJAX) {
            $data = $this->checkFields($this->_post('data', false), array('name', 'mobile', 'content', 'yuyue_date', 'yuyue_time', 'number'));
            $data['user_id'] = (int) $this->uid;
            $data['shop_id'] = (int) $shop_id;
            $data['name'] = htmlspecialchars($data['name']);
            if (empty($data['name'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '称呼不能为空'));
            }
            $data['content'] = htmlspecialchars($data['content']);
            if (empty($data['content'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '留言不能为空'));
            }
            $data['mobile'] = htmlspecialchars($data['mobile']);
            if (empty($data['mobile'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机不能为空'));
            }
            if (!isMobile($data['mobile'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机格式不正确'));
            }
            $data['yuyue_date'] = htmlspecialchars($data['yuyue_date']);
            $data['yuyue_time'] = htmlspecialchars($data['yuyue_time']);
            if (empty($data['yuyue_date']) || empty($data['yuyue_time'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '预定日期不能为空'));
            }
            if (!isDate($data['yuyue_date'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '预定日期格式错误'));
            }
            $data['number'] = (int) $data['number'];
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $obj = D('Shopyuyue');
            $data['code'] = $obj->getCode();
            if ($obj->add($data)) {
                //通知用户
                if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                    D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_shop_yuyue_code', $data['mobile'], array(
					'shop_name' => $detail['shop_name'], 
					'shop_tel' => $detail['mobile'], 
					'shop_addr' => $detail['addr'], 
					'code' => $data['code']
					));
                } else {
                    D('Sms')->sendSms('sms_shop_yuyue', $data['mobile'], array(
					'shop_name' => $detail['shop_name'], 
					'shop_tel' => $detail['tel'], 
					'shop_addr' => $detail['addr'], 
					'code' => $data['code']
					));
                }
                //预约通知商家功能开始
                if (!empty($detail['mobile'])) {
                    if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                        D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_shop_yuyue_shop', $detail['mobile'], array(
							'name' => $data['name'], 
							'content' => $data['content'], 
							'yuyue_date' => $data['yuyue_date'], 
							'mobile' => $data['mobile'], 
							'number' => $data['number']
						));
                    } else {
                        D('Sms')->sendSms('sms_shangjia_yuyue', $detail['mobile'], array(
							'name' => $data['name'], 
							'content' => $data['content'], 
							'mobile' => $data['mobile'], 
							'number' => $data['number'], 
							'yuyue_date' => $data['yuyue_date']
						));
                    }
                }
                //预约通知商家功能结束
                D('Shop')->updateCount($shop_id, 'yuyue_total');
                $this->ajaxReturn(array('status' => 'success', 'msg' => '预约成功', 'url' => U('shop/detail', array('shop_id' => $shop_id))));
            }
            $this->ajaxReturn(array('status' => 'error', 'msg' => '预约失败'));
        }
    }
    public function recognition(){
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        if (IS_AJAX) {
            $shop_id = I('shop_id', 0, 'trim,intval');
            if (!($detail = D('Shop')->find($shop_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '没有该商家'));
            }
            if ($detail['closed']) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该商家已经被删除'));
            }
            if (D('Shop')->find(array('where' => array('user_id' => $this->uid)))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '您已经拥有一家店铺了'));
            }
            if (D('Shoprecognition')->where(array('user_id' => $this->uid))->find()) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '您已经认领过一家商铺了'));
            }
            $data['user_id'] = (int) $this->uid;
            $data['shop_id'] = (int) $shop_id;
            $data['name'] = htmlspecialchars($_POST['name']);
            if (empty($data['name'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '称呼不能为空'));
            }
            $data['mobile'] = htmlspecialchars($_POST['mobile']);
            if (empty($data['mobile'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机不能为空'));
            }
            if (!isMobile($data['mobile'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机格式不正确'));
            }
            $data['content'] = htmlspecialchars($_POST['content']);
            if (empty($data['content'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '留言不能为空'));
            }
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $obj = D('Shoprecognition');
            if ($obj->add($data)) {
                $mobile = $this->_CONFIG['site']['config_mobile'];
                //通知用户
                if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                    D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_shop_recognition_admin', $mobile, array(
						'shop_name' => $detail['shop_name'], 
						'name' => $data['name']
					));
                }
                $shop_name = $detail['shop_name'];
                //邮件通知网站管理员
                $pc_email_recognition = $this->_CONFIG['site']['config_email'];
                D('Email')->sendMail('pc_email_recognition', $pc_email_recognition, '你好，管理员：有客户认领商家了！', array(
					'shop_name' => $shop_name, 
					'name' => $data['name'], 
					'mobile' => $data['mobile'], 
					'content' => $data['content']
				));
                $this->ajaxReturn(array('status' => 'success', 'msg' => '认领成功！', U('shop/detail', array('shop_id' => $detail['shop_id']))));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '参数错误'));
            }
        }
    }
    public function ping(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该商家');
            die;
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
            die;
        }
        $Shopdianping = D('Shopdianping');
        import('ORG.Util.Page');// 导入分页类
        $map = array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY));
        $count = $Shopdianping->where($map)->count();
        
        $Page = new Page($count, 5);
        
        $show = $Page->show();
        
        $list = $Shopdianping->where($map)->order(array('dianping_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $dianping_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $dianping_ids[$val['dianping_id']] = $val['dianping_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($dianping_ids)) {
            $this->assign('pics', D('Shopdianpingpics')->where(array('dianping_id' => array('IN', $dianping_ids)))->select());
        }
        $this->assign('totalnum', $count);
        $this->assign('list', $list);
        
        $this->assign('page', $show);
        
        $this->assign('pic', $pic = D('Shoppic')->where(array('shop_id' => $shop_id))->order(array('pic_id' => 'desc'))->count());
        $this->assign('ex', $ex = D('Shopdetails')->find($shop_id));
        $this->assign('detail', $detail);
        $this->display();
    }
    //团
    public function tuan(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该商家');
            die;
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
            die;
        }
        $tuanload = D('Tuan');
        import('ORG.Util.Page');
        $map = array('closed' => 0,'audit' => 1,  'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY));
        $count = $tuanload->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $list = $tuanload->where($map)->order(array('tuan_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        
        $this->assign('pic', $pic = D('Shoppic')->where(array('shop_id' => $shop_id))->order(array('pic_id' => 'desc'))->count());
        $this->assign('ex', $ex = D('Shopdetails')->find($shop_id));
        $this->assign('detail', $detail);
        $this->display();
        
    }
    //优惠劵
    public function coupon(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该商家');
            die;
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
            die;
        }
        $couponload = D('Coupon');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY));

        $count = $couponload->where($map)->count();
        
        $Page = new Page($count, 5);
        
        $show = $Page->show();
        
        $list = $couponload->where($map)->order(array('coupon_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        
        $this->assign('pic', $pic = D('Shoppic')->where(array('shop_id' => $shop_id))->order(array('pic_id' => 'desc'))->count());
        $this->assign('ex', $ex = D('Shopdetails')->find($shop_id));
        $this->assign('detail', $detail);
        $this->display();
        
    }
    public function photo()
    {
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该商家');
            die;
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
            die;
        }
        $map = array('shop_id' => $shop_id);
        $list = D('Shoppic')->where($map)->order(array('pic_id' => 'desc'))->select();
        $this->assign('list', $list);
        $thumb = unserialize($detail['thumb']);
        $this->assign('thumb', $thumb);
        $this->assign('pic', $pic = D('Shoppic')->where(array('shop_id' => $shop_id))->order(array('pic_id' => 'desc'))->count());
        $this->assign('ex', $ex = D('Shopdetails')->find($shop_id));
        $this->assign('detail', $detail);
        $this->display();
    }
    public function about()
    {
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该商家');
            die;
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
            die;
        }
        $this->assign('pic', $pic = D('Shoppic')->where(array('shop_id' => $shop_id))->order(array('pic_id' => 'desc'))->count());
        $this->assign('ex', $ex = D('Shopdetails')->find($shop_id));
        $this->assign('detail', $detail);
        $this->display();
    }
    //分类信息
    public function life()
    {
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该商家');
            die;
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
            die;
        }
        $Life = D('Life');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('audit' => 1, 'city_id' => $this->city_id, 'user_id' => $detail['user_id']);
        $count = $Life->where($map)->count();
        
        $Page = new Page($count, 25);
        
        $show = $Page->show();
        
        $list = $Life->where($map)->order(array('top_date' => 'desc', 'last_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        
        $this->assign('page', $show);
        
        $this->assign('pic', $pic = D('Shoppic')->where(array('shop_id' => $shop_id))->order(array('pic_id' => 'desc'))->count());
        $this->assign('ex', $ex = D('Shopdetails')->find($shop_id));
        $this->assign('detail', $detail);
        $this->display();
    }
    //分类信息
    public function news(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该商家');
            die;
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
            die;
        }
        $article = D('Article');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('audit' => 1, 'city_id' => $this->city_id, 'shop_id' => $shop_id);
        $count = $article->where($map)->count();
        
        $Page = new Page($count, 10);
        
        $show = $Page->show();
        
        $list = $article->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        
        $this->assign('page', $show);
        
        $this->assign('pic', $pic = D('Shoppic')->where(array('shop_id' => $shop_id))->order(array('pic_id' => 'desc'))->count());
        $this->assign('ex', $ex = D('Shopdetails')->find($shop_id));
        $this->assign('detail', $detail);
        $this->display();
    }
    //商品
    public function goods(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该商家');
            die;
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
            die;
        }
        $Goods = D('Goods');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'audit' => 1, 'shop_id' => $shop_id);
        $count = $Goods->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $list = $Goods->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('pic', $pic = D('Shoppic')->where(array('shop_id' => $shop_id))->order(array('pic_id' => 'desc'))->count());
        $this->assign('ex', $ex = D('Shopdetails')->find($shop_id));
        $this->assign('detail', $detail);
        $this->display();
        
    }
}
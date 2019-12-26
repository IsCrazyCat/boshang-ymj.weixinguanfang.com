<?php
class TuanAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        $this->Tuancates = D('Tuancate')->fetchAll();
        $this->assign('tuancates', $this->Tuancates);
        $this->type = D('Keyword')->fetchAll();
        $this->assign('types', $this->type);
        $cat = (int) $this->_param('cat');
        $this->assign('cat', $cat);
        $this->assign('host', __HOST__);
    }
    public function main(){
        $this->display();
    }
    public function get_like(){
        if (IS_AJAX) {
            $cookie = unserialize($_COOKIE['iLike']);

            $like_where = array();
            $like_where['cate_id'] = array('in', $cookie);
            $likes = D('Tuan')->where($like_where)->order('rand()')->limit(5)->select();
            if ($likes) {
                $this->ajaxReturn(array('status' => 'success', 'likes' => $likes));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'message' => '换一换失败！'));
            }
        }
    }
    public function detail(){
        $tuan_id = (int) $this->_get('tuan_id');
        $tao_arr = D('Tuanmeal')->order(array('id' => 'asc'))->where(array('tuan_id' => $tuan_id))->select();
		
		//检测域名前缀封装函数
		$tuan_city_id = D('Tuan')->where(array('tuan_id' => $tuan_id))->Field('city_id')->select();
		$url = D('city')->check_city_domain($tuan_city_id['0']['city_id'],$this->_NOWHOST,$this->_BAO_DOMAIN);
		if(!empty($url)){
			 header("Location:".$url);
		}

        $this->assign('tuan_id', $tuan_id);
        $this->assign('tao_arr', $tao_arr);
        $id = (int) $this->_get('id');
        $this->assign('id', $id);
        if ($id) {
            $tuan_id = $id;
        }
        if (empty($tuan_id)) {
            $this->error('该套餐信息不存在！');
            die;
        }
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->error('该套餐信息不存在！');
            die;
        }
        if ($detail['closed']) {
            $this->error('该套餐信息不存在！');
            die;
        } else {
            $cate_id = (int) $detail['cate_id'];
            $cookie = unserialize($_COOKIE['iLike']);
            $cookie[] = $cate_id;
            $cookie = array_flip(array_flip($cookie));
            $cate_arr = serialize($cookie);
            cookie('iLike', $cate_arr);
            cookie('iLike', $cate_arr, 86400);
        }
        //查询我喜欢的内容
        $like_where = array();
        $like_where['cate_id'] = array('in', $cookie);
        $like = D('Tuan')->where($like_where)->order('rand()')->limit(5)->select();
        $this->assign('like', $like);
        $detail = D('Tuan')->_format($detail);
		$this->assign('return_column_value',$return_column_value = D('Tuancate')->return_column_value($detail['cate_id']));//获取元素封装
        $tuan_details = D('Tuandetails')->find($tuan_id);
        $detail['end_time'] = strtotime($detail['end_date']) - NOW_TIME + 86400;
        $thumb = unserialize($detail['thumb']);
        $this->assign('thumb', $thumb);
        $this->assign('tuandetail', $tuan_details);
        $this->assign('detail', $detail);
        $shop_id = $detail['shop_id'];
        $shop = D('Shop')->find($shop_id);
        $this->assign('shop', $shop);
        if (!($favo = D('Shopfavorites')->where(array('user_id' => $this->uid, 'shop_id' => $shop_id))->find())) {
            $shop['favo'] = 0;
        } else {
            $shop['favo'] = 1;
        }
        $this->assign('shop', $shop);
        $this->assign('ex', D('Shopdetails')->find($shop_id));
        $Tuandianping = D('Tuandianping');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'tuan_id' => $tuan_id, 'show_date' => array('ELT', TODAY));
        $count = $Tuandianping->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $list = $Tuandianping->where($map)->order(array('dianping_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $orders_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $orders_ids[$val['order_id']] = $val['order_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($orders_ids)) {
            $this->assign('pics', D('Tuandianpingpics')->where(array('order_id' => array('IN', $orders_ids)))->select());
        }
        $this->assign('counts', $count);
        $this->assign('list', $list);
        $this->assign('page', $show);

        $maps = array('closed' => 0, 'audit' => 1, 'shop_id' => $shop_id);
        $lists = D('Shopbranch')->where($maps)->order(array('orderby' => 'asc'))->select();
        $shop_arr = array('name' => '总店', 'score' => $shop['score'], 'score_num' => $shop['score_num'], 'lng' => $shop['lng'], 'lat' => $shop['lat'], 'telephone' => $shop['tel'], 'addr' => $shop['addr']);
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
        $this->assign('lists', $lists);
        $this->seodatas['cate_name'] = $this->Tuancates[$detail['cate_id']]['cate_name'];
        $this->seodatas['cate_area'] = $this->areas[$detail['area_id']]['area_name'];
        $this->seodatas['cate_business'] = $this->bizs[$detail['business_id']]['business_name'];
        $this->seodatas['shop_name'] = $shop['shop_name'];
        $this->seodatas['title'] = $detail['title'];
        $this->seodatas['intro'] = $detail['intro'];
        D('Tuan')->updateCount($tuan_id, 'views');
        $viewArr = cookie('views');
        $cooarr = array('tuan_id' => $detail['tuan_id'], 'title' => $detail['title'], 'price' => $detail['price'], 'tuan_price' => $detail['tuan_price'], 'photo' => $detail['photo']);
        if (!$viewArr) {
            cookie('views', serialize($cooarr[$detail['tuan_id']]));
        } else {
            $viewArr = unserialize($viewArr);
            if (count($viewArr) == 5) {
                $arr = array_pop($viewArr);
                unset($arr);
            }
            if (!isset($viewArr[$detail['tuan_id']])) {
                $viewArr[$detail['tuan_id']] = $cooarr;
                cookie('views', serialize($viewArr));
            }
        }
        $views = unserialize(cookie('views'));
        $views = array_reverse($views, TRUE);
        /** 修复评论用户等级不显示 */
        $userrank = D('user_rank')->select();
        $this->assign('userrank', $userrank);
        /**修复等级不显示 end**/
        $this->assign('views', $views);
        $this->assign('height_num', 760);
        $tuansids = $detail['cate_id'];
        $this->assign('tuansids', $tuansids);
        $this->display();
    }
    public function emptyviews()
    {
        cookie('views', null);
        $this->ajaxReturn(array('status' => 'success', 'msg' => '清空成功'));
    }
    public function index()
    {
        $Tuan = D('Tuan');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('audit' => 1, 'closed' => 0, 'city_id' => $this->city_id, 'end_date' => array('EGT', TODAY));
        $linkArr = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
            $linkArr['keywrod'] = $map['title'];
        }
        $cates = D('Tuancate')->fetchAll();
        $cat = (int) $this->_param('cat');
        $cate_id = (int) $this->_param('cate_id');
        if ($cat) {
            if (!empty($cate_id)) {
                $map['cate_id'] = $cate_id;
                $this->seodatas['cate_name'] = $cates[$cate_id]['cate_name'];
                $linkArr['cat'] = $cat;
                $linkArr['cate_id'] = $cate_id;
            } else {
                $catids = D('Tuancate')->getChildren($cat);
                if (!empty($catids)) {
                    $map['cate_id'] = array('IN', $catids);
                }
                $this->seodatas['cate_name'] = $cates[$cat]['cate_name'];
                $linkArr['cat'] = $cat;
            }
        }
        $this->assign('cat', $cat);
        $this->assign('cate_id', $cate_id);
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
        $price = (int) $this->_param('price');
        switch ($price) {
            case 1:
                $map['tuan_price'] = array('ELT', '5000');
                $this->assign('pricestr', '50元以下');
                $linkArr['price'] = $price;
                break;
            case 2:
                $map['tuan_price'] = array('between', '5001,10000');
                $this->assign('pricestr', '50-100元');
                $linkArr['price'] = $price;
                break;
            case 3:
                $map['tuan_price'] = array('between', '10001,20000');
                $this->assign('pricestr', '100-200元');
                $linkArr['price'] = $price;
                break;
            case 4:
                $map['tuan_price'] = array('between', '20001,30000');
                $this->assign('pricestr', '200-300元');
                $linkArr['price'] = $price;
                break;
            case 5:
                $map['tuan_price'] = array('EGT', '30001');
                $this->assign('pricestr', '300元以上');
                $linkArr['price'] = $price;
                break;
        }
        $this->assign('price', $price);
        $tao_num = (int) $this->_param('tao_num');
        switch ($tao_num) {
            case 1:
                $map['tao_num'] = 1;
                $this->assign('taostr', '单人餐');
                $linkArr['tao_num'] = $tao_num;
                break;
            case 2:
                $map['tao_num'] = 2;
                $this->assign('taostr', '双人餐');
                $linkArr['tao_num'] = $tao_num;
                break;
            case 3:
                $map['tao_num'] = array('IN', array(3, 4));
                $this->assign('taostr', '3-4人');
                $linkArr['tao_num'] = $tao_num;
                break;
            case 5:
                $map['tao_num'] = array('IN', array(5, 6));
                $this->assign('taostr', '5-6人');
                $linkArr['tao_num'] = $tao_num;
                break;
            case 7:
                $map['tao_num'] = array('IN', array(7, 8));
                $this->assign('taostr', '7-8人');
                $linkArr['tao_num'] = $tao_num;
                break;
            case 9:
                $map['tao_num'] = array('IN', array(9, 10));
                $this->assign('taostr', '9-10人');
                $linkArr['tao_num'] = $tao_num;
                break;
            case 11:
                $map['tao_num'] = array('GT', 10);
                $this->assign('taostr', '10人以上');
                $linkArr['tao_num'] = $tao_num;
                break;
        }
        $this->assign('tao_num', $tao_num);
        $order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 's':
                $orderby = array('sold_num' => 'asc');
                $linkArr['order'] = $order;
                break;
            case 'p':
                $orderby = array('tuan_price' => 'asc');
                $linkArr['order'] = $order;
                break;
            case 't':
                $orderby = array('create_time' => 'asc');
                $linkArr['order'] = $order;
                break;
            case 'v':
                $orderby = array('views' => 'asc');
                $linkArr['order'] = $order;
                break;
            default:
                $orderby = array('orderby' => 'asc', 'sold_num' => 'desc', 'tuan_id' => 'desc');
                break;
        }
        if ($new = (int) $this->_param('new')) {
            $linkArr['new'] = $new;
            $map['is_new'] = $new;
        }
        $this->assign('new', $new);
        if ($hot = (int) $this->_param('hot')) {
            $linkArr['hot'] = $hot;
            $map['is_hot'] = $hot;
        }
        $this->assign('hot', $hot);
        if ($tui = (int) $this->_param('tui')) {
            $linkArr['tui'] = $tui;
            $map['is_chose'] = $tui;
        }
        $this->assign('tui', $tui);
        if ($freebook = (int) $this->_param('freebook')) {
            $linkArr['freebook'] = $freebook;
            $map['freebook'] = $freebook;
        }
        $this->assign('freebook', $freebook);
        $this->assign('order', $order);
        $count = $Tuan->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Tuan->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $val['end_time'] = strtotime($val['end_date']) - NOW_TIME + 86400;
            $val = $Tuan->_format($val);
            $list[$k] = $val;
            if ($result = D('Tuanmeal')->where(array('id' => $val['tuan_id']))->find()) {
                $list[$k]['tao_arr'] = $result['tuan_id'];
            } else {
                $list[$k]['tao_arr'] = 0;
            }
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $selArr = $linkArr;
        foreach ($selArr as $k => $val) {
            if ($k == 'order' || $k == 'new' || $k == 'freebook' || $k == 'hot' || $k == 'tui') {
                unset($selArr[$k]);
            }
        }
        $views = unserialize(cookie('views'));
        $views = array_reverse($views, TRUE);
        $this->assign('views', $views);
        $this->assign('selArr', $selArr);
        $this->assign('cates', $cates);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('linkArr', $linkArr);
        $this->display();
    }
    public function around()
    {
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        $type = (int) $this->_param('type');
        if (empty($type)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请选择地址类型'));
        }
        $addr = $this->_param('addr', 'htmlspecialchars');
        if (empty($addr)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '地址不能为空'));
        }
        $lng = $this->_param('lng', 'htmlspecialchars');
        $lat = $this->_param('lat', 'htmlspecialchars');
        if (empty($lng) || empty($lat)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '地址坐标不能为空'));
        }
        $res = D('Around')->where(array('user_id' => $this->uid, 'type' => $type))->find();
        if ($res) {
            $data = array('around_id' => $res['around_id'], 'name' => $addr, 'lng' => $lng, 'lat' => $lat);
            if (false !== D('Around')->save($data)) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您保存地址成功！', 'url' => U('tuan/nearby', array('around_id' => $res['around_id']))));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '保存失败'));
            }
        } else {
            $data = array('user_id' => $this->uid, 'type' => $type, 'name' => $addr, 'lng' => $lng, 'lat' => $lat);
            if ($around_id = D('Around')->add($data)) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您保存地址成功！', 'url' => U('tuan/nearby', array('around_id' => $around_id))));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '添加失败'));
            }
        }
    }
    public function nearby()
    {
        $lat = cookie('lat');
        $lng = cookie('lng');
        $res = D('Around')->where(array('user_id' => $this->uid))->select();
        if (empty($res) && empty($lng) && empty($lat)) {
            header("Location:" . U('tuan/location'));
            die;
        }
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('audit' => 1, 'city_id' => $this->city_id, 'closed' => 0, 'bg_date' => array('ELT', TODAY), 'end_date' => array('EGT', TODAY));
        $linkArr = array();
        $around_id = (int) $this->_param('around_id');
        $this->assign('around_id', $around_id);
        if ($around_id) {
            $around = D('Around')->find($around_id);
        }
        if (!empty($around)) {
            $addr = $around['name'];
            $lng = $around['lng'];
            $lat = $around['lat'];
        } else {
            $specil = 1;
            $addr = cookie('addr');
        }
        $this->assign('specil', $specil);
        $this->assign('addr', $addr);
        $order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 's':
                $orderby = array('sold_num' => 'asc');
                break;
            case 'p':
                $orderby = array('tuan_price' => 'asc');
                break;
            case 't':
                $orderby = array('create_time' => 'asc');
                break;
            case 'd':
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
                break;
        }
        $linkArr['order'] = $order;
        $this->assign('order', $order);
        $lists = D('Tuan')->order($orderby)->where($map)->select();
        $shop_ids = array();
        foreach ($lists as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $s = getDistanceNone($lat, $lng, $val['lat'], $val['lng']);
            $s = $s / 1000;
            if ($s > 20) {
                $lists[$k]['d'] = "2公里以上";
                unset($lists[$k]);
            } elseif ($s >= 10 && $s < 20) {
                $lists[$k]['d'] = "1公里以上";
            } else {
                if ($s < 1) {
                    $lists[$k]['d'] = "<100米";
                } elseif ($s < 2 && $s >= 1) {
                    $lists[$k]['d'] = "约200米";
                } elseif ($s < 3 && $s >= 2) {
                    $lists[$k]['d'] = "约300米";
                } elseif ($s < 4 && $s >= 3) {
                    $lists[$k]['d'] = "约400米";
                } elseif ($s < 5 && $s >= 4) {
                    $lists[$k]['d'] = "约500米";
                } elseif ($s < 6 && $s >= 5) {
                    $lists[$k]['d'] = "约600米";
                } elseif ($s < 7 && $s >= 6) {
                    $lists[$k]['d'] = "约700米";
                } elseif ($s < 8 && $s >= 7) {
                    $lists[$k]['d'] = "约800米";
                } elseif ($s < 9 && $s >= 8) {
                    $lists[$k]['d'] = "约900米";
                } elseif ($s < 10 && $s >= 9) {
                    $lists[$k]['d'] = "约1公里";
                }
            }
        }
        if ($shop_ids) {
            $shops = D('Shop')->itemsByIds($shop_ids);
            $this->assign('shops', $shops);
        }
        $count = count($lists);
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('tuans', $tuans);
        $views = unserialize(cookie('views'));
        $views = array_reverse($views, TRUE);
        $this->assign('views', $views);
        $linkArr['lng'] = $lng;
        $linkArr['lat'] = $lat;
        $this->assign('linkArr', $linkArr);
        $aorunds = D('Around')->order('type asc')->where(array('user_id' => $this->uid))->select();
        $this->assign('arounds', $aorunds);
        $this->display();
    }
    public function location(){
        $this->display();
    }
    public function order(){
        if (!$this->uid) {
            $this->ajaxLogin();
        }
        if (!$this->member['mobile']) {
            echo "<script>parent.check_user_mobile_for_pc();</script>";
            die;
        }
        $tuan_id = (int) $this->_get('tuan_id');
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->baoError('该商品不存在');
        }
        if ($detail['closed'] == 1 || $detail['end_date'] < TODAY) {
            $this->baoError('该商品已经结束');
        }
		if (false == D('Shop')->check_shop_user_id($detail['shop_id'],$this->uid)) {//不能购买自己家的产品
			$this->baoError('您不能购买自己的产品');
		}
        $num = (int) $this->_post('num');
        if ($num <= 0 || $num > 99) {
            $this->baoError('请输入正确的购买数量');
        }
        //增加限购开始
        if ($num > $detail['xiangou'] && $detail['xiangou'] > 0) {
            $this->baoError('亲，每人只能购买' . $detail['xiangou'] . '份哦！');
        }
        if ($detail['xiadan'] == 1) {
            $where['user_id'] = $this->uid;
            $where['tuan_id'] = $tuan_id;
            $xdinfo = D('Tuanorder')->where($where)->order('order_id desc')->Field('order_id')->find();
            if ($xdinfo) {
                $this->baoError('该商品只允许购买一次!');
                die;
            }
        }
        if ($detail['xiangou'] > 0) {
            $y = date("Y");
            $m = date("m");
            $d = date("d");
            $day_start = mktime(0, 0, 0, $m, $d, $y);
            $day_end = mktime(23, 59, 59, $m, $d, $y);
            $where['user_id'] = $this->uid;
            $where['tuan_id'] = $tuan_id;
            $xdinfo = D('Tuanorder')->where($where)->order('order_id desc')->Field('create_time,num')->select();
            $order_num = 0;
            foreach ($xdinfo as $k => $val) {
                if ($val['create_time'] >= $day_start && $val['create_time'] <= $day_end) {
                    $order_num += $val['num'] + $num;
                    if ($order_num > $detail['xiangou']) {
                        $this->baoError('该商品每天每人限购' . $detail['xiangou'] . '份');
                        die;
                    }
                }
            }
        }

        if ($detail['num'] < $num) {
            $this->error('亲，购买数量不得超过' . $detail['num'] . '份');
        }
        $order = (int) $this->_param('order');
        if (!empty($order)) {
            //编辑订单
            $data = array(
				'order_id' => $order, 
				'num' => $num, 
				'total_price' => $detail['tuan_price'] * $num, 
				'need_pay' => $detail['tuan_price'] * $num - $detail['use_integral'] * $num / 100, 
				'update_time' => NOW_TIME, 
				'update_ip' => get_client_ip()
			);
            if (false !== D('Tuanorder')->save($data)) {
                $this->baoSuccess('修改订单成功！', U('tuan/pay', array('order_id' => $order)));
            }
            $this->baoError('修改订单失败！');
        } else {
            $data = array(
				'tuan_id' => $tuan_id, 
				'num' => $num, 
				'user_id' => $this->uid, 
				'shop_id' => $detail['shop_id'], 
				'create_time' => NOW_TIME, 
				'create_ip' => get_client_ip(), 
				'total_price' => $detail['tuan_price'] * $num, 
				'need_pay' => $detail['tuan_price'] * $num, 
				'status' => 0
			);
            if ($order_id = D('Tuanorder')->add($data)) {
                $where['tuan_id'] = $tuan_id;
                D("Tuan")->where($where)->setDec("num", $num);
                //更新减掉库存
                $this->baoSuccess('创建订单成功！', U('tuan/pay', array('order_id' => $order_id)));
            }
            $this->baoError('创建订单失败！');
        }
    }
    public function buy()
    {
        $tuan_id = (int) $this->_get('tuan_id');
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->error('该商品不存在');
            die;
        }
        if ($detail['closed'] == 1 || $detail['end_date'] < TODAY) {
            $this->error('该商品已经结束');
            die;
        }
        $num = (int) $this->_get('num');
        if (empty($num) || $num <= 0) {
            $num = 1;
        }
        if ($num > 99) {
            $num = 99;
        }
        if ($num > $detail['xiangou'] && $detail['xiangou'] > 0) {
            $this->error('亲，每人只能购买' . $detail['xiangou'] . '份哦！');
        }
        if ($detail['xiadan'] == 1) {
            $where['user_id'] = $this->uid;
            $where['tuan_id'] = $tuan_id;
            $xdinfo = D('Tuanorder')->where($where)->order('order_id desc')->Field('order_id')->find();
            if ($xdinfo) {
                $this->error('该商品只允许购买一次!');
                die;
            }
        }
        if ($detail['xiangou'] > 0) {
            $y = date("Y");
            $m = date("m");
            $d = date("d");
            $day_start = mktime(0, 0, 0, $m, $d, $y);
            $day_end = mktime(23, 59, 59, $m, $d, $y);
            $where['user_id'] = $this->uid;
            $where['tuan_id'] = $tuan_id;
            $xdinfo = D('Tuanorder')->where($where)->order('order_id desc')->Field('create_time,num')->select();
            $order_num = 0;
            foreach ($xdinfo as $k => $val) {
                if ($val['create_time'] >= $day_start && $val['create_time'] <= $day_end) {
                    $order_num += $val['num'] + $num;
                    if ($order_num > $detail['xiangou']) {
                        $this->error('该商品每天每人限购' . $detail['xiangou'] . '份');
                        die;
                    }
                }
            }
        }
        //限购结束
        if ($detail['num'] < $num) {
            $this->error('亲，购买数量不得超过' . $detail['num'] . '份');
        }
        $this->assign('num', $num);
        $order = (int) $this->_param('order');
        if (!empty($order)) {
            $this->assign('order', $order);
        }
        $detail = D('Tuan')->_format($detail);
        $this->assign('detail', $detail);
        $this->display();
    }
    public function pay2()
    {
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Tuanorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->baoError('该订单不存在');
        }
        if (!($code = $this->_post('code'))) {
            $this->baoError('请选择支付方式！');
        }
        if ($code == 'wait') {
            // 到店付 将不会有支付记录，并且不能使用在线的积分
            $codes = array();
            $obj = D('Tuancode');
            if (D('Tuanorder')->save(array('order_id' => $order_id, 'status' => '-1'))) {
                //更新成到店付的状态
                $tuan = D('Tuan')->find($order['tuan_id']);
                for ($i = 0; $i < $order['num']; $i++) {
                    $local = $obj->getCode();
                    $insert = array(
						'user_id' => $this->uid, 
						'shop_id' => $tuan['shop_id'], 
						'branch_id' => $tuan['branch_id'], 
						'order_id' => $order['order_id'], 
						'tuan_id' => $order['tuan_id'], 
						'code' => $local, 
						'price' => 0, 
						'real_money' => 0, 
						'real_integral' => 0, 
						'fail_date' => $tuan['fail_date'], 
						'settlement_price' => 0, 
						'create_time' => NOW_TIME, 
						'create_ip' => $ip
					);
                    $codes[] = $local;
                    $obj->add($insert);
                }
                D('Tuan')->updateCount($tuan['tuan_id'], 'sold_num');
				D('Sms')->sms_tuan_user($this->uid,$order['order_id']);//团购商品通知用户
                D('Users')->prestige($this->uid, 'tuan');//发送短信暂时不处理
                D('Sms')->tuanTZshop($tuan['shop_id']);
                D('Weixintmpl')->weixin_notice_tuan_user($order_id,$this->uid,0);
                $this->baoSuccess('恭喜您下单成功！', U('tuan/yes', array('code' => $codestr)));
            } else {
                $this->baoError('您已经设置过该套餐为到店付了！');
            }
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->baoError('该支付方式不存在');
            }
            $order['need_pay'] = D('Tuanorder')->get_tuan_need_pay($order_id,$this->uid,1);//获取实际支付价格封装	
            $logs = D('Paymentlogs')->getLogsByOrderId('tuan', $order_id);
            if (empty($logs)) {
                $logs = array(
					'type' => 'tuan', 
					'user_id' => $this->uid, 
					'order_id' => $order_id, 
					'code' => $code, 
					'need_pay' => $order['need_pay'], 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'is_paid' => 0
				);
                $logs['log_id'] = D('Paymentlogs')->add($logs);
            } else {
                $logs['need_pay'] = $order['need_pay'];
                $logs['code'] = $code;
                D('Paymentlogs')->save($logs);
            }
            D('Weixintmpl')->weixin_notice_tuan_user($order_id,$this->uid,1);
            $this->baoSuccess('选择支付方式成功！下面请进行支付！', U('payment/payment', array('log_id' => $logs['log_id'])));
        }
    }
    public function yes($code){
        $code = htmlspecialchars($code);
        $this->assign('waitSecond', 10);
        $this->success('恭喜您选择了到店支付，套餐码为:<font style="color:red;">' . $code . '</font>!到店消费时出示可以享受套餐价！', U('members/index'));
    }
    public function pay(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Tuanorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
        $tuan = D('Tuan')->find($order['tuan_id']);
        if (empty($tuan) || $tuan['closed'] == 1 || $tuan['end_date'] < TODAY) {
            $this->error('该套餐不存在');
            die;
        }
        $this->assign('use_integral', $tuan['use_integral'] * $order['num']);
        $this->assign('payment', D('Payment')->getPayments());
        $this->assign('tuan', $tuan);
        $this->assign('order', $order);
        $this->display();
    }
}
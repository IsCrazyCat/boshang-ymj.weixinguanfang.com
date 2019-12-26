<?php
class EleAction extends CommonAction{
    protected $cart = array();
    public function _initialize(){
        parent::_initialize();
        $eleproducts = $this->_getCartGoods();
        $total_money = '';
        $cart_num = '';
        $carts = array();
        foreach ($eleproducts as $k => $val) {
            $total_money += $val['total_price'];
            $cart_num += $val['cart_num'];
            $carts[] = $val['product_id'] . '_' . $val['cart_num'];
        }
        $this->assign('total_money', $total_money);
        $this->assign('cartnum', $cart_num);
        $this->assign('eleproducts', $eleproducts);
        $this->cart = join('|', $carts);
        $cate = D('Ele')->getEleCate();
        $this->assign('elecate', $cate);
    }
    private function _getCartGoods(){
        $carts = cookie('eleproducts');
        if (empty($carts)) {
            return null;
        }
        $carts = explode('|', $carts);
        $ids = $nums = array();
        foreach ($carts as $key => $val) {
            $local = explode('_', $val);
            $local[0] = (int) $local[0];
            $local[1] = (int) $local[1];
            if (!empty($local[0]) && !empty($local[1]) && $local[1] > 0) {
                $ids[$local[0]] = $local[0];
                $nums[$local[0]] = $local[1];
            }
        }
        $eleproducts = D('Eleproduct')->itemsByIds($ids);
        foreach ($eleproducts as $k => $val) {
            $eleproducts[$k]['cart_num'] = $nums[$val['product_id']];
            $eleproducts[$k]['total_price'] = $nums[$val['product_id']] * $val['price'];
        }
        $cookies = array();
        foreach ($nums as $k => $v) {
            $cookies[] = $k . '_' . $v;
        }
        $cookiestr = join('|', $cookies);
        setcookie('eleproducts', join('|', $cookies), NOW_TIME + 604800, '/');
        $_COOKIE['eleproducts'] = $cookiestr;
        return $eleproducts;
    }
    public function delete2($product_id) {
        $product_id = (int) $product_id;
        if (!($detail = D('Eleproduct')->find($product_id))) {
            $this->baoError('该产品不存在');
        }
        $this->_getcookie($product_id);
        $this->baoSuccess('删除成功', U('ele/cart'));
    }
    public function _getcookie($product_id){
        $cartall = explode('|', $this->cart);
        foreach ($cartall as $key => $val) {
            $local = explode('_', $val);
            $local[0] = (int) $local[0];
            $local[1] = (int) $local[1];
            if ($local[0] == $product_id) {
                unset($cartall[$key]);
            }
        }
        cookie('eleproducts', join('|', $cartall), NOW_TIME + 604800);
    }
    public function delete($product_id){
        $product_id = (int) $product_id;
        if (!($detail = D('Eleproduct')->find($product_id))) {
            $this->baoError('该产品不存在');
        }
        $this->_getcookie($product_id);
        $this->baoSuccess('删除成功', U('ele/shop', array('shop_id' => $detail['shop_id'])));
    }
    public function clean(){
        $shop_id = (int) $this->_param('shop_id');
        setcookie('eleproducts');
        $this->baoSuccess('清空购物车成功', U('ele/shop', array('shop_id' => $shop_id)));
    }
    public function changenum($product_id, $num){
        $product_id = (int) $product_id;
        $num = (int) $num;
        if ($this->cart[$product_id]) {
            if ($num >= 1 && $num <= 99) {
                $this->cart[$product_id] = $num;
                cookie('eleproducts', $this->cart, NOW_TIME + 604800);
            }
        }
    }
    public function add($product_id){
        $product_id = (int) $product_id;
        if (empty($product_id)) {
            $this->baoError('参数错误');
        }
        if (!($detail = D('Eleproduct')->find($product_id))) {
            $this->baoError('该产品不存在');
        }
        if (!empty($this->cart)) {
            foreach ($this->cart as $k => $v) {
                $data = D('Eleproduct')->find($k);
                if ($data['shop_id'] != $detail['shop_id']) {
                    $this->baoError('一次只能订购一家的外卖，您可以清空购物车重新定！');
                }
                break;
            }
        }
        if (isset($this->cart[$product_id])) {
            $this->cart[$product_id] += 1;
        } else {
            $this->cart[$product_id] = 1;
        }
        setcookie('eleproducts', $this->cart, NOW_TIME + 604800);
        $this->baoSuccess('增加购物车成功', U('ele/shop', array('shop_id' => $detail['shop_id'])));
    }
    public function cart(){
        if (!empty($this->cart)) {
            $ids = array_keys($this->cart);
            $total = array('num' => 0, 'money' => 0);
            $products = D('Eleproduct')->itemsByIds($ids);
            foreach ($products as $k => $val) {
                $products[$k]['cart_num'] = $this->cart[$val['product_id']];
                $total['num'] += $this->cart[$val['product_id']];
                $total['money'] += $this->cart[$val['product_id']] * $val['price'];
            }
            $this->assign('total', $total);
            $this->assign('cartgoods', $products);
        }
        $this->display();
    }
    public function delother(){
        $shop_id = (int) $_POST['shop_id'];
        $eleproducts = $this->_getCartGoods();
        foreach ($eleproducts as $k => $val) {
            if ($val['shop_id'] != $shop_id) {
                unset($eleproducts[$k]);
            }
        }
        $cookies = array();
        foreach ($eleproducts as $key => $v) {
            $cookies[] = $key . '_' . $v['cart_num'];
        }
        $cookiestr = join('|', $cookies);
        setcookie('eleproducts', join('|', $cookies), NOW_TIME + 604800, '/');
        $_COOKIE['eleproducts'] = $cookiestr;
        $this->ajaxReturn(array('status' => 'success', 'msg' => '操作成功'));
    }
    public function order()
    {
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        $num = $this->_post('num', false);
        if (empty($num)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '您还没有订餐呢'));
        }
        $shop_id = 0;
        $shops = array();
        $products = array();
        $total = array('money' => 0, 'num' => 0);
        foreach ($num as $key => $val) {
            $key = (int) $key;
            $val = (int) $val;
            if ($val < 1 || $val > 99) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '请选择正确的购买数量'));
            }
            $product = D('Eleproduct')->find($key);
            if (empty($product)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '产品不正确'));
            }
            $shop_id = $product['shop_id'];
            $product['buy_num'] = $val;
            $products[$key] = $product;
            $shops[$shop_id] = $shop_id;
            $total['money'] += $product['price'] * $val;
            $total['num'] += $val;
			$settlement_price  += $product['settlement_price'] * $val;
        }
        if (count($shops) > 1) {
            $this->ajaxReturn(array('status' => 'more', 'msg' => '您购买的商品是多个商户的!,要清空之前的购物车吗？'));
        }
        if (empty($shop_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商家不存在'));
        }
        $shop = D('Ele')->find($shop_id);
        if (empty($shop)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商家不存在'));
        }
		if (false == D('Shop')->check_shop_user_id($shop_id,$this->uid)) {//不能购买自己家的产品
			 $this->ajaxReturn(array('status' => 'error', 'msg' => '对不起，您是该商铺管理员，无法购买哦'));
		}
        if (!$shop['is_open']) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '商家已经打烊，实在对不住客官'));
        }
		$busihour = $this->closeshopele($shop['busihour']);
		 if ($busihour == 1) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '当前时间段商家正在休息，请稍后再来，谢谢'));
        }
        $total['money'] += $shop['logistics'];
        $total['need_pay'] = $total['money'];
        //后面要用到计算
        if ($shop['since_money'] > $total['money']) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '客官，您再订点吧！'));
        }
        if ($shop['is_new'] && !D('Eleorder')->checkIsNew($this->uid, $shop_id)) {
            //如果是新单
            if ($total['money'] >= $shop['full_money']) {
               
                $num1 = (int) (($total['money'] - $shop['full_money']) / 1000); //满足新单的条件 立马减几块钱
                //10块钱加1规则
                $total['new_money'] = $shop['new_money'] + $num1 * 100;
                $total['need_pay'] = $total['need_pay'] - $total['new_money'];
            }
        }
        
        $month = date('Ym', NOW_TIME);
        if ($order_id = D('Eleorder')->add(array(
			'user_id' => $this->uid, 
			'shop_id' => $shop_id, 
			'total_price' => $total['money'], 
			'logistics' => $shop['logistics'], 
			'need_pay' => $total['need_pay'], 
			'num' => $total['num'], 
			'new_money' => (int) $total['new_money'], 
			'settlement_price' => $settlement_price, 
			'status' => 0, 
			'create_time' => NOW_TIME, 
			'create_ip' => get_client_ip(), 
			'is_pay' => 0, 
			'month' => $month
		))) {
            foreach ($products as $val) {
                D('Eleorderproduct')->add(array(
					'order_id' => $order_id, 
					'product_id' => $val['product_id'], 
					'num' => $val['buy_num'], 
					'total_price' => $val['price'] * $val['buy_num'], 
					'month' => $month
				));
            }
            $this->ajaxReturn(array('status' => 'success', 'msg' => '下单成功！您可以选择配送地址!', 'url' => U('ele/pay', array('order_id' => $order_id))));
        }
        $this->ajaxReturn(array('status' => 'error', 'msg' => '创建订单失败'));
    }
    public function pay()
    {
        if (empty($this->uid)) {
            header('Location:' . U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Eleorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
        $this->assign('shop', D('Ele')->find($order['shop_id']));
        $ordergoods = D('Eleorderproduct')->where(array('order_id' => $order_id))->select();
        $goods = array();
        foreach ($ordergoods as $key => $val) {
            $goods[$val['product_id']] = $val['product_id'];
        }
        $products = D('Eleproduct')->itemsByIds($goods);
        $this->assign('products', $products);
        $this->assign('ordergoods', $ordergoods);
        if (!empty($order['addr_id'])) {
            $thisaddr = D('Useraddr')->find($order['addr_id']);
            $addrs = D('Useraddr')->where(array('user_id' => $this->uid, 'addr_id' => array('NEQ', $order['addr_id'])))->order('addr_id DESC')->limit(0, 4)->select();
            if (empty($addrs)) {
                $addrs[] = $thisaddr;
            } else {
                array_unshift($addrs, $thisaddr);
            }
        } else {
            $addrs = D('Useraddr')->where(array('user_id' => $this->uid))->order(array('is_default' => 'desc', 'addr_id' => 'desc'))->limit(0, 5)->select();
        }
        $this->assign('useraddr', $addrs);
        $this->assign('order', $order);
        $this->assign('payment', D('Payment')->getPayments());
        $this->display();
    }
    public function change_addr()
    {
        if (IS_AJAX) {
            $order_id = (int) $_POST['order_id'];
            $addr_id = (int) $_POST['addr_id'];
            $data = array('order_id' => $order_id, 'addr_id' => $addr_id);
            if (false !== D('Eleorder')->save($data)) {
                $thisaddr = D('Useraddr')->find($addr_id);
                $addrs = D('Useraddr')->where(array('user_id' => $this->uid, 'addr_id' => array('NEQ', $addr_id)))->order('addr_id DESC')->limit(0, 4)->select();
                if (empty($addrs)) {
                    $addrs[] = $thisaddr;
                } else {
                    array_unshift($addrs, $thisaddr);
                }
                $addr_array = array();
                foreach ($addrs as $k => $val) {
                    $addr_array[$k]['addr_id'] = $val['addr_id'];
                    $addr_array[$k]['city_id'] = $val['city_id'];
                    $addr_array[$k]['area_id'] = $val['area_id'];
                    $addr_array[$k]['business_id'] = $val['business_id'];
                    $addr_array[$k]['city'] = $this->citys[$val['city_id']]['name'];
                    $addr_array[$k]['area'] = $this->areas[$val['area_id']]['area_name'];
                    $addr_array[$k]['bizs'] = $this->bizs[$val['business_id']]['business_name'];
                    $addr_array[$k]['name'] = $val['name'];
                    $addr_array[$k]['addr'] = $val['addr'];
                    $addr_array[$k]['mobile'] = $val['mobile'];
                }
                $this->ajaxReturn(array('status' => 'success', 'msg' => '更换成功', 'res' => $addr_array));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '更换失败'));
            }
        }
    }
    public function pay2(){
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
            $this->ajaxLogin();
        }
        if (!$this->member['mobile']) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '<script>parent.check_user_mobile_for_pc();</script>'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Eleorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该订单不存在'));
            die;
        }
        $addr_id = (int) $this->_post('addr_id');
        if (empty($addr_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请选择一个要配送的地址！'));
        }
        D('Eleorder')->save(array('addr_id' => $addr_id, 'order_id' => $order_id));
        if (!($code = $this->_post('code'))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请选择支付方式！'));
        }

        //为写入物流记录，查询商家类型
        $shop = D('Shop');
        $fshop = $shop->where('shop_id =' . $order['shop_id'])->find();
        
        
        if ($code == 'wait') {
           
            D('Eleorder')->save(array('order_id' => $order_id, 'status' => 1)); //如果是货到付款
			D('Eleorder')->ele_delivery_order($order_id);//外卖配送接口
            D('Weixintmpl')->weixin_notice_ele_user($order_id,$this->uid,0);//外卖微信通知
			D('Eleorder')->combination_ele_print($order_id, $addr_id);//外卖打印万能接口
            D('Sms')->eleTZshop($order_id);//外卖短信通知
			D('Eleorder')->ele_month_num($order_id);//更新外卖销量
            D('Eleorder')->save(array('order_id' => $order_id, 'is_daofu' => 1, 'status' => 1));
            $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您下单成功！', 'url' => U('members/ele/index')));
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该支付方式不存在'));
            }
            $logs = D('Paymentlogs')->getLogsByOrderId('ele', $order_id);
            if (empty($logs)) {
                $logs = array(
					'type' => 'ele', 
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
            D('Weixintmpl')->weixin_notice_ele_user($order_id,$this->uid,1);//外卖微信通知
            $this->ajaxReturn(array('status' => 'success', 'msg' => '选择支付方式成功！下面请进行支付！', 'url' => U('payment/payment', array('log_id' => $logs['log_id']))));
        }
    }
    public function ajax()
    {
        $lng = $this->_get('lng', 'addslashes');
        $lat = $this->_get('lat', 'addslashes');
        $shop = D('Ele')->order(" (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ")->limit(0, 100)->select();
        $shop = $this->getShop($shop, $lng, $lat);
        $num = count($shop);
        die("{$num}");
    }
    //外卖
    public function takeout(){
        $this->display();
    }
    public function shop(){
        $shop_id = (int) $this->_param('shop_id');
        if (!($detail = D('Ele')->find($shop_id))) {
            $this->error('该餐厅不存在');
        }
        if (!($shop = D('Shop')->find($shop_id))) {
            $this->error('该餐厅不存在');
        }
        $Eleproduct = D('Eleproduct');
        import('ORG.Util.Page');
        // 导入分页类 
        $linkArr = array();
        $map = array('closed' => 0, 'audit' => 1, 'shop_id' => $shop_id);
        $linkArr = array('shop_id' => $shop_id);
        $cate = (int) $this->_param('cate');
        if ($cate) {
            $linkArr['cate'] = $cate;
            $map['cate_id'] = $cate;
            $this->assign('cate', $cate);
        }
        if ($is_new = (int) $this->_param('is_new')) {
            $linkArr['is_new'] = $is_new;
            $map['is_new'] = 1;
            $this->assign('is_new', $is_new);
        }
        if ($is_hot = (int) $this->_param('is_hot')) {
            $linkArr['is_hot'] = $is_hot;
            $map['is_hot'] = 1;
            $this->assign('is_hot', $is_hot);
        }
        if ($is_tuijian = (int) $this->_param('is_tuijian')) {
            $linkArr['is_tuijian'] = $is_tuijian;
            $map['is_tuijian'] = 1;
            $this->assign('is_tuijian', $is_tuijian);
        }
        $orderby = array();
        $order = $this->_param('order');
        switch ($order) {
            case 'p':
                $linkArr['order'] = 'p';
                $orderby = array('price' => 'asc');
                break;
            case 'n':
                $linkArr['order'] = 'n';
                $orderby = array('sold_num' => 'desc');
                break;
            default:
                $linkArr['order'] = 'd';
                $orderby = array('product_id' => 'desc');
                break;
        }
        $count = $Eleproduct->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $Eleproduct->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('linkArr', $linkArr);
        $this->assign('cates', D('Elecate')->where(array('shop_id' => $shop_id, 'closed' => 0))->select());
        $this->assign('detail', $detail);
        $this->assign('shop', $shop);
        $this->assign('ex', D('Shopdetails')->find($shop_id));
        if (!empty($this->cart)) {
            $ids = array_keys($this->cart);
            $total = array('num' => 0, 'money' => 0);
            $products = D('Eleproduct')->itemsByIds($ids);
            foreach ($products as $k => $val) {
                $products[$k]['cart_num'] = $this->cart[$val['product_id']];
                $total['num'] += $this->cart[$val['product_id']];
                $total['money'] += $this->cart[$val['product_id']] * $val['price'];
            }
            $this->assign('total', $total);
            $this->assign('cartgoods', $products);
        }
        $this->display();
    }
    public function evaluate(){
        $shop_id = (int) $this->_param('shop_id');
        if (!($detail = D('Ele')->find($shop_id))) {
            $this->error('该餐厅不存在');
        }
        if (!($shop = D('Shop')->find($shop_id))) {
            $this->error('该餐厅不存在');
        }
        $shopdianping = D('Eledianping');
        import('ORG.Util.Page');
        // 导入分页类 
        $linkArr = array();
        $map = array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY));
        $linkArr = array('shop_id' => $shop_id);
        if ($evaluate = (int) $this->_param('evaluate')) {
            switch ($evaluate) {
                case 1:
                    $map['score'] = array('LT', 3);
                    break;
                case 2:
                    $map['score'] = array('EQ', 3);
                    break;
                case 3:
                    $map['score'] = array('GT', 3);
                    break;
            }
            $linkArr['evaluate'] = $evaluate;
            $this->assign('evaluate', $evaluate);
        }
        $count = $shopdianping->where($map)->count();
        $counts = $shopdianping->where(array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY)))->count();
        $count1 = $shopdianping->where(array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY), 'score' => array('LT', 3)))->count();
        $count2 = $shopdianping->where(array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY), 'score' => array('EQ', 3)))->count();
        $count3 = $shopdianping->where(array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY), 'score' => array('GT', 3)))->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $shopdianping->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($order_ids)) {
            $this->assign('pics', D('Eledianpingpics')->where(array('order_id' => array('IN', $order_ids)))->select());
        }
        $this->assign('counts', $counts);
        $this->assign('count1', $count1);
        $this->assign('count2', $count2);
        $this->assign('count3', $count3);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('detail', $detail);
        $this->assign('shop', $shop);
        $this->assign('linkArr', $linkArr);
        $this->assign('ex', D('Shopdetails')->find($shop_id));
        if (!empty($this->cart)) {
            $ids = array_keys($this->cart);
            $total = array('num' => 0, 'money' => 0);
            $products = D('Eleproduct')->itemsByIds($ids);
            foreach ($products as $k => $val) {
                $products[$k]['cart_num'] = $this->cart[$val['product_id']];
                $total['num'] += $this->cart[$val['product_id']];
                $total['money'] += $this->cart[$val['product_id']] * $val['price'];
            }
            $this->assign('total', $total);
            $this->assign('cartgoods', $products);
        }
        $this->display();
    }
    private function getShop($shop, $lng, $lat)
    {
        // 2公里过滤
        foreach ($shop as $k => $v) {
            $shop[$k]['d'] = getDistanceNone($lat, $lng, $v['lat'], $v['lng']);
            if ($shop[$k]['d'] > 20000) {
                //大于2KM的要咔嚓掉
                unset($shop[$k]);
            }
        }
        return $shop;
    }
    public function main()
    {
        $shop_ids = array();
        $map = array();
        $linkArr = array();
        if ($is_new = (int) $this->_param('is_new')) {
            $map['is_new'] = 1;
            $linkArr['is_new'] = 1;
            $this->assign('is_new', $is_new);
        }
        if ($is_open = (int) $this->_param('is_open')) {
            $map['is_open'] = 1;
            $linkArr['is_open'] = 1;
            $this->assign('is_open', $is_open);
        }
        if ($is_pay = (int) $this->_param('is_pay')) {
            $map['is_pay'] = 1;
            $linkArr['is_pay'] = 1;
            $this->assign('is_pay', $is_pay);
        }
        if ($business = (int) $this->_get('business')) {
            $linkArr['business'] = $business;
            $eles = D('Ele')->where($map)->order(array('sold_num' => 'desc'))->limit(0, 100)->select();
        } else {
            $lng = $this->_get('lng', 'addslashes');
            $lat = $this->_get('lat', 'addslashes');
            $linkArr['lng'] = $lng;
            $linkArr['lat'] = $lat;
            if (empty($lng) || empty($lat)) {
                $this->error('很抱歉，您查看的位置不存在！');
            }
            $eles = D('Ele')->where($map)->order(" (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ")->limit(0, 100)->select();
            $eles = $this->getShop($eles, $lng, $lat);
        }
        foreach ($eles as $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
        }
        $tops = D('Ele')->order(array('orderby' => 'asc'))->limit(0, 4)->select();
        foreach ($tops as $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
        }
        $this->assign('eles', $eles);
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('tops', $tops);
        $this->assign('linkArr', $linkArr);
        $this->display();
    }
    public function index(){
        date_default_timezone_set('Asia/Shanghai');
        import('ORG.Util.Page');
        // 导入分页类 
        $ele = D('Ele');
        $map = array('is_open'=>1,'audit' => 1,'city_id' => $this->city_id);
        $linkArr = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
            $linkArr['keywrod'] = $map['shop_name'];
        }
        $cate = $this->_param('cate', 'htmlspecialchars');
        $lng = $this->_param('lng', 'htmlspecialchars');
        $lat = $this->_param('lat', 'htmlspecialchars');
        $linkArr['cate'] = $cate;
        $linkArr['lng'] = $lng;
        $linkArr['lat'] = $lat;
        $this->assign('cate', $cate);
        $price = (int) $this->_param('price');
        switch ($price) {
            case 1:
                $map['since_money'] = array('ELT', '5000');
                break;
            case 2:
                $map['since_money'] = array('between', '5001,10000');
                break;
            case 3:
                $map['since_money'] = array('between', '10001,20000');
                break;
            case 4:
                $map['since_money'] = array('EGT', '20001');
                break;
        }
        $linkArr['price'] = $price;
        $this->assign('price', $price);
        $order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 's':
                $orderby = array('month_num' => 'desc');
                $linkArr['order'] = $order;
                break;
            case 't':
                $orderby = array('distribution' => 'asc');
                $linkArr['order'] = $order;
                break;
            default:
                $orderby = array('orderby' => 'asc', 'sold_num' => 'desc', 'month_num' => 'desc');
                break;
        }
        $this->assign('order', $order);
        if ($new = (int) $this->_param('new')) {
            $linkArr['new'] = $new;
            $map['is_new'] = $new;
        }
        $this->assign('new', $new);
        if ($fan = (int) $this->_param('fan')) {
            $linkArr['fan'] = $fan;
            $map['is_fan'] = $fan;
        }
        $this->assign('fan', $fan);
        if ($pay = (int) $this->_param('pay')) {
            $linkArr['pay'] = $pay;
            $map['is_pay'] = $pay;
        }
        $this->assign('pay', $pay);
        $lists = $ele->order($orderby)->where($map)->select();
        foreach ($lists as $k => $val) {
            if (!empty($cate)) {
                if (strpos($val['cate'], $cate) === false) {
                    unset($lists[$k]);
                }
            }
			//增加时间段控制
            if ($this->closeshopele($val['busihour'])) {
                $lists[$k]['bsti'] = 1;
            } else {
                $lists[$k]['bsti'] = 0;
            }
            if (!empty($lng) && !empty($lat)) {
                $lists[$k]['d'] = getDistanceNone($lat, $lng, $val['lat'], $val['lng']);
				
                if ($lists[$k]['d'] > ($val['is_radius']*10000)) {
                    //大于2KM的要咔嚓掉
                    unset($lists[$k]);
                }
            }
        }
        $count = count($lists);
        $Page = new Page($count, 20);
        $show = $Page->show();
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);
        $shop_ids = array();
        foreach ($list as $k => $val) {
            if (!empty($cate)) {
                if (strpos($val['cate'], $cate) === false) {
                    unset($list[$k]);
                }
            }
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('linkArr', $linkArr);
        $this->display();
    }
    public function detail(){
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
        import('ORG.Util.Page');
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
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->assign('detail', $detail);
        $this->assign('ex', D('Shopdetails')->find($shop_id));
        $tuan = D('Tuan')->where(array('shop_id' => $shop_id, 'audit' => 1, 'closed' => 0, 'end_date' => array('EGT', TODAY)))->order(' tuan_id desc ')->limit(0, 5)->select();
        $this->assign('tuan', $tuan);
        $coupon = D('Coupon')->order(' coupon_id desc ')->find(array('where' => array('shop_id' => $shop_id, 'audit' => 1, 'closed' => 0, 'expire_date' => array('EGT', TODAY))));
        $this->assign('coupon', $coupon);
        D('Shop')->updateCount($shop_id, 'view');
        $this->seodatas['shop_name'] = $detail['shop_name'];
        $this->seodatas['shop_tel'] = $detail['shop_tel'];
        if ($this->uid) {
            D('Userslook')->look($this->uid, $shop_id);
        }
        $this->assign('cate', $this->shopcates[$detail['cate_id']]);
        $this->assign('shoppic', D('Shoppic')->order('orderby asc')->limit(0, 8)->where(array('shop_id' => $shop_id))->select());
        $this->display();
    }
    public function closeshopele($busihour)
    {
        $timestamp = time();
        $now = date('G.i', $timestamp);
        $close = true;
        if (empty($busihour)) {
            return false;
        }
        foreach (explode(',', str_replace(':', '.', $busihour)) as $period) {
            list($periodbegin, $periodend) = explode('-', $period);
            if ($periodbegin > $periodend && ($now >= $periodbegin || $now < $periodend) || $periodbegin < $periodend && $now >= $periodbegin && $now < $periodend) {
                $close = false;
            }
        }
        return $close;
    }
    public function dianping()
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
        $data['show_date'] = date('Y-m-d', NOW_TIME);//实时生效
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
            $this->baoSuccess('恭喜您点评成功!', U('ele/detail', array('shop_id' => $shop_id)));
        }
        $this->baoError('点评失败！');
    }
}
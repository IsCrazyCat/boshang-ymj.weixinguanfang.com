<?php
class EleAction extends CommonAction{
    protected $cart = array();
    public function _initialize(){
        parent::_initialize();
        $this->cart = $this->getcart();
        $this->assign('cartnum', (int) array_sum($this->cart));
        $cate = D('Ele')->getEleCate();
        $this->assign('elecate', $cate);
    }
    public function getcart(){
        $shop_id = (int) $this->_param('shop_id');
        $cart = (array) json_decode($_COOKIE['ele']);
        $carts = array();
        foreach ($cart as $kk => $vv) {
            foreach ($vv as $key => $v) {
                $carts[$kk][$key] = (array) $v;
            }
        }
        $ids = $nums = array();
        foreach ($carts[$shop_id] as $k => $val) {
            $ids[$val['product_id']] = $val['product_id'];
            $nums[$val['product_id']] = $val['num'];
        }
        $eleproducts = D('Eleproduct')->itemsByIds($ids);
        foreach ($eleproducts as $k => $val) {
            $eleproducts[$k]['cart_num'] = $nums[$val['product_id']];
            $eleproducts[$k]['total_price'] = $nums[$val['product_id']] * $val['price'];
        }
        return $eleproducts;
    }
  
    public function index(){
        $linkArr = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $linkArr['keyword'] = $keyword;
        $cate = $this->_param('cate', 'htmlspecialchars');
        $this->assign('cate', $cate);
        $linkArr['cate'] = $cate;
        $order = $this->_param('order', 'htmlspecialchars');
        $this->assign('order', $order);
        $linkArr['order'] = $order;
        $area = (int) $this->_param('area');
        $this->assign('area', $area);
        $linkArr['area'] = $area;
        $business = (int) $this->_param('business');
        $this->assign('business', $business);
        $linkArr['business'] = $business;
        $this->assign('nextpage', LinkTo('ele/loaddata', $linkArr, array('t' => NOW_TIME, 'p' => '0000')));
        $this->assign('linkArr', $linkArr);
        $this->display();
    }
	
	
	
	
    public function loaddata(){
        $ele = D('Ele');
        import('ORG.Util.Page');
        $map = array('audit' => 1,'is_open'=>1, 'city_id' => $this->city_id);
        $area = (int) $this->_param('area');
        if ($area) {
            $map['area_id'] = $area;
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $business = (int) $this->_param('business');
        if ($business) {
            $map['business_id'] = $business;
        }
        $order = $this->_param('order', 'htmlspecialchars');
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        switch ($order) {
            case 'a':
                $orderby = array("(ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') )" => 'asc', 'orderby' => 'asc', 'month_num' => 'desc', 'distribution' => 'asc', 'since_money' => 'asc');
                break;
            case 'p':
                $orderby = array('since_money' => 'asc');
                break;
            case 'v':
                $orderby = array('distribution' => 'asc');
                break;
            case 'd':
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}')) asc ";
                break;
            case 's':
                $orderby = array('month_num' => 'desc');
                break;
			default:
                $orderby = array("(ABS(lng - '{$lng}') +  ABS(lat - '{$lat}'))" => 'asc', 'orderby' => 'asc');
                break;
        }
        $cate = $this->_param('cate', 'htmlspecialchars');
        $lists = $ele->order($orderby)->where($map)->select();
        foreach ($lists as $k => $val) {
            if (!empty($cate)) {
                if (strpos($val['cate'], $cate) === false) {
                    unset($lists[$k]);
                }
            }
        }
        $count = count($lists);
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);
		
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
            if ($this->closeshopele($val['busihour'])) {
                $list[$k]['bsti'] = 1;
            } else {
                $list[$k]['bsti'] = 0;
            }
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

	
    public function closeshopele($busihour) {
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
    public function shop(){
        $shop_id = (int) $this->_param('shop_id');
        if (!($detail = D('Ele')->find($shop_id))) {
            $this->error('该餐厅不存在');
        }
        if (!($shop = D('Shop')->find($shop_id))) {
            $this->error('该餐厅不存在');
        }
        $Eleproduct = D('Eleproduct');
        $map = array('closed' => 0, 'audit' => 1, 'shop_id' => $shop_id);
        $list = $Eleproduct->where($map)->order(array('sold_num' => 'desc', 'price' => 'asc'))->select();
        foreach ($list as $k => $val) {
            $list[$k]['cart_num'] = $this->cart[$val['product_id']]['cart_num'];
        }
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->assign('cates', D('Elecate')->where(array('shop_id' => $shop_id, 'closed' => 0))->select());
        $this->assign('shop', $shop);
        $this->display();
    }
    public function test(){
        setcookie("ele", "", time() - 3600, "/");
        $goods = cookie('ele');
        print_r($goods);
        exit;
    }
    //重写购物车
    public function cart(){
        $cart = null;
		$type = (int) $this->_param('type');
        if ($goods = cookie('ele')) {
            $total = array('num' => 0, 'money' => 0);
            $goods = (array) json_decode($goods);
            $ids = array();
            foreach ($goods as $shop_id => $items) {
                foreach ($items as $k2 => $item) {
                    $item = (array) $item;
                    $total['num'] += $item['num'];
                    $total['money'] += $item['price'] * $item['num'];
                    $ids[] = $item['product_id'];
                    $product_item_num[$item['product_id']] = $item['num'];
                }
            }
            $ids = implode(',', $ids);
            $products = D('Eleproduct')->where('closed=0')->select($ids);
            foreach ($products as $k => $val) {
                $products[$k]['cart_num'] = $product_item_num[$val['product_id']];
                if ($products[$k]['cart_num'] < 1) {
                    unset($products[$k]);
                }
            }
            $this->assign('detail', D('Ele')->find($shop_id));
            $this->assign('total', $total);
            $this->assign('shop_id', $shop_id);
			$this->assign('type', $type);
            $this->assign('cartgoods', $products);
        }
        $this->display();
    }
    public function ajax(){
        $this->cart = cookie('eleproduct');
        $num = count($this->cart);
        $num = $num + 1;
        die("{$num}");
    }
    public function order(){
        if (empty($this->uid)) {
            $this->fengmiMsg('请先登陆', U('passport/login'));
        }
        $num = $this->_post('num', false);
        if (empty($num)) {
            $this->error('您还没有订餐呢');
        }
        $shop_id = 0;
        $shops = array();
        $products = array();
        $total = array('money' => 0, 'num' => 0);
        $product_name = array();
        foreach ($num as $key => $val) {
            $key = (int) $key;
            $val = (int) $val;
            if ($val < 1 || $val > 99) {
                $this->fengmiMsg('请选择正确的购买数量');
            }
            $product = D('Eleproduct')->find($key);
            $product_name[] = $product['product_name'];
            if (empty($product)) {
                $this->fengmiMsg('产品不正确');
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
            $this->fengmiMsg('您购买的商品是2个商户的！');
        }
        if (empty($shop_id)) {
            $this->fengmiMsg('商家不存在');
        }
        $shop = D('Ele')->find($shop_id);
        if (empty($shop)) {
            $this->fengmiMsg('该商家不存在');
        }
		if (false == D('Shop')->check_shop_user_id($shop_id,$this->uid)) {//不能购买自己家的产品
			 $this->fengmiMsg('您不能购买自己的外卖');
		}
		
        if (!$shop['is_open']) {
            $this->fengmiMsg('商家已经打烊，实在对不住客官');
        }
		$busihour = $this->closeshopele($shop['busihour']);
		 if ($busihour == 1) {
            $this->fengmiMsg('商家休息中，请稍后再试');
        }
        $total['money'] += $shop['logistics'];
        $total['need_pay'] = $total['money'];
        if ($shop['since_money'] > $total['money']) {
            $this->fengmiMsg('客官，您再订点吧！');
        }
        if ($shop['is_new'] && !D('Eleorder')->checkIsNew($this->uid, $shop_id)) {
            if ($total['money'] >= $shop['full_money']) {
                $num1 = (int) (($total['money'] - $shop['full_money']) / 1000);
                $total['new_money'] = $shop['new_money'] + $num1 * 100;
                $total['need_pay'] = $total['need_pay'] - $total['new_money'];
            }
        }
        $month = date('Ym', NOW_TIME);
        if ($order_id = D('Eleorder')->add(array(
			'user_id' => $this->uid, 
			'shop_id' => $shop_id, 
			'total_price' => $total['money'], 
			'need_pay' => $total['need_pay'], 
			'num' => $total['num'], 
			'new_money' => (int) $total['new_money'], 
			'logistics' => $shop['logistics'], 
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
            setcookie("ele", "", time() - 3600, "/");
            $this->fengmiMsg('下单成功！您可以选择配送地址!', U('ele/pay', array('order_id' => $order_id)));
        }
        $this->fengmiMsg('创建订单失败！');
    }
    public function message(){
        $order_id = (int) $this->_get('order_id');
        if (!($detail = D('Eleorder')->find($order_id))) {
            $this->fengmiMsg('没有该订单');
            die;
        }
        if ($detail['status'] != 0) {
            $this->fengmiMsg('参数错误');
            die;
        }
        $ele_shop = D('Ele')->find($detail['shop_id']);
        $tags = $ele_shop['tags'];
        $tagsarray = array();
        if (!empty($tags)) {
            $tagsarray = explode(',', $tags);
        }
        if ($this->isPost()) {
            if ($message = $this->_param('message', 'htmlspecialchars')) {
                $data = array('order_id' => $order_id, 'message' => $message);
                if (D('Eleorder')->save($data)) {
                    $this->fengmiMsg('添加留言成功', U('Wap/ele/pay', array('order_id' => $detail['order_id'])));
                }
            }
            $this->fengmiMsg('请填写留言');
        } else {
            $this->assign('detail', $detail);
            $this->assign('tagsarray', $tagsarray);
            $this->display();
        }
    }
    public function pay(){
        if (empty($this->uid)) {
            header('Location:' . U('passport/login'));
            die;
        }
        $this->check_mobile();
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
        $useraddr_is_default = D('Useraddr')->where(array('user_id' => $this->uid, 'is_default' => 1))->limit(0, 1)->select();
        $useraddrs = D('Useraddr')->where(array('user_id' => $this->uid))->limit(0, 1)->select();
        if (!empty($useraddr_is_default)) {
            $this->assign('useraddr', $useraddr_is_default);
        } else {
            $this->assign('useraddr', $useraddrs);
        }
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('order', $order);
        $eles = D('Ele')->find($order['shop_id']);
        if ($eles['is_pay'] == 1) {
            $payment = D('Payment')->getPayments(true);
        } else {
            $payment = D('Payment')->getPayments_delivery(true);
        }
        $this->assign('payment', $payment);
        $this->display();
    }
    public function pay2() {
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Eleorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->fengmiMsg('该订单不存在');
            die;
        }
        $addr_id = (int) $this->_post('addr_id');
        $uaddr = D('Useraddr')->where('addr_id =' . $addr_id)->find();
        if (empty($addr_id)) {
            $this->fengmiMsg('请选择一个要配送的地址！');
        }
//        $mobile = D('Users')->where(array('user_id' => $this->uid))->getField('mobile');
//        if (!$mobile) {
//            $this->fengmiMsg('请先绑定手机号码再提交！');
//        }
        D('Eleorder')->save(array('addr_id' => $addr_id, 'order_id' => $order_id));
        if (!($code = $this->_post('code'))) {
            $this->fengmiMsg('请选择支付方式！');
        }
        if ($code == 'wait') {
            D('Eleorder')->ele_delivery_order($order_id);//外卖配送接口
            D('Eleorder')->save(array('order_id' => $order_id, 'status' => 1));
            setcookie("ele", "", time() - 3600, "/");
            D('Eleorder')->save(array('order_id' => $order_id, 'is_daofu' => 1, 'status' => 1));
			D('Eleorder')->combination_ele_print($order_id, $addr_id);//外卖打印万能接口
            D('Sms')->eleTZshop($order_id);
			D('Eleorder')->ele_month_num($order_id);//更新外卖销量
			D('Weixintmpl')->weixin_notice_ele_user($order_id,$this->uid,0);//外卖微信通知货到付款
            $this->fengmiMsg('货到付款您下单成功！', U('user/eleorder/index'));
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->error('该支付方式不存在');
            }
            $logs = D('Paymentlogs')->getLogsByOrderId('ele', $order_id);
            if (empty($logs)) {
                $logs = array('type' => 'ele', 'user_id' => $this->uid, 'order_id' => $order_id, 'code' => $code, 'need_pay' => $order['need_pay'], 'create_time' => NOW_TIME, 'create_ip' => get_client_ip(), 'is_paid' => 0);
                $logs['log_id'] = D('Paymentlogs')->add($logs);
            } else {
                $logs['need_pay'] = $order['need_pay'];
                $logs['code'] = $code;
                D('Paymentlogs')->save($logs);
            }
            D('Weixintmpl')->weixin_notice_ele_user($order_id,$this->uid,1);//外卖微信通知货到付款
            $this->fengmiMsg('选择支付方式成功！下面请进行支付！', U('payment/payment', array('log_id' => $logs['log_id'])));
        }
    }
   
    public function favorites(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('没有该商家');
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
        }
        if (D('Shopfavorites')->check($shop_id, $this->uid)) {
            $this->error('您已经收藏过了！');
        }
        $data = array('shop_id' => $shop_id, 'user_id' => $this->uid, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
        if (D('Shopfavorites')->add($data)) {
            $this->success('恭喜您收藏成功！', U('ele/detail', array('shop_id' => $shop_id)));
        }
        $this->error('收藏失败！');
    }
    public function detail(){
        $shop_id = (int) $this->_param('shop_id');
        if (!($detail = D('Ele')->find($shop_id))) {
            $this->error('没有该商家');
            die;
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->error('该商家不存在');
            die;
        }
        $this->assign('detail', $detail);
        $this->assign('shop', D('Shop')->find($shop_id));
        $this->assign('ex', D('Shopdetails')->find($shop_id));
        $this->display();
    }
    public function dianping(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Ele')->find($shop_id))) {
            $this->error('没有该商家');
            die;
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
            die;
        }
        $this->assign('detail', $detail);
        $this->display();
    }
    public function dianpingloading(){
        $shop_id = (int) $this->_get('shop_id');
        if (!($detail = D('Ele')->find($shop_id))) {
            die('0');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            die('0');
        }
        $Eledianping = D('Eledianping');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY));
        $count = $Eledianping->where($map)->count();
        $Page = new Page($count, 5);
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $show = $Page->show();
        $list = $Eledianping->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($order_ids)) {
            $this->assign('pics', D('Eledianpingpics')->where(array('order_id' => array('IN', $order_ids)))->select());
        }
        $this->assign('totalnum', $count);
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
	//点评详情
    public function img(){
        $dianping_id = (int) $this->_get('dianping_id');
        if (!($detail = D('Eledianping')->where(array('dianping_id'=>$dianping_id))->find())){
            $this->error('没有该点评');
            die;
        }
        if ($detail['closed']) {
            $this->error('该点评已经被删除');
            die;
        }
        $list =  D('Eledianpingpics')->where(array('order_id' =>$detail['order_id']))->select();
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
	
}
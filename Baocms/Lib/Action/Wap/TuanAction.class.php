<?php
class TuanAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        //统计套餐分类数量代码开始
        $Tuan = D('Tuan');
        $tuancates = D('Tuancate')->fetchAll();
        foreach ($tuancates as $key => $v) {
            if ($v['cate_id']) {
                $catids = D('Tuancate')->getChildren($v['cate_id']);
                if (!empty($catids)) {
                    $count = $Tuan->where(array('cate_id' => array('IN', $catids), 'closed' => 0, 'audit' => 1, 'city_id' => $this->city_id))->count();
                } else {
                    $count = $Tuan->where(array('cate_id' => $cat, 'closed' => 0, 'audit' => 1, 'city_id' => $this->city_id))->count();
                }
            }
            $tuancates[$key]['count'] = $count;
        }
        $this->assign('tuancates', $tuancates);
    }
    public function main(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->mobile_title = '套餐主页';
        $this->display();
    }
    public function mainload(){
        $aready = (int) $this->_param('aready');
        $t = D('Tuan');
        if ($aready == 1) {
            $order = 'create_time desc';
        } elseif ($aready == 2) {
            $order = 'sold_num desc';
        } elseif ($aready == 3) {
            $order = 'views desc';
        }
        import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0, 'city_id' => $this->city_id, 'end_date' => array('EGT', TODAY));
        $count = $t->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $t->where($map)->order($order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('tuans', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function push(){
        $Tuan = D('Tuan');
        import('ORG.Util.Page');
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $map = array('audit' => 1, 'closed' => 0, 'city_id' => $this->city_id, 'end_date' => array('EGT', TODAY));
        $count = $Tuan->where($map)->count();
        $Page = new Page($count, 3);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $tuans = $Tuan->order(" (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ")->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($tuans as $k => $val) {
            $tuans[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $this->assign('tuans', $tuans);
        $this->assign('page', $show);
        $this->display();
    }
    public function tuancate(){
        $this->display();
    }
    public function index(){
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $cat = (int) $this->_param('cat');
        $this->assign('cat', $cat);
        $areas = D('Area')->fetchAll();
        $area = (int) $this->_param('area');
        $this->assign('area_id', $area);
        $this->assign('areas', $areas);
        $order = $this->_param('order', 'htmlspecialchars');
        $this->assign('order', $order);
        $biz = D('Business')->fetchAll();
        $business = (int) $this->_param('business');
        $tuancates = D('Tuancates')->find();
        $this->assign('business_id', $business);
        $this->assign('biz', $biz);
        $this->assign('nextpage', LinkTo('tuan/loaddata', array('cat' => $cat, 'area' => $area, 'business' => $business, 'order' => $order, 't' => NOW_TIME, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
    }
    public function loaddata(){
        $Tuan = D('Tuan');
        import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0, 'city_id' => $this->city_id, 'end_date' => array('EGT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $cat = (int) $this->_param('cat');
        if ($cat) {
            $catids = D('Tuancate')->getChildren($cat);
            if (!empty($catids)) {
                $map['cate_id'] = array('IN', $catids);
            } else {
                $map['cate_id'] = $cat;
            }
        }
        $area = (int) $this->_param('area');
        if ($area) {
            $map['area_id'] = $area;
        }
        $order = $this->_param('order', 'htmlspecialchars');
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $orderby = '';
        switch ($order) {
            case 3:
                $orderby = array('create_time' => 'desc');
                break;
            case 2:
                $orderby = array('orderby' => 'asc', 'tuan_id' => 'desc');
                break;
            default:
                $orderby = array('orderby' => 'asc');
                break;
        }
        $count = $Tuan->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Tuan->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['end_time'] = strtotime($val['end_date']) - NOW_TIME + 86400;
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $shops = D('Shop')->itemsByIds($shop_ids);
            $ids = array();
            foreach ($shops as $k => $val) {
                $shops[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
                $d = getDistanceNone($lat, $lng, $val['lat'], $val['lng']);
                $ids[$d][] = $k;
            }
            ksort($ids);
            $showshops = array();
            foreach ($ids as $arr1) {
                foreach ($arr1 as $val) {
                    $showshops[$val] = $shops[$val];
                }
            }
            $this->assign('shops', $showshops);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function detail(){
        $tuan_id = (int) $this->_get('tuan_id');
        $tao_arr = D('Tuanmeal')->order(array('id' => 'asc'))->where(array('tuan_id' => $tuan_id))->select();
        $this->assign('tuan_id', $tuan_id);
        $this->assign('tao_arr', $tao_arr);
        if (empty($tuan_id)) {
            $this->error('该套餐信息不存在！');
            die;
        }
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->error('该套餐信息不存在！');
            die;
        }
        if ($detail['audit'] != 1) {
            $this->error('该套餐信息还在审核中哦');
            die;
        }
        if ($detail['closed']) {
            $this->error('该套餐信息不存在！');
            die;
        }
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $detail = D('Tuan')->_format($detail);
        $detail['d'] = getDistance($lat, $lng, $detail['lat'], $detail['lng']);
        $detail['end_time'] = strtotime($detail['end_date']) - NOW_TIME + 86400;
        $this->assign('detail', $detail);
        $shop_id = $detail['shop_id'];
        $shop = D('Shop')->find($shop_id);
        $this->assign('tuans', D('Tuan')->where(array('audit' => 1, 'closed' => 0, 'shop_id' => $shop_id, 'bg_date' => array('ELT', TODAY), 'end_date' => array('EGT', TODAY), 'tuan_id' => array('NEQ', $tuan_id)))->limit(0, 5)->select());
        $pingnum = D('Tuandianping')->where(array('tuan_id' => $tuan_id))->count();
        $this->assign('pingnum', $pingnum);
        $score = (int) D('Tuandianping')->where(array('tuan_id' => $tuan_id))->avg('score');
        if ($score == 0) {
            $score = 5;
        }
        $this->assign('score', $score);
        $tuandetails = D('Tuandetails')->find($tuan_id);
        $this->assign('tuandetails', $tuandetails);
        $this->assign('shop', $shop);
        $tuansids = $detail['cate_id'];
        $this->assign('tuansids', $tuansids);
        $thumb = unserialize($detail['thumb']);
        $this->assign('thumb', $thumb);
        $this->display();
    }
    //团购图片详情
    public function pic(){
        $tuan_id = (int) $this->_get('tuan_id');
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->error('没有该团购');
            die;
        }
        if ($detail['closed']) {
            $this->error('该团购已经被删除');
            die;
        }
        $thumb = unserialize($detail['thumb']);
        $this->assign('thumb', $thumb);
        $this->assign('detail', $detail);
        $this->display();
    }
    public function tuwen(){
        $tuan_id = (int) $this->_get('tuan_id');
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->error('没有该团购');
            die;
        }
        if ($detail['closed']) {
            $this->error('该团购已经被删除');
            die;
        }
        $detail = D('Tuan')->_format($detail);
        $tuandetails = D('Tuandetails')->find($tuan_id);
        $this->assign('tuandetails', $tuandetails);
        $this->assign('detail', $detail);
        $this->display();
    }
    //团购点评
    public function dianping(){
        $tuan_id = (int) $this->_get('tuan_id');
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->error('没有该团购');
            die;
        }
        if ($detail['closed']) {
            $this->error('该团购已经被删除');
            die;
        }
        $this->assign('next', LinkTo('tuan/dianpingloading', $linkArr, array('tuan_id' => $tuan_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }
    public function dianpingloading(){
        $tuan_id = (int) $this->_get('tuan_id');
        if (!($detail = D('Tuan')->find($tuan_id))) {
            die('0');
        }
        if ($detail['closed']) {
            die('0');
        }
        $Tuandianping = D('Tuandianping');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'tuan_id' => $tuan_id, 'show_date' => array('ELT', TODAY));
        $count = $Tuandianping->where($map)->count();
        $Page = new Page($count, 5);
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Tuandianping->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
        $this->assign('totalnum', $count);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	
	//点评详情
    public function img(){
        $dianping_id = (int) $this->_get('dianping_id');
        if (!($detail = D('Tuandianping')->where(array('dianping_id'=>$dianping_id))->find())){
            $this->error('没有该点评');
            die;
        }
        if ($detail['closed']) {
            $this->error('该点评已经被删除');
            die;
        }
        $list =  D('Tuandianpingpics')->where(array('order_id' =>$detail['order_id']))->select();
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	
    public function order(){
        if (!$this->uid) {
            $this->fengmiMsg('登录状态失效!', U('passport/login'));
        }
        $tuan_id = (int) $this->_get('tuan_id');
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->fengmiMsg('该商品不存在');
        }
        if ($detail['closed'] == 1 || $detail['end_date'] < TODAY) {
            $this->fengmiMsg('该商品已经结束');
        }
        $num = (int) $this->_post('num');
        if ($num <= 0 || $num > 99) {
            $this->fengmiMsg('请输入正确的购买数量');
        }
		
		if ($num > $detail['num']) {
            $this->fengmiMsg('亲，您最多购买' . $detail['num'] . '份哦！');
        }
		if (false == D('Shop')->check_shop_user_id($detail['shop_id'],$this->uid)) {//不能购买自己家的产品
			$this->fengmiMsg('您不能购买自己的产品');
		}
        if ($num > $detail['xiangou'] && $detail['xiangou'] > 0) {
            $this->fengmiMsg('亲，每人只能购买' . $detail['xiangou'] . '份哦！');
        }
        if ($detail['xiadan'] == 1) {
            $where['user_id'] = $this->uid;
            $where['tuan_id'] = $tuan_id;
            $xdinfo = D('Tuanorder')->where($where)->order('order_id desc')->Field('order_id')->find();
            if ($xdinfo) {
                $this->fengmiMsg('该商品只允许购买一次!');
                die;
            }
        }
        if ($detail['xiangou'] > 0) {
            $y = date('Y');
            $m = date('m');
            $d = date('d');
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
                        $this->fengmiMsg('该商品每天每人限购' . $detail['xiangou'] . '份');
                        die;
                    }
                }
            }
        }

        //优惠劵结束
        $data = array(
			'tuan_id' => $tuan_id, 
			'num' => $num, 
			'user_id' => $this->uid, 
			'shop_id' => $detail['shop_id'], 
			'create_time' => NOW_TIME, 
			'create_ip' => get_client_ip(), 
			'total_price' => $detail['tuan_price'] * $num, 
			'mobile_fan' => $detail['mobile_fan'] * $num, 
			'need_pay' => $detail['tuan_price'] * $num - $detail['mobile_fan'] * $num,
			'status' => 0, 
			'is_mobile' => 1
		);
        if ($order_id = D('Tuanorder')->add($data)) {
            //添加优惠劵满减的优惠劵
            $download_id = (int) $this->_post('download_id');
            if(!empty($download_id)){
                $coupon_price = D('Coupon')->Obtain_Coupon_Price_tuan($order_id,$download_id);
                if(!empty($coupon_price)){
                    D('TuanOrder')->save(array('order_id' =>$order_id,'download_id' =>$download_id,'need_pay' =>($detail['tuan_price'] * $num - $detail['mobile_fan'] * $num - $coupon_price)));
                    //p(D('Order')->getLastSql()) ;die;这里有问题，后面立即处理
                }
            }
            D('Tuan')->where($where)->setDec('num', $num);//更新减掉库存
            $this->fengmiMsg('创建订单成功，下一步选择支付方式！', U('tuan/pay', array('order_id' => $order_id)));
            die;
        }
        $this->fengmiMsg('创建订单失败！');
    }
    public function buy(){
        if (empty($this->uid)) {
            header('Location: ' . U('passport/login'));
            die;
        }
        $tuan_id = (int) $this->_get('tuan_id');
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->error('该商品不存在');
            die;
        }
        if ($detail['bg_date'] > TODAY) {
            $this->error('该套餐还未开始开抢');
        }
        if ($detail['closed'] == 1 || $detail['end_date'] < TODAY) {
            $this->error('该商品已经结束');
            die;
        }
        //如果没有优惠劵ID就去获取开始
        if(!empty($detail['download_id'])){
            $this->assign('download_id', $detail['download_id']);
        }else{
            $this->assign('coupon', $coupon = D('Coupon')->Obtain_Coupon_tuan($tuan_id,$this->uid));
        }

        $detail = D('Tuan')->_format($detail);
        $this->assign('detail', $detail);
        $this->mobile_title = '支付订单';
        $this->display();
    }
    public function pay(){
        if (empty($this->uid)) {
            header('Location:' . U('passport/login'));
            die;
        }
        $this->check_mobile();
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
        //如果没有优惠劵ID就去获取开始
        if(!empty($order['download_id'])){
            $this->assign('download_id', $order['download_id']);
        }else{
            $this->assign('coupon', $coupon = D('Coupon')->Obtain_Coupon_tuan($order_id,$this->uid));
        }
        //获取优惠劵ID结束
        $this->assign('use_integral', $tuan['use_integral'] * $order['num']);
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->assign('tuan', $tuan);
        $this->assign('order', $order);
        $this->mobile_title = '订单支付';
        $this->display();
    }
    public function tuan_mobile(){
        $this->mobile();
    }
    public function tuan_mobile2(){
        $this->mobile2();
    }
    public function tuan_sendsms(){
        $this->sendsms();
    }
    public function pay2(){
        if (empty($this->uid)) {
            $this->fengmiMsg('登录状态失效!', U('passport/login'));
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Tuanorder')->find($order_id);
        if (empty($order) || (int) $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->fengmiMsg('该订单不存在');
        }
        if (!($code = $this->_post('code'))) {
            $this->fengmiMsg('请选择支付方式！');
        }
//        $mobile = D('Users')->where(array('user_id' => $this->uid))->getField('mobile');
//        if (!$mobile) {
//            $this->fengmiMsg('请先绑定手机号码再提交！');
//        }
        $pay_mode = '在线支付';
        if ($code == 'wait') {
            $pay_mode = '货到支付';
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
                D('Tuan')->updateCount($tuan['tuan_id'], 'sold_num');//更新卖出产品
				D('Sms')->sms_tuan_user($this->uid,$order['order_id']);//团购商品通知用户
                D('Users')->prestige($this->uid, 'tuan');
                D('Sms')->tuanTZshop($tuan['shop_id']);
       			D('Weixintmpl')->weixin_notice_tuan_user($order_id,$this->uid,0);
                $this->fengmiMsg('恭喜您下单成功！', U('user/tuan/index'));
            } else {
                $this->fengmiMsg('您已经设置过该套餐为到店付了！');
            }
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->fengmiMsg('该支付方式不存在');
            }
			$order['need_pay'] = D('Tuanorder')->get_tuan_need_pay($order_id,$this->uid,2);//获取实际支付价格封装
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
            $codestr = join(',', $codes);
            D('Weixintmpl')->weixin_notice_tuan_user($order_id,$this->uid,1);
            $this->fengmiMsg('订单设置完毕，即将进入付款。', U('payment/payment', array('log_id' => $logs['log_id'])));
            die;
        }
    }
    public function delete(){
        $id = (int) $_GET['order_id'];
        if (is_numeric($id) && $id > 0) {
            $map = array('order_id' => $id);
            $findone = D('Tuanorder')->where($map)->find();
            if (!empty($findone)) {
                $res = D('Tuanorder')->delete($id);
                $this->success('删除成功!');
            }
        }
    }
    public function near(){
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $tuans = D('Tuan')->order(" (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ")->where(array('closed' => 0, 'audit' => 1, 'bg_date' => array('ELT', TODAY), 'end_date' => array('EGT', TODAY)))->limit(0, 4)->select();
        foreach ($tuans as $k => $val) {
            $tuans[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $this->assign('tuans', $tuans);
        $this->display();
    }
    public function loadindex(){
        $Tuan = D('Tuan');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('audit' => 1, 'closed' => 0, 'city_id' => $this->city_id, 'end_date' => array('EGT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $cat = (int) $this->_param('cat');
        if ($cat) {
            $catids = D('Tuancate')->getChildren($cat);
            if (!empty($catids)) {
                $map['cate_id'] = array('IN', $catids);
            } else {
                $map['cate_id'] = $cat;
            }
        }
        $area = (int) $this->_param('area');
        if ($area) {
            $map['area_id'] = $area;
        }
        $order = $this->_param('order', 'htmlspecialchars');
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $orderby = array('orderby' => 'asc', 'tuan_id' => 'desc');
        $count = $Tuan->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Tuan->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['end_time'] = strtotime($val['end_date']) - NOW_TIME + 86400;
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $shops = D('Shop')->itemsByIds($shop_ids);
            $ids = array();
            foreach ($shops as $k => $val) {
                $shops[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
                $d = getDistanceNone($lat, $lng, $val['lat'], $val['lng']);
                $ids[$d][] = $k;
            }
            ksort($ids);
            $showshops = array();
            foreach ($ids as $arr1) {
                foreach ($arr1 as $val) {
                    $showshops[$val] = $shops[$val];
                }
            }
            $this->assign('shops', $showshops);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}
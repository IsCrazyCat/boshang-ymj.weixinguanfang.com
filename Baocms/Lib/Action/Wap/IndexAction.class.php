<?php


class IndexAction extends CommonAction {

      public function index() {

        $this->assign('lifecate', D('Lifecate')->fetchAll());
        $this->assign('channel', D('Lifecate')->getChannelMeans());
		
		//获取用户自定坐标
		$lat = cookie('lat_ok');
		$lng = cookie('lng_ok');
		if(empty($lat) || empty($lng)){
			$lat = cookie('lat');
			$lng = cookie('lng');
		}
        if (empty($lat) || empty($lng)) {
            $lat = $this->_CONFIG['site']['lat'];
            $lng = $this->_CONFIG['site']['lng'];
        }
        $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
        $shoplist = D('Shop')->where(array('city_id'=>$this->city_id, 'closed' => 0, 'audit' => 1))->order($orderby)->limit(0, 5)->select();
		foreach ($shoplist as $k => $val) {
            $shoplist[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
		
		
        $news = D('Article')->where(array('city_id'=>$this->city_id, 'closed' => 0, 'audit' => 1))->order(array('create_time' => 'desc'))->limit(0, 5)->select();
		$community = D('Community')->where(array('city_id'=>$this->city_id, 'closed' => 0, 'audit' => 1,))->order($orderby)->limit(0, 5)->select();
		foreach ($community as $k => $val) {
            $community[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
		
        $this->assign('shoplist', $shoplist);
        $this->assign('news', $news);
		$this->assign('community', $community);

		$maps = array('status' => 2,'closed'=>0);
		$this->assign('nav',$nav = D('Navigation') ->where($maps)->order(array('orderby' => 'asc'))->select());
		$bg_time = strtotime(TODAY);
		$this->assign('sign_day', $sign_day = (int) D('Usersign')->where(array('user_id' => $this->uid, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count());
        $this->display();
    }
   

    public function search() {
        $keys = D('Keyword')->fetchAll();
        $keytype = D('Keyword')->getKeyType();
        $this->assign('keys',$keys);
        $this->display();
    }
	
	 public function dingwei() {
        $lat = $this->_get('lat', 'htmlspecialchars');
        $lng = $this->_get('lng', 'htmlspecialchars');
        cookie('lat', $lat);
        cookie('lng', $lng);
        echo NOW_TIME;
    }

	public function more() {
		$maps = array('status' => 2,'closed'=>0);
		$this->assign('nav',$nav = D('Navigation') ->where($maps)->order(array('orderby' => 'asc'))->select());
		$this->display();
	}
	public function ranking(){
//        if (empty($this->uid)) {
//            header('Location: ' . U('passport/login'));
//            die;
//        }
        $profit_users = D('Userprofitlogs')
            ->field('user_id,sum(money) as money')
            ->where(array('is_separate' =>1))
            ->order(array('money'=>'desc'))
            ->group('user_id')
            ->select();
        foreach ($profit_users as $key=>$val){
            $info = D('Users')->find($val['user_id']);
            $profit_users[$key]['info']=$info;
            for ($i = 1;$i <= 3;$i++ ){
                $map = array('closed' => 0, 'fuid' . $i => $val['user_id']);
                $profit_users[$key]['level'.$i]=D('Users')->where($map)->count();
            }
        }
        $first_user = D('Users')->where(array('mobile'=>'15562884131'))->find();
        $first_user['fuid1_count'] = 500 + D('Users')->where(array('closed' => 0, 'fuid1' => $first_user['user_id']))->count();
        $first_user['fuid2_count'] = 600 + D('Users')->where(array('closed' => 0, 'fuid2' => $first_user['user_id']))->count();

        $sencond_user = D('Users')->where(array('mobile'=>'18264582670 '))->find();
        $sencond_user['fuid1_count'] = 500 + D('Users')->where(array('closed' => 0, 'fuid1' => $sencond_user['user_id']))->count();
        $sencond_user['fuid2_count'] = 600 + D('Users')->where(array('closed' => 0, 'fuid2' => $sencond_user['user_id']))->count();

        $third_user = D('Users')->where(array('mobile'=>'15166353000'))->find();
        $third_user['fuid1_count'] = 500 + D('Users')->where(array('closed' => 0, 'fuid1' => $third_user['user_id']))->count();
        $third_user['fuid2_count'] = 600 + D('Users')->where(array('closed' => 0, 'fuid2' => $third_user['user_id']))->count();

        $first_user = D('Users')->where(array('mobile'=>'15562884131'))->find();
        $first_user['fuid1_count'] = 500 + D('Users')->where(array('closed' => 0, 'fuid1' => $first_user['user_id']))->count();
        $first_user['fuid2_count'] = 600 + D('Users')->where(array('closed' => 0, 'fuid2' => $first_user['user_id']))->count();



        $this->assign('users',$profit_users);
        $this->display();
    }
    public function wait(){
        $this->error('积分商城尚未开放，敬请期待！');
        die;
    }
    /**
     * 自动返款
     */
    public function backtask(){
        //查询需要返款的数据， 订单表中is_back状态为1 返款中的订单 中的 订单商品表中 back_status 为1 的数据
        $back_orders = D('Order')->where(array('is_back'=>1))->select();
        foreach ($back_orders as $key=>$order){
            $user_id = $order['user_id'];
            //获取订单下返款中的商品
            $back_goods = D('OrderGoods')->where(array('order_id'=>$order['order_id'],'back_status'=>1))->select();
            if(empty($back_goods)){
                D('Order')->save(array('order_id'=>$order['order_id'],'status'=>8,'is_back'=>2));
            }else{
                $back_money = 0;
                foreach ($back_goods as $gk=>$good){
                    //上次返还的期数 是否等于总期数 等于则修改状态 返还结束
                    if($good['cur_back_count'] == $good['back_count']){
                        D('OrderGoods')->save(array('id'=>$good['id'],'back_status'=>2,'back_end_time'=>NOW_TIME));
                    }else if($good['cur_back_count'] < $good['back_count']){
                        //进行返还
                        $back_money += $good['back_money'];
                        //添加资金记录日志
                        $goods = D('Goods')->find($good['goods_id']);
                        ////添加用户余额变动日志 这里intro
                        D('Usermoneylogs')->add(array(
                            'user_id' => $user_id,
                            'money' => $good['back_money'],
                            'create_time' => NOW_TIME,
                            'create_ip' => get_client_ip(),
                            'intro' => '订单：'.$good['order_id'].'中商品：'.$goods['title'].'第'.($good['cur_back_count']+1).'次返款'
                        ));
                        if(($good['cur_back_count']+1) == $good['back_count']){
                            D('OrderGoods')->save(array('id'=>$good['id'],'back_status'=>2,'back_end_time'=>NOW_TIME,'cur_back_count'=>$good['back_count']));
                        }else{
                            D('OrderGoods')->save(array('id'=>$good['id'],'back_status'=>1,'cur_back_count'=>($good['cur_back_count']+1)));
                        }
                    }
                }
                //更新用户余额
                $result = D('Users')->save(array('user_id'=>$user_id,'money'=>array('exp','money+'.$back_money)));
                //检查该订单下是否都返还完毕
                $back_goods = D('OrderGoods')->where(array('order_id'=>$order['order_id'],'back_status'=>1))->select();
                if(empty($back_goods)) {
                    D('Order')->save(array('order_id' => $order['order_id'], 'status' => 8, 'is_back' => 2));
                }
            }
        }
    }
    public function test(){
         return 1;
    }
}

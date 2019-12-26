<?php
//暂时不做PC，直接扫码进入微信端购买即可
class CrowdAction extends CommonAction {
	
	public function _initialize(){
        parent::_initialize();
		 if ($this->_CONFIG['operation']['crowd'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
		$check_user_addr = D('Paddress')->where(array('user_id'=>$this->uid))->find();
		$this->assign('check_user_addr', $check_user_addr);//检测收货地址
		$this->assign('host', __HOST__);
		
    }

		
	public function index(){
		$Crowd = D('Crowd');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'audit' => 1,'city_id' => $this->city_id);
       $linkArr = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        //搜索二开结束
		$cates = D('Crowdcate')->fetchAll();
        $cat = (int) $this->_param('cat');
        $cate_id = (int) $this->_param('cate_id');
        if ($cat) {
            if (!empty($cate_id)) {
                $map['cate_id'] = $cate_id;
                $this->seodatas['cate_name'] = $cates[$cate_id]['cate_name'];
                $linkArr['cat'] = $cat;
                $linkArr['cate_id'] = $cate_id;
            } else {
                $catids = D('Appointcate')->getChildren($cat);
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
		
		$order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 's':
                $orderby = array('zan_num' => 'asc');
                $linkArr['order'] = $order;
                break;
            case 'p':
                $orderby = array('all_price' => 'asc');
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
                $orderby = array('zan_num' => 'desc', 'goods_id' => 'desc');
                break;
        }
		$this->assign('order', $order);

        $count = $Crowd->where($map)->count(); 
        $Page = new Page($count, 20); 
        $show = $Page->show(); 
        $list = $Crowd->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $v){
			$arr[] = $v['goods_id'];
		}
		
		$selArr = $linkArr;
        foreach ($selArr as $k => $val) {
            if ($k == 'order' || $k == 'new' || $k == 'freebook' || $k == 'hot' || $k == 'tui') {
                unset($selArr[$k]);
            }
        }
		
		
        $this->assign('list', $list); 
		$this->assign('selArr', $selArr);
		$this->assign('cates', $cates);
        $this->assign('page', $show); 
		$this->assign('linkArr', $linkArr);
		$this->display();
	}


	public function detail($goods_id) {
        $goods_id = (int) $goods_id;
		$Crowd = D('Crowd');
		$Crowdproject = D('Crowdproject');
		$Crowdask = D('Crowdask');
		$Crowdlist = D('Crowdlist');
		$Crowdtype = D('Crowdtype');
        if (!$goods = D('Crowd')->find($goods_id)) {
            $this->error('您访问的产品不存在！');
        }
        if ($goods['closed'] != 0 || $goods['audit'] != 1) {
            $this->error('您访问的产品不存在！');
        }
		if (!$crowd = $Crowd->find($goods_id)) {
            $this->error('您访问的产品不存在！');
        }
		$detail = array_merge($goods,$crowd);
        $shop = D('Shop')->find($detail['shop_id']);
        $this->assign('shop', $shop);
      

        //其他众筹
        $maps = array('closed' => 0, 'audit' => 1,'type'=>'crowd', 'city_id' => $this->city_id, 'ltime' => array('GT', time()));
        $list = $Crowd->where($maps)->order(array('views'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $v){
			$arr[] = $v['goods_id'];
		}
		$details['goods_id'] = array('IN',implode(',',$arr));
		$crowd = $Crowd->where($details)->select();
		$like = $Crowd->merge($list,$crowd);
        $this->assign('like', $like);



        $goodsdianping = D('Goodsdianping');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'goods_id' => $goods_id, 'show_date' => array('ELT', TODAY));
        $count = $goodsdianping->where($map)->count(); 
        $Page = new Page($count, 5); 
        $show = $Page->show(); 
        $list = $goodsdianping->where($map)->order(array('dianping_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $dianping_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $dianping_ids[$val['dianping_id']] = $val['dianping_id'];
        }
        
        if (!empty($dianping_ids)) {
            $this->assign('pics', D('Goodsdianpingpics')->where(array('dianping_id' => array('IN', $dianping_ids)))->select());
        }

		//项目进展
		$projectcount = $Crowdproject->where(array('goods_id' =>$goods_id))->count();
		$project = $Crowdproject->where(array('goods_id' =>$goods_id))->order(array('dateline' => 'desc'))->select();
		$this->assign('projectcount', $projectcount);
		$this->assign('project', $project);

		//问题回答
		$askcount = $Crowdask->where(array('goods_id' =>$goods_id))->count();
		$ask_list = $Crowdask->where(array('goods_id' =>$goods_id))->order(array('dateline' => 'desc'))->select();
        $user_idss = array();
		foreach($ask_list as $k => $v){
			$user_idss[$v['uid']] = $v['uid'];
		}
		$this->assign('askcount', $askcount);
		$this->assign('ask_list', $ask_list);
        $this->assign('userss', D('Users')->itemsByIds($user_idss));
		//获取购买列表

		$goodstype = $Crowdtype->where(array('goods_id' =>$goods_id))->order(array('price' => 'asc'))->select();
		$this->assign('goodstype', $goodstype);

		//获取购买记录
		$listcount = $Crowdlist->where(array('goods_id' =>$goods_id))->count();
		$goods_list = $Crowdlist->where(array('goods_id' =>$goods_id))->order(array('dateline' => 'desc'))->select();
		foreach($goods_list as $k => $v){
			$user_ids[$v['uid']] = $v['uid'];
		}

		if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
		
		$this->assign('listcount', $listcount); 
		$this->assign('goods_list', $goods_list); 
		//开始
		$Crowdcate = D('Crowdcate')->fetchAll();
        if ($Crowdcate[$detail['cate_id']]['parent_id'] == 0) {
            $this->assign('catstr', $Crowdcate[$detail['cate_id']]['cate_name']);
        } else {
            $this->assign('catstr', $Crowdcate[$Crowdcate[$detail['cate_id']]['parent_id']]['cate_name']);
            $this->assign('cat', $Crowdcate[$detail['cate_id']]['parent_id']);
            $this->assign('catestr', $Crowdcate[$detail['cate_id']]['cate_name']);
        }
		
		
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->seodatas['title'] = $detail['title'];
        D('Goods')->updateCount($goods_id, 'views');
        $this->assign('detail', $detail);
        $this->display();
    }
	 //众筹支持列表
	public function detail_c($goods_id){
		$Crowd = D('Crowd');
        $map = array('closed' => 0, 'audit' => 1,'city_id' => $this->city_id,'goods_id' => array('NEQ',$goods_id), 'ltime' => array('GT', time()));
        $list = $Crowd->where($map)->order($orderby)->limit('20')->select();
		shuffle($list);$num = 0;//shuffle是什么意思呢
        foreach($list as $k => $v){
			$num++;
			if($num<5){
				$arr[] = $v['goods_id'];
				$arrs[] = $v;
			}
		}
		
		$details['goods_id'] = array('IN',implode(',',$arr));
		$crowd = $Crowd->where($details)->select();
		$items = $Crowd->merge($arrs,$crowd);
        $this->assign('itemss', $items); 
		$this->display();
	}
	//提问
	public function ask($goods_id){
		$Crowdask = D('Crowdask');
		if (empty($this->uid)) {
            $this->ajaxLogin(); 
        }
		$goods_id = (int) $goods_id;
        if (!$detail = D('Crowd')->find($goods_id)) {
            $this->error('该商品不存在');
        }
		$data['uid'] = $this->uid;
		$data['goods_id'] = $goods_id;
		$data['ask_c'] = $this->_post('ask_c', 'htmlspecialchars');
        if(empty($data['ask_c'])){
            $this->baoError("提问问题不能为空");
        }
		$data['dateline'] =	time();
		if ($ask_id = $Crowdask->add($data)) {
			$this->baoMsg('提交成功',U('crowd/detail',array('goods_id'=>$goods_id)));
		}
		
	}
	//众筹收藏
	public function favorites($goods_id){
		$Crowdfollow = D('Crowdfollow');
		$Crowd = D('Crowd');
		if (empty($this->uid)) {
            $this->ajaxLogin(); //提示异步登录
        }
		$goods_id = (int) $goods_id;
        if (!$detail = $Crowd->find($goods_id)) {
            $this->error('该众筹不存在');
        }
		$data['type'] = 'follow';
		$data['goods_id'] = $goods_id;
		$data['uid'] = $this->uid;
		$data['dateline'] =	time();
		$map = array('type'=>'follow','uid'=>$this->uid,'goods_id'=>$goods_id);
		if($Crowdfollow->where($map)->find()){
			$this->baoMsg('您已经关注过了');
		}else if ($ask_id = $Crowdfollow->add($data)) {
			$Crowd->updateCount($goods_id, 'follow_num');
			$this->baoMsg('关注成功',U('crowd/detail',array('goods_id'=>$goods_id)));
		}
	}
	 //众筹点赞
	public function zan($goods_id){
		$Crowdfollow = D('Crowdfollow');
		$Crowd = D('Crowd');
		if (empty($this->uid)) {
            $this->ajaxLogin(); 
        }
		$goods_id = (int) $goods_id;
        if (!$detail = D('Crowd')->find($goods_id)) {
            $this->error('该商品不存在');
        }
		$data['uid'] = $this->uid;
		$data['goods_id'] = $goods_id;
		$data['type'] = 'zan';
		$data['dateline'] =	time();

		$map = array('type'=>'zan','uid'=>$this->uid,'goods_id'=>$goods_id);
		if($Crowdfollow->where($map)->find()){
			$this->baoMsg('您已经赞过了');
		}else if ($ask_id = $Crowdfollow->add($data)) {
			$Crowd->updateCount($goods_id, 'zan_num');
			$this->baoMsg('点赞成功',U('crowd/detail',array('goods_id'=>$goods_id)));
		}
	}
	
	 //众筹举报重写
	public function report($goods_id){
		$Crowd = D('Crowd');
		$Crowdreport = D('Crowdreport');
		if (empty($this->uid)) {
            $this->ajaxLogin(); //提示异步登录
        }
		$data = $this->_post('data');
		$goods_id = (int) $goods_id;
        if (!$detail = $Crowd->find($goods_id)) {
            $this->baoMsg('该众筹不存在');
        }
		if(!$data['name']){
			$this->baoMsg('姓名不能为空');
		}
		if(!$data['mobile']){
			$this->baoMsg('手机号码不能为空');
		}
		if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->baoMsg('联系电话格式不正确');
        }
		if(!$data['contents']){
			$this->baoMsg('内容不能为空');
		}
		$data['uid'] = $this->uid;
		$data['goods_id'] = $goods_id;
		$data['status'] = 0;
		$data['create_time'] =	time();
		$data['create_ip'] = get_client_ip();
		$map = array('uid'=>$this->uid,'goods_id'=>$goods_id);
		
		if($Crowdreport->where($map)->find()){
			$this->baoMsg('您已经举报过了');
		}else if ($report_id = $Crowdreport->add($data)) {
			$this->baoMsg('举恭喜您举报成功',U('crowd/detail',array('goods_id'=>$goods_id)));
		}

	}
	
	 //众筹证明重写
	public function prove($goods_id){
		$Crowd = D('Crowd');
		$Crowdprove = D('Crowdprove');
		if (empty($this->uid)) {
            $this->ajaxLogin(); //提示异步登录
        }
		$goods_id = (int) $goods_id;
        if (!$detail = $Crowd->find($goods_id)) {
            $this->baoMsg('该众筹不存在');
        }
		if ($this->isPost()) {
            $data = $this->proveCheck();
			$data['goods_id'] = $goods_id;
            $obj = D('Crowdprove');
			if($Crowdprove = $obj->where(array('title'=>$data['title'],'user_id'=>$data['user_id']))->select()){
				 $this->baoMsg('不能重复证明');
			}
            if ($obj->add($data)) {
                $this->baoMsg('恭喜您证明成功',U('crowd/detail',array('goods_id'=>$goods_id)));
            }
            $this->baoMsg('操作失败！');
        } else {
			$types = $Crowdprove->type();
			$this->assign('types', $types);
            $this->assign('detail', $detail);
            $this->display();
        }
	}
	//证明检测
	 public function proveCheck() {
        $data = $this->checkFields($this->_post('data', false), array('user_id','city_id','type_id','contents', 'photo'));
        $data['user_id'] = $this->uid;
        $data['city_id'] = $this->city_id;
        $data['type_id'] = (int) $data['type_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoMsg('请上传证明图片');
        }
        if (!isImage($data['photo'])) {
            $this->baoMsg('证明图不正确');
        }
        $data['status'] = 0;
		$data['create_time'] =	time();
		$data['create_ip'] = get_client_ip();
        return $data;
    }
	

   //购买
  public function buy(){
        if (!$this->uid) {
            $this->ajaxLogin();
        }
		$goods_id = (int) $this->_param('goods_id');
		$type_id = (int) $this->_param('type_id');
        if (!$detail = D('Crowd')->find($goods_id)) {
            $this->baoError('该众筹不存在');
        }
        if ($detail['closed'] == 1 || $detail['ltime'] < TODAY) {
            $this->baoError('该众筹已经结束');
        }
		$Crowdtype = D('Crowdtype')->crowd_type_need_pay($goods_id,$type_id);
		if(empty($Crowdtype)){
			$this->baoError('获取众筹信息有误，请刷新页面后稍后再试试');
		}
		$address_id = D('Crowdorder')->check_user_address_id($goods_id,$this->uid);//检测用户有无默认收货地址
		if (empty($address_id)) {
			$this->baoError('获取用户地址错误');
		}
		$local = D('Crowdorder')->getCode();
        $data = array(
				'goods_id' => $goods_id, 
				'address_id' => $address_id, 
				'type_id' => $type_id, 
				'user_id' => $this->uid, 
				'uid' => $detail['user_id'], //发起人ID
				'price' => $Crowdtype['price'], //支持价格
				'yunfei' => $Crowdtype['yunfei'], //运费
				'need_pay' => $Crowdtype['need_pay'], //价格
				'code'=> $local, //众筹劵
				'create_time' => NOW_TIME, 
				'create_ip' => get_client_ip(), 
				'status' => 0
	   );
       if ($order_id = D('Crowdorder')->add($data)) {
		   if(!empty($address_id)){
			   $this->baoSuccess('恭喜您众筹成功，正在为您跳转！', U('crowd/pay', array('order_id' => $order_id,'address_id'=>$address_id)));
		   }else{
			   $this->baoSuccess('您暂无收货地址！,正在为您跳', U('members/malladdress/create', array('order_id' => $order_id,'type'=>crowd)));   
		   }
       }
       $this->baoError('创建订单失败！');
       
    }
	//众筹直接支付页面
	 public function pay(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Crowdorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
		$address_id = (int) $this->_param('address_id');
		$addrs = D('Crowdorder')->addrs_address($order_id,$this->uid);//获取用户收货地址
        $this->assign('useraddr', $addrs);
        $provinceList = D('Paddlist')->where(array('level' => 1))->select();//全部省份
        $this->assign('provinceList', $provinceList);
        $this->assign('order', $order);
		$this->assign('type', crowd);
        $this->assign('payment', D('Payment')->getPayments());
        $this->display();
    }
	//去付款
	 public function pay2(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
		$id = (int) $this->_post('id');
		if (empty($id)) {
            $this->baoError('请您选择收货地址');
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Crowdorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->baoError('该订单不存在');
            die;
        }
        D('Crowdorder')->save(array('order_id' => $order_id,'address_id' =>$id));//更新收货地址
        if (!($code = $this->_post('code'))) {
            $this->baoError('请选择支付方式！');
        }
        $uaddr = D('Paddress')->where(array('id' => $order['address_id']))->find();
        if ($code == 'wait') {
             $this->baoError('暂不支持货到付款，请重新选择支付方式');
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->baoError('该支付方式不存在，请稍后再试试');
            }
            $logs = D('Paymentlogs')->getLogsByOrderId('crowd', $order_id);//查找日志
			$need_pay = $order['need_pay'];//再更新防止篡改支付日志
            if (empty($logs)) {//独家再更新
                $logs = array(
					'type' => 'crowd', 
					'user_id' => $this->uid, 
					'order_id' => $order_id, 
					'code' => $code, 
					'need_pay' => $need_pay, 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'is_paid' => 0
				);
                $logs['log_id'] = D('Paymentlogs')->add($logs);
            } else {
                $logs['need_pay'] = $need_pay;
                $logs['code'] = $code;
                D('Paymentlogs')->save($logs);
            }
			if (false == D('Crowd')->add_crowd_list($order_id,$this->uid)) {//后期下单后更新，更新购买列表信息
				$this->baoError('更新购买信息出错');
			}else{
				$this->baoJump(U('payment/payment', array('log_id' => $logs['log_id'])));
			}
            
        }
    }
	
}
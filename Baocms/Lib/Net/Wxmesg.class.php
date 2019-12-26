<?php 
class Wxmesg{
	/**
	 * 网络发送数据
	 * @param string $uid,用户的openid
	 * @param string $serial,模板编号
	 * @param array  $data ,填充模板数据
	 */
	static public function net($uid,$serial=null,$data=null)
	{
        $uid=(int)$uid;
//		if(!$uid) throw new Exception("Uid参数不正确！");

		$openid = D('Connect')->where("type='weixin'")->getFieldByUid($uid,'open_id'); 

		if($openid){
			if(!$serial)     throw new Exception("模板编号参数不正确！", 1000);
			if(empty($data)) throw new Exception("没有数据可供发送！");
            $data['template_id'] = D('Weixintmpl')->getFieldBySerial($serial,'template_id');//支付成功模板
            $data['touser']  = $openid;
            return D('Weixin')->tmplmesg($data);
		}
		return false;
	}
	/**
	 * 下单成功模板
	 */
	static public function order($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！",1001);
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=>	$data['first'],    'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['orderNum'], 'color'=>'#000000'), //订单号
				'keyword2'=>array('value'=> $data['goodsName'],'color'=>'#000000'), //商品名称
				'keyword3'=>array('value'=> $data['buyNum'],   'color'=>'#000000'), //订购数量
				'keyword4'=>array('value'=> $data['money'],    'color'=>'#000000'), //订单金额
				'keyword5'=>array('value'=> $data['payType'],  'color'=>'#000000'), //付款方式
				'remark'  =>array('value'=> $data['remark'],   'color'=>'#000000')
			)
		);
	}
	//支付成功调用全局通用，小灰灰修改
	static public function pay($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！",1002);
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=>$data['first'],   'color'=>'#000000'),
				'keyword1'=>array('value'=>$data['accountType'],   'color'=>'#000000'), //会员账户
				'keyword2'=>array('value'=>$data['operateType'],'color'=>'#000000'), //费用类型
				'keyword3'=>array('value'=>$data['operateInfo'],    'color'=>'#000000'), //消费类型
				'keyword4'=>array('value'=>$data['price'],'color'=>'#000000'), //变动金额
				'remark'  =>array('value'=>$data['balance'],  'color'=>'#000000')//会员账户余额
			)
		);
	}
	
	//会员提现余额变动全部封装
	static public function cash($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！",1002);
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=>$data['first'],   'color'=>'#000000'),
				'keyword1'=>array('value'=>$data['balance'],   'color'=>'#000000'), //余额
				'keyword2'=>array('value'=>$data['time'],'color'=>'#000000'), //时间
				'remark'  =>array('value'=>$data['remark'],  'color'=>'#000000')
			)
		);
	}
	//订单取消
	static public function cancle($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=>$data['first'],              'color'=>'#000000'),
				'orderProductPrice'=>array('value'=>$data['money'],     'color'=>'#000000'),  //订单金额
				'orderProductName' =>array('value'=>$data['orderInfo'], 'color'=>'#000000'), //商品详情
				'orderAddress'     =>array('value'=>$data['addr'],      'color'=>'#000000'), //收货地址
				'orderName'        =>array('value'=>$data['orderNum'],  'color'=>'#000000'), //订单编号
				'remark'           =>array('value'=>$data['remark'],    'color'=>'#000000')
			)
		);
	}
	//商家确认
	static public function sure($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=> $data['first'],     'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['orderNum'],  'color'=>'#000000'), //订单编号
				'keyword2'=>array('value'=> $data['money'],     'color'=>'#000000'), //订单金额
				'keyword3'=>array('value'=> $data['orderDate'], 'color'=>'#000000'), //订单时间
				'remark'  =>array('value'=> $data['remark'],    'color'=>'#000000')
			)
		);
	}
	//已发货
	static public function deliver($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=> $data['first'],     'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['orderInfo'], 'color'=>'#000000'), //订单内容
				'keyword2'=>array('value'=> $data['wuliu'],     'color'=>'#000000'), //物流服务
				'keyword3'=>array('value'=> $data['wuliuNum'],  'color'=>'#000000'), //快递单号
				'keyword4'=>array('value'=> $data['addr'],      'color'=>'#000000'), //收货信息
				'remark'  =>array('value'=> $data['remark'],    'color'=>'#000000')
			)
		);
	}
	//确认收货
	static public function take($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=> $data['first'],    'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['orderNum'], 'color'=>'#000000'), //订单号
				'keyword2'=>array('value'=> $data['goodsName'],'color'=>'#000000'), //商品名称
				'keyword3'=>array('value'=> $data['orderDate'],'color'=>'#000000'), //下单时间
				'keyword4'=>array('value'=> $data['sendDate'], 'color'=>'#000000'), //发货时间
				'keyword5'=>array('value'=> $data['sureDate'], 'color'=>'#000000'), //收货时间
				'remark'  =>array('value'=> $data['remark'],   'color'=>'#000000')
			)
		);
	}
	//余额变动
	static public function balance($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['accountType'], 'color'=>'#000000'), //账户类型
				'keyword2'=>array('value'=> $data['operateType'], 'color'=>'#000000'), //操作类型
				'keyword3'=>array('value'=> $data['operateInfo'], 'color'=>'#000000'), //操作内容
				'keyword4'=>array('value'=> $data['limit'],       'color'=>'#000000'), //变动额度
				'keyword5'=>array('value'=> $data['balance'],     'color'=>'#000000'), //账户余额
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}
	/*订单通知模板*/
	static public function notice($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'         => '',
			'url'            => $data['url'],
			'template_id'    => '',
			'topcolor'       => '#000000',
			'data'           => array(
				'first'   => array( 'value'=> $data['first'],  'color'=>'#000000' ),
				'keyword1'=> array( 'value'=> $data['order'],  'color'=>'#000000' ),//订单号
				'keyword2'=> array( 'value'=> $data['amount'], 'color'=>'#000000' ),//订单金额
				'keyword3'=> array( 'value'=> $data['info'],   'color'=>'#000000' ),//商品信息
				'remark'  => array( 'value'=> $data['remark'], 'color'=>'#000000' )
			)
		);
	}
	
	//下单成功模板重写，小灰灰重新编写，模板IDOPENTM202297555
	static public function place_an_order($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'         => '',
			'url'            => $data['url'],
			'template_id'    => '',
			'topcolor'       => '#000000',
			'data'           => array(
				'first'   => array( 'value'=> $data['first'],  'color'=>'#000000' ),
				'keyword1'=> array( 'value'=> $data['order_id'],  'color'=>'#000000' ),//订单号
				'keyword2'=> array( 'value'=> $data['title'], 'color'=>'#000000' ),//商品名称
				'keyword3'=> array( 'value'=> $data['num'],   'color'=>'#000000' ),//订购数量
				'keyword4'=> array( 'value'=> $data['price'],   'color'=>'#000000' ),//订单总额
				'keyword5'=> array( 'value'=> $data['pay_type'],   'color'=>'#000000' ),//付款方式
				'remark'  => array( 'value'=> $data['remark'], 'color'=>'#000000' )
			)
		);
	}
	
	//用户付款后订单通知商家
	static public function order_notice_shop($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'         => '',
			'url'            => $data['url'],
			'template_id'    => '',
			'topcolor'       => '#000000',
			'data'           => array(
				'first'   => array( 'value'=> $data['first'],  'color'=>'#000000' ),
				'keyword1'=> array( 'value'=> $data['order_id'],  'color'=>'#000000' ),
				'keyword2'=> array( 'value'=> $data['order_goods'], 'color'=>'#000000' ),
				'keyword3'=> array( 'value'=> $data['order_price'],   'color'=>'#000000' ),
				'keyword4'=> array( 'value'=> $data['order_ways'],   'color'=>'#000000' ),
				'keyword5'=> array( 'value'=> $data['order_user_information'],   'color'=>'#000000' ),
				'remark'  => array( 'value'=> $data['remark'], 'color'=>'#000000' )
			)
		);
	}
	
	//客户预约成功通知商家

	static public function yuyue($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！",1001);
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=>	$data['first'],    'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['remark'], 'color'=>'#000000'), //订单号
				'keyword2'=>array('value'=> $data['name'],'color'=>'#000000'), //预约人名字
				'keyword3'=>array('value'=> $data['date'],   'color'=>'#000000'), //时间
				'keyword4'=>array('value'=> $data['tel'],    'color'=>'#000000'), //电话
				'keyword5'=>array('value'=> $data['contents'],  'color'=>'#000000') //内容
			)
		);
	}
	
	
	//开团提醒二开到下面结束
	static public function kaituan($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['goodsName'], 'color'=>'#000000'), //商品名称
				'keyword2'=>array('value'=> $data['orderno'], 'color'=>'#000000'), //订单编号
				'keyword3'=>array('value'=> $data['pintuannum'], 'color'=>'#000000'), //拼团人数
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}

	//参团成功通知
	static public function cantuan($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['payprice'], 'color'=>'#000000'), //订单金额
				'keyword2'=>array('value'=> $data['goodsName'], 'color'=>'#000000'), //商品名称
				'keyword3'=>array('value'=> $data['dizhi'], 'color'=>'#000000'), //收货地址
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}

	//用户拼团成功通知
	static public function ctsuccess($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['payprice'], 'color'=>'#000000'), //商品名称
				'keyword2'=>array('value'=> $data['orderno'], 'color'=>'#000000'), //订单编号
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}

	//拼团失败通知
	static public function ctover($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['payprice'], 'color'=>'#000000'), //订单金额
				'keyword2'=>array('value'=> $data['goodsName'], 'color'=>'#000000'), //商品名称
				'keyword3'=>array('value'=> $data['orderno'], 'color'=>'#000000'), //订单编号
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}
	//拼团退款通知
	static public function cttuikuan($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['yuanyin'], 'color'=>'#000000'), //退款原因
				'keyword2'=>array('value'=> $data['payprice'], 'color'=>'#000000'), //退款金额
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}
	//拼团发货通知
	static public function fahuo($data=null){
		if(empty($data)) throw new Exception("微信模板消息没有数据！");
		return array(
			'touser'       => '',
			'url'          => $data['url'],
			'template_id'  => '',
			'topcolor'     => $data['topcolor'],
			'data'		   => array(
				'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['goodsName'], 'color'=>'#000000'), //商品名称
				'keyword2'=>array('value'=> $data['kuaidi'], 'color'=>'#000000'), //快递名称
				'keyword3'=>array('value'=> $data['kuaididanhao'], 'color'=>'#000000'), //快递名称
				'keyword4'=>array('value'=> $data['dizhi'],       'color'=>'#000000'), //收货地址
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}	
	//物业
        static public function wuyetz($data=null){
                if(empty($data)) throw new Exception("微信模板消息没有数据！");
                return array(
                        'touser'       => '',
                        'url'          => $data['url'],
                        'template_id'  => '',
                        'topcolor'     => $data['topcolor'],
                        'data'                   => array(
                                'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
                                'keyword1'=>array('value'=> $data['nickname'], 'color'=>'#000000'), //账户类型
                                'keyword2'=>array('value'=> $data['title'], 'color'=>'#000000'), //操作类型
                                'keyword3'=>array('value'=> $data['nowtime'], 'color'=>'#000000'), //操作类型
                                'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
                        )
                );
        }
        //物业通知
        static public function wuyexttz($data=null){
                if(empty($data)) throw new Exception("微信模板消息没有数据！");
                return array(
                        'touser'       => '',
                        'url'          => $data['url'],
                        'template_id'  => '',
                        'topcolor'     => $data['topcolor'],
                        'data'                   => array(
                                'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
                                'keyword1'=>array('value'=> $data['nickname'], 'color'=>'#000000'), //账户
                                'keyword2'=>array('value'=> $data['title'], 'color'=>'#000000'), //操作类型
                                'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
                        )
                );
        }	
		//全局推送到配送员
        static public function delivery_tz_user($data=null){
                if(empty($data)) throw new Exception("微信模板消息没有数据！");
                return array(
                        'touser'       => '',
                        'url'          => $data['url'],
                        'template_id'  => '',
                        'topcolor'     => $data['topcolor'],
                        'data'         => array(
                        'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
                        'keyword1'=>array('value'=> $data['nickname'], 'color'=>'#000000'), //账户
                        'keyword2'=>array('value'=> $data['title'], 'color'=>'#000000'), //操作类型
                        'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
                      )
                );
        }	
		//后台全局推送
        static public function tuisongweixin($data=null){
                if(empty($data)) throw new Exception("微信模板消息没有数据！");
                return array(
                        'touser'       => '',
                        'url'          => $data['url'],
                        'template_id'  => '',
                        'topcolor'     => $data['topcolor'],
                        'data'                   => array(
                                'first'   =>array('value'=> $data['first'],    'color'=>'#000000'),
                                'keyword1'=>array('value'=> $data['nickname'], 'color'=>'#000000'), //账户
                                'keyword2'=>array('value'=> $data['title'], 'color'=>'#000000'), //操作类型
                                'remark'  =>array('value'=> $data['remark'], 'color'=>'#000000')
                        )
                );
        }											
	
}
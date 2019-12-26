<?php
class PaymentModel extends CommonModel {
   protected $pk = 'payment_id';
    protected $tableName = 'payment';
    protected $token = 'payment';
    protected $types = array(
        'goods' => '商城购物',
        'appoint' => '家政购买',
        'tuan' => '生活购物',
        'money' => '余额充值',
        'ele' => '在线订餐',
        'booking'  => '订座定金',
        'fzmoney'=> '冻结金充值',
        'breaks'=>'优惠买单',
		'pintuan' => '拼团',//拼团添加
		'crowd' =>'众筹',
		'donate' =>'打赏',
		'running'=>'跑腿',
		'farm'=>'农家乐预订',
		'cloud'=>'云购',
        'gwmd'=>'门店付款'
    );
    protected $type = null;
    protected $log_id = null;
    public function getType() {
        return $this->type;
    }

    public function getLogId() {
        return $this->log_id;
    }

    public function getTypes() {
        return $this->types;
    }

    public function getPayments($mobile = false) {
        $datas = $this->fetchAll();
        $return = array();
        foreach ($datas as $val) {
            if ($val['is_open']) {
                if ($mobile == false) {
                    if (!$val['is_mobile_only'])
                        $return[$val['code']] = $val;
                }else {
                   if ( $val['code'] != 'tenpay' && $val['code'] != 'native' && $val['code'] != 'micro' ) {
                            $return[$val['code']] = $val;
                      }
                }
            }
        }

        if (!is_weixin()) {
            unset($return['weixin']);
        }

        if (is_weixin()) {
            unset($return['alipay']);
        }
        return $return;
    }
	//外卖关闭在线支付
	 public function getPayments_delivery($mobile = false) {
        $datas = $this->fetchAll();
        $return = array();
        foreach ($datas as $val) {
            if ($val['is_open']) {
                if ($mobile == false) {
                    if (!$val['is_mobile_only'])
                        $return[$val['code']] = $val;
                }else {
                    if ($val['code'] != 'tenpay') {
                        $return[$val['code']] = $val;
                    }
                }
            }
        }
        unset($return['money']);
		unset($return['tenpay']);
		unset($return['native']);
        unset($return['weixin']);
        unset($return['alipay']);
        return $return;
    }
	
	//订座关闭WAP扫码支付
	 public function getPayments_booking($mobile = false) {
        $datas = $this->fetchAll();
        $return = array();
        foreach ($datas as $val) {
            if ($val['is_open']) {
                if ($mobile == false) {
                    if (!$val['is_mobile_only'])
                        $return[$val['code']] = $val;
                }else {
                    if ($val['code'] != 'tenpay') {
                        $return[$val['code']] = $val;
                    }
                }
            }
        }
        if (!is_weixin()) {
            unset($return['weixin']);
			unset($return['native']);
        }

        if (is_weixin()) {
            unset($return['alipay']);
			unset($return['native']);
        }
        return $return;
    }
	
	//跑腿直接只能在线支付
	 public function getPayments_running($mobile = false) {
        $datas = $this->fetchAll();
        $return = array();
        foreach ($datas as $val) {
            if ($val['is_open']) {
                if ($mobile == false) {
                    if (!$val['is_mobile_only'])
                        $return[$val['code']] = $val;
                }else {
                    if ($val['code'] != 'tenpay') {
                        $return[$val['code']] = $val;
                    }
                }
            }
        }
        if (!is_weixin()) {
            unset($return['weixin']);
			unset($return['native']);
			//unset($return['money']);
        }

        if (is_weixin()) {
            unset($return['alipay']);
			unset($return['native']);
			//unset($return['money']);
        }
        return $return;
    }
    
    public function _format($data) {
        $data['setting'] = unserialize($data['setting']);
        return $data;
    }		
	public function respond($code) {
        $payment = $this->checkPayment($code);
        if (empty($payment))
            return false;
		
		if ( $code == 'native' || $code == 'micro' ) {
			  require_cache( APP_PATH . 'Lib/Payment/' . $code . '.weixin' . '.class.php' );//扫码支付
		}elseif (defined('IN_MOBILE')) {
            require_cache(APP_PATH . 'Lib/Payment/' . $code . '.mobile.class.php');
        } else {
            require_cache(APP_PATH . 'Lib/Payment/' . $code . '.class.php');
        }
        $obj = new $code();
        return $obj->respond();
    }
		
	public function getCode($logs) {
        $CONFIG = D('Setting')->fetchAll();
        $datas = array(
            'subject' => $CONFIG['site']['sitename'] . $this->types[$logs['type']],
            'logs_id' => $logs['log_id'],
            'logs_amount' => $logs['need_pay'] / 100,
        );
        $payment = $this->getPayment($logs['code']);
		if ( $logs['code'] == 'native' || $logs['code'] == 'micro' ) {
			 require_cache( APP_PATH . 'Lib/Payment/' . $logs['code'] . '.weixin' . '.class.php' );//扫码支付
		} elseif (defined('IN_MOBILE')) {
            require_cache(APP_PATH . 'Lib/Payment/' . $logs['code'] . '.mobile.class.php');
        } else {
            require_cache(APP_PATH . 'Lib/Payment/' . $logs['code'] . '.class.php');

        }
        $obj = new $logs['code']();
        return $obj->getCode($datas, $payment);

    }	

    public function checkMoney($logs_id, $money) {
        $money = (int) ($money );
        $logs = D('Paymentlogs')->find($logs_id);
        if ($logs['need_pay'] == $money)
            return true;
        return false;

    }
	
    public function logsPaid($logs_id) {
        $this->log_id = $logs_id; //用于外层回调
        $logs = D('Paymentlogs')->find($logs_id);
        if (!empty($logs) && !$logs['is_paid']) {
            $data = array('log_id' => $logs_id,'is_paid' => 1,);
            if (D('Paymentlogs')->save($data)) { //总之 先更新 然后再处理逻辑  这里保障并发是安全的
                $ip = get_client_ip();
                D('Paymentlogs')->save(array('log_id' => $logs_id,'pay_time' => NOW_TIME,'pay_ip' => $ip));//更新付款时间
                $this->type = $logs['type'];
                if ($logs['type'] == 'appoint') {//家政购买
					D('Appointorder') -> save(array('order_id' => $logs['order_id'], 'status' => 1, 'pay_time' => NOW_TIME,));//家政改变订单状态
					D('Appointorder')->appoint_order_print($logs['order_id']);//家政打印万能接口
					D('Sms') -> sms_appoint_TZ_user($logs['order_id']);//家政短信通知用户
					D('Sms') -> sms_appoint_TZ_shop($logs['order_id']);//家政短信通知商家
                    return true;
                }elseif($logs['type'] == 'fzmoney'){//冻结金充值，我这用不上
                    $CONFIG = D('Setting')->fetchAll();
                    D('Usersex')->save(array(
						'user_id'=>$logs['user_id'],
						'frozen_money'=>$logs['need_pay'],
						'frozen_date'=>NOW_TIME + $CONFIG['quanming']['money_day']*86400)
					);
                    D('Quanming')->fzmoney($logs['user_id']);
                    return true;
                }elseif($logs['type'] == 'breaks'){   //优惠买单
                    $order = D('Breaksorder')->find($logs['order_id']);
                    $shop = D('Shop')->find($order['shop_id']);
                    D('Users')->updateCount($shop['user_id'], 'money', $logs['need_pay']);
					
					D('Breaksorder') -> settlement($logs['order_id']);//去执行逻辑,这里可能有问题，不返回什么直接执行已付款后处理资金
					
                    $youhui = D('Shopyouhui')->where(array('shop_id'=>$order['shop_id']))->find();
                    D('Breaksorder')->save(array('order_id' => $logs['order_id'], 'status' => 1)); //设置已付款
                    D('Shopyouhui')->updateCount($youhui['yh_id'], 'use_count',1);
					D('Sms') -> breaksTZshop($order['order_id']);//发送短信给商家
					D('Sms') -> breaksTZuser($order['order_id']);//发送短信给用户
					return true;

				} elseif ($logs['type'] == 'money') {
					D('Users') -> updateCount($logs['user_id'], 'money', $logs['need_pay']);
					D('Users')->Recharge_Full_Gvie_User_Money($logs['user_id'], $logs['need_pay']);//充值满送，忽略错误
					D('Users')->return_recharge_integral($logs_id,$logs['user_id'], $logs['need_pay']);//充值余额送积分，忽略错误
					D('Usermoneylogs') -> add(array(
						'user_id' => $logs['user_id'], 
						'money' => $logs['need_pay'], 
						'create_time' => NOW_TIME, 
						'create_ip' => $ip, 
						'intro' => '余额充值
						，支付记录ID：' . $logs['log_id'], 
					));
					return true;

                } elseif ($logs['type'] == 'tuan') {//套餐都是发送套餐码！
                    $member = D('Users') -> find($logs['user_id']);
					$codes = array();
					$obj = D('Tuancode');
					$order = D('Tuanorder') -> find($logs['order_id']);
					$tuan = D('Tuan') -> find($order['tuan_id']);
					//结束
					for ($i = 0; $i < $order['num']; $i++) {
						$local = $obj -> getCode();
						$insert = array(
							'user_id' => $logs['user_id'], 
							'shop_id' => $tuan['shop_id'], 
							'order_id' => $order['order_id'], 
							'tuan_id' => $order['tuan_id'], 
							'code' => $local, 
							'price' => $tuan['price'], 
							'real_money' => (int)($order['need_pay'] / $order['num']), //退款的时候用
							'real_integral' => (int)($order['use_integral'] / $order['num']), //退款的时候用
							'fail_date' => $tuan['fail_date'], 
							'settlement_price' => $tuan['settlement_price'], 
							'create_time' => NOW_TIME, 
							'create_ip' => $ip, 
						);
						$codes[] = $local;
						$obj -> add($insert);

					}
					D('Tuanorder') -> save(array('order_id' => $order['order_id'], 'status' => 1));//设置已付款
					D('Sms') -> sms_tuan_user($member['user_id'],$order['order_id']);//团购商品通知用户
					D('Tuan') -> updateCount($tuan['tuan_id'], 'sold_num');//更新卖出产品
					D('Tuan') -> updateCount($tuan['tuan_id'], 'num', -$order['num']);
					D('Sms') -> tuanTZshop($tuan['shop_id']);//发送短信通知商家
					D('Users') -> prestige($member['user_id'], 'tuan');
					
					$tg = D('Users') -> checkInvite($order['user_id'], $tuan['price']);
					if ($tg !== false) {
						D('Users') -> addIntegral($tg['uid'], $tg['integral'], "分享获得积分！");
					}
					D('Tongji') -> log(1, $logs['need_pay']);//统计//分销
					$tuan_is_profit = D('Shop') -> find($order['shop_id']);
					if($tuan_is_profit['is_profit'] == 1){
						D('Userprofitlogs')->profitFusers(0, $logs['user_id'], $logs['order_id']);//单个套餐奖励分成和升级等级
					}
                    D('Coupon')->change_download_id_is_used($logs['order_id']);//如果有优惠劵就修改优惠劵的状态
					return true;
                } elseif ($logs['type'] == 'ele') {//餐饮订餐
                    D('Eleorder') -> save(array('order_id' => $logs['order_id'], 'status' => 1, 'is_pay' => 1));
					$order = D('EleOrder') -> where('order_id =' . $logs['order_id']) -> find();
					$member = D('Users') -> find($logs['user_id']);
					$shops = D('Shop') -> find($order['shop_id']);
					D('Eleorder')->ele_month_num($logs['order_id']);//更新外卖销量
					D('Eleorder') -> ele_delivery_order($logs['order_id'],0);//外卖配送接口
					D('Tongji') -> log(3, $logs['need_pay']);//统计
					D('Sms') -> eleTZshop($logs['order_id']);//通知商家
					D('Eleorder')->combination_ele_print($logs['order_id'],$order['addr_id']);//外卖打印万能接口
					return true;
                }elseif($logs['type'] == 'hotel'){   //酒店预订
                    $order = D('Hotelorder')->find($logs['order_id']);
                    $room = D('Hotelroom')->find($order['room_id']);
                    $hotel = D('Hotel')->find($order['hotel_id']);
                    $shop = D('Shop')->find($hotel['shop_id']);
                    D('Hotelorder')->save(array('order_id' => $logs['order_id'], 'order_status' => 1)); //设置已付款
					D('Sms')->sms_hotel_user($logs['order_id']);//短信通知用户
					D('Sms')->sms_hotel_shop($logs['order_id']);//短信通知酒店商家
                    return true;
                } elseif ($logs['type'] == 'crowd') {//众筹
                    D('Crowdorder')->save(array('order_id' => $logs['order_id'],'status' => 1 ));
					D('Sms')->sms_crowd_user($logs['order_id']);//短信通知会员
					D('Sms')->sms_crowd_uid($logs['order_id']);//通知众筹发起人
					return true;
                } elseif ($logs['type'] == 'farm'){   //农家乐预订
                    $order = D('FarmOrder')->find($logs['order_id']);
                    $f = D('FarmPackage')->find($order['pid']);
                    $farm = D('Farm')->find($order['farm_id']);
                    $shop = D('Shop')->find($farm['shop_id']);
                    D('FarmOrder')->save(array('order_id' => $logs['order_id'], 'order_status' => 1)); //设置已付款
                    return true;
                }  elseif ($logs['type'] == 'booking') {//订座定金
                    D('Bookingorder')->save(array('order_id' => $logs['order_id'],'order_status' => 1 ));
	                D('Tongji')->log(3, $logs['need_pay']);
					D('Sms')->sms_booking_user($logs['order_id']);
					D('Sms')->sms_booking_shop($logs['order_id']);
					return true;
                }  elseif ($logs['type'] == 'running') {//跑腿
                    D('Running')->save(array('running_id' => $logs['order_id'],'status' => 1 ));
					D('Sms')->sms_running_user($logs['order_id']);
					D('Sms')->sms_delivery_user($logs['order_id'],2);
					return true;
                } elseif ($logs['type'] == 'cloud') {//元购
                    D('Cloudgoods')->save_cloud_logs_status($logs['order_id']);
					return true;
                }elseif ($logs['type'] == 'pintuan') {//拼团二开开始
					$obj = D('Porder');
					$tuan = $obj -> find($logs['order_id']);
					$uid = $tuan['user_id'];
					if ($tuan['tstatus'] == 0) {
						$obj -> save(array(
							'id' => $logs['order_id'], 
							'pay_time' => NOW_TIME, 
							'order_no' => $logs_id, 
							'pay_name' => $logs['code'], 
							'tuan_status' => 3, 
							'order_status' => 2
						));
						include_once "Baocms/Lib/Net/Wxmesg.class.php";
						$_data_order = array(
							'url' => "http://" . $_SERVER['HTTP_HOST'] . "/user/pintuan/order/id/" . $logs['order_id'] . ".html", 
							'topcolor' => '#F55555', 
							'first' => '亲,您的直接购买订单已经创建，我们将尽快为您发货！', 
							'remark' => '更多信息,请登录http://' . $_SERVER['HTTP_HOST'] . '！再次感谢您的惠顾！', 
							'money' => round($logs['need_pay'] / 100, 2) . '元', 
							'goodsName' => $tuan['goods_name'], 
							'payType' => $logs['code'], 
							'orderNum' => $logs_id, 
							'buyNum' => '点击查看详单', 
						);
						$order_data = Wxmesg::order($_data_order);
						$return = Wxmesg::net($uid, 'OPENTM202297555', $order_data);
					} else {
						$obj -> save(array(
							'id' => $logs['order_id'], 
							'pay_time' => NOW_TIME, 
							'order_no' => $logs_id, 
							'pay_name' => $logs['code'], 
							'tuan_status' => 2, 
							'order_status' => 2
						));
						D('Ptuan') -> save(array('id' => $tuan['tuan_id'], 'tuan_status' => 2));
						D('Ptuanteam') -> where(array('order_id' => $logs['order_id'])) -> setField('tuan_status', '2');
						if ($tuan['tstatus'] == 1) {
							$num = $tuan['renshu'] - 1;
							include_once "Baocms/Lib/Net/Wxmesg.class.php";
							$_data_kaituan = array(
								'url' => "http://" . $_SERVER['HTTP_HOST'] . "/wap/pintuan/tuan/id/" . $tuan['tuan_id'] . ".html", 
								'topcolor' => '#F55555', 'first' => '亲,您成功开启了一个新的拼团', 
								'remark' => '还差' . $num . '人成团，快快邀请您的小伙伴们一起参团吧', 
								'goodsName' => $tuan['goods_name'], 'orderno' => $logs_id, 
								'pintuannum' => $tuan['renshu'],
							 );
							$kaituan_data = Wxmesg::kaituan($_data_kaituan);
							$return = Wxmesg::net($uid, 'OPENTM206953801', $kaituan_data);
						} elseif ($tuan['tstatus'] == 2) {
							include_once "Baocms/Lib/Net/Wxmesg.class.php";
							$_data_cantuan = array(
								'url' => "http://" . $_SERVER['HTTP_HOST'] . "/wap/pintuan/tuan/id/" . $tuan['tuan_id'] . ".html", 
								'topcolor' => '#F55555', 'first' => '您已参团成功，请等待成团', 
								'payprice' => round($logs['need_pay'] / 100, 2) . '元', 
								'goodsName' => $tuan['goods_name'], 
								'dizhi' => $tuan['address'], 
								'remark' => '更多详情可点击查看',
							 );
							$cantuan_data = Wxmesg::cantuan($_data_cantuan);
							$return = Wxmesg::net($uid, 'OPENTM400890529', $cantuan_data);
						}
					}
					$pgoods = D('Pgoods');
					$pgoods -> where(array('id' => $tuan['goods_id'])) -> setDec('xiangou_num', $tuan['goods_num']);//更新减掉库存
					$pgoods -> where(array('id' => $tuan['goods_id'])) -> setInc('sales_num', $tuan['goods_num']);//更新销售数量
					$tuanrenCount = D('Ptuanteam') -> where(array('tuan_id' => $tuan['tuan_id'], 'tuan_status' => 2)) -> count();//拼团二开结束
					return true;
				} elseif ($logs['type'] == 'gwmd') {
                    $ip = get_client_ip();

                    if ( (int)$logs['shop_id'] > 0 ) {

                        $users = D('users')->find($logs['user_id']);
                        $shop = D('shop')->find($logs['shop_id']);

                        if ( $logs['need_pay'] > 0 ) {
                            D('Usermoneylogs') -> add(array(
                                'user_id' => $logs['user_id'],
                                'money' => 0-$logs['need_pay'],
                                'create_time' => NOW_TIME,
                                'create_ip' => $ip,
                                'intro' => '到店付款 会员支付 支付ID：' . $logs['log_id'],
                                'shop_id' => $logs['shop_id'],
                                'pay_id' => $logs['log_id'],
                            ));
                        }

                        $need_pay = $logs['need_pay'];

                        if ( (int)$shop['user_id']> 0 ) {
                            $shopusers = D('users')->find($shop['user_id']);
                        }
                        /* 在用户店铺付款后 商家微信模板消息通知 开始 */

                        /*
                            {{first.DATA}}
                            订单编号：{{keyword1.DATA}}
                            下单时间：{{keyword2.DATA}}
                            {{remark.DATA}}
                        */
                        if ( (int)$shop['user_id']> 0 ) {
                            $shopusers = D('users')->find($shop['user_id']);
                        }
                        if ((int)$shopusers['user_id'] > 0) {
                            include_once "Baocms/Lib/Net/Wxmesg.class.php";
                            $_data_order = array(
                                'url' => "http://" . $_SERVER['HTTP_HOST'] . "/wap/payment/yes/log_id/" . $logs['log_id'] . ".html",
                                'topcolor' => '#F55555',
                                'first' => '门店：'.$shop['shop_name'],
                                'remark' => '收款：'. round($need_pay / 100, 2) . ' 元 ',
                                'keyword1' => $logs['log_id'].' 金额：'.round($need_pay / 100, 2) . ' 元',//消费金额
                                'keyword2' => date("Y-m-d H:i:s",NOW_TIME),//消费时间
                            );
                            $order_data = Wxmesg::ddfkorder($_data_order);
                            $return = Wxmesg::net((int)$shop['user_id'], 'OPENTM203940481', $order_data);
                        }
                        /* 在用户店铺付款后 商家微信模板消息通知 结束 */
                    }

                }else { // 商城购物
                    if (empty($logs['order_id']) && !empty($logs['order_ids'])) {//合并付款
                        $order_ids = explode(',', $logs['order_ids']);
						$goods_order_profit = D('Order')->where(array('order_id'=>$logs['order_id']))->find();
						$goods_is_profit = D('Shop') -> find($goods_order_profit['shop_id']);
						if($goods_is_profit['is_profit'] == 1){
							foreach ($order_ids as $order_id) {
								D('Userprofitlogs')->profitFusers(1, $logs['user_id'], $order_id);//三级分销循环分成
							}
						}
                        D('Order')->save(array('status' => 1), array('where' => array('order_id' => array('IN', $order_ids))));
                        D('Sms')->mallTZshop($order_ids); //通知商家
                        D('Order')->mallSold($order_ids);//更新销售接口
                        D('Order')->mallPeisong(array($order_ids),0);//更新配送接口
						D('Order')->combination_goods_print($order_ids);//万能商城订单打印
						
                    } else {
                        D('Order')->save(array('order_id' => $logs['order_id'],'status' => 1));
                        D('Order')->mallPeisong(array($logs['order_id']),0);//更新配送接口
                        D('Order')->mallSold($logs['order_id']);//更新销售接口
                        D('Sms')->mallTZshop($logs['order_id']);//通知商家
						D('Coupon')->change_download_id_is_used($logs['order_id']);//如果有优惠劵就修改优惠劵的状态，合并付款暂时不做
						$goods_order = D('Order')->where(array('order_id'=>$logs['order_id']))->find();
						$goods_order_shop = D('Shop') -> find($goods_order['shop_id']);
						if($goods_order_shop['is_profit'] == 1){
							D('Userprofitlogs')->profitFusers(1, $logs['user_id'], $logs['order_id']);//单个商品奖励分成和升级等级
						}
						D('Order')->combination_goods_print($logs['order_id']);//万能商城订单打印
                    }
                    D('Tongji')->log(2, $logs['need_pay']); //统计
                }
				D('Weixintmpl')->weixin_pay_balance_user($logs['log_id']);//会员账户余额变动通知全局
				D('Weixintmpl')->weixin_pay_balance_shop($logs['log_id'],1);//用户付款后微信通知商家
            }
        return true;
      }
   }
    public function checkPayment($code) {
        $datas = $this->fetchAll();
        foreach ($datas as $val) {
            if ($val['code'] == $code)
                return $val;
        }
        return array();
    }
    public function getPayment($code) {
        $datas = $this->fetchAll();
        foreach ($datas as $val) {
            if ($val['code'] == $code)
                return $val['setting'];
        }
        return array();
    }
}


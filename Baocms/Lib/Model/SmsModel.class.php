<?php
class SmsModel extends CommonModel{
    protected $pk = 'sms_id';
    protected $tableName = 'sms';
    protected $token = 'bao_sms';
	
    public function sendSms($code, $mobile, $data){
        $tmpl = $this->fetchAll();
        if (!empty($tmpl[$code]['is_open'])) {
            $content = $tmpl[$code]['sms_tmpl'];
            $config = D('Setting')->fetchAll();
            $data['sitename'] = $config['site']['sitename'];
            $data['tel'] = $config['site']['tel'];
            foreach ($data as $k => $val) {
                $val = str_replace('【', '', $val);
                $val = str_replace('】', '', $val);
                $content = str_replace('{' . $k . '}', $val, $content);
            }
            if (is_array($mobile)) {
                $mobile = join(',', $mobile);
            }
            if ($config['sms']['charset']) {
                $content = auto_charset($content, 'UTF8', 'gbk');
            }
			$sms_id = $this->sms_bao_add($mobile,$content);
            $local = array('mobile' => $mobile, 'content' => $content);
            $http = tmplToStr($config['sms']['url'], $local);
			D('Smsbao')->where(array('sms_id' => $sms_id))->save(array('status' => $res));
            $res = file_get_contents($http);
            if ($res == $config['sms']['code']) {
                return true;
            }
        }
        return false;
    }

    public function DySms($sign, $code, $mobile, $data) {
        $config = D('Setting')->fetchAll();
        $dycode = D('Dayu')->where(array("dayu_local='{$code}'"))->find();
        if (!empty($dycode['is_open'])) {
			$sms_id = $this->sms_dayu_add($sign, $code, $mobile,$data,$dycode['dayu_note']);
            import('ORG.Util.Dayu');
            $obj = new AliSms($config['sms']['dykey'], $config['sms']['dysecret']);
            if ($obj->sign($sign)->data($data)->sms_id($sms_id)->code($dycode['dayu_tag'])->send($mobile)) {
                return true;
            }
        }
        return false;
    }
	public function sms_dayu_add($sign, $code, $mobile,$data,$dayu_note){
		foreach ($data as $k => $val) {
			$content = str_replace('${' . $k . '}', $val, $dayu_note);
			$dayu_note = $content;
		}
		$sms_data = array();
		$sms_data['sign'] = $sign.'-'.time();
		$sms_data['code'] = $code;
		$sms_data['mobile'] = $mobile;
		$sms_data['content'] = $content;
		$sms_data['create_time'] = time();
		$sms_data['create_ip'] = get_client_ip();
		if ($sms_id = D('Dayusms')->add($sms_data)) {
            return $sms_id;
        }
		return true;
	}
	public function sms_bao_add($mobile,$content){
		$sms_data = array();
		$sms_data['mobile'] = $mobile;
		$sms_data['content'] = $content;
		$sms_data['create_time'] = time();
		$sms_data['create_ip'] = get_client_ip();
		if ($sms_id = D('Smsbao')->add($sms_data)) {
            return $sms_id;
        }
		return true;
	}
    public function mallTZshop($order_id) {
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $order_id = array($order_id);
        }
        $config = D('Setting')->fetchAll();
        $orders = D('Order')->itemsByIds($order_id);
        $shop = array();
        foreach ($orders as $val) {
            $shop[$val['shop_id']] = $val['shop_id'];
        }
        $shops = D('Shop')->itemsByIds($shop);
        foreach ($shops as $val) {
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'sms_mall_tz_shop',$val['mobile'], array(
					'sitename' => $config['site']['sitename']
				));
            } else {
                $this->sendSms('sms_shop_mall', $val['mobile'], array());
            }
        }
        return true;
    }
	//用户下载优惠劵通知用户手机
	public function coupon_download_user($download_id,$uid){
		 $Coupondownload = D('Coupondownload')->find($download_id);
		 $Coupon = D('Coupon')->find($Coupondownload['coupon_id']);
		 $user = D('Users')->find($uid);
		 $config = D('Setting')->fetchAll();
		 //如果有手机号
		 if(!empty($user['mobile'])){
			if($config['sms']['dxapi'] == 'dy'){
                D('Sms')->DySms($config['site']['sitename'], 'coupon_download_user',$user['mobile'], array(
                    'coupon_title' => $Coupon['title'],
                    'code' => $Coupondownload['code'],
                    'expire_date' => $Coupon['expire_date']
                ));
            }else{
                D('Sms')->sendSms('coupon_download_user',$user['mobile'], array(
                    'coupon_title' => $Coupon['title'],
                    'code' => $Coupondownload['code'],
                    'expire_date' => $Coupon['expire_date'],
                ));
            }
		 }else{
			return false; 
		}
		 return true;
	}	
		
	//商城退款短信通知
	public function goods_refund_user($order_id){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $order = D('Order')->find($order_id);
            $config = D('Setting')->fetchAll();
            $user = D('Users')->find($order['user_id']);
			$t = time();
            $date = date('Y-m-d H:i:s ', $t);
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'goods_refund_user',$user['mobile'], array(
					'need_pay' => round($order['need_pay']/100,2),//退款金额
					'order_id' => $order['order_id'],//订单ID
				));
            } else {
                $this->sendSms('goods_refund_user', $user['mobile'], array(
					'need_pay' => round($order['need_pay']/100,2),//退款金额
					'order_id' => $order['order_id'],//订单ID
				));
            }
        }
        return true;
    }
	
	//外卖退款短信通知用户
	public function eleorder_refund_user($order_id){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $ele_order = D('Eleorder')->find($order_id);
            $config = D('Setting')->fetchAll();
            $user = D('Users')->find($ele_order['user_id']);
			$t = time();
            $date = date('Y-m-d H:i:s ', $t);
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'eleorder_refund_user',$user['mobile'], array(
					'need_pay' => round($ele_order['need_pay']/100,2),//退款金额
					'order_id' => $order_id
				));
            } else {
                $this->sendSms('eleorder_refund_user', $user['mobile'], array(
					'need_pay' => round($ele_order['need_pay']/100,2),//退款金额
					'order_id' => $order_id
				));
            }
        }
        return true;
    }
	
	//套餐劵退款短信通知
	public function tuancode_refund_user($code_id){
        	$code_id = (int) $code_id;
            $tuancode = D('Tuancode')->find($code_id);
            $config = D('Setting')->fetchAll();
            $user = D('Users')->find($Tuancode['user_id']);
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'tuancode_refund_user',$user['mobile'], array(
					'real_money' => round($tuancode['real_money']/100,2),//退款金额
					'order_id' => $code_id,//订单ID
				));
            } else {
                $this->sendSms('tuancode_refund_user', $user['mobile'], array(
					'real_money' => round($tuancode['real_money']/100,2),//退款金额
					'order_id' => $code_id,//订单ID
				));
            }
        return true;
    }
	
	
	
	//优惠劵万能通知接口1,1是用户下载优惠劵，2代表用户会员中心再次请求优惠劵，这个不行了，大于规则更改了报废！！！
	public function sms_coupon_user($download_id,$type){
            $Coupondownload = D('Coupondownload')->find($download_id);
			$users = D('Users')->find($Coupondownload['user_id']);
			$Coupon = D('Coupon')->find($Coupondownload['coupon_id']);
			$config = D('Setting')->fetchAll();
			
			if($type ==1){
				$info = '您成功下载'.$Coupon['title'].'优惠劵，验证码：'.$Coupondownload['code'].'过期时间：'.$Coupon['expire_date'];
			}elseif($type ==2){
				$info = '您的优惠劵'.$Coupon['title'].'验证码：'.$Coupondownload['code'].'过期时间：'.$Coupon['expire_date'];
			}
			
            if ($config['sms']['dxapi'] == 'dy') {
                    D('Sms')->DySms($config['site']['sitename'], 'sms_coupon_user', $users['mobile'], array(
						'sitename' => $config['site']['sitename'], 
						'user_name' => $users['nickname'], 
						'info' => $info
					));
              } else {
                    D('Sms')->sendSms('sms_coupon_user', $users['mobile'], array(
						'user_name' => $users['nickname'], 
						'info' => $info
					));
              }
        return true;
    }
	
	//优惠劵赠送万能接口，分有会员账户跟没有会员账户，这个已不行了，大于规则修改了
	public function register_account_give_coupon($download_id,$give_user_id){
            $Coupondownload = D('Coupondownload')->find($download_id);
			$users = D('Users')->find($uid);//新用户账户
			$give_user = D('Users')->find($give_user_id);//原始账户
			$Coupon = D('Coupon')->find($Coupondownload['coupon_id']);
			$config = D('Setting')->fetchAll();
			
			$info = '您的朋友'.$give_user['nickname'].'赠送了您一张优惠劵,账户：'.$users['mobile'].'登录密码'.$users['mobile'].'登录地址：'.$config['site']['host'];
			
            if ($config['sms']['dxapi'] == 'dy') {
                    D('Sms')->DySms($config['site']['sitename'], 'register_account_give_coupon', $users['mobile'], array(
						'sitename' => $config['site']['sitename'], 
						'user_name' => $users['nickname'], 
						'info' => $info
					));
              } else {
                    D('Sms')->sendSms('register_account_give_coupon', $users['mobile'], array(
						'user_name' => $users['nickname'], 
						'info' => $info
					));
              }
        return true;
    }
	
    public function eleTZshop($order_id){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $order = D('Eleorder')->find($order_id);
            $config = D('Setting')->fetchAll();
            $shop = D('Shop')->find($order['shop_id']);
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'sms_ele_tz_shop',$shop['mobile'], array(
					'sitename' => $config['site']['sitename'], 
					'sitename' => $config['site']['sitename']
				));
            } else {
                $this->sendSms('sms_shop_ele', $shop['mobile'], array());
            }
        }
        return true;
    }
	
	public function breaksTZshop($order_id){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $order = D('Breaksorder')->find($order_id);
            $config = D('Setting')->fetchAll();
            $shop = D('Shop')->find($order['shop_id']);
			$users = D('Users')->find($order['user_id']);
			if(!empty($users['nickname'])){
				$user_name = $users['nickname'];
			}else{
				$user_name = $users['account'];
			}
			if(!empty($shop['mobile'])){
				if ($config['sms']['dxapi'] == 'dy') {
					$this->DySms($config['site']['sitename'], 'sms_breaks_tz_shop',$shop['mobile'], array(
						'shop_name' => $shop['shop_name'], //商家名字
						'user_name' => $user_name, //会员名字
						'amount' => $order['amount'], //买单金额
						'money' => $order['need_pay']//实际付款
					));
				} else {
					$this->sendSms('sms_breaks_tz_shop', $shop['mobile'], array(
						'shop_name' => $shop['shop_name'], //商家名字
						'user_name' => $user_name, //会员名字
						'amount' => $order['amount'], //买单金额
						'money' => $order['need_pay']//实际付款
					));
				}
			}
        }
        return true;
    }
	
	
	public function breaksTZuser($order_id){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $order = D('Breaksorder')->find($order_id);
            $config = D('Setting')->fetchAll();
            $users = D('Users')->find($order['user_id']);
			if(!empty($users['nickname'])){
				$user_name = $users['nickname'];
			}else{
				$user_name = $users['account'];
			}
			$shop = D('Shop')->find($order['shop_id']);
			$t = time();
            $date = date('Y-m-d H:i:s ', $t);
			if(!empty($users['mobile'])){
				if ($config['sms']['dxapi'] == 'dy') {
					$this->DySms($config['site']['sitename'], 'sms_breaks_tz_user',$users['mobile'], array(
						'user_name' => $user_name, //会员名字
						'shop_name' => $shop['shop_name'], //商家名字
						'money' => $order['need_pay'], //实付金额
						'data' => $date, //买单时间
					));
				} else {
					$this->sendSms('sms_breaks_tz_user', $user['mobile'], array(
						'user_name' => $user_name, //会员名字
						'shop_name' => $shop['shop_name'], //商家名字
						'money' => $order['need_pay'], //实付金额
						'data' => $date, //买单时间
					));
				}
			}
        }
        return true;
    }
	
	//商家套餐劵验证成功后发送消息到用户手机
    public function tuan_TZ_user($code_id){
        if (is_numeric($code_id) && ($code_id = (int) $code_id)) {
            $tuancode = D('Tuancode')->find($code_id);
            $config = D('Setting')->fetchAll();
            $user = D('Users')->find($tuancode['user_id']);
            //用户手机号
            $tuan = D('Tuan')->find($tuancode['tuan_id']);
            $t = time();
            $date = date('Y-m-d H:i:s ', $t);
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'],'tuan_TZ_user',$user['mobile'], array(
					'name' => $tuan['title'], 
					'data' => $date, 
					'tel' => $config['site']['tel']
				));
            } else {
                $this->sendSms('tuan_TZ_user',$user['mobile'], array());
            }
        }
        return true;
    }
	//发送团购劵到用户手机
	 public function sms_tuan_user($uid,$order_id){
		$user = D('Users')->find($uid);
		$config = D('Setting')->fetchAll();
		$order = D('Tuancode')->where(array('order_id'=>$order_id))->select();
		foreach($order as $v){
			$code[] =  $v['code'];
		}
		$tuan_id = $order[0]['tuan_id'];
		$count = $order = D('Tuancode')->where(array('order_id'=>$order_id))->count();//统计
		if($count == 1){
			$tuan = D('Tuan')->where(array('tuan_id'=>$tuan_id))->find();
			$tuan_title = $tuan['title'];
		}else{
			$tuan_title = '套餐列表';
		}
		$codestr = join(',', $code);
        //发送团购劵
        if ($config['sms']['dxapi'] == 'dy') {
           D('Sms')->DySms($config['site']['sitename'], 'sms_tuan_user',$user['mobile'], array(
				'code' => $codestr, 
				'user' => $user['nickname'], 
				'shop_name' => $tuan_title
			));
        }else{
           D('Sms')->sendSms('sms_tuan', $user['mobile'], array(
				'code' => $codestr, 
				'nickname' => $user['nickname'], 
				'tuan' => $tuan_title
			));
        }
		return true;
	}			
				
   
	//团购通知商家
    public function tuanTZshop($shop_id){
        $shop_id = (int) $shop_id;
        $shop = D('Shop')->find($shop_id);
        $config = D('Setting')->fetchAll();
        if ($config['sms']['dxapi'] == 'dy') {
            $this->DySms($config['site']['sitename'], 'sms_tuan_tz_shop',$shop['mobile'], array('sitename' => $config['site']['sitename']));
        } else {
            $this->sendSms('sms_shop_tuan', $shop['mobile'], array());
        }
        return true;
    }
	
	//酒店通知用户
	public function sms_hotel_user($order_id){
        	$order = D('Hotelorder')->find($logs['order_id']);
            $room = D('Hotelroom')->find($order['room_id']);
            $hotel = D('Hotel')->find($order['hotel_id']);
            $shop = D('Shop')->find($hotel['shop_id']);
			$config = D('Setting')->fetchAll();
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'sms_hotel_user',$order['mobile'], array(
					    'hotel_name' => $hotel['hotel_name'],//酒店名字
                        'tel' => $hotel['tel'],//电话
                        'stime' => $order['stime'],//入驻时间			
					));
            } else {
                $this->sendSms('sms_hotel_user', $order['mobile'], array(
					    'hotel_name' => $hotel['hotel_name'],
                        'tel' => $hotel['tel'],
                        'stime' => $order['stime'],	
				));
            }
        return true;
    }
	
	//酒店通知商家
	public function sms_hotel_shop($order_id){
        	$order = D('Hotelorder')->find($logs['order_id']);
            $room = D('Hotelroom')->find($order['room_id']);
            $hotel = D('Hotel')->find($order['hotel_id']);
            $shop = D('Shop')->find($hotel['shop_id']);
			$config = D('Setting')->fetchAll();
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'sms_hotel_shop',$shop['mobile'], array(
					    'shop_name' => $shop['hotel_name'],//酒店名字
                        'title' => $room['title'],//包房名字				
					));
            } else {
                $this->sendSms('sms_hotel_shop', $order['mobile'], array(
					    'shop_name' => $shop['hotel_name'],//酒店名字
                        'title' => $room['title'],//包房名字	
				));
            }
        return true;
    }
	
	
	//预订通知会员
	public function sms_booking_user($order_id){
		    $order = D('Bookingorder')->find($logs['order_id']);
            $booking = D('Booking')->find($order['shop_id']);//这里是预订里面填写的手机
			$config = D('Setting')->fetchAll();
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'sms_booking_user',$order['mobile'], array(
					    'booking_name' => $booking['shop_name'],//预订名字
					));
            } else {
                $this->sendSms('sms_booking_user', $order['mobile'], array(
					   'booking_name' => $booking['shop_name'],//预订名字
				));
            }
        return true;
    }
	//预订通知商家
	public function sms_booking_shop($order_id){
		    $order = D('Bookingorder')->find($logs['order_id']);
            $booking = D('Booking')->find($order['shop_id']);//这里是预订里面填写的手机
			$config = D('Setting')->fetchAll();
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'sms_booking_shop',$booking['mobile'], array(
					    'booking_name' => $booking['shop_name'],//预订名字
					));
            } else {
                $this->sendSms('sms_booking_shop', $booking['mobile'], array(
					    'booking_name' => $booking['shop_name'],//预订名字
				));
            }
        return true;
    }
	
	
	//众筹通知用户
	public function sms_crowd_user($order_id){
        	$order = D('Crowdorder')->find($logs['order_id']);
            $Crowd = D('Crowd')->find($order['goods_id']);
			$users = D('Users')->find($order['user_id']);
			$config = D('Setting')->fetchAll();
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'sms_crowd_user',$users['mobile'], array(
						'user_name' => $users['nickname'],//买家姓名
					    'title' => $Crowd['title'],//众筹名字
					));
            } else {
                $this->sendSms('sms_crowd_user', $users['mobile'], array(
					    'user_name' => $users['nickname'],//买家姓名
					    'title' => $Crowd['title'],//众筹名字
				));
            }
        return true;
    }
	
	//众筹通知发起人
	public function sms_crowd_uid($order_id){
        	$order = D('Crowdorder')->find($logs['order_id']);
            $Crowd = D('Crowd')->find($order['goods_id']);
			$users = D('Users')->find($order['uid']);
			$config = D('Setting')->fetchAll();
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'sms_crowd_uid',$users['mobile'], array(
						'user_name' => $users['nickname'],//发起人姓名
					    'title' => $Crowd['title'],//众筹名字
					));
            } else {
                $this->sendSms('sms_crowd_uid', $users['mobile'], array(
					    'user_name' => $users['nickname'],//发起人姓名
					    'title' => $Crowd['title'],//众筹名字
				));
            }
        return true;
    }
	
	//家政预约成功再通知用户
	public function sms_appoint_TZ_user($order_id){
		$order = D('Appointorder')->find($order_id);
        $Appoint = D('Appoint')->find($order['appoint_id']);
		$users = D('Users')->find($order['user_id']);
		$config = D('Setting')->fetchAll();
		if($config['sms']['dxapi'] == 'dy'){
                D('Sms')->DySms($config['site']['sitename'], 'sms_appoint_TZ_user', $users['mobile'], array(
			 	    'sitename'=>$config['site']['sitename'], 
                    'appoint_name' => $appoint['title'], 
					'time' => $order['svctime'], 
					'addr' => $order['addr'], 
                ));
            }else{
                D('Sms')->sendSms('sms_appoint_TZ_user', $users['mobile'], array(
                    'appoint_name' => $appoint['title'], 
					'time' => $order['svctime'], 
					'addr' => $order['addr'], 
                ));
            }	
		 return true;
	}
	
	//家政预约成功再通知商家
	public function sms_appoint_TZ_shop($order_id){
		$order = D('Appointorder')->find($order_id);
		$appoint = D('Appoint')->find($order['appoint_id']);
		$shop = D('Shop')->find($order['shop_id']);
		$config = D('Setting')->fetchAll();
		if($config['sms']['dxapi'] == 'dy'){
                D('Sms')->DySms($config['site']['sitename'], 'sms_appoint_TZ_shop', $shop['mobile'], array(
                    'shop_name' => $shop['shop_name'], 
					'appoint_name' => $appoint['title'], 
					'time' => $order['svctime'], 
					'addr' => $order['addr']
                ));
            }else{
                D('Sms')->sendSms('sms_appoint_TZ_shop', $shop['mobile'], array(
                    'shop_name' => $shop['shop_name'], 
					'appoint_name' => $appoint['title'], 
					'time' => $order['svctime'], 
					'addr' => $order['addr'] 
                ));
            }	
		 return true;
	}
	
	
	//家政退款通知用户手机
	public function sms_appoint_refund_user($order_id){
		$order = D('Appointorder')->find($order_id);
        $Appoint = D('Appoint')->find($order['appoint_id']);//众筹类目
		$users = D('Users')->find($order['user_id']);
		$config = D('Setting')->fetchAll();
		if($config['sms']['dxapi'] == 'dy'){
                D('Sms')->DySms($config['site']['sitename'], 'sms_appoint_refund_user', $users['mobile'], array(
			 	    'sitename'=>$config['site']['sitename'], 
                    'user_name' => $users['nickname'], 
					'refund_money' => round($order['need_pay']/100,2), 
					'order_id' => $order['order_id'], 
                ));
            }else{
                D('Sms')->sendSms('sms_appoint_refund_user', $users['mobile'], array(
                    'user_name' => $users['nickname'], 
					'refund_money' => round($order['need_pay']/100,2), 
					'order_id' => $order['order_id'], 
                ));
            }	
		 return true;
	}
	
	//跑腿发布成功后通知用户
	public function sms_running_user($running_id){
		$running= D('Running')->find($running_id);
		$users = D('Users')->find($running['user_id']);
		$config = D('Setting')->fetchAll();
		$t = time();
        $date = date('Y-m-d H:i:s ', $t);
		if($config['sms']['dxapi'] == 'dy'){
                D('Sms')->DySms($config['site']['sitename'], 'sms_running_user', $users['mobile'], array(
			 	    'sitename'=>$config['site']['sitename'], 
                    'user_name' => $users['nickname'], //用户名称
					'need_pay' => round($running['need_pay']/100,2), //付款金额
					'running_id' =>$running_id,//跑腿ID
					'time' => $date 
                ));
            }else{
                D('Sms')->sendSms('sms_running_user', $users['mobile'], array(
                    'user_name' => $users['nickname'], //用户名称
					'need_pay' => round($running['need_pay']/100,2), //付款金额
					'running_id' =>$running_id,//跑腿ID
					'time' => $date 
                ));
            }	
		 return true;
	}
	
	//配送员接单通知用户
	public function sms_Running_Delivery_User($running_id){
		$running= D('Running')->find($running_id);
		$users = D('Users')->find($running['user_id']);
		$delivery = D('Delivery')->find($running['cid']);
		$config = D('Setting')->fetchAll();
		if(!empty($running)){
			if($running['status'] == 2){
				$info = '您的跑腿订单ID：'.$running_id.'已被配送员'.$delivery['name'].'接单，手机：'.$delivery['mobile']; 
			}elseif($running['status'] == 3){
				$info = '您的跑腿订单ID：'.$running_id.'已完成配送'; 	
			}else{
				 return true;	
			}
		}else{
			return false;	
		}
		if(!empty($delivery)){
			if($config['sms']['dxapi'] == 'dy'){
			   D('Sms')->DySms($config['site']['sitename'], 'sms_running_delivery_user', $users['mobile'], array(
					'sitename'=>$config['site']['sitename'], 
					'user_name' => $users['nickname'], 
					'info' => $info, 
			   ));
			}else{
				D('Sms')->sendSms('sms_running_delivery_user', $users['mobile'], array(
					'user_name' => $users['nickname'], 
					'info' => $info, 
				));
			}
		}else{
			return true;
			//发短信暂时忽略错误return false;	
		}
		 return true;
	}
	


	//批量推送给配送员
	public function sms_delivery_user($order_id,$type){
		$type = (int) $type;//0是商城，1是外卖，2跑腿
		if($type == 0){
			$obj = D('Order');
			$info = '商城订单';
		}elseif($type == 1){
			$obj = D('Eleorder');
			$info = '外卖订单';
		}else{
			$obj = D('Running');
			$info = '跑腿';
		}
		$t = time();
        $date = date('m-d H:i', $t);
		$Delivery = D('Delivery')->where(array('is_sms'=>1))->field('mobile')-> select();
		$config = D('Setting')->fetchAll();
		foreach($Delivery as $value){
			if($config['sms']['dxapi'] == 'dy'){
                D('Sms')->DySms($config['site']['sitename'], 'sms_delivery_user', $value['mobile'], array(
			 	    'sitename'=>$config['site']['sitename'], 
                    'info' => $info, 
					'data' => $date 
                ));
            }else{
                D('Sms')->sendSms('sms_delivery_user', $value['mobile'], array(
                    'info' => $info, 
					'data' => $date
                ));
            }	
		}
		 return true;
	}		
			
	//云购中奖通知用户
	public function sms_cloud_win_user($goods_id,$user_id,$number){
        	$Cloudgoods = D('Cloudgoods')->find($goods_id);
            $Users = D('Users')->find($user_id);
			$config = D('Setting')->fetchAll();
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'sms_cloud_win_user',$Users['mobile'], array(
					    'title' => $Cloudgoods['title'],
                        'user_name' => $Users['nickname'],
                        'number' => $number,		
					));
            } else {
                $this->sendSms('sms_cloud_win_user', $Users['mobile'], array(
					    'title' => $Cloudgoods['title'],
                        'user_name' => $Users['nickname'],
                        'number' => $number,	
				));
            }
        return true;
    }		
	
			
	//云购中奖通知商家
	public function sms_cloud_win_shop($goods_id,$number){
        	$Cloudgoods = D('Cloudgoods')->find($goods_id);
            $Shop = D('Shop')->find($Cloudgoods['shop_id']);
			$config = D('Setting')->fetchAll();
            if ($config['sms']['dxapi'] == 'dy') {
                $this->DySms($config['site']['sitename'], 'sms_cloud_win_shop',$Shop['mobile'], array(
					    'title' => $Cloudgoods['title'],
                        'shop_name' => $Shop ['shop_name'],
                        'number' => $number,		
					));
            } else {
                $this->sendSms('sms_cloud_win_shop', $Shop['mobile'], array(
					    'title' => $Cloudgoods['title'],
                        'shop_name' => $Shop ['shop_name'],
                        'number' => $number,	
				));
            }
        return true;
    }		
	
    public function fetchAll(){
        $cache = cache(array('type' => 'File', 'expire' => $this->cacheTime));
        if (!($data = $cache->get($this->token))) {
            $result = $this->order($this->orderby)->select();
            $data = array();
            foreach ($result as $row) {
                $data[$row['sms_key']] = $row;
            }
            $cache->set($this->token, $data);
        }
        return $data;
    }
}
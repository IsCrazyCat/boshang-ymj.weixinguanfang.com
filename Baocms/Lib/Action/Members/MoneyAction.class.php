<?php
class MoneyAction extends CommonAction{
    public function money(){
        //余额充值
        $this->assign('payment', D('Payment')->getPayments());
        $this->display();
    }
    //积分兑换余额
      public function exchange(){
        if($this->isPost()){
			$config = D('Setting')->fetchAll();
			$integral_buy = $config['integral']['buy'];
			//判断积分设置是否合法
			if (false == D('Users')->check_integral_buy($integral_buy)) {
				$this->baoError('网站后台积分设置不合法，请联系管理员');
			}
			
            $exchange = (int)$this->_post('exchange');
			if($exchange <=0){
                $this->baoError('要兑换的数量不能为空！');
            }
			$scale  = D('Users')->obtain_integral_scale($integral_buy);//获取积分比例便于同步
			
			//批量检测积分兑换余额批量代码封装
			if (!D('Users')->check_integral_exchange_legitimate($exchange,$scale)) {
				$this->baoError(D('Users')->getError(), 3000, true);	  
			}
	
            if($this->member['integral'] < $exchange){
                $this->baoError('账户积分不足');
            }
			$actual_integral = $exchange*$scale;
			$money = $actual_integral - intval(($actual_integral*$config['integral']['integral_exchange_tax'])/100);
			if($money > 0){
				if(D('Users')->addMoney($this->uid,$money,'积分兑换现金')){
					D('Users')->addIntegral($this->uid,-$exchange,'扣除兑换余额使用积分');          
				} 
			}
            $this->baoSuccess('您成功兑换余额'.round($money/100,2).'元',U('logs/moneylogs')); 
        }else{
             $this->display();
        }
    }
    public function moneypay(){
        //后期优化
        $money = (int) ($this->_post('money') * 100);
        $code = $this->_post('code', 'htmlspecialchars');
        if ($money <= 0) {
            $this->error('请填写正确的充值金额！');
        }
        $payment = D('Payment')->checkPayment($code);
        if (empty($payment)) {
            $this->error('该支付方式不存在');
        }
        $logs = array(
			'user_id' => $this->uid, 
			'type' => 'money', 
			'code' => $code, 
			'order_id' => 0, 
			'need_pay' => $money, 
			'create_time' => NOW_TIME, 
			'create_ip' => get_client_ip()
		);
        $logs['log_id'] = D('Paymentlogs')->add($logs);
        $this->assign('button', D('Payment')->getCode($logs));
		$this->assign('paytype', D('Payment')->getPayments());
        $this->assign('money', $money);
		$this->assign('log_id', $logs['log_id']);
        $this->display();
    }
    public function recharge(){
        //代金券充值
        if ($this->isPost()) {
            $card_key = $this->_post('card_key', htmlspecialchars);
            if (empty($card_key)) {
                $this->baoError('充值卡号不能为空');
            }
            if (!($detail = D('Rechargecard')->where(array('card_key' => $card_key))->find())) {
                $this->baoError('该充值卡不存在');
            }
            if ($detail['is_used'] == 1) {
                $this->baoError('该充值卡已经使用过了');
            }
            $member = D('Users')->find($this->uid);
            $member['money'] += $detail['value'];
            if (D('Rechargecard')->save(array('card_id' => $detail['card_id'], 'is_used' => 1))) {
                D('Users')->save(array('user_id' => $this->uid, 'money' => $member['money']));
                D('Usermoneylogs')->add(array('user_id' => $this->uid, 'money' => $detail['value'], 'create_time' => NOW_TIME, 'create_ip' => get_client_ip(), 'intro' => '代金券充值' . $detail['card_id']));
                D('Rechargecard')->save(array('card_id' => $detail['card_id'], 'user_id' => $this->uid, 'used_time' => NOW_TIME));
                //微信通知
                $this->remainMoneyNotify($detail['value'], $member['money'], 1);
                $this->baoSuccess('充值成功！', U('money/recharge'));
            }
        } else {
            $this->display();
        }
    }
    //微信余额通知
    private function remainMoneyNotify($pay, $remain, $type = 0){
        //余额变动,微信通知
        $openid = D('Connect')->getFieldByUid($this->uid, 'open_id');
        $order_id = $order['order_id'];
        $user_name = D('User')->getFieldByUser_id($this->uid, 'nickname');
        if ($type) {
            $words = '您的账户于' . date('Y-m-d H:i:s') . '收入' . $pay . '元,余额' . $remain . '元';
        } else {
            $words = '您的账户于' . date('Y-m-d H:i:s') . '支出' . $pay . '元,余额' . $remain . '元';
        }
        if ($openid) {
            $template_id = D('Weixintmpl')->getFieldByTmpl_id(4, 'template_id');
            //余额变动模板
            $tmpl_data = array(
				'touser' => $openid, 'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/user', 
				'template_id' => $template_id, 
				'topcolor' => '#2FBDAA', 
				'data' => array(
				'first' => array('value' => '尊敬的用户,您的账户余额有变动！', 
				'color' => '#2FBDAA'), 
				'keynote1' => array('value' => $user_name, 'color' => '#2FBDAA'), 
				'keynote2' => array('value' => $words, 'color' => '#2FBDAA'), 
				'remark' => array('value' => '详情请登录您的用户中心了解', 'color' => '#2FBDAA')
			));
            D('Weixin')->tmplmesg($tmpl_data);
        }
    }
	
	//获取验证码
	  public function sendsms() {
        if (!$mobile = $this->_post('mobile')) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'请输入正确的手机号码'));
        }
        if (!isMobile($mobile)) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'请输入正确的手机号码'));
        }
        if (!$user = D('Users')->where(array('mobile' => $mobile))->find()) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'手机号码不存在！'));
        }
		if ($user['user_id'] != $this->uid) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'非法操作！'));
        }
        session('mobile', $mobile);
        $randstring = session('code');
        if (empty($randstring)) {
            $randstring = rand_string(6, 1);
            session('code', $randstring);
        }
		//大鱼短信
		if($this->_CONFIG['sms']['dxapi'] == 'dy'){
            D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_yzm', $mobile, array(
				'sitename'=>$this->_CONFIG['site']['sitename'],
				'code' => $randstring
			));
        }else{
            D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
        }
        $this->ajaxReturn(array('status'=>'success','msg'=>'短信发送成功，请留意收到的短信','code'=>session('code')));
    }

	//检测手机号合法
	public function check_mobile(){
        $mobile = $this->_get('mobile');
		if(!empty($mobile)){
			$count_mobile = D('Users')->where(array('mobile' => $mobile))->count();
			if($count_mobile == 1){
				$user = D('Users')->where(array('mobile' => $mobile))->find();//这个版本不加手机号
				if (empty($user) || $user['mobile'] == $this->member['mobile']) {
					echo '0';
				} else {
					echo '您转账该对方昵称是'.'<font size="8" color="#F00">'.$user['nickname'].'</font>'.'请核对后转账，转账后无法退款';
				}
			}else{
				echo '0';
			}
		}else{
			echo '0';
		}
		
    }
	
	//好友转账
      public function transfer(){
        if($this->isPost()){
			$config = D('Setting')->fetchAll();
			$obj = D('Usertransferlogs');
			$cash_is_transfer = $config['cash']['is_transfer'];
			
			//判断网站后台设置是否合法
			if (false == $obj->check_admin_is_transfer($cash_is_transfer)) {
				$this->baoError('网站后台设置不合法，请联系管理员');
			}
			
			//检测被赠送的用户手机封装
            $mobile = $this->_post('mobile');
			if (false == $obj->check_transfer_user_mobile($mobile,$this->member['mobile'])) {
				$this->baoError($obj->getError(), 3000, true);
			}
	
			//检测余额小于0，用户余额是不是不足，超过最大限制，最小限制，检测用户转账间隔时间
			$money = ((int)$this->_post('money'))*100;
			
			if (false == $obj->check_transfer_user_money($money,$this->uid)) {
				$this->baoError($obj->getError(), 3000, true);
			}

			$yzm = $this->_post('yzm');
            if (empty($mobile) || empty($yzm))
                $this->baoError('请填写正确的手机及手机收到的验证码！');
            $session_mobile = session('mobile');
            $session_code = session('code');
            if ($this->member['mobile'] != $session_mobile)
                $this->baoError('手机号码和收取验证码的手机号不一致！');
            if ($yzm != $session_code){
				$this->baoError('验证码不正确');
			}
			
			if(!empty($config['cash']['is_transfer_commission'])){
				$commission = intval(($money*$config['cash']['is_transfer_commission'])/100);
				$receive_money = $money + $commission ;//实际扣除
			}
			
			//获取接收的USER
			$users = $obj->get_receive_users($mobile);
			$intro = $this->member['nickname'].'给您转账了'.round($money/100,2).'元';
			$intro1 = $this->member['nickname'].'给'.$users['nickname'].'转账了'.round($money/100,2).'元，手续费'.round($commission/100,2).'元';
			if($money > 0){
				if(D('Users')->addMoney($users['user_id'],$money,$intro)){
				    $logs = array();
					$logs['user_id'] = $this->uid;
					$logs['uid'] = $users['user_id'];
					$logs['money'] = $money;
					$logs['commission'] = $commission;
					$logs['intro'] = $intro1;
					$logs['create_time'] = time();
					$logs['create_ip'] = get_client_ip();
					$log_id = $obj->add($logs);
					if($log_id){
						$intro2 = '您给'.$users['nickname'].'转账了'.round($money/100,2).'元，手续费'.round($commission/100,2).'元';
						if(D('Users')->addMoney($this->uid,-$receive_money,$intro2)){
							$this->baoSuccess('恭喜您转账成功',U('money/transfer')); 
						}else{
							$this->baoError('操作失败！');
						}
					}else{
						$this->baoError('操作失败！');
					}        
				} 
			}
            
        }else{
             $this->display();
        }
    }
   
   //检测扫码支付支付状态
	 public function check() {
		$log_id = $this->_get('log_id');
        $paymentlogs = D('Paymentlogs')->find($log_id);
        if (!empty($paymentlogs) && $paymentlogs['is_paid'] ==1) {
          $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您支付成功，正在为您跳转'));
        }
	 }
}
<?php
class MoneyAction extends CommonAction
{
    public function index(){
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->display();
    }
    public function moneypay(){
        $money = (int) ($this->_post('money') * 100);
        $code = $this->_post('code', 'htmlspecialchars');
        if ($money <= 0) {
            $this->error('请填写正确的充值金额！');
        }
        if ($money > 1000000) {
            $this->error('每次充值金额不能大于1万');
        }
        $payment = D('Payment')->checkPayment($code);
        if (empty($payment)) {
            $this->error('该支付方式不存在'.$code );
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
        $this->assign('money', $money);
        $this->assign('logs', $logs);
        $this->display();
    }
    public function recharge(){
        //代金券充值
        if ($this->isPost()) {
            $card_key = $this->_post('card_key', htmlspecialchars);
            if (!D('Lock')->lock($this->uid)) {
                $this->fengmiMsg('服务器繁忙，1分钟后再试');
            }
            if (empty($card_key)) {
                D('Lock')->unlock();
                $this->fengmiMsg('充值卡号不能为空');
            }
            if (!($detail = D('Rechargecard')->where(array('card_key' => $card_key))->find())) {
                D('Lock')->unlock();
                $this->fengmiMsg('该充值卡不存在');
            }
            if ($detail['is_used'] == 1) {
                D('Lock')->unlock();
                $this->fengmiMsg('该充值卡已经使用过了');
            }
            $member = D('Users')->find($this->uid);
            $member['money'] += $detail['value'];
            if (D('Users')->save(array('user_id' => $this->uid, 'money' => $member['money']))) {
                D('Usermoneylogs')->add(array(
					'user_id' => $this->uid, 
					'money' => +$detail['value'], 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'intro' => '代金券充值' . $detail['card_id']
				));
                $res = D('Rechargecard')->save(array('card_id' => $detail['card_id'], 'is_used' => 1));
                if (!empty($res)) {
                    D('Rechargecard')->save(array('card_id' => $detail['card_id'], 'user_id' => $this->uid, 'used_time' => NOW_TIME));
                }
                $this->fengmiMsg('充值成功！', U('money/recharge'));
            }
            D('Lock')->unlock();
        } else {
            $this->display();
        }
    }
	
	 //积分兑换余额
      public function exchange(){
        if($this->isPost()){
			$config = D('Setting')->fetchAll();
			$integral_buy = $config['integral']['buy'];
			//判断积分设置是否合法
			if (false == D('Users')->check_integral_buy($integral_buy)) {
				$this->fengmiMsg('网站后台积分设置不合法，请联系管理员');
			}
			
            $exchange = (int)$this->_post('exchange');
			if($exchange <=0){
                $this->fengmiMsg('要兑换的数量不能为空！');
            }
			$scale  = D('Users')->obtain_integral_scale($integral_buy);//获取积分比例便于同步
			
			//批量检测积分兑换余额批量代码封装
			if (!D('Users')->check_integral_exchange_legitimate($exchange,$scale)) {
				$this->fengmiMsg(D('Users')->getError());	  
			}
	
            if($this->member['integral'] < $exchange){
                $this->fengmiMsg('账户积分不足');
            }
			$actual_integral = $exchange*$scale;
			$money = $actual_integral - intval(($actual_integral*$config['integral']['integral_exchange_tax'])/100);
			if($money > 0){
				if(D('Users')->addMoney($this->uid,$money,'积分兑换现金')){
					D('Users')->addIntegral($this->uid,-$exchange,'扣除兑换余额使用积分');          
				} 
			}
            $this->fengmiMsg('您成功兑换余额'.round($money/100,2).'元',U('logs/moneylogs')); 
        }else{
             $this->display();
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
					echo '您转账到对方昵称是'.'<font color="#F00">'.$user['nickname'].'</font>'.'转账后无法退款，请跟对方核实后再操作打款，建议转账前先联系对方！';
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
				$this->fengmiMsg('网站后台设置不合法，请联系管理员');
			}
			
			//检测被赠送的用户手机封装
            $mobile = $this->_post('mobile');
			if (false == $obj->check_transfer_user_mobile($mobile,$this->member['mobile'])) {
				$this->fengmiMsg($obj->getError());
			}
	
			//检测余额小于0，用户余额是不是不足，超过最大限制，最小限制，检测用户转账间隔时间
			$money = ((int)$this->_post('money'))*100;
			
			if (false == $obj->check_transfer_user_money($money,$this->uid)) {
				$this->fengmiMsg($obj->getError());
			}

			$yzm = $this->_post('yzm');
            if (empty($mobile) || empty($yzm))
                $this->fengmiMsg('请填写正确的手机及手机收到的验证码！');
            $session_mobile = session('mobile');
            $session_code = session('code');
            if ($this->member['mobile'] != $session_mobile)
                $this->fengmiMsg('手机号码和收取验证码的手机号不一致！');
            if ($yzm != $session_code){
				$this->fengmiMsg('验证码不正确');
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
							$this->fengmiMsg('恭喜您转账成功',U('logs/moneylogs')); 
						}else{
							$this->fengmiMsg('操作失败！');
						}
					}else{
						$this->fengmiMsg('操作失败！');
					}        
				} 
			}
            
        }else{
             $this->display();
        }
    }
    /**
     * 扫描购物二维码
     */
    public function scanshoppingqrcode(){
        //判读登录状态
        if (empty($this->uid)) {
            header("Location: " . U("wap/passport/login"));
            exit;
        }
        $shop_id = $this->_param('shop_id');
        if(empty($shop_id)){
            $this->error('商家信息有误，请重新扫码！');
        }
        $this->assign('shop',D('Shop')->find($shop_id));
        $this->assign('users',D('Users')->find($this->uid));
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->display();

    }
    public function scanshoppingqrcodepay(){

        //后期优化
        $pay_money = (int) ($this->_post('pay_money') * 100);//支付金额  *100 截取两位到分，暂时转换为整数 防止精度丢失
        $pay_method = $this->_post('pay_method', 'htmlspecialchars');//支付方式
        $shop_id = $this->_post('shop_id', 'htmlspecialchars');
        $user_name = $this->_post('user_name', 'htmlspecialchars');
        $user_mobile = $this->_post('user_mobile', 'htmlspecialchars');

        $users = D('users')->find($this->uid);

        if ($pay_money <= 0) {
            $this->error('请填写正确的金额！');
        }

        $payment = D('Payment')->checkPayment($pay_method);
        if (empty($payment)) {
            $this->error('该支付方式不存在');
        }
        if (strlen($user_name)<2) {
            $this->error('请填写姓名！');
        }
        if (strlen($user_mobile)<10) {
            $this->error('请填写手机号码！');
        }
        if (!isMobile($user_mobile)) {
            $this->error('手机格式不正确');
        }
        $logs = array('user_id' => $this->uid, 'type' => 'gwmd', 'code' => $pay_method, 'order_id' => 0, 'need_pay' => $pay_money, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip(), 'shop_id' => $shop_id);
        $logs['log_id'] = D('Paymentlogs')->add($logs);

        if (strlen($users['ext0']) < 2  ) {
            D('users')->save(array(
                'user_id'=>$this->uid,
                'ext0'=>$user_name
            ));
        }
        if (strlen($users['mobile']) < 10  ) {
            D('users')->save(array(
                'user_id'=>$this->uid,
                'mobile'=> $user_mobile
            ));
        }

        $this -> assign('types', D('Payment')->getTypes());
        $this -> assign('paytype', D('Payment')->getPayments());
        $check_pay_password = D('Users')->check_pay_password($this->uid);
        $this->assign('user_pay_password',$check_pay_password);

        $pay_title = '余额支付';
        if($pay_method=='alipay'){
            $pay_title='支付宝支付';
        }else if($pay_method=='tenpay'){
            $pay_title='财付通支付';
        }else if($pay_method=='weixin'){
            $pay_title='微信支付';
        }
        $this->assign('pay_title', $pay_title);
        $this->assign('button', D('Payment')->getCode($logs));
        $this->assign('money', $pay_money);
        $this->assign('shop_id', $shop_id);
        $this->assign('logs', $logs);

        $this->display();
    }
}
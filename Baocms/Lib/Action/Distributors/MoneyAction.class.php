<?php
class MoneyAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
		$this->assign('types', $types = D('Shopmoney')->getType());
    }
	

    public function sendsms(){
        $mobile = $this->_post('mobile');
        if (isMobile($mobile)) {
            session('mobile', $mobile);
            $randstring = session('cash_code', 100);
            if (empty($randstring)) {
                $randstring = rand_string(6, 1);
                session('cash_code', $randstring);
            }
            //大鱼短信
            if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_yzm', $mobile, array(
					'sitename' => $this->_CONFIG['site']['sitename'], 
					'code' => $randstring
				));
            } else {
                D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
            }
        }
    }
    public function index()
    {
        $bg_time = strtotime(TODAY);
        $counts = array();
        //财务管理
        $counts['money'] = (int) D('Shopmoney')->where(array('shop_id' => $this->shop_id))->sum('money');
        $counts['money_goods'] = (int) D('Shopmoney')->where(array('type' => goods, 'shop_id' => $this->shop_id))->sum('money');
        $counts['money_tuan'] = (int) D('Shopmoney')->where(array('type' => tuan, 'shop_id' => $this->shop_id))->sum('money');
        $counts['money_ele'] = (int) D('Shopmoney')->where(array('type' => ele, 'shop_id' => $this->shop_id))->sum('money');
        $counts['money_booking'] = (int) D('Shopmoney')->where(array('type' =>booking, 'shop_id' => $this->shop_id))->sum('money');
		$counts['money_hotel'] = (int) D('Shopmoney')->where(array('type' =>hotel, 'shop_id' => $this->shop_id))->sum('money');
		$counts['money_appoint'] = (int) D('Shopmoney')->where(array('type' =>appoint, 'shop_id' => $this->shop_id))->sum('money');
        //这个统计今日，要求统计昨日数据，+最近7天总收入。
        $str = '-1 day';
        $bg_time_yesterday = strtotime(date('Y-m-d', strtotime($str)));
        $counts['money_day'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'shop_id' => $this->shop_id))->sum('money');
        //昨日总收入
        $counts['money_day_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)), 'shop_id' => $this->shop_id))->sum('money');
        $counts['money_day_goods'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'shop_id' => $this->shop_id, 'type' => goods))->sum('money');
        $counts['money_day_goods_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)), 'shop_id' => $this->shop_id, 'type' => goods))->sum('money');
        $counts['money_day_tuan'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'shop_id' => $this->shop_id, 'type' => tuan))->sum('money');
        $counts['money_day_tuan_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)), 'shop_id' => $this->shop_id, 'type' => tuan))->sum('money');
        $counts['money_day_ele'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'shop_id' => $this->shop_id, 'type' => ele))->sum('money');
        $counts['money_day_ele_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)), 'shop_id' => $this->shop_id, 'type' => ele))->sum('money');
        $counts['money_day_booking'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'shop_id' => $this->shop_id, 'type' => booking))->sum('money');
        $counts['money_day_booking_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)), 'shop_id' => $this->shop_id, 'type' =>booking))->sum('money');
		
		 $counts['money_day_hotel'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'shop_id' => $this->shop_id, 'type' => hotel))->sum('money');
        $counts['money_day_hotel_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)), 'shop_id' => $this->shop_id, 'type' =>hotel))->sum('money');
		
		$counts['money_day_appoint'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'shop_id' => $this->shop_id, 'type' => appoint))->sum('money');
        $counts['money_day_appoint_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)), 'shop_id' => $this->shop_id, 'type' =>appoint))->sum('money');
		
		
        //商城待确认收货
        $counts['money_day_goods_recipient'] = (int) D('Ordergoods')->where(array('status' => 1, 'is_daofu' => 0, 'shop_id' => $this->shop_id))->sum('js_price');
        //团购待验证金额
        $Tuanorder = D('Tuanorder')->where(array('status' => 1, 'shop_id' => $this->shop_id))->select();
        $order_ids = array();
        foreach ($Tuanorder as $k => $val) {
            $order_ids[$val['order_id']] = $val['order_id'];
        }
        $Tuancode['order_id'] = array('IN', $order_ids);
        $Tuancode['status'] = array('eq', 0);
        $Tuancode['is_used'] = array('eq', 0);
        $Tuancode['shop_id'] = array('eq', $this->shop_id);
        $counts['money_day_tuan_recipient'] = D('Tuancode')->where($Tuancode)->sum('settlement_price');
        //团购结算
        $counts['money_day_ele_recipient'] = (int) D('Eleorder')->where(array('status' => 2, 'shop_id' => $this->shop_id))->sum('need_pay');
        //外卖待确认收货
        $is_pei = D('Shop')->where(array('shop_id' => $this->shop_id))->find();
        if ($is_pei == 0) {
            $zong = (int) D('Eleorder')->where(array('is_pay' => 1, 'status' => 2, 'shop_id' => $this->shop_id))->sum('need_pay');
            $logistics = (int) D('Eleorder')->where(array('is_pay' => 1, 'status' => 2, 'shop_id' => $this->shop_id))->sum('logistics');
            $counts['money_day_ele_recipient'] = $zong - $logistics;
        } else {
            $counts['money_day_ele_recipient'] = (int) D('Eleorder')->where(array('is_pay' => 1, 'status' => 2, 'shop_id' => $this->shop_id))->sum('need_pay');
        }
        //统计订座
        $counts['money_day_booking_recipient'] = (int) D('Shopdingyuyue')->where(array('status' => 1, 'shop_id' => $this->shop_id))->sum('need_price');
        $this->assign('counts', $counts);
        $this->display();
    }
	//商户资金日志
    public function detail(){
        $this->display();
    }
	
	//商户资金日志LOAD
    public function load(){
       $map = array('shop_id' => $this->shop_id);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        $money = D('Shopmoney');
        import('ORG.Util.Page');// 导入分页类 
        $count = $money->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
		
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }		
		
        $list = $money->where($map)->order(array('money_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
			$type = D('Shopmoney')->get_money_type($val['type']);
            $list[$k]['type'] = $type;
			
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
    public function cashlogs(){
        $map = array('shop_id' => $this->shop_id, 'type' => shop);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        $Userscash = D('Userscash');
        import('ORG.Util.Page');
        $count = $Userscash->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $Userscash->where($map)->order(array('cash_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function cash(){
		if (false == D('Userscash')->check_cash_addtime($this->uid,2)) {//后期下单后更新，更新购买列表信息
			$this->error('您提现太频繁了，明天再来试试吧');
		}		
        $user_ids = D('Shop')->where(array('shop_id' => $this->shop_id))->find();
        $user_id = $user_ids['user_id'];
        $userscash = D('Userscash')->where(array('user_id' => $user_ids['user_id']))->find();
        $shop = D('Shop')->where(array('user_id' => $user_id))->find();
        if ($shop == '') {
            $cash_money = $this->_CONFIG['cash']['user'];
            $cash_money_big = $this->_CONFIG['cash']['user_big'];
        } elseif ($shop['is_renzheng'] == 0) {
            $cash_money = $this->_CONFIG['cash']['shop'];
            $cash_money_big = $this->_CONFIG['cash']['shop_big'];
        } elseif ($shop['is_renzheng'] == 1) {
            $cash_money = $this->_CONFIG['cash']['renzheng_shop'];
            $cash_money_big = $this->_CONFIG['cash']['renzheng_shop_big'];
        } else {
            $cash_money = $this->_CONFIG['cash']['user'];
            $cash_money_big = $this->_CONFIG['cash']['user_big'];
        }
        //对比手机号码，验证码
        $shop = D('Shop')->where(array('shop_id' => $this->shop_id))->find();
        $users = D('Users')->where(array('user_id' => $shop['user_id']))->find();
        $s_mobile = session('mobile');
        $cash_code = session('cash_code');
        //获取life_code
        if (IS_POST) {
            $gold = (int) ($_POST['gold'] * 100);
            if ($gold <= 0) {
                $this->fengmiMsg('提现金额不合法');
            }
            if ($gold < $cash_money * 100) {
                $this->fengmiMsg('提现金额小于最低提现额度');
            }
            if ($gold > $cash_money_big * 100) {
                $this->fengmiMsg('您单笔最多能提现' . $cash_money_big . '元');
            }
//            if ($gold > $this->member['gold'] || $this->member['gold'] == 0) {
//                $this->fengmiMsg('商户资金不足，无法提现');
//            }
//            if (!($data['bank_name'] = htmlspecialchars($_POST['bank_name']))) {
//                $this->fengmiMsg('开户行不能为空');
//            }
//            if (!($data['bank_num'] = htmlspecialchars($_POST['bank_num']))) {
//                $this->fengmiMsg('银行账号不能为空');
//            }
//            if (!($data['bank_realname'] = htmlspecialchars($_POST['bank_realname']))) {
//                $this->fengmiMsg('开户姓名不能为空');
//            }
            //获取手机，验证码
            $yzm = $this->_post('yzm');
            $s_mobile = session('mobile');
            $cash_code = session('cash_code');
            if (empty($yzm)) {
                $this->fengmiMsg('请输入短信验证码');
            }
            if ($users['mobile'] != $s_mobile) {
                $this->fengmiMsg('手机号码和收取验证码的手机号不一致！');
            }
            if ($yzm != $cash_code) {
                $this->fengmiMsg('短信验证码不正确');
            }
            $data['bank_branch'] = htmlspecialchars($_POST['bank_branch']);
            $data['user_id'] = $this->uid;
			
			if(!empty($this->_CONFIG['cash']['shop_cash_commission'])){
				$commission = intval(($gold * $this->_CONFIG['cash']['shop_cash_commission'])/100);
				$gold = $gold - $commission;
			}
			
            $arr = array();
            $arr['user_id'] = $this->uid;
            $arr['shop_id'] = $this->shop_id;
            //提现商家
            $arr['city_id'] = $shop['city_id'];
            $arr['area_id'] = $shop['area_id'];
            $arr['gold'] = $gold;
			$arr['commission'] = $commission;
            $arr['type'] = shop;
            $arr['addtime'] = NOW_TIME;
            $arr['account'] = $this->member['account'];
            $arr['bank_name'] = $data['bank_name'];
            $arr['bank_num'] = $data['bank_num'];
            $arr['bank_realname'] = $data['bank_realname'];
            $arr['bank_branch'] = $data['bank_branch'];
			
			if(!empty($commission)){
				$intro = $shop['shop_name'].'申请提现，扣款'.round($gold/100,2).'元，其中手续费：'.round($commission/100,2).'元';
			}else{
				$intro = $shop['shop_name'].'申请提现，扣款'.round($gold/100,2).'元';
			}
			if($cash_id = D('Userscash')->add($arr)){
                D('Users')->Money($this->uid, -$gold, $intro);
				D('Usersex')->save($data);
				$this->fengmiMsg('恭喜，申请提现成功，请等待管理员审核', U('money/cashlogs'));
			}else{
				$this->fengmiMsg('抱歉，提现操作失败！');
			}	
			
        } else {
            $this->assign('info', D('Usersex')->getUserex($this->uid));
            $this->assign('gold', $this->member['gold']);
            $this->assign('cash_money', $cash_money);
            $this->assign('cash_money_big', $cash_money_big);
            $this->assign('userscash', $userscash);
            $this->display();
        }
    }
}
<?php
class MoneyAction extends CommonAction{
	
	 public function _initialize() {
        parent::_initialize(); 
		$this->assign('types', $types = D('Shopmoney')->getType());
    }
	
	
	
	public function finance(){
		$bg_time = strtotime(TODAY);
		$counts = array();
			//财务管理
		$counts['money'] = (int) D('Shopmoney')->where(array('shop_id' => $this->shop_id))->sum('money');

		$counts['money_goods'] = (int) D('Shopmoney')->where(array('type'=>goods,'shop_id' => $this->shop_id))->sum('money');
		$counts['money_tuan'] = (int) D('Shopmoney')->where(array('type'=>tuan,'shop_id' => $this->shop_id))->sum('money');
		$counts['money_ele'] = (int) D('Shopmoney')->where(array('type'=>ele,'shop_id' => $this->shop_id))->sum('money');
		$counts['money_booking'] = (int) D('Shopmoney')->where(array('type'=>booking,'shop_id' => $this->shop_id))->sum('money');
		$counts['money_hotel'] = (int) D('Shopmoney')->where(array('type'=>hotel,'shop_id' => $this->shop_id))->sum('money');
		$counts['money_appoint'] = (int) D('Shopmoney')->where(array('type'=>appoint,'shop_id' => $this->shop_id))->sum('money');
		//这个统计今日，要求统计昨日数据，+最近7天总收入。
		
		$str = '-1 day';
		$bg_time_yesterday =strtotime(date('Y-m-d',strtotime($str)));

		$counts['money_day'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'shop_id' => $this->shop_id,))->sum('money');
		//昨日总收入
		$counts['money_day_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)),'shop_id' => $this->shop_id,))->sum('money');						
					
		$counts['money_day_goods'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'shop_id' => $this->shop_id,'type'=>goods,))->sum('money');
				
		$counts['money_day_goods_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)),'shop_id' => $this->shop_id,'type'=>goods))->sum('money');
				
					
		$counts['money_day_tuan'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'shop_id' => $this->shop_id,'type'=>tuan,))->sum('money');
					
		$counts['money_day_tuan_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)),'shop_id' => $this->shop_id,'type'=>tuan))->sum('money');
					
		$counts['money_day_ele'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'shop_id' => $this->shop_id,'type'=>ele,))->sum('money');			
			
				
		$counts['money_day_ele_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)),'shop_id' => $this->shop_id,'type'=>ele))->sum('money');			
					
		$counts['money_day_booking'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'shop_id' => $this->shop_id,'type'=>booking,))->sum('money');			
					
		$counts['money_day_booking_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)),'shop_id' => $this->shop_id,'type'=>booking))->sum('money');	
		
		$counts['money_day_hotel'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'shop_id' => $this->shop_id,'type'=>hotel,))->sum('money');			
					
		$counts['money_day_hotel_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)),'shop_id' => $this->shop_id,'type'=>hotel))->sum('money');	
		
		$counts['money_day_appoint'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'shop_id' => $this->shop_id,'type'=>appoint,))->sum('money');			
					
		$counts['money_day_appoint_yesterday'] = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_yesterday)),'shop_id' => $this->shop_id,'type'=>appoint))->sum('money');	
		
		
		//商城待确认收货
		$counts['money_day_goods_recipient'] = (int) D('Ordergoods')->where(array('status' => 1,'is_daofu' => 0,'shop_id' => $this->shop_id))->sum('js_price');
		
		//团购待验证金额
		$Tuanorder = D('Tuanorder')->where(array('status' => 1,'shop_id' => $this->shop_id))->select();
				$order_ids = array();
				foreach ($Tuanorder as $k => $val) {
					$order_ids[$val['order_id']] = $val['order_id'];
				}
		$Tuancode['order_id'] =  array('IN', $order_ids);
		$Tuancode['status'] =  array('eq', 0);
		$Tuancode['is_used'] =  array('eq', 0);
		$Tuancode['shop_id'] = array('eq',$this->shop_id); 
		$counts['money_day_tuan_recipient'] = D('Tuancode')->where($Tuancode)->sum('settlement_price');//团购结算
		
		
		$counts['money_day_ele_recipient'] = (int) D('Eleorder')->where(array('status' => 2,'shop_id' => $this->shop_id))->sum('need_pay');
		//外卖待确认收货
			$is_pei = D('Shop')->where(array('shop_id' => $this->shop_id))->find();
			if($is_pei == 0){
					$zong = (int) D('Eleorder')->where(array('is_pay' => 1,'status' => 2,'shop_id' => $this->shop_id))->sum('need_pay');
					$logistics = (int) D('Eleorder')->where(array('is_pay' => 1,'status' => 2,'shop_id' => $this->shop_id))->sum('logistics');
					$counts['money_day_ele_recipient'] = $zong - $logistics ;
				}else{
					$counts['money_day_ele_recipient'] = (int) D('Eleorder')->where(array('is_pay' => 1,'status' => 2,'shop_id' => $this->shop_id))->sum('need_pay');
			}
			
		//统计订座
		$counts['money_day_ding_recipient'] = (int) D('Shopdingyuyue')->where(array('status' => 1,'shop_id' => $this->shop_id))->sum('need_price');
		$this->assign('counts', $counts);
		$this->display();
		
	}
    public function index(){
        $map = array('user_id' => $this->uid);
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
        $Usermoneylogs = D('Usergoldlogs');
        import('ORG.Util.Page');
        $count = $Usermoneylogs->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $Usermoneylogs->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function shopmoney(){
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
		
		if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = $this->_param('st', 'htmlspecialchars');
            if (!empty($st)) {
                $map['type'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
		
        $money = D('Shopmoney');
        import('ORG.Util.Page');// 导入分页类 
        $count = $money->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $money->where($map)->order(array('money_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
			$type = D('Shopmoney')->get_money_type($val['type']);
            $list[$k]['type'] = $type;
			
        }
		$shop_money_sum = $money->where($map)->sum('money');
        $this->assign('shop_money_sum',$shop_money_sum);
		
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function tixian(){
        $user_ids = D('Shop')->where(array('shop_id' => $this->shop_id))->find();
        $user_id = $user_ids['user_id'];
        $userscash = D('Userscash')->where(array('user_id' => $user_ids['user_id']))->find();
        $user_mobile = D('Users')->where(array('user_id' => $user_ids['user_id']))->find();
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
        if (IS_POST) {
            $gold = (int) ($_POST['gold'] * 100);
            if ($gold <= 0) {
                $this->baoError('提现金额不合法');
            }
            if ($gold < $cash_money * 100) {
                $this->baoError('提现金额小于最低提现额度');
            }
            if ($gold > $cash_money_big * 100) {
                $this->baoError('您单笔最多能提现' . $cash_money_big . '元');
            }
            if ($gold > $this->member['gold'] || $this->member['gold'] == 0) {
                $this->baoError('商户资金不足，无法提现');
            }
//            if (!($data['bank_name'] = htmlspecialchars($_POST['bank_name']))) {
//                $this->baoError('开户行不能为空');
//            }
//            if (!($data['bank_num'] = htmlspecialchars($_POST['bank_num']))) {
//                $this->baoError('银行账号不能为空');
//            }
//            if (!($data['bank_realname'] = htmlspecialchars($_POST['bank_realname']))) {
//                $this->baoError('开户姓名不能为空');
//            }
            $data['bank_branch'] = htmlspecialchars($_POST['bank_branch']);
            $data['user_id'] = $this->uid;
            $arr = array();
            $arr['user_id'] = $this->uid;
            $arr['shop_id'] = $this->shop_id;
            $arr['city_id'] = $shop['city_id'];
            $arr['area_id'] = $shop['area_id'];
            $arr['money'] = $gold;
            $arr['type'] = shop;
            $arr['addtime'] = NOW_TIME;
            $arr['account'] = $this->member['account'];
            $arr['bank_name'] = $data['bank_name'];
            $arr['bank_num'] = $data['bank_num'];
            $arr['bank_realname'] = $data['bank_realname'];
            $arr['bank_branch'] = $data['bank_branch'];
            D("Userscash")->add($arr);
            D('Usersex')->save($data);
            D('Users')->Money($this->uid, -$gold, '商户申请提现，扣款');
            $this->baoSuccess('申请提现成功', U('money/index'));
        } else {
            $this->assign('info', D('Usersex')->getUserex($this->uid));
            $this->assign('gold', $this->member['gold']);
            $this->assign('cash_money', $cash_money);
            $this->assign('cash_money_big', $cash_money_big);
            $this->assign('userscash', $userscash);
            $this->display();
        }
    }
    public function tixianlog(){
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
    public function tjmonth(){
        $Shopmoney = D('Shopmoney');
        import('ORG.Util.Page');
        $count = $Shopmoney->tjmonthCount("", $this->shop_id);
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Shopmoney->tjmonth("", $this->shop_id, $Page->firstRow, $Page->listRows);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function tjday(){
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date) + 86400;
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            $bg_time = NOW_TIME - 86400 * 30;
            $bg_date = date('Y-m-d', $bg_time);
            $end_date = date('Y-m-d', NOW_TIME);
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
            $end_time = NOW_TIME + 86400;
        }
        $data = D('Shopmoney')->money($bg_time, $end_time, $this->shop_id);
        $this->assign('data', $data);
        $this->display();
    }
}
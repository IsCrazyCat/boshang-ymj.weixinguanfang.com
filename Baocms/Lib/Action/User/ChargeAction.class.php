<?php
class ChargeAction extends CommonAction {
    public function index() {
        $this->assign('billtypes', D('Billtype')->select());
        $id = (int)$this->_param('id');
        if ($id) {
            $billType = D('Billtype')->find($id);
            if(!$billType) {
                $this->error('缴费类型不存在！');
            }
            $this->assign('id', $id);
            $this->assign('billType', $billType);
            $fieldNames = array('mobile' => '手机', 'realname' => '户名', 'account' => '编号');
            $fields = explode(',', $billType['bill_fields']);
            $this->assign('fieldNames', $fieldNames);
            $this->assign('fields', $fields);
        }
		if (!$this->member['mobile']) {
			$this->error('请先绑定手机号', U('/mcenter/information'));
		}
        $this->display();
    }

    public function pay(){
        $id = (int) $this->_post('id');
        if ($id) {
            $billType = D('Billtype')->find($id);
            if(!$billType) {
                $this->error('缴费类型不存在！');
            }
            $fieldNames = array('mobile' => '手机', 'realname' => '户名', 'account' => '编号');
            $fields = explode(',', $billType['bill_fields']);
        }
		if (!$this->member['mobile']) {
			$this->error('请先绑定手机号', U('/mcenter/information'));
		}		
    	$Users = D('Users');
        if (IS_POST) {
            $member = $this->member;
        	foreach($fields as $v) {
                $fieldValue = $this->_post($v);
                if (!$fieldValue) {
                    $this->error('请输入' . $fieldNames[$v]);
                }
            }
            $userMoney = $member['money'] / 100;
            $money = (int)($_POST['sum'] * 100);
            $fee = $money * $billType['fee_rate'] / 100;
            $total = $money + $fee;
            if ($money <= 0) {
                $this->error('请输入正确的缴费金额(必须大于0,小于' . $userMoney . ')！');
            }
            if ($member['money'] < $total) {
                $this->error('很抱歉您的账户余额不足', U('/mcenter/money'));
            }
            if ($member['interest_money'] >= $total) {
                $logMoney = 0;
                $logInterest = $total;
                $member['used_interest'] += $total;
            }
            else {
                $logMoney = $total - $member['interest_money'];
                $logInterest = $member['interest_money'];
                $member['used_interest'] += $logInterest;
                $member['money'] -= $logMoney;
            }

            if ($Users->save(array('user_id' => $this->uid, 'money' => $member['money'], 'used_interest' => $member['used_interest']))) {
                $arr = array(
                    'bill_type_id' => $id,
                    'user_id' => $this->uid,
                    'city_id' => $this->city_id,
                    'area_id' => (int)$this->_post('area_id'),
                    'mobile' => trim($this->_post('mobile', 'htmlspecialchars')),
                    'mobile' => trim($this->_post('mobile', 'htmlspecialchars')),
                    'realname' => trim($this->_post('realname', 'htmlspecialchars')),
                    'account' => trim($this->_post('account', 'htmlspecialchars')),
                    'memo' => trim($this->_post('memo', 'htmlspecialchars')),
                    'sum' => $money,
                    'money' => $logMoney,
                    'interest' => $logInterest,
                    'create_time' => NOW_TIME,
                    'create_ip' => get_client_ip()
                );
                $orderId = D('Billorder')->add($arr);
                if ($logMoney > 0) {
                    D('Usermoneylogs')->add(array(
                        'user_id' => $this->uid,
                        'money' => -$logMoney,
                        'create_time' => NOW_TIME,
                        'create_ip' => get_client_ip(),
                        'intro' => '账户余额缴费' . $billType['bill_type_name']  . ',订单ID' . $orderId
                    ));
                }
                if ($logInterest > 0) {
                    $intro = '利息余额缴费' . $billType['bill_type_name'] . ',订单ID' . $orderId;
                    D('Userinterestlogs')->add(array(
                        'user_id' => $this->uid,
                        'interest' => -$logInterest,
                        'intro' => $intro,
                        'create_time' => NOW_TIME,
                        'create_ip' => get_client_ip()
                    ));
                }
                $this->success('缴费提交成功！等待网站管理员处理！', U('/mcenter/charge'));
            }
            else {
                $this->baoError('缴费提交失败！');
            }
        }
        $this->display();
    }

}
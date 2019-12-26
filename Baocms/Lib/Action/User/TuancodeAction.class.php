<?php
class TuancodeAction extends CommonAction{
    public function codeloading(){
        $Tuancode = D('Tuancode');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'closed' => 0);
        $aready = (int) $this->_param('aready');
        if ($aready == 2) {
            $map['is_used'] = 1;
        } elseif ($aready == 1) {
            $map['status'] = 0;
            $map['is_used'] = 0;
        } else {
            $aready == null;
        }
        if ($order_id = (int) $this->_get('order_id')) {
            $map['order_id'] = $order_id;
        }
        $count = $Tuancode->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Tuancode->where($map)->order(array('code_id' => 'desc','used_time'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $tuan_ids = array();
        foreach ($list as $val) {
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }
        $this->assign('tuans', D('Tuan')->itemsByIds($tuan_ids));
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display(); 
    }
    public function index(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
    }
    public function refund($code_id){
        $code_id = (int) $code_id;
        if ($detail = D('Tuancode')->find($code_id)) {
            if ($detail['user_id'] != $this->uid) {
                $this->fengmiMsg('非法操作');
            }
            if ($detail['status'] != 0 || $detail['is_used'] != 0) {
                $this->fengmiMsg('该套餐码不能申请退款');
            }
            D('Tuanorder')->save(array('order_id' => $detail['order_id'], 'status' => 3));
            if (D('Tuancode')->save(array('code_id' => $code_id, 'status' => 1))) {
				D('Weixintmpl')->weixin_user_refund_shop($code_id,4);//套餐劵申请退款，传订单ID跟类型
                $this->fengmiMsg('申请成功！等待网站客服处理！', U('tuancode/index'));
            }
        }
        $this->error('操作失败');
    }
    public function quxiao($code_id){
        $code_id = (int) $code_id;
        if ($detail = D('Tuancode')->find($code_id)) {
            if ($detail['user_id'] != $this->uid) {
                $this->error('非法操作');
            }
            D('Tuanorder')->save(array('order_id' => $detail['order_id'], 'status' => 1));
            if (D('Tuancode')->save(array('code_id' => $code_id, 'status' => 0))) {
				D('Weixintmpl')->weixin_delete_order_shop($code_id,4);//套餐劵取消订单，传订单ID跟类型
                $this->fengmiMsg('取消成功，您可以正常使用了！', U('tuancode/index'));
            }
        }
        $this->error('操作失败');
    }
    public function weixin(){
        $code_id = $this->_get('code_id');
        if (!($detail = D('Tuancode')->find($code_id))) {
            $this->error('没有该套餐码');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error("套餐码不存在！");
        }
        if ($detail['status'] != 0 || $detail['is_used'] != 0) {
            $this->error('该套餐码属于不可消费的状态');
        }
        $url = U('worker/index/scanaudit', array('use_user_id'=>$this->uid,'code_id' => $code_id, 't' => NOW_TIME, 'sign' => md5($code_id . C('AUTH_KEY') . NOW_TIME)));
        $token = 'tuancode_' . $code_id;
        $file = baoQrCode($token, $url);
        $this->assign('file', $file);
        $this->assign('detail', $detail);
        $this->display();
    }
    public function sms($code_id)
    {
        $code_id = (int) $code_id;
        $obj = D('Tuancode');
        if ($detail = D('Tuancode')->find($code_id)) {
            if ($detail['user_id'] != $this->uid) {
                $this->error('非法操作');
            }
            if ($detail['is_sms'] != 0) {
                $this->error('您已请求过短信啦~');
            }
            $users = D('Users')->find($this->uid);
            $list = $obj->find($code_id);
            $code = $list['code'];
            $price = round($list['price'] / 100);
            $shop_ids = $list['shop_id'];
            $shop = D('Shop')->find($shop_ids);
            if (!empty($users['mobile'])) {
                if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                    D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_tuan_user', 
					$users['mobile'], array(
						'code' => $code, 
						'user' => $users['nickname'], 
						'shop_name' => $shop['shop_name']
					));
                } else {
                    D('Sms')->sendSms('sms_tuancode', $mobile, array(
						'user' => $users['nickname'], 
						'code' => $code, 
						'price' => $price, 
						'shop_name' => $shop['shop_name']
					));
                }
            }
            $obj->save(array('code_id' => $code_id, 'is_sms' => 1));
            $this->fengmiMsg('短信已成功发送到您手机！', U('tuancode/index'));
        } else {
            $this->error('操作失败');
        }
    }
    public function delete($code_id){
        $code_id = (int) $code_id;
        if ($detail = D('Tuancode')->find($code_id)) {
            if ($detail['user_id'] != $this->uid) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '非法操作！'));
            }
            if ($detial['status'] == 1) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该套餐码暂时不能删除！'));
            }
            if ($detial['status'] == 0) {
                if ($detial['is_used'] == 0) {
                    $this->ajaxReturn(array('status' => 'error', 'msg' => '该套餐码暂时不能删除！'));
                }
            }
            if (D('Tuancode')->save(array('code_id' => $code_id, 'closed' => 1))) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功', U('tuancode/index')));
            }
        }
        $this->ajaxReturn(array('status' => 'error', 'msg' => '操作失败'));
    }
}
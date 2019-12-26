<?php 
class CloudAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
		if ($this->_CONFIG['operation']['cloud'] == 0) {
				$this->error('此功能已关闭');die;
		}
        $this->types = d('Cloudgoods')->getType();
        $this->assign('types', $this->types);
		$this->assign('areas', $areas = D('Area')->fetchAll());
		$this->assign('bizs', $biz = D('Business')->fetchAll());
    }
    public function index(){
        $linkArr = array();
        $type = (int) $this->_param('type');
        if (!empty($type)) {
            $this->assign('type', $type);
        }
		$order = $this->_param('order','htmlspecialchars');
        $this->assign('order', $order);

        $area_id = (int) $this->_param('area_id');
        $this->assign('area_id', $area_id);

        $this->assign('nextpage', linkto('cloud/loaddata', array('type'=>$type,'type'=>$type,'area_id'=>$area_id,'order'=>$order,'t' => NOW_TIME, 'p' => '0000')));
        $this->assign('linkArr', $linkArr);
        $this->display();
    }
    public function loaddata(){
        $goods = d('Cloudgoods');
        import('ORG.Util.Page');
	    $map = array('audit' => 1, 'closed' => 0);
        $type = (int) $this->_param('type');
        if (!empty($type)) {
            $map['type'] = $type;
            $this->assign('type', $type);
        }
		$area_id = (int) $this->_param('area_id');
        if ($area) {
            $map['area_id'] = $area_id;
        }
		//排序重写
		$order = $this->_param('order','htmlspecialchars');
		switch ($order) {
            case 'p':
                $orderby = array('create_time' => 'desc');
                break;
            case 'v':
                $orderby = array('price' => 'asc', 'goods_id' => 'desc');
                break;
            case 's':
                $orderby = array('join' => 'desc');
                break;
        }
		
        $count = $goods->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $var = c('VAR_PAGE') ? c('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $goods->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function cloudbuy(){
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        $goods_id = (int) $_POST['goods_id'];
        $detail = D('Cloudgoods')->find($goods_id);
        if (empty($detail)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该云购商品不存在'));
        }
        $obj = D('Cloudgoods');
        $logs = D('Cloudlogs');
        if (IS_AJAX) {
            $num = (int) $_POST['num'];
            if (empty($num)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '数量不能为空'));
            }
            if ($num < $this->types[$detail['type']]['num'] || $num % $this->types[$detail['type']]['num'] != 0) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '数量不正确'));
            }
            $count = $logs->where(array('goods_id' => $goods_id, 'user_id' => $this->uid))->sum('num');
            $left = $detail['max'] - $count;
            $lefts = $detail['price'] - $detail['join'];
            $left <= $lefts ? $limit = $left : ($limit = $lefts);
            if ($limit < $num) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '您最多能购买' . $limit . '人次'));
            }
            $details = D("Cloudgoods" )->find($goods_id);
            if ($log_id = $obj->cloud($goods_id,$this->uid,$num )){
				if($this->member['money'] > $num * 100){//如果有余额就扣费
					if (!D('Cloudgoods')->pay_cloud($goods_id, $this->uid, $num,$log_id)) {
						$this->ajaxReturn( array( "status" => "error", "msg" => "很抱歉您云购失败"));
					}else{
						$this->ajaxReturn( array( "status" => "success", "msg" => "云购成功，请等待结果或者继续加注" ));
					}
				}else{
					$this->ajaxReturn( array("status" => "error","msg" => "您云购成功，正在为您跳转付款页面","url" => U("cloud/pay", array('log_id' => $log_id))));
				}	
            }
            else{
                $this->ajaxReturn( array( "status" => "error", "msg" => "云购失败" ) );
            }
        }
    }
    public function detail($goods_id){
        if ($goods_id = (int) $goods_id) {
            $obj = D('Cloudgoods');
            if (!($detail = $obj->find($goods_id))) {
                $this->error('没有该商品');
            }
            if ($detail['closed'] != 0 || $detail['audit'] != 1) {
                $this->error('没有该商品');
            }
            $thumb = unserialize($detail['thumb']);
            $this->assign('thumb', $thumb);
            $count = D('Cloudlogs')->where(array('goods_id' => $goods_id, 'user_id' => $this->uid))->sum('num');
            $left = $detail['max'] - $count;
            $cloudlogs = D('Cloudlogs');
            $map = array('goods_id' => $goods_id);
            $list = $cloudlogs->where($map)->order(array('log_id' => 'desc'))->select();
            $user_ids = array();
            foreach ($list as $k => $val) {
                $user_ids[$val['user_id']] = $val['user_id'];
            }
            $this->assign('users', D('Users')->itemsByIds($user_ids));
            $this->assign('list', $list);
            $total = $cloudlogs->where(array('goods_id' => $goods_id, 'user_id' => $detail['win_user_id']))->sum('num');
            $data_all = $obj->get_datas($list);
            $return = $obj->get_last50_time($list);
            $zhongjiang = fmod($return['total'], $detail['price']) + 10000001;
            $zhong = $data_all[$zhongjiang];
            $this->assign('zhong', $zhong);
            $this->assign('total', $total);
            $this->assign('left', $left);
            $this->assign('detail', $detail);
            $this->display();
        } else {
            $this->error('没有该商品');
        }
    }
	
	//云购直接支付页面
	 public function pay(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $log_id = (int) $this->_get('log_id');
        $cloudlogs = D('Cloudlogs')->find($log_id);
        if (empty($cloudlogs) || $cloudlogs['status'] != 0 || $cloudlogs['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
		$this->assign('cloudlogs', $cloudlogs);
        $this->assign('payment', D('Payment')->getPayments_running());//新版跑腿支付，云购暂时用这个去掉货到付款
        $this->display();
    }
	 //去付款
	 public function pay2(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $log_id = (int) $this->_get('log_id');
        $cloudlogs = D('Cloudlogs')->find($log_id);
         if (empty($cloudlogs) || $cloudlogs['status'] != 0 || $cloudlogs['user_id'] != $this->uid) {
            $this->fengmiMsg('该订单不存在');
            die;
        }
        if (!($code = $this->_post('code'))) {
            $this->fengmiMsg('请选择支付方式！');
        }
        if ($code == 'wait') {
             $this->fengmiMsg('暂不支持货到付款，请重新选择支付方式');
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->fengmiMsg('该支付方式不存在，请稍后再试试');
            }
			$need_pay = $cloudlogs['money'];//再更新防止篡改支付日志
			if(!empty($need_pay)){
				$logs = array(
					'type' => 'cloud', 
					'user_id' => $this->uid, 
					'order_id' => $log_id, //支付日志的ORDER_ID对应log_id
					'code' => $code, 
					'need_pay' => $need_pay, 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'is_paid' => 0
				);
                $logs['log_id'] = D('Paymentlogs')->add($logs);
				if($logs['log_id']){
					$this->fengmiMsg('创建订单成功，下一步将跳转到付款页面',U('payment/payment', array('log_id' => $logs['log_id'])));
				}else{
					$this->fengmiMsg('写入支付日志表失败');
				}
			}else{
				$this->fengmiMsg('非法操作');
			}
        }
    }
}
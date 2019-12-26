<?php 
class RunningAction extends CommonAction{
	
	private $create_fields = array('city_id', 'user_id','title', 'thumb','name', 'addr', 'mobile', 'price', 'freight','need_pay', 'lng', 'lat', 'lbs_addr');
	
    protected function _initialize(){
        parent::_initialize();
        $running = (int) $this->_CONFIG['operation']['running'];
        if ($running == 0) {
            $this->error('此功能已关闭');
            die;
        }
		$this->assign('types', D('Running')->getType());
    }
    public function index(){
        $status = (int) $this->_param("status");
        $this->assign("status", $status);
        $this->display();
    }
    public function load(){
        $running = D('Running');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'closed' => 0);
		if (isset($_GET['status']) || isset($_POST['status'])) {
            $status = (int) $this->_param('status');
            if ($status != 999) {
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        } else {
            $this->assign('status', 999);
        }
        $count = $running->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $running->where($map)->order('running_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function state($running_id){
        $running_id = (int) $running_id;
        if (empty($running_id) || !($detail = D("Running")->find($running_id))) {
            $this->error("该跑腿不存在");
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error("请不要操作他人的跑腿");
        }
		$thumb = unserialize($detail['thumb']);
        $this->assign('thumb', $thumb);
		$this->assign('deliverys', D('Delivery')->where(array('user_id'=>$detail['cid']))->find());
        $this->assign("detail", $detail);
        $this->display();
    }
	
	public function detail($running_id){
        $running_id = (int) $running_id;
        if (empty($running_id) || !($detail = D("Running")->find($running_id))) {
            $this->error("该跑腿不存在");
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error("请不要操作他人的跑腿");
        }
		$thumb = unserialize($detail['thumb']);
        $this->assign('thumb', $thumb);
		$this->assign('deliverys', D('Delivery')->where(array('user_id'=>$detail['cid']))->find());
        $this->assign("detail", $detail);
        $this->display();
    }
	
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
			if (!D('Running')->Check_Running_Interval_Time($this->uid)) {
				$this->fengmiMsg(D('Running')->getError(), U("running/index"));	 
			}
            if ($running_id = D("Running")->add($data)){
				$running = D("Running")->find($running_id);
				if($this->member['money'] >= $running['need_pay']){//如果有余额就扣费
					if (!D('Running')->Pay_Running($running_id,$this->uid)) {
						$this->fengmiMsg(D('Running')->getError(), U("running/index"));	  
					}else{
						$this->fengmiMsg("恭喜发布跑腿成功", U("running/index"));
					}
				}else{
					$this->fengmiMsg('恭喜您发布跑腿成功，正在为您跳转付款！', U('running/pay', array('running_id' => $running_id)));
				}
            }
            $this->fengmiMsg("发布失败");
        } else {
            $this->assign('useraddr', D('Useraddr')->where(array('user_id' => $this->uid, 'is_default' => 1))->limit(0, 1)->select());
            $this->display();
        }
    }
    public function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['city_id'] = $this->city_id;
        $data['user_id'] = $this->uid;
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->fengmiMsg("需求不能为空");
        }
		//传图组合开始
		$thumb = $this->_param('thumb', false);
            foreach ($thumb as $k => $val) {
                if (empty($val)) {
                    unset($thumb[$k]);
                }
                if (!isImage($val)) {
                    unset($thumb[$k]);
                }
            }
        $data['thumb'] = serialize($thumb);
		//传图组合结束	
		if(!empty($MEMBER['nickname'])){
			$name = $MEMBER['nickname'];
		}else{
			$name = $MEMBER['account'];
		}
        $data['name'] = $name;
		$data['mobile'] = $this->member['mobile'];
        if (empty($data['mobile'])) {
			echo "<script>parent.check_user_mobile();</script>";
            die;
        }
		if (!ismobile($data['mobile'])) {
            $this->fengmiMsg("手机格式不正确");
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->fengmiMsg("地址不能为空");
        }
        $data['price'] = (int) $data['price']*100;
        $data['freight'] = (int) $data['freight']*100;
        if (empty($data['freight'])) {
            $this->fengmiMsg("运费不能为空");
        }
        $data['need_pay'] = $data['price']+$data['freight'];//应付金额
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
   
    public function delete($running_id){
        if (is_numeric($running_id) && ($running_id = (int) $running_id)) {
            $obj = D("Running");
            if (!($detail = $obj->find($running_id))) {
                $this->error("跑腿不存在");
            }
            if ($detail['closed'] == 1 || $detail['status'] != 0 && $detail['status'] != 2) {
                $this->error("该跑腿状态不允许被删除");
            }
            $obj->save(array("running_id" => $running_id, "closed" => 1));
            $this->success("删除成功！", u("running/index"));
        }
    }
	
	//跑腿直接支付页面
	 public function pay(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $running_id = (int) $this->_get('running_id');
        $running = D('Running')->find($running_id);
        if (empty($running) || $running['status'] != 0 || $running['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
		$this->assign('running', $running);
        $this->assign('payment', D('Payment')->getPayments_running(true));//新版跑腿支付
        $this->display();
    }
	 //去付款
	 public function pay2(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $running_id = (int) $this->_get('running_id');
        $running = D('Running')->find($running_id);
        if (empty($running) || $running['status'] != 0 || $running['user_id'] != $this->uid) {
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
			$need_pay = $running['need_pay'];//再更新防止篡改支付日志
			if(!empty($need_pay)){
				$logs = array(
					'type' => 'running', 
					'user_id' => $this->uid, 
					'order_id' => $running_id, 
					'code' => $code, 
					'need_pay' => $need_pay, 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'is_paid' => 0
				);
                $logs['log_id'] = D('Paymentlogs')->add($logs);
				$this->fengmiMsg('选择支付方式成功！下面请进行支付！', U('wap/payment/payment',array('log_id' => $logs['log_id'])));
			}else{
				$this->fengmiMsg('非法操作用');
			}
        }
    }
}
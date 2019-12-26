<?php
class AppointAction extends CommonAction {
	protected $Activitycates = array();
    public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['appoint'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $this->appointcates = D('Appointcate')->fetchAll();//分类表
        $this->assign('appointcates', $this->appointcates);
		$this->assign('areas', $areas = D('Area')->fetchAll());
		$this->assign('bizs', $biz = D('Business')->fetchAll());
		$this->assign('getcfg', $getCfg = D('Appoint')->getCfg());
		$this->assign('host',__HOST__);
    }
	
	public function index(){
        $cat = (int) $this->_param('cat');
        $this->assign('cat', $cat);

        $order = $this->_param('order', 'htmlspecialchars');
        $this->assign('order', $order);
		
		$area_id = (int) $this->_param('area_id');
        $this->assign('area_id', $area_id);

        $this->assign('nextpage', linkto('appoint/loaddata', array('cat'=>$cat,'area_id'=>$area_id,'order'=>$order,'t' => NOW_TIME, 'p' => '0000')));
        $this->display();
    }
	
	
    public function loaddata() {
        $Appoint = D('Appoint');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0,'audit' => 1,'city_id'=>$this->city_id, 'end_date' => array('EGT', TODAY));
		
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        //搜索二开结束
		$cates = D('Appointcate')->fetchAll();
        $cat = (int) $this->_param('cat');
        $cate_id = (int) $this->_param('cate_id');
        if ($cat) {
            if (!empty($cate_id)) {
                $map['cate_id'] = $cate_id;
            } else {
                $catids = D('Appointcate')->getChildren($cat);
                if (!empty($catids)) {
                    $map['cate_id'] = array('IN', $catids);
                }
            }
        }
        $this->assign('cat', $cat);
        $this->assign('cate_id', $cate_id);
		
        $area_id = (int) $this->_param('area_id');
        if ($area_id) {
            $map['area_id'] = $area_id;
        }
		
		$order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
         switch ($order) {
			case 4:
                $orderby = array('views' => 'asc');
                break;
            case 2:
                $orderby = array('yuyue_num' => 'asc');
                break;
            default:
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
                break;
        }
		$this->assign('order', $order);
		//搜索二开结束
        $count = $Appoint->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
		
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		
        $list = $Appoint->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
       
        $shop_ids = $cate_ids = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
				$cate_ids[$val['cate_id']] = $val['cate_id'];
            }
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
		if ($cate_ids) {
            $this->assign('appoint_cates', D('Appointcate')->itemsByIds($cate_ids));
        }
		
        $this->assign('list', $list); 
        $this->assign('page', $show);
        $this->display(); 
    }

	//家政技师列表
	public function worker_list(){
		$appoint_id = (int) $this->_param('appoint_id');
		if (!$detail = D('Appoint')->find($appoint_id)) {
            $this->error('该家政项目不存在！');die;
        }
		$this->assign('detail', $detail);
		$this->assign('appoint_id', $appoint_id);
        $order = $this->_param('order', 'htmlspecialchars');
        $this->assign('order', $order);
        $this->assign('nextpage', linkto('appoint/worker_list_load_data', array('order'=>$order,'appoint_id'=>$appoint_id,'t' => NOW_TIME, 'p' => '0000')));
        $this->display();
    }
	//家政列表加载页面
	public function worker_list_load_data() {
		$appoint_id = (int) $this->_param('appoint_id');
        $Appointworker = D('Appointworker');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0,'audit' => 1,'appoint_id' =>$appoint_id);
		$order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
         switch ($order) {
			case 3:
                $orderby = array('views' => 'asc');
                break;
            case 2:
                $orderby = array('create_time' => 'asc');
                break;
            default:
                $orderby = array('views' => 'asc');
                break;
        }
		$this->assign('order', $order);
        $count = $Appointworker -> where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Appointworker -> where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $appoint_ids = array();
        foreach ($list as $k => $val) {

             $appoint_ids[$val['appoint_id']] = $val['appoint_id'];
			 $list[$k]['score'] = $Appointworker->get_worker_score($val['worker_id']);
        }
        if ($appoint_ids) {
            $this->assign('appoints', D('Appoint')->itemsByIds($appoint_ids));
        }
        $this->assign('list', $list); 
        $this->assign('page', $show);
        $this->display(); 
    }
	
	//家政详情页
    public function detail($appoint_id) {
        $appoint_id = (int) $appoint_id;
		$Appoint = D('Appoint');
        $this->assign('cates', D('Appointcate')->fetchAll());
		if (!$detail = $Appoint->find($appoint_id)) {
            $this->error('该家政项目不存在！');
            die;
        }
		//预约判断
		$sign = D('Appointorder')->where(array('user_id' => $this->uid, 'appoint_id' => $appoint_id))->select();
        if (!empty($sign)) {
            $detail['sign'] = 1;
        } else {
            $detail['sign'] = 0;
        }
		
		$Appoint->updateCount($appoint_id, 'views');//更新浏览量
		$detail['thumb'] = unserialize($detail['thumb']);
		//修复点评开启
		$Appointdianping = D('Appointdianping');
		$pingnum = $Appointdianping->where(array('appoint_id' => $appoint_id))->count();
        $this->assign('pingnum', $pingnum);
        $score = (int) $Appointdianping->where(array('appoint_id' => $appoint_id))->avg('score');
        $this->assign('score', $score); 
		$this->assign('appointworker', $Appointworker = D('Appointworker')->take_out_Appoint_worker($detail['appoint_id']));//取出设计师
		$this->assign('shops', D('Shop')->find($detail['shop_id']));
		$this->assign('detail', $detail);
        $this->display();

    }
	//取出单个技师详情页
	 public function worker($worker_id) {
        $worker_id = (int) $worker_id;
		$Appointworker = D('Appointworker');
		if (!$detail = $Appointworker->find($worker_id)) {
            $this->error('该技师不存在！');
            die;
        }
		$Appointworker ->updateCount($worker_id, 'views');//更新技师浏览量
		$detail['thumb'] = unserialize($detail['thumb']);
		$Appointdianping = D('Appointdianping');//修复点评开启
		$pingnum = $Appointdianping->where(array('appoint_id' =>$detail['appoint_id'],'worker_id' => $worker_id))->count();
        $this->assign('pingnum', $pingnum);
        $score = (int) $Appointdianping->where(array('worker_id' => $worker_id,'closed' => 0, 'show_date' => array('ELT', TODAY)))->avg('score');
        $this->assign('score', $score); 
		$this->assign('Appoint', D('Appoint')->find($detail['Appoint_id']));
		$this->assign('detail', $detail);
        $this->display();

    }
	//家政点评详情
	public function workerdianping(){
        $worker_id = (int) $this->_get('worker_id');
        if (!($detail = D('Appointworker')->find($worker_id))) {
            $this->error('没有该技师');
            die;
        }
        if ($detail['closed']) {
            $this->error('点评政已经被删除');
            die;
        }
		$this->assign('worker_id', $worker_id);
        $this->assign('next', LinkTo('appoint/workerdianpingloading', array('worker_id' => $worker_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }
	//技师点评详情
	public function workerdianpingloading(){
        $worker_id = (int) $this->_get('worker_id');
        if (!($detail = D('Appointworker')->find($worker_id))) {
            die('0');
        }
        if ($detail['closed']) {
            die('0');
        }
        $Appointdianping = D('Appointdianping');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'worker_id' => $worker_id, 'show_date' => array('ELT', TODAY));
        $count = $Appointdianping->where($map)->count();
        $Page = new Page($count, 5);
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Appointdianping->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $appoint_ids = $worker_ids = $order_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $appoint_ids[$val['appoint_id']] = $val['appoint_id'];
			$worker_ids[$val['worker_id']] = $val['worker_id'];
			$order_ids[$val['order_id']] = $val['order_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
		if (!empty($worker_ids)) {
            $this->assign('workers', D('Appointworker')->itemsByIds($worker_ids));
        }
        if (!empty($order_ids)) {
            $this->assign('pics', D('Appointdianpingpics')->where(array('order_id' => array('IN', $order_ids)))->select());
        }
        $this->assign('totalnum', $count);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	//技师点评图片详情
    public function workerimg(){
        $dianping_id = (int) $this->_get('dianping_id');
        if (!($detail = D('Appointdianping')->where(array('dianping_id'=>$dianping_id))->find())){
            $this->error('没有该点评');
            die;
        }
        if ($detail['closed']) {
            $this->error('该点评已经被删除');
            die;
        }
        $list =  D('Appointdianpingpics')->where(array('order_id' =>$detail['order_id']))->select();
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	
	
	public function dianping(){
        $appoint_id = (int) $this->_get('appoint_id');
        if (!($detail = D('Appoint')->find($appoint_id))) {
            $this->error('没有该家政');
            die;
        }
        if ($detail['closed']) {
            $this->error('该家政已经被删除');
            die;
        }
        $this->assign('next', LinkTo('appoint/dianpingloading', array('appoint_id' => $appoint_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }
    public function dianpingloading(){
        $appoint_id = (int) $this->_get('appoint_id');
        if (!($detail = D('Appoint')->find($appoint_id))) {
            die('0');
        }
        if ($detail['closed']) {
            die('0');
        }
        $Appointdianping = D('Appointdianping');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'appoint_id' => $appoint_id, 'show_date' => array('ELT', TODAY));
        $count = $Appointdianping->where($map)->count();
        $Page = new Page($count, 5);
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Appointdianping->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $appoint_ids = $worker_ids = $order_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $appoint_ids[$val['appoint_id']] = $val['appoint_id'];
			$worker_ids[$val['worker_id']] = $val['worker_id'];
			$order_ids[$val['order_id']] = $val['order_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
		if (!empty($worker_ids)) {
            $this->assign('workers', D('Appointworker')->itemsByIds($worker_ids));
        }
        if (!empty($order_ids)) {
            $this->assign('pics', D('Appointdianpingpics')->where(array('order_id' => array('IN', $order_ids)))->select());
        }
        $this->assign('totalnum', $count);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	//家政总点评图片详情
    public function img(){
        $dianping_id = (int) $this->_get('dianping_id');
        if (!($detail = D('Appointdianping')->where(array('dianping_id'=>$dianping_id))->find())){
            $this->error('没有该点评');
            die;
        }
        if ($detail['closed']) {
            $this->error('该点评已经被删除');
            die;
        }
        $list =  D('Appointdianpingpics')->where(array('order_id' =>$detail['order_id']))->select();
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	public function yuyue($appoint_id) {
		$appoint_id = (int) $appoint_id;
		$worker_id = (int) $this->_param('worker_id');
        $this->assign('worker_id', $worker_id);
		if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
        }
        if (!($detail = D('Appoint')->find($appoint_id))) {
            $this->error('该家政项目不存在！');
            die;
        }
		$this->assign('appointworker', $Appointworker = D('Appointworker')->take_out_Appoint_worker($appoint_id));//取出设计师
		$this->assign('cates', $this->appointcates); 
        $this->assign('detail', $detail);
        $this->display();
	 }
    public function create($appoint_id) {
		if (empty($this->uid)) {
            $this->fengmiMsg('请登录后预约', U('passport/login'));
        }
        if (!$appoint_id = (int) $appoint_id) {
            $this->fengmiMsg('服务类型不能为空');
        }		
		
		$cate_id = D('Appoint')->find($appoint_id);
        if (!isset($this->appointcates[$cate_id['cate_id']])) {
            $this->fengmiMsg('暂时没有该服务类型');
        }
		if (false == D('Shop')->check_shop_user_id($cate_id['shop_id'],$this->uid)) {//判断
			$this->fengmiMsg('不能预约自己的家政');
		}
		//先判断余额
		if ($this->member['money'] < $cate_id['price']){
			$this->fengmiMsg('抱歉，您的余额不足',U('user/money/index'));
		}
		
		$appoint_shop = D('Shop')->find($cate_id['shop_id']);//商家信息
		$appoint_shop_user = D('Users')->find($appoint_shop['user_id']);//商家信息
		$data['city_id'] = $this->city_id;
		$data['appoint_id'] = $appoint_id;
		$data['user_id'] = (int) $this->uid;
        $data['cate_id'] = $cate_id['cate_id'];
		$data['shop_id'] = $appoint_shop['shop_id'];
        $data['date'] = htmlspecialchars($_POST['date']);
        $data['time'] = htmlspecialchars($_POST['time']);
		
        if(empty($data['date'])|| empty($data['time'])){
            $this->fengmiMsg('服务时间不能为空');
        }
        $data['svctime'] = $data['date'].  " " . $data['time']; 
		
		//判断时间是否过期
		$svctime = $data['date'].' '.$data['time'];
		$appoint_time = strtotime($svctime);
		if (empty($data['time'])) { 
            $this->fengmiMsg('请选择时间');
        }else if($appoint_time < time()){
			$this->fengmiMsg('预约时间已经过期，请选择正确的时间');
		}
		//判断时间过期结束
		$data['worker_id'] = intval($_POST['worker_id']);
        if (!$data['addr'] = $this->_post('addr', 'htmlspecialchars')) {
            $this->fengmiMsg('服务地址不能为空');
        }
        if (!$data['name'] = $this->_post('name', 'htmlspecialchars')) {
            $this->fengmiMsg('联系人不能为空');
        }
        if (!$data['tel'] = $this->_post('tel', 'htmlspecialchars')) {
            $this->fengmiMsg('联系电话不能为空');
        }
        if (!isMobile($data['tel']) && !isPhone($data['tel'])) {
            $this->fengmiMsg('电话号码不正确');
        }
		$data['need_pay'] = $cate_id['price'];
		$data['status'] = 0;//购买，后期增加退
        $data['contents'] = $this->_post('contents', 'htmlspecialchars');
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();			
        if ($order_id = D('Appointorder')->add($data)) {
			$this->fengmiMsg('恭喜您预约家政服务成功，正在为您跳转付款！', U('appoint/pay', array('order_id' => $order_id)));
        }
        $this->fengmiMsg('服务器繁忙');
    }
	
	//家政直接支付页面
	 public function pay(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Appointorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
		$this->assign('order', $order);
		$this->assign('appointworker', $Appointworker = D('Appointworker')->find($order['worker_id']));
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->display();
    }
	//去付款
	 public function pay2(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Appointorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
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
            $logs = D('Paymentlogs')->getLogsByOrderId('appoint', $order_id);//查找日志
			$need_pay = $order['need_pay'];//再更新防止篡改支付日志
            if (empty($logs)) {//独家再更新
                $logs = array(
					'type' => 'appoint', 
					'user_id' => $this->uid, 
					'order_id' => $order_id, 
					'code' => $code, 
					'need_pay' => $need_pay, 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'is_paid' => 0
				);
                $logs['log_id'] = D('Paymentlogs')->add($logs);
            } else {
                $logs['need_pay'] = $need_pay;
                $logs['code'] = $code;
                D('Paymentlogs')->save($logs);
            }
					
			if (false == D('Appointorder')->updateCount_yuyue_num($order_id)) {//更新什么什么的
				$this->fengmiMsg('更新购买信息出错');
			}else{
				$this->fengmiMsg('选择支付方式成功！下面请进行支付！', U('payment/payment',array('log_id' => $logs['log_id'])));
			}
            
        }
    }
}


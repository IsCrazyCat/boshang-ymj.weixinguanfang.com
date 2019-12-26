<?php
class CloudAction extends CommonAction{
    protected $types = array( );
    public function _initialize( ){
        parent::_initialize( );
		if ($this->_CONFIG['operation']['cloud'] == 0) {
				$this->error('此功能已关闭');die;
		}
        $this->types = D( "Cloudgoods" )->getType( );
        $this->assign( "types", $this->types );
    }

    public function index( ){
        $goods = D( "Cloudgoods" );
        import( "ORG.Util.Page" );
        $map = array( "audit" => 1, "closed" => 0 );
        $type = ( integer )$this->_param( "type" );
        if ( !empty( $type ) ){
            $map['type'] = $type;
            $this->assign( "type", $type );
        }
        if ( $area_id = ( integer )$this->_param( "area_id" ) ){
            $map['area_id'] = $area_id;
            $this->assign( "area_id", $area_id );
        }
        if ( $keyword = $this->_param( "keyword", "htmlspecialchars" ) ){
            $map['title|intro'] = array("LIKE","%".$keyword."%");
            $this->assign( "keyword", $keyword );
        }
        $count = $goods->where( $map )->count( );
        $Page = new Page ( $count, 25 );
        $show = $Page->show( );
        $list = $goods->where( $map )->order( array( "goods_id" => "desc" ) )->limit( $Page->firstRow.",".$Page->listRows )->select( );
        $this->assign( "list", $list );
        $this->assign( "page", $show );
        $this->display( );
    }

    public function cloudbuy( ){
        if ( empty( $this->uid )){
            $this->ajaxReturn( array( "status" => "login" ) );
        }
        $goods_id = ( integer )$_POST['goods_id'];
        $detail = D( "Cloudgoods" )->find( $goods_id );
        if ( empty( $detail ) ){
            $this->ajaxReturn( array( "status" => "error", "msg" => "该云购商品不存在" ) );
        }
        $obj = D( "Cloudgoods" );
        $logs = D( "Cloudlogs" );
        if ( IS_AJAX ){
            $num = ( integer )$_POST['num'];
            if ( empty( $num ) ){
                $this->ajaxReturn( array( "status" => "error", "msg" => "数量不能为空" ) );
            }
            if ( $num < $this->types[$detail['type']]['num'] || $num % $this->types[$detail['type']]['num'] != 0 ){
                $this->ajaxReturn( array( "status" => "error", "msg" => "数量不正确" ) );
            }
            $count = $logs->where( array("goods_id" => $goods_id,"user_id" => $this->uid))->sum( "num" );
            $left = $detail['max'] - $count;
            $lefts = $detail['price'] - $detail['join'];
            $left <= $lefts ? ( $limit = $left ) : ( $limit = $lefts );
            if ( $limit < $num ){
                $this->ajaxReturn( array("status" => "error","msg" => "您最多能购买".$limit."人次"));
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

    public function detail($goods_id = 0) {
        if ( $goods_id = (integer )$goods_id){
            $obj = D( "Cloudgoods" );
            if (!($detail = $obj->find( $goods_id))){
                $this->error( "没有该商品" );
            }
            $thumb = unserialize( $detail['thumb'] );
            $count = D( "Cloudlogs" )->where( array("goods_id" => $goods_id,"user_id" => $this->uid))->sum( "num" );
            $left = $detail['max'] - $count;
            $cloudlogs = d( "Cloudlogs" );
            $map = array("goods_id" => $goods_id);
            $list = $cloudlogs->where( $map )->order( array( "log_id" => "asc" ) )->select( );
            $lists = $obj->get_datas( $list );
            $listss = $user_ids = array( );
            foreach ( $lists as $k => $val ){
                $user_ids[$val['user_id']] = $val['user_id'];
                $listss[date( "Y-m-d", $val['create_time'] )][date( "H:i:s", $val['create_time'] ).".".$val['microtime']][] = $val;
            }
            krsort( $listss );
            foreach ( $listss as $k => $val ){
                krsort( $listss[$k] );
            }
            $this->assign( "users", d( "Users" )->itemsByIds($user_ids));
            $this->assign( "list", $listss );
            $this->assign( "left", $left );
            $this->assign( "thumb", $thumb );
            $this->assign( "detail", $detail );
            if ( $detail['status'] == 1 ){
                redirect(U( "cloud/zhong", array("goods_id" => $goods_id)));
            }
            else{
                $this->display( );
            }
        }
        else{
            $this->error( "没有该商品" );
        }
    }

    public function zhong( $goods_id ){
        if ( $goods_id = ( integer )$goods_id ){
            $obj = D( "Cloudgoods" );
            if ( !( $detail = $obj->find( $goods_id ) ) ){
                $this->error( "没有该商品" );
            }
            if ( $detail['status'] != 1 || empty( $detail['win_number'] ) || empty( $detail['win_user_id'] ) ) {
                $this->error( "该商品还未开奖" );
            }
            $cloudlogs = D( "Cloudlogs" );
            $map = array("goods_id" => $goods_id);
            $list = $cloudlogs->where( $map )->order( array( "log_id" => "asc" ) )->select( );
            $lists = $obj->get_datas( $list );
            $listss = $user_ids = array( );
            foreach ( $lists as $k => $val ){
                $user_ids[$val['user_id']] = $val['user_id'];
                $listss[date( "Y-m-d", $val['create_time'] )][date( "H:i:s", $val['create_time'] ).".".$val['microtime']][] = $val;
            }
            krsort( $listss );
            foreach ( $listss as $k => $val ){
                krsort( $listss[$k] );
            }
            $this->assign( "users", d( "Users" )->itemsByIds( $user_ids ) );
            $this->assign( "list", $listss );
            $win_list = $cloudlogs->where( $map )->order( array( "log_id" => "asc" ) )->select( );
            $win_lists = $obj->get_datas( $win_list );
            $win_listss = array( );
            foreach ( $win_lists as $k => $val ){
                if ( $val['user_id'] == $detail['win_user_id'] ){
                    $win_listss[date( "Y-m-d H:i:s", $val['create_time'] ).".".$val['microtime']][] = $val;
                }
            }
            $this->assign( "lists", $win_listss );
            $total = $cloudlogs->where( array( "goods_id" => $goods_id,"user_id" => $detail['win_user_id']))->sum("num");
            $return = $obj->get_last50_time( $list );
            $this->assign( "return", $return );
            $this->assign( "total", $total );
            $u_list = array( );
            foreach ( $win_lists as $k => $val ) {
                if ( $val['user_id'] == $detail['win_user_id'] ){
                    $u_list[] = $val;
                }
            }
            $this->assign( "u_list", $u_list );
            $this->assign( "detail", $detail );
            $this->display( );
        }
        else{
            $this->error( "没有该商品" );
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
            $this->baoError('该订单不存在');
            die;
        }
        if (!($code = $this->_post('code'))) {
            $this->baoError('请选择支付方式！');
        }
        if ($code == 'wait') {
             $this->baoError('暂不支持货到付款，请重新选择支付方式');
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->baoError('该支付方式不存在，请稍后再试试');
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
					$this->baoJump(U('payment/payment', array('log_id' => $logs['log_id'])));
				}else{
					$this->baoError('写入支付日志表失败');
				}
			}else{
				$this->baoError('非法操作');
			}
        }
    }

}

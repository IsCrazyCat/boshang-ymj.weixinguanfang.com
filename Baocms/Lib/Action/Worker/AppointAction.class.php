<?php
class AppointAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        if ($this->workers['is_appoint'] != 1) {
            $this->error('对不起，您无权限，请联系掌柜开通');
        }
    }
    public function index(){
		$st = (int) $this->_param('st');
		$this->assign('st', $st);
        $this->display();
    }
   public function loaddata() {
        $Appoint = D('Appoint');
        $Appointorder = D('Appointorder');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id,'closed'=>0);
		$st = (int) $this->_param('st');
		if ($st == 1) {
			$map['status'] = 1;
		}elseif ($st == 2) {
			$map['status'] = 2;
		}elseif ($st == 3) {
			$map['status'] = 3;
		}elseif ($st == 4) {
			$map['status'] = 4;
		}elseif ($st == 8) {
			$map['status'] = 8;
		}else{
			$map['status'] = 0;
		}
        $count = $Appointorder->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Appointorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $appoint_ids = array();
        foreach ($list as $k => $val) {
            $appoint_ids[$val['appoint_id']] = $val['appoint_id'];
        }
        $this->assign('appoints', $Appoint->itemsByIds($appoint_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	//家政详情
	public function detail($order_id){
        if(!$order_id = (int)$order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('Appointorder')->find($order_id)){
            $this->error('该订单不存在');
        }elseif($detail['shop_id'] != $this->shop_id){
            $this->error('非法的订单操作');
        }else{
           $Appoint = D('Appoint')->find($detail['appoint_id']);
		   $this->assign('appoint',$Appoint);
		   
		   $Appointworker = D('Appointworker')->find($detail['worker_id']);
		   $this->assign('appointworker',$Appointworker);
		   
           $this->assign('detail',$detail);
           $this->display();
        }
    }
	
   //管理员取消订单
    public function cancel($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Appointorder');
            if (!($detial = $obj->find($order_id))) {
                $this->fengmiMsg('该订单不存在');
            }elseif(false == D('Appointorder')->Appoint_order_Distribution($order_id,$type =0)){
				$this->fengmiMsg('检测到家政配送状态有误');
			}elseif($appoint_order['status'] != 0 ||$appoint_order['status'] != 4){
				$this->fengmiMsg('该订单暂时不能取消');
			}elseif($detail['shop_id'] != $this->shop_id){
				$this->fengmiMsg('请不要操作他人的订单');
			}else{
				if ($obj->save(array('order_id' => $order_id, 'closed' => 1))) {
					$this->fengmiMsg('您已成功删除家政订单', U('appoint/index', array('st' => 1)));
				}else{
					$this->fengmiMsg('操作失败');
				}
			}
		}
    }

	//接单
    public function confirm(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Appointorder = D('Appointorder');
        $appoint_order = $Appointorder->where('order_id =' . $order_id)->find();
		if (!($detial = $Appointorder->find($order_id))) {
                $this->fengmiMsg('该订单不存在');
        }elseif($appoint_order['status'] != 1){
				$this->fengmiMsg('订单状态不正确，但是无法发货');
		}elseif($detial['shop_id'] != $this->shop_id){
				$this->fengmiMsg('请不要操作其他商铺的订单');
		}else{
			if ($Appointorder->save(array('order_id' => $order_id, 'status' => 2))) {
				D('Weixintmpl')->weixin_shop_delivery_user($order_id,$this->uid,3);//发货通知买家接口，1外卖，2商城，3家政
				$this->fengmiMsg('您已成功接单', U('appoint/index', array('st' => 2)));
			}else{
				$this->fengmiMsg('操作失败');
			}
		}
		
    }
	
	
				
				
	//同意退款操作
    public function agree_refund(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Appointorder = D('Appointorder');
        $appoint_order = $Appointorder->where('order_id =' . $order_id)->find();
		if (!($detial = $Appointorder->find($order_id))) {
                $this->fengmiMsg('该订单不存在');
        }elseif($appoint_order['status'] != 3){
				$this->fengmiMsg('订单状态不正确，无法退款');
		}elseif($detial['shop_id'] != $this->shop_id){
				$this->fengmiMsg('请不要操作其他商铺的订单');
		}else{
			if (false == $Appointorder->refund_user($order_id)) {//退款操作
				$this->fengmiMsg('非法操作');
			}else{
				$this->fengmiMsg('已成功退款',U('appoint/index', array('st' => 4)));	
			}
		}
    }
	
}
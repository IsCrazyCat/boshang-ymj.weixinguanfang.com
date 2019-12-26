<?php
class AppointAction extends CommonAction{
    public function index(){
        $Appoint = D('Appoint');
        $Appointorder = D('Appointorder');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid,'closed'=>0);
        $count = $Appointorder->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Appointorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $worker_ids = $appoint_ids = array();
        foreach ($list as $k => $val) {
            $appoint_ids[$val['appoint_id']] = $val['appoint_id'];
			$worker_ids[$val['worker_id']] = $val['worker_id'];
        }
        $this->assign('appoint', $Appoint->itemsByIds($appoint_ids));
		$this->assign('worker', D('Appointworker')->itemsByIds($worker_ids));
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
        }elseif($detail['user_id'] != $this->uid){
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
	
   //删除订单重做
    public function orderdel($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Appointorder');
            if (!($detial = $obj->find($order_id))) {
                $this->baoError('该订单不存在');
            }elseif($detial['status'] != 0 && $detial['status'] != 4){
				$this->baoError('该订单暂时不能取消');
			}elseif($detial['user_id'] != $this->uid){
				$this->baoError('请不要操作他人的订单');
			}else{
				if ($obj->save(array('order_id' => $order_id, 'closed' => 1))) {
					$this->baoSuccess('您已成功删除家政订单', U('appoint/index', array('st' => 1)));
				}else{
					$this->baoError('操作失败');
				}
			}
		}
    }
	//家政申请退款
	public function refund(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Appointorder = D('Appointorder');
        $appoint_order = $Appointorder->where('order_id =' . $order_id)->find();

        if (!$appoint_order) {
            $this->baoError('未检测到ID');
        }else{
            if ($appoint_order['user_id'] != $this->uid) {
                $this->baoError('请不要操作他人的订单');
            }elseif($appoint_order['status'] != 1){
				 $this->baoError('当前订单状态不永许这样操作');
			}else{
				$Appointorder->where('order_id =' . $order_id)->setField('status', 3);
				D('Weixintmpl')->weixin_user_refund_shop($order_id,3);//家政申请退款，传订单ID跟类型
           		$this->baoSuccess('申请退款成功！', U('appoint/index', array('st' => 3)));
			}
        }		
    }
	//取消家政申请退款订单
    public function cancel_refund(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Appointorder = D('Appointorder');
        $appoint_order = $Appointorder->where('order_id =' . $order_id)->find();
        if (!$appoint_order) {
            $this->baoError('操作错误！');
        } else {
            if ($appoint_order['user_id'] != $this->uid) {
                $this->baoError('请不要操作他人的订单');
            }elseif($appoint_order['status'] != 3){
				 $this->baoError('订单状态不正确');
			}else{
				$Appointorder->where('order_id =' . $order_id)->setField('status', 1);
				D('Weixintmpl')->weixin_delete_order_shop($order_id,3);//家政取消订单，传订单ID跟类型
				$this->baoSuccess('家政取消退款成功！',U('appoint/index', array('st' => 2)));
			}
			
        }
    }
	
	//用户确认订单完成
    public function confirm_complete(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Appointorder = D('Appointorder');
        $appoint_order = $Appointorder->where('order_id =' . $order_id)->find();
        if (!$appoint_order) {
            $this->baoError('操作错误！');
        } else {
            if ($appoint_order['user_id'] != $this->uid) {
                $this->baoError('请不要操作他人的订单');
            }elseif($appoint_order['status'] != 2){
				 $this->baoError('当前订单状态不永许这样操作');
			}else{
				if (false == D('Appointorder')->appoint_settlement($order_id)) {//确认订单去把余额返回给商家
					$this->baoError('非法操作');
				}else{
					$this->baoSuccess('您已成功确认订单，请给我们评价下吧',U('appoint/index', array('st' => 8)));	
				}
				
			}
			
        }
    }
	
	 //家政点评
	 public function comment($order_id) {
        if(!$order_id = (int) $order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('Appointorder')->find($order_id)){
            $this->error('该订单不存在');
        }elseif($detail['user_id'] != $this->uid){
            $this->error('非法操作订单');
        }elseif($detail['comment_status'] == 1){
            $this->error('已经评价过了');
        }else{
            if ($this->_Post()) {
                $data = $this->checkFields($this->_post('data', false), array('score', 'contents'));
                $data['user_id'] = $this->uid;
                $data['appoint_id'] = $detail['appoint_id'];
                $data['order_id'] = $order_id;
                $data['score'] = (int) $data['score'];
                if (empty($data['score'])) {
                    $this->baoError('评分不能为空');
                }
                if ($data['score'] > 5 || $data['score'] < 1) {
                    $this->baoError('评分为1-5之间的数字');
                }
                $data['contents'] = htmlspecialchars($data['contents']);
                if (empty($data['contents'])) {
                    $this->baoError('评价内容不能为空');
                }
                if ($words = D('Sensitive')->checkWords($data['contents'])) {
                    $this->baoError('评价内容含有敏感词：' . $words);
                }
				$data['show_date'] = date('Y-m-d', NOW_TIME + ($this->_CONFIG['mobile']['data_appoint_dianping'] * 86400));
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                $photos = $this->_post('photos', false);
                if ($dianping_id = D('Appointdianping')->add($data)) {
                    $local = array();
                    foreach ($photos as $val) {
                        if (isImage($val))
                            $local[] = $val;
                    }
                    if (!empty($local)){
                        foreach($local as $k=>$val){
                            D('Appointdianpingpics')->add(array('dianping_id'=>$dianping_id,'order_id'=>$order_id,'pic'=>$val));
                        }
                    }
                    D('Appointorder')->save(array('order_id'=>$order_id,'comment_status'=>1));
                    D('Users')->updateCount($this->uid, 'ping_num');
                    $this->baoSuccess('恭喜您点评成功!', U('appoint/index'));
                }
                $this->baoError('点评失败！');
            }else {
                $this->assign('detail', $detail);
                $this->assign('appoint',D('Appoint')->find($detail['appoint_id']));
                $this->display();
            }
        }
    }
}
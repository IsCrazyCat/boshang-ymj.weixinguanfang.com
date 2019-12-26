<?php


class FarmAction extends CommonAction {
	protected function _initialize(){
       parent::_initialize();
        if ($this->_CONFIG['operation']['farm'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
  
    public function index() {
        $st = (int) $this->_param('st');
		$this->assign('st', $st);
		$this->display(); // 输出模板
    }
    
    public function loaddata() {
		$farmorder = D('FarmOrder');
		import('ORG.Util.Page'); 
		$map = array('user_id' => $this->uid); 
		$st = (int) $this->_param('st');
		if ($st == 1) { 
			$map['order_status'] = array('in',array(-1,2));
		}elseif ($st == 0) {    //进行中
			$map['order_status'] = array('in',array(0,1));
		}else{  //进行中
			$map['order_status'] = array('in',array(0,1));
		}
		$count = $farmorder->where($map)->count(); 
        
		$Page = new Page($count, 10); 
		$show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
            die('0');
		}
		$list = $farmorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $v){
            if($f = D('Farm')->where(array('farm_id'=>$v['farm_id']))->find()){
                $list[$k]['farm'] = $f;
            }
        }
		$this->assign('list', $list); 
		$this->assign('page', $show); 
		$this->display(); 
	}

    
    public function detail($order_id){
        if(!$order_id = (int)$order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('FarmOrder')->find($order_id)){
            $this->error('该订单不存在');
        }elseif($detail['user_id'] != $this->uid){
            $this->error('非法的订单操作');
        }else{
           $detail['package'] = D('FarmPackage')->where(array('pid'=>$detail['pid']))->find(); 
           $detail['farm'] = D('Farm')->where(array('farm_id'=>$detail['farm_id']))->find();
           $this->assign('detail',$detail);
           $this->display();
        }
    }

    
   public function cancel($order_id){
       if(!$order_id = (int)$order_id){
           $this->error('订单不存在');
       }elseif(!$detail = D('Hotelorder')->find($order_id)){
           $this->error('订单不存在');
       }elseif($detail['user_id'] != $this->uid){
           $this->error('非法操作订单');
       }else{
           if(false !== D('Hotelorder')->cancel($order_id)){
               $this->success('订单取消成功');
           }else{
               $this->error('订单取消失败');
           }
       }
   }
   
   public function comment($order_id) {
        if(!$order_id = (int) $order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('FarmOrder')->find($order_id)){
            $this->error('该订单不存在');
        }elseif($detail['user_id'] != $this->uid){
            $this->error('非法操作订单');
        }elseif($detail['comment_status'] == 1){
            $this->error('已经评价过了');
        }else{
            if ($this->_Post()) {
                $data = $this->checkFields($this->_post('data', false), array('score', 'content'));
                $data['user_id'] = $this->uid;
				if (!$Farm = D('Farm')->find($detail['farm_id'])) {
                    $this->fengmiMsg('没有找到对应的农家乐，暂时无法点评，请稍后再试');
                }
				$data['shop_id'] = $Farm['shop_id'];
                $data['farm_id'] = $detail['farm_id'];
                $data['order_id'] = $order_id;
                $data['score'] = (int) $data['score'];
                if (empty($data['score'])) {
                    $this->baoError('评分不能为空');
                }
                if ($data['score'] > 5 || $data['score'] < 1) {
                    $this->baoError('评分为1-5之间的数字');
                }
                $data['content'] = htmlspecialchars($data['content']);
                if (empty($data['content'])) {
                    $this->baoError('评价内容不能为空');
                }
                if ($words = D('Sensitive')->checkWords($data['content'])) {
                    $this->baoError('评价内容含有敏感词：' . $words);
                }
				$data['show_date'] = date('Y-m-d', NOW_TIME + ($this->_CONFIG['mobile']['data_fram_dianping'] * 86400));
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                $photos = $this->_post('photos', false);
                if($photos){
                    $data['have_photo'] = 1;
                }
                if ($comment_id = D('FarmComment')->add($data)) {
                    $local = array();
                    foreach ($photos as $val) {
                        if (isImage($val))
                            $local[] = $val;
                    }
                    if (!empty($local)){
                        foreach($local as $k=>$val){
                            D('FarmCommentPics')->add(array('comment_id'=>$comment_id,'photo'=>$val));
                        }
                    }
                    D('FarmOrder')->save(array('order_id'=>$order_id,'comment_status'=>1));
                    D('Users')->updateCount($this->uid, 'ping_num');
                    D('Farm')->updateCount($detail['farm_id'],'comments');
                    D('Farm')->updateCount($detail['farm_id'],'score',$data['score']);
                    $this->baoSuccess('恭喜您点评成功!'.$comment_id, U('mcenter/farm/index'));
                }
                $this->baoError('点评失败！');
            }else {
                $detail['package'] = D('FarmPackage')->where(array('pid'=>$detail['pid']))->find();
                $detail['farm'] = D('Farm')->where(array('farm_id'=>$detail['farm_id']))->find();
                $this->assign('detail', $detail);
                $this->display();
            }
        }
    }

}

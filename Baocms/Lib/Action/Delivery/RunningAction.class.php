<?php

class RunningAction extends CommonAction {
	
	public function _initialize() {
        parent::_initialize();
		$this->assign('areas', $areas = D('Area')->fetchAll());
		$this->assign('bizs', $biz = D('Business')->fetchAll());
		
    }
	
	//跑腿众包开始
	public function index( ){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->assign('nextpage', LinkTo('running/loaddata', array('aready' => $aready, 't' => NOW_TIME,'p' => '0000')));
        $this->display(); // 输出模板
	
	
	}
	public function loaddata( ){
        if (empty($this->delivery_id)){
            header('Location:'.U('index/index'));
        }
        $cid = $this->delivery_id; //获取配送员ID
        $running = D('Running');
		import('ORG.Util.Page'); // 导入分页类 
        $map = array();
        if (isset($_GET['aready']) || isset($_POST['aready'])) {
			$aready = (int) $this->_param('aready');
			if ($aready != 999) {
				$map['status'] = $aready;
			}
			$this->assign('aready', $aready);
		} else {
			$this->assign('aready', 999);
		}
			
		$count = $running->where($map)->count(); 
		$Page = new Page($count, 10); 
		$show = $Page->show(); 
	
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
			die('0');
		}
			
        $lat = addslashes( cookie('lat' ) );
        $lng = addslashes( cookie('lng' ) );
        if ( empty( $lat ) || empty( $lng ) ){
           $lat = $this->city['lat'];
           $lng = $this->city['lng'];
        }
        $orderby = "(ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc";
		
        $list = $running->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ( $list as $k => $val ){
          $list[$k]['d'] = getdistance( $lat, $lng, $val['lat'], $val['lng'] );
        }
        $this->assign('list', $list);
		$this->assign('page', $show); 
        $this->display( );
    }
	
	//跑腿众包结束
	
	public function state($running_id){
        $running_id = (int) $running_id;
        if (empty($running_id) || !($detail = D("Running")->find($running_id))) {
            $this->error("该跑腿不存在");
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
		$thumb = unserialize($detail['thumb']);
        $this->assign('thumb', $thumb);
		$this->assign('deliverys', D('Delivery')->where(array('user_id'=>$detail['cid']))->find());
        $this->assign("detail", $detail);
        $this->display();
    }
	
   //强跑腿开始
	public function qiang(){
        if ( IS_AJAX ){
            $running_id = i('running_id', 0,'trim,intval');
            $running = D('Running');
            if (empty($this->delivery_id)){
                $this->ajaxReturn(array('status' =>'error','message' =>'您还没有登录或登录超时!' ));
            }
            else{
                $detail = $running->find($running_id);
                if (!$detail){
                    $this->ajaxReturn(array('status' =>'error','message' =>'跑腿不存在!'));
                }
                if($detail['status'] != 1 || $detail['closed'] != 0){
                    $this->ajaxReturn( array('status' =>'error','message' =>'该跑腿状态不支持抢单!'));
                }
                $cid = $this->delivery_id; //获取配送员ID
				$runnin = $running->Running_Confirm_Complete($running_id,$cid);
				if($running->Running_Confirm_Complete($running_id,$cid)){
					$this->ajaxReturn(array('status' =>'success','message' =>'恭喜您！接单成功！请尽快进行配送！'));
				}else{
                   $this->ajaxReturn(array('status' =>'error','message' =>'接单失败！错误！'));
                }
				
				
            }
        }
    }

	//跑腿确认
	public function running_ok(){
        if (IS_AJAX){
            $running_id = i('running_id', 0,'trim,intval');
            $running = D('Running');
            if (empty($this->delivery_id)){
                $this->ajaxReturn( array('status' =>'error','message' =>'您还没有登录或登录超时!' ));
            }
            else{
                $detail = $running->find($running_id);
                if (!$detail){
                    $this->ajaxReturn( array('status' =>'error','message' =>'跑腿不存在!' ));
                }
                if ($detail['status'] != 2 || $detail['closed'] != 0){
                    $this->ajaxReturn( array('status' =>'error','message' =>'该跑腿状态不能完成!' ));
                }
                $cid = $this->delivery_id; //获取配送员ID
                if ( $detail['cid'] != $cid ){
                    $this->ajaxReturn( array('status' =>'error','message' =>'不能操作别人的跑腿!' ));
                }
				if (false === $running->Running_Confirm_Complete($running_id,$cid)){
					$this->ajaxReturn( array('status' =>'success','message' =>'恭喜您完成订单' ) );
				}else{
                    $this->ajaxReturn( array('status' =>'error','message' =>'操作失败！' ) );
                }
               
            }
        }
    }
	//跑腿确认结束
}
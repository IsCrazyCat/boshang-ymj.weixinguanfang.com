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
        $F = D('FarmOrder'); 
        $gotime = I('gotime','','trim');
        $order_id = I('order_id',0,'trim,intval');
        $map = array();
        $map['user_id'] = $this->uid;
        if($gotime){
            $gotime = strtotime($gotime);
            $map['gotime']  = array('between',array($gotime,$gotime+86399));
        }
        if($order_id){
            $map['order_id'] = $order_id;
        }
        import('ORG.Util.Page');
 
        $count  = $F->where($map)->count();
        $Page   = new Page($count,25); 
        $show   = $Page->show();
        $list = $F->where($map)->order('order_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        
        foreach($list as $k => $v){
            $farm = D('Farm')->where(array('farm_id'=>$v['farm_id']))->find();
            $list[$k]['farm'] = $farm;
        }

        $this->assign('list',$list);
        $this->assign('page',$show);
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
           $detail['package'] = D('HotelPackage')->where(array('pid'=>$detail['pid']))->find();
           $detail['farm'] = D('Farm')->where(array('farm_id'=>$detail['farm_id']))->find();
           print
           $this->assign('detail',$detail);
           $this->display();
        } 
    }
    

    public function cancel($order_id){
       if(!$order_id = (int)$order_id){
           $this->baoError('订单不存在');
       }elseif(!$detail = D('FarmOrder')->find($order_id)){
           $this->baoError('订单不存在');
       }elseif($detail['user_id'] != $this->uid){
           $this->baoError('非法操作订单');
       }else{
           if(false !== D('FarmOrder')->cancel($order_id)){
               $this->baoSuccess('订单取消成功',U('farm/detail',array('order_id'=>$order_id)));
           }else{
               $this->baoError('订单取消失败');
           }
       }
    }

}

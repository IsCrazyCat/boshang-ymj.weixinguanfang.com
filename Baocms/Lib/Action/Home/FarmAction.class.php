<?php

class FarmAction extends CommonAction {
    protected $cate = array();
    protected $group = array();

    public function _initialize() {
        parent::_initialize();
		 if ($this->_CONFIG['operation']['farm'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $this->group = D('Farm')->getFarmGroup();
        $this->assign('group', $this->group);
        $this->cate = D('Farm')->getFarmCate();
        $this->assign('cate', $this->cate);
        $this->people = D('Farm')->getPeople();
        $this->assign('people', $this->people);
        $this->days = D('Farm')->getDays();
        $this->assign('days', $this->days);
    }

    public function index() {
        //热门农家乐
        $cate_id = I('cate_id',0,'trim,intval');
        $where = $whereh = array();
        if($cate_id){
            $where['attr_id'] = $cate_id;
            $PlayAttr = D('Farmplayattr')->where($where)->select();
            $PlayAttrArray = array();
            foreach($PlayAttr as $k => $v){
                $PlayAttrArray[] = $v['shop_id'];
            }
            $map = array();
            $map['shop_id']  = array('in',$PlayAttrArray); 
            $this->assign('cate_id',$cate_id);
        }
        $hot = D('Farm')->where($map)->order('farm_id desc')->limit(4)->select();
        
        //优选农家乐
        $good = D('Farm')->order('price desc')->limit(8)->select();
        
        //农家攻略
        $tribe_id = $this->_CONFIG['site']['tribe_id'];
        $list = D('Tribepost')->where(array('tribe_id'=>$tribe_id,'closed'=>0))->limit(6)->select();
        $post_ids = $user_ids = array();
        foreach($list as $k=>$val){
            $post_ids[$val['post_id']] = $val['post_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users',D('Users')->itemsByIds($user_ids));
        $pics = D('Tribepostphoto')->where(array('IN'=>$post_ids))->select();
        foreach ($list as $k=>$val){
            foreach($pics as $kk=>$v){
                if($v['post_id'] == $val['post_id']){
                    $list[$k]['pic'] = $v['photo'];
                }
            }
        }
        $this->assign('tribe_id',$tribe_id);
        $this->assign('list',$list);
        $this->assign('hot',$hot);
        $this->assign('good',$good);
        $this->display(); // 输出模板
    }
	
    
	public function lists(){
        $f = M('Farm');
        $linkArr = array();
        
        
        $order = (int) $this->_param('order');
        if($order == 2){
            $orderby = array('orders' => 'desc');
            $linkArr['order'] = $order;
        }else if($order == 3){
            $orderby = array('price' => 'desc');
            $linkArr['order'] = $order;
        }else if($order == 4){
            $orderby = array('score' => 'desc');
            $linkArr['order'] = $order;
        }
        
        $area_id = (int) $this->_param('area_id');
        if ($area_id) {
            $map['area_id'] = $area_id;
            $linkArr['area_id'] = $area_id;
        }
        
        $business_id = (int) $this->_param('business_id');
        if ($business_id) {
            $map['business_id'] = $business_id;
            $linkArr['business_id'] = $business_id;
        }
        
        $from_price = (int) $this->_param('fp');
        $to_price = (int) $this->_param('tp');
        
        if(!$from_price && $to_price){
            $map['price'] = array('ELT', $to_price);
        }elseif($from_price && !$to_price){
            $map['price'] = array('GT', $from_price);
        }elseif($from_price&&$to_price){
            $map['price'] = array('between', $from_price.','.$to_price);
        }
        
        
        $play = $this->_param('play');
        if($play){$linkArr['play'] = $play;}
        $play_array = explode('-',$play);
        $play_array = array_unique($play_array);
        
        //求出shop_id集
        $shop2 = D('Farmplayattr')->where(array('attr_id'=>array('in',$play_array)))->select();
        $shoplist2 = array();
        foreach($shop2 as $sk=>$sv){
            $shoplist2[] = $sv['shop_id'];
        }
        $shoplist2 = array_unique($shoplist2);

        
        $p = $this->cate;
        $cate = array();
        foreach($p as $k => $v){
            $cate[$k]['name'] = $v;
            foreach($play_array as $kk => $vv){
                if($k == $vv){
                    $cate[$k]['sel'] = 1;
                    $sel1 = 1;
                }
            }
        }

        $g = $this->_param('group');
        if($g){$linkArr['group'] = $g;}
        $g_array = explode('-',$g);
        $g_array = array_unique($g_array);
        //求出shop_id集
        $shop1 = D('Farmgroupattr')->where(array('attr_id'=>array('in',$g_array)))->select();
        $shoplist1 = array();
        foreach($shop1 as $sk=>$sv){
            $shoplist1[] = $sv['shop_id'];
        }
        $shoplist1 = array_unique($shoplist1);

        
        $gg = $this->group;
        $group = array();
        foreach($gg as $k => $v){
            $group[$k]['name'] = $v;
            foreach($g_array as $kk => $vv){
                if($k == $vv){
                    $group[$k]['sel'] = 1;
                    $sel2 = 1;
                }
            }
        }
        
        $shop_list = array_unique($shoplist1+$shoplist2);
        if($shop_list){
           $map['shop_id'] = array('in',$shop_list); 
        }


        import('ORG.Util.Page');
        $count = $f->where($map)->count();
        $Page = new Page($count,12);
        $show = $Page->show();
        $list = $f->where($map)->order($orderby)->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('order',$order);
        $this->assign('group',$group);
        $this->assign('cate',$cate);
        $this->assign('sel1',$sel1);
        $this->assign('sel2',$sel2);
 
        $this->assign('area_id',$area_id);
        $this->assign('business_id',$business_id);
        $this->assign('fp',$from_price);
        $this->assign('tp',$to_price);
        $this->assign('play',$play);
        $this->assign('list',$list);
        $this->assign('page',$show);

        
        $this->assign('linkArr',$linkArr);
        $this->display(); // 输出模板
	}

    public function detail($farm_id){
        $obj = D('Farm');
        if(!$farm_id = (int)$farm_id){
            $this->error('该农家乐不存在');
        }elseif(!$detail = $obj->where(array('farm_id'=>$farm_id))->find()){
            $this->error('该农家乐不存在');
        }elseif($detail['closed'] == 1||$detail['audit'] == 0){
            $this->error('该农家乐已删除或未通过审核');
        }else{
            $detail = $obj ->where(array('farm_id'=>$farm_id))-> find();
            $groupid = $obj->getid($detail['shop_id'],1);
            $playid = $obj->getid($detail['shop_id'],2);
            //轮播
            $pics = D('Farmpics')->where(array('farm_id'=>$farm_id))->select();
            $pics[] = array('photo'=>$detail['photo']);
            $this->assign('pics',$pics);

            //套餐
            $package = D('FarmPackage')->where(array('farm_id'=>$detail['farm_id']))->select();
            if($package){
                $this->assign('is_package',1);
            }
            
            //用户评价列表
 
            $fc = D('FarmComment'); 
            import('ORG.Util.Page');
            $count      = $fc->where('farm_id = '.$farm_id)->count();
            $Page       = new Page($count,10);
            $show       = $Page->show();
            $list = $fc->where('farm_id = '.$farm_id)->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
            $this->assign('list',$list);
            $this->assign('page',$show);
            
            foreach($list as $k => $v){
                if($pics = D('FarmCommentPics') -> where('comment_id ='.$v['comment_id']) -> select()){
                    $list[$k]['pics'] = $pics;
                }
            }
            
            $count2      = $fc->where(array('farm_id'=>$farm_id,'have_photo'=>1))->count();
            $Page2       = new Page($count2,10);
            $show2       = $Page2->show();
            $list2 = $fc->where(array('farm_id'=>$farm_id,'have_photo'=>1))->order('create_time')->limit($Page2->firstRow.','.$Page2->listRows)->select();
            $this->assign('list2',$list2);
            $this->assign('page2',$show2);
            
            foreach($list2 as $kk => $vv){
                if($pics2 = D('FarmCommentPics') -> where('comment_id ='.$v['comment_id']) -> select()){
                    $list2[$kk]['pics'] = $pics2;
                }
            }

            $mtime = date('Y-m-d',time());
            $this->assign('mtime',$mtime);
            $this->assign('package',$package);
            $this->assign('groupid',$groupid);
            $this->assign('playid',$playid);
            $this->assign('detail',$detail);
            $this->assign('height_num',880);
            $this->display();
        }
    }

    
    public function order(){
        $gotime = I('gotime',0,'trim');
        $name = I('name','','trim,htmlspecialchars');
        $mobile = I('mobile','','trim');
        $pid = I('pid',0,'trim,intval');
        if(!$gotime){
            $this->error('没有选择时间！');
        }else if(!$name){
            $this->error('没有填写联系人！');
        }else if(!$mobile || !isMobile($mobile)){
            $this->error('手机号码不正确！'.$mobile);
        }else if(!$pid){
            $this->error('没有选择套餐！');
        }else{
           $p = D('FarmPackage')->find($pid);
           $f = D('Farm')->where(array('farm_id'=>$p['farm_id']))->find();
           $shop = D('shop')->find($f['shop_id']);

           $this->assign('p',$p);
           $this->assign('f',$f);
           $this->assign('shop',$shop);
           $this->assign('gotime',$gotime);
           $this->assign('name',$name);
           $this->assign('mobile',$mobile);
           $this->display();
        }
    }
    
    public function orderCreate(){
        
        if (empty($this->uid)) {
            $this->error('您还没有登录',U('passport/login'));
            die;
        }else{
            $data = I('data');

            $gotime = I('gotime',0,'trim');
            $data['gotime'] = strtotime(trim($data['gotime']));
            $data['name'] = htmlspecialchars(trim($data['name']));
            $data['mobile'] = trim($data['mobile']);
            $data['pid'] = intval(trim($data['pid']));
            $data['note'] = htmlspecialchars(trim($data['note']));

            if(!$data['gotime']){
                $this->error('没有选择时间！');
            }else if(!$data['name']){
                $this->error('没有填写联系人！');
            }else if(!$data['mobile'] || !isMobile($data['mobile'])){
                $this->error('手机号码不正确！'.$mobile);
            }else if(!$data['pid']){
                $this->error('没有选择套餐！');
            }else{
                $p = D('FarmPackage')->find($data['pid']);
                $data['user_id'] = $this->uid;
                $data['farm_id'] =$p['farm_id'];
                $data['amount'] = $p['price'];
                $data['jiesuan_amount'] = $p['jiesuan_price'];
                $data['create_time'] = time();
                $data['create_ip'] = get_client_ip();
                
                if($add = D('FarmOrder')->add($data)){
                    $this->success('下单成功',U('farm/pay',array('order_id'=>$add)));
                }else{
                    $this->error('下单失败!');
                }
            }
        }
        
        
    }
    
    
	public function order2(){
        $this->display();
    }
	public function order3(){
        $this->display();
    }


    
    

    public function pay(){
        if (empty($this->uid)) {
            $this->error('您还没有登录',U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('FarmOrder')->find($order_id);
        if (empty($order) || $order['order_status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
        $f = D('FarmPackage')->find($order['pid']);
        if (!$f) {
            $this->error('该套餐不存在');
            die;
        }
        $this->assign('payment', D('Payment')->getPayments());
        $this->assign('f', $f);
        $this->assign('order', $order);
        $this->display();
    }
    
    
    public function pay2(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('FarmOrder')->find($order_id);
        if (empty($order) || $order['order_status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
        if (!$code = $this->_post('code')) {
            $this->error('请选择支付方式！');
        }
        
        $payment = D('Payment')->checkPayment($code);
        if (empty($payment)) {
            $this->error('该支付方式不存在');
        }
        $f = D('FarmPackage')->find($order['pid']);
        if (empty($f)) {
            $this->error('该套餐不存在');
        }
        $logs = D('Paymentlogs')->getLogsByOrderId('farm', $order_id);
        if (empty($logs)) {
            $logs = array(
                'type' => 'farm',
                'user_id' => $this->uid,
                'order_id' => $order_id,
                'code' => $code,
                'need_pay' => $order['amount']*100,
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
                'is_paid' => 0
            );
            $logs['log_id'] = D('Paymentlogs')->add($logs);
        } else {
            $logs['need_pay'] = $order['amount']*100;
            $logs['code'] = $code;
            D('Paymentlogs')->save($logs);
        }
        D('Weixintmpl')->weixin_notice_farm_user($order_id,$this->uid,1);//农家乐微信通知用户   
        $this->success('选择支付方式成功！下面请进行支付！', U('payment/payment', array('log_id' => $logs['log_id'])));
    }

   

}

<?php
class BizAction extends CommonAction {

    public function _initialize() {
        parent::_initialize();
		$getType = D('Near')->getType();
		$this->assign('getType', $getType);
    }


    public function index() {
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
		
		$type = (int) $this->_param('type');
        $this->assign('type', $type);
		
        $order = (int) $this->_param('order');
        $this->assign('nextpage', LinkTo('biz/loaddata', array('t' => NOW_TIME, 'type' => $type, 'order' => $order,  'keyword' => $keyword, 'p' => '0000')));
        $this->display(); // 输出模板
	
    }
	
	 public function loaddata() {
        $Biz = D('Biz');
        import('ORG.Util.Page'); // 导入分页类 
		$map = array('city_id'=>$this->city_id);
		$keyword = $this->_param('keyword');
		$type = (int) $this->_param('type');
        if ($type) {
            $map['type'] = $type;
        }
		//查询置顶
		if(!empty($keyword)){
			$word = D('Nearword')->where(array('text' => $keyword))->find();
			$word_pois = $word['pois_id'];
			if($word_pois){
				$ding = $Biz->find($word_pois);
			}
			//查询列表条件
			$map['name|tag'] = array('LIKE',array('%'.$keyword.'%','%'.$keyword,$keyword.'%','OR'));
		}
			
		$map['status'] = array('egt',0);
		if(!empty($ding)){
			$map['pois_id'] = array('neq',$ding['pois_id']);
		}
		//距离排序
		
		
		$lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
		$order=(int) $this->_param('order');
		switch ($order) {
            case 2:
                $orderby = array('create_time' => 'desc');
                break;
            case 3:
                $orderby = array('orderby' => 'desc');
                break;
            default:
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
                break;
        }
		
        $count = $Biz->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		
        $list = $Biz->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		foreach ($list as $k => $val) {
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
		
		
		$this->assign('keyword', $keyword); 
		$this->assign('ding', $ding); 
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
	
	
	public function gps($pois_id){
        $pois_id = (int)$pois_id;
        if(empty($pois_id)){
            $this->error('该商家不存在');
        }
        $Biz = D('Biz')->find($pois_id);
        
        $this->assign('Biz',$Biz);
        $this->display();
    }
    


    public function detail($pois_id = 0) {
        if ($pois_id = (int) $pois_id) {
            $obj = D('Biz');
            if (!$detail = $obj->find($pois_id)) {
                $this->error('没有该商家信息');
            }

			$lat =$detail['lat'];
			$lng =$detail['lng'];
			$orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
			$list = $obj->order($orderby)->limit(0,10)->select();
			
			$this->assign('list', $list);
            $this->assign('detail', $detail);
            $this->seodatas['title'] = $detail['name'];
            $this->seodatas['keywords'] = $detail['tag'];
            $this->display();
        } else {
            $this->error('没有该商家信息');
        }
    }

	 public function claim(){
		if (empty($this->uid)) {
			$this->fengmiMsg('登录状态失效!', U('passport/login'));
		}
		$pois_id = I('pois_id',0,'trim,intval');
        $obj = D('Near');
        $detail = $obj->find($pois_id);
        if (empty($detail)) {
            $this->fengmiMsg('请选择需要编辑的内容操作');
        }
		
		if (!empty($detail['shop_id'])) {
            $this->fengmiMsg('该黄页已经名花有主！');
        }
		$shops = D('Shop')->where(array('user_id'=>$this->uid))->find();
		if(empty($shops)) {
            $this->fengmiMsg('您还不是商家哦');
        }
		if($shops['is_biz'] == 0) {
            $this->fengmiMsg('您还没申请开通此项服务');
        }
		$map['status'] =  -1;
        $count = $obj->where($map)->count(); 
		
		if($count >0){
			$this->fengmiMsg('您还有未审核的黄页，请等待审核完毕后再认领！');
		}else{
			$obj->save(array('shop_id'=>$shops['shop_id'],'status'=> $map['status'],'pois_id'=>$pois_id));
			$this->fengmiMsg('恭喜您认领成功，审核完毕后该黄页就属于您了！',U('biz/index'));
		}

    }
		
		
	//新添加预约商家开始
	
	 public function yuyue($pois_id) {
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
        }
		$pois_id = (int)($pois_id);
        $detail = D('Biz')->find($pois_id);
				
		$shops = D('Shop')->find($detail['shop_id']);
		
			
        if ($this->isPost()) {
            $data = $this->checkBook($shop_id);
            $obj = D('Shopyuyue');
			$data['pois_id'] = $pois_id;
            $data['shop_id'] = $detail['shop_id'];
			$data['type'] = 1;
            $data['code'] = $obj->getCode();
            if ($obj->add($data)) {
				//通知用户
               if($this->_CONFIG['sms']['dxapi'] == 'dy'){
                    D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_shop_yuyue_code', $data['mobile'], array(
                        'shop_name' => $detail['name'],
                        'shop_tel' => $shops['mobile'],
                        'shop_addr' => $detail['address'],
                        'code' => $data['code']
                        ));
                }else{
                    D('Sms')->sendSms('sms_shop_yuyue', $data['mobile'], array(
                        'shop_name' => $detail['name'],
                        'shop_tel' => $shops['mobile'],
                        'shop_addr' =>  $detail['address'],
                        'code' => $data['code']
                    ));
                }
				//预约通知商家功能开始
				
			   if(!empty($shops['mobile'])){
               if($this->_CONFIG['sms']['dxapi'] == 'dy'){
                     D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_shop_yuyue_shop', $shops['mobile'], array(
                            'name'=>$data['name'],
                            'content'=>$data['content'],
                            'date'=>$data['yuyue_date'],
                            'mobile'=>$data['mobile'],
                            'number'=>$data['number']
                            ));
                    }else{
                        D('Sms')->sendSms('sms_shangjia_yuyue',$shops['mobile'],array(
                            'name'=>$data['name'],
                            'content'=>$data['content'],
                            'date'=>$data['yuyue_date'],
                            'mobile'=>$data['mobile'],
                            'number'=>$data['number']
                        ));
                    }
				  }
                D('Shop')->updateCount($shop_id, 'yuyue_total');
                $this->fengmiSuccess('预约成功！', U('user/yuyue/index',array('status'=>2)));
            }
            $this->fengmiError('操作失败！');
        } else {
            $this->assign('shop_id', $shop_id);
            $this->assign('detail', $detail);
            $this->display();
        }
    }

    public function checkBook() {
        $data = $this->checkFields($this->_post('data', false), array('pois_id','name', 'mobile','type', 'content', 'yuyue_date', 'number'));
        $data['user_id'] = (int) $this->uid;
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->fengmiError('称呼不能为空');
			
        }
        $data['content'] = htmlspecialchars($data['content']);
        if (empty($data['content'])) {
            $this->fengmiError('留言不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->fengmiError('手机不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->fengmiError('手机格式不正确');
        }
        $data['yuyue_date'] = htmlspecialchars($data['yuyue_date']);
        if (empty($data['yuyue_date'])) {
            $this->fengmiError('预定日期不能为空');
        }
        if (!isDate($data['yuyue_date'])) {
            $this->fengmiError('预定日期格式错误！');
        }
        $data['number'] = (int) $data['number'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	//预约商家结束

}
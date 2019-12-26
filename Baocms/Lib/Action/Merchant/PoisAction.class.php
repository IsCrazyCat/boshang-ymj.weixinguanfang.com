<?php
class  PoisAction extends CommonAction{
	
	 protected function _initialize() {
        parent::_initialize();
        $getType = D('Biz')->getType();
        $this->assign('getType', $getType);

    }
    private $edit_fields = array('name', 'type','photo', 'city_id','lat' , 'lng' , 'telephone' , 'address', 'tag', 'is_lock', 'orderby' , 'create_time');

    public function index() {
        $Pois = D('Near');
        import('ORG.Util.Page'); 
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|type'] =  array('LIKE',array('%'.$keyword.'%','%'.$keyword,$keyword.'%','OR'));
            $this->assign('keyword', $keyword);
        }
        $count = $Pois->where($map)->count(); 
        $Page = new Page($count, 25);
        $show = $Page->show(); 
        $list = $Pois->where($map)->order(array('pois_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	
    public function me() {
        $Pois = D('Near');
        import('ORG.Util.Page'); 
        $map['shop_id'] =  $this->shop_id;
        $count = $Pois->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Pois->where($map)->order(array('pois_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	
    public function word() {
        $Pois = D('Nearword');
        import('ORG.Util.Page');
        $map['pois_id'] = '';
        $count = $Pois->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Pois->where($map)->order(array('pois_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display();
    }
	
    public function buy($word_id = 0) {
		$word_id = (int) $word_id;
        if (empty($word_id)) {
			$this->baoError('参数传递错误！');
        }
        if(!$word = D('Nearword')->find($word_id)){
            $this->baoError('参数传递错误！');
        }
		
		if($this->MEMBER['gold'] < $word[price]){
			  $this->baoError('账户余额不足！');
		}else{
		
			$list = D('Near') ->where(array('shop_id'=>$this->shop_id))->select();
			if(empty($list)){
				$this->baoError('没有黄页信息');
			}else{
				$this->assign('word', $word); 
				$this->assign('list', $list); 
				$this->display();
			}
		}
	}
	
	
    public function pay($word_id = 0) {
		$word_id = (int) $word_id;
		$pois_id = (int) $this->_param('pois_id');
        if (empty($word_id)) {
			$this->baoError('参数传递错误！');
        }
        if(!$word = D('Nearword')->find($word_id)){
            $this->baoError('参数传递错误！');
        }
		$money = $this->MEMBER['money']*100;
		if($money < $word[price]){
			  $this->baoError('账户余额不足！');
		}else{
			$pois = D('Near') ->where(array('pois_id'=> $pois_id))->find();
			if(empty($pois)){
				$this->baoError('没有黄页信息');
			}else{
				$eprice = $word['price']*100;	//反算价格
				$estick =  $word['text'];
				$etime =  strtotime(date('Y',time()) + 1 . '-' . date('m-d H:i:s',time()));
				if(D('Users')->addMoney($this->uid, -$eprice ,'购买黄页关键词包年服务，关键词：'.$word['text'])){
					D('Nearword')->save(array('pois_id'=>$pois['pois_id'],'over_time'=>$etime,'word_id'=>$word['word_id']));
					$this->baoSuccess('您为【'.$pois['name'].'】购包年关键词【'.$word['text'].'】成功！',U('pois/stick'));
				}else{
					$this->baoError('购买错误，没有付款成功！');
				}
			}
		}
	}
	
	

    public function create() {
        if ($this->isPost()) {
            $data = $this->editCheck(); 
			$seed=md5(microtime()).md5(mt_rand(0,31));
			$data['uid'] = substr(md5($seed),0,24);
			$data['shop_id'] = $this->shop_id;
			$shops = D('Shop')->find($this->shop_id);
			$data['city_id'] = $shops['city_id'];
			$data['photo'] = htmlspecialchars($data['photo']);
			if (empty($data['photo'])) {
				$this->baoError('请上传黄页图片');
			}
			if (!isImage($data['photo'])) {
				$this->baoError('黄页图片格式不正确');
			} 
			$data['status'] = -1;	
			$data['create_time'] = time();	
			$data['is_lock'] = 1;
			$obj = D('Near');
		if ($obj->add($data)) {
			$this->baoSuccess('添加成功等待系统审核！', U('pois/me'));
		}
		$this->baoError('操作失败！');

        } else {
			$Pois = D('Near');
			$list = $Pois -> where(array('shop_id'=>$this->shop_id)) ->select();
			$this->assign('list', $list); 
            $this->display();
        }
    }
	

	public function raise($pois_id = 0){
		$pois_id = (int) $pois_id;
        if (empty($pois_id)) {
			$this->baoError('参数传递错误！');
        }
        if(!$pois = D('Near')->find($pois_id)){
            $this->baoError('参数传递错误！');
        }
		if($this->MEMBER['money'] <= 100){
			  $this->baoError('账户余额不足！');
		}else{
			if($pois['orderby'] >= 2){
				if(D('Users')->addMoney($this->uid, -100,'提升黄页权重1点')){
				   D('Near')->save(array('orderby'=>$pois[orderby] - 1,'pois_id'=>$pois_id));
					$this->baoSuccess('您为'.$pois['name'].'提升了1点权重！',U('pois/me'));
				}
			}else{
				 $this->baoError('您排名很高了，无需购买！');
			}
		}
	}
	
	
    public function stick() {
        $Pois = D('Near');
		$Word = D('Nearword');
        import('ORG.Util.Page'); 
		$mypois = $Pois->where("shop_id = $this->shop_id")->order(array('pois_id' => 'desc'))->select();
		foreach($mypois as $k => $var ){
			$mypoisid .= ','.$var['pois_id'];
		}
		$mypoisid = ltrim($mypoisid, ",");
		
        $map ="pois_id IN ($mypoisid)"  ;
        $count = $Word->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Word->where($map)->order(array('word_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$ids =array();
        foreach ($list as $k => $val) {
            $ids[$val['pois_id']] = $val['pois_id'];
        }
        if ($ids) {
            $this->assign('poiss', D('Near')->itemsByIds($ids));
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	
	
	public function renew($word_id = 0){
		$word_id = (int) $word_id;
        if (empty($word_id)) {
			$this->baoError('参数传递错误！');
        }
        if(!$word = D('Nearword')->find($word_id)){
            $this->baoError('参数传递错误！');
        }
		
		$text = $word['text'];
		$price = $word['price']*100;
		if($word['over_time']<time()){
			$time = time();
		}else{
			$time = $word['over_time'];
		}
		if($this->MEMBER['money'] < $price){
			  $this->baoError('账户余额不足，需要'.$price.'余额进行续费！');
		}else{
			$etime = strtotime(date('Y',$time) + 1 . '-' . date('m-d H:i:s',$time));
			if(D('Users')->addMoney($this->uid, -$price ,'续费黄页包年关键词：'.$text)){
				D('Nearword')->save(array('over_time'=>$etime,'word_id'=>$word_id));
				$this->baoSuccess('恭喜您为['.$text.']关键词续费成功！',U('pois/stick'));
			}
		}
	}
	
    public function lead($pois_id = 0) {
        if (empty($pois_id)) {
            $this->error('请选择需要编辑的内容操作');
        }
        $pois_id = (int) $pois_id;
        $obj = D('Near');
        $detail = $obj->find($pois_id);
        if (empty($detail)) {
            $this->baoError('请选择需要编辑的内容操作');
        }
        if (!empty($detail['shop_id'])) {
            $this->baoError('该黄页已经名花有主！');
        }

        $map['shop_id'] =  $this->shop_id;
		$map['status'] =  -1;
        $count = $obj->where($map)->count(); 
		
		if($count >0){
			$this->baoError('您还有未审核的黄页，请等待审核完毕后再认领！');
		}else{
			$obj->save(array('shop_id'=>$this->shop_id,'status'=> $map['status'],'pois_id'=>$pois_id));
			$this->baoSuccess('恭喜您认领成功，审核完毕后该黄页就属于您了！',U('pois/me'));
		}
	}
	
    
    public function edit($pois_id = 0) {
        if (empty($pois_id)) {
            $this->error('请选择需要编辑的内容操作');
        }
        $pois_id = (int) $pois_id;
        $obj = D('Near');
        $detail = $obj->find($pois_id);
        if (empty($detail) || $detail['shop_id'] != $this->shop_id) {
            $this->error('请选择需要编辑的内容操作');
        }
        if ($this->isPost()) {
            $data = $this->editCheck();
			$shops = D('Shop')->find($this->shop_id);
			$data['city_id'] = $shops['city_id'];
            $data['pois_id'] = $pois_id;
			$data['status'] = -1;
            if (false !== $obj->save($data)) {
                $this->baoSuccess('操作成功', U('pois/me'));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);

        if (empty($data['name'])) {
            $this->baoError('名称不能为空');
        }
		$data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传黄页图片');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('黄页图片格式不正确');
        } 
        if (empty($data['type'])) {
            $this->baoError('请选择分类');
        }
        if (empty($data['lat']) || empty($data['lng'])) {
            $this->baoError('坐标不能为空');
        }
        if (empty($data['telephone'])) {
            $this->baoError('联系电话不能为空');
        }
        if (empty($data['address'])) {
            $this->baoError('详细地址不能为空');
        }

        return $data;
    }
    
    
   
    
}
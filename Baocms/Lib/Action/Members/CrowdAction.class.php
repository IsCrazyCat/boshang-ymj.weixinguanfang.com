<?php
//这里是会员中心的
class CrowdAction extends CommonAction {
	 private $create_fields = array('city_id', 'area_id','title','intro', 'user_id', 'photo', 'cate_id', 'price', 'all_price', 'commission',  'orderby', 'views',  'details','lat','lng', 'end_date','ltime');
    private $edit_fields = array('city_id', 'area_id','title','intro', 'user_id', 'photo', 'cate_id', 'price', 'all_price', 'commission',  'orderby', 'views',  'details', 'lat','lng', 'end_date','ltime');

    public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['crowd'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $this->cates = D('Crowdcate')->fetchAll();
        $this->assign('cates', $this->cates);
    }
   //我的列表订单
   public function index() {
		$Crowdorder = D('Crowdorder');
		import('ORG.Util.Page');
		$map = array('user_id' => $this->uid,'closed'=>0);
		//搜索
		if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
		$count = $Crowdorder->where($map)->count(); 
        $Page = new Page($count, 20); 
        $show = $Page->show(); 
		$list = $Crowdorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $v){
			$goods_ids[$v['goods_id']] = $v['goods_id'];
			$type_ids[$v['type_id']] = $v['type_id'];
			$address_ids[$v['address_id']] = $v['address_id'];
			$user_ids[$v['user_id']] = $v['user_id'];
			$order_ids[$v['order_id']] = $v['order_id'];
		}
		if (!empty($type_ids)) {
			$this->assign('type', D('Crowdtype')->itemsByIds($type_ids));
		}
		if (!empty($goods_ids)) {
			$this->assign('crowd', D('Crowd')->itemsByIds($goods_ids));
		}
		if (!empty($address_ids)) {
			$this->assign('paddress', D('Paddress')->itemsByIds($address_ids));
		}
		if (!empty($user_ids)) {
			$this->assign('users', D('Users')->itemsByIds($user_ids));
		}
		if (!empty($order_ids)) {
            $Crowdlist = D('Crowdlist')->where(array('order_id' => array('IN', $order_ids)))->select();
            $this->assign('crowdlist', $Crowdlist);
        }
		$this->assign('list', $list); 
        $this->assign('page', $show); 
		$this->display();
    }
	
    //我发布的众筹列表
    public function crowd_list() {
        $Crowd = D('Crowd');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'user_id' => $this->uid);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Crowd->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Crowd->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
			$goods_ids[] = $val['goods_id']; 
            $val = $Crowd->_format($val);
            $list[$k] = $val;
        }
		if($goods_ids){
			$f['goods_id'] = array('IN',implode(',',$goods_ids));
			$Crowd_list = $Crowd->where($f)->select();
			foreach($Crowd_list as $k => $v){
				$Crowd_lists[$v['goods_id']] = $v;
			}
			$this->assign('crowd', $Crowd_lists);
		}
		
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
   //往期众筹
    public function history() {
        $Goods = D('Goods');
		$Crowd = D('Goodscrowd');
        import('ORG.Util.Page');
		$map = array('closed' => 0, 'shop_id' => $this->shop_id,'type'=>'crowd','ltime'=>array('LT',time()));
        $count = $Goods->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show();
        $list = $Goods->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
			$goods_ids[] = $val['goods_id']; 
            $val = $Goods->_format($val);
            $list[$k] = $val;
        }
		if($goods_ids){
			$f['goods_id'] = array('IN',implode(',',$goods_ids));
			$Crowd_list = $Crowd->where($f)->select();
			foreach($Crowd_list as $k => $v){
				$Crowd_lists[$v['goods_id']] = $v;
			}
			$this->assign('crowd', $Crowd_lists);
		}
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
    //添加众筹
	public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Crowd');
            if ($goods_id = $obj->add($data)) {
                $photos = $this->_post('photos', false);
                if (!empty($photos)) {
                    D('Crowdphoto')->upload($goods_id, $photos);
                }
                $this->baoSuccess('恭喜你添加众筹成功', U('crowd/crowd_list'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
		
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('产品名称不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['title'])) {
            $this->baoError('描述不能为空');
        }
        $data['user_id'] = $this->uid;
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('请选择分类');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
		$data['all_price'] = (int) ($data['all_price'] * 100);
        if (empty($data['all_price'])) {
           $this->baoError('众筹金额不能为空'); 
		}
        $data['commission'] = (int) ($data['commission'] * 100);
        if ($data['commission'] < 0) {
            $this->baoError('佣金不能为负数');
        }
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('众筹详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('众筹详情含有敏感词：' . $words);
        } $data['ltime'] = htmlspecialchars($data['ltime']);
        if (empty($data['ltime'])) {
            $this->baoError('过期时间不能为空');
        }
		$data['lng'] = htmlspecialchars(trim($data['lng']));
        $data['lat'] = htmlspecialchars(trim($data['lat']));
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    //编辑众筹
    public function edit($goods_id = 0) {
        if ($goods_id = (int) $goods_id) {
            $obj = D('Crowd');
            if (!$detail = $obj->find($goods_id)) {
                $this->baoError('请选择要编辑的众筹');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['goods_id'] = $goods_id;
                if (false !== $obj->save($data)) {
					$photos = $this->_post('photos', false);
                    if (!empty($photos)) {
                        D('Crowdphoto')->upload($goods_id, $photos);
                    }
                    $this->baoSuccess('恭喜您编辑众筹成功', U('crowd/crowd_list'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $obj->_format($detail));
				$this->assign('photos', D('Crowdphoto')->getPics($goods_id));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的众筹');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('产品名称不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['title'])) {
            $this->baoError('描述不能为空');
        }
        $data['user_id'] = $this->uid;
        $shop = D('Users')->find($data['user_id']);
        if (empty($shop)) {
            $this->baoError('请选择正确的会员');
        }
   
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('请选择分类');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        } 
		$data['all_price'] = (int) ($data['all_price'] * 100);
        if (empty($data['all_price'])) {
           $this->baoError('众筹金额不能为空'); 
		}
        $data['commission'] = (int) ($data['commission'] * 100);
        if ($data['commission'] < 0) {
            $this->baoError('佣金不能为负数');
        }
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('众筹详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('众筹详情含有敏感词：' . $words);
        } 
		$data['ltime'] = htmlspecialchars($data['ltime']);
        if (empty($data['ltime'])) {
            $this->baoError('过期时间不能为空');
        }
		$data['lng'] = htmlspecialchars(trim($data['lng']));
        $data['lat'] = htmlspecialchars(trim($data['lat']));
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
		$data['audit'] = 0;
        return $data;
    }


	public function setting($goods_id = 0) {
        if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdtype = D('Crowdtype');
			$detail = $Crowd->find($goods_id);
			if ($detail['user_id'] != $this->uid) {
                $this->error('请不要操作别人的众筹');
            }
			$this->assign('detail', $detail);
			$this->assign('meals', $Crowdtype->where(array('goods_id' => $goods_id))->select());
			$this->display();
        } else {
            $this->error('请选择要设置的众筹');
        }
    }

	public function type_create($goods_id){
		if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdtype = D('Crowdtype');
            if (!$detail = $Crowd->find($goods_id)) {
                $this->error('请选择要设置的众筹');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->error('请不要操作别人的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->error('该众筹已被删除');
            }
            $Crowd_list = $Crowd->find($goods_id);

			if ($data = $this->_post('data', false)) {
				if (!empty($data['price']) && !empty($data['content']) && !empty($data['max_num']) && !empty($data['fahuo'])){
					$Crowdtype->add(array(
						'goods_id' => $goods_id,
						'price' => $data['price']*100,
						'content' => $data['content'],
						'max_num' => $data['max_num'],
						'yunfei' => $data['yunfei']*100,
						'fahuo' => $data['fahuo'],
						'choujiang' => $data['choujiang'],
						'img' => $data['img'],
						'dateline' => time(),
					));
					$this->baoSuccess('操作成功', U('crowd/setting',array('goods_id'=>$detail['goods_id'])));
				}else{
					 $this->baoError('内容不能为空');
				}
			}else{
				$this->assign('detail', $detail);
				$this->display();
			}

		} else {
            $this->error('请选择要设置的众筹');
        }
	}

	public function type_edit($type_id)
	{
		if ($type_id = (int) $type_id) {
			$Crowd = D('Crowd');
            $Crowdtype = D('Crowdtype');
			if (!$type = $Crowdtype->find($type_id)) {
                $this->baoError('修改的内容不存在');
            }
			$goods_id = $type['goods_id'];
            if (!$detail = $Crowd->find($goods_id)) {
                $this->baoError('请选择要设置的众筹');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->baoError('请不要操作别人的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->baoError('该众筹已被删除');
            }
            $Crowd_list = $Crowd->find($goods_id);			
			if ($data = $this->_post('data', false)) {
				if (!empty($data['price']) && !empty($data['content']) && !empty($data['max_num']) && !empty($data['fahuo'])){
					$data = array(
						'goods_id' => $goods_id,
						'price' => $data['price']*100,
						'content' => $data['content'],
						'max_num' => $data['max_num'],
						'yunfei' => $data['yunfei']*100,
						'fahuo' => $data['fahuo'],
						'choujiang' => $data['choujiang'],
						'img' => $data['img'],
					);

					if (false !== $Crowdtype->where(array('type_id' => $type_id))->save($data)) {
						$this->baoSuccess('操作成功', U('crowd/setting',array('goods_id'=>$detail['goods_id'])));
					}
					
				}else{
					 $this->baoError('内容不能为空');
				}
			}else{
				$this->assign('type', $type);
				$this->assign('detail', $detail);
				$this->display();
			}

		} else {
            $this->error('修改的内容不存在');
        }
	}

	public function type_delete($type_id)
	{
		if ($type_id = (int) $type_id) {
			$Crowd = D('Crowd');
            $Crowdtype = D('Crowdtype');
			if (!$type = $Crowdtype->find($type_id)) {
                $this->baoError('删除的内容不存在');
            }
			$goods_id = $type['goods_id'];
            if (!$detail = $Crowd->find($goods_id)) {
                $this->baoError('请选择要设置的众筹');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->baoError('请不要操作别人的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->baoError('该众筹已被删除');
            }
            $Crowd_list = $Crowd->find($goods_id);
			
			if (false !== $Crowdtype->where(array('type_id' => $type_id))->delete()) {
				$this->baoSuccess('操作成功', U('crowd/setting',array('goods_id'=>$detail['goods_id'])));
			}
		} else {
            $this->error('删除的内容不存在');
        }
	}

	public function project($goods_id){
		if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdproject = D('Crowdproject');
			$detail = $Crowd->find($goods_id);
			$this->assign('detail', $detail);
			$this->assign('meals', $Crowdproject->where(array('goods_id' => $goods_id))->select());
			$this->display();
        } else {
            $this->error('请选择要设置的众筹');
        }
	}

	public function project_create($goods_id){
		if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdproject = D('Crowdproject');

            if (!$detail = $Crowd ->find($goods_id)) {
                $this->error('请选择要设置的众筹');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->error('请不要操作别人的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->error('该众筹已被删除');
            }
            $Crowd_list = $Crowd->find($goods_id);
			
			
			if ($data = $this->_post('data', false)) {
				if (!empty($data['content'])){
					$Crowdproject->add(array(
						'goods_id' => $goods_id,
						'content' => $data['content'],
						'dateline' => time(),
					));
					$this->baoSuccess('操作成功', U('crowd/project',array('goods_id'=>$detail['goods_id'])));
				}else{
					 $this->baoError('内容不能为空');
				}
			}else{
				$this->assign('detail', $detail);
				$this->display();
			}
		} else {
            $this->error('请选择要设置的众筹');
        }
	}

	public function project_delete($project_id){
		if ($project_id = (int) $project_id) {
			$Crowd = D('Crowd');
            $Crowdproject = D('Crowdproject');
			if (!$type = $Crowdproject->find($project_id)) {
                $this->baoError('删除的内容不存在');
            }
			$goods_id = $type['goods_id'];
            if (!$detail = $Crowd->find($goods_id)) {
                $this->baoError('请选择要设置的众筹');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->baoError('请不要操作别人的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->baoError('该众筹已被删除');
            }
            $Crowd_list = $Crowd->find($goods_id);
			
			if (false !== $Crowdproject->where(array('project_id' => $project_id))->delete()) {
				$this->baoSuccess('操作成功', U('crowd/project',array('goods_id'=>$detail['goods_id'])));
			}
		} else {
            $this->baoError('删除的内容不存在');
        }
	}

	public function ask($goods_id){
		if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdask = D('Crowdask');
			$detail = $Crowd->find($goods_id);
			$this->assign('detail', $detail);
			$ask = $Crowdask->where(array('goods_id' => $goods_id))->select();
			$this->assign('meals', $ask);
			foreach($ask as $k => $v){
				$user_ids[$v['uid']] = $v['uid'];
			}
			if (!empty($user_ids)) {
				$this->assign('users', D('Users')->itemsByIds($user_ids));
			}
			$this->display();
        } else {
            $this->error('请选择众筹');
        }
	}

	public function ask_answer($ask_id){
		if ($ask_id = (int) $ask_id) {
			$Crowd = D('Crowd');
            $Crowdask = D('Crowdask');
			if (!$ask = $Crowdask->find($ask_id)) {
                $this->error('该问题不存在');
            }
            if (!$detail = $Crowd ->find($ask['goods_id'])) {
                $this->error('请选择要设置的众筹');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->error('请不要操作别人的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->error('该众筹已被删除');
            }
            $Crowd_list = $Crowd->find($goods_id);
			
			if ($data = $this->_post('data', false)) {
				if (!empty($data['answer_c'])){
					$Crowdask->where(array('ask_id' => $ask_id))->save(array(
						'answer_c' => $data['answer_c'],
						'answer_time' => time(),
					));
					$this->baoSuccess('操作成功', U('crowd/ask',array('goods_id'=>$ask['goods_id'])));
				}else{
					 $this->error('内容不能为空');
				}
			}else{
				$this->assign('detail', $detail);
				$this->assign('ask', $ask);
				$this->display();
			}
		} else {
            $this->error('请选择要设置的众筹');
        }
	}

	public function follow($goods_id)
	{
		if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdfollow = D('Crowdfollow');
			$detail = $Crowd->find($goods_id);
			$this->assign('detail', $detail);
			$follow = $Crowdfollow->where(array('goods_id' => $goods_id))->select();
			$this->assign('meals', $follow);

			foreach($follow as $k => $v){
				$user_ids[$v['uid']] = $v['uid'];
			}
			if (!empty($user_ids)) {
				$this->assign('users', D('Users')->itemsByIds($user_ids));
			}
			$this->display();
        } else {
            $this->error('请选择众筹');
        }
	}

	public function lists($goods_id){
		if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdlist = D('Crowdlist');
			$detail = $Crowd->find($goods_id);
			$this->assign('detail', $detail);
			$list = $Crowdlist->where(array('goods_id' => $goods_id))->select();
			$this->assign('meals', $list);
			
			foreach($list as $k => $v){
				$user_ids[$v['uid']] = $v['uid'];
				$type_ids[$v['type_id']] = $v['type_id'];
			}
			if (!empty($type_ids)) {
				$this->assign('type', D('Crowdtype')->itemsByIds($type_ids));
			}
			if (!empty($user_ids)) {
				$this->assign('users', D('Users')->itemsByIds($user_ids));
			}
			$this->display();
        } else {
            $this->baoError('请选择众筹');
        }
	}

	public function zhong($list_id){
		$Crowdlist = D('Crowdlist');
		$data['is_zhong'] = '1';
		$list = $Crowdlist->where(array('list_id'=>$list_id))->find();
		if($Crowdlist->where(array('list_id'=>$list_id))->save($data)){
			$this->baoMsg('设为中奖成功', U('crowd/lists',array('goods_id'=>$list['goods_id'])));
		}
	}



    public function order() {
        $Goodsorder = D('Tuanorder');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('shop_id' => $this->shop_id);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars') ) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }

        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }

        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
        $count = $Goodsorder->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Goodsorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $user_ids = $goods_ids = array();
        foreach ($list as $k => $val) {
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $user_ids[$val['user_id']] = $val['user_id'];
            $goods_ids[$val['goods_id']] = $val['goods_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('tuan', D('Tuan')->itemsByIds($goods_ids));
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function used() {
        if ($this->isPost()) {
            $code = $this->_post('code', false);
            if (empty($code)) {
                $this->baoError('请输入众筹券！');
            }

            $obj = D('Tuancode');
            $shopmoney = D('Shopmoney');
            $return = array();
            $ip = get_client_ip();
            if (count($code) > 10) {
                $this->baoError('一次最多验证10条众筹券！');
            }
            $userobj = D('Users');
            foreach ($code as $key => $var) {
                $var = trim(htmlspecialchars($var));

                if (!empty($var)) {
                    $data = $obj->find(array('where' => array('code' => $var)));

                    if (!empty($data) && $data['shop_id'] == $this->shop_id && (int) $data['is_used'] == 0 && (int) $data['status'] == 0) {
                        if ($obj->save(array('code_id' => $data['code_id'], 'is_used' => 1))) { //这次更新保证了更新的结果集              
                            //增加MONEY 的过程 稍后补充
                            if (!empty($data['price'])) {
                                $data['intro'] = '众筹消费' . $data['order_id'];

                                $data['settlement_price'] =  D('Quanming')->quanming($data['user_id'],$data['settlement_price'],'tuan'); //扣去全民营销

                                $shopmoney->add(array(
                                    'shop_id' => $data['shop_id'],
                                    'money' => $data['settlement_price'],
                                    'create_ip' => $ip,
                                    'create_time' => NOW_TIME,
                                    'order_id' => $data['order_id'],
                                    'intro' => $data['intro'],
                                ));
                                $shop = D('Shop')->find($data['shop_id']);
                                D('Users')->addMoney($shop['user_id'], $data['settlement_price'], $data['intro']);
                                $return[$var] = $var;
                                D('Users')->gouwu($data['user_id'],$data['price'],'众筹券消费成功');
                                $obj->save(array('code_id' => array('used_time' => NOW_TIME, 'used_ip' => $ip))); //拆分2次更新是保障并发情况下安全问题
                                echo '<script>parent.used(' . $key . ',"√验证成功",1);</script>';
                            } else {
                                echo '<script>parent.used(' . $key . ',"√到店付众筹券验证成功，需要现金付款",2);</script>';
                            }
                  
                        }
                    } else {
                        echo '<script>parent.used(' . $key . ',"X该众筹券无效",3);</script>';
                    }
                }
            }
        } else {
            $this->display();
        }
    }

    

   

}

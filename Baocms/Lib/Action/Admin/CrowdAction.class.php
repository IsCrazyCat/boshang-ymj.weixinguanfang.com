<?php

class CrowdAction extends CommonAction {
	
	 private $create_fields = array('city_id', 'area_id','title','intro', 'user_id', 'photo', 'cate_id', 'price', 'all_price', 'commission',  'orderby', 'views',  'details', 'end_date', 'orderby','ltime');
    private $edit_fields = array('title','intro','city_id','area_id', 'user_id', 'photo', 'cate_id', 'price', 'all_price', 'commission', 'orderby', 'views',  'details', 'end_date', 'orderby','ltime');


   public function index() {
        $Crowd = D('Crowd');
		$Crowdgoods = D('Crowdgoods');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0,'type'=>'crowd');
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
		if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = ($audit === 1 ? 1 : 0);
            $this->assign('audit', $audit);
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

	
   //全部订单列表
   public function order() {
		$Crowdorder = D('Crowdorder');
		import('ORG.Util.Page');
		$map = array('closed'=>0);
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
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
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
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
		$this->assign('types',D('Crowdorder')->getCfg());
        $this->assign('page', $show); 
		$this->display();
    }

    public function delete($goods_id = 0) {
        if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)) {
            $obj = D('Crowd');
            $obj->save(array('goods_id' => $goods_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('crowd/index'));
        } else {
            $goods_id = $this->_post('goods_id', false);
            if (is_array($goods_id)) {
                $obj = D('Crowd');
                foreach ($goods_id as $id) {
                    $obj->save(array('goods_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('crowd/index'));
            }
            $this->baoError('请选择要删除的商家');
        }
    }

    public function audit($goods_id = 0) {
        if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)) {
            $obj = D('Crowd');
            $r = $obj -> where('goods_id ='.$goods_id) -> find();
            if(empty($r['settlement_price'])){
            }
            $obj->save(array('goods_id' => $goods_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('crowd/index'));
        } else {
            $goods_id = $this->_post('goods_id', false);
            if (is_array($goods_id)) {
                $obj = D('Crowd');
                $error = 0;
                foreach ($goods_id as $id) {
                    $r = $obj -> where('goods_id ='.$id) -> find();
                    if(empty($r['settlement_price'])){
                    }
                    $obj->save(array('goods_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！'.$error.'条失败', U('crowd/index'));
            }
            $this->baoError('请选择要审核的商品');
        }
    }
	
	 public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Crowd');
            if ($goods_id = $obj->add($data)) {
                $photos = $this->_post('photos', false);
                if (!empty($photos)) {
                    D('Crowdphoto')->upload($goods_id, $photos);
                }
                $this->baoSuccess('添加成功', U('crowd/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Crowdcate')->fetchAll());
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
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('会员不能为空');
        }
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
        $data['rush_price'] = (int) ($data['rush_price'] * 100);
        $data['views'] = (int) $data['views'];
        if (empty($data['views'])) {
            $this->baoError('浏览量不能为空');
        }
		 $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('商品详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('商品详情含有敏感词：' . $words);
        } $data['ltime'] = htmlspecialchars($data['ltime']);
        if (empty($data['ltime'])) {
            $this->baoError('过期时间不能为空');
        }
        if (!isDate($data['ltime'])) {
            $this->baoError('过期时间格式不正确');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function edit($goods_id = 0) {
        if ($goods_id = (int) $goods_id) {
            $obj = D('Crowd');
            if (!$detail = $obj->find($goods_id)) {
                $this->baoError('请选择要编辑的商品');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['goods_id'] = $goods_id;
                if (false !== $obj->save($data)) {
					$photos = $this->_post('photos', false);
                    if (!empty($photos)) {
                        D('Crowdphoto')->upload($goods_id, $photos);
                    }
                    $this->baoSuccess('操作成功', U('crowd/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $obj->_format($detail));
                $this->assign('cates', D('Crowdcate')->fetchAll());
				$this->assign('photos', D('Crowdphoto')->getPics($goods_id));
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商品');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('产品名称不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['title'])) {
            $this->baoError('描述不能为空');
        }
		
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('会员不能为空');
        }
        $shop = D('Users')->find($data['user_id']);
        if (empty($shop)) {
            $this->baoError('请选择正确的会员');
        }
   
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('请选择分类');
        }
		$data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
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
        $data['rush_price'] = (int) ($data['rush_price'] * 100);
        $data['views'] = (int) $data['views'];
        if (empty($data['views'])) {
            $this->baoError('浏览量不能为空');
        }
		 $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('商品详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('商品详情含有敏感词：' . $words);
        } $data['ltime'] = htmlspecialchars($data['ltime']);
        if (empty($data['ltime'])) {
            $this->baoError('过期时间不能为空');
        }
        if (!isDate($data['ltime'])) {
            $this->baoError('过期时间格式不正确');
        }
		$data['orderby'] = (int) $data['orderby'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
	public function setting($goods_id = 0) {
        if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdtype = D('Crowdtype');
			$detail = $Crowd->find($goods_id);
			$this->assign('detail', $detail);
			$this->assign('meals', $list = $Crowdtype->where(array('goods_id' => $goods_id,'closed'=>0))->select());
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

	public function type_edit($type_id){
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
	//后台批量删除
	 public function type_delete($type_id = 0) {
        if (is_numeric($type_id) && ($type_id = (int) $type_id)) {
            $obj = D('Crowdtype');
			if (!$detail = D('Crowd')->find($goods_id)) {
                $this->baoError('请选择要设置的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->baoError('该众筹已被删除');
            }
            $obj->save(array('type_id' => $type_id, 'closed' => 1));
            $this->baoSuccess('操作成功', U('crowd/setting',array('goods_id'=>$detail['goods_id'])));
        } else {
            $type_id = $this->_post('type_id', false);
            if (is_array($type_id)) {
                $obj = D('Crowdtype');
                foreach ($type_id as $id) {
                    $obj->save(array('type_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('操作成功', U('crowd/index'));
            }
            $this->baoError('请选择要删除的项目');
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
    //删除进展只能单个
	public function project_create($goods_id){
		if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdproject = D('Crowdproject');

            if (!$detail = $Crowd ->find($goods_id)) {
                $this->error('请选择要设置的众筹');
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
	
	//后台话题批量删除
	 public function ask_delete($ask_id = 0) {
        if (is_numeric($ask_id) && ($ask_id = (int) $ask_id)) {
			$Crowd = D('Crowd');
			$obj = D('Crowdask');
			if (!$detail = $obj ->find($ask_id)) {
                $this->error('请选择要设置的众筹');
            }
            $obj->delete($ask_id);
			$obj->cleanCache();
            $this->baoSuccess('操作成功', U('crowd/ask',array('goods_id'=>$detail['goods_id'])));
        } else {
            $ask_id = $this->_post('ask_id', false);
            if (is_array($ask_id)) {
                $obj = D('Crowdask');
                foreach ($ask_id as $id) {
                    $obj->delete($id);
                }
				$obj->cleanCache();
                $this->baoSuccess('操作成功', U('crowd/index'));
            }
            $this->baoError('请选择要删除的话题');
        }
    }
	

	public function follow($goods_id){
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
}

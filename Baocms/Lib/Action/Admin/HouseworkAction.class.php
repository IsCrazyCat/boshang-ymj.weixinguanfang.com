<?php


class  HouseworkAction extends CommonAction{
	 private $create_fields = array('id', 'cate_id','city_id','shop_id','price', 'unit','gongju', 'photo','thumb', 'name', 'tel', 'biz_time', 'title', 'contents', 'yuyue_num', 'views', 'areas');
    private $edit_fields = array('id', 'cate_id','city_id','shop_id','price', 'unit','gongju', 'photo', 'thumb','name', 'tel', 'biz_time', 'title', 'contents', 'yuyue_num', 'views', 'areas');
    
    public function index(){
        $Housework = D('Housework');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|tel|contents'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }  
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $Housework->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Housework->where($map)->order(array('housework_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		$shop_ids = array();
        foreach ($list as $key => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
		
		
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
		$this->assign('cates', D('Housekeepingcate')->fetchAll());
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->display(); // 输出模板
    }
    
    // 家政的项目配置
    public function setting(){
        $houseworksetting = D('Houseworksetting');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array('closed' => 0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
		
        if ($keyword) {
            $map['name|title|contents'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }  
        if ($svc_id = (int) $this->_param('svc_id')) {
            $map['svc_id'] = $svc_id;
            $this->assign('svc_id', $svc_id);
        }
		
        $count = $houseworksetting->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $houseworksetting->where($map)->order(array('id' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		$shop_ids = array();
        foreach ($list as $key => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }		
		
        $this->assign('list', $list); // 赋值数据集
		$this->assign('cates', D('Housekeepingcate')->fetchAll());
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->display(); // 输出模板
    }
    
    public function setting2($id){
        $Houseworksetting = D('Houseworksetting');
        $Housework = D('Housework');
        $id = (int)$id;
        $detail = $Houseworksetting->detail($id);
		$data = $this->checkFields($this->_post('data', false), $this->create_fields);
        if ($this->isPost()) {			
			$data['cate_id'] = (int) $data['cate_id'];//ID
			if (empty($data['cate_id'])) {
				$this->baoError('类型ID不能为空');
			}
			$data['city_id'] = (int) $data['city_id'];
			if (empty($data['city_id'])) {
				$this->baoError('城市不能为空');
			}
			$data['area_id'] = (int) $data['area_id'];
			$data['shop_id'] = (int) $data['shop_id'];//商家ID
			if (empty($data['shop_id'])) {
            $this->baoError('请您选择商家');
     	    }
			$data['name'] = htmlspecialchars($_POST['name']);//标题名字
			if (empty($data['name'])) {
            $this->baoError('请您填写家政名字');
     	    }
            $data['title'] = htmlspecialchars($_POST['title']);
			if (empty($data['title'])) {
            $this->baoError('请您填写服务标题');
     	    }
            $data['price'] = (int)($_POST['price'] * 100);
			if (empty($data['price'])) {
            $this->baoError('价格不能为空');
            }
            $data['unit']  = htmlspecialchars($_POST['unit']);
            $data['gongju']  = htmlspecialchars($_POST['gongju']);
			
			$data['user'] = htmlspecialchars($_POST['user']);//标题名字
			if (empty($data['user'])) {
            $this->baoError('请您填写姓名');
     	    }
			$data['tel'] = htmlspecialchars($_POST['tel']);//标题名字
			if (empty($data['tel'])) {
            $this->baoError('请您手机号码');
     	    }
			if (!isPhone($data['tel']) && !isMobile($data['tel'])) {
            $this->baoError('联系电话格式不正确');
            }
			
            $data['photo']  = htmlspecialchars($_POST['photo']);
			if (empty($data['photo'])) {
            $this->baoError('请您上传图片');
            }
			
			$thumb = $this->_param('thumb', false);
			foreach ($thumb as $k => $val) {
				if (empty($val)) {
					unset($thumb[$k]);
				}
				if (!isImage($val)) {
					unset($thumb[$k]);
				}
			}
			$data['thumb'] = serialize($thumb);
		
		
            $data['biz_time']  = htmlspecialchars($_POST['biz_time']);
            $data['contents'] = SecurityEditorHtml($_POST['contents']);
			if (empty($data['contents'])) {
            $this->baoError('内容不能为空');
			}
			
			if ($words = D('Sensitive')->checkWords($data['name'])) {
            $this->baoError('活动内容含有敏感词：' . $words);
			}
			if ($words = D('Sensitive')->checkWords($data['title'])) {
				$this->baoError('活动标题含有敏感词：' . $words);
			}
			if ($words = D('Sensitive')->checkWords($data['contents'])) {
				$this->baoError('活动简介含有敏感词：' . $words);
			}
			$data['id'] = $id;
            $Houseworksetting->save($data);
            $this->baoSuccess('操作成功', U('housework/setting'));
        }else{
			$thumb = unserialize($detail['thumb']);
			$this->assign('thumb', $thumb);
			$this->assign('cates', D('Housekeepingcate')->fetchAll());
			$this->assign('shops', D('Shop')->find($detail['shop_id']));
            $this->assign('detail', $detail);
            $this->display(); 
        }
    }
	//添加家政
	public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Houseworksetting');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('housework/setting'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Housekeepingcate')->fetchAll());
            $this->display();
        }
    }
	//添加验证
	 private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_id'] = (int) $data['cate_id'];//ID
			if (empty($data['cate_id'])) {
				$this->baoError('类型ID不能为空');
			}
			
			$data['city_id'] = (int) $data['city_id'];
			if (empty($data['city_id'])) {
				$this->baoError('城市不能为空');
			}
			$data['area_id'] = (int) $data['area_id'];
			
			$data['shop_id'] = (int) $data['shop_id'];//商家ID
			if (empty($data['shop_id'])) {
            $this->baoError('请您选择商家');
     	    }
			$data['name'] = htmlspecialchars($_POST['name']);//标题名字
			if (empty($data['name'])) {
            $this->baoError('请您填写家政名字');
     	    }
            $data['title'] = htmlspecialchars($_POST['title']);
			if (empty($data['title'])) {
            $this->baoError('请您填写服务标题');
     	    }
            $data['price'] = (int)($_POST['price'] * 100);
			if (empty($data['price'])) {
            $this->baoError('价格不能为空');
            }
            $data['unit']  = htmlspecialchars($_POST['unit']);
            $data['gongju']  = htmlspecialchars($_POST['gongju']);
			
			$data['user'] = htmlspecialchars($_POST['user']);//标题名字
			if (empty($data['user'])) {
            $this->baoError('请您填写姓名');
     	    }
			$data['tel'] = htmlspecialchars($_POST['tel']);//标题名字
			if (empty($data['tel'])) {
            $this->baoError('请您手机号码');
     	    }
			if (!isPhone($data['tel']) && !isMobile($data['tel'])) {
            $this->baoError('联系电话格式不正确');
            }
			
            $data['photo']  = htmlspecialchars($_POST['photo']);
			if (empty($data['photo'])) {
            $this->baoError('请您上传图片');
            }
			
			$thumb = $this->_param('thumb', false);
			foreach ($thumb as $k => $val) {
				if (empty($val)) {
					unset($thumb[$k]);
				}
				if (!isImage($val)) {
					unset($thumb[$k]);
				}
			}
			$data['thumb'] = serialize($thumb);
			
		
            $data['biz_time']  = htmlspecialchars($_POST['biz_time']);
            $data['contents'] = SecurityEditorHtml($_POST['contents']);
			if (empty($data['contents'])) {
            $this->baoError('内容不能为空');
			}
			
			if ($words = D('Sensitive')->checkWords($data['name'])) {
            $this->baoError('活动内容含有敏感词：' . $words);
			}
			if ($words = D('Sensitive')->checkWords($data['title'])) {
				$this->baoError('活动标题含有敏感词：' . $words);
			}
			if ($words = D('Sensitive')->checkWords($data['contents'])) {
				$this->baoError('活动简介含有敏感词：' . $words);
			}
			$data['id'] = $id;
 
        return $data;
    }
	
	
    
    public function edit($housework_id){
        if ($housework_id = (int) $housework_id) {
            $obj = D('Housework');
            if (!$detail = $obj->find($housework_id)) {
                $this->baoError('请选择要编辑的活动');
            }
            if ($this->isPost()) {
                $data['is_real'] = (int)$this->_post('is_real');
                $data['num']     = (int)  $this->_post('num');
                $data['money']    = (int) ($this->_post('money')*100);
                $data['housework_id'] = $housework_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('housework/index'));
                }
                $this->baoError('操作失败');
            } else {
    
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的活动');
        }
        
        
    }
    
     public function delete($housework_id = 0) {
        if (is_numeric($housework_id) && ($housework_id = (int) $housework_id)) {
            $obj = D('Housework');
            $obj->delete($housework_id);
            $this->baoSuccess('删除成功！', U('housework/index'));
        } else {
            $housework_id = $this->_post('housework_id', false);
            if (is_array($housework_id)) {
                $obj = D('Housework');
                foreach ($housework_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('housework/index'));
            }
            $this->baoError('请选择要删除的预约');
        }
    }
	
	public function delete2($id = 0) {
        if (is_numeric($id) && ($id = (int) $id)) {
            $obj = D('Houseworksetting');
            $obj->save(array('id' => $id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('housework/setting'));
        } else {
            $housework_id = $this->_post('id', false);
            if (is_array($id)) {
                $obj = D('Houseworksetting');
                foreach ($id as $id) {
                    $obj->save(array('id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('批量删除成功！', U('housework/setting'));
            }
            $this->baoError('请选择要删除的预约项目');
        }
    }
    
}
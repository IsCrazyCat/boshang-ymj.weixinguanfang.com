<?php

class ShopkuaidiAction extends CommonAction {

	private $create_fields = array('type','shop_id','name', 'tel');
	private $edit_fields = array('type','shop_id','name', 'tel');
	private $listcreate_fields = array('type','shop_id','name', 'tel','shouzhong','xuzhong','province_id');
	private $listedit_fields = array('type','shop_id','name', 'tel','shouzhong','xuzhong','province_id');

	public function index() {
		$Pkuaidi = D('Pkuaidi');
		import('ORG.Util.Page');
		$map = array('type'=> goods,'closed'=> 0);
		if ($keyword = $this -> _param('keyword', 'htmlspecialchars')) {
			$map['name|tel'] = array('LIKE', '%' . $keyword . '%');
			$this -> assign('keyword', $keyword);
		}

        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }

		$count = $Pkuaidi-> where($map)->count(); 
		$Page = new Page($count, 20); 
		$show = $Page->show(); 
		$list = $Pkuaidi -> order(array('id' => 'desc')) -> where($map)->limit($Page->firstRow . ',' . $Page->listRows) -> select();
		$shop_ids = array();
			foreach ($list as $key => $val) {
				$shop_ids[$val['shop_id']] = $val['shop_id'];
			}
			$this->assign('shops', D('Shop')->itemsByIds($shop_ids));
		$this -> assign('list', $list);
		$this -> assign('page', $show);
		$this -> display();
	}

	public function create() {
		if ($this -> isPost()) {
			$data = $this -> createCheck();
			$obj = D('Pkuaidi');
			$data['id'] = $id;
			if ($obj -> add($data)) {
				$this -> baoSuccess('添加成功', U('shopkuaidi/index'));
			}
			$this -> baoError('操作失败！');
		} else {
			$this -> assign('id', $id);
			$this -> display();
		}
	}

	private function createCheck() {
		$data = $this -> checkFields($this -> _post('data', false), $this -> create_fields);
		$data['type'] = goods;
		$data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $shop = D('Shop')->find($data['shop_id']);
        if (empty($shop)) {
            $this->baoError('请选择正确的商家');
        }
		$data['name'] = htmlspecialchars($data['name']);
		if (empty($data['name'])) {
			$this -> baoError('运费模板不能为空');
		}
		$data['tel'] = (int)$data['tel'];
		return $data;
	}

	public function edit($kuaidi_id = 0) {
		if ($kuaidi_id = (int)$kuaidi_id) {
			$obj = D('Pkuaidi');
			if (!$detail = $obj -> find($kuaidi_id)) {
				$this -> baoError('请选择要编辑的运费模板');
			}
			if ($this -> isPost()) {
				$data = $this -> editCheck();
				$data['id'] = $kuaidi_id;
				if (false !== $obj -> save($data)) {
					$this -> baoSuccess('操作成功', U('shopkuaidi/index'));
				}
				$this -> baoError('操作失败');
			} else {
				$this -> assign('detail', $detail);
				$this -> display();
			}
		} else {
			$this -> baoError('请选择要编辑的运费模板');
		}
	}

	private function editCheck() {
		$data = $this -> checkFields($this -> _post('data', false), $this -> edit_fields);
		$data['type'] = goods;
		$data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $shop = D('Shop')->find($data['shop_id']);
        if (empty($shop)) {
            $this->baoError('请选择正确的商家');
        }
		$data['name'] = htmlspecialchars($data['name']);
		if (empty($data['name'])) {
			$this -> baoError('运费模板名不能为空');
		}
		$data['tel'] = (int)$data['tel'];
		return $data;
	}

	 public function delete($kuaidi_id = 0) {
        if (is_numeric($kuaidi_id) && ($kuaidi_id = (int) $kuaidi_id)) {
            $obj = D('Pkuaidi');
            $obj->save(array('id' => $kuaidi_id, 'closed' => 1));
            $this->baoSuccess('审核成功！', U('shopkuaidi/index'));
        } else {
            $kuaidi_id = $this->_post('kuaidi_id', false);
            if (is_array($kuaidi_id)) {
                $obj = D('Pkuaidi');
                foreach ($kuaidi_id as $id) {
                    $obj->save(array('id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('shopkuaidi/index'));
            }
            $this->baoError('请选择要删除的运费模板');
        }
    }
	
	
	 public function audit($kuaidi_id = 0) {
        if (is_numeric($kuaidi_id) && ($kuaidi_id = (int) $kuaidi_id)) {
            $obj = D('Pkuaidi');
            $obj->save(array('id' => $kuaidi_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('shopkuaidi/index'));
        } else {
            $kuaidi_id = $this->_post('kuaidi_id', false);
            if (is_array($kuaidi_id)) {
                $obj = D('Pkuaidi');
                foreach ($kuaidi_id as $id) {
                    $obj->save(array('id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('shopkuaidi/index'));
            }
            $this->baoError('请选择要审核的运费模板');
        }
    }

	public function lists($kuaidi_id = 0) {
		if ($kuaidi_id = (int)$kuaidi_id) {
			$lists = D('Pyunfei');
			import('ORG.Util.Page');
			$map = array('type'=> goods);
			if ($keyword = $this -> _param('keyword', 'htmlspecialchars')) {
				$map['name'] = array('LIKE', '%' . $keyword . '%');
				$this -> assign('keyword', $keyword);
			}
			$map['kuaidi_id'] = $kuaidi_id;
			$count = $lists ->where($map)->count(); 
			$Page = new Page($count, 20); 
			$show = $Page->show(); 
			$list = $lists -> order(array('id' => 'desc')) -> where($map)->limit($Page->firstRow . ',' . $Page->listRows) -> select();
			$shop_ids = array();
			foreach ($list as $key => $val) {
				$shop_ids[$val['shop_id']] = $val['shop_id'];
			}
			$this->assign('shops', D('Shop')->itemsByIds($shop_ids));
			$this -> assign('list', $list);
			$this -> assign('page', $show);
			$this -> assign('count', $count);
			$this -> assign('kuaidi_id', $kuaidi_id);
			$this -> display();
		} else {
			$this -> baoError('请选择运费模板');
		}
	}
	
	public function listcreate($kuaidi_id = 0){
		$kuaidi_id = (int)$kuaidi_id;
		$detail = D('Pkuaidi') -> find($kuaidi_id);
		if ($this -> isPost()) {
			$data = $this -> listcreateCheck();
			$obj = D('Pyunfei');
			$data['kuaidi_id'] = $kuaidi_id;
			$data['shop_id'] = $detail['shop_id'];
			if ($obj -> add($data)) {
				$this -> baoSuccess('添加成功', U('shopkuaidi/lists',array('kuaidi_id' =>$kuaidi_id)));
			}
			$this -> baoError('操作失败！');
		} else {
			$provinceList = D('Paddlist') -> where(array('level' => 1)) -> select();
			$this -> assign('provinceList', $provinceList);
			$this -> assign('kuaidi_id', $kuaidi_id);
			$this -> display();
		}
	}

	private function listcreateCheck() {
		$data = $this -> checkFields($this -> _post('data', false), $this -> listcreate_fields);
		$data['type'] = goods;
		$data['name'] = htmlspecialchars($data['name']);
		if (empty($data['name'])) {
			$this -> baoError('名称不能为空');
		}
		$data['province_id'] = (int)$data['province_id'];
		if (empty($data['province_id'])) {
            $this->baoError('请选择区域');
        } 
		$data['shouzhong'] = (int) ($data['shouzhong'] * 100);
        if (empty($data['shouzhong'])) {
            $this->baoError('首重价格不能为空');
        }  
        $data['xuzhong'] = (int) ($data['xuzhong'] * 100);
        if (empty($data['xuzhong'])) {
            $this->baoError('续重价格不能为空');
        }
		if ($data['xuzhong'] >= $data['shouzhong'] ) {
            $this->baoError('续重价格不能大于首重');
        }
		return $data;
	}
	public function listedit($yunfei_id = 0) {
		$kuaidi_id = (int)$kuaidi_id;
		$detail = D('Pkuaidi') -> find($kuaidi_id);
		if ($yunfei_id = (int)$yunfei_id) {
			$obj = D('Pyunfei');
			if (!$detail = $obj -> find($yunfei_id)) {
				$this -> baoError('请选择要编辑的运费设置');
			}
			if ($this -> isPost()) {
				$data = $this -> listeditCheck();
				$data['id'] = $yunfei_id;
				$data['shop_id'] = $detail['shop_id'];
				if (false !== $obj -> save($data)) {
					$this -> baoSuccess('操作成功', U('shopkuaidi/lists',array('kuaidi_id' =>$detail['kuaidi_id'])));
				}
				$this -> baoError('操作失败');
			} else {
				$provinceList = D('Paddlist') -> where(array('level' => 1)) -> select();
				$this -> assign('provinceList', $provinceList);
				$this -> assign('detail', $detail);
				$this -> display();
			}
		} else {
			$this -> baoError('请选择要编辑的运费设置');
		}
	}

	private function listeditCheck() {
		$data = $this -> checkFields($this -> _post('data', false), $this -> listedit_fields);
		$data['type'] = goods;
		$data['name'] = htmlspecialchars($data['name']);
		if (empty($data['name'])) {
			$this -> baoError('名称不能为空');
		}
		$data['province_id'] = (int)$data['province_id'];
		if (empty($data['province_id'])) {
            $this->baoError('请选择区域');
        } 
		$data['shouzhong'] = (int) ($data['shouzhong'] * 100);
        if (empty($data['shouzhong'])) {
            $this->baoError('首重价格不能为空');
        }  
        $data['xuzhong'] = (int) ($data['xuzhong'] * 100);
        if (empty($data['xuzhong'])) {
            $this->baoError('续重价格不能为空');
        }
		if ($data['xuzhong'] >= $data['shouzhong'] ) {
            $this->baoError('续重价格不能大于首重');
        }
		return $data;
	}
	
	
	public function listdelete($yunfei_id = 0) {
		if (is_numeric($yunfei_id) && ($yunfei_id = (int)$yunfei_id)) {
			$obj = D('Pyunfei');
			$detail = $obj -> find($yunfei_id);
			$obj -> delete($yunfei_id);
			$this -> baoSuccess('删除成功！', U('shopkuaidi/lists',array('kuaidi_id' =>$detail['kuaidi_id'])));
		} else {
			$yunfei_id = $this -> _post('yunfei_id', false);
			if (is_array($yunfei_id)) {
				$obj = D('Pyunfei');
				$detail = $obj -> find($yunfei_id);
				foreach ($yunfei_id as $id) {
					$obj -> delete($id);
				}
				$obj -> cleanCache();
				$this -> baoSuccess('删除成功！', U('shopkuaidi/index'));
			}
			$this -> baoError('请选择要删除的运费选项');
		}
	}
	

	
}

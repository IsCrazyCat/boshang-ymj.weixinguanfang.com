<?php

class logisticsAction extends CommonAction {

	private $create_fields = array('shop_id','express_com', 'express_name','orderby');
	private $edit_fields = array('shop_id','express_com', 'express_name','orderby');


	public function index() {
		$Logistics = D('Logistics');
		import('ORG.Util.Page');
		$map = array('closed'=> 0);
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
		$count = $Logistics-> where($map)->count(); 
		$Page = new Page($count, 20); 
		$show = $Page->show(); 
		$list = $Logistics -> order(array('express_id' => 'desc')) -> where($map)->limit($Page->firstRow . ',' . $Page->listRows) -> select();
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
			$obj = D('Logistics');
			if ($obj -> add($data)) {
				$this -> baoSuccess('添加成功', U('logistics/index'));
			}
			$this -> baoError('操作失败！');
		}else{
			$this -> display();
		}
	}

	private function createCheck() {
		$data = $this -> checkFields($this -> _post('data', false), $this -> create_fields);
		$data['shop_id'] = (int) $data['shop_id'];
        $data['express_com'] = htmlspecialchars($data['express_com']);
		if (empty($data['express_com'])) {
			$this -> baoError('快递编号必须填写');
		}
		$data['express_name'] = htmlspecialchars($data['express_name']);
		if (empty($data['express_name'])) {
			$this -> baoError('快递名称不能为空');
		}
		$data['orderby'] = (int) $data['orderby'];
		$data['create_time'] = NOW_TIME;
		return $data;
	}

	public function edit($express_id = 0) {
		if ($express_id = (int)$express_id) {
			$obj = D('Logistics');
			if (!$detail = $obj -> find($express_id)) {
				$this -> baoError('请选择要编辑的快递');
			}
			if ($this -> isPost()) {
				$data = $this -> editCheck();
				$data['express_id'] = $express_id;
				if (false !== $obj -> save($data)) {
					$this -> baoSuccess('操作成功', U('logistics/index'));
				}
				$this -> baoError('操作失败');
			} else {
				$this -> assign('detail', $detail);
				$this->assign('shops', D('Shop')->find($detail['shop_id']));
				$this -> display();
			}
		} else {
			$this -> baoError('请选择要编辑的运费模板');
		}
	}

	private function editCheck() {
		$data = $this -> checkFields($this -> _post('data', false), $this -> edit_fields);
		$data['shop_id'] = (int) $data['shop_id'];
        $shop = D('Shop')->find($data['shop_id']);
		$data['express_com'] = htmlspecialchars($data['express_com']);
		if (empty($data['express_com'])) {
			$this -> baoError('快递编号必须填写');
		}
		$data['express_name'] = htmlspecialchars($data['express_name']);
		if (empty($data['express_name'])) {
			$this -> baoError('快递名称不能为空');
		}
		$data['orderby'] = (int) $data['orderby'];
		$data['create_time'] = NOW_TIME;
		return $data;
	}

	 public function delete($express_id = 0) {
        if (is_numeric($express_id) && ($express_id = (int) $express_id)) {
            $obj = D('Logistics');
            $obj->save(array('express_id' => $express_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('logistics/index'));
        } else {
            $express_id = $this->_post('express_id', false);
            if (is_array($express_id)) {
                $obj = D('Logistics');
                foreach ($express_id as $id) {
                    $obj->save(array('id' => $express_id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('logistics/index'));
            }
            $this->baoError('请选择要批量删除的快递');
        }
    }
	
	
}

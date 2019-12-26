<?php
class PoisAction extends CommonAction {
	
	 protected function _initialize() {
        parent::_initialize();
        $getType = D('Biz')->getType();
        $this->assign('getType', $getType);
		$this->citys = D('City')->fetchAll();
        $this->assign('citys', $this->citys);
    }
		

    private $create_fields = array('name', 'shop_id' ,'city_id','type','photo', 'lat' , 'lng' , 'telephone' , 'address', 'tag', 'is_lock', 'orderby' ,'status', 'create_time');
    private $edit_fields = array('name', 'shop_id' ,'city_id', 'type', 'photo','lat' , 'lng' , 'telephone' , 'address', 'tag', 'is_lock', 'orderby' ,'status', 'create_time');
    private $word_fields = array('text','price');
	
    public function index() {
        $Pois = D('Near');
        import('ORG.Util.Page'); 
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|type'] =  array('LIKE',array('%'.$keyword.'%','%'.$keyword,$keyword.'%','OR'));
            $this->assign('keyword', $keyword);
        }
        $count = $Pois->where($map)->count();
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Pois->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

	
    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
			$seed=md5(microtime()).md5(mt_rand(0,31));
			$data['uid'] = substr(md5($seed),0,24);
			if($data['shop_id']!=''){
				$data['status'] = 1;
			}else{
				$data['status'] = 0;
			}
			$data['create_time'] = time();	
			$data['is_lock'] = 1;
            $obj = D('Near');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('pois/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);

        if (empty($data['name'])) {
            $this->baoError('名称不能为空');
        }
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
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
    

    public function edit($pois_id = 0) {
        if ($pois_id = (int) $pois_id) {
            $obj = D('Near');
            if (!$detail = $obj->find($pois_id)) {
                $this->baoError('请选择要编辑的黄页');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['pois_id'] = $pois_id;
				if($data['status']== 0){
					$data['shop_id'] = 0;
				}
				
                if (false != $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('pois/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的黄页');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);

        if (empty($data['name'])) {
            $this->baoError('名称不能为空');
        }
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
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
    
	
	
    public function stick() {
        $Pois = D('Near');
		$Word = D('Nearword');
        import('ORG.Util.Page'); // 导入分页类
		$time = time();
        $map ="pois_id <> ''"  ;
        $count = $Word->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Word->where($map)->order(array('word_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		
		$ids =array();
        foreach ($list as $k => $val) {
            $ids[$val['pois_id']] = $val['pois_id'];
        }
		
        if ($ids) {
            $this->assign('poiss', D('Near')->itemsByIds($ids));
        }
		
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
	
	
	
    public function word() {
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['text'] =  array('LIKE',array('%'.$keyword.'%','%'.$keyword,$keyword.'%','OR'));
            $this->assign('keyword', $keyword);
        }
        $Pois = D('Nearword');
        import('ORG.Util.Page'); // 导入分页类
        $count = $Pois->where()->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Pois->where($map)->order(array('pois_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
	
	

	public function wordcreate() {
        if ($this->isPost()) {
			$data = $this->checkFields($this->_post('data', false), $this->word_fields);
            if (D('Nearword') ->add($data)) {
                $this->baoSuccess('添加成功', U('pois/word'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
	}
	
    public function wordedit($word_id = 0) {
        if ($word_id = (int) $word_id) {
            $obj = D('Nearword');
            if (!$detail = $obj->find($word_id)) {
                $this->baoError('请选择要编辑的词条');
            }
            if ($this->isPost()) {
				$data = $this->checkFields($this->_post('data', false), $this->word_fields);
				$data['word_id'] = $detail['word_id'];
                if (false != $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('pois/word'));
                }else{
					$this->baoError('操作失败'.$data['price']);
				}
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的词条');
        }
    }
	
	
    public function delete($pois_id = 0) {
        if (is_numeric($pois_id) && ($pois_id = (int) $pois_id)) {
            $obj = D('Near');
            $obj->delete($pois_id);
            $this->baoSuccess('删除成功！', U('pois/index'));
        } else {
            $pois_id = $this->_post('pois_id', false);
            if (is_array($pois_id)) {
                $obj = D('Near');
                foreach ($pois_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('pois/index'));
            }
            $this->baoError('请选择要删除的黄页');
        }
    }
	
	
	public function clean($word_id = 0) {
        if ($word_id = (int) $word_id) {
            $obj = D('Nearword');
            if (!$detail = $obj->find($word_id)) {
                $this->baoError('请选择要编辑的词条');
            }
			
			$data['word_id'] = $word_id;
			$data['pois_id'] = '';
			$data['over_time'] = '';
		
			if (false != $obj->save($data)) {
				$this->baoSuccess('操作成功', U('pois/stick'));
			}else{
				
				$this->baoError('操作失败');
			}
		
        } else {
            $this->baoError('请选择要编辑的词条');
        }
	}
}

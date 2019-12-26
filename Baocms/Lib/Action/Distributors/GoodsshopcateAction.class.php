<?php



class GoodsshopcateAction extends CommonAction {

    private $create_fields = array( 'cate_name', 'orderby', 'shop_id');
    private $edit_fields = array( 'cate_name', 'orderby', 'shop_id');

    public function index() {
        $autocates = D('Goodsshopcate')->order(array('orderby' => 'asc'))->where(array('shop_id' => $this->shop_id))->select();   
		$map = array('closed' => 0, 'shop_id' => $this->shop_id, 'is_mall' => 1);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if (!empty($keyword)) {
            $map['cate_name|orderby'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
		}
		$this->assign('autocates',$autocates);
         $this->display();      
    }
    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Goodsshopcate');
            $data['shop_id'] = $this->shop_id;
            if ($obj->add($data)) {
                $this->success('添加成功', U('goodsshopcate/index'));
            }
            $this->error('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->error('分类不能为空');
        }
        $detail = D('Goodsshopcate')->where(array('shop_id'=>$this->shop_id,'cate_name'=>$data['cate_name']))->select();
        if(!empty($detail)){
            $this->error('分类名称已存在');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    
        public function edit($cate_id=0) {
        if ($cate_id = (int) $cate_id) {
            $obj = D('Goodsshopcate');
            if (!$detail = $obj->find($cate_id)) {
                $this->error('请选择要编辑的商家分类');
            }
            if($detail['shop_id'] != $this->shop_id){
                $this->error('不可以修改别人的内容');
            }
            
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                $data['shop_id'] = $this->shop_id;
                if (false !== $obj->save($data)) {
                    $this->success('操作成功', U('goodsshopcate/index'));
                }
                $this->error('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('请选择要编辑的商家分类');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->error('分类不能为空');
        }
        $detail = D('Goodsshopcate')->where(array('shop_id'=>$this->shop_id,'cate_name'=>$data['cate_name']))->select();
        if(!empty($detail)){
            $this->error('分类名称已存在');
        }
        $data['orderby'] = (int) $data['orderby'];
        if (empty($data['orderby'])) {
           $data['orderby'] = 100;
        }
        return $data;
    }
    
    public function delete($cate_id=0){
        if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
            $obj = D('Goodsshopcate');
            if (!$detail = $obj->find($cate_id)) {
                $this->error('该分类不存在');
            }
            if($detail['shop_id'] != $this->shop_id){
                $this->error('该分类不存在');
            }
            
            $obj->delete($cate_id);
            $this->success('删除成功！', U('goodsshopcate/index'));
        }
    }
    
    
    

}

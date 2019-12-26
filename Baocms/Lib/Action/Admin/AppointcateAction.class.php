<?php
class AppointcateAction extends CommonAction{
    private $create_fields = array('cate_name', 'photo', 'orderby');
    private $edit_fields = array('cate_name', 'photo', 'orderby');
    public function index(){
        $Appointcate = D('Appointcate');
        $list = $Appointcate->fetchAll();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create($parent_id = 0){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Appointcate');
            $data['parent_id'] = $parent_id;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('appointcate/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('parent_id', $parent_id);
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function edit($cate_id = 0){
        if ($cate_id = (int) $cate_id) {
            $obj = D('Appointcate');
            if (!($detail = $obj->find($cate_id))) {
                $this->baoError('请选择要编辑的家政分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('appointcate/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的家政分类');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function delete($cate_id = 0){
            $cate_id = (int) $cate_id;
            $obj = D('Appointcate');
			if(false == D('Appointcate')->check_parent_id($cate_id)){
				$this->baoError('当前分类下面还有二级分类');
			}
			if(false == D('Appointcate')->check_cate_id_appoint($cate_id)){
				$this->baoError('当前分类下面还有家政服务删除');
			}
			
            $obj->delete($cate_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('appointcate/index'));
    }
    public function update(){
        $orderby = $this->_post('orderby', false);
        $obj = D('Appointcate');
        foreach ($orderby as $key => $val) {
            $data = array('cate_id' => (int) $key, 'orderby' => (int) $val);
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->baoSuccess('更新成功', U('appointcate/index'));
    }
}
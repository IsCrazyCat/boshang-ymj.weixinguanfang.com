<?php
class CrowdcateAction extends CommonAction{
    private $create_fields = array('cate_name', 'rate', 'select1', 'select2', 'select3', 'select4', 'select5', 'orderby');
    private $edit_fields = array('cate_name', 'rate', 'select1', 'select2', 'select3', 'select4', 'select5', 'orderby');
    public function index(){
        $Crowdcate = D('Crowdcate');
        $list = $Crowdcate->fetchAll();
		$Crowd = D('Crowd');
        foreach ($list as $key => $v) {
            if ($v['cate_id']) {
                $catids = $Crowdcate->getChildren($v['cate_id']);
                if (!empty($catids)) {
                    $count = $Crowd->where(array('cate_id' => array('IN', $catids),'closed' => 0))->count();
                } else {
                    $count = $Crowd->where(array('cate_id' => $cat, 'closed' => 0))->count();
                }
            }
            $list[$key]['count'] = $count;
        }
		$this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create($parent_id = 0){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Crowdcate');
            $data['parent_id'] = $parent_id;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('crowdcate/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('parent_id', $parent_id);
            $this->display();
        }
    }
    public function child($parent_id = 0){
        $datas = D('Crowdcate')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str .= '<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '(' . $var2['rate'] . ')‰' . '</option>' . '

';
                        foreach ($datas as $var3) {
                            if ($var3['parent_id'] == $var2['cate_id']) {
                                $str .= '<option value="' . $var3['cate_id'] . '">  --' . $var3['cate_name'] . '(' . $var3['rate'] . ')‰' . '</option>' . '

';
                            }
                        }
                    }
                }
            }
        }
        echo $str;
        die;
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类不能为空');
        }
		$data['rate'] = (int) $data['rate'];
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function edit($cate_id = 0){
        if ($cate_id = (int) $cate_id) {
            $obj = D('Crowdcate');
            if (!($detail = $obj->find($cate_id))) {
                $this->baoError('请选择要编辑的众筹分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('crowdcate/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的众筹分类');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类不能为空');
        }
		$data['rate'] = (int) $data['rate'];
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function createone($parent_id = 0){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Crowdcate');
            $data['parent_id'] = $parent_id;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('crowdcate/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('parent_id', $parent_id);
            $this->display();
        }
    }
   
    public function delete($cate_id = 0){
        if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
            $obj = D('Crowdcate');
            $obj->delete($cate_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('crowdcate/index'));
        } else {
            $cate_id = $this->_post('cate_id', false);
            if (is_array($cate_id)) {
                $obj = D('Crowdcate');
                foreach ($cate_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('crowdcate/index'));
            }
            $this->baoError('请选择要删除的众筹分类');
        }
    }
  
    public function update(){
        $orderby = $this->_post('orderby', false);
        $obj = D('Crowdcate');
        foreach ($orderby as $key => $val) {
            $data = array('cate_id' => (int) $key, 'orderby' => (int) $val);
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->baoSuccess('更新成功', U('crowdcate/index'));
    }
}
<?php
class AreaAction extends CommonAction{
    private $create_fields = array('area_name', 'city_id', 'orderby');
    private $edit_fields = array('area_name', 'city_id', 'orderby');
    public function index(){
        $Area = D('Area');
        import('ORG.Util.Page');
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['area_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('keyword', $keyword);
        $city_id = (int) $this->_param('city_id');
        if ($city_id) {
            $map['city_id'] = $city_id;
        }
        $this->assign('city_id', $city_id);
        $count = $Area->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Area->where($map)->order(array('area_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('citys', D('City')->fetchAll());
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Area');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('area/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('citys', D('City')->fetchAll());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['area_name'] = htmlspecialchars($data['area_name']);
        if (empty($data['area_name'])) {
            $this->baoError('区域名称不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['city_id'] = (int) $data['city_id'];
        return $data;
    }
    public function edit($area_id = 0){
        if ($area_id = (int) $area_id) {
            $obj = D('Area');
            if (!($detail = $obj->find($area_id))) {
                $this->baoError('请选择要编辑的区域管理');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['area_id'] = $area_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('area/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('citys', D('City')->fetchAll());
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的区域管理');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['area_name'] = htmlspecialchars($data['area_name']);
        if (empty($data['area_name'])) {
            $this->baoError('区域名称不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['city_id'] = (int) $data['city_id'];
        return $data;
    }
    public function delete($area_id = 0){
        if (is_numeric($area_id) && ($area_id = (int) $area_id)) {
            $obj = D('Area');
			$count = D('Business')->where(array('area_id'=>$area_id))->count;
			if($count > 0){
				$this->baoError('该区域下面还有商圈，请先删除对应的商圈');
			}
            $obj->delete($area_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('area/index'));
        } else {
            $area_id = $this->_post('area_id', false);
            if (is_array($area_id)) {
                $obj = D('Area');
                foreach ($area_id as $id) {
					$count = D('Business')->where(array('area_id'=>$id))->count;
					if($count > 0){
						$this->baoError('该区域下面还有商圈，请先删除对应的商圈');
					}
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('area/index'));
            }
            $this->baoError('请选择要删除的区域管理');
        }
    }
}
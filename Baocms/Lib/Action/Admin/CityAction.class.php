<?php


class CityAction extends CommonAction {

    private $create_fields = array('name', 'pinyin','photo', 'is_open', 'lng', 'lat','orderby','theme','first_letter','domain');
    private $edit_fields = array('name', 'pinyin','photo', 'is_open', 'lng', 'lat','orderby','theme','first_letter','domain');

    public function index() {
        $City = D('City');
        import('ORG.Util.Page'); // 导入分页类
        $map = array();
        $keyword = $this->_param('keyword','htmlspecialchars');
        if($keyword){
            $map['name|pinyin'] = array('LIKE', '%'.$keyword.'%');
        }    
        $this->assign('keyword',$keyword);

        
        $count = $City->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $City->where($map)->order(array('city_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('City');
            if ($obj->add($data)) {
                 $obj->cleanCache();
                $this->baoSuccess('添加成功', U('city/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('themes',D('Template')->fetchAll());
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('城市名称不能为空');
        } $data['pinyin'] = htmlspecialchars($data['pinyin']);
        if (empty($data['pinyin'])) {
            $this->baoError('城市拼音不能为空');
        }
		$data['photo'] = htmlspecialchars($data['photo']);
        if (!isImage($data['photo'])) {
            $this->baoError('请上传logo');
        }
        $data['is_open'] = (int)($data['is_open']);
		$data['domain'] = (int)($data['domain']);
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['first_letter'] = htmlspecialchars($data['first_letter']);
        $data['theme'] = htmlspecialchars($data['theme']);
        $data['orderby'] = (int)($data['orderby']);
        return $data;
    }

    public function edit($city_id = 0) {
        if ($city_id = (int) $city_id) {
            $obj = D('City');
            if (!$detail = $obj->find($city_id)) {
                $this->baoError('请选择要编辑的城市站点');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['city_id'] = $city_id;
                if (false !== $obj->save($data)) {
                     $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('city/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('themes',D('Template')->fetchAll());
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的城市站点');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('城市名称不能为空');
        } $data['pinyin'] = htmlspecialchars($data['pinyin']);
        if (empty($data['pinyin'])) {
            $this->baoError('城市拼音不能为空');
        }
		$data['photo'] = htmlspecialchars($data['photo']);
        if (!isImage($data['photo'])) {
            $this->baoError('请上传logo');
        }
        $data['is_open'] = (int)($data['is_open']);
		$data['domain'] = (int)($data['domain']);
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['first_letter'] = htmlspecialchars($data['first_letter']);
        $data['orderby'] = (int)($data['orderby']);
        $data['theme'] = htmlspecialchars($data['theme']);
        return $data;
    }

    public function delete($city_id = 0) {
        if (is_numeric($city_id) && ($city_id = (int) $city_id)) {
            $obj = D('City');
            $obj->delete($city_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('city/index'));
        } else {
            $city_id = $this->_post('city_id', false);
            if (is_array($city_id)) {
                $obj = D('City');
                foreach ($city_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('city/index'));
            }
            $this->baoError('请选择要删除的城市站点');
        }
    }

}

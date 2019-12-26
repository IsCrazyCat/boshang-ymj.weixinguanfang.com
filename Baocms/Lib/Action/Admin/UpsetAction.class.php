<?php

class UpsetAction extends CommonAction {

    public function index() {
        $set = D('Uploadset');
        $list = $set->order(array('id' => 'desc'))->select();

        $this->assign('list', $list); // 赋值数据集
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
        $data['is_open'] = (int)($data['is_open']);
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['first_letter'] = htmlspecialchars($data['first_letter']);
        $data['orderby'] = (int)($data['orderby']);
        return $data;
    }

    public function edit($id = 0) {
        if ($id = (int)$id) {
            $obj = D('Uploadset');
            if (!$detail = $obj->find($id)) {
                $this->baoError('请选择要编辑的方式');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
				
					$filename = 'ueconfig.json';
		

					$datajson = $_POST['para'];
					$datajson['status'] = $_POST['status'];

					$d = json_encode($datajson);



					file_put_contents(APP_PATH.'../Public/qiniu_ueditor/php/'.$filename, $d);

                    $this->baoSuccess('操作成功', U('Upset/index'));
                }
                $this->baoError('操作失败');
            } else {
                $detail['para'] = json_decode($detail['para'],true);
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的方式');
        }
    }

    private function editCheck() {
        $data['status'] = (int)($_POST['status']);
        $data['para'] = json_encode($_POST['para']);
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

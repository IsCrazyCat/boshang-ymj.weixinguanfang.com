<?php
class AdsiteAction extends CommonAction
{
    private $create_fields = array('site_name', 'theme', 'site_type', 'site_place');
    private $edit_fields = array('site_name', 'theme', 'site_type', 'site_place');
    public function index()
    {
        $Adsite = D('Adsite');
        $this->assign('adsite', $Adsite->fetchAll());
        $this->assign('types', $Adsite->getType());
        $this->assign('place', $Adsite->getPlace());
        $this->display();
        // 输出模板
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Adsite');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('adsite/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $Adsite = D('Adsite');
            $Template = D('Template');
            $this->assign('adsite', $Adsite->fetchAll());
            $this->assign('template', $Template->fetchAll());
            $this->assign('types', $Adsite->getType());
            $this->assign('place', $Adsite->getPlace());
            $this->display();
            // 输出模板
        }
    }
    public function edit($site_id = 0)
    {
        if ($site_id = (int) $site_id) {
            $obj = D('Adsite');
            if (!($detail = $obj->find($site_id))) {
                $this->baoError('请选择需要编辑的广告位');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['site_id'] = $site_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('adsite/index'));
                }
                $this->baoError('操作失败');
            } else {
                $Adsite = D('Adsite');
                $Template = D('Template');
                $this->assign('adsite', $Adsite->fetchAll());
                $this->assign('template', $Template->fetchAll());
                $this->assign('types', $Adsite->getType());
                $this->assign('place', $Adsite->getPlace());
                $this->assign('detail', $detail);
                $this->display();
                // 输出模板
            }
        } else {
            $this->baoError('请选择要编辑的商家分类');
        }
    }
    public function delete($site_id = 0)
    {
        if (is_numeric($site_id) && ($site_id = (int) $site_id)) {
            $obj = D('Adsite');
            $obj->delete($site_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('adsite/index'));
        } else {
            $site_id = $this->_post('site_id', false);
            if (is_array($site_id)) {
                $obj = D('Adsite');
                foreach ($site_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('adsite/index'));
            }
            $this->baoError('请选择要删除的广告位');
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['site_name'] = htmlspecialchars($data['site_name']);
        if (empty($data['site_name'])) {
            $this->baoError('广告位名称不能为空');
        }
        return $data;
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['site_name'] = htmlspecialchars($data['site_name']);
        if (empty($data['site_name'])) {
            $this->baoError('广告位名称不能为空');
        }
        return $data;
    }
}
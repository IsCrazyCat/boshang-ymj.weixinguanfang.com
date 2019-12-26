<?php
class SeoAction extends CommonAction
{
    private $create_fields = array('seo_key', 'seo_explain', 'seo_title', 'seo_keywords', 'seo_desc');
    private $edit_fields = array('seo_key', 'seo_explain', 'seo_title', 'seo_keywords', 'seo_desc');
    public function index()
    {
        $Seo = D('Seo');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['seo_key'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Seo->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Seo->where($map)->order(array('seo_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Seo');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('seo/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['seo_key'] = htmlspecialchars($data['seo_key']);
        if (empty($data['seo_key'])) {
            $this->baoError('标签不能为空');
        }
        $data['seo_explain'] = htmlspecialchars($data['seo_explain']);
        if (empty($data['seo_explain'])) {
            $this->baoError('说明不能为空');
        }
        $data['seo_title'] = htmlspecialchars($data['seo_title']);
        if (empty($data['seo_title'])) {
            $this->baoError('SEO标题不能为空');
        }
        $data['seo_keywords'] = htmlspecialchars($data['seo_keywords']);
        if (empty($data['seo_keywords'])) {
            $this->baoError('SEO关键字不能为空');
        }
        $data['seo_desc'] = htmlspecialchars($data['seo_desc']);
        if (empty($data['seo_desc'])) {
            $this->baoError('SEO描述不能为空');
        }
        return $data;
    }
    public function edit($seo_id = 0)
    {
        if ($seo_id = (int) $seo_id) {
            $obj = D('Seo');
            if (!($detail = $obj->find($seo_id))) {
                $this->baoError('请选择要编辑的SEO设置');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['seo_id'] = $seo_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('seo/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的SEO设置');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['seo_key'] = htmlspecialchars($data['seo_key']);
        if (empty($data['seo_key'])) {
            $this->baoError('标签不能为空');
        }
        $data['seo_explain'] = htmlspecialchars($data['seo_explain']);
        if (empty($data['seo_explain'])) {
            $this->baoError('说明不能为空');
        }
        $data['seo_title'] = htmlspecialchars($data['seo_title']);
        if (empty($data['seo_title'])) {
            $this->baoError('SEO标题不能为空');
        }
        $data['seo_keywords'] = htmlspecialchars($data['seo_keywords']);
        if (empty($data['seo_keywords'])) {
            $this->baoError('SEO关键字不能为空');
        }
        $data['seo_desc'] = htmlspecialchars($data['seo_desc']);
        if (empty($data['seo_desc'])) {
            $this->baoError('SEO描述不能为空');
        }
        return $data;
    }
    public function delete($seo_id = 0)
    {
        if (is_numeric($seo_id) && ($seo_id = (int) $seo_id)) {
            $obj = D('Seo');
            $obj->delete($seo_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('seo/index'));
        } else {
            $seo_id = $this->_post('seo_id', false);
            if (is_array($seo_id)) {
                $obj = D('Seo');
                foreach ($seo_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('seo/index'));
            }
            $this->baoError('请选择要删除的SEO设置');
        }
    }
}
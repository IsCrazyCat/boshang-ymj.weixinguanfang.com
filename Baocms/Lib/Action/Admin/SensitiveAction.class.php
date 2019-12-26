<?php
class SensitiveAction extends CommonAction
{
    private $create_fields = array('words');
    private $edit_fields = array('words');
    public function index()
    {
        $Sensitive = D('Sensitive');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['words'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Sensitive->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Sensitive->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
            $obj = D('Sensitive');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('sensitive/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['words'] = htmlspecialchars($data['words']);
        if (empty($data['words'])) {
            $this->baoError('关键词不能为空');
        }
        return $data;
    }
    public function edit($words_id = 0)
    {
        if ($words_id = (int) $words_id) {
            $obj = D('Sensitive');
            if (!($detail = $obj->find($words_id))) {
                $this->baoError('请选择要编辑的敏感词');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['words_id'] = $words_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('sensitive/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的敏感词');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['words'] = htmlspecialchars($data['words']);
        if (empty($data['words'])) {
            $this->baoError('关键词不能为空');
        }
        return $data;
    }
    public function delete($words_id = 0)
    {
        if (is_numeric($words_id) && ($words_id = (int) $words_id)) {
            $obj = D('Sensitive');
            $obj->delete($words_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('sensitive/index'));
        } else {
            $words_id = $this->_post('words_id', false);
            if (is_array($words_id)) {
                $obj = D('Sensitive');
                foreach ($words_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('sensitive/index'));
            }
            $this->baoError('请选择要删除的敏感词');
        }
    }
}
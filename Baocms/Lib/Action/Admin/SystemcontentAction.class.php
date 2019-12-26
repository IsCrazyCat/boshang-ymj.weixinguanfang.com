<?php
class SystemcontentAction extends CommonAction
{
    private $create_fields = array('title', 'contents', 'orderby');
    private $edit_fields = array('title', 'contents', 'orderby');
    public function index()
    {
        $Systemcontent = D('Systemcontent');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        $count = $Systemcontent->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Systemcontent->where($map)->order(array('orderby' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
            $obj = D('Systemcontent');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('systemcontent/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['contents'] = SecurityEditorHtml($data['contents']);
        if (empty($data['contents'])) {
            $this->baoError('内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['contents'])) {
            $this->baoError('内容含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['orderby'] = (int) $data['orderby'];
        if (empty($data['orderby'])) {
            $this->baoError('排序不能为空');
        }
        return $data;
    }
    public function edit($content_id = 0)
    {
        if ($content_id = (int) $content_id) {
            $obj = D('Systemcontent');
            if (!($detail = $obj->find($content_id))) {
                $this->baoError('请选择要编辑的系统文章');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['content_id'] = $content_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('systemcontent/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的系统文章');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['contents'] = SecurityEditorHtml($data['contents']);
        if (empty($data['contents'])) {
            $this->baoError('内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['contents'])) {
            $this->baoError('内容含有敏感词：' . $words);
        }
        $data['orderby'] = (int) $data['orderby'];
        if (empty($data['orderby'])) {
            $this->baoError('排序不能为空');
        }
        return $data;
    }
    public function delete($content_id = 0)
    {
        if (is_numeric($content_id) && ($content_id = (int) $content_id)) {
            $obj = D('Systemcontent');
            $obj->delete($content_id);
            $this->baoSuccess('删除成功！', U('systemcontent/index'));
        } else {
            $content_id = $this->_post('content_id', false);
            if (is_array($content_id)) {
                $obj = D('Systemcontent');
                foreach ($content_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('systemcontent/index'));
            }
            $this->baoError('请选择要删除的系统文章');
        }
    }
}
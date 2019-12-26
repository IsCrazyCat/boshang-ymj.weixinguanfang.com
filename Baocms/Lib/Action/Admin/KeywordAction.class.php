<?php
class KeywordAction extends CommonAction
{
    private $create_fields = array('keyword', 'type');
    private $edit_fields = array('keyword', 'type');
    public function _initialize()
    {
        parent::_initialize();
        $this->type = D('Keyword')->getKeyType();
        $this->assign('types', $this->type);
    }
    public function index()
    {
        $Keyword = D('Keyword');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        if ($keys = $this->_param('keys', 'htmlspecialchars')) {
            $map['keyword'] = array('LIKE', '%' . $keys . '%');
            $this->assign('keys', $keys);
        }
        $type = (int) $this->_param('type');
        if (!empty($type)) {
            $map['type'] = $type;
            $this->assign('type', $type);
        }
        $count = $Keyword->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Keyword->where($map)->order(array('key_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
            $obj = D('Keyword');
            var_dump($data);
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('keyword/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['keyword'] = htmlspecialchars($data['keyword']);
        if (empty($data['keyword'])) {
            $this->baoError('关键字不能为空');
        }
        $data['type'] = (int) $data['type'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($key_id = 0)
    {
        if ($key_id = (int) $key_id) {
            $obj = D('Keyword');
            if (!($detail = $obj->find($key_id))) {
                $this->baoError('请选择要编辑的关键字');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['key_id'] = $key_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('keyword/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的关键字');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['keyword'] = htmlspecialchars($data['keyword']);
        if (empty($data['keyword'])) {
            $this->baoError('关键字不能为空');
        }
        $data['type'] = (int) $data['type'];
        return $data;
    }
    public function delete($key_id = 0)
    {
        if (is_numeric($key_id) && ($key_id = (int) $key_id)) {
            $obj = D('Keyword');
            $obj->delete($key_id);
            $this->baoSuccess('删除成功！', U('keyword/index'));
        } else {
            $key_id = $this->_post('key_id', false);
            if (is_array($key_id)) {
                $obj = D('Keyword');
                foreach ($key_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('keyword/index'));
            }
            $this->baoError('请选择要删除的关键字');
        }
    }
}
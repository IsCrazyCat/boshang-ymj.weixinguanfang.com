<?php
class PluginAction extends CommonAction
{
    private $create_fields = array('plugin_name', 'icon', 'contents', 'month_price', 'menu', 'orderby', 'is_theme');
    private $edit_fields = array('plugin_name', 'icon', 'contents', 'month_price', 'menu', 'orderby', 'is_theme');
    public function index()
    {
        $Plugin = D('Plugin');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['plugin_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Plugin->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Plugin->where($map)->order(array('plugin_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
            $obj = D('Plugin');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('plugin/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['plugin_name'] = htmlspecialchars($data['plugin_name']);
        if (empty($data['plugin_name'])) {
            $this->baoError('插件名称不能为空');
        }
        $data['icon'] = htmlspecialchars($data['icon']);
        if (empty($data['icon'])) {
            $this->baoError('请上传插件图标');
        }
        if (!isImage($data['icon'])) {
            $this->baoError('插件图标格式不正确');
        }
        $data['contents'] = htmlspecialchars($data['contents']);
        if (empty($data['contents'])) {
            $this->baoError('插件说明不能为空');
        }
        $data['month_price'] = (int) $data['month_price'];
        $data['orderby'] = (int) $data['orderby'];
        $data['is_theme'] = (int) $data['is_theme'];
        $data['menu'] = serialize($data['menu']);
        return $data;
    }
    public function edit($plugin_id = 0)
    {
        if ($plugin_id = (int) $plugin_id) {
            $obj = D('Plugin');
            if (!($detail = $obj->find($plugin_id))) {
                $this->baoError('请选择要编辑的插件列表');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['plugin_id'] = $plugin_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('plugin/index'));
                }
                $this->baoError('操作失败');
            } else {
                $detail['menu'] = unserialize($detail['menu']);
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的插件列表');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['plugin_name'] = htmlspecialchars($data['plugin_name']);
        if (empty($data['plugin_name'])) {
            $this->baoError('插件名称不能为空');
        }
        $data['icon'] = htmlspecialchars($data['icon']);
        if (empty($data['icon'])) {
            $this->baoError('请上传插件图标');
        }
        if (!isImage($data['icon'])) {
            $this->baoError('插件图标格式不正确');
        }
        $data['contents'] = htmlspecialchars($data['contents']);
        if (empty($data['contents'])) {
            $this->baoError('插件说明不能为空');
        }
        $data['month_price'] = (int) $data['month_price'];
        $data['orderby'] = (int) $data['orderby'];
        $data['is_theme'] = (int) $data['is_theme'];
        $data['menu'] = serialize($data['menu']);
        return $data;
    }
    public function delete($plugin_id = 0)
    {
        if (is_numeric($plugin_id) && ($plugin_id = (int) $plugin_id)) {
            $obj = D('Plugin');
            $obj->delete($plugin_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('plugin/index'));
        } else {
            $plugin_id = $this->_post('plugin_id', false);
            if (is_array($plugin_id)) {
                $obj = D('Plugin');
                foreach ($plugin_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('plugin/index'));
            }
            $this->baoError('请选择要删除的插件列表');
        }
    }
}
<?php
class BillboardAction extends CommonAction
{
    protected function _initialize()
    {
        parent::_initialize();
        $shopcate = D('Billcate')->select();
        $this->assign('shopcate', $shopcate);
    }
    public function index()
    {
        $Billboard = D('Billboard');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Billboard->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Billboard->where($map)->order(array('list_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $cate_ids = array();
        foreach ($list as $k => $val) {
            if ($val['cate_id']) {
                $cate_ids[$val['cate_id']] = $val['cate_id'];
            }
        }
        $this->assign('cates', D('Billcate')->itemsByIds($cate_ids));
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
            $obj = D('Billboard');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('billboard/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    public function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), array('title', 'intro', 'photo', 'cate_id', 'is_new', 'is_hot', 'is_chose', 'orderby'));
        $data['title'] = trim(htmlspecialchars($data['title']));
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['title'])) {
            $this->baoError('榜单标题不能为空！');
        }
        $data['intro'] = trim(htmlspecialchars($data['intro']));
        if (empty($data['intro'])) {
            $this->baoError('详情不能为空！');
        }
        if (empty($data['orderby'])) {
            $this->baoError('排序不能为空！');
        }
        $data['orderby'] = (int) $data['orderby'];
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['is_new'] = (int) $data['is_new'];
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_chose'] = (int) $data['is_chose'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($list_id = 0)
    {
        if ($list_id = (int) $list_id) {
            $obj = D('Billboard');
            if (!($detail = $obj->find($list_id))) {
                $this->baoError('请选择要编辑的榜单');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['list_id'] = $list_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('billboard/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        }
    }
    public function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), array('title', 'intro', 'photo', 'cate_id', 'is_new', 'is_hot', 'is_chose', 'orderby'));
        $data['title'] = trim(htmlspecialchars($data['title']));
        if (empty($data['title'])) {
            $this->baoError('榜单标题不能为空！');
        }
        $data['intro'] = trim(htmlspecialchars($data['intro']));
        if (empty($data['intro'])) {
            $this->baoError('详情不能为空！');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['is_new'] = (int) $data['is_new'];
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_chose'] = (int) $data['is_chose'];
        return $data;
    }
    public function delete($list_id = 0)
    {
        if (is_numeric($list_id) && ($list_id = (int) $list_id)) {
            $obj = D('Billboard');
            $obj->delete($list_id);
            $this->baoSuccess('删除成功！', U('billboard/index'));
        } else {
            $list_id = $this->_post('$list_id', false);
            if (is_array($list_id)) {
                $obj = D('Billboard');
                foreach ($list_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('billboard/index'));
            }
            $this->baoError('请选择要删除的榜单');
        }
    }
}
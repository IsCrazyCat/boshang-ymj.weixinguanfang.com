<?php
class PhoneAction extends CommonAction
{
    public function index()
    {
        $phones = D('Convenientphonemaps');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('community_id' => $this->community_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name'] = array('LIKE', '%' . $keyword . '%');
        }
        $phone = $phones->where($map)->select();
        $phone_ids = array();
        foreach ($phone as $val) {
            $phone_ids[$val['phone_id']] = $val['phone_id'];
        }
        $count = $phones->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 16);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        if (!empty($phone_ids)) {
            $this->assign('list', D('Convenientphone')->limit($Page->firstRow . ',' . $Page->listRows)->itemsByIds($phone_ids));
        }
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
    }
    public function delete()
    {
        if (IS_AJAX) {
            $phone_id = (int) $_POST['phone_id'];
            $obj = D('Convenientphonemaps');
            if (empty($phone_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '便民电话不存在'));
            }
            if (!($detail = $obj->find($phone_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '便民电话不存在'));
            }
            if ($detail['community_id'] != $this->community_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '不是您小区的'));
            }
            $obj->delete($phone_id);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功'));
        }
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->checkCreate();
            $obj = D('Convenientphone');
            if ($phone_id = $obj->add($data)) {
                $this->baoSuccess('添加成功', U('phone/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    public function checkCreate()
    {
        $data = $this->checkFields($this->_post('data', false), array('name', 'phone', 'expiry_date', 'orderby'));
        $data['community_id'] = (int) $this->community_id;
        $data['name'] = htmlspecialchars($data['name']);
        $data['phone'] = htmlspecialchars($data['phone']);
        $data['expiry_date'] = htmlspecialchars($data['expiry_date']);
        if (empty($data['name'])) {
            $this->baoError('名称不能为空');
        }
        if (empty($data['phone'])) {
            $this->baoError('电话不能为空');
        }
        if (empty($data['expiry_date'])) {
            $this->baoError('过期时间不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['name'])) {
            $this->baoError('名称含有敏感词：' . $words);
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function edit($phone_id)
    {
        if ($phone_id = (int) $phone_id) {
            $obj = D('Convenientphone');
            if (empty($phone_id)) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '便民电话不存在'));
                // $this->baoError('便民电话不存在');
            }
            if (!($detail = $obj->find($phone_id))) {
                // $this->baoError('便民电话不存在');
                $this->ajaxReturn(array('status' => 'success', 'msg' => '便民电话不存在'));
            }
            $map = array('community_id' => $this->community_id);
            $phones = D('Convenientphonemaps');
            //查找出来
            $phone = $phones->where($map)->select();
            $phone_ids = array();
            foreach ($phone as $val) {
                $phone_ids[$val['phone_id']] = $val['phone_id'];
            }
            if (!$phone_ids) {
                $this->baoError('不能操作其他小区电话');
            }
            //p($phone_id);die;
            if ($this->isPost()) {
                $data = $this->checkEdit();
                $data['phone_id'] = $phone_id;
                //编辑的时候
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    //更新
                    $this->baoSuccess('编辑成功', U('phone/index'));
                }
                $this->baoError('操作失败！');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的便民电话');
        }
    }
    public function checkEdit()
    {
        $data = $this->checkFields($this->_post('data', false), array('name', 'phone', 'expiry_date', 'orderby'));
        $data['name'] = htmlspecialchars($data['name']);
        $data['phone'] = htmlspecialchars($data['phone']);
        $data['expiry_date'] = htmlspecialchars($data['expiry_date']);
        if (empty($data['name'])) {
            $this->baoError('名称不能为空');
        }
        if (empty($data['phone'])) {
            $this->baoError('电话不能为空');
        }
        if (empty($data['expiry_date'])) {
            $this->baoError('过期时间不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['name'])) {
            $this->baoError('名称含有敏感词：' . $words);
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
}
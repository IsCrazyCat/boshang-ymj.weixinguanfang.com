<?php
class SmsAction extends CommonAction
{
    private $create_fields = array('sms_key', 'sms_explain', 'sms_tmpl');
    private $edit_fields = array('sms_key', 'sms_explain', 'sms_tmpl');
    public function index()
    {
        $Sms = D('Sms');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        $count = $Sms->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Sms->where($map)->order(array('sms_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
            $obj = D('Sms');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('sms/index'));
            }
            $obj->cleanCache();
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['sms_key'] = htmlspecialchars($data['sms_key']);
        if (empty($data['sms_key'])) {
            $this->baoError('标签不能为空');
        }
        $data['sms_explain'] = htmlspecialchars($data['sms_explain']);
        if (empty($data['sms_explain'])) {
            $this->baoError('说明不能为空');
        }
        $data['sms_tmpl'] = htmlspecialchars($data['sms_tmpl']);
        if (empty($data['sms_tmpl'])) {
            $this->baoError('模版不能为空');
        }
        return $data;
    }
    public function edit($sms_id = 0)
    {
        if ($sms_id = (int) $sms_id) {
            $obj = D('Sms');
            if (!($detail = $obj->find($sms_id))) {
                $this->baoError('请选择要编辑的短信模版');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['sms_id'] = $sms_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('sms/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的短信模版');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['sms_key'] = htmlspecialchars($data['sms_key']);
        if (empty($data['sms_key'])) {
            $this->baoError('标签不能为空');
        }
        $data['sms_explain'] = htmlspecialchars($data['sms_explain']);
        if (empty($data['sms_explain'])) {
            $this->baoError('说明不能为空');
        }
        $data['sms_tmpl'] = htmlspecialchars($data['sms_tmpl']);
        if (empty($data['sms_tmpl'])) {
            $this->baoError('模版不能为空');
        }
        return $data;
    }
    public function delete($sms_id = 0)
    {
        if (is_numeric($sms_id) && ($sms_id = (int) $sms_id)) {
            $obj = D('Sms');
            $obj->save(array('sms_id' => $sms_id, 'is_open' => 0));
            $obj->cleanCache();
            $this->baoSuccess('关闭成功！', U('sms/index'));
        } else {
            $sms_id = $this->_post('sms_id', false);
            if (is_array($sms_id)) {
                $obj = D('Sms');
                foreach ($sms_id as $id) {
                    $obj->save(array('sms_id' => $id, 'is_open' => 0));
                }
                $obj->cleanCache();
                $this->baoSuccess('关闭成功！', U('sms/index'));
            }
            $this->baoError('请选择要关闭的短信模版');
        }
    }
    public function audit($sms_id = 0)
    {
        if (is_numeric($sms_id) && ($sms_id = (int) $sms_id)) {
            $obj = D('Sms');
            $obj->save(array('sms_id' => $sms_id, 'is_open' => 1));
            $obj->cleanCache();
            $this->baoSuccess('开启成功！', U('sms/index'));
        } else {
            $sms_id = $this->_post('sms_id', false);
            if (is_array($sms_id)) {
                $obj = D('Sms');
                foreach ($sms_id as $id) {
                    $obj->save(array('sms_id' => $id, 'is_open' => 1));
                }
                $obj->cleanCache();
                $this->baoSuccess('开启成功！', U('sms/index'));
            }
            $this->baoError('请选择要开启的短信模版');
        }
    }
}
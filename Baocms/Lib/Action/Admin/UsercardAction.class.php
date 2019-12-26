<?php
class UsercardAction extends CommonAction
{
    public function index()
    {
        $Usercard = D('Usercard');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        $count = $Usercard->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Usercard->where($map)->order(array('card_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->display();
    }
    public function import()
    {
        if ($this->isPost()) {
            $file = fopen($_FILES['csv']['tmp_name'], 'r');
            $list = array();
            while ($data = fgetcsv($file)) {
                $var = trim($data[0]);
                if (preg_match('/[0-9]{10}/', $var)) {
                    $list[$var] = $var;
                }
            }
            if (empty($list)) {
                $this->error('没有被识别的符合格式的会员卡');
            }
            $this->assign('list', $list);
            $this->display('importok');
        } else {
            $this->display();
        }
    }
    public function importok(){
        $codes = $this->_post('codes');
        if (empty($codes)) {
            $this->baoError('导入的会员卡不能为空');
        }
        $data = array('user_id' => 0, 'card_num' => '', 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
        $obj = D('Usercard');
        foreach ($codes as $val) {
            if (!empty($val)) {
                $data['card_num'] = $val;
                $obj->add($data);
            }
        }
        $this->baoSuccess('导入成功！', U('usercard/index'));
    }
    public function create(){
        if ($this->isPost()) {
            $card_num = $this->_post('card_num', 'htmlspecialchars');
            $user_id = (int) $this->_post('user_id');
            if (empty($card_num)) {
                $this->baoError('卡号不能为空！');
            }
            if (D('Usercard')->checkCard($card_num)) {
                $this->baoError('卡号已经存在！');
            }
            $data = array('user_id' => $user_id, 'card_num' => $card_num, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
            if (D('Usercard')->add($data)) {
                $this->baoSuccess('录入卡号成功！', U('usercard/create'));
            }
            $this->baoError('录入失败！');
        } else {
            $this->display();
        }
    }
    public function delete($card_id = 0){
        if (is_numeric($card_id) && ($card_id = (int) $card_id)) {
            $obj = D('Usercard');
            $obj->delete($card_id);
            $this->baoSuccess('删除成功！', U('usercard/index'));
        } else {
            $card_id = $this->_post('card_id', false);
            if (is_array($card_id)) {
                $obj = D('Usercard');
                foreach ($card_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('usercard/index'));
            }
            $this->baoError('请选择要删除的会员卡');
        }
    }
}
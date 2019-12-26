<?php
class TuanAction extends CommonAction
{
    public function index()
    {
        $tuan = D('Tuan');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'audit' => 1, 'end_date' => array('EGT', TODAY), 'city_id' => $this->city_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $tuan->where($map)->count();
        $Page = new Page($count, 20);
        $show = $Page->show();
        $list = $tuan->order(array('tuan_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ctuan = D('Communitytuan')->where(array('community_id' => $this->community_id))->select();
        foreach ($list as $k => $val) {
            foreach ($ctuan as $kk => $v) {
                if ($v['tuan_id'] == $val['tuan_id']) {
                    $list[$k]['join'] = 1;
                }
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function add()
    {
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login', 'msg' => '您还未登录', 'url' => U('login/index')));
        }
        if (IS_AJAX) {
            $tuan_id = (int) $_POST['tuan_id'];
            if (empty($tuan_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该套餐不存在'));
            }
            if (!($detail = D('Tuan')->find($tuan_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该套餐不存在'));
            }
            if ($detail['audit'] != 1 || $detail['closed'] != 0 || $detail['end_date'] < TODAY) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该套餐不存在'));
            }
            $orderby = (int) $_POST['orderby'];
            $obj = D('Communitytuan');
            if (!($res = $obj->where(array('tuan_id' => $tuan_id, 'community_id' => $this->community_id))->find())) {
                if ($obj->add(array('tuan_id' => $tuan_id, 'community_id' => $this->community_id, 'orderby' => $orderby))) {
                    $this->ajaxReturn(array('status' => 'success', 'msg' => '添加套餐成功'));
                }
            }
            $this->ajaxReturn(array('status' => 'error', 'msg' => '操作失败'));
        }
    }
    public function remove()
    {
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login', 'msg' => '您还未登录', 'url' => U('login/index')));
        }
        if (IS_AJAX) {
            $tuan_id = (int) $_POST['tuan_id'];
            if (empty($tuan_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该套餐不存在'));
            }
            if (!($detail = D('Tuan')->find($tuan_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该套餐不存在'));
            }
            if ($detail['audit'] != 1 || $detail['closed'] != 0 || $detail['end_date'] < TODAY) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该套餐不存在'));
            }
            $obj = D('Communitytuan');
            $data = array('community_id' => $this->community_id, 'tuan_id' => $tuan_id);
            if ($obj->delete($data)) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功'));
            }
            $this->ajaxReturn(array('status' => 'error', 'msg' => '操作失败'));
        }
    }
}
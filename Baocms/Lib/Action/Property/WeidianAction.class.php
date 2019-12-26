<?php
class WeidianAction extends CommonAction
{
    public function index(){
        $weidian = D('WeidianDetails');
        import('ORG.Util.Page');
        $map = array('audit' => 1, 'city_id' => $this->city_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['weidian_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $weidian->where($map)->count();
        $Page = new Page($count, 20);
        $show = $Page->show();
        $list = $weidian->order(array('id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $cweidian = D('Communityweidian')->where(array('community_id' => $this->community_id))->select();
        foreach ($list as $k => $val) {
            foreach ($cweidian as $kk => $v) {
                if ($v['weidian_id'] == $val['id']) {
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
            $weidian_id = (int) $_POST['weidian_id'];
            if (empty($weidian_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该微店不存在'));
            }
            if (!($detail = D('WeidianDetails')->find($weidian_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该微店不存在'));
            }
            if ($detail['audit'] != 1) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该微店不存在'));
            }
            $orderby = (int) $_POST['orderby'];
            $obj = D('Communityweidian');
            if (!($res = $obj->where(array('weidian_id' => $weidian_id, 'community_id' => $this->community_id))->find())) {
                if ($obj->add(array('weidian_id' => $weidian_id, 'community_id' => $this->community_id, 'orderby' => $orderby))) {
                    $this->ajaxReturn(array('status' => 'success', 'msg' => '添加微店成功'));
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
            $weidian_id = (int) $_POST['weidian_id'];
            if (empty($weidian_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该微店不存在'));
            }
            if (!($detail = D('WeidianDetails')->find($weidian_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该微店不存在'));
            }
            if ($detail['audit'] != 1) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该微店不存在'));
            }
            $obj = D('Communityweidian');
            $data = array('community_id' => $this->community_id, 'weidian_id' => $weidian_id);
            if ($obj->delete($data)) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功'));
            }
            $this->ajaxReturn(array('status' => 'error', 'msg' => '操作失败'));
        }
    }
}
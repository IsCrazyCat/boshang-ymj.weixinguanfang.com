<?php
class ActivityAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        if ($this->_CONFIG['operation']['huodong'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
    public function index(){
        $this->display();
    }
    public function load(){
        $Activity = D('Activity');
        $Activitysign = D('Activitysign');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Activitysign->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Activitysign->where($map)->order(array('sign_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $activitys_ids = array();
        foreach ($list as $k => $val) {
            $activitys_ids[$val['activity_id']] = $val['activity_id'];
        }
        $this->assign('activity', $Activity->itemsByIds($activitys_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}
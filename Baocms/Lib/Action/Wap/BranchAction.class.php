<?php
class BranchAction extends CommonAction {
	
	public function tuan() {
		$branch_id = (int) $this->_get('branch_id');
        $shop_id = (int) $this->_get('shop_id');
        $detail = D('Shopbranch')->find($branch_id);
        if(empty($detail)||$detail['shop_id'] != $shop_id){
            $this->error('该分店不存在');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
           $this->error('该分店不存在');
            die;
        }
        $this->assign('detail', $detail);
        $this->assign('nextpage', LinkTo('branch/tuanload', array('shop_id' => $shop_id,'branch_id' => $branch_id, 't' => NOW_TIME, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();  
    }
	
	public function tuanload() {
		$branch_id = (int) $this->_get('branch_id');
        $shop_id = (int) $this->_get('shop_id');
		$tuanload = D('Tuan');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $shop_id, 'branch_id' => $branch_id,'show_date' => array('ELT', TODAY));
        $count = $tuanload->where($map)->count();  
        $Page = new Page($count, 5);
        $show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $tuanload->where($map)->order(array('tuan_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list',$list);
        $this->display(); 
    }
	
    public function dianping() {
		$branch_id = (int) $this->_get('branch_id');
        $shop_id = (int) $this->_get('shop_id');
        $detail = D('Shopbranch')->find($branch_id);
        if(empty($detail)||$detail['shop_id'] != $shop_id){
            $this->error('该分店不存在');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
           $this->error('该分店不存在');
            die;
        }
        $this->assign('detail', $detail);
		$this->assign('nextpage', LinkTo('branch/dianpingloading', array('shop_id' => $shop_id,'branch_id' => $branch_id, 't' => NOW_TIME, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
    }

    public function dianpingloading() {
		$branch_id = (int) $this->_get('branch_id');
        $shop_id = (int) $this->_get('shop_id');
        if (!$detail = D('Shop')->find($shop_id)) {
            die('0');
        }
        if ($detail['closed']) {
            die('0');
        }
        $Shopdianping = D('Shopdianping');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'shop_id' => $shop_id,'branch_id' => $branch_id, 'show_date' => array('ELT', TODAY));
        $count = $Shopdianping->where($map)->count();
        $Page = new Page($count, 5); 

        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $show = $Page->show(); 
        $list = $Shopdianping->where($map)->order(array('dianping_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $dianping_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $dianping_ids[$val['dianping_id']] = $val['dianping_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($dianping_ids)) {
            $this->assign('pics', D('Shopdianpingpics')->where(array('dianping_id' => array('IN', $dianping_ids)))->select());
        }
        $this->assign('totalnum', $count);
        $this->assign('list', $list); 
        $this->assign('detail', $detail);
        $this->display();
    }
}
<?php
class LogsAction extends CommonAction{
    public function prestigelogs(){
        $Userprestigelogs = D('Userprestigelogs');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Userprestigelogs->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $Userprestigelogs->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function moneylogs(){
        $Usermoneylogs = D('Usermoneylogs');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Usermoneylogs->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $Usermoneylogs->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function cashlogs(){
        $Userscash = D('Userscash');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'type' => user);
        $count = $Userscash->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $Userscash->where($map)->order(array('cash_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function integral(){
        $Userintegrallogs = D('Userintegrallogs');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Userintegrallogs->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $Userintegrallogs->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function rechargecard(){
        $Rechargecard = D('Rechargecard');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'is_used' => 1);
        $count = $Rechargecard->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $Rechargecard->where($map)->order(array('card_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}
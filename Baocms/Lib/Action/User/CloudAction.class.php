<?php 
class CloudAction extends CommonAction{
	protected function _initialize(){
        parent::_initialize();
        if ($this->_CONFIG['operation']['cloud'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
    public function index(){
        $cloudlogs = D('Cloudlogs');
        $cloudgoods = D('Cloudgoods');
        import('ORG.Util.Page');
        $goods_ids = $cloudlogs->where(array('user_id' => $this->uid))->getField('goods_id', TRUE);
        array_unique($goods_ids);
        $map = array('closed' => 0, 'audit' => 1);
        $map['goods_id'] = array('IN', $goods_ids);
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
        $count = $cloudgoods->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $cloudgoods->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['win_user_id']] = $val['win_user_id'];
			$shop_ids[$val['shop_id']] = $val['shop_id'];
            $sum = $cloudlogs->where(array('goods_id' => $val['goods_id'], 'user_id' => $this->uid))->sum('num');
            $list[$k]['sum'] = $sum;
            if (!empty($val['win_user_id'])) {
                $sum2 = $cloudlogs->where(array('goods_id' => $val['goods_id'], 'user_id' => $val['win_user_id']))->sum('num');
            }
            $list[$k]['sum2'] = $sum2;
            $res = $cloudlogs->where(array('goods_id' => $val['goods_id']))->order(array('log_id' => 'asc'))->select();
            $rlist = $cloudgoods->get_datas($res);
            foreach ($rlist as $kk => $v) {
                if ($v['user_id'] == $this->uid) {
                    $list[$k]['mlist'][] = $rlist[$kk];
                }
            }
        }
		$this->assign('shops', D('Shop')->itemsByIds($shop_ids));//增加的
        $this->assign('users', d('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	//订单表新修改
	public function order(){
		$status = (int) $this->_param("status");
        $this->assign("status", $status);
        $this->display();
	}
	//会员中心云购数据加载
	public function loaddata(){
        $obj = D("Cloudlogs");
        import("ORG.Util.Page");
        $map = array( "user_id" =>$this->uid);
        
        if ($keyword = $this->_param( "keyword", "htmlspecialchars" ) ){
            $map['log_id'] = array("LIKE","%".$keyword."%");
            $this->assign("keyword", $keyword);
        }
        if (isset($_GET['status']) || isset($_POST['status'])) {
            $status = (int) $this->_param('status');
            if ($status != 999) {
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        } else {
            $this->assign('status', 999);
        }
        $count = $obj->where($map)->count();
        $Page = new Page( $count, 10 );
        $show = $Page->show();
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $obj->where($map)->order(array("log_id" => "desc" ))->limit( $Page->firstRow.",".$Page->listRows )->select();
        $goods_ids = $user_ids = array( );
        foreach ($list as $k => $val ){
            $user_ids[$val['users_id']] = $val['users_id'];
			$goods_ids[$val['goods_id']] = $val['goods_id'];
        }
        $this->assign("users", D("Users")->itemsByIds($user_ids));
		$this->assign("cloudgoods", D("Cloudgoods")->itemsByIds($goods_ids));
        $this->assign("list", $list);
        $this->assign("page", $show);
        $this->display();
    }
	//云购支付详细
	public function detail($log_id){
        $log_id = (int) $log_id;
        if (empty($log_id) || !($detail = D("Cloudlogs")->find($log_id))) {
            $this->error("该云购订单不存在");
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error("请不要操作他人的云购订单");
        }
		$this->assign('cloudgoods', D('Cloudgoods')->find($detail['goods_id']));
        $this->assign("detail", $detail);
        $this->display();
    }
	//云购删除订单
	 public function delete($log_id){
        if (is_numeric($log_id) && ($log_id = (int) $log_id)) {
            $obj = D("Cloudlogs");
            if (!($detail = $obj->find($log_id))) {
                $this->fengmiMsg("云购不存在");
            }
            if ($detail['status'] != 0) {
                $this->fengmiMsg("该云购状态不允许被删除");
            }
            $obj->delete($log_id);
            $this->fengmiMsg("删除成功！", U("cloud/order"));
        }
    }
	
}
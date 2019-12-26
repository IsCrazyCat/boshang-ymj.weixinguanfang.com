<?php
class TuanAction extends CommonAction{
    public function index(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
        // 输出模板
    }
	
    public function delete($order_id){
		$order_id = I('order_id', 0, 'trim,intval');
        $obj = D('Tuanorder');
        if (!($detail = D('Tuanorder')->find($order_id))) {
            $this->fengmiMsg('套餐不存在', U('tuan/index'));
        }
		
	    if ($detail['status'] == -1) {
			$Tuancode = D('Tuancode');
			$tuan_code_is_used = $Tuancode->where(array('order_id' => $order_id,'status'=>0,'is_used'=>1))->select();
			
			$maps['order_id'] = array('eq',$order_id);
			$maps['status'] = array('gt',0);
			$tuan_code_status = $Tuancode->where($maps)->select();
			if (!empty($tuan_code_is_used)) {
				$this->fengmiMsg('已有套餐劵验证不能取消订单');
			}elseif(!empty($tuan_code_status)){
				$this->fengmiMsg('已有套餐劵申请退款不行执行此操作');
			}else{
				$tuan_code = $Tuancode->where(array('order_id' => $order_id,'status'=>0,'is_used'=>0))->select();
				foreach($tuan_code as $k => $value){
					$Tuancode->save(array('code_id' => $value['code_id'], 'closed' => 1));
				}	
				$obj->save(array('order_id' => $order_id, 'closed' => 1));
				D('Users')->addIntegral($detail['user_id'], $detail['use_integral'], '取消套餐订单' . $detail['order_id'] . '积分退还');//返积分
				$this->fengmiMsg('取消订单成功!', U('tuan/index'));
			}
        }elseif($detail['status'] != 0){
			$this->fengmiMsg('状态不正确', U('tuan/index'));
		}elseif($detial['closed'] == 1){
			$this->fengmiMsg('套餐已关闭', U('tuan/index'));
		}elseif($detail['user_id'] != $this->uid){
			$this->fengmiMsg('不能操作别人的套餐', U('tuan/index'));
		}else{
			 if ($obj->save(array('order_id' => $order_id, 'closed' => 1))) {
				D('Users')->addIntegral($detail['user_id'], $detail['use_integral'], '取消套餐订单' . $detail['order_id'] . '积分退还');//返积分
				$this->fengmiMsg('取消订单成功!', U('tuan/index'));
			 }else{
				$this->fengmiMsg('操作失败');
			 }
	    }
      
        
    }
	
	
    public function orderloading(){
        $Tuanorder = D('Tuanorder');
        import('ORG.Util.Page');// 导入分页类
        $map = array('user_id' => $this->uid, 'closed' => 0);//这里只显示 实物
        if (isset($_GET['aready']) || isset($_POST['aready'])) {
            $aready = (int) $this->_param('aready');
            if ($aready != 999) {
                $map['status'] = $aready;
            }
            $this->assign('aready', $aready);
        } else {
            $this->assign('aready', 999);
        }
        $count = $Tuanorder->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Tuanorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $tuan_ids = array();
        foreach ($list as $k => $val) {
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }
        //查询商家
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('tuans', D('Tuan')->itemsByIds($tuan_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));//查询商家名字
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();// 输出模板
    }
    public function detail($order_id) {
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D('Tuanorder')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error('请不要操作他人的订单');
        }
        if (!($dianping = D('Tuandianping')->where(array('order_id' => $order_id, 'user_id' => $this->uid))->find())) {
            $detail['dianping'] = 0;
        } else {
            $detail['dianping'] = 1;
        }
        $this->assign('tuans', D('Tuan')->find($detail['tuan_id']));
        $this->assign('detail', $detail);
        $this->display();
    }
    public function dianping($order_id) {
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D("Tuanorder")->find($order_id))) {
            $this->error("该订单不存在");
        }
        if (!($tc = D("Tuancode")->where(array("order_id" => $order_id, "is_used" => 1))->find())) {
            $this->error("您的套餐码还没有使用");
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error("请不要操作他人的订单");
        }
        if ($detail['is_dianping'] != 0) {
            $this->error("您已经点评过了");
        }
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post("data", FALSE), array("score", "cost", "contents"));
            $data['user_id'] = $this->uid;
            $data['order_id'] = $detail['order_id'];
            $data['shop_id'] = $detail['shop_id'];
            $data['tuan_id'] = $detail['tuan_id'];
            $data['score'] = (int) $data['score'];
            if ($data['score'] <= 0 || 5 < $data['score']) {
                $this->fengmiMsg("请选择评分");
            }
            $data['cost'] = (int) $data['cost'];
            $data['contents'] = htmlspecialchars($data['contents']);
            if (empty($data['contents'])) {
                $this->fengmiMsg("不说点什么么");
            }
            $data['create_time'] = NOW_TIME;
            $data_tuan_dianping = $this->_CONFIG['mobile']['data_tuan_dianping'];
            $data['show_date'] = date('Y-m-d', NOW_TIME + $data_tuan_dianping * 86400);
            $data['create_ip'] = get_client_ip();
            $obj = d("Tuandianping");
			 
            if ($dianping_id = $obj->add($data)) {
                $photos = $this->_post("photos", FALSE);
                $local = array();
                foreach ($photos as $val) {
                    if (isimage($val)) {
                        $local[] = $val;
                    }
                }
                if (!empty($local)) {
                    D("Tuandianpingpics")->upload($order_id, $local);
                }
                D("Tuanorder")->save(array("order_id" => $order_id, "is_dianping" => 1));
                D("Shop")->updateCount($detail['shop_id'], "score_num");
                D("Users")->updateCount($this->uid, "ping_num");
                D("Users")->prestige($this->uid, "dianping");
                $this->fengmiMsg("评价成功", U("member/index"));
            }
            $this->fengmiMsg("操作失败！");
        } else {
            $this->assign("detail", $detail);
            $tuan = D("Tuan")->find($detail['tuan_id']);
            $this->assign("tuan", $tuan);
            $this->display();
        }
    }
}
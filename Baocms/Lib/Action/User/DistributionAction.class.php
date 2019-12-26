<?php
class DistributionAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        $distributions = (int) $this->_CONFIG['profit']['profit'];
        //赋值分销开关
        if ($distributions == 0) {
            $this->error('暂无此功能');
            die;
        }
        $profit_min_rank_id = (int) $this->_CONFIG['profit']['profit_min_rank_id'];
        $fuser = $this->member;
        if ($fuser) {
            $flag = false;
            if ($profit_min_rank_id) {
                $modelRank = D('Userrank');
                $rank = $modelRank->find($profit_min_rank_id);
                $userRank = $modelRank->find($fuser['rank_id']);
                if ($rank) {
                    if ($userRank && $userRank['prestige'] >= $rank['prestige']) {
                        $flag = true;
                    } else {
                        $flag = false;
                    }
                } else {
                    $flag = false;
                }
            } else {
                $flag = true;
            }
            if (!$flag) {
                $this->error('对不起您必须达到' . $rank['rank_name'] . '及以上等级才有分销权限');
            }
        }
    }
    public function index(){
        if (empty($this->uid)) {
            header("Location: " . U('Wap/passport/login'));
            die;
        }
		$this->assign('profit_ok', $profit_ok = D('Userprofitlogs')->where(array('user_id' => $this->uid,'is_separate' =>1))->sum('money'));
		$this->assign('profit_cancel',$profit_cancel = D('Userprofitlogs')->where(array('user_id' => $this->uid,'is_separate' =>2))->sum('money'));
        $this->display();
    }
    public function profit(){
		$status = (int) $this->_param('status');
		$this->assign('status', $status);
		$this->assign('nextpage', LinkTo('distribution/profitloaddata',array('status'=>$status,'t' => NOW_TIME, 'p' => '0000')));
        $this->mobile_title = '优惠买单';
		$this->display(); // 输出模板		
    }
	public function profitloaddata(){
        $status = (int) $this->_param('status');
        if (!in_array($status, array(0, 1, 2, 3))) {
            $status = 1;
        }
        $model = D('Userprofitlogs');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'is_separate' => $status);
        $count = $model->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
			die('0');
		}
        $orderby = array('log_id' => 'DESC');
        $list = $model->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('status', $status);
		$this->display();
		
	}
	
    public function subordinate(){
		$level = (int) $this->_param('level');
		$this->assign('level', $level);
		$this->assign('nextpage', LinkTo('distribution/subordinateloaddata',array('level'=>$level,'t' => NOW_TIME, 'p' => '0000')));
        $this->mobile_title = '优惠买单';
        $user = D('Users');
        $map1 = array('closed' => 0, 'fuid1' => $this->uid);
        $count1 = $user->where($map1)->count();
        $map2 = array('closed' => 0, 'fuid2' => $this->uid);
        $count2 = $user->where($map2)->count();
        $this->assign('count1',$count1);
        $this->assign('count2',$count2);
		$this->display(); // 输出模板		
    }
	
	public function subordinateloaddata(){
		$level = (int) $this->_param('level');
        if (!in_array($level, array(1, 2, 3))) {
            $level = 1;
        }
        $user = D('Users');
        import('ORG.Util.Page');

        $map = array('closed' => 0, 'fuid' . $level => $this->uid);
        $count = $user->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $orderby = array('user_id' => 'DESC');
        $list = $user->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('level', $level);
        $this->display();
		 
	}
    public function qrcode(){
        if (empty($this->uid)) {
            header("Location: " . U('Wap/passport/login'));
            die;
        }
        $user = D('Users')->find($this->uid);
//        $file =$user['distribution_qrcode_url'];
//        if(empty($file)){
//            $order=D('Order')->where(array('user_id'=>$this->uid,'status'=>array('IN','1,2,8')))->find();
//            if(!empty($order)){
                //分销二维码 修改为微信的二维码 + logo
//                for($i=0;$i<3;$i++){
                    $wx_qrcode_url = D('Weixin')->getCode($this->uid,4);
                    $token = 'fuid_' . $this->uid;
                    $logo = __ROOT__.'Public/img/blk_logo.jpg';
                    $file = baoQrCodeLogo($token,$wx_qrcode_url,$logo);

//                }
                D('Users')->save(array('user_id'=>$this->uid,'distribution_qrcode_url'=>$file));
//            }else{
//                $this->error('您还未拥有分销二维码哦，请先去购物消费！',U('wap/mall/index'));
//            }
//        }
        $this->assign('file', $file);
        $this->display();
    }
    public function poster()
    {
        if (empty($this->uid)) {
            header("Location: " . U('Wap/passport/login'));
            die;
        }
//        $user = D('Users')->find($this->uid);
//        $file =$user['distribution_qrcode_url'];
//        if(empty($file)){
//            $order=D('Order')->where(array('user_id'=>$this->uid,'status'=>array('IN','1,2,8')))->find();
//            if(!empty($order)){
                //分销二维码 修改为微信的二维码 + logo
                $wx_qrcode_url = D('Weixin')->getCode($this->uid,4);
                $token = 'fuid_' . $this->uid;
                $logo = __ROOT__.'Public/img/blk_logo.jpg';
                $file = baoQrCodeLogo($token,$wx_qrcode_url,$logo);
                D('Users')->save(array('user_id'=>$this->uid,'distribution_qrcode_url'=>$file));
//            }else{
//                $this->error('您还未拥有分销二维码哦，请先去购物消费！',U('wap/mall/index'));
//            }
//        }
        $this->assign('file', $file);
        $this->display();
    }
    public function superior()
    {
        $user = D('Users');
        if ($this->member['fuid1']) {
            $fuser = $user->find($this->member['fuid1']);
        }
        $this->assign('fuser', $fuser);
        $this->display();
    }

    /**
     * 作弊直接生成分销二维码
     */
    public function qrcode_bad(){
        if (empty($this->uid)) {
            header("Location: " . U('Wap/passport/login'));
            die;
        }
//        if(!($this->uid == 1741)){
//            exit('不是指定用户，无权限！'.$this->uid);
//        }
        $user = D('Users')->find($this->uid);
//        $file =$user['distribution_qrcode_url'];
//        $wx_qrcode_url = '';
        //没有分销二维码，则生成二维码
        //分销二维码 修改为微信的二维码 + logo
//        $wx_qrcode_url = D('Weixin')->getCode($this->uid,4);
        $wx_qrcode_url = U('wap/tuan/index.html?nav_id=53');
        $token = 'fuid11__' . $this->uid;
        $logo = __ROOT__.'Public/img/blk_logo.jpg';
        $file = baoQrCodeLogo1($token,$wx_qrcode_url,$logo);
        D('Users')->save(array('user_id'=>$this->uid,'distribution_qrcode_url'=>$file));
//        header("Location: " . U('User/distribution/qrcode'));
//        die;
        if(empty($wx_qrcode_url)){
            exit($this->uid.'===='. D('Weixin')->getToken());
        }
        exit('success='.json_encode($wx_qrcode_url));
    }
    public function test(){
        if(checkFile($wx_qrcode_url = D('Weixin')->getCode($this->uid,4))){
            exit(1);
        }
        exit(2);

//        if(file_exists("")){
//               $flag .= "头像存在";
//        }
//        if(file_exists()){
//            $flag .=" ===二维码存在";
//        }else{
//            exit($flag .'===二维码'. $wx_qrcode_url);
//        }
//        exit($flag);
    }
}
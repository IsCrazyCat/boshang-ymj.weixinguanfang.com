<?php
class CommunityAction extends CommonAction {
	protected function _initialize(){
        parent::_initialize();
        if ($this->_CONFIG['operation']['community'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
    public function index() {
        $this->display(); // 输出模板;
    }


    public function community_load() {
        $map = array('user_id' => $this->uid);
		$joined = D('Communityusers')->where($map)->order(array('join_id' => 'desc'))->limit(0,20)-> select();	
		foreach ($joined as $val) {
			$cmm_ids[$val['community_id']] = $val['community_id'];
		}
		$this->assign('list', D('Community')->itemsByIds($cmm_ids));		
        $this->display();

    }
	public function tongzhi(){
         $this->display(); // 输出模板;

    }
	public function tongzhi_load() {
		$communitynews = D('Communitynews');
		import('ORG.Util.Page'); // 导入分页类
		$map = array('user_id' => $this->uid);
		$joined = D('Communityusers')->where($map)->order(array('join_id' => 'desc'))->select();
		foreach ($joined as $val) {
			$cmm_ids[$val['community_id']] = $val['community_id'];
		}
		$maps['community_id']  = array('in',$cmm_ids);
		$count = $communitynews->where($maps)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 5); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE')?C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		$news = $communitynews->where($maps)->order(array('news_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$community_ids = array();
        foreach ($news as $k => $val) {
            if ($val['community_id']) {
                $community_ids[$val['community_id']] = $val['community_id'];
            }
        }
        if ($community_ids) {
            $this->assign('communitys', D('Community')->itemsByIds($community_ids));
        }
		
		$this->assign('list', D('Community')->itemsByIds($cmm_ids));
		$this->assign('news', $news);
		$this->assign('page', $show); // 赋值分页输出
		$this->display();


    }

	

	public function newsdetail($news_id) {
        $news_id = (int)$news_id;
        if(!$detail = D('Communitynews')->find($news_id)){
            $this->error('该问题不存在');
        }

        if($detail['closed'] != 0){
            $this->error('该问题已被删除');
        }

		$new_id = $detail['community_id'];
        $community = D('Community')->find($new_id);
		$this->assign('community', $community);
        $this->assign('detail',$detail);
        $this->display();

    }

	public function feedback(){
		 $this->assign('nextpage', LinkTo('community/feedback_load', array('t' => NOW_TIME, 'community_id' => $this->community_id, 'p' => '0000')));
         $this->display(); // 输出模板;
    }

	public function feedback_load() {
		$feedback = D('Feedback');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0, 'user_id' => $this->uid);
        $count = $feedback->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 5); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE')?C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $feedback->order(array('feed_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$community_ids = array();
        foreach ($list as $k => $val) {
            if ($val['community_id']) {
                $community_ids[$val['community_id']] = $val['community_id'];
            }
        }
        if ($community_ids) {
            $this->assign('communitys', D('Community')->itemsByIds($community_ids));
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板 
    }

	public function feedbackdetail($feed_id) {
        $feed_id = (int)$feed_id;
        if(!$detail = D('Feedback')->find($feed_id)){
            $this->error('该问题不存在');
        }
        if($detail['closed'] != 0){
            $this->error('该问题已被删除');
        }
        if($detail['user_id'] != $this->uid){
            $this->error('请不要查看他人的问题反馈');
        }
        $this->assign('detail',$detail);
        $this->display();
    }

	public function order(){
		$bg_time = strtotime(TODAY);
	    $list = D('Communityorder')->order()->where(array('user_id' => $this->uid))->select();
        $order_ids = array();
        foreach ($list as $k => $val) {
            $order_ids[$val['order_id']] = $val['order_id'];
        }
		//财务管理
		$counts['order'] = (int) D('Communityorderproducts')->where(array('order_id' => array('IN', $order_ids)))->sum('money');
		$counts['order_0'] = (int) D('Communityorderproducts')->where(array(
			'order_id' => array('IN', $order_ids),
			'is_pay'=>0
		))->sum('money');//未交费

		
		$counts['order_1'] = (int) D('Communityorderproducts')->where(array(
			'order_id' => array('IN', $order_ids),
			'is_pay'=>1
		))->sum('money');//未交费
			
		//小区账单
		$counts['order_type_1'] = (int) D('Communityorderproducts')->where(array('order_id' => array('IN', $order_ids)))->sum('money');
		
		$counts['order_type_1_is_pay'] = (int) D('Communityorderproducts')->where(array(
			'type'=>1,
			'order_id' => array('IN', $order_ids),
			'is_pay'=>0
		))->sum('money');
			
		$counts['order_type_2'] = (int) D('Communityorderproducts')->where(array('type'=>2,'user_id' => $this->uid))->sum('money');
		$counts['order_type_2_is_pay'] = (int) D('Communityorderproducts')->where(array(
			'type'=>2,
			'order_id' => array('IN', $order_ids),
			'is_pay'=>0
		))->sum('money');
		
		$counts['order_type_3'] = (int) D('Communityorderproducts')->where(array('type'=>3,'user_id' => $this->uid))->sum('money');
		$counts['order_type_3_is_pay'] = (int) D('Communityorderproducts')->where(array(
			'type'=>3,
			'order_id' => array('IN', $order_ids),
			'is_pay'=>0
		))->sum('money');
			
		$counts['order_type_4'] = (int) D('Communityorderproducts')->where(array('type'=>4,'user_id' => $this->uid))->sum('money');
		$counts['order_type_4_is_pay'] = (int) D('Communityorderproducts')->where(array(
			'type'=>4,
			'order_id' => array('IN', $order_ids),
			'is_pay'=>0
		))->sum('money');
			
		$counts['order_type_5'] = (int) D('Communityorderproducts')->where(array('type'=>5,'user_id' => $this->uid))->sum('money');
		$counts['order_type_5_is_pay'] = (int) D('Communityorderproducts')->where(array(
			'type'=>5,
			'order_id' => array('IN', $order_ids),
			'is_pay'=>0
		))->sum('money');
		$this->assign('nextpage', LinkTo('community/order_load', array('t' => NOW_TIME, 'user_id' => $this->uid, 'p' => '0000')));
		$this->assign('counts', $counts);
        $this->display(); // 输出模板;
    }
       
		
	public function order_load() {
		$orders = D('Communityorder');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('user_id' => $this->uid);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars') ) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $map['order_date'] = array(array('ELT', $end_date), array('EGT', $bg_date));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $this->assign('bg_date', $bg_date);
                $map['order_date'] = array('EGT', $bg_date);
            }

            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $this->assign('end_date', $end_date);
                $map['order_date'] = array('ELT', $end_date);
            }
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $this->assign('user_id', $user_id);
        }
        $count = $orders->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 5); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE')?C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $orders->order(array('order_date' => 'desc'))->where($map)->select();
        $user_ids = $order_ids  = $community_ids= array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
			$community_ids[$val['community_id']] = $val['community_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('communitys', D('Community')->itemsByIds($community_ids));
        $products = D('Communityorderproducts')->where(array('order_id' => array('IN', $order_ids)))->select();
        foreach ($list as $k => $val) {
            foreach ($products as $kk => $v) {
                if ($v['order_id'] == $val['order_id']) {
                    $list[$k]['type' . $v['type']] = $v;
                }
            }
        }
		
		
        $this->assign('list', $list);
        $this->assign('page', $show); // 赋值分页输出
        $this->display();

    }

	 public function tieba() {
        $this->display(); // 输出模板;
    }

    public function tieba_load() {
       $Post = D('Communityposts');
		import('ORG.Util.Page');
		$map = array('user_id' => $this->uid, 'closed' => 0); //查出当前用户名
		$count = $Post->where($map)->count();
		$Page = new Page($count, 5); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		$list = $Post->where($map)->order(array('post_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
			$ids = array();
			if ($val['user_id']) {
				$ids[$val['user_id']] = $val['user_id'];
				$ids[$val['last_id']] = $val['last_id'];
			}
			$list[$k] = $val;
		}
		$this->assign('users', D('Users')->itemsByIds($ids));
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();

    }

	 public function tiebadelete($post_id = 0) {
            $obj = D('Communityposts');
            $obj->save(array('post_id' => $post_id, 'closed' => 1));
            $this->success('删除成功！', U('mcenter/community/tieba'));
    }

   

}
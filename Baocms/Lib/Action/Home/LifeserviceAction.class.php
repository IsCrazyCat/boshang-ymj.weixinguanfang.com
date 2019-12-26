<?php
class LifeserviceAction extends CommonAction {
protected $Activitycates = array();

    public function _initialize() {
        parent::_initialize();
		$lifeservice = (int)$this->_CONFIG['operation']['lifeservice'];
		if ($lifeservice == 0) {
				$this->error('此功能已关闭');
				die;
		}
        $this->lifeservicecates = D('Housekeepingcate')->fetchAll();
        $this->assign('lifeservicecates', $this->lifeservicecates);
		$this->assign('host',__HOST__);
    }
    public function index() {

        $houseworksetting = D('Houseworksetting');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0,'city_id'=>$this->city_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $cat = (int) $this->_param('cat');
        if ($cat) {
            $map['cate_id'] = $cat;
            $this->seodatas['cate_name'] = $this->Activitycates[$cat]['cate_name'];
        }
        $count = $houseworksetting->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $list = $houseworksetting->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
       
        $shop_ids = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list); 
        $this->assign('page', $show);
        $this->display(); 
    }



    public function detail($id) {
        $id = (int) $id;
        $this->assign('cates', D('Housekeepingcate')->fetchAll());
		if (!$detail = D('Houseworksetting')->find($id)) {
            $this->error('该家政项目不存在！');
            die;
        }
        
        $detail = D('Houseworksetting')->find($id);
        $this->assign('detail', $detail);
        $h = date('H',NOW_TIME) + 1;
        $this->assign('h',$h);
        $cfg = D('Shopdingsetting')->getCfg();
        $this->assign('cfg',$cfg);
		//更新点击量
		$sign = D('Housework')->where(array('user_id' => $this->uid, 'id' => $id))->select();
        if (!empty($sign)) {
            $detail['sign'] = 1;
        } else {
            $detail['sign'] = 0;
        }
		D('Houseworksetting')->updateCount($id, 'views');
		$ids = D('Houseworksetting')->find($id);
		$shops = $ids['shop_id'];
		$this->assign('shops', D('Shop')->itemsByIds($shops));
		$detail['thumb'] = unserialize($detail['thumb']);
		
		// 点评开始
		$Lifeservicedianping = D('Lifeservicedianping');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'activity_id' => $id, 'show_date' => array('ELT', TODAY));
        $count = $Lifeservicedianping->where($map)->count();
        $Page = new Page($count, 5); 
        $show = $Page->show(); 
        $list = $Lifeservicedianping->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $id_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $id_ids[$val['id']] = $val['id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($id_ids)) {
            $this->assign('pics', D('Lifeservicedianpingpics')->where(array('id' => array('IN', $id_ids)))->select());
        }
        $this->assign('totalnum', $count);
        $this->assign('list', $list); 
        $this->assign('page', $show);
        $userrank = D('user_rank')-> select();
        $this -> assign('userrank',$userrank);	
		$this->assign('detail', $detail);
        $this->display();

    }

    public function create($id) {
		if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        if (!$id = (int) $id) {
            $this->baoError('服务类型不能为空');
        }
		$cate_id = D('Houseworksetting')->find($id);
        if (!isset($this->lifeservicecates[$cate_id['cate_id']])) {
            $this->baoError('暂时没有该服务类型');
        }
		
		$lifeservice_shop = D('Shop')->find($ids['shop_id']);//商家信息
		$lifeservice_user = D('Users')->find($lifeservice_shop['user_id']);//用户信息

		$data['id'] = $id;
		$data['user_id'] = (int) $this->uid;
        $data['cate_id'] = $this->lifeservicecates[$cate_id['cate_id']]['cate_name'];
		$data['shop_id'] = $lifeservice_shop['shop_id'];
        $data['date'] = htmlspecialchars($_POST['date']);
        $data['time'] = htmlspecialchars($_POST['time']);
		
        if(empty($data['date'])|| empty($data['time'])){
            $this->baoError('服务时间不能为空');
        }
        $data['svctime'] = $data['date'].  " " . $data['time']; 
		
		//判断时间是否过期
		$svctime = $data['date'].' '.$data['time'];
		$lifeservice_time = strtotime($svctime);
		if (empty($data['time'])) { 
            $this->baoError('请选择时间');
        }else if($lifeservice_time < time()){
			$this->baoError('预约时间已经过期，请选择正确的时间');
		}
		//判断时间过期结束
		
        if (!$data['addr'] = $this->_post('addr', 'htmlspecialchars')) {
            $this->baoError('服务地址不能为空');
        }
        if (!$data['name'] = $this->_post('name', 'htmlspecialchars')) {
            $this->baoError('联系人不能为空');
        }
        if (!$data['tel'] = $this->_post('tel', 'htmlspecialchars')) {
            $this->baoError('联系电话不能为空');
        }
        if (!isMobile($data['tel']) && !isPhone($data['tel'])) {
            $this->baoError('电话号码不正确');
        }
        $data['contents'] = $this->_post('contents', 'htmlspecialchars');
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
		
			
        if (D('Housework')->add($data)) {
			D('Houseworksetting')->updateCount($id, 'yuyue_num');
			
			//短信通知用户预约成功
			$sms_time = $data['date'].'时间'.$data['time'];
			if(!empty($data['tel'])){
				$user_mobile = $data['tel'];
			}else{
				$user_mobile = $this->member['mobile'];	
			}
						
			if($this->_CONFIG['sms']['dxapi'] == 'dy'){
                D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_lifeservice_TZ_user', $user_mobile, array(
			 	    'sitename'=>$this->_CONFIG['site']['sitename'], 
                    'name' => $data['name'], 
					'time' => $sms_time, 
					'addr' => $data['addr'], 
					'lifeservice' => $this->lifeservicecates[$cate_id['cate_id']]['cate_name']
                ));
            }else{
                D('Sms')->sendSms('sms_lifeservice_TZ_user', $user_mobile, array(
                    'name' => $data['name'], 
					'time' => $sms_time, 
					'addr' => $data['addr'], 
					'lifeservice' => $this->lifeservicecates[$cate_id['cate_id']]['cate_name']
                ));
            }
			//邮件通知管理员
			$lifeservice = $this->_CONFIG['site']['config_email'];			
			D('Email')->sendMail('email_lifeservice_yuyue', $lifeservice, $this->_CONFIG['site']['sitename'].'管理员：有客户预约'.$this->lifeservicecates[$cate_id['cate_id']]['cate_name'], array(
				'name'=>$data['name'],
				'date'=>$data['date'],
				'time'=>$data['time'],
				'addr'=>$data['addr'],
				'tel'=>$data['tel'],
				'contents'=>$data['contents']
			));
			//邮件通知商家

			if(!empty($shangjia_email)){		
			D('Email')->sendMail('email_sj_lifeservice_yuyue', $lifeservice_user['email'], '尊敬的商家，有客户预约'.$this->lifeservicecates[$cate_id['cate_id']]['cate_name'], array(
				'name'=>$data['name'],
				'date'=>$data['date'],
				'time'=>$data['time'],
				'addr'=>$data['addr'],
				'tel'=>$data['tel'],
				'contents'=>$data['contents']
				));
			}

            $this->baoSuccess('恭喜您预约家政服务成功！网站会推荐给您最优秀的阿姨帮忙！', U('lifeservice/index'));
        }
        $this->baoError('服务器繁忙');
    }
}


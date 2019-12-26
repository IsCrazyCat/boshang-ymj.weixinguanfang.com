<?php



class ActivityAction extends CommonAction {
	
	 public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['huodong'] == 0) {
				$this->error('此功能已关闭');die;
		}
    }
	

    public function index() {
        $linkArr = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $linkArr['keyword'] = $keyword;
        
        $cat = (int) $this->_param('cat');
        $this->assign('cat', $cat);
        $linkArr['cat'] = $cat;
        
        $bg_time = (int) $this->_param('bg_time');
        $this->assign('bg_time', $bg_time);
        $linkArr['bg_time'] = $bg_time;  
        
        $this->assign('nextpage', LinkTo('activity/loaddata',$linkArr,array('t' => NOW_TIME, 'p' => '0000')));
        $cates = D('Activitycate')->fetchAll();
        $this->assign('cates', $cates);
        $this->assign('linkArr',$linkArr);
        $this->display(); // 输出模板
    }

    public function loaddata() {
        $huodong = D('Activity');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('audit' => 1,'city_id'=>$this->city_id, 'closed' => 0, 'end_date' => array('EGT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $cat = (int) $this->_param('cat');
        if ($cat) {
            $map['cate_id'] = $cat;
        }
        $bg_time = (int) $this->_param('bg_time');
        switch ($bg_time) {
            case 1:
                $map['bg_date'] = array('EQ', TODAY);
                $linkArr['bg_time'] = $bg_time;
                break;
            case 2:
                $yestoday = NOW_TIME - 86400;
                $yestoday = date('Y-m-d',$yestoday);
                $map['bg_date'] = array('EQ', $yestoday);
                $linkArr['bg_time'] = $bg_time;
                break;
            case 3:
                $wk = NOW_TIME - 7*86400;
                $wk = date('Y-m-d',$wk);
                $map['bg_date'] = array('ELT',$wk);
                $linkArr['bg_time'] = $bg_time;
                break;
            case 4:
                $mk = NOW_TIME - 30*86400;
                $mk = date('Y-m-d',$mk);
                $map['bg_date'] = array('ELT',$mk);
                $linkArr['bg_time'] = $bg_time;
                break;
            case 5:
                $mk = NOW_TIME - 30*86400;
                $mk = date('Y-m-d',$mk);
                $map['bg_date'] = array('GT', $mk);
                $linkArr['bg_time'] = $bg_time;
                break;
        }
        $map['city_id']= $this->city_id;
        $count = $huodong->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $huodong->where($map)->order('orderby desc')->limit($Page->firstRow . ',' . $Page->listRows)->select(); 
        foreach ($list as $k => $val) {
            $sign = D('Activitysign')->where(array('user_id' => $this->uid, 'activity_id' => $val['activity_id']))->find();
            if (!empty($sign)) {
                $list[$k]['sign'] = 1;
            } else {
                $list[$k]['sign'] = 0;
            }
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $cates = D('Activitycate')->fetchAll();
        $this->assign('cates', $cates);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function detail() {
        $activity_id = (int) $this->_get('activity_id');
        if (empty($activity_id)) {
            $this->error('该活动信息不存在！');
            die;
        }
        if (!$detail = D('Activity')->find($activity_id)) {
            $this->error('该活动信息不存在！');
            die;
        }
        if ($detail['closed']) {
            $this->error('该活动信息不存在！');
            die;
        }
        $sign = D('Activitysign')->where(array('user_id' => $this->uid, 'activity_id' => $activity_id))->select();
        if (!empty($sign)) {
            $detail['sign'] = 1;
        } else {
            $detail['sign'] = 0;
        }
        $detail = D('Activity')->_format($detail);
        $detail['end_time'] = strtotime($detail['sign_end']) - NOW_TIME + 86400;
        $this->assign('detail', $detail);
        $shop_id = $detail['shop_id'];
        $shop = D('Shop')->find($shop_id);
        $cates = D('Activitycate')->fetchAll();
		
		$detail['thumb'] = unserialize($detail['thumb']);
		
        $this->assign('cates', $cates);
        $this->assign('shop', $shop);
        $this->display();
    }

    public function sign($activity_id) {
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
        }
        //$activity_id = (int) $this->_param('activity_id');
        $activity_id = (int) $activity_id;
        $detail = D('Activity')->find($activity_id);
        if (empty($detail)) {
            $this->error('报名的活动不存在');
        }
        if ($this->isPost()) {
            $data = $this->checkSign();

            $data['activity_id'] = $activity_id;
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $obj = D('Activitysign');
            if ($obj->add($data)) {
                D('Activity')->updateCount($activity_id, 'sign_num');
                $this->error('恭喜您报名成功', U('activity/index'));
            }
            $this->error('操作失败！');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }

    public function checkSign() {
        $data = $this->checkFields($this->_post('data', false), array('name', 'mobile', 'num'));
        $data['user_id'] = (int) $this->uid;
        $data['name'] = $data['name'];
        if (empty($data['name'])) {
            $this->error('联系人不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->error('联系电话不能为空');
        }
        if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->error('联系电话格式不正确');
        }
        $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->error('活动人数不能为空');
        }
        return $data;
    }

}

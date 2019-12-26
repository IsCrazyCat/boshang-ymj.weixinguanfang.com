<?php
class VillageAction extends CommonAction{
    public function _initialize() {
        parent::_initialize();
        $getVillageCate = D('Village')->getVillageCate();
        $this->assign('getVillageCate', $getVillageCate);
        if ($this->_CONFIG['operation']['village'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
    //社区村主页
    public function index(){
        $community_id = cookie('village_id');
        if ($community_id && empty($_GET['change'])) {
            $this->detail($community_id);
            die;
        }
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $areas = D('Area')->where('city_id=' . $this->city_id)->select();
		
        $this->assign('areas', $areas);
        $area = (int) $this->_param('area');
        $this->assign('area_id', $area);
		
		$order = $this->_param('order', 'htmlspecialchars');
        $this->assign('order', $order);
		
		
		$cate = $this->_param('cate', 'htmlspecialchars');
        $this->assign('cate', $cate);

        $this->assign('nextpage', LinkTo('village/loaddata', array('area' => $area, 't' => NOW_TIME, 'change' => '1', 'keyword' => $keyword, 'cate' => $cate, 'order' => $order, 'p' => '0000')));
        $this->display();
    }
    //社区村加载
    public function loaddata(){
        $community = D('Village');
        import('ORG.Util.Page');
        $map = array('city_id' => $this->city_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|addr'] = array('LIKE', '%' . $keyword . '%');
        }
        $area = (int) $this->_param('area');
        if ($area) {
            $map['area_id'] = $area;
        }
		
	
        $order = $this->_param('order', 'htmlspecialchars');
		$lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $orderby = '';
        switch ($order) {
            case 3:
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";//距离
                break;
            case 2:
                $orderby = array('orderby' => 'asc');//人数
                break;
            default:
                $orderby = array('create_time' => 'desc');
                break;
        }
		$cate = $this->_param('cate', 'htmlspecialchars');
        $lists = $community->order($orderby)->where($map)->select();
        foreach ($lists as $k => $val) {
            if (!empty($cate)) {
                if (strpos($val['cate'], $cate) === false) {
                    unset($lists[$k]);
                }
            }
        }
		
		
        $count = count($lists);
        $Page = new Page($count, 10);
        $show = $Page->show(); 
       
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);
		
        foreach ($list as $k => $val) {
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
		
        $this->assign('list', $list);
        $this->assign('page', $show);
        
        $this->display();
    }
    //社区村介绍
    public function detail($village_id){
        $community_id = (int) $village_id;
        $community = D('Village');
		 if (empty($community_id)) {
            $this->error('没有该社区村，清理cookie后重新登录');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该社区村，清理cookie后重新登录');
            die;
        }
        if ($detail['closed']) {
            $this->error('该社区村已经被删除');
            die;
        }
        $phone = D('Convenientphonevillages')->where(array('village_id' => $community_id))->limit(0, 5)->select();
        $phone_ids = array();
        foreach ($phone as $val) {
            $phone_ids[$val['phone_id']] = $val['phone_id'];
        }
        if (!empty($phone_ids)) {
            $this->assign('phones', D('Convenientphone')->itemsByIds($phone_ids));
        }
        $map = array('village_id' => $community_id, 'hot' => 1, 'audit' => 1);
        $bbs = D('village_bbs')->where($map)->order(array('create_time' => 'desc'))->limit(0, 5)->select();
        $map = array('village_id' => $community_id, 'type' => 1);
        $suggestion = D('village_suggestion')->where($map)->order(array('type' => 'desc', 'addtime' => 'desc'))->limit(0, 5)->select();
        $map = array('village_id' => $community_id, 'type' => 1);
        $notice = D('village_notice')->where($map)->order('addtime desc')->limit(0, 5)->select();
        $map = array('village_id' => $community_id, 'type' => 2);
        $notice_2 = D('village_notice')->where($map)->order('addtime desc')->limit(0, 5)->select();
        $map = array('village_id' => $community_id);
        $worker = D('village_worker')->where($map)->order('orderby asc')->limit(0, 4)->select();
        $map = array('closed' => 0, 'audit' => 1, 'city_id' => $this->city_id, 'end_date' => array('EGT', TODAY));
        $tuan = D('Tuan')->where($map)->limit(10)->select();
        $tuan_num = count($tuan);
        
        $lat = cookie('lat_ok');
        $lng = cookie('lng_ok');
        if (empty($lat) || empty($lng)) {
            $lat = cookie('lat');
            $lng = cookie('lng');
        }
        if (empty($lat) || empty($lng)) {
            $lat = $this->_CONFIG['site']['lat'];
            $lng = $this->_CONFIG['site']['lng'];
        }
        $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
        $shoplist = D('Shop')->where(array('closed' => 0, 'audit' => 1))->order($orderby)->limit(0, 4)->select();
        $join_info = D('Villagejoin')->where(array('village_id' => $community_id, 'user_id' => $this->uid))->select();
        $village_join = count($join_info);
        $this->assign('village_join', $village_join);

        $this->assign('shoplist', $shoplist);
        $this->assign('tuannum', $tuan_num);
        $keys = array_keys($tuan);
        shuffle($keys);
        $this->assign('tuan', $tuan);
        $this->assign('suggestion', $suggestion);
        $this->assign('keys', $keys);
        $this->assign('bbs', $bbs);
        $this->assign('worker', $worker);
        $this->assign('notice', $notice);
        $this->assign('notice_2', $notice_2);
        $this->assign('detail', $detail);
        $this->display('detail');
    }
    //社区村贴吧
    public function tieba()
    {
        $community_id = (int) $this->_param('village_id');
        $community = D('village');
        if (empty($community_id)) {
            $this->error('没有该社区村');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该社区村');
            die;
        }
        $join_info = D('Villagejoin')->where(array('village_id' => $community_id, 'user_id' => $this->uid))->select();
        if (count($join_info) == 0) {
            $this->error('您还没有入驻该社区村！');
            die;
        }
        $map = array('village_id' => $community_id);
        $this->assign('nextpage', LinkTo('village/loadtieba', array('village_id' => $community_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('detail', $detail);
        $count['post'] = D('village_bbs')->where($map)->count();
        $count['reply'] = D('Villagebbsreplys')->where($map)->count();
        $this->assign('count', $count);
        $this->display();
    }
    //贴吧帖子加载
    public function loadtieba()
    {
        $community_id = (int) $this->_param('village_id');
        $community = D('Village');
        if (empty($community_id)) {
            $this->error('没有该社区村');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该社区村');
            die;
        }
        $join_info = D('Villagejoin')->where(array('village_id' => $community_id, 'user_id' => $this->uid))->select();
        if (count($join_info) == 0) {
            $this->error('您还没有入驻该社区村2！');
            die;
        }
        $Tieba = D('Village_bbs');
        import('ORG.Util.Page');
        $map = array('village_id' => $community_id, 'audit' => 1);
        $count = $Tieba->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Tieba->where($map)->order(array('hot' => 'desc', 'create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
            $reply = D('Villagebbsreplys')->where('post_id=' . $val['post_id'])->count();
            $list[$k]['reply'] = $reply;
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('linkArr', $linkArr);
        $this->display();
    }
    //贴吧帖子加载-> 首页
    public function loading()
    {
        $community_id = (int) $this->_param('village_id');
        $community = D('Village');
        if (empty($community_id)) {
            $this->error('没有该社区村');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该社区村');
            die;
        }
        if ($detail['closed']) {
            $this->error('该社区村已经被删除');
            die;
        }
        $join_info = D('Villagejoin')->where(array('village_id' => $community_id, 'user_id' => $this->uid))->select();
        if (count($join_info) == 0) {
            $this->error('您还没有入驻该社区村！');
        }
        $Tieba = D('Village_bbs');
        import('ORG.Util.Page');
        $map = array('village_id' => $community_id, 'audit' => 1);
        $count = $Tieba->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Tieba->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
            $reply = D('Villagebbsreplys')->where(array('post_id' => $val['post_id']))->limit(0, 5)->order('reply_id desc')->select();
            foreach ($reply as $i => $arr) {
                $reply[$i]['user'] = D('Users')->find($arr[user_id]);
            }
            $list[$k]['reply'] = $reply;
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //贴吧帖子
    public function tie()
    {
        $post_id = (int) $this->_param('post_id');
        $tie = D('Village_bbs')->find($post_id);
        $puser = D('Users')->find($tie['user_id']);
        $tie['nickname'] = $puser['nickname'];
        if (empty($tie)) {
            $this->error('您查看的内容不存在！');
            die;
        }
        if ($tie['audit'] == 0) {
            $this->error('您查看的内容不存在！');
            die;
        }
        $community = D('Village');
        if (empty($tie['village_id'])) {
            $this->error('没有该社区村');
            die;
        }
        if (!($detail = $community->find($tie['Village_id']))) {
            $this->error('没有该社区村');
            die;
        }
        $join_info = D('Villagejoin')->where(array('village_id' => $tie['village_id'], 'user_id' => $this->uid))->select();
        if (count($join_info) == 0) {
            $this->error('您还没有入驻该社区村！');
        }
        D('Village_bbs')->updateCount($post_id, 'view_num');
        $this->assign('puser', $puser);
        $this->assign('detail', $detail);
        $this->assign('tie', $tie);
        $this->assign('replys', D('Villagebbsreplys')->where('post_id=' . $post_id)->count());
        $this->seodatas['title'] = $detail['title'];
        $this->assign('nextpage', LinkTo('village/loadreply', array('post_id' => $tie['post_id'], 't' => NOW_TIME, 'p' => '0000')));
        $this->display();
    }
    //贴吧点赞
    public function zantie()
    {
        if (empty($this->uid)) {
            echo '-2';
            die;
        }
        $post_id = (int) $this->_get('post_id');
        $tie = D('Village_bbs')->find($post_id);
        if (empty($tie)) {
            echo '-3';
        }
        D('Village_bbs')->updateCount($post_id, 'zan_num');
        $num = intval($tie['zan_num']) + 1;
        echo $num;
        die;
    }
    //贴吧回复加载
    public function loadreply()
    {
        $post_id = (int) $this->_param('post_id');
        $Postreply = D('Villagebbsreplys');
        import('ORG.Util.Page');
        $map = array('post_id' => $post_id, 'audit' => 1);
        $count = $Postreply->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Postreply->where($map)->order(array('reply_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $list[$k] = $val;
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //贴吧发帖
    public function post()
    {
        $community_id = (int) $this->_get('village_id');
        $community = D('Village');
        if (empty($community_id)) {
            $this->error('没有该社区村');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该社区村');
            die;
        }
        if (empty($this->uid)) {
            $this->error('登录后才能发帖！', U('passport/login'));
        }
        if ($this->isPost()) {
            $data = $this->postCheck();
            $obj = D('Village_bbs');
            $data['create_time'] = time();
            $data['create_ip'] = get_client_ip();
            $data['username'] = $this->member['nickname'];
            $data['user_id'] = $this->uid;
            $data['village_id'] = $detail['village_id'];
            $data['audit'] = 0;
            $last = $obj->add($data);
            if ($last) {
                $this->fengmiMsg('帖子发布成功！', U('village/tie', array('post_id' => $last)));
            }
            $this->fengmiMsg('发帖失败！');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    //贴吧发帖检测
    private function postCheck()
    {
        $data = $this->checkFields($this->_post('data', false), array('title', 'details'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title']) || $data['title'] == '标题') {
            $this->fengmiMsg('标题不能为空');
        }
        $data['user_id'] = (int) $this->uid;
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->fengmiMsg('详细内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->fengmiMsg('详细内容含有敏感词：' . $words);
        }
        return $data;
    }
    //贴吧回复
    public function reply($post_id)
    {
        if (empty($this->uid)) {
            $this->error('登录后才能发帖！', U('passport/login'));
        }
        $post_id = (int) $post_id;
        $tie = D('Village_bbs')->find($post_id);
        if (empty($tie)) {
            $this->fengmiMsg('没有该帖子');
        }
        if (!($detail = D('Village')->find($tie['village_id']))) {
            $this->error('没有该社区村');
            die;
        }
        if ($this->isPost()) {
            $data = $this->checkReply();
            $data['post_id'] = $post_id;
            $data['audit'] = 0;
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $obj = D('Villagebbsreplys');
            if ($obj->add($data)) {
                $this->fengmiMsg('回帖成功！', U('Village/tie', array('post_id' => $post_id)));
            }
            $this->fengmiMsg('回帖失败！');
        } else {
            $this->assign('tie', $tie);
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    //贴吧回复检测
    public function checkReply()
    {
        $data = $this->checkFields($this->_post('data', false), array('details', 'village_id'));
        $data['user_id'] = (int) $this->uid;
        $data['village_id'] = $data['village_id'];
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->fengmiMsg('内容不能为空！');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->fengmiMsg('详细内容含有敏感词：' . $words);
        }
        return $data;
    }
    //意见反馈
    public function suggestion($village_id)
    {

        $community_id = (int) $village_id;
        if (!($detail = D('Village')->find($community_id))) {
            $this->error('要反馈的社区村不存在');
        }
        if ($this->isPost()) {
            $data = $this->checkFeed();
            $data['village_id'] = $village_id;
            $data['addtime'] = NOW_TIME;
            $data['type'] = 0;
            $obj = D('Village_suggestion');
            if ($obj->add($data)) {
                $this->fengmiMsg('意见反馈提交成功', U('village/detail', array('village_id' => $village_id)));
            }
            $this->fengmiMsg('操作失败！');
        } else {
            $this->assign('detail', $detail);
            $this->assign('next', LinkTo('village/loadsuggestion', array('village_id' => $community_id, 'type' => 1, 'p' => '0000')));
            $this->display();
        }
    }
    public function loadsuggestion()
    {
        import('ORG.Util.Page');
        $map = array('village_id' => $this->_param('village_id'), 'type' => 1);
        $count = D('village_suggestion')->where($map)->count();
        $Page = new Page($count, 3);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = D('village_suggestion')->where($map)->order(array('replytime' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //检测
    public function checkFeed()
    {
        $data = $this->checkFields($this->_post('data', false), array('title', 'context', 'person', 'tel'));
        $data['title'] = $data['title'];
        if (empty($data['title'])) {
            $this->fengmiMsg('标题不能为空');
        }
        $data['person'] = $data['person'];
        if (empty($data['person'])) {
            $this->fengmiMsg('姓名不能为空');
        }
        $data['tel'] = $data['tel'];
        if (empty($data['tel'])) {
            $this->fengmiMsg('联系方式不能为空');
        }
        $data['context'] = htmlspecialchars($data['context']);
        if (empty($data['context'])) {
            $this->fengmiMsg('反馈内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['context'])) {
            $this->fengmiMsg('反馈内容含有敏感词：' . $words);
        }
        return $data;
    }
    //工作人员列表
    public function workerslist()
    {
        $community_id = (int) $this->_param('village_id');
        if (!($detail = D('village_worker')->where('village_id = ' . $community_id)->find())) {
            $this->error('要查看的社区村不存在');
        }
        $this->assign('next', LinkTo('village/loadworkers', array('village_id' => $community_id, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }
    public function noticelist()
    {
        $community_id = (int) $this->_param('village_id');
        if (!($detail = D('village')->find($community_id))) {
            $this->error('要反馈的社区村不存在');
        }
        $this->assign('next', LinkTo('village/loadnotice', array('t' => NOW_TIME, 'village_id' => $community_id, 'type' => (int) $this->_param('type'), 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->assign('type', $this->_param('type'));
        $this->display();
    }
    //通知加载
    public function loadnotice()
    {
        $community_id = (int) $this->_param('village_id');
        $news = D('Village_notice');
        import('ORG.Util.Page');
        $map = array('type' => $this->_param('type'));
        $map['village_id'] = $community_id;
        $count = $news->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $news->order(array('id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //共走人员加载
    public function loadworkers()
    {
        $community_id = (int) $this->_param('village_id');
        $workers = D('village_worker');
        import('ORG.Util.Page');
        $map = array('village_id' => $community_id);
        $count = $workers->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $workers->order(array('orderby' => 'asc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //通知内容
    public function notice()
    {
        $news_id = (int) $this->_param('id');
        if (!($news = D('village_notice')->find($news_id))) {
            $this->error('没有该通知或是活动展示');
            die;
        }
        $community_id = (int) $news['village_id'];
        if (!($detail = D('Village')->find($community_id))) {
            $this->error('要反馈的社区村不存在');
        }
        $this->assign('detail', $detail);
        $this->assign('news', $news);
        $this->display();
    }
    //便民合作
    public function together($village_id = null)
    {
        $community_id = (int) $village_id;
        if (!$community_id) {
            $this->error('参数不正确！');
        }
        if (!($detail = D('village')->find($community_id))) {
            $this->error('要反馈的社区村不存在');
        }
        if ($data = $this->_post('data', false)) {
            $data['expiry_date'] = NOW_TIME;
            $data['orderby'] = 0;

            if (empty($data['name']) || $data['name'] == '输入您的项目，如牛哥开锁...') {
                $this->fengmiMsg('项目名称不能为空！');
            }
            if (empty($data['phone']) || $data['phone'] == '输入联系电话...') {
                $this->fengmiMsg('手机号码不能为空！');
            }
            if ($phone_id = D('Convenientphone')->add($data)) {
                if (D('Convenientphonemaps')->add(array('phone_id' => $phone_id, 'community_id' => $community_id))) {
                    $this->fengmiMsg('您的申请提交成功,等待审核', U('community/detail', array('community_id' => $community_id)));
                } else {
                    $this->fengmiMsg('申请失败');
                }
            } else {
                $this->fengmiMsg('申请失败');
            }
        } else {
            $this->assign('next', LinkTo('village/loadtogether', array('t' => NOW_TIME, 'village_id' => $community_id, 'p' => '0000')));
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    public function loadtogether()
    {
        import('ORG.Util.Page');
        $map = array('village_id' => $this->_param('village_id'));
        $count = D('Convenientphonevillages')->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $phone = D('Convenientphonevillages')->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $phone_ids = array();
        foreach ($phone as $val) {
            $phone_ids[$val['phone_id']] = $val['phone_id'];
        }
        if (!empty($phone_ids)) {
            $list = D('Convenientphone')->where($map)->itemsByIds($phone_ids);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //附近服务
    public function near()
    {
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $community_id = (int) $this->_param('village_id');
        if (!($detail = D('Village')->find($community_id))) {
            $this->error('要反馈的社区村不存在');
        }
        $this->assign('keyword', $keyword);
        $this->assign('detail', $detail);
        $this->assign('nextpage', LinkTo('village/loadnear', array('t' => NOW_TIME, 'village_id' => $community_id, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
    }
    //附近服务加载
    public function loadnear()
    {
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $keyword = urldecode($keyword);
        $map['name|tag'] = array('LIKE', array('%' . $keyword . '%', '%' . $keyword, $keyword . '%', 'OR'));
        $community_id = (int) $this->_param('village_id');
        if (!($detail = D('Village')->find($community_id))) {
            $this->error('要反馈的社区村不存在');
        }
        $lat = $detail['lat'];
        $lng = $detail['lng'];
        import('ORG.Util.Page');
        $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
        $count = D('Near')->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            echo '附近没有' . $keyword;
            die(0);
        }
        $list = D('Near')->order($orderby)->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('lat', $lat);
        $this->assign('lng', $lng);
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function gps($village_id)
    {
        $shop_id = (int) $village_id;
        if (empty($shop_id)) {
            $this->error('该社区村不存在');
        }
        $shop = D('Village')->find($shop_id);
        $this->assign('shop', $shop);
        $this->display();
    }
    //社区村入驻
    public function joinvillage($village_id)
    {
        $village_id = (int) $village_id;
        if (empty($village_id)) {
            $this->error('请选择要入驻的社区村！');
        }
        if (empty($this->uid) || $this->uid == 0) {
            $this->error('登录后才能入驻！', U('passport/login'));
        }
        $join_info = D('Villagejoin')->where(array('village_id' => $village_id, 'user_id' => $this->uid))->select();
        if (count($join_info) > 0) {
            if (D('Villagejoin')->where(array('village_id' => $village_id, 'user_id' => $this->uid))->delete()) {
                $this->success('取消入驻成功！');
            }
        } else {
            $data['village_id'] = $village_id;
            $data['user_id'] = $this->uid;
            if (D('Villagejoin')->add($data)) {
                $this->success('入驻成功！');
            }
        }
    }
	
	  public function tuan(){
		$village_id = (int) $this->_param('village_id');
        if (!($detail = D('Village')->find($village_id))) {
            $this->error('要反馈的乡村不存在');
        }
        if (!empty($detail['closed'])) {
            $this->error('要反馈的乡村不存在');
        }
        $this->assign('nextpage', LinkTo('village/loadtuan', array('village_id' => $village_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }
    public function loadtuan(){
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
		import('ORG.Util.Page');
		$Tuan = D('Tuan');
		$map = array('closed' => 0, 'audit' => 1, 'expire' => array('EGT', TODAY));
        $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
		
		$count = $Tuan->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		
        $list = $Tuan->where($map)->order($orderby)->select();
        foreach ($tuans as $k => $val) {
            $tuans[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $this->assign('list', $list);
        $this->display();
    }
}
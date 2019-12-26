<?php
class CommunityAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        if ($this->_CONFIG['operation']['community'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
    public function owner($community_id){
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
        }
        $community_id = (int) $community_id;
        if (empty($community_id)) {
            $this->fengmiMsg('小区不存在');
        }
        if (!($detail = D('Community')->find($community_id))) {
            $this->fengmiMsg('小区不存在');
        }
        if ($detail['closed'] != 0) {
            $this->fengmiMsg('小区不存在');
        }
        if ($this->isPost()) {
            $data['location'] = htmlspecialchars($_POST['location']);
            if (empty($data['location'])) {
                $this->fengmiMsg('具体地址不能为空');
            }
            $data['name'] = htmlspecialchars($_POST['name']);
            if (empty($data['name'])) {
                $this->fengmiMsg('称呼不能为空');
            }
            $data['user_id'] = $this->uid;
            $data['community_id'] = $community_id;
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $obj = D('Communityowner');
            if (!($res = $obj->where(array('community_id' => $community_id, 'user_id' => $this->uid))->find())) {
                if ($obj->add($data)) {
                    $this->fengmiMsg('申请成功，请等待物业审核', U('community/detail', array('community_id' => $community_id)));
                }
                $this->fengmiMsg('申请失败');
            }
            $this->fengmiMsg('不能重复申请');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    //小区主页
    public function index(){
        $community_id = cookie('community_id');
        if ($community_id && empty($_GET['change'])) {
            $this->detail($community_id);
            die;
        }
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $areas = D('Area')->fetchAll();
        $this->assign('areas', $areas);
        $area = (int) $this->_param('area');
        $this->assign('area_id', $area);
        $this->assign('nextpage', LinkTo('community/loaddata', array('area' => $area, 't' => NOW_TIME, 'change' => '1', 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
    }
    //小区加载
    public function loaddata(){
        $community = D('Community');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'city_id' => $this->city_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|addr'] = array('LIKE', '%' . $keyword . '%');
        }
        $area = (int) $this->_param('area');
        if ($area) {
            $map['area_id'] = $area;
        }
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
        $count = $community->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $community->order($orderby)->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //小区介绍
    public function detail($community_id){
        $community_id = (int) $community_id;
        $community = D('Community');
        if (empty($community_id)) {
            $this->error('没有该小区');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该小区');
            die;
        }
        if ($detail['closed']) {
            $this->error('该小区已经被删除');
            die;
        }
        $phone = D('Convenientphonemaps')->where(array('community_id' => $community_id,'audit'=>1))->limit(0, 6)->select();
        $phone_ids = array();
        foreach ($phone as $val) {
            $phone_ids[$val['phone_id']] = $val['phone_id'];
        }
        if (!empty($phone_ids)) {
            $this->assign('phones', D('Convenientphone')->itemsByIds($phone_ids));
        }
        $map = array('community_id' => $community_id, 'closed' => 0, 'audit' => 1);
        $news = D('Communitynews')->where($map)->limit(0, 6)->select();
        $isjoin = D('Communityusers')->where(array('community_id' => $community_id, user_id => $this->uid))->count();
        $map = array('closed' => 0, 'audit' => 1, 'city_id' => $this->city_id, 'end_date' => array('EGT', TODAY));
        $tuan = D('Tuan')->where($map)->limit(10)->select();
        $keys = array_keys($tuan);
        shuffle($keys);
        $this->assign('tuan', $tuan);
        $this->assign('keys', $keys);
        $cat = (int) $this->_param('cat');
        $this->assign('cat', $cat);
        $this->assign('isjoin', $isjoin);
        $this->assign('nextpage', LinkTo('community/loading', array('community_id' => $community_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('news', $news);
        $this->assign('detail', $detail);
        $owner = D('Communityowner')->where(array('community_id' => $community_id, 'user_id' => $this->uid))->find();
        $this->assign('owner', $owner);
        $ads = D('Communityad')->order(array('orderby' => 'asc'))->where(array('community_id' => $community_id))->limit(5)->select();
        $this->assign('ads', $ads);
        $this->assign('counts', $counts = D('Communityusers')->where(array('community_id' => $community_id))->count());
        $this->assign('user_owner', $user_owner = D('Communityowner')->where(array('community_id' => $community_id))->count());
        $zhangdan = D('Communityorder')->order(array('order_date' => 'desc'))->where(array('user_id' => $this->uid))->select();
        $user_ids = $order_ids = array();
        foreach ($zhangdan as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
        }
        $map_pays = array();
        $map_pays['money'] = array('gt', 0);
        $map_pays['order_id'] = array('IN', $order_ids);
        $map_pays['is_pay'] = 0;
        $map_pays['community_id'] = $community_id;
        $products_pay = D('Communityorderproducts')->where($map_pays)->count();
        $this->assign('products_pay', $products_pay);
        $this->display('detail');
    }
    //小区贴吧
    public function tieba(){
        $community_id = (int) $this->_param('community_id');
        $community = D('Community');
        if (empty($community_id)) {
            $this->error('没有该小区');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该小区');
            die;
        }
        if ($detail['closed']) {
            $this->error('该帖子已经被删除或者未通过审核');
            die;
        }
        $this->assign('nextpage', LinkTo('community/loadtieba', array('community_id' => $community_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('detail', $detail);
		
		
        $count['post'] = D('Communityposts')->where(array('community_id'=>$community_id,'closed'=>0))->count();
        $count['reply'] = D('Postreply')->where(array('community_id'=>$community_id,'audit'=>1))->count();
        $this->assign('count', $count);
        $this->display();
    }
    //贴吧帖子加载
    public function loadtieba(){
        $community_id = (int) $this->_param('community_id');
        $community = D('Community');
        if (empty($community_id)) {
            $this->error('没有该小区');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该小区');
            die;
        }
        if ($detail['closed']) {
            $this->error('该帖子已经被删除或者未通过审核');
            die;
        }
        $Tieba = D('Communityposts');
        import('ORG.Util.Page');
        $map = array('community_id' => $community_id, 'closed' => 0, 'audit' => 1);
        $count = $Tieba->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Tieba->where($map)->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
            $reply = D('Communityreplys')->where(array('post_id' => $val['post_id'], 'closed' => 0, 'audit' => 1))->limit(0, 5)->order('reply_id desc')->select();
            foreach ($reply as $i => $arr) {
                $reply[$i]['user'] = D('Users')->find($arr[user_id]);
            }
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
        $community_id = (int) $this->_param('community_id');
        $community = D('Community');
        if (empty($community_id)) {
            $this->error('没有该小区');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该小区');
            die;
        }
        if ($detail['closed']) {
            $this->error('该小区已经被删除');
            die;
        }
        $Tieba = D('Communityposts');
        import('ORG.Util.Page');
        $map = array('community_id' => $community_id, 'closed' => 0, 'audit' => 1);
        $count = $Tieba->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Tieba->where($map)->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
            $reply = D('Communityreplys')->where(array('post_id' => $val['post_id']))->limit(0, 5)->order('reply_id desc')->select();
            foreach ($reply as $i => $arr) {
                $reply[$i]['user'] = D('Users')->find($arr[user_id]);
            }
            $list[$k]['reply'] = $reply;
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('linkArr', $linkArr);
        $this->display();
    }
    //贴吧帖子
    public function tie()
    {
        $post_id = (int) $this->_get('post_id');
        $tie = D('Communityposts')->find($post_id);
        $puser = D('Users')->find($tie['user_id']);
        $tie['nickname'] = $puser['nickname'];
        if (empty($tie)) {
            $this->error('您查看的内容不存在！');
            die;
        }
        $community = D('Community');
        if (empty($tie['community_id'])) {
            $this->error('没有该小区');
            die;
        }
        if (!($detail = $community->find($tie['community_id']))) {
            $this->error('没有该小区');
            die;
        }
        if ($detail['closed']) {
            $this->error('该小区已经被删除');
            die;
        }
        if ($tie['audit'] != 1) {
            $this->error('该信息不存在或者未审核', U('community/index'));
        }
        D('Communityposts')->updateCount($post_id, 'view_num');
        $this->assign('puser', $puser);
        $this->assign('cate', $cate);
        $this->assign('detail', $detail);
        $this->assign('tie', $tie);
        $this->assign('count', $count);
        $this->seodatas['title'] = $detail['title'];
        $this->assign('nextpage', LinkTo('community/loadreply', array('post_id' => $tie['post_id'], 't' => NOW_TIME, 'p' => '0000')));
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
        $tie = D('Communityposts')->find($post_id);
        if (empty($tie)) {
            echo '-3';
        }
        D('Communityposts')->updateCount($post_id, 'zan_num');
        $num = intval($tie['zan_num']) + 1;
        echo $num;
        die;
    }
    //贴吧回复加载
    public function loadreply()
    {
        $post_id = (int) $this->_param('post_id');
        $Postreply = D('Communityreplys');
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
        $community_id = (int) $this->_get('community_id');
        $community = D('Community');
        if (empty($community_id)) {
            $this->error('没有该小区');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该小区');
            die;
        }
        if ($detail['closed']) {
            $this->error('该小区已经被删除');
        }
        if (empty($this->uid)) {
            $this->error('登录后才能发帖！', U('passport/login'));
        }
        $count = D('Communityusers')->where(array('community_id' => $community_id, user_id => $this->uid))->count();
        if ($count == 0) {
            $this->error('您还没有入驻该小区！');
        }
        if ($this->isPost()) {
            $data = $this->postCheck();
            $obj = D('Communityposts');
            $data['audit'] = $this->_CONFIG['site']['xiaoqu_post_audit'];//回帖是否免审核
            
            $data['create_time'] = time();
            $data['create_ip'] = get_client_ip();
            $data['username'] = $this->member['nickname'];
            $data['user_id'] = $this->uid;
            $data['community_id'] = $detail['community_id'];
			
			//发帖传图开始
			$tupian = $this->_post('data');
            $photos = $this->_post('photos', false);
            $photo = $val = '';
            if (!empty($photos)) {
                foreach ($photos as $val) {
                    if (isImage($val) && $val != '') {
						$photo = $photo . '<img src='. config_img($val) .'>';
                    }
                }
            }
            $photo1 = $val1 = '';
            if (!empty($photos)) {
                foreach ($photos as $val1) {
                    if (isImage($val1) && $val1 != '') {
                        $photo1 = $photo1 . ',' . $val1;
                    }
                }
            }
            $data['gallery'] = ltrim($photo1, ',');
            $data['details'] = $tupian[details] . $photo;
			//发帖传图结束
			
			
            $last = $obj->add($data);
            if ($last) {
                $this->fengmiMsg('帖子发布成功！', U('community/tie', array('post_id' => $last)));
            }
            $this->fengmiMsg('发帖失败！');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    //贴吧发帖检测
    private function postCheck(){
        $data = $this->checkFields($this->_post('data', false), array('title', 'details', 'gallery', 'photo'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title']) || $data['title'] == '标题') {
            $this->fengmiMsg('标题不能为空');
        }
        $data['user_id'] = (int) $this->uid;
        $data['details'] = SecurityEditorHtml($data['details']);
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->fengmiMsg('详细内容含有敏感词：' . $words);
        }
        return $data;
    }
    //贴吧回复
    public function reply($post_id){
        if (empty($this->uid)) {
            $this->error('登录后才能发帖！', U('passport/login'));
        }
        $post_id = (int) $post_id;
        $tie = D('Communityposts')->find($post_id);
        if (empty($tie)) {
            $this->fengmiMsg('没有该帖子');
        }
        if (!($detail = D('Community')->find($tie['community_id']))) {
            $this->error('没有该小区');
            die;
        }
        if ($detail['closed']) {
            $this->error('该小区已经被删除');
        }
        $count = D('Communityusers')->where(array('community_id' => $detail['community_id'], user_id => $this->uid))->count();
        if ($count == 0) {
            $this->error('您还没有入驻该小区！');
        }
        if ($this->isPost()) {
            $data = $this->checkReply();
            $data['post_id'] = $post_id;
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $data['audit'] = $this->_CONFIG['site']['xiaoqu_reply_audit'];
            $obj = D('Communityreplys');
            if ($obj->add($data)) {
                D('Communityposts')->updateCount($post_id, 'reply_num');
                $this->fengmiMsg('帖子发布成功！', U('community/tie', array('post_id' => $post_id)));
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
        $data = $this->checkFields($this->_post('data', false), array('details', 'photo'));
        $data['user_id'] = (int) $this->uid;
        $data['gallery'] = $data['photo'];
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->fengmiMsg('内容不能为空！');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->fengmiMsg('详细内容含有敏感词：' . $words);
        }
        return $data;
    }
    //联系物业
    public function contact($community_id)
    {
        $community_id = (int) $community_id;
        $community = D('Community');
        if (empty($community_id)) {
            $this->error('没有该小区');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该小区');
            die;
        }
        if ($detail['closed']) {
            $this->error('该小区已经被删除');
            die;
        }
        $this->assign('detail', $detail);
        $this->display();
    }
    // 小区邻居
    public function neighbor($community_id)
    {
        $community_id = (int) $community_id;
        $community = D('Community');
        if (empty($community_id)) {
            $this->error('没有该小区');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该小区');
            die;
        }
        if ($detail['closed']) {
            $this->error('该小区已经被删除');
            die;
        }
        $this->assign('detail', $detail);
        $this->assign('nextpage', LinkTo('community/loadneighbor', array('community_id' => $detail['community_id'], 't' => NOW_TIME, 'p' => '0000')));
        $this->display();
    }
    //贴吧邻居加载
    public function loadneighbor()
    {
        $community_id = (int) $this->_param('community_id');
        $community = D('Community');
        if (empty($community_id)) {
            $this->error('没有该小区');
            die;
        }
        if (!($detail = $community->find($community_id))) {
            $this->error('没有该小区');
            die;
        }
        if ($detail['closed']) {
            $this->error('该小区已经被删除');
            die;
        }
        $Users = D('Communityusers');
        import('ORG.Util.Page');
        $map = array('community_id' => $community_id);
        $count = $Users->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Users->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('ex', D('Usersex')->itemsByIds($ids));
        $this->assign('stars', D('Usersex')->getStar());
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //入驻小区
    public function join()
    {
        if (empty($this->uid)) {
            $this->error('登录后才能发帖！', U('passport/login'));
        }
        $community_id = (int) $this->_get('community_id');
        if (!($detail = D('Community')->find($community_id))) {
            $this->error('没有该小区');
        }
        if ($detail['closed']) {
            $this->error('该小区已经被删除');
        }
        $count = D('Communityusers')->where(array('community_id' => $community_id, 'user_id' => $this->uid))->count();
        if ($count > 0) {
            $this->error('您已经入驻了该小区！');
        }
        $data = array('community_id' => $community_id, 'user_id' => $this->uid);
        if (D('Communityusers')->add($data)) {
            $this->error('欢迎您加入' . $detail['name'] . '小区！', U('community/detail', array('community_id' => $community_id)));
        }
        $this->error('加入' . $detail['name'] . '失败！');
    }
    //退出小区
    public function out()
    {
        if (empty($this->uid)) {
            $this->error('登录后才能发帖！', U('passport/login'));
        }
        $community_id = (int) $this->_get('community_id');
        if (!($detail = D('Community')->find($community_id))) {
            $this->error('没有该小区');
        }
        if ($detail['closed']) {
            $this->error('该小区已经被删除');
        }
        $count = D('Communityusers')->where(array('community_id' => $community_id, user_id => $this->uid))->count();
        if ($count == 0) {
            $this->error('您还没有入驻了该小区！');
        }
        $map = array('community_id' => $community_id, 'user_id' => $this->uid);
        if (D('Communityusers')->where($map)->delete()) {
            $this->error('您已经退出' . $detail['name'] . '小区！', U('community/detail', array('community_id' => $community_id)));
        }
        $this->error('退出' . $detail['name'] . '失败！');
    }
    //物业反馈
    public function feedback($community_id)
    {
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
        }
        $community_id = (int) $community_id;
        if (!($detail = D('Community')->find($community_id))) {
            $this->error('要反馈的小区不存在');
        }
        if (!empty($detail['closed'])) {
            $this->error('要反馈的小区不存在');
        }
        if ($this->isPost()) {
            $data = $this->checkFeed();
            $data['community_id'] = $community_id;
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $obj = D('Feedback');
            if ($obj->add($data)) {
                $this->fengmiMsg('反馈提交成功', U('community/detail', array('community_id' => $community_id)));
            }
            $this->fengmiMsg('操作失败！');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    //物业检测
    public function checkFeed()
    {
        $data = $this->checkFields($this->_post('data', false), array('title', 'details'));
        $data['user_id'] = (int) $this->uid;
        $data['title'] = $data['title'];
        if (empty($data['title'])) {
            $this->fengmiMsg('标题不能为空');
        }
        $data['details'] = htmlspecialchars($data['details']);
        if (empty($data['details'])) {
            $this->fengmiMsg('反馈内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->fengmiMsg('反馈内容含有敏感词：' . $words);
        }
        return $data;
    }
    //物业通知
    public function newslist()
    {
        $community_id = (int) $this->_param('community_id');
        if (!($detail = D('Community')->find($community_id))) {
            $this->error('要反馈的小区不存在');
        }
        if (!empty($detail['closed'])) {
            $this->error('要反馈的小区不存在');
        }
        $this->assign('next', LinkTo('community/loadnews', array('t' => NOW_TIME, 'community_id' => $community_id, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }
    //物业通知加载
    public function loadnews()
    {
        $community_id = (int) $this->_param('community_id');
        $news = D('Communitynews');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'audit' => 1);
        $map['community_id'] = $community_id;
        $count = $news->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $news->order(array('news_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //物业通知内容
    public function news()
    {
        $news_id = (int) $this->_param('news_id');
        if (!($news = D('Communitynews')->find($news_id))) {
            $this->error('没有该物业通知');
            die;
        }
        if ($news['closed']) {
            $this->error('该物业通知已经被删除');
            die;
        }
        if (!$news['audit']) {
            $this->error('该物业通知未通过审核');
            die;
        }
        D('Communitynews')->updateCount($news_id, 'views');
        $community_id = (int) $news['community_id'];
        if (!($detail = D('Community')->find($community_id))) {
            $this->error('要反馈的小区不存在');
        }
        if (!empty($detail['closed'])) {
            $this->error('要反馈的小区不存在');
        }
        $this->assign('detail', $detail);
        $this->assign('news', $news);
        $this->display();
    }
    //便民合作
    public function together($community_id = null){
        if (!$community_id) {
            $this->error('参数不正确！');
        }
        if (!($detail = D('Community')->find($community_id))) {
            $this->error('要反馈的小区不存在');
        }
        if (!empty($detail['closed'])) {
            $this->error('要反馈的小区不存在');
        }
		//二开设置
        if ($data = $this->_post('data', false)) {
            $data['expiry_date'] = date('Y-m-d', NOW_TIME + ($this->_CONFIG['community']['community_phone_expiry_date'] * 86400));
            $data['orderby'] = $this->_CONFIG['community']['community_phone_orderby'];
			$data['community_id'] = $community_id;
			$data['audit'] = $this->_CONFIG['community']['community_phone_audit'];
            if (empty($data['name'])) {
                $this->fengmiMsg('项目名称不能为空！');
            }
            if (empty($data['phone'])) {
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
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    //附近服务
    public function near()
    {
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $community_id = (int) $this->_param('community_id');
        if (!($detail = D('Community')->find($community_id))) {
            $this->error('要反馈的小区不存在');
        }
        if (!empty($detail['closed'])) {
            $this->error('要反馈的小区不存在');
        }
        $this->assign('keyword', $keyword);
        $this->assign('detail', $detail);
        $this->assign('nextpage', LinkTo('community/loadnear', array('t' => NOW_TIME, 'community_id' => $community_id, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
    }
    //附近服务加载
    public function loadnear()
    {
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $keyword = urldecode($keyword);
        $map['name|tag'] = array('LIKE', array('%' . $keyword . '%', '%' . $keyword, $keyword . '%', 'OR'));
        $community_id = (int) $this->_param('community_id');
        if (!($detail = D('Community')->find($community_id))) {
            $this->error('要反馈的小区不存在');
        }
        if (!empty($detail['closed'])) {
            $this->error('要反馈的小区不存在');
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
            die('0');
        }
        $list = D('Near')->order($orderby)->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('lat', $lat);
        $this->assign('lng', $lng);
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //附近小区活动关联
    public function activity()
    {
        $community_id = cookie('community_id');
        if (!($detail = D('Community')->find($community_id))) {
            $this->error('要反馈的小区不存在');
        }
        if (!empty($detail['closed'])) {
            $this->error('要反馈的小区不存在');
        }
        $community_id = (int) $this->_param('community_id');
        $this->assign('detail', $detail);
        $this->display();
    }
    //附近小区代收快递
    public function express()
    {
        if (empty($this->uid)) {
            $community_id = (int) $this->_param('community_id');
            $this->error('登录状态失效!', U('passport/login'));
        }
        if (!($detail = D('Community')->find($community_id))) {
            $this->error('要反馈的小区不存在');
        }
        if (!empty($detail['closed'])) {
            $this->error('要反馈的小区不存在');
        }
        $count = D('Communityusers')->where(array('community_id' => $community_id, user_id => $this->uid))->count();
        if ($count == 0) {
            $this->error('您还没有入驻该小区！');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
    //物业缴费
    public function order($community_id)
    {
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
        }
        if (empty($community_id)) {
            $this->error('小区不存在');
        }
        if (!($detail = D('Community')->find($community_id))) {
            $this->error('没有该小区');
            die;
        }
        if ($detail['closed']) {
            $this->error('该小区已经被删除');
            die;
        }
        if (!($res = D('Communityowner')->where(array('community_id' => $community_id, 'user_id' => $this->uid))->find())) {
            redirect(U('community/owner', array('community_id' => $community_id)));
        }
        $obj = D('Communityorder');
        $map = array('user_id' => $this->uid, 'community_id' => $community_id);
        $order_date = $this->_param('order_date', 'htmlspecialchars');
        $now_date = date('Y-m', NOW_TIME);
        if (!empty($order_date)) {
            $map['order_date'] = $order_date;
            $this->assign('order_date', $order_date);
        } else {
            $this->assign('order_date', $now_date);
            $map['order_date'] = $now_date;
        }
        $list = $obj->where($map)->find();
        $products = D('Communityorderproducts')->where(array('order_id' => $list['order_id']))->select();
        $types = D('Communityorder')->getType();
        foreach ($products as $k => $val) {
            $products[$k]['type_name'] = $types[$val['type']];
        }
        $days = array();
        $Y = date('Y', NOW_TIME);
        $m = date('m', NOW_TIME);
        $d = 12 - $m;
        for ($k = 1; $k <= $d; $k++) {
            $days[$k] = $Y - 1 . '-' . (12 - $k);
        }
        for ($i = 1; $i <= $m; $i++) {
            $days[$d + $i] = $Y . '-0' . $i;
        }
        $this->assign('days', $days);
        $this->assign('products', $products);
        $this->assign('detail', $detail);
        $this->display();
    }
    //物业缴费列表
    public function orderpay()
    {
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        if (IS_AJAX) {
            $community_id = (int) $_POST['community_id'];
            if (empty($community_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '小区不存在'));
            }
            $order_date = htmlspecialchars($_POST['order_date']);
            $detail = D('Communityorder')->where(array('community_id' => $community_id, 'order_date' => $order_date, 'user_id' => $this->uid))->find();
            if (empty($detail)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '账单不存在'));
            }
            $products = D('Communityorderproducts')->order(array('type' => 'asc'))->where(array('order_id' => $detail['order_id']))->select();
            $pp = array();
            foreach ($products as $k => $val) {
                $pp[$val['type']] = round($val['money'] / 100, 2);
            }
            $type = $_POST['type'];
            foreach ($type as $k => $val) {
                if (empty($val)) {
                    unset($type[$k]);
                } else {
                    $type[$k] = floatval($val);
                }
            }
            $res = array_uintersect($type, $pp, 'array_comparison');
            if ($res === $type) {
                $total2 = 0;
                foreach ($type as $k => $val) {
                    $total2 += $val;
                }
                $total = $_POST['total'];
                //提交的价格
                if ($total2 == $total) {
                    if ($this->member['money'] < $total * 100) {
                        $this->ajaxReturn(array('status' => 'error', 'msg' => '账户余额不足', 'url' => U('mcenter/money/index')));
                    } else {
                        if (false != D('Communityorder')->orderpay($detail['order_id'], $this->uid, $type, $total * 100)) {
                            $this->ajaxReturn(array('status' => 'success', 'msg' => '缴费成功'));
                        } else {
                            $this->ajaxReturn(array('status' => 'error', 'msg' => '缴费失败'));
                        }
                    }
                } else {
                    $this->ajaxReturn(array('status' => 'error', 'msg' => '金额不正确，或者单选缴费项目试试'));
                }
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '账单不合法'));
            }
        }
    }
    //附近小区团购
    public function tuan()
    {
        $community_id = cookie('community_id');
        if (!($detail = D('Community')->find($community_id))) {
            $this->error('要反馈的小区不存在');
        }
        if (!empty($detail['closed'])) {
            $this->error('要反馈的小区不存在');
        }
        $community_id = (int) $this->_param('community_id');
        $this->assign('nextpage', LinkTo('community/loadtuan', array('community_id' => $community_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }
    //附近的团购
    public function loadtuan(){
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
        $tuans = D('Tuan')->where(array('closed' => 0, 'audit' => 1, 'expire' => array('EGT', TODAY)))->order($orderby)->limit(0, 6)->select();
        foreach ($tuans as $k => $val) {
            $tuans[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $this->assign('tuans', $tuans);
        $this->display();
    }
    public function ele()
    {
        $community_id = (int) $this->_param('community_id');
        $detail = D('Community')->find($community_id);
        $this->assign('nextpage', LinkTo('community/eleload', array('t' => NOW_TIME, 'community_id' => $community_id, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->mobile_title = '小区外卖';
        $this->display();
    }
    public function eleload()
    {
        $community_id = (int) $this->_param('community_id');
        $community = D('Community')->find($community_id);
        $ele = D('Ele');
        import('ORG.Util.Page');
        $map = array('audit' => 1, 'city_id' => $this->city_id);
        $lat = $community['lat'];
        $lng = $community['lng'];
        if (empty($lat) || empty($lng)) {
            $lat = addslashes(cookie('lat'));
            $lng = addslashes(cookie('lng'));
        }
        $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
        $count = $ele->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $ele->order($orderby)->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function weidian(){
        $community_id = (int) $this->_param('community_id');
        $detail = D('Community')->find($community_id);
        $this->assign('nextpage', LinkTo('community/weiload', array('t' => NOW_TIME, 'community_id' => $community_id, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->mobile_title = '小区微店';
        $this->display();
    }
    public function weiload(){
        $community_id = (int) $this->_param('community_id');
        $detail = D('Community')->find($community_id);
        $weidian = D('Weidiandetails');
        import('ORG.Util.Page');
        $map = array('audit' => 1, 'city_id' => $this->city_id);
        $lat = $detail['lat'];
        $lng = $detail['lng'];
        if (empty($lat) || empty($lng)) {
            $lat = addslashes(cookie('lat'));
            $lng = addslashes(cookie('lng'));
        }
        $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
        $count = $weidian->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $weidian->order($orderby)->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $cates = D('Weidiancate')->fetchAll();
        $this->assign('cates', $cates);
        $this->display();
    }
    public function pinche(){
        $community_id = (int) $this->_param('community_id');
        $detail = D('Community')->find($community_id);
        $this->assign('nextpage', LinkTo('community/pincheload', array('t' => NOW_TIME, 'community_id' => $community_id, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }
    public function pincheload(){
        $community_id = (int) $this->_param('community_id');
        $pinche = D('Pinche');
        import('ORG.Util.Page');
		
		$lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
		
		
        $map = array('audit' => 1,'city_id'=>$this->city_id,'community_id'=>$community_id, 'closed' => 0, 'start_time' => array('EGT', TODAY));
        $count = $pinche->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 

        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $pinche->where($map)->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
		
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}
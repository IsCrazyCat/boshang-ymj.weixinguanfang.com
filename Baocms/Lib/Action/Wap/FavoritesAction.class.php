<?php

class FavoritesAction extends CommonAction {

    public function index() {
        if(empty($this->uid)){
            header("Location:".U('passport/login'));die;
        }
        $like = I('like', '', 'trim,htmlspecialchars');
        $this->assign('like', $like);
		$this->assign('nextpage', LinkTo('favorites/favoritesloading', array("t"=>time(),"p"=>"0000","like"=>$like)));
        $this->display();
    }

    public function favoritesloading() {
        $like = I('like', '', 'trim,htmlspecialchars');
        $Shopfavorites = D('Shopfavorites');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Shopfavorites->where($map)->count();
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
			die('0');
        }
        $list = $Shopfavorites->where($map)->order('last_news_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        $last_news_ids= $read_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
            if(!empty($val['last_news_id'])){
                $last_news_ids[$val['last_news_id']] = $val['last_news_id'];
                $read_ids[$val['read_id']] = $val['read_id'];
            }
        }

        $shops = array();
        if(!empty($shop_ids)){
            if ($like) {
                $shops = D('Shop')->where(array('shop_id'=>array('IN',$shop_ids),'shop_name'=>array('like','%'.$like.'%')))->select();
            } else {
                $shops = D('Shop')->itemsByIds($shop_ids);
            }
        }
        
        if(!empty($last_news_ids)){
            $news = D('Shopnews')->itemsByIds($last_news_ids);
            $newsdata = array();
            foreach($news as $val){
                $newsdata[$val['shop_id']] = $val;
            }
            $this->assign('news',$newsdata);
        }
        $this->assign('read_ids',$read_ids);
        $this->assign('shops', $shops);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    
    public function listsloading(){
        
        $like = I('like', '', 'trim,htmlspecialchars');
        $shopObj = D('Shop');
        import('ORG.Util.Page');
        $map = array('audit'=>1,'closed'=>0);
        if($like){
            $map['shop_name|tags'] = array('like','%'.$like.'%');
            $this->assign('like',$like);
        }else{
            $map['city_id'] = $this->city_id;//搜索的时候 不限制城市
        }
        
        $count = $shopObj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $lat = cookie('lat');
        $lng = cookie('lng');
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $list = $shopObj->where($map)->order('orderby asc,fans_num desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
       // print_r($shopObj->getLastSql());die;
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $datas = D('Shopfavorites')->where(array('user_id'=>$this->uid,'shop_id'=>array('IN',$shop_ids)))->select();
        $guanzhu = array();
        foreach($datas as $val){
            $guanzhu[$val['shop_id']] = $val;
        }
        $this->assign('guanzhu',$guanzhu);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    
    public function add(){
        if(empty($this->uid)){
            $this->error('请登录后关注',U('passport/login'));
        }
        $shop_id = (int) $this->_get('shop_id');
        if (!$detail = D('Shop')->find($shop_id)) {
            $this->error('没有该商家');
        }
        if ($detail['closed']) {
            $this->error('该商家已经被删除');
        }
        if (D('Shopfavorites')->check($shop_id, $this->uid)) {
            $this->error('您已经关注过该商家了！');
        }
        $data = array(
            'shop_id' => $shop_id,
            'user_id' => $this->uid,
            'create_time' => NOW_TIME,
            'create_ip' => get_client_ip()
        );
        if (D('Shopfavorites')->add($data)) {
            D('Shop')->updateCount($shop_id,'fans_num');
            $this->success('恭喜您关注成功！',U('favorites/index'));
        }
        $this->error('关注失败！');
        
    }
    
    //搜索要关注的商家
    public function lists(){
        if(empty($this->uid)){
            header("Location:".U('passport/login'));die;
        }
        $like = I('like', '', 'trim,htmlspecialchars,remove_xss');
        $this->assign('like', $like);
        $this->display();
    }
    
    //
    public function detail(){
        if(empty($this->uid)){
            header("Location:".U('passport/login'));die;
        }
        $shop_id = (int)$this->_get('shop_id');
        if(!$detail = D('Shop')->find($shop_id)){
            $this->error('商家不存在');
        }
        if($detail['audit']!=1 || $detail['closed']!=0){
            $this->error('该商家不存在 ');
        }
        if(!$fans = D('Shopfavorites')->check($shop_id,$this->uid)){
            $this->error('您还未关注该商家');
        }
        $news = D('Shopnews')->where(array(
            'shop_id' => $shop_id,
            'audit' => 1,
            'news_id' => array(
                'EGT',$fans['read_id']
            )
        ))->order(array('news_id'=>'desc'))->limit(0,1)->select();
        $this->assign('news',$news);
        $this->assign('detail',$detail);
        $details = D('Shopdetails')->find($shop_id);
        $datas =  unserialize($details['menus']);
        $this->assign('weixin',  $datas);
        $this->display();
    }
    //读文章
    public function read($news_id){
        if(!$detail = D('Shopnews')->find($news_id)){
            $this->error('您所查看的内容不存在');
        }
		
        if($detail['audit'] != 1){
            $this->error('您所查看的内容暂未通过审核');
        }
        if($this->uid){
            $fans = D('Shopfavorites')->check($detail['shop_id'],$this->uid);
            if($fans && $fans['read_id']< $new_id){
                $fans['read_id'] = $new_id;
                D('Shopfavorites')->save($fans);
            }
        }
        D('Shopnews')->updateCount($new_id,'views');
        $this->assign('shop',D('Shop')->find($detail['shop_id']));
        $this->assign('detail',$detail);
        $this->display();
    }
    
    // 发送记录 关键字响应
    public function send(){
        if(empty($this->uid)){
            $this->error('您还没有登录');
        }
        $shop_id = (int)$this->_get('shop_id');
        if(!$detail = D('Shop')->find($shop_id)){
            $this->error('商家不存在');
        }
        if($detail['audit']!=1 || $detail['closed']!=0){
            $this->error('该商家不存在 ');
        }
        if(!$fans = D('Shopfavorites')->check($shop_id,$this->uid)){
            $this->error('您还未关注该商家');
        }
        $word = htmlspecialchars($_POST['word']);
        $keyword = D('Shopweixinkeyword')->checkKeyword($shop_id,$word);
        if($keyword){
             switch ($keyword['type']) {
                 case 'text':
                     $data = array(
                         'ret' => 1,
                         'type' => 'text',
                         'contents' => $keyword['contents'],
                         'face' => __ROOT__.'/attachs/' .$detail['logo'],
                     );
                     die(json_encode($data));
                     break;
                 case 'news':
                     $data = array(
                         'ret'   => 1,
                         'type'  => 'news',
                         'title' =>$keyword['title'],
                         'intro' => msubstr($keyword['contents'],0,60),
                         'photo' => __ROOT__.'/attachs/' .$keyword['photo'],
                         'url'   => $keyword['url'],
                         'face' => __ROOT__.'/attachs/' .$detail['logo'],
                     );
                     die(json_encode($data));
                     break;
             }
            
        }else{
            die(json_encode(array('ret'=>0)));
        }       
    }
    
    
}

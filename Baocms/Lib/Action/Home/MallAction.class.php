<?php
class MallAction extends CommonAction{
    protected $goodscate = array();
    public function _initialize(){
        parent::_initialize();
        if ($this->_CONFIG['operation']['mall'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $this->goodscate = D('Goodscate')->fetchAll();
        $this->assign('goodscate', $this->goodscate);
        $this->type = D('Keyword')->fetchAll();
        $this->assign('types', $this->type);
        $goods = cookie('goods');
        $this->assign('cartnum', (int) array_sum($goods));
		$check_user_addr = D('Paddress')->where(array('user_id'=>$this->uid))->find();
		$this->assign('check_user_addr', $check_user_addr);
		$this->assign('host', __HOST__);
		
    }
    
	 public function goods(){
         	$goods_spec = cookie('goods_spec');
        	if (!empty($goods_spec)) {
            $spec_keys = array_keys($goods_spec);
            $spec_arr = $this -> spec_to_arr($goods_spec);           
            $goods_ids= $this->get_goods_ids($goods_spec);
            foreach($goods_ids as $k=> $v){
            	$cart_goods[] = D('Goods')->itemsByIds($v);
            } 
            foreach ($cart_goods as $k => $val) {
            	foreach($val as $key => $det){
                $cart_goods[$k][$key]['buy_num'] = $spec_arr[$k][2];//购买数量
                 $cart_goods[$k][$key]['sky'] =  $spec_arr[$k][1];
                   $cart_goods[$k][$key]['goods_spec'] = $spec_keys[$k];
                $shop_ids[$det['shop_id']] = $det['shop_id'];
                if(!empty($cart_goods[$k][$key][sky])){
	              		$spt=D('TpSpecGoodsPrice')->where("`key`='{$cart_goods[$k][$key][sky]}' and `goods_id`='{$cart_goods[$k][$key][goods_id]}'")->find();	              
	              		$cart_goods[$k][$key]['mall_price']=$spt['price']*100;
	              		$cart_goods[$k][$key]['key_name']=$spt['key_name'];
         			}
         		}
         		
            }
            
            $this->assign('cart_shops', D('Shop')->itemsByIds($shop_ids));
            $this->assign('cart_goods', $cart_goods);
        }
        $this->display();
    }
       
    public function main()
    {
        $mcates = $this->_CONFIG['mall'];
        $cates = array();
        for ($i = 1; $i <= count($mcates); $i += 1) {
            if ($i % 2 == 0) {
                $ii = $i / 2;
                if (!empty($mcates['ming' . $ii]) && !empty($mcates['dian' . $ii])) {
                    $cates[$mcates['dian' . $ii]] = array('cate_name' => $mcates['ming' . $ii], 'cate_id' => $mcates['dian' . $ii]);
                }
            }
        }
        $this->assign('cates', $cates);
        $channels = $this->template_setting['setting']['floor'];
        $goods = array();
        foreach ($channels as $k => $val) {
            $cate_ids = D('Goodscate')->getChildren($val['value'], false);
            $cates = D('Goodscate')->where(array('cate_id' => array('IN', $cate_ids)))->order(array('orderby' => 'asc'))->select();
            $channels[$k]['cates'] = $cates;
            foreach ($cate_ids as $cate) {
                $goods[$cate] = D('Goods')->where(array('cate_id' => $cate, 'audit' => 1, 'closed' => 0))->order(array('orderby' => 'asc', 'goods_id' => 'desc'))->limit(0, 8)->select();
            }
        }
        $this->assign('goods', $goods);
        //二开开始，注意是胡乱写的，别介意，自己可以根据自己的需要些
        $itemss = D('Cloudgoods')->where(array('closed' => 0, 'audit' => 1, 'city_id' => $this->city_id))->order(array('goods_id' => 'asc'))->limit(0, 10)->select();
        $this->assign('itemss', $itemss);
        $new = D('Goods')->where(array('closed' => 0, 'audit' => 1, 'city_id' => $this->city_id))->order(array('sold_num' => 'asc'))->limit(0, 3)->select();
        $this->assign('new', $new);
        $tuijian = D('Goods')->where(array('closed' => 0, 'audit' => 1, 'city_id' => $this->city_id))->order(array('views' => 'asc'))->limit(0, 5)->select();
        $this->assign('tuijian', $tuijian);
        $xianshi = D('Goods')->where(array('closed' => 0, 'audit' => 1, 'city_id' => $this->city_id))->order(array('create_time' => 'asc'))->limit(0, 4)->select();
        $this->assign('xianshi', $xianshi);
        $like = D('Goods')->where(array('closed' => 0, 'audit' => 1, 'city_id' => $this->city_id))->order(array('goods_id' => 'asc'))->limit(0, 8)->select();
        $this->assign('like', $like);
        $this->assign('channels', $channels);
        $this->display();
    }
    public function index(){
        $Goods = D('Goods');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'audit' => 1, 'city_id' => $this->city_id, 'end_date' => array('EGT', TODAY));
        $linkArr = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
            $linkArr['keywrod'] = $map['title'];
        }
        $cat = (int) $this->_param('cat');
        $cate_id = (int) $this->_param('cate_id');
        if ($cat) {
            if (!empty($cate_id)) {
                $map['cate_id'] = $cate_id;
                $this->seodatas['cate_name'] = $this->goodscate[$cate_id]['cate_name'];
                $linkArr['cat'] = $cat;
                $linkArr['cate_id'] = $cate_id;
            } else {
                $catids = D('Goodscate')->getChildren($cat);
                if (!empty($catids)) {
                    $map['cate_id'] = array('IN', $catids);
                }
                $this->seodatas['cate_name'] = $this->goodscate[$cat]['cate_name'];
                $linkArr['cat'] = $cat;
            }
            if (!empty($cate_id)) {
                if ($cate = $this->goodscate[$cate_id]) {
                    $map['cate_id'] = $cate_id;
                    $this->seodatas['cate_name'] = $this->goodscate[$cate_id]['cate_name'];
                    $linkArr['cat'] = $cat;
                    $linkArr['cates'] = $cates;
                    $linkArr['cate_id'] = $cate_id;
                    $this->assign('cate', $cate);
                    $attrs = D('Goodscateattr')->getAttrs($cate_id);
                    for ($i = 1; $i <= 5; $i++) {
                        if (!empty($cate['select' . $i])) {
                            $s[$i] = (int) $this->_param('s' . $i);
                            if ($attrs['select' . $i][$s[$i]]) {
                                $map['select' . $i] = $s[$i];
                                $this->assign('s' . $i, $s[$i]);
                                $linkArr['s' . $i] = $s[$i];
                            }
                        }
                    }
                    $this->assign('attrs', $attrs);
                }
            }
        }
        $this->assign('cat', $cat);
        $this->assign('cates', $cates);
        $this->assign('cate_id', $cate_id);
        $area = (int) $this->_param('area');
        if ($area) {
            $map['area_id'] = $area;
            $this->seodatas['area_name'] = $this->areas[$area]['area_name'];
            $linkArr['area'] = $area;
        }
        $this->assign('area_id', $area);
        $business = (int) $this->_param('business');
        if ($business) {
            $map['business_id'] = $business;
            $this->seodatas['business_name'] = $this->bizs[$business]['business_name'];
            $linkArr['business'] = $business;
        }
        $this->assign('business_id', $business);
        $order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 's':
                $orderby = array('sold_num' => 'desc');
                $linkArr['order'] = $order;
                break;
            case 'p':
                $orderby = array('mall_price' => 'asc');
                $linkArr['order'] = $order;
                break;
            case 'v':
                $orderby = array('views' => 'asc');
                $linkArr['order'] = $order;
                break;
            default:
                $orderby = array('orderby' => 'asc', 'sold_num' => 'desc', 'goods_id' => 'desc');
                $linkArr['order'] = $order;
                break;
        }
        $this->assign('order', $order);
        $count = $Goods->where($map)->count();
        $Page = new Page($count, 20);
        $show = $Page->show();
        $list = $Goods->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) {
            $list[$k] = $Goods->_format($v);
        }
        $selArr = $linkArr;
        foreach ($selArr as $k => $val) {
            if ($k == 'order') {
                unset($selArr[$k]);
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('selArr', $selArr);
        $this->assign('linkArr', $linkArr);
        $this->assign('host', __HOST__);
        $this->display('index');
    }
    public function shoplist()
    {
        $Shop = D('Shop');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'audit' => 1, 'is_mall' => 1);
        $count = $Shop->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Shop->where($map)->order(array('orderby' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function shop()
    {
        if (!($shop_id = (int) $this->_param('shop_id'))) {
            $this->error('该商家不存在');
        }
        if (!($shop = D('Shop')->find($shop_id))) {
            $this->error('该商家不存在');
        }
        if (!$shop['is_mall']) {
            $this->error('该商家不存在');
        }
        $this->assign('shop_id', $shop_id);
        $this->assign('shop', $shop);
        $this->assign('details', D('Shopdetails')->find($shop_id));
        $this->assign('cates', D('Goodsshopcate')->where(array('shop_id' => $shop_id))->select());
        D('Shop')->updateCount($shop_id, 'view');
        $this->seodatas['shop_name'] = $shop['shop_name'];
        $Goods = D('Goods');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $shop_id, 'audit' => 1);
        $linkArr = array('shop_id' => $shop_id);
        $cat = (int) $this->_param('cat');
        if ($cat) {
            $map['shopcate_id'] = $cat;
            $linkArr['cat'] = $cat;
        }
        $this->assign('cat', $cat);
        $linkArr['order'] = $order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 's':
                $orderby = array('sold_num' => 'desc');
                break;
            case 'p':
                $orderby = array('mall_price' => 'desc');
                break;
            case 'v':
                $orderby = array('views' => 'desc');
            default:
                $order = 'd';
                $orderby = array('orderby' => 'asc', 'sold_num' => 'desc', 'goods_id' => 'desc');
                break;
        }
        $this->assign('order', $order);
        $count = $Goods->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Goods->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) {
            $list[$k] = $Goods->_format($v);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('linkArr', $linkArr);
        $this->display();
    }
    public function cartdel(){
        $goods_spec = $_POST['goods_spec'];
        $goods_spec_all = cookie('goods_spec');
        if (isset($goods_spec_all[$goods_spec])) {
            unset($goods_spec_all[$goods_spec]);
            cookie('goods_spec', $goods_spec_all, 604800);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功'));
        } else {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '删除失败'));
        }
    }
    public function cart(){
        $order_id = (int) $_GET['order_id'];
        if (!empty($order_id)) {
            $order = D('Order')->find($order_id);
            if ($order['user_id'] != $this->uid) {
                $this->error("不能修改别人的订单!", U('mall/index'));
            }
            if ($order['status'] != 0) {
                $this->error("该订单不能修改!", U('mall/index'));
            }
            $order_goods = D('Ordergoods')->where(array('order_id' => $order_id))->select();
            $goods_ids = $nums = array();
            foreach ($order_goods as $key => $v) {
                $goods_ids[][$v['goods_id']] = $v['goods_id'];
                $nums[][$v['goods_id']] = $v['num'];  
            }
            
            foreach($goods_ids as $k => $v){
            	$cart_goods[] = D('Goods')->itemsByIds($v);
            }
            $shop_ids = array();
            foreach ($cart_goods as $k => $val) {
            	foreach($val as $key => $det){
                $cart_goods[$k][$key]['buy_num'] = $nums[$k][$key];
                $shop_ids[$val['shop_id']] = $val['shop_id'];
                $cart_goods[$k][$key]['key_name'] =$order_goods[$k][key_name];
                $cart_goods[$k][$key]['sky'] = $order_goods[$k][key];
                $cart_goods[$k][$key]['goods_spec'] = $key.'|'.$cart_goods[$k][$key]['sky'];
                if(!empty($cart_goods[$k][$key][sky])){
	              		$spt=D('TpSpecGoodsPrice')->where("`key`='{$cart_goods[$k][$key][sky]}' and `goods_id`='{$cart_goods[$k][$key][goods_id]}'")->find();	              
	              		$cart_goods[$k][$key]['mall_price']=$spt['price']*100;
	              		$cart_goods[$k][$key]['key_name']=$spt['key_name'];
         			}                                                  
              }
                
            }
            $this->assign('order_id', $order_id);
            $this->assign('cart_shops', D('Shop')->itemsByIds($shop_ids));
            $this->assign('cart_goods', $cart_goods);
            $this->display('change_cart');
        } else {
            $goods_spec = cookie('goods_spec');
              if (empty($goods_spec)) {
                $this->error("亲还没有选购产品呢!", U('mall/index'));
            }         
            $spec_keys = array_keys($goods_spec);
            $spec_arr = $this -> spec_to_arr($goods_spec);           
            $goods_ids= $this->get_goods_ids($goods_spec);

            foreach($goods_ids as $k=> $v){
            	            $cart_goods[] = D('Goods')->itemsByIds($v);
            }
                       
            $shop_ids = array();
            foreach ($cart_goods as $k => $val) {
            	foreach($val as $key => $det){
                $cart_goods[$k][$key]['buy_num'] = $spec_arr[$k][2];//购买数量
                 $cart_goods[$k][$key]['sky'] =  $spec_arr[$k][1];
                   $cart_goods[$k][$key]['goods_spec'] = $spec_keys[$k];
                $shop_ids[$det['shop_id']] = $det['shop_id'];
                if(!empty($cart_goods[$k][$key][sky])){
	              		$spt=D('TpSpecGoodsPrice')->where("`key`='{$cart_goods[$k][$key][sky]}' and `goods_id`='{$cart_goods[$k][$key][goods_id]}'")->find();	              
	              		$cart_goods[$k][$key]['mall_price']=$spt['price']*100;
	              		$cart_goods[$k][$key]['key_name']=$spt['key_name'];
         			}
         		}
         		
            }
               
  		
            $this->assign('cart_shops', D('Shop')->itemsByIds($shop_ids));
            $this->assign('cart_goods', $cart_goods);
            $this->display();
        }
    }

    private function spec_to_arr($goods_spec){
    	    $spec_key = array_keys($goods_spec);
            foreach($spec_key as $k=> $v){
            	$spec_arr[$k] = explode('|',$v); 
            	$spec_arr[$k][]= $goods_spec[$v];
            }
            return $spec_arr;
            
    }
    
    //获取所有的 每一条记录 组成的good_ids
    private function get_goods_ids($goods_spec){
    		$spec_arr = $this -> spec_to_arr($goods_spec);
    		foreach($spec_arr as $k => $v){
    			$goods_ids[] = $v[0];
    		}		
    		return $goods_ids;
    }
  
    public function ajaxcart(){
           $goods_spec = cookie('goods_spec');
           if (!empty($goods_spec)) {
            $spec_keys = array_keys($goods_spec);
            $spec_arr = $this -> spec_to_arr($goods_spec);           
            $goods_ids= $this->get_goods_ids($goods_spec);

            foreach($goods_ids as $k=> $v){
            	            $cart_goods[] = D('Goods')->itemsByIds($v);
            } 
            foreach ($cart_goods as $k => $val) {
            	foreach($val as $key => $det){
                $cart_goods[$k][$key]['buy_num'] = $spec_arr[$k][2];
                 $cart_goods[$k][$key]['sky'] =  $spec_arr[$k][1];
                   $cart_goods[$k][$key]['goods_spec'] = $spec_keys[$k];
                $shop_ids[$det['shop_id']] = $det['shop_id'];
                if(!empty($cart_goods[$k][$key][sky])){
	              		$spt=D('TpSpecGoodsPrice')->where("`key`='{$cart_goods[$k][$key][sky]}' and `goods_id`='{$cart_goods[$k][$key][goods_id]}'")->find();	              
	              		$cart_goods[$k][$key]['mall_price']=$spt['price']*100;
	              		$cart_goods[$k][$key]['key_name']=$spt['key_name'];
         			}
         		}
         		
            }
            $this->assign('cart_shops', D('Shop')->itemsByIds($shop_ids));
            $this->assign('cart_goods', $cart_goods);
        }
        $this->display();
    }
    public function ajaxcartlist(){   
         $goods_spec = cookie('goods_spec');
        if (!empty($goods_spec)) {     
             $spec_keys = array_keys($goods_spec);
            $spec_arr = $this -> spec_to_arr($goods_spec);           
            $goods_ids= $this->get_goods_ids($goods_spec);
            foreach($goods_ids as $k=> $v){
            	            $cart_goods[] = D('Goods')->itemsByIds($v);
            }      
            foreach ($cart_goods as $k => $val) {
            	foreach($val as $key => $det){
                $cart_goods[$k][$key]['buy_num'] = $spec_arr[$k][2];
                 $cart_goods[$k][$key]['sky'] =  $spec_arr[$k][1];
                   $cart_goods[$k][$key]['goods_spec'] = $spec_keys[$k];
                $shop_ids[$det['shop_id']] = $det['shop_id'];
                if(!empty($cart_goods[$k][$key][sky])){
	              		$spt=D('TpSpecGoodsPrice')->where("`key`='{$cart_goods[$k][$key][sky]}' and `goods_id`='{$cart_goods[$k][$key][goods_id]}'")->find();	              
	              		$cart_goods[$k][$key]['mall_price']=$spt['price']*100;
	              		$cart_goods[$k][$key]['key_name']=$spt['key_name'];
         			}
         		}       		
            }   
            $this->assign('cart_shops', D('Shop')->itemsByIds($shop_ids));
            $this->assign('cart_goods', $cart_goods);
        }
        $this->display();
    }
    
    
    public function detail($goods_id){
        $goods_id = (int) $goods_id;
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->error('您访问的产品不存在！');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->error('您访问的产品不存在！');
        } 
        $shop = D('Shop')->find($detail['shop_id']);
        if (!($favo = D('Shopfavorites')->where(array('user_id' => $this->uid, 'shop_id' => $shop_id))->find())) {
            $shop['favo'] = 0;
        } else {
            $shop['favo'] = 1;
        }
        $this->assign('shop', $shop);
        $shop_id = $detail['shop_id'];
        $this->assign('ex', D('Shopdetails')->find($shop_id));
        $cate_id = (int) $detail['cate_id'];
        $cookie = unserialize($_COOKIE['iLikegoods']);
        $cookie[] = $cate_id;
        $cookie = array_flip(array_flip($cookie));
        $cate_arr = serialize($cookie);
        cookie('iLikegoods', $cate_arr, 86400);
        $like_where = array();
        $like_where['cate_id'] = array('in', $cookie);
        $like_where['audit'] = 1;
        $like_where['closed'] = 0;
        $like = D('Goods')->where($like_where)->order('rand()')->limit(5)->select();
        $this->assign('like', $like);
        $goodsdianping = D('Goodsdianping');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'goods_id' => $goods_id, 'show_date' => array('ELT', TODAY));
        $count = $goodsdianping->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $list = $goodsdianping->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $dianping_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($order_ids)) {
            $this->assign('goodsdianpingpics', D('Goodsdianpingpics')->where(array('order_id' => array('IN', $order_ids)))->select());
        }
        $viewArr = cookie('viewgoods');
        $cooarr = array('goods_id' => $goods_id, 'title' => $detail['title'], 'price' => $detail['price'], 'mall_price' => $detail['mall_price'], 'photo' => $detail['photo']);
        if (!$viewArr) {
            cookie('viewgoods', serialize($cooarr[$detail['goods_id']]));
        } else {
            $viewArr = unserialize($viewArr);
            if (count($viewArr) == 5) {
                $arr = array_pop($viewArr);
                unset($arr);
            }
            if (!isset($viewArr[$detail['goods_id']])) {
                $viewArr[$detail['goods_id']] = $cooarr;
                cookie('viewgoods', serialize($viewArr));
            }
        }
        $viewgoods = unserialize(cookie('viewgoods'));
        $viewgoods = array_reverse($viewgoods, TRUE);
        $this->assign('viewgoods', $viewgoods);
        $this->assign('totalnum', $count);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $maps = array('closed' => 0, 'audit' => 1, 'shop_id' => $detail['shop_id']);
        $lists = D('Shopbranch')->where($maps)->order(array('orderby' => 'asc'))->select();
        $shop_arr = array('name' => '总店', 'score' => $shop['score'], 'score_num' => $shop['score_num'], 'lng' => $shop['lng'], 'lat' => $shop['lat'], 'telephone' => $shop['tel'], 'addr' => $shop['addr']);
        if (!empty($lists)) {
            array_unshift($lists, $shop_arr);
        } else {
            $lists[] = $shop_arr;
        }
        $counts = count($lists);
        if ($counts % 5 == 0) {
            $num = $counts / 5;
        } else {
            $num = (int) ($counts / 5) + 1;
        }
  
        //商品多属性      
        $filter_spec = $this->get_spec($goods_id); //获取商品规格参数        
        $goodsss=M('Goods')->find($goods_id);
        $goodsss[mall_price]=$goodsss[mall_price]/100;
        $spec_goods_price  = M('TpSpecGoodsPrice')->where("goods_id = $goods_id")->getField("key,price,store_count"); // 规格 对应 价格 库存表
        if($spec_goods_price != null){
        	$this->assign('spec_goods_price', json_encode($spec_goods_price,true)); // 规格 对应 价格 库存表
        }
               $yh=$goodsss[yh];
                if($yh!= '0'){
                        $yh=explode(PHP_EOL,$yh);
                            for ($i=0; $i < count($yh)-1;$i++){ 
                               $yh[s][]=explode(',',$yh[$i]);                   
                            }
                    foreach($yh[s] as $k2=>$vo){                 
                        foreach($vo as $k2=>$v2){
                            $rs[$k2][] = $v2;    
                        }
                     }
                    $goodsss['zks'][]=$rs[0];
                    $goodsss['zks'][]=$rs[1];
                }
        
         $this->assign('filter_spec',$filter_spec);//规格参数
         $this->assign('goods', $goodsss);      
        $this->assign('count', $counts);
        $this->assign('totalnums', $num);
        $this->assign('lists', $lists);
        $goodsids = $detail['cate_id'];
        $this->assign('goodsids', $goodsids);
        $this->seodatas['cate_name'] = $this->goodscate[$detail['cate_id']]['cate_name'];
        $this->seodatas['cate_area'] = $this->areas[$detail['area_id']]['area_name'];
        $this->seodatas['cate_business'] = $this->bizs[$detail['business_id']]['business_name'];
        $this->seodatas['title'] = $detail['title'];
        $this->seodatas['intro'] = $detail['intro'];
        $this->seodatas['shop_name'] = $shop['shop_name'];
        D('Goods')->updateCount($goods_id, 'views');
        $this->assign('pics', D('Goodsphoto')->getPics($detail['goods_id']));
        $this->cates = D('Goodscate')->fetchAll();
        $this->assign('cates', $this->cates);
        $this->assign('cate', $this->cates[$detail['cate_id']]);
        $this->assign('detail', $detail);
        $attrs = D('Goodscateattr')->getAttrs($detail['cate_id']);
        $this->assign('attrs', $attrs);
        $userrank = D('user_rank')->select();
        $this->assign('userrank', $userrank);
        $this->assign('pics', D('Goodsphoto')->getPics($detail['goods_id']));
        $this->assign('host', __HOST__);
        $this->assign('height_num', 675);
        $this->display();
    }
    
  
   //获取商品规格
   public function get_spec($goods_id){
        //商品规格 价钱 库存表 找出 所有 规格项id
        $keys = M('TpSpecGoodsPrice')->where("goods_id = $goods_id")->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        $filter_spec = array();
       
        if($keys){
            //$specImage =  M('TpSpecImage')->where("goods_id = $goods_id and src != '' ")->getField("spec_image_id,src");// 规格对应的 图片表， 例如颜色
            $keys = str_replace('_',',',$keys);
            $sql  = "SELECT a.name,a.order,b.* FROM __PREFIX__tp_spec AS a INNER JOIN __PREFIX__tp_spec_item AS b ON a.id = b.spec_id WHERE b.id IN($keys) ORDER BY a.order";
            $filter_spec2 = M()->query($sql);
            foreach($filter_spec2 as $key => $val){
                $filter_spec[$val['name']][] = array(
                        'item_id'=> $val['id'],
                        'item'=> $val['item'],
                );
            }
        }  
        
        return $filter_spec;
   }
     
    
    
    public function emptygoods(){
        cookie('viewgoods', null);
        $this->ajaxReturn(array('status' => 'success', 'msg' => '清空成功'));
    }
    public function get_like() {
        if (IS_AJAX) {
            $cookie = unserialize($_COOKIE['iLikegoods']);
            $like_where = array();
            $like_where['cate_id'] = array('in', $cookie);
            $likes = D('Goods')->where($like_where)->order('rand()')->limit(5)->select();
            if ($likes) {
                $this->ajaxReturn(array('status' => 'success', 'likes' => $likes));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'message' => '换一换失败！'));
            }
        }
    }
    public function cartadd(){
        $goods_id = (int) $this->_param('goods_id');
        if (empty($goods_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请选择产品'));
        }
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商品不存在'));
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商品不存在'));
        }
        if ($detail['end_date'] < TODAY) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商品已经过期，暂时不能购买'));
        }
        if ($detail['num'] <= 0) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！没有库存了！'));
        }
        $goods_spec= cookie('goods_spec');

        $num = (int) $this->_get('num');
         $spec_key =  $this->_get('spec_key');
        
        if (empty($num) || $num <= 0) {
            $num = 1;
        }
        if ($num > 99) {
            $num = 99;
        }
        //加入购物车时候检查规格库存  如果不走这里他会走下面的
        $is_spec_stock = is_spec_stock($goods_id,$spec_key,$num);
        if(!$is_spec_stock){
        	 $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该规格库存不足了，少买点吧！'));
        }
        if ($detail['num'] <= $num) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该商品只剩' . $detail['num'] . '件了，少买点吧！'));
        }
        $goods_spec_v = $goods_id.'|'.$spec_key; //重新组合那个 商品id和那个啥规格键
            if (isset($goods_spec[$goods_spec_v])) {
	            $goods_spec[$goods_spec_v] += $num;
	        } else {
	            $goods_spec[$goods_spec_v] = $num;
	        }
        cookie('goods_spec', $goods_spec, 604800);
        $this->ajaxReturn(array('status' => 'success', 'msg' => '添加购物车成功'));
    }   
    
    public function cartadd3(){
        $goods_id = (int) $this->_param('goods_id');
        if (empty($goods_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请选择产品'));
        }
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商品不存在'));
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商品不存在'));
        }
        if ($detail['end_date'] < TODAY) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商品已经过期，暂时不能购买'));
        }
        if ($detail['num'] <= 0) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！没有库存了！'));
        }
       
         $goods_spec= cookie('goods_spec');
        $num = (int) $this->_get('num');
         $spec_key =  $this->_get('spec_key');
        
        if (empty($num) || $num <= 0) {
            $num = 1;
        }
        if ($num > 99) {
            $num = 99;
        }
        //加入购物车时候检查规格库存  如果不走这里他会走下面的
        $is_spec_stock = is_spec_stock($goods_id,$spec_key,$num);
        if(!$is_spec_stock){
        	 $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该规格库存不足了，少买点吧！'));
         }
        if ($detail['num'] <= $num) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该商品只剩' . $detail['num'] . '件了，少买点吧！'));
        }
        $goods_spec_v = $goods_id.'|'.$spec_key; //重新组合那个 商品id和那个啥规格键
            if (isset($goods_spec[$goods_spec_v])) {
	            $goods_spec[$goods_spec_v] += $num;
	        } else {
	            $goods_spec[$goods_spec_v] = $num;
	        }
        $key[$goods_id]=$spec_key;//规格
        cookie('goods_spec', $goods_spec, 604800);
        $this->ajaxReturn(array('status' => 'success', 'msg' => '添加购物车成功'));
        die('0');
    }
    public function cartadd2($goods_id){
        $goods_id = (int) $goods_id;
        if (empty($goods_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请选择产品'));
        }
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商品不存在'));
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商品不存在'));
        }
        if ($detail['end_date'] < TODAY) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该商品已经过期，暂时不能购买'));
        }
        if ($detail['num'] <= 0) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！没有库存了！'));
        }
       $goods_spec= cookie('goods_spec');

        $num = (int) $this->_get('num');
         $spec_key =  $this->_get('spec_key');
        
        if (empty($num) || $num <= 0) {
            $num = 1;
        }
        if ($num > 99) {
            $num = 99;
        }
        //加入购物车时候检查规格库存  如果不走这里他会走下面的
        $is_spec_stock = is_spec_stock($goods_id,$spec_key,$num);
        if(!$is_spec_stock){
        	    $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该规格库存不足了，少买点吧！'));
        	}
        if ($detail['num'] <= $num) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该商品只剩' . $detail['num'] . '件了，少买点吧！'));
        }
        $goods_spec_v = $goods_id.'|'.$spec_key; //重新组合那个 商品id和那个啥规格键
            if (isset($goods_spec[$goods_spec_v])) {
	            $goods_spec[$goods_spec_v] += $num;
	        } else {
	            $goods_spec[$goods_spec_v] = $num;
	        }
        $key[$goods_id]=$spec_key;//规格
        cookie('goods_spec', $goods_spec, 604800);
        $this->ajaxReturn(array('status' => 'success', 'msg' => '加入购物车成功,正在跳转到购物车', 'url' => U('mall/cart')));
    }
    
    public function neworder(){
        $goods = $this->_get('goods');
        $goods = explode(',', $goods);
        if (empty($goods)) {
            $this->error('亲购买点吧');
        }
        $datas = array();
        foreach ($goods as $val) {
            $good = explode('-', $val);
            $good[1] = (int) $good[1];
            if (empty($good[0]) || empty($good[1])) {
                $this->error('亲购买点吧');
            }
            if ($good[1] > 99 || $good[1] < 0) {
                $this->error('本店不支持批发');
            }
            $datas[$good[0]] = $good[1];
        }
        cookie('goods', $datas, 604800);
        header("Location:" . U('mall/cart'));
        die;
    }
    //修改订单要重写
    public function order_change() {
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        $order_id = (int) $_POST['order_id'];
        $order = D('Order')->find($order_id);
        if ($order['user_id'] != $this->uid) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '不能修改别人的订单'));
        }
        if ($order['status'] != 0) {
            $this->error("该订单不能修改!", U('mall/index'));
        }
        $num = $this->_post('num', false);
        $goods_ids = array();
        foreach ($num as $k => $val) {
            $val = (int) $val;
            if (empty($val)) {
                unset($num[$k]);
            } elseif ($val < 1 || $val > 99) {
                unset($num[$k]);
            } else {
                  $spec_keys[]=$k;
            	$spec_arr[] = explode('|',$k); 
            	$spec_temp = explode('|',$k); 
                $goods_ids[$k][] = (int)$spec_temp[0];
            }
        }
        if (empty($goods_ids)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '很抱歉请填写正确的购买数量'));
        }
        
 	 foreach($goods_ids as $v){
        $goods[] = D('Goods')->itemsByIds($v); 
      }
      
        foreach ($goods as $k => $v) {
        	foreach($v as $key => $val){
            if ($val['closed'] != 0 || $val['audit'] != 1 || $val['end_date'] < TODAY) {
                unset($goods[$key]);
            }         
	        //把这个商品的规格存进数组
            $goods[$k][$key][sky]=$spec_arr[$k][1]; //把后面的规格存进来
            $goods[$k][$key]['goods_spec'] = $spec_keys[$k];//整个存一下
            if(!empty($goods[$k][$key][sky])){
            //改变价格
            $spt=D('TpSpecGoodsPrice')->where("`key`='{$goods[$k][$key][sky]}' and `goods_id`='{$goods[$k][$key][goods_id]}'")->find();	              
			$goods[$k][$key]['mall_price']=$spt['price']*100;
			$goods[$k][$key]['key_name']=$spt['key_name'];//建的中文名
            	}
            }
        }
        
        if (empty($goods)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '很抱歉，您提交的产品暂时不能购买！'));
        }
        
        //下单前检查库存
        foreach ($goods as $val) {
		$val = reset($val); //二维数组 取第一个  
		 //加入购物车时候检查规格库存  如果不走这里他会走下面的
        $is_spec_stock = is_spec_stock($val[goods_id],$val[sky],$num[$val['goods_spec']]);
       	if(!$is_spec_stock){
       	 	$spec_one_num =  get_one_spec_stock($val[goods_id],$val[sky]);
       	 	$msg = '亲！规格为<' . $val['key_name']. '>的商品库存不够了,只剩' .  $spec_one_num . '件了！';	
       	    $this->ajaxReturn(array('status' => 'error', 'msg' => $msg));
        }
		
	    if ($val['num'] < $num[$val['goods_spec']]) {
	      	$msg ='亲！商品<' . $val['title'] . '>库存不够了,只剩' . $val['num'] . '件了！';
        	$this->ajaxReturn(array('status' => 'error', 'msg' => $msg));
	    }
	   }
             
        
        $tprice = 0;
        $ip = get_client_ip();
        $ordergoods = $total_price = array();
        $can_use_integral = 0;
        foreach ($goods as $val) {
        $val = reset($val); 
		//二维数组 取第一个  
		//二次开发的 其他人可能看不懂 之前是  $num[$val['goods_id']]  这个我前面那个num已经改过了 但是下面的代码不想改了 所以统一赋值一下
		//前面已经通过这个规格的键值来重新传了
		$num[$val['goods_id']] = $num[$val['goods_spec']];
            $price = $val['mall_price'] * $num[$val['goods_id']];
            $can_use_integral += $val['use_integral'] * $num[$val['goods_id']];
            $js_price = $val['settlement_price'] * $num[$val['goods_id']];
            $tprice += $price;
            $ordergoods = array(
				'num' => $num[$val['goods_id']], 
				'goods_id' => $val['goods_id'], 
				'price' => $val['mall_price'], 
				'total_price' => $price, 
				'js_price' => $js_price, 
				'update_time' => NOW_TIME, 
				'update_ip' => $ip
			);
            D('Ordergoods')->where(array('order_id' => $order_id, 'goods_id' => $val['goods_id'],'key'=> $val['sky']))->setField($ordergoods);
            //忽略报错
        }
        if (false !== D('Order')->save(array(
			'order_id' => $order_id, 
			'can_use_integral' => $can_use_integral, 
			'total_price' => $tprice, 
			'update_time' => NOW_TIME, 
			'update_ip' => $ip
		))) {
            $this->ajaxReturn(array('status' => 'success', 'msg' => '成功修改订单，正在跳转到支付页面', 'url' => U('mall/pay', array('order_id' => $order_id))));
        } else {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '修改订单失败'));
        }
    }
    public function order(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $num = $this->_post('num', false);
        $goods_ids = array();
        foreach ($num as $k => $val) {
            $val = (int) $val;
            if (empty($val)) {
                unset($num[$k]);//销毁指定变量
            } elseif ($val < 1 || $val > 99) {
                unset($num[$k]);
            } else {
            	 $spec_keys[]=$k;
            	$spec_arr[] = explode('|',$k); 
            	$spec_temp = explode('|',$k); 
                $goods_ids[$k][] = (int)$spec_temp[0];
            }
        }
        if (empty($goods_ids)) {
            $this->baoError('很抱歉请填写正确的购买数量');
        }
	 foreach($goods_ids as $v){
        $goods[] = D('Goods')->itemsByIds($v); 
      }
        foreach ($goods as $k => $v) {
        	foreach($v as $key => $val){
            if ($val['closed'] != 0 || $val['audit'] != 1 || $val['end_date'] < TODAY) {
                unset($goods[$key]);
            }
            //把这个商品的规格存进数组
            $goods[$k][$key][sky]=$spec_arr[$k][1]; //把后面的规格存进来 148_150
             $goods[$k][$key]['goods_spec'] = $spec_keys[$k];//整个存一下
            if(!empty($goods[$k][$key][sky])){
            //改变价格
            $spt=D('TpSpecGoodsPrice')->where("`key`='{$goods[$k][$key][sky]}' and `goods_id`='{$goods[$k][$key][goods_id]}'")->find();	              
			$goods[$k][$key]['mall_price']=$spt['price']*100;
			$goods[$k][$key]['key_name']=$spt['key_name'];//建的中文名
            	}
            }
        }
        
        if (empty($goods)) {
            $this->baoError('很抱歉，您提交的产品暂时不能购买！');
        }
         //下单前检查库存
        foreach ($goods as $val) {
		$val = reset($val); //二维数组 取第一个  
		 //加入购物车时候检查规格库存  如果不走这里他会走下面的
         $is_spec_stock = is_spec_stock($val[goods_id],$val[sky],$num[$val['goods_spec']]);
       	 if(!$is_spec_stock){
       	 	$spec_one_num =  get_one_spec_stock($val[goods_id],$val[sky]);
        	    $this->baoError('亲！规格为<' . $val['key_name']. '>的商品库存不够了,只剩' . $spec_one_num . '件了！');
        	}
		
	        if ($val['num'] < $num[$val['goods_spec']]) {
	            $this->baoError('亲！商品<' . $val['title'] . '>库存不够了,只剩' . $val['num'] . '件了！');
	        }
	   }
	   
        $tprice = 0;
        $ip = get_client_ip();
        $ordergoods = $total_price = array();
        $canuserintegral = array();
        foreach ($goods as $val) {
			$val = reset($val); 
			//二维数组 取第一个  
			//二次开发的 其他人可能看不懂 之前是  $num[$val['goods_id']]  这个我前面那个num已经改过了 但是下面的代码不想改了 所以统一赋值一下
			//前面已经通过这个规格的键值来重新传了
			$num[$val['goods_id']] = $num[$val['goods_spec']];
				$price = $val['mall_price'] * $num[$val['goods_id']];
				$dan_price = $val['mall_price'] * $num[$val['goods_id']];//总费用
				$use_price = $val['use_integral'] * $num[$val['goods_id']];//积分抵现多少钱
				//返回单个商品运费
				$order_express_price = D('Ordergoods')->calculation_express_price($this->uid, $val['kuaidi_id'], $num[$val['goods_id']], $val['goods_id'],1);
				$need_pay = $dan_price - $use_price;//实际支付金额
				$js_price = $val['settlement_price'] * $num[$val['goods_id']];
				$tprice += $price;
				$canuserintegral[$val['shop_id']] += $val['use_integral'] * $num[$val['goods_id']];
				$ordergoods[$val['shop_id']][] = array(
					'goods_id' => $val['goods_id'], 
					'shop_id' => $val['shop_id'], 
					'num' => $num[$val['goods_id']], 
					'kuaidi_id' => $val['kuaidi_id'], 
					'price' => $val['mall_price'], 
					'total_price' => $price, 
					'need_pay' => $need_pay, 
					'js_price' => $js_price, 
					'create_time' => NOW_TIME, 
					'create_ip' => $ip,
					'key'=> $val['sky'],
					'key_name' => $val['key_name']
				);
            $total_price[$val['shop_id']] += $price;
            $express_price[$val['shop_id']] += $order_express_price;//不同商家总运费
        }
        //总订单
        $order = array('user_id' => $this->uid, 'create_time' => NOW_TIME, 'create_ip' => $ip, 'need_pay' => $need_pay, 'goods_id' => $val['goods_id'], 'total_price' => 0);
		
        $defaultAddress = D('Paddress')->defaultAddress($this->uid, $type);//收货地址部分重写
        $order_ids = array();
        foreach ($ordergoods as $k => $val) {
            $shop = D('Shop')->find($k);
            $order['shop_id'] = $k;
            $order['can_use_integral'] = $canuserintegral[$k];
            $order['total_price'] = $total_price[$k];//这里影响的没有减去积分
            $order['express_price'] = $express_price[$k];//写入运费
            $order['address_id'] = $defaultAddress['id'];//写入快递ID
            $order['is_shop'] = (int) $shop['is_pei'];//是否由商家自己配送
			$val[0]['express_price'] = $express_price[$k];//写入运费
			$val[0]['address_id'] = $defaultAddress['id'];//写入快递I
            if ($order_id = D('Order')->add($order)) {
                $order_ids[] = $order_id;
                foreach ($val as $k1 => $val1) {
                    $val1['order_id'] = $order_id;
					print_r($val1);
                    D('Ordergoods')->add($val1);
                }
            }
        }
        cookie('goods_spec', null);// 清空 cookie
        //如果大于1 那么形成一个 支付记录 来合并付款！如果其他条件可以直接去付款
        if (count($order_ids) > 1) {
            $need_pay = D('Order')->useIntegral($this->uid, $order_ids);
            $logs = array(
				'type' => 'goods', 
				'user_id' => $this->uid, 
				'order_id' => 0, 
				'order_ids' => join(',', $order_ids), 
				'code' => '', 
				'need_pay' => $need_pay, 
				'create_time' => NOW_TIME, 
				'create_ip' => get_client_ip(), 
				'is_paid' => 0
			);
			
            $logs['log_id'] = D('Paymentlogs')->add($logs);
            $this->baoJump(U('mall/paycode', array('log_id' => $logs['log_id'])));
        } else {
            $this->baoJump(U('mall/pay', array('order_id' => $order_id, 'address_id' => $defaultAddress['id'])));
            //加入快递参数
        }
    }
    
    //合并付款页面
    public function paycode(){
        $log_id = (int) $this->_get('log_id');
        if (empty($log_id)) {
            $this->error('没有有效支付记录！');
        }
        if (!($detail = D('Paymentlogs')->find($log_id))) {
            $this->error('没有有效的支付记录！');
        }
        if ($detail['is_paid'] != 0 || empty($detail['order_ids']) || !empty($detail['order_id']) || empty($detail['need_pay'])) {
            $this->error('没有有效的支付记录！');
        }
        $order_ids = explode(',', $detail['order_ids']);
        $ordergood = D('Ordergoods')->where(array('order_id' => array('IN', $order_ids)))->select();
        $goods_id = $shop_ids = array();
        foreach ($ordergood as $k => $val) {
            $goods_id[$val['goods_id']] = $val['goods_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('goods', D('Goods')->itemsByIds($goods_id));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('ordergoods', $ordergood);
		//收货地址重写开始
		$addrs = D('Paddress')->pc_paycode_address($log_id,$this->uid);
		$this->assign('useraddr', $addrs);
		//收货地址重写结束
        $this->assign('payment', D('Payment')->getPayments());
        $this->assign('logs', $detail);
		$this->assign('type', paycode); //
        $this->display();
    }
    public function pay(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Order')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
        $ordergood = D('Ordergoods')->where(array('order_id' => $order_id))->select();
         
        $goods_id = $shop_ids = array();
        foreach ($ordergood as $k => $val) {
            $goods_id[$val['goods_id']] = $val['goods_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $goods = D('Goods')->itemsByIds($goods_id);
        $this->assign('goods', $goods);
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('ordergoods', $ordergood);
		//收货地址重写开始
		$addrs = D('Paddress')->pc_pay_address($order['address_id'],$this->uid);
		$this->assign('useraddr', $addrs);
		//收货地址重写结束		
        $provinceList = D('Paddlist')->where(array('level' => 1))->select();//全部省份
        $this->assign('provinceList', $provinceList);
        $this->assign('type', goods); //
        $this->assign('order', $order);
        $this->assign('payment', D('Payment')->getPayments());
        $this->display();
    }
	//合并订单付款逻辑重写
    public function paycode2(){
        //这里是因为原来的是按订单付，这里是合并付款逻辑部分
        $log_id = (int) $this->_get('log_id');
        if (empty($log_id)) {
            $this->error('没有有效支付记录！');
        }
        if (!($detail = D('Paymentlogs')->find($log_id))) {
            $this->error('没有有效的支付记录！');
        }
        if ($detail['is_paid'] != 0 || empty($detail['order_ids']) || !empty($detail['order_id']) || empty($detail['need_pay'])) {
            $this->error('没有有效的支付记录！');
        }
        $order_ids = explode(',', $detail['order_ids']);
        D('Order')->where(array('order_id' => array('IN', $order_ids)))->save(array('addr_id' => $addr_id));
        if (!($code = $this->_post('code'))) {
            $this->baoError('请选择支付方式！');
        }
        if ($code == 'wait') {//如果是货到付款
            D('Order')->save(array('is_daofu' => 1), array('where' => array('order_id' => array('IN', $order_ids))));
            D('Ordergoods')->save(array('is_daofu' => 1), array('where' => array('order_id' => array('IN', $order_ids))));
            D('Sms')->mallTZshop($order_ids);//用户下单通知商家
            $this->goods_mum($order_id);//检测库存
            D('Order')->mallSold($order_ids);//配送接口
            D('Order')->mallPeisong(array($order_ids), 1);//更新配送
			D('Order')->combination_goods_print($order_ids);//万能商城订单打印
            $this->baoSuccess('恭喜您下单成功！', U('members/order/goods'));
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->baoError('该支付方式不存在');
            }
			foreach($order_ids as $v){
				$need_pay = D('Order')->useIntegral($this->uid, array($v));//这个不知道能不能返回
            	D('Order')->where("order_id={$v}")->save(array('need_pay' => $need_pay));//合并付款的时候更新实际付款金额    
			 	$log_need +=$need_pay;
			}
			$detail['need_pay']= $log_need;
            $detail['code'] = $code;
			
            D('Paymentlogs')->save($detail);
            $this->baoJump(U('mall/combine', array('log_id' => $detail['log_id'])));
        }
    }
    public function combine(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $log_id = (int) $this->_get('log_id');
        if (!($detail = D('Paymentlogs')->find($log_id))) {
            $this->error('没有有效的支付记录！');
        }
        if ($detail['is_paid'] != 0 || empty($detail['order_ids']) || !empty($detail['order_id']) || empty($detail['need_pay'])) {
            $this->error('没有有效的支付记录！');
        }
        $url = U('mall/paycode', array('order_id' => $logs['order_id']));
        $this->assign('url', $url);
        $this->assign('button', D('Payment')->getCode($detail));
        $this->assign('logs', $detail);
        $this->assign('types', D('Payment')->getTypes());
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display();
    }
    //这是单独付款执行的
    public function pay2(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
		
		$id = (int) $this->_post('id');
		if (empty($id)) {
            $this->baoError('暂无收货地址');
            die;
        }
		
        $order_id = (int) $this->_get('order_id');
        $order = D('Order')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->baoError('该订单不存在');
            die;
        }
        D('Order')->save(array('addr_id' => $addr_id, 'order_id' => $order_id));
        if (!($code = $this->_post('code'))) {
            $this->baoError('请选择支付方式！');
        }
        $this->goods_mum($order_id);
        //检测库存独家二开
        $uaddr = D('Paddress')->where(array('id' => $order['address_id']))->find();
        if ($code == 'wait') {
            //如果是货到付款
            D('Order')->save(array('order_id' => $order_id, 'is_daofu' => 1));
            D('Ordergoods')->save(array('is_daofu' => 1), array('where' => array('order_id' => $order_id)));
            D('Order')->mallSold($order_id);//更新销量
            D('Order')->mallPeisong(array($order_id), 1);//更新配送接口
			D('Order')->combination_goods_print($order_id);//万能商城订单打印
			D('Sms')->mallTZshop($order_id);//用户下单通知商家
            D('Weixintmpl')->weixin_notice_goods_user($order_id,$this->uid,0);//商城微信通知货到付款
            $this->baoSuccess('恭喜您下单成功！', U('members/order/goods'));
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->baoError('该支付方式不存在');
            }
            $logs = D('Paymentlogs')->getLogsByOrderId('goods', $order_id);
            $need_pay = D('Order')->useIntegral($this->uid, array($order_id));
            //独家再更新
            if (empty($logs)) {
                $logs = array(
					'type' => 'goods', 
					'user_id' => $this->uid, 
					'order_id' => $order_id, 
					'code' => $code, 
					'need_pay' => $need_pay, 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'is_paid' => 0
				);
                $logs['log_id'] = D('Paymentlogs')->add($logs);
            } else {
                $logs['need_pay'] = $need_pay;
                $logs['code'] = $code;
                D('Paymentlogs')->save($logs);
            }
            D('Order')->where("order_id={$order_id}")->save(array('need_pay' => $need_pay));//再更新一次最终的价格，跟日志同步，蜂蜜独创
            D('Weixintmpl')->weixin_notice_goods_user($order_id,$this->uid,1);//商城微信通知
            $this->baoJump(U('payment/payment', array('log_id' => $logs['log_id'])));
        }
    }
    //付款前检测库存
    public function goods_mum($order_id){
        $order_id = (int) $order_id;
        $ordergoods_ids = D('Ordergoods')->where(array('order_id' => $order_id))->select();
        foreach ($ordergoods_ids as $k => $v) {
         $goods_num = D('Goods')->where(array('goods_id' => $v['goods_id']))->find();
          //也得检查下那个多规格的 这里
         $is_spec_stock = is_spec_stock($v[goods_id],$v[key],$v['num']);
       	 if(!$is_spec_stock){
       	 	$spec_one_num =  get_one_spec_stock($v[goods_id],$v[key]);
        	     $this->baoError('亲！规格为<' . $v['key_name']. '>的商品库存不够了,只剩' . $spec_one_num . '件了！');
        	}
            if ($goods_num['num'] < $v['num']) {
                $this->baoSuccess('商品ID' . $v['goods_id'] . '库存不足无法付款', U('home/mall/index'));
            }
        }
        return false;
    }
}
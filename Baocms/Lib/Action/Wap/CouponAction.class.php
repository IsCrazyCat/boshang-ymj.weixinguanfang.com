<?php
class CouponAction extends CommonAction {
	
	public function _initialize() {
        parent::_initialize();

		$shopcates = D('Shopcate')->fetchAll();
	    foreach ($shopcates as $key => $v) {
           if ($v['cate_id']) {
            $catids = D('Shopcate')->getChildren($v['cate_id']);
				if (!empty($catids)) {
					$count = D('Shop')->where(array('cate_id' => array('IN', $catids), 'closed' => 0, 'audit' => 1, 'city_id' => $this->city_id))->count();
				} else {
					$count = D('Shop')->where(array('cate_id' => $cat, 'closed' => 0, 'audit' => 1, 'city_id' => $this->city_id))->count();
				}
        	}
          $shopcates[$key]['count'] = $count;
        }
        $this->assign('shopcates',$shopcates);
		//结束
		
    }

    public function main() {
           $news= (int) $this->_param('news');
         if ($news == 1) {
            $orderby = array('coupon_id' => 'desc');
        }
        if ($news == 2) {
            $orderby = array('downloads' => 'desc');
        }
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $Coupon = D('Coupon');
        $map = array('audit' => 1,'city_id'=>$this->city_id, 'closed' => 0, 'expire_date' => array('EGT', TODAY));
        $list = $Coupon->where($map)->order($orderby)->limit(0,10)->select();
        $shop_ids = array();
        foreach ($list as $key => $v) {
            $shop_ids[$v['shop_id']] = $v['shop_id'];
        }
        $shops = D('Shop')->itemsByIds($shop_ids);
           foreach ($shops as $k => $val) {
            $shops[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $this->assign('list', $list);
        $this->assign('shops', $shops);
        $this->display();
    }

    public function index() {
		
	 $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $cat = (int) $this->_param('cat');
        $this->assign('cat', $cat);
         $order = (int) $this->_param('order');
        $areas = D('Area')->fetchAll();
        $area_id = (int) $this->_param('area_id');
        $this->assign('area_id', $area_id);
        $this->assign('areas', $areas);
        $this->assign('nextpage', LinkTo('coupon/loaddata', array('cat' => $cat, 't' => NOW_TIME, 'area_id' => $area_id, 'order' => $order,  'keyword' => $keyword, 'p' => '0000')));
        $this->display(); // 输出模板
    }

    public function loaddata() {
        $Coupon = D('Coupon');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('audit' => 1,'city_id'=>$this->city_id, 'closed' => 0, 'expire_date' => array('EGT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $area_id = (int) $this->_param('area_id');
        if ($area_id) {
            $map['area_id'] = $area_id;
        }
        $cat = (int) $this->_param('cat');
        if ($cat) {
            $catids = D('Shopcate')->getChildren($cat);
            if (!empty($catids)) {
                $map['cate_id'] = array('IN', $catids);
            } else {
                $map['cate_id'] = $cat;
            }
        }
          $order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 1:
                $orderby = array('downloads' => 'desc');
                break;
            default:
                  $orderby = array('views' => 'desc');
                break;
        }
     
        $this->assign('order', $order);
        $count = $Coupon->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }

        $list = $Coupon->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['end_time'] = strtotime($val['end_date']) - NOW_TIME + 86400;
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $shops = D('Shop')->itemsByIds($shop_ids);
            $ids = array();
            foreach ($shops as $k => $val) {
                $shops[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
                $d = getDistanceNone($lat, $lng, $val['lat'], $val['lng']);
                $ids[$d][] = $k; //防止同样的距离出现 
            }
            ksort($ids);
            $showshops = array();
            foreach ($ids as $arr1) {
                foreach ($arr1 as $val) {
                    $showshops[$val] = $shops[$val];
                }
            }
            $this->assign('shops', $showshops);
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function detail() {
        $coupon_id = (int) $this->_get('coupon_id');
        if (empty($coupon_id)) {
            $this->error('该优惠券不存在！');
            die;
        }
		
		
        $Coupon = D('Coupon');
        if (!$detail = $Coupon->find($coupon_id)) {
            $this->error('该优惠券不存在！');
            die;
        }
        $Coupon->updateCount($coupon_id, 'views');
        $shop = D('Shop')->find($detail['shop_id']);
        $this->assign('shop', $shop);
        $this->assign('ex', D('Shopdetails')->find($detail['shop_id']));
        $this->assign('detail', $detail);
        $this->seodatas['shop_name'] = $shop['shop_name'];
        $this->seodatas['title'] = $detail['title'];
        $this->display(); // 输出模板 
    }

    public function baoprint() {
        $coupon_id = (int) $this->_get('coupon_id');
        if (empty($coupon_id)) {
            $this->error('该优惠券不存在！');
            die;
        }
        $Coupon = D('Coupon');
        if (!$detail = $Coupon->find($coupon_id)) {
            $this->error('该优惠券不存在！');
            die;
        }
        $this->assign('detail', $detail);
        $this->display(); // 输出模板 
    }

    public function download() {
        if (empty($this->uid)) {
           $this->error('登录状态失效!', U('passport/login'));
        }
        if (empty($this->member['mobile'])) {
            $this->error('亲还没有验证手机号码！', U('user/information/index'));
        }
        $coupon_id = (int) $this->_get('coupon_id');
        if (empty($coupon_id)) {
            $this->error('该优惠券不存在！');
            die;
        }
        $Coupon = D('Coupon');
        if (!$detail = $Coupon->find($coupon_id)) {
            $this->error('该优惠券不存在！');
            die;
        }

        if ($detail['expire_date'] < TODAY) {
            $this->error('该优惠券已经过期');
        }
        
         if($detail['num'] <=0){
            $this->error('该优惠券已经下载完了');
        }
        
        if($detail['limit_num']){
            $count = D('Coupondownload')->where(array( 'coupon_id' => $coupon_id,'user_id'=>  $this->uid))->count();
            if($count+1 > $detail['limit_num']){
                $this->error('您已经超过下载该优惠券的限制了！');
            }
        }
        
        
        $shop = D('Shop')->find($detail['shop_id']);

        $code = D('Coupondownload')->getCode();
        $data = array(
            'user_id' => $this->uid,
            'shop_id' => $detail['shop_id'],
            'coupon_id' => $coupon_id,
            'create_time' => NOW_TIME,
            'mobile' => $this->member['mobile'],
            'create_ip' => get_client_ip(),
            'code' => $code,
        );
        if ($download_id = D('Coupondownload')->add($data)) {
            D('Coupon')->updateCount($coupon_id, 'downloads');
            D('Coupon')->updateCount($coupon_id,'num',-1);
			D('Sms')->sms_coupon_user($download_id,$type =1);
            $this->success('恭喜您下载成功！', U('user/coupon/index'));
        }
        $this->error('下载失败！');
    }

}

<?php

class MallAction extends CommonAction
{
    public function _initialize()
    {
        parent::_initialize();
        if ($this->_CONFIG['operation']['mall'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $goods = cookie('goods_spec');
        $this->assign('cartnum', (int)array_sum($goods));
        //统计商城分类数量代码开始
        $cat = (int)$this->_param('cat');
        $Goods = D('Goods');
        $goodscates = D('Goodscate')->fetchAll();
        foreach ($goodscates as $key => $v) {
            if ($v['cate_id']) {
                $catids = D('Goodscate')->getChildren($v['cate_id']);
                if (!empty($catids)) {
                    $count = $Goods->where(array('cate_id' => array('IN', $catids), 'closed' => 0, 'audit' => 1, 'city_id' => $this->city_id))->count();
                } else {
                    $count = $Goods->where(array('cate_id' => $cat, 'closed' => 0, 'audit' => 1, 'city_id' => $this->city_id))->count();
                }
            }
            $goodscates[$key]['count'] = $count;
        }
        $this->assign('goodscates', $goodscates);
        $check_user_addr = D('Paddress')->where(array('user_id' => $this->uid))->find();//全局检测地址
        $this->assign('check_user_addr', $check_user_addr);
    }

    public function index()
    {
        $car_id = $this->_param('car_id', 'htmlspecialchars');
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $cat = (int)$this->_param('cat');
        $area = (int)$this->_param('area');
        $business = (int)$this->_param('business');
        $cate_id = (int)$this->_param('cate_id');
        $order = (int)$this->_param('order');
        $this->assign('area', $area);
        $this->assign('business', $business);
        $this->assign('cate_id', $cate_id);
        $this->assign('order', $order);
        $this->assign('cat', $cat);
        $this->assign('car_id', $car_id);
        $this->assign('nextpage', LinkTo('mall/loaddata', array('car_id' => $car_id, 'cat' => $cat, 'order' => $order, 'area' => $area, 'business' => $business, 'cate_id' => $cate_id, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
    }

    public function loaddata()
    {
        $Goods = D('Goods');
        import('ORG.Util.Page');
        $car_id = (int)$this->_param('car_id');

        $area = (int)$this->_param('area');
        $order = (int)$this->_param('order');
        $business = (int)$this->_param('business');
        if ($area) {
            $map['area_id'] = $area;
        }
        $cate_id = (int)$this->_param('cate_id');
        if ($cate_id) {
            $map['cate_id'] = $cate_id;
        }
        $map['audit'] = 1;
        $map['closed'] = 0;
        $map['end_date'] = array('egt', TODAY);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $cat = (int)$this->_param('cat');
        if ($cat) {
            $catids = D('Goodscate')->getChildren($cat);
            if (!empty($catids)) {
                $map['cate_id'] = array('IN', $catids);
            } else {
                $map['cate_id'] = $cat;
            }
        }
        $map['city_id'] = $this->city_id;
        if ($car_id) {
            $goods_ids = D('Cargoods')->where(array('car_id' => $car_id, 'closed' => 0))->getField('good_id', true);
            if (!empty($goods_ids)) {
                $map['goods_id'] = array('IN', $goods_ids);
            } else {
                die('0');
            }
        }
        $count = $Goods->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        if ($order == '1') {
            $order_arr = 'create_time desc';
        } elseif ($order == '2') {
            $order_arr = 'sold_num desc';
        } elseif ($order == '3') {
            $order_arr = 'mall_price desc';
        } elseif ($order == '4') {
            $order_arr = 'mall_price asc';
        } else {
            $order_arr = 'orderby asc';
        }
        $list = $Goods->where($map)->order($order_arr)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['end_time'] = strtotime($val['end_date']) - NOW_TIME + 86400;
            $list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    //商品收藏
    public function favorites()
    {
        if (empty($this->uid)) {
            $this->fengmiMsg('登录状态失效!', U('passport/login'));
            die;
        }
        $goods_id = (int)$this->_get('goods_id');
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->fengmiMsg('没有该商品');
        }
        if ($detail['closed']) {
            $this->fengmiMsg('该商品已经被删除');
        }
        if (D('Goodsfavorites')->check($goods_id, $this->uid)) {
            $this->fengmiMsg('您已经收藏过了！');
        }
        $data = array('goods_id' => $goods_id, 'user_id' => $this->uid, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
        if (D('Goodsfavorites')->add($data)) {
            $this->fengmiMsg('恭喜您收藏成功！', U('mall/detail', array('goods_id' => $goods_id)));
        }
        $this->fengmiMsg('收藏失败！');
    }

    //立即购买
    public function buy($goods_id)
    {
        $goods_id = (int)$goods_id;
        if (empty($goods_id)) {
            $this->error('请选择产品');
        }
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->error('该商品不存在');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->error('该商品不存在');
        }
        if ($detail['end_date'] < TODAY) {
            $this->error('该商品已经过期，暂时不能购买');
        }
        $goods_spec = cookie('goods_spec');
        $num = (int)$this->_get('num');
        $spec_key = $this->_get('spec_key');
        if (empty($num) || $num <= 0) {
            $num = 1;
        }
        $is_spec_stock = is_spec_stock($goods_id, $spec_key, $num);
        if (!$is_spec_stock) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该规格库存不足了，少买点吧！'));
        }
        if ($detail['num'] <= $num) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该商品只剩' . $detail['num'] . '件了，少买点吧！'));
        }
        $goods_spec_v = $goods_id . '|' . $spec_key; //重新组合那个 商品id和那个啥规格键
        if (isset($goods_spec[$goods_spec_v])) {
            $goods_spec[$goods_spec_v] += $num;
        } else {
            $goods_spec[$goods_spec_v] = $num;
        }
        $key[$goods_id] = $spec_key;//规格
        cookie('goods_spec', $goods_spec, 604800);
        $this->ajaxReturn(array('status' => 'success', 'msg' => '加入购物车成功,正在跳转到购物车', 'url' => U('mall/cart')));
    }

    public function cartadd($goods_id)
    {
        $shop_id = (int)$this->_param('shop_id');
        $goods_id = (int)$goods_id;
        if (empty($goods_id)) {
            die('请选择产品');
        }
        if (!($detail = D('Goods')->find($goods_id))) {
            die('该商品不存在');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            die('该商品不存在');
        }
        if ($detail['end_date'] < TODAY) {
            die('该商品已经过期，暂时不能购买');
        }
        $goods = cookie('goods');
        if (isset($goods[$goods_id])) {
            $goods[$goods_id] = $goods[$goods_id] + 1;
        } else {
            $goods[$goods_id] = 1;
        }
        cookie('goods', $goods);
        die('0');
    }

    public function cartadd2()
    {
        if (IS_AJAX) {
            $shop_id = (int)$_POST['shop_id'];
            $goods_id = (int)$_POST['goods_id'];
            $goods_spec = cookie('goods_spec');
            $spec_key = $_POST['spec_key'];
            $num = $_POST['num'];
            if (empty($goods_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '请选择商品'));
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
            $goods_spec_v = $goods_id . '|' . $spec_key;
            //重新组合那个 商品id和那个啥规格键
            //加入购物车时候检查规格库存  如果不走这里他会走下面的
            $is_spec_stock = is_spec_stock($goods_id, $spec_key, $num);
            if (!$is_spec_stock) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该规格库存不足了，少买点吧！'));
            }
            if ($detail['num'] <= $num) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '亲！该商品只剩' . $detail['num'] . '件了，少买点吧！'));
            }
            if (isset($goods_spec[$goods_spec_v])) {
                $goods_spec[$goods_spec_v] += $num;
            } else {
                $goods_spec[$goods_spec_v] = $num;
            }
            cookie('goods_spec', $goods_spec, 604800);
            $goods = cookie('goods');
            if (isset($goods[$goods_id])) {
                $goods[$goods_id] = $goods[$goods_id] + 1;
            } else {
                $goods[$goods_id] = 1;
            }
            $this->ajaxReturn(array('status' => 'success', 'msg' => '加入购物车成功'));
        }
    }

    public function cart()
    {
        if (empty($this->uid)) {
            $this->error('请先登陆', U('passport/login'));
        }
        $goods = cookie('goods');
        $back = end($goods);
        $back = key($goods);
        $goods_spec = cookie('goods_spec');
        $this->assign('back', $back);
        if (empty($goods_spec)) {
            $this->error('亲还没有选购产品呢!', U('mall/index'));
        }
        $spec_keys = array_keys($goods_spec);
        $spec_arr = $this->spec_to_arr($goods_spec);
        $goods_ids = $this->get_goods_ids($goods_spec);

        foreach ($goods_ids as $k => $v) {
            $cart_goods[] = D('Goods')->itemsByIds($v);
        }
        $shop_ids = array();
        foreach ($cart_goods as $k => $val) {
            foreach ($val as $key => $det) {
                $cart_goods[$k][$key]['buy_num'] = $spec_arr[$k][2];//购买数量
                $cart_goods[$k][$key]['sky'] = $spec_arr[$k][1];
                $cart_goods[$k][$key]['goods_spec'] = $spec_keys[$k];
                $shop_ids[$det['shop_id']] = $det['shop_id'];
                if (!empty($cart_goods[$k][$key]['sky'])) {
                    //通过这个sky来查多属性里面的价格  其实也就是一条数据
                    $spt = D('TpSpecGoodsPrice')->where("`key`='{$cart_goods[$k][$key]['sky']}' and `goods_id`='{$cart_goods[$k][$key]['goods_id']}'")->find();
                    $cart_goods[$k][$key]['mall_price'] = $spt['price'] * 100;
                    $cart_goods[$k][$key]['key_name'] = $spt['key_name'];
                }
            }

        }
        $this->assign('cart_shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('cart_goods', $cart_goods);
        $this->display();
    }

    private function spec_to_arr($goods_spec)
    {
        $spec_key = array_keys($goods_spec);
        foreach ($spec_key as $k => $v) {
            $spec_arr[$k] = explode('|', $v);
            $spec_arr[$k][] = $goods_spec[$v];
        }
        return $spec_arr;

    }

    private function get_goods_ids($goods_spec)
    {
        $spec_arr = $this->spec_to_arr($goods_spec);
        foreach ($spec_arr as $k => $v) {
            $goods_ids[] = $v[0];
        }
        return $goods_ids;
    }

    public function detail($goods_id)
    {
        $goods_id = (int)$goods_id;
        if (empty($goods_id)) {
            $this->error('商品不存在');
        }
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->error('商品不存在');
        }
        if ($detail['closed'] != 0 || $detail['audit'] != 1) {
            $this->error('商品不存在');
        }
        $shop_id = $detail['shop_id'];

        $recom = D('Goods')->where(array('shop_id' => $shop_id, 'audit' => 1, 'closed' => 1, 'goods_id' => array('neq', $goods_id), 'end_date' => array('egt', TODAY)))->select();
        $record = D('Usersgoods');
        $insert = $record->getRecord($this->uid, $goods_id);
        $this->assign('recom', $recom);
        $this->assign('detail', $detail);
        $this->assign('shop', D('Shop')->find($shop_id));
        $filter_spec = $this->get_spec($goods_id); //获取商品规格参数        
        $goodsss = M('Goods')->find($goods_id);
        $goodsss[mall_price] = $goodsss[mall_price] / 100;
        $spec_goods_price = M('TpSpecGoodsPrice')->where("goods_id = $goods_id")->getField("key,price,store_count"); // 规格 对应 价格 库存表
        if ($spec_goods_price != null) {
            $this->assign('spec_goods_price', json_encode($spec_goods_price, true)); // 规格 对应 价格 库存表
        }
        $yh = $goodsss[yh];
        if ($yh != '0') {
            $yh = explode(PHP_EOL, $yh);
            for ($i = 0; $i < count($yh) - 1; $i++) {
                $yh[s][] = explode(',', $yh[$i]);
            }
            foreach ($yh[s] as $k2 => $vo) {
                foreach ($vo as $k2 => $v2) {
                    $rs[$k2][] = $v2;
                }
            }
            $goodsss['zks'][] = $rs[0];
            $goodsss['zks'][] = $rs[1];
        }

        $this->assign('filter_spec', $filter_spec);
        $this->assign('goods', $goodsss);
        $pingnum = D('Goodsdianping')->where(array('goods_id' => $goods_id, 'show_date' => array('ELT', TODAY)))->count();
        $this->assign('pingnum', $pingnum);
        $score = (int)D('Goodsdianping')->where(array('goods_id' => $goods_id))->avg('score');
        if ($score == 0) {
            $score = 5;
        }
        $this->assign('score', $score);
        if (($detail['is_vs1'] || $detail['is_vs2'] || $detail['is_vs3'] || $detail['is_vs4'] || $detail['is_vs5'] || $detail['is_vs6']) == 1) {
            $this->assign('is_vs', $is_vs = 1);
        } else {
            $this->assign('is_vs', $is_vs = 0);
        }

        $this->assign('pics', D('Goodsphoto')->getPics($detail['goods_id']));
        $this->assign('count_goodsfavorites', $count_goodsfavorites = D('Goodsfavorites')->where(array('goods_id' => $detail['goods_id']))->count());
        $this->assign('goodsfavorites', $goodsfavorites = D('Goodsfavorites')->check($goods_id, $this->uid));//检测自己是不是收藏
        $this->display();
    }

    public function get_spec($goods_id)
    {
        //商品规格 价钱 库存表 找出 所有 规格项id
        $keys = M('TpSpecGoodsPrice')->where("goods_id = $goods_id")->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        $filter_spec = array();
        if ($keys) {
            //$specImage =  M('TpSpecImage')->where("goods_id = $goods_id and src != '' ")->getField("spec_image_id,src");// 规格对应的 图片表， 例如颜色
            $keys = str_replace('_', ',', $keys);
            $sql = "SELECT a.name,a.order,b.* FROM __PREFIX__tp_spec AS a INNER JOIN __PREFIX__tp_spec_item AS b ON a.id = b.spec_id WHERE b.id IN($keys) ORDER BY a.order";
            $filter_spec2 = M()->query($sql);
            foreach ($filter_spec2 as $key => $val) {
                $filter_spec[$val['name']][] = array(
                    'item_id' => $val['id'],
                    'item' => $val['item'],
                );
            }
        }

        return $filter_spec;
    }

    public function cartdel()
    {
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

    public function cartdel2()
    {
        $goods_id = (int)$this->_get('goods_id');
        $goods = cookie('goods');
        if (isset($goods[$goods_id])) {
            unset($goods[$goods_id]);
            cookie('goods', $goods);
        }
        header('Location:' . U('mall/cart'));
    }

    public function neworder()
    {
        $goods = $this->_get('goods');
        $goods = explode(',', $goods);
        if (empty($goods)) {
            $this->error('亲购买点吧');
        }
        $datas = array();
        foreach ($goods as $val) {
            $good = explode('-', $val);
            $good[1] = (int)$good[1];
            if (empty($good[0]) || empty($good[1])) {
                $this->error('亲购买点吧');
            }
            if ($good[1] > 99 || $good[1] < 0) {
                $this->error('本店不支持批发');
            }
            $datas[$good[0]] = $good[1];
        }
        cookie('goods', $datas);
        header('Location:' . U('mall/cart'));
        die;
    }

    public function order()
    {
        if (empty($this->uid)) {
            $this->fengmiMsg('请先登陆', U('passport/login'));
        }
        $user_integral = D("users")->field('integral')->find($this->uid);
        $num = $this->_post('num', false);
        $goods_ids = array();
        foreach ($num as $k => $val) {
            $val = (int)$val;
            if (empty($val)) {
                unset($num[$k]);
            } elseif ($val < 1 || $val > 99) {
                unset($num[$k]);
            } else {
                $spec_keys[] = $k;
                $spec_arr[] = explode('|', $k);
                $spec_temp = explode('|', $k);
                $goods_ids[$k][] = (int)$spec_temp[0];
            }
        }

        foreach ($goods_ids as $v) {
            $goods[] = D('Goods')->itemsByIds($v);
        }
        foreach ($goods as $k => $v) {
            foreach ($v as $key => $val) {
                if ($val['closed'] != 0 || $val['audit'] != 1 || $val['end_date'] < TODAY) {
                    unset($goods[$key]);
                }
                //把这个商品的规格存进数组
                $goods[$k][$key][sky] = $spec_arr[$k][1]; //把后面的规格存进来 148_150
                $goods[$k][$key]['goods_spec'] = $spec_keys[$k];//整个存一下
                if (!empty($goods[$k][$key][sky])) {
                    //改变价格
                    $spt = D('TpSpecGoodsPrice')->where("`key`='{$goods[$k][$key][sky]}' and `goods_id`='{$goods[$k][$key][goods_id]}'")->find();
                    $goods[$k][$key]['mall_price'] = $spt['price'] * 100;
                    $goods[$k][$key]['key_name'] = $spt['key_name'];//建的中文名
                }
            }
        }

        if (empty($goods)) {
            $this->fengmiMsg('很抱歉，您提交的产品暂时不能购买！');
        }
        //下单前检查库存
        foreach ($goods as $val) {
            $val = reset($val);
            //二维数组 取第一个
            //加入购物车时候检查规格库存  如果不走这里他会走下面的
            $is_spec_stock = is_spec_stock($val[goods_id], $val[sky], $num[$val['goods_spec']]);
            if (!$is_spec_stock) {
                $spec_one_num = get_one_spec_stock($val[goods_id], $val[sky]);
                $this->fengmiMsg('亲！规格为<' . $val['key_name'] . '>的商品库存不够了,只剩' . $spec_one_num . '件了！');
            }

            if ($val['num'] < $num[$val['goods_spec']]) {
                $this->fengmiMsg('亲！商品<' . $val['title'] . '>库存不够了,只剩' . $val['num'] . '件了！');
            }
        }


        $tprice = 0;
        $all_integral = $total_mobile = 0;
        $ip = get_client_ip();
        $total_canuserintegral = $ordergoods = $total_price = array();
        foreach ($goods as $val) {
            $val = reset($val);
            //二维数组 取第一个
            //二次开发的 其他人可能看不懂 之前是  $num[$val['goods_id']]  这个我前面那个num已经改过了 但是下面的代码不想改了 所以统一赋值一下
            //前面已经通过这个规格的键值来重新传了
            $num[$val['goods_id']] = $num[$val['goods_spec']];
            $price = $val['mall_price'] * $num[$val['goods_id']];
            $js_price = $val['settlement_price'] * $num[$val['goods_id']];
            $mobile_fan = $val['mobile_fan'] * $num[$val['goods_id']]; //每个商品的手机减少的钱
            $canuserintegral = $val['use_integral'] * $num[$val['goods_id']];
            $order_express_price = D('Ordergoods')->calculation_express_price($this->uid, $val['kuaidi_id'], $num[$val['goods_id']], $val['goods_id'], 0);
            //返回单个商品运费
            $m_price = $price - $mobile_fan;
            $tprice += $m_price;
            $total_mobile += $mobile_fan;
            $all_integral += $canuserintegral;
            $back_money = ((int)$val['price'] - (int)$val['mall_price'])/((int)$val['backcount']);//每期返还金额
            $ordergoods[$val['shop_id']][] = array(
                'goods_id' => $val['goods_id'],
                'shop_id' => $val['shop_id'],
                'num' => $num[$val['goods_id']],
                'kuaidi_id' => $val['kuaidi_id'],
                'price' => $val['mall_price'],
                'total_price' => $price,
                'back_money' =>$back_money,
                'back_count' => $val['backcount'],
                'back_inteval' =>$val['backinteval'],
                'mobile_fan' => $mobile_fan,
                'express_price' => $order_express_price, //单个商品运费总价
                'is_mobile' => 1,
                'js_price' => $js_price,
                'create_time' => NOW_TIME,
                'create_ip' => $ip,
                'key' => $val['sky'],
                'key_name' => $val['key_name']
            );
            $total_canuserintegral[$val['shop_id']] += $canuserintegral; //不同商家可使用积分
            $total_price[$val['shop_id']] += $price; //不同商家的总价格
            $express_price[$val['shop_id']] += $order_express_price; //不同商家总运费
            $mm_price[$val['shop_id']] += $mobile_fan;  //不同商家的手机下单立减

        }
        $order = array('user_id' => $this->uid, 'create_time' => NOW_TIME, 'create_ip' => $ip, 'is_mobile' => 1);
        $tui = cookie('tui');
        if (!empty($tui)) {
            $tui = explode('_', $tui);
            $tuiguang = array('uid' => (int)$tui[0], 'goods_id' => (int)$tui[1]);
        }
        $defaultAddress = D('Paddress')->defaultAddress($this->uid, $type);//收货地址部分重写
        $order_ids = array();
        foreach ($ordergoods as $k => $val) {
            $order['shop_id'] = $k;
            $order['total_price'] = $total_price[$k];
            $order['mobile_fan'] = $mm_price[$k];
            $order['can_use_integral'] = $total_canuserintegral[$k];
            $order['express_price'] = $express_price[$k];//写入运费
            $order['address_id'] = $defaultAddress['id'];//写入快递ID

            $val[0]['express_price'] = $express_price[$k];//写入运费,蜂蜜7月30日二开
            $val[0]['address_id'] = $defaultAddress['id'];//写入快递,蜂蜜7月30日二开
            $shop = D('Shop')->find($k);
            $order['is_shop'] = (int)$shop['is_pei'];
            if ($order_id = D('Order')->add($order)) {//这里写入订单表了
                $order_ids[] = $order_id;
                foreach ($val as $k1 => $val1) {
                    $val1['order_id'] = $order_id;
                    if (!empty($tuiguang)) {
                        if ($tuiguang['goods_id'] == $val1['goods_id']) {
                            $val1['tui_uid'] = $tuiguang['uid'];
                        }
                    }
                    D('Ordergoods')->add($val1);
                }
            }
        }
        cookie('goods_spec', null);// 清空 cookie
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
            $this->fengmiMsg('合并下单成功，接下来选择支付方式和配送地址！', U('mall/paycode', array('log_id' => $logs['log_id'])));
        } else {
            $this->fengmiMsg('下单成功，接下来选择支付方式和配送地址！', U('mall/pay', array('order_id' => $order_id,'address_id'=>$defaultAddress['id'])));
        }
//        $this->fengmiMsg('恭喜您预约成功，请耐心等待审核！', U('user/goods/index'));
        die;
    }

    public function paycode()
    {
        $log_id = (int)$this->_get('log_id');
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
        //收货地址部分重写
        $defaultAddress = D('Paddress')->defaultAddress($this->uid, $type);
        $changeAddressUrl = "http://" . $_SERVER['HTTP_HOST'] . U('address/addlist', array('type' => goods, 'log_id' => $log_id));
        $this->assign('defaultAddress', $defaultAddress);
        $this->assign('changeAddressUrl', $changeAddressUrl);
        //重写结束
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->assign('logs', $detail);
        $this->display();
    }

    public function pay()
    {
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
            die;
        }
        $this->check_mobile();
        cookie('goods', null); //销毁cookie
        $order_id = (int)$this->_get('order_id');
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
        $this->assign('goods', D('Goods')->itemsByIds($goods_id));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('ordergoods', $ordergood);

        //收货地址部分重写
        if (false == $defaultAddress = D('Paddress')->order_address_id($this->uid, $order_id)) {
            $this->error('获取用户地址出错，请先去会员中心添加商城地址后下单');
        }
        $changeAddressUrl = "http://" . $_SERVER['HTTP_HOST'] . U('address/addlist', array('type' => goods, 'order_id' => $order_id));
        $this->assign('defaultAddress', $defaultAddress);
        $this->assign('changeAddressUrl', $changeAddressUrl);
        //重写结束
        //如果没有优惠劵ID就去获取开始
        if (!empty($order['download_id'])) {
            $this->assign('download_id', $order['download_id']);
        } else {
            $this->assign('coupon', $coupon = D('Coupon')->Obtain_Coupon($order_id, $this->uid));
        }
        //获取优惠劵ID结束
        if (is_mobile()) {
            $this->assign('mobile_fan', $ordergood['0']['mobile_fan']);
        }
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('order', $order);
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->display();
    }

    public function paycode2()
    {
        //这里是因为原来的是按订单付，这里是合并付款逻辑部分
        $log_id = (int)$this->_get('log_id');
        if (empty($log_id)) {
            $this->fengmiMsg('没有有效支付记录！');
        }
        if (!($detail = D('Paymentlogs')->find($log_id))) {
            $this->fengmiMsg('没有有效的支付记录！');
        }
        if ($detail['is_paid'] != 0 || empty($detail['order_ids']) || !empty($detail['order_id']) || empty($detail['need_pay'])) {
            $this->fengmiMsg('没有有效的支付记录！');
        }
        $order_ids = explode(',', $detail['order_ids']);
        //这里合并付款逻辑暂时不做1，做留言系统，2，做优惠劵ID，3;优惠劵减去的金额
        D('Order')->where(array('order_id' => array('IN', $order_ids)))->save(array('addr_id' => $addr_id));
        /**********************蜂蜜 修复合并付款的时候的系列订单错误问题*****************************/
        $orders = D('order')->where(array('order_id' => array('IN', $order_ids)))->select();
        foreach ($orders as $k => $val) {
            $need_pay[$val[order_id]] = $val['total_price'] - $val['mobile_fan'] - $val['use_integral'];
            D('Order')->where(array('order_id' => $val['order_id']))->save(array('need_pay' => $need_pay[$val[order_id]]));
        }
        /*****************************************************/
        if (!($code = $this->_post('code'))) {
            $this->fengmiMsg('请选择支付方式！');
        }
        if ($code == 'wait') {
            //如果是货到付款
            D('Order')->save(array('is_daofu' => 1), array('where' => array('order_id' => array('IN', $order_ids))));
            D('Ordergoods')->save(array('is_daofu' => 1), array('where' => array('order_id' => array('IN', $order_ids))));
            D('Order')->mallSold($order_ids);//更新销量
            D('Order')->mallPeisong(array($order_ids), 1);//更新配送
            D('Sms')->mallTZshop($order_ids);//用户下单通知商家
            D('Order')->combination_goods_print($order_ids);//多商家订单打印
            $this->fengmiMsg('恭喜您下单成功！', U('user/goods/index'));
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->fengmiMsg('该支付方式不存在');
            }
            //蜂蜜二开合并付款开始
            foreach ($order_ids as $v) {
                $need_pay = D('Order')->useIntegral($this->uid, array($v));//这个不知道能不能返回
                D('Order')->where("order_id={$v}")->save(array('need_pay' => $need_pay));//合并付款的时候更新实际付款金额
                $log_need += $need_pay;
            }
            $detail['need_pay'] = $log_need;
            $detail['code'] = $code;
            //蜂蜜二开合并付款结束
            $detail['code'] = $code;
            D('Paymentlogs')->save($detail);
            $this->fengmiMsg('订单设置完成，即将进入付款。', U('mall/combine', array('log_id' => $detail['log_id'])));
        }
    }

    public function combine()
    {
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
            die;
        }
        $log_id = (int)$this->_get('log_id');
        if (!($detail = D('Paymentlogs')->find($log_id))) {
            $this->error('没有有效的支付记录！');
        }

        if ($detail['is_paid'] != 0 || empty($detail['order_ids']) || !empty($detail['order_id']) || empty($detail['need_pay'])) {
            $this->error('没有有效的支付记录！');
        }
        $this->assign('button', D('Payment')->getCode($detail));
        $this->assign('logs', $detail);
        $this->display();
    }

    //付款
    public function pay2()
    {
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('passport/login'));
            die;
        }
        $order_id = (int)$this->_get('order_id');
        $order = D('Order')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->fengmiMsg('该订单不存在');
        }

        $address_id = isset($_GET['address_id']) ? intval($_GET['address_id']) : $order['address_id'];//检测配送地址ID
        if (empty($address_id)) {
            $this->fengmiMsg('配送的地址异常');
        } else {
            D('Order')->save(array('order_id' => $order_id, 'address_id' => $address_id));
        }

        //添加优惠劵满减的优惠劵
        $download_id = (int)$this->_post('download_id');
        if (!empty($download_id)) {
            $coupon_price = D('Coupon')->Obtain_Coupon_Price($order_id, $download_id);
            if (!empty($coupon_price)) {
                D('Order')->save(array('order_id' => $order_id, 'download_id' => $download_id, 'coupon_price' => $coupon_price));
                //p(D('Order')->getLastSql()) ;die;这里有问题，后面立即处理
            }
        }
        //优惠劵结束
        if (!($code = $this->_post('code'))) {
            $this->fengmiMsg('请选择支付方式！');
        }
        $this->goods_mum($order_id);//检测库存
        $address = D('Paddress')->where(array('address_id' => $order['address_id']))->find();
        if ($code == 'wait') {
            //如果是货到付款
            D('Order')->save(array('order_id' => $order_id, 'is_daofu' => 1));
            D('Ordergoods')->save(array('is_daofu' => 1), array('where' => array('order_id' => $order_id)));
            D('Order')->mallSold($order_id);//更新销量
            D('Order')->mallPeisong(array($order_id), 1);//更新配送
            D('Sms')->mallTZshop($order_id);//用户下单通知商家
            D('Order')->combination_goods_print($order_id);//万能商城订单打印
            D('Weixintmpl')->weixin_notice_goods_user($order_id, $this->uid, 0);//商城微信通知
            $this->fengmiMsg('恭喜您预约成功！', U('user/goods/index'));
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->fengmiMsg('该支付方式不存在');
            }

            $logs = D('Paymentlogs')->getLogsByOrderId('goods', $order_id); //写入支付记录
            $need_pay = D('Order')->useIntegral($this->uid, array($order_id));//更新支付结果,这里加了配送费
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
                //单个付款走的这里，为什么没写入数据库
                $logs['log_id'] = D('Paymentlogs')->add($logs);
            } else {
                $logs['need_pay'] = $need_pay;
                $logs['code'] = $code;
                D('Paymentlogs')->save($logs);
            }

            D('Order')->where("order_id={$order_id}")->save(array('need_pay' => $need_pay));//再更新一次最终的价格，蜂蜜独创
            D('Weixintmpl')->weixin_notice_goods_user($order_id, $this->uid, 1);//商城微信通知
            $this->fengmiMsg('订单设置完成，即将进入付款。', U('payment/payment', array('log_id' => $logs['log_id'])));
        }
    }

    //团购点评
    public function dianping()
    {
        $goods_id = (int)$this->_get('goods_id');
        if (!($detail = D('Goods')->find($goods_id))) {
            $this->error('没有该商品');
            die;
        }
        if ($detail['closed']) {
            $this->error('该商品已经被删除');
            die;
        }

        $this->assign('next', LinkTo('mall/dianpingloading', $linkArr, array('goods_id' => $goods_id, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('detail', $detail);
        $this->display();
    }

    public function dianpingloading()
    {
        $goods_id = (int)$this->_get('goods_id');
        if (!($detail = D('Goods')->find($goods_id))) {
            die('0');
        }
        if ($detail['closed']) {
            die('0');
        }
        $Goodsdianping = D('Goodsdianping');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'goods_id' => $goods_id, 'show_date' => array('ELT', TODAY));
        $count = $Goodsdianping->where($map)->count();
        $Page = new Page($count, 5);
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Goodsdianping->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $orders_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $orders_ids[$val['order_id']] = $val['order_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($orders_ids)) {
            $this->assign('pics', D('Goodsdianpingpics')->where(array('order_id' => array('IN', $orders_ids)))->select());
        }
        $this->assign('totalnum', $count);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('detail', $detail);
        $this->display();
    }

    //点评详情
    public function img()
    {
        $order_id = (int)$this->_get('order_id');
        if (!($detail = D('Goodsdianping')->find($order_id))) {
            $this->error('没有该点评');
            die;
        }
        if ($detail['closed']) {
            $this->error('该点评已经被删除');
            die;
        }
        $list = D('Goodsdianpingpics')->where(array('order_id' => $order_id))->select();
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }

    //付款前检测库存
    public function goods_mum($order_id)
    {
        $order_id = (int)$order_id;
        $ordergoods_ids = D('Ordergoods')->where(array('order_id' => $order_id))->select();
        foreach ($ordergoods_ids as $k => $v) {
            $goods_num = D('Goods')->where(array('goods_id' => $v['goods_id']))->find();
            //也得检查下那个多规格的 这里
            $is_spec_stock = is_spec_stock($v[goods_id], $v[key], $v['num']);
            if (!$is_spec_stock) {
                $spec_one_num = get_one_spec_stock($v[goods_id], $v[key]);
                $this->baoError('亲！规格为<' . $v['key_name'] . '>的商品库存不够了,只剩' . $spec_one_num . '件了！');
            }
            if ($goods_num['num'] < $v['num']) {
                $this->fengmiMsg('商品ID' . $v['goods_id'] . '库存不足无法付款', U('user/goods/index', array('aready' => 1)));;
            }
        }
        return false;
    }

    public function carInfo()
    {
        $car_id = $this->_param('car_id');
        if (empty($car_id)) {
            $this->error('获取车辆信息有误，请稍后重试！');
        }
        $car = D('Car')->find($car_id);

        $this->assign('car', $car);

        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $cat = (int)$this->_param('cat');
        $area = (int)$this->_param('area');
        $business = (int)$this->_param('business');
        $cate_id = (int)$this->_param('cate_id');
        $order = (int)$this->_param('order');
        $this->assign('area', $area);
        $this->assign('business', $business);
        $this->assign('cate_id', $cate_id);
        $this->assign('order', $order);
        $this->assign('cat', $cat);
        $this->assign('nextpage', LinkTo('mall/loaddata', array('car_id' => $car_id, 'cat' => $cat, 'order' => $order, 'area' => $area, 'business' => $business, 'cate_id' => $cate_id, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
    }

    public function push()
    {
        $Good = D('Goods');
        import('ORG.Util.Page');
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $map = array('audit' => 1, 'closed' => 0, 'end_date' => array('EGT', TODAY));
        $count = $Good->where($map)->count();
        $Page = new Page($count, 3);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $map['shoplx'] = '53';
        $Goods = $Good->order('orderby asc')->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
//        foreach ($Goods as $k => $val) {
//            $Goods[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
//        }
        $this->assign('goods', $Goods);
        $this->assign('page', $show);
        $this->display();
    }
}
<?php
class DianpingAction extends CommonAction{
    public function index(){
        import('ORG.Util.Page');
        $status = (int) $this->_param('status');
        $this->assign('status', $status);
        $st = (int) $this->_param('st');
        $this->assign('st', $st);
        if ($status == 1 || empty($status)) {
            $Shopdianping = D('Shopdianping');
            $map = array('closed' => 0, 'user_id' => $this->uid);
            $count = $Shopdianping->where($map)->count();
            $Page = new Page($count, 25);
            $show = $Page->show();
            $list = $Shopdianping->where($map)->order(array('dianping_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $dianping_ids = $shop_ids = array();
            foreach ($list as $k => $val) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
                $dianping_ids[$val['dianping_id']] = $val['dianping_id'];
            }
            if (!empty($shop_ids)) {
                $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
            }
            if (!empty($dianping_ids)) {
                $this->assign('pics', D('Shopdianpingpics')->where(array('dianping_id' => array('IN', $dianping_ids)))->select());
            }
            foreach ($list as $key => $v) {
                if (in_array($v['dianping_id'], $dianping_ids)) {
                    $list[$key]['pichave'] = 1;
                }
            }
            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->display('index');
        } elseif ($status == 2) {
            $Tuanorder = D('Tuanorder');
            if ($st == 1 || empty($st)) {
                $map = array('user_id' => $this->uid, 'status' => 1);
                $lists = $Tuanorder->where($map)->order(array('order_id' => 'desc'))->select();
                $dianping = D('Tuandianping')->where(array('user_id' => $this->uid))->select();
                $orders = array();
                foreach ($dianping as $key => $v) {
                    $orders[] = $v['order_id'];
                }
                foreach ($lists as $kk => $vv) {
                    if (in_array($vv['order_id'], $orders)) {
                        unset($lists[$kk]);
                    }
                }
                $count = count($lists);
                $Page = new Page($count, 10);
                $show = $Page->show();
                $list = array_slice($lists, $Page->firstRow, $Page->listRows);
                $shop_ids = $tuan_ids = array();
                foreach ($list as $k => $val) {
                    if (!empty($val['shop_id'])) {
                        $shop_ids[$val['shop_id']] = $val['shop_id'];
                    }
                    $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
                }
                $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
                $this->assign('tuan', D('Tuan')->itemsByIds($tuan_ids));
                $this->assign('list', $list);
                $this->assign('page', $show);
            } elseif ($st == 2) {
                $map = array('closed' => 0, 'user_id' => $this->uid);
                $count = D('Tuandianping')->where($map)->count();
                $Page = new Page($count, 25);
                $show = $Page->show();
                $list = D('Tuandianping')->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $order_ids = $shop_ids = $tuan_ids = array();
                foreach ($list as $k => $val) {
                    $shop_ids[$val['shop_id']] = $val['shop_id'];
                    $order_ids[$val['order_id']] = $val['order_id'];
                    $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
                }
                if (!empty($shop_ids)) {
                    $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
                }
                if (!empty($tuan_ids)) {
                    $this->assign('tuans', D('Tuan')->itemsByIds($tuan_ids));
                }
                if (!empty($order_ids)) {
                    $this->assign('pics', D('Tuandianpingpics')->where(array('order_id' => array('IN', $order_ids)))->select());
                }
                foreach ($list as $key => $v) {
                    if (in_array($v['order_id'], $order_ids)) {
                        $list[$key]['pichave'] = 1;
                    }
                }
                $this->assign('list', $list);
                $this->assign('page', $show);
            }
            $this->display('tuan');
        } elseif ($status == 3) {
            $Eleorder = D('Eleorder');
            if ($st == 1 || empty($st)) {
                $map = array('user_id' => $this->uid, 'status' => 8);
                $lists = $Eleorder->where($map)->order(array('order_id' => 'desc'))->select();
                $dianping = D('Eledianping')->where(array('user_id' => $this->uid))->select();
                $orders = array();
                foreach ($dianping as $key => $v) {
                    $orders[] = $v['order_id'];
                }
                foreach ($lists as $kk => $vv) {
                    if (in_array($vv['order_id'], $orders)) {
                        unset($lists[$kk]);
                    }
                }
                $count = count($lists);
                $Page = new Page($count, 10);
                $show = $Page->show();
                $list = array_slice($lists, $Page->firstRow, $Page->listRows);
                $shop_ids = $order_ids = array();
                foreach ($list as $k => $val) {
                    if (!empty($val['shop_id'])) {
                        $shop_ids[$val['shop_id']] = $val['shop_id'];
                    }
                    $order_ids[$val['order_id']] = $val['order_id'];
                }
                if (!empty($shop_ids)) {
                    $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
                }
                if (!empty($order_ids)) {
                    $products = D('Eleorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
                    $product_ids = $shop_ids = array();
                    foreach ($products as $val) {
                        $product_ids[$val['product_id']] = $val['product_id'];
                    }
                    $this->assign('products', $products);
                    $this->assign('eleproducts', D('Eleproduct')->itemsByIds($product_ids));
                }
                $this->assign('cfg', D('Eleorder')->getCfg());
                $this->assign('list', $list);
                $this->assign('page', $show);
            } elseif ($st == 2) {
                $map = array('closed' => 0, 'user_id' => $this->uid);
                $count = D('Eledianping')->where($map)->count();
                $Page = new Page($count, 25);
                $show = $Page->show();
                $list = D('Eledianping')->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $order_ids = $shop_ids = array();
                foreach ($list as $k => $val) {
                    $shop_ids[$val['shop_id']] = $val['shop_id'];
                    $order_ids[$val['order_id']] = $val['order_id'];
                }
                if (!empty($shop_ids)) {
                    $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
                }
                if (!empty($order_ids)) {
                    $this->assign('pics', D('Eledianpingpics')->where(array('order_id' => array('IN', $order_ids)))->select());
                    $products = D('Eleorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
                    $product_ids = $shop_ids = array();
                    foreach ($products as $val) {
                        $product_ids[$val['product_id']] = $val['product_id'];
                    }
                    $this->assign('products', $products);
                    $this->assign('eleproducts', D('Eleproduct')->itemsByIds($product_ids));
                }
                foreach ($list as $key => $v) {
                    if (in_array($v['order_id'], $order_ids)) {
                        $list[$key]['pichave'] = 1;
                    }
                }
                $this->assign('list', $list);
                $this->assign('page', $show);
            }
            $this->display('ele');
        } elseif ($status == 4) {
            $Shopdingorder = D('Shopdingorder');
            if ($st == 1 || empty($st)) {
                $map = array('user_id' => $this->uid, 'status' => 2, 'is_dianping' => 0);
                $list = $Shopdingorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $ding_ids = $order_ids = array();
                foreach ($list as $k => $val) {
                    if (!empty($val['shop_id'])) {
                        $shop_ids[$val['shop_id']] = $val['shop_id'];
                    }
                    $order_ids[$val['order_id']] = $val['order_id'];
                }
                $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
                $this->assign('tuan', D('Tuan')->itemsByIds($tuan_ids));
                $this->assign('list', $list);
                $this->assign('page', $show);
            } elseif ($st == 2) {
                $map = array('closed' => 0, 'user_id' => $this->uid);
                $count = D('Shopdingdianping')->where($map)->count();
                $Page = new Page($count, 25);
                $show = $Page->show();
                $list = D('Shopdingdianping')->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $order_ids = $shop_ids = array();
                foreach ($list as $k => $val) {
                    $shop_ids[$val['shop_id']] = $val['shop_id'];
                    $order_ids[$val['order_id']] = $val['order_id'];
                }
                if (!empty($shop_ids)) {
                    $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
                }
                if (!empty($order_ids)) {
                    $this->assign('pics', D('Tuandianpingpics')->where(array('order_id' => array('IN', $order_ids)))->select());
                }
                foreach ($list as $key => $v) {
                    if (in_array($v['order_id'], $order_ids)) {
                        $list[$key]['pichave'] = 1;
                    }
                }
                $this->assign('list', $list);
                $this->assign('page', $show);
            }
            $this->display('ding');
        } elseif ($status == 6) {
            $order = D('Order');
            if ($st == 1 || empty($st)) {
                $map = array('user_id' => $this->uid, 'status' => 1);
                $lists = $order->where($map)->order(array('order_id' => 'desc'))->select();
                $dianping = D('Goodsdianping')->where(array('user_id' => $this->uid))->select();
                $orders = array();
                foreach ($dianping as $key => $v) {
                    $orders[] = $v['order_id'];
                }
                foreach ($lists as $kk => $vv) {
                    if (in_array($vv['order_id'], $orders)) {
                        unset($lists[$kk]);
                    }
                }
                $count = count($lists);
                $Page = new Page($count, 10);
                $show = $Page->show();
                $list = array_slice($lists, $Page->firstRow, $Page->listRows);
                $shop_ids = $goods_ids = array();
                foreach ($list as $k => $val) {
                    if (!empty($val['shop_id'])) {
                        $shop_ids[$val['shop_id']] = $val['shop_id'];
                    }
                    $goods_ids[$val['goods_id']] = $val['goods_id'];
                }
                $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
                $this->assign('goods', D('Goods')->itemsByIds($goods_ids));
                $this->assign('list', $list);
                $this->assign('page', $show);
            } elseif ($st == 2) {
                $map = array('closed' => 0, 'user_id' => $this->uid);
                $count = D('Goodsdianping')->where($map)->count();
                $Page = new Page($count, 25);
                $show = $Page->show();
                $list = D('Goodsdianping')->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $order_ids = $shop_ids = $goods_ids = array();
                foreach ($list as $k => $val) {
                    $shop_ids[$val['shop_id']] = $val['shop_id'];
                    $order_ids[$val['order_id']] = $val['order_id'];
                    $goods_ids[$val['goods_id']] = $val['goods_id'];
                }
                if (!empty($shop_ids)) {
                    $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
                }
                if (!empty($goods_ids)) {
                    $this->assign('goodss', D('Goods')->itemsByIds($goods_ids));
                }
                if (!empty($order_ids)) {
                    $this->assign('pics', D('Goodsdianpingpics')->where(array('order_id' => array('IN', $order_ids)))->select());
                }
                foreach ($list as $key => $v) {
                    if (in_array($v['order_id'], $order_ids)) {
                        $list[$key]['pichave'] = 1;
                    }
                }
                $this->assign('list', $list);
                $this->assign('page', $show);
            }
            $this->display('mall');
        } elseif ($status == 5) {
            $Appointdianping = D('Appointdianping');
            $map = array('closed' => 0, 'user_id' => $this->uid);
            $count = $Appointdianping ->where($map)->count();
            $Page = new Page($count, 25);
            $show = $Page->show();
            $list = $Appointdianping->where($map)->order(array('dianping_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $appoint_ids = $shop_ids = array();
            foreach ($list as $k => $val) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
                $appoint_ids[$val['appoint_id']] = $val['appoint_id'];
				$order_ids[$val['order_id']] = $val['order_id'];
            }
            if (!empty($appoint_ids)) {
                $this->assign('appoint', D('Appoint')->itemsByIds($appoint_ids));
            }
            if (!empty($appoint_ids)) {
                $this->assign('pics', D('Appointdianpingpics')->where(array('order_id' => array('IN', $order_ids)))->select());
            }
            foreach ($list as $key => $v) {
                if (in_array($v['appoint_id'], $appoint_ids)) {
                    $list[$key]['pichave'] = 1;
                }
            }
            $this->assign('list', $list);
            $this->assign('page', $show);
            $this->display('appoint');
        }
    }
    public function tuandianping($order_id){
        $order_id = (int) $order_id;
        if (!($detail = D('Tuanorder')->find($order_id))) {
            $this->baoError('没有该套餐');
        } else {
            if ($detail['user_id'] != $this->uid) {
                $this->baoError('不要评价别人的套餐');
                die;
            }
        }
        if (D('Tuandianping')->check($order_id, $this->uid)) {
            $this->baoError('已经评价过了');
        }
        if ($this->_Post()) {
            $data = $this->checkFields($this->_post('data', false), array('score', 'cost', 'contents'));
            $data['user_id'] = $this->uid;
            $data['shop_id'] = $detail['shop_id'];
            $data['tuan_id'] = $detail['tuan_id'];
            $data['order_id'] = $order_id;
            $data['score'] = (int) $data['score'];
            if (empty($data['score'])) {
                $this->baoError('评分不能为空');
            }
            if ($data['score'] > 5 || $data['score'] < 1) {
                $this->baoError('评分为1-5之间的数字');
            }
            $data['cost'] = (int) $data['cost'];
            $data['contents'] = htmlspecialchars($data['contents']);
            if (empty($data['contents'])) {
                $this->baoError('评价内容不能为空');
            }
            if ($words = D('Sensitive')->checkWords($data['contents'])) {
                $this->baoError('评价内容含有敏感词：' . $words);
            }
            $data_tuan_dianping = $this->_CONFIG['mobile']['data_tuan_dianping'];
            $data['show_date'] = date('Y-m-d', NOW_TIME + $data_tuan_dianping * 86400);
            //15天生效
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            if (D('Tuandianping')->add($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val)) {
                        $local[] = $val;
                    }
                }
                if (!empty($local)) {
                    D('Tuandianpingpics')->upload($order_id, $local);
                }
                D('Tuanorder')->save(array('order_id' => $order_id, 'is_dainping' => 1));
                D('Users')->prestige($this->uid, 'dianping');
                D('Users')->updateCount($this->uid, 'ping_num');
                $this->baoSuccess('恭喜您点评成功!', U('order/index'));
            }
            $this->baoError('点评失败！');
        } else {
            $tuandetails = D('Tuan')->find($detail['tuan_id']);
            $this->assign('tuandetails', $tuandetails);
            $this->assign('order_id', $order_id);
            $this->display();
        }
    }
    public function tuandpedit($order_id){
        $order_id = (int) $order_id;
        $obj = D('Tuandianping');
        if ($this->_Post()) {
            if (!($detail = $obj->find($order_id))) {
                $this->baoError('请选择要编辑的套餐点评');
            }
            if (!($detail = $obj->find($order_id))) {
                $this->baoError('没有该套餐点评');
            } else {
                if ($detail['user_id'] != $this->uid) {
                    $this->baoError('不要编辑别人的套餐');
                }
                if ($detail['show_date'] < '$today 00:00:00') {
                    $this->baoError('点评已过期');
                }
            }
            $data = $this->checkFields($this->_post('data', false), array('score', 'cost', 'contents'));
            $data['user_id'] = $this->uid;
            $data['tuan_id'] = $detail['tuan_id'];
            $data['shop_id'] = $detail['shop_id'];
            $data['order_id'] = $order_id;
            $data['score'] = (int) $data['score'];
            if (empty($data['score'])) {
                $this->baoError('评分不能为空');
            }
            if ($data['score'] > 5 || $data['score'] < 1) {
                $this->baoError('评分为1-5之间的数字');
            }
            $data['cost'] = (int) $data['cost'];
            $data['contents'] = htmlspecialchars($data['contents']);
            if (empty($data['contents'])) {
                $this->baoError('评价内容不能为空');
            }
            if ($words = D('Sensitive')->checkWords($data['contents'])) {
                $this->baoError('评价内容含有敏感词：' . $words);
            }
            $data_tuan_dianping = $this->_CONFIG['mobile']['data_tuan_dianping'];
            $data['show_date'] = date('Y-m-d', NOW_TIME + $data_tuan_dianping * 86400);
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            if (false !== $obj->save($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val)) {
                        $local[] = $val;
                    }
                }
                if (!empty($local)) {
                    D('Tuandianpingpics')->upload($order_id, $local);
                }
                $this->baoSuccess('恭喜您编辑点评成功!', U('order/index'));
            }
            $this->baoError('点评编辑失败！');
        } else {
            $this->assign('detail', $obj->find($order_id));
            $this->assign('tuandetails', D('Tuan')->find($detail['tuan_id']));
            $this->assign('photos', D('Tuandianpingpics')->getPics($order_id));
            $this->display();
        }
    }
    //增加家政点评开始
    public function appointdianping($appoint_id,$appoint_order_id){
        $appoint_id = (int) $appoint_id;
		$appoint_order_id = (int) $appoint_order_id;
		
		$Appointdianping = D('Appointdianping');
        if (!($detail = D('Appoint')->find($appoint_id))) {
            $this->error('没有该家政');
        }
        if ($Appointdianping->check($appoint_order_id, $this->uid)) {
            $this->error('已经评价过了');
        }
        if ($this->_Post()) {
            $data = $this->checkFields($this->_post('data', false), array('score', 'cost', 'contents'));
            $data['user_id'] = $this->uid;
            $data['appoint_id'] = $appoint_id;
			$data['order_id'] = $appoint_order_id;
            $data['score'] = (int) $data['score'];
            if (empty($data['score'])) {
                $this->baoError('评分不能为空');
            }
            if ($data['score'] > 5 || $data['score'] < 1) {
                $this->baoError('评分为1-5之间的数字');
            }
            $data['cost'] = (int) $data['cost'];
            $data['contents'] = htmlspecialchars($data['contents']);
            if (empty($data['contents'])) {
                $this->baoError('评价内容不能为空');
            }
            if ($words = D('Sensitive')->checkWords($data['contents'])) {
                $this->baoError('评价内容含有敏感词：' . $words);
            }
            $data_appoint_dianping = $this->_CONFIG['mobile']['data_appoint_dianping'];
            $data['show_date'] = date('Y-m-d', NOW_TIME + $data_appoint_dianping * 86400);//15天生效
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            if ($dianping_id = $Appointdianping->add($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val)) {
                        $local[] = $val;
                    }
                }
                if (!empty($local)) {
                    D('Appointdianpingpics')->upload($dianping_id, $local,$appoint_order_id);
                }
                D('Users')->prestige($this->uid, 'dianping');
                D('Users')->updateCount($this->uid, 'ping_num');
                $this->baoSuccess('恭喜您点评成功!', U('appoint/index'));
            }
            $this->baoError('点评失败！');
        } else {
            $Appoint = D('Appoint')->find($detail['id']);
            $this->assign('appoint', $Appoint);
            $this->assign('appoint_id', $appoint_id);
			$this->assign('appoint_order_id', $appoint_order_id);
            $this->display();
        }
    }
}
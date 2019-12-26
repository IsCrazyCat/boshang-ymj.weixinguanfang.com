<?php
class DianpingAction extends CommonAction{
    public function index($shop_id){
        $shop_id = (int) $shop_id;
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->fengmiMsg('该商家不存在');
        }
        $cates = D('Shopcate')->fetchAll();
        $cate = $cates[$detail['cate_id']];
        $this->assign('cate', $cate);
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('score', 'd1', 'd2', 'd3', 'cost', 'contents'));
            $data['user_id'] = $this->uid;
            $data['shop_id'] = $shop_id;
            $data['score'] = (int) $data['score'];
            if ($data['score'] <= 0 || $data['score'] > 5) {
                $this->fengmiMsg('请选择评分');
            }
            $data['d1'] = (int) $data['d1'];
            if (empty($data['d1'])) {
                $this->fengmiMsg($cate['d1'] . '评分不能为空');
            }
            if ($data['d1'] > 5 || $data['d1'] < 1) {
                $this->fengmiMsg($cate['d1'] . '评分不能为空');
            }
            $data['d2'] = (int) $data['d2'];
            if (empty($data['d2'])) {
                $this->fengmiMsg($cate['d2'] . '评分不能为空');
            }
            if ($data['d2'] > 5 || $data['d2'] < 1) {
                $this->fengmiMsg($cate['d2'] . '评分不能为空');
            }
            $data['d3'] = (int) $data['d3'];
            if (empty($data['d3'])) {
                $this->fengmiMsg($cate['d3'] . '评分不能为空');
            }
            if ($data['d3'] > 5 || $data['d3'] < 1) {
                $this->fengmiMsg($cate['d3'] . '评分不能为空');
            }
            $data['cost'] = (int) $data['cost'];
            $data['contents'] = htmlspecialchars($data['contents']);
            if (empty($data['contents'])) {
                $this->fengmiMsg('不说点什么么');
            }
            $data['create_time'] = NOW_TIME;
            $data['show_date'] = date('Y-m-d', NOW_TIME + ($this->_CONFIG['mobile']['data_shop_dianping'] * 86400));
            $data['create_ip'] = get_client_ip();
            $obj = D('Shopdianping');
            if ($dianping_id = $obj->add($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val)) {
                        $local[] = $val;
                    }
                }
                if (!empty($local)) {
                    D('Shopdianpingpics')->upload($dianping_id, $data['shop_id'], $local);
                }
                D('Shop')->updateCount($shop_id, 'score_num');
                D('Users')->updateCount($this->uid, 'ping_num');
                D('Shopdianping')->updateScore($shop_id);
				D('Users')->prestige($this->uid, 'dianping_shop');
                $this->fengmiMsg('评价成功', U('Wap/shop/detail', array('shop_id' => $shop_id)));
            }
            $this->fengmiMsg('操作失败！');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    //小灰灰增加
    public function tuandianping($order_id){
        $order_id = (int) $order_id;
        if (!($detail = D('Tuanorder')->find($order_id))) {
            $this->fengmiMsg('没有该套餐');
        } else {
            if ($detail['user_id'] != $this->uid) {
                $this->fengmiMsg('不要评价别人的套餐');
                die;
            }
        }
        if (D('Tuandianping')->check($order_id, $this->uid)) {
            $this->fengmiMsg('已经评价过了');
        }
        if ($this->_Post()) {
            $data = $this->checkFields($this->_post('data', false), array('score', 'cost', 'contents'));
            $data['user_id'] = $this->uid;
            $data['shop_id'] = $detail['shop_id'];
            $data['tuan_id'] = $detail['tuan_id'];
            $data['order_id'] = $order_id;
            $data['score'] = (int) $data['score'];
            if (empty($data['score'])) {
                $this->fengmiMsg('评分不能为空');
            }
            if ($data['score'] > 5 || $data['score'] < 1) {
                $this->fengmiMsg('评分为1-5之间的数字');
            }
            $data['cost'] = (int) $data['cost'];
            $data['contents'] = htmlspecialchars($data['contents']);
            if (empty($data['contents'])) {
                $this->fengmiMsg('评价内容不能为空');
            }
            if ($words = D('Sensitive')->checkWords($data['contents'])) {
                $this->fengmiMsg('评价内容含有敏感词：' . $words);
            }
            $data['show_date'] = date('Y-m-d', NOW_TIME + 15 * 86400);
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
                $this->fengmiMsg('恭喜您点评成功!', U('mcenter/tuan/index'));
            }
            $this->fengmiMsg('点评失败！');
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
                $this->fengmiMsg('请选择要编辑的套餐点评');
            }
            if (!($detail = $obj->find($order_id))) {
                $this->fengmiMsg('没有该套餐点评');
            } else {
                if ($detail['user_id'] != $this->uid) {
                    $this->fengmiMsg('不要编辑别人的套餐');
                }
                if ($detail['show_date'] < '$today 00:00:00') {
                    $this->fengmiMsg('点评已过期');
                }
            }
            $data = $this->checkFields($this->_post('data', false), array('score', 'cost', 'contents'));
            $data['user_id'] = $this->uid;
            $data['tuan_id'] = $detail['tuan_id'];
            $data['shop_id'] = $detail['shop_id'];
            $data['order_id'] = $order_id;
            $data['score'] = (int) $data['score'];
            if (empty($data['score'])) {
                $this->fengmiMsg('评分不能为空');
            }
            if ($data['score'] > 5 || $data['score'] < 1) {
                $this->fengmiMsg('评分为1-5之间的数字');
            }
            $data['cost'] = (int) $data['cost'];
            $data['contents'] = htmlspecialchars($data['contents']);
            if (empty($data['contents'])) {
                $this->fengmiMsg('评价内容不能为空');
            }
            if ($words = D('Sensitive')->checkWords($data['contents'])) {
                $this->fengmiMsg('评价内容含有敏感词：' . $words);
            }
            $data['show_date'] = $detail['show_date'];
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
                $this->fengmiMsg('恭喜您编辑点评成功!', U('members/order'));
            }
            $this->fengmiMsg('点评编辑失败！');
        } else {
            $this->assign('detail', $obj->find($order_id));
            $this->assign('tuandetails', D('Tuan')->find($detail['tuan_id']));
            $this->assign('photos', D('Tuandianpingpics')->getPics($order_id));
            $this->display();
        }
    }
}
<?php
class MalldianpingAction extends CommonAction {

    private $create_fields = array('user_id', 'reply','shop_id', 'order_id', 'score', 'cost', 'contents', 'show_date');
    private $edit_fields = array('user_id', 'reply','shop_id', 'order_id', 'score', 'cost', 'contents', 'show_date');

    public function index() {
        $Goodsdianping = D('Goodsdianping');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($order_id = (int) $this->_param('order_id')) {
            $map['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
        }

        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $user = D('Users')->find($user_id);
            $this->assign('nickname', $user['nickname']);
            $this->assign('user_id', $user_id);
        }
        $count = $Goodsdianping->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Goodsdianping->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($shop_ids)) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Goodsdianping');
            if ($obj->add($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val))
                        $local[] = $val;
                }
                if (!empty($local))
                    D('Goodsdianpingpics')->upload($data['order_id'], $local);
                $this->baoSuccess('添加成功', U('malldianping/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
		 $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $data['order_id'] = (int) $data['order_id'];
        if (empty($data['order_id'])) {
            $this->baoError('商城订单不能为空');
        }
        if (!$order = D('Order')->find($data['order_id'])) {
            $this->baoError('商城订单不存在');
        }
        $data['shop_id'] = (int) $order['shop_id'];
        $data['goods_id'] = (int) $order['goods_id'];
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
        $data['show_date'] = htmlspecialchars($data['show_date']);
        if (empty($data['show_date'])) {
            $this->baoError('生效日期不能为空');
        }
        if (!isDate($data['show_date'])) {
            $this->baoError('生效日期格式不正确');
        }
        $data['reply'] = htmlspecialchars($data['reply']);
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
		

        return $data;
    }

       public function edit($order_id = 0) {
        if ($order_id = (int) $order_id) {
            $obj = D('Goodsdianping');
            if (!$detail = $obj->find($order_id)) {
                $this->baoError('请选择要编辑的商城点评1');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['order_id'] = $order_id;
                if (false !== $obj->save($data)) {
                    $photos = $this->_post('photos', false);
                    $local = array();
                    foreach ($photos as $val) {
                        if (isImage($val))
                            $local[] = $val;
                    }
                    if (!empty($local))
                        D('Goodsdianpingpics')->upload($order_id, $local);
						D('Users')->prestige($this->uid, 'dianping');
                        D('Users')->updateCount($this->uid, 'ping_num');
					
                    $this->baoSuccess('操作成功', U('malldianping/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('photos', D('Goodsdianpingpics')->getPics($order_id));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商城点评2');
            
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
		$data['order_id'] = $order_id;
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
		 $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $data['score'] = (int) $data['score'];
        if (empty($data['score'])) {
            $this->baoError('评分不能为空');
        }
        $data['cost'] = (int) $data['cost'];
        $data['contents'] = htmlspecialchars($data['contents']);
        if (empty($data['contents'])) {
            $this->baoError('评价内容不能为空');
        }
        $data['show_date'] = htmlspecialchars($data['show_date']);
        if (empty($data['show_date'])) {
            $this->baoError('生效日期不能为空');
        }
        if (!isDate($data['show_date'])) {
            $this->baoError('生效日期格式不正确');
        }
        $data['reply'] = htmlspecialchars($data['reply']);
        $photos = $this->_post('photos', false);
        $local = array();
        foreach ($photos as $val) {
            if (isImage($val))
                $local[] = $val;
        }
        $data['photos'] = json_encode($local);
        return $data;
    }

	
	 public function delete($order_id = 0) {
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Goodsdianping');
            $obj->save(array('order_id' => $order_id, 'closed' => 1));
            $this->baoSuccess('删除成功1！', U('malldianping/index'));
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                $obj = D('Goodsdianping');
                foreach ($order_id as $id) {
                    $obj->save(array('order_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('批量删除成功！', U('malldianping/index'));
            }
            $this->baoError('请选择要删除的商城点评');
        }
    }

}

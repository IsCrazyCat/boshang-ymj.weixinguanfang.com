<?php
class AppointdianpingAction extends CommonAction {

    private $create_fields = array('user_id', 'reply','order_id', 'appoint_id', 'score', 'contents', 'show_date');
    private $edit_fields = array('user_id', 'reply', 'order_id','appoint_id', 'score', 'contents', 'show_date');

    public function index() {
        $Appointdianping = D('Appointdianping');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0);
       
        if ($dianping_id = (int) $this->_param('dianping_id')) {
            $map['dianping_id'] = $dianping_id;
            $this->assign('dianping_id', $dianping_id);
        }

        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $user = D('Users')->find($user_id);
            $this->assign('nickname', $user['nickname']);
            $this->assign('user_id', $user_id);
        }
		
        $count = $Appointdianping->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Appointdianping->where($map)->order(array('dianping_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
            $obj = D('Appointdianping');
            if ($dianping_id = $obj->add($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val))
                        $local[] = $val;
                }
                if (!empty($local))
                    D('Appointdianpingpics')->upload($dianping_id, $local,$data['order_id']);
                $this->baoSuccess('添加成功', U('Appointdianping/index'));
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
        $data['order_id'] = (int) $data['order_id'];
        if (empty($data['order_id'])) {
            $this->baoError('家政订单号不能为空');
        }
        if (!$order = D('Appointorder')->find($data['order_id'])) {
            $this->baoError('家政订单不存在');
        }
		$data['appoint_id'] = $order['appoint_id'];
        $data['score'] = (int) $data['score'];
        if (empty($data['score'])) {
            $this->baoError('评分不能为空');
        }
        if ($data['score'] > 5 || $data['score'] < 1) {
            $this->baoError('评分为1-5之间的数字');
        }

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

       public function edit($dianping_id = 0) {
        if ($dianping_id = (int) $dianping_id) {
            $obj = D('Appointdianping');
            if (!$detail = $obj->find($dianping_id)) {
                $this->baoError('请选择要编辑的家政点评');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['dianping_id'] = $dianping_id;
                if (false !== $obj->save($data)) {
                    $photos = $this->_post('photos', false);
                    $local = array();
                    foreach ($photos as $val) {
                        if (isImage($val))
                            $local[] = $val;
                    }
                    if (!empty($local))
                        D('Appointdianpingpics')->upload($dianping_id, $local,$detail['order_id']);
						D('Users')->prestige($this->uid, 'dianping');
                        D('Users')->updateCount($this->uid, 'ping_num');
					
                    $this->baoSuccess('操作成功', U('Appointdianping/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('photos', D('Appointdianpingpics')->getPics($dianping_id));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的家政点评');
            
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
		$data['dianping_id'] = $dianping_id;
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['score'] = (int) $data['score'];
        if (empty($data['score'])) {
            $this->baoError('评分不能为空');
        }

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


	
	 public function delete($id = 0) {
        if (is_numeric($id) && ($id = (int) $id)) {
            $obj = D('Appointdianping');
            $obj->save(array('id' => $id, 'closed' => 1));
            $this->baoSuccess('删除成功2！', U('Appointdianping/index'));
        } else {
            $id = $this->_post('id', false);
            if (is_array($id)) {
                $obj = D('Appointdianping');
                foreach ($id as $id) {
                    $obj->save(array('id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('Appointdianping/index'));
            }
            $this->baoError('请选择要删除的设计师点评');
        }
    }

}

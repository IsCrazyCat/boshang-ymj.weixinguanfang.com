<?php
class PhotoAction extends CommonAction{
	private $photo_create_fields = array('title', 'photo', 'orderby');
	
    public function index(){
        $Shoppic = D('Shoppic');
        $map = array('shop_id' => $this->shop_id);
        $list = $Shoppic->where($map)->order(array('orderby' => 'desc'))->select();
        $this->assign('list', $list);
        $this->assign('sig', md5($this->shop_id . C('AUTH_KEY')));
        $this->display();
    }
    public function update(){
        $title = $this->_post('title', false);
        $orderby = $this->_post('orderby', false);
        $Shoppic = D('Shoppic');
        $map = array('shop_id' => $this->shop_id);
        if ($photo_list = $Shoppic->where($map)->order(array('orderby' => 'desc'))->select()) {
            foreach ($photo_list as $k => $val) {
                $data = array('pic_id' => (int) $val['pic_id'], 'title' => htmlspecialchars($title[$val['pic_id']]), 'orderby' => $orderby[$val['pic_id']]);
                $Shoppic->save($data);
            }
        }
        $this->baoSuccess('更新成功！', U('photo/index'));
    }
	
	 //传图
    public function photo_create() {
        if ($this->isPost()) {
            $data = $this->photo_createCheck();
            $obj = D('Shoppic');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功，请等待网站管理员审核', U('photo/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
	
   private function photo_createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->photo_create_fields);
        $data['shop_id'] = $this->shop_id;
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传环境图图片');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('环境图图片格式不正确');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
    public function delete(){
        $pic_id = (int) $this->_get('pic_id');
        $obj = D('Shoppic');
        $detail = $obj->find($pic_id);
        if (!empty($detail) && $detail['shop_id'] == $this->shop_id) {
            $obj->delete($pic_id);
            $this->baoSuccess('删除成功！', U('photo/index'));
        }
        $this->baoError('你懂的');
    }
    public function banner(){
        $Shopbanner = D('Shopbanner');
        $map = array('shop_id' => $this->shop_id, 'is_mobile' => 1);
        $list = $Shopbanner->where($map)->order(array('orderby' => 'desc'))->select();
        $this->assign('list', $list);
        $this->assign('sig', md5($this->shop_id . C('AUTH_KEY')));
        $this->display();
    }
    public function banner1(){
        $Shopbanner = D('Shopbanner');
        $map = array('shop_id' => $this->shop_id, 'is_mobile' => 0);
        $list = $Shopbanner->where($map)->order(array('orderby' => 'desc'))->select();
        $this->assign('list', $list);
        $this->assign('sig', md5($this->shop_id . C('AUTH_KEY')));
        $this->display();
    }
    public function updatebanner(){
        $title = $this->_post('title', false);
        $orderby = $this->_post('orderby', false);
        $obj = D('Shopbanner');
        foreach ($orderby as $k => $val) {
            $data = array();
            $val = (int) $val;
            $detail = $obj->find($k);
            if (!empty($detail) && $detail['shop_id'] == $this->shop_id) {
                $data = array('banner_id' => (int) $k, 'title' => htmlspecialchars($title[$k]), 'orderby' => $val);
                $obj->save($data);
            }
        }
        $this->baoSuccess('更新成功！', U('photo/banner'));
    }
    public function deletebanner(){
        $banner_id = (int) $this->_get('banner_id');
        $obj = D('Shopbanner');
        $detail = $obj->find($banner_id);
        if (!empty($detail) && $detail['shop_id'] == $this->shop_id) {
            $obj->delete($banner_id);
            $this->baoSuccess('删除成功！', U('photo/banner'));
        }
        $this->baoError('你懂的');
    }
    public function updatebanner1(){
        $title = $this->_post('title', false);
        $orderby = $this->_post('orderby', false);
        $obj = D('Shopbanner');
        foreach ($orderby as $k => $val) {
            $data = array();
            $val = (int) $val;
            $detail = $obj->find($k);
            if (!empty($detail) && $detail['shop_id'] == $this->shop_id) {
                $data = array('banner_id' => (int) $k, 'title' => htmlspecialchars($title[$k]), 'orderby' => $val);
                $obj->save($data);
            }
        }
        $this->baoSuccess('更新成功！', U('photo/banner1'));
    }
    public function deletebanner1(){
        $banner_id = (int) $this->_get('banner_id');
        $obj = D('Shopbanner');
        $detail = $obj->find($banner_id);
        if (!empty($detail) && $detail['shop_id'] == $this->shop_id) {
            $obj->delete($banner_id);
            $this->baoSuccess('删除成功！', U('photo/banner1'));
        }
        $this->baoError('你懂的');
    }
}
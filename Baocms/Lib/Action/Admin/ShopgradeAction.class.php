<?php
class ShopgradeAction extends CommonAction{
    private $create_fields = array('grade_name', 'photo', 'money', 'gold', 'content', 'orderby');
    private $edit_fields = array('grade_name', 'photo', 'money', 'gold', 'content', 'orderby');
	private $edit_fields_jurisdiction = array('is_mall', 'is_tuan', 'is_ele','is_news', 'is_hotel', 'is_booking', 'is_farm', 'is_appoint', 'is_huodong', 'is_coupon', 'is_life', 'is_jifen', 'is_cloud', 'is_mall_num', 'is_tuan_num', 'is_ele_num','is_news_num', 'is_hotel_num', 'is_booking_num', 'is_farm_num', 'is_appoint_num', 'is_huodong_num', 'is_coupon_num', 'is_life_num', 'is_jifen_num', 'is_cloud_num');
    public function index(){
        $Shopgrade = D('Shopgrade');
        import('ORG.Util.Page');
        $map = array('closed'=>0);
        $count = $Shopgrade->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Shopgrade->where($map)->order(array('orderby' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
            $list[$k]['shop_count'] = $Shopgrade->get_shop_count($val['grade_id']);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Shopgrade');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('Shopgrade/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['grade_name'] = htmlspecialchars($data['grade_name']);
        if (empty($data['grade_name'])) {
            $this->baoError('等级名称不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('等级图标不能为空');
        }
        $data['money'] = ((int) $data['money'])*100;
		if (empty($data['money'])) {
            $this->baoError('单独购买需要多少金额不能为空');
        }
        $data['gold'] = ((int) $data['gold'])*100;
		if (empty($data['gold'])) {
             $this->baoError('自动升级需要的商户资金不能为空');
        }
		$data['content'] = htmlspecialchars($data['content']);
		if (empty($data['content'])) {
            $this->baoError('还是写下等级介绍吧');
        }
		$data['orderby'] = (int) $data['orderby'];
		if (empty($data['orderby'])) {
            $this->baoError('等级权重不能为空');
        }
		if($detail = D('Shopgrade')->where(array('orderby'=>$data['orderby']))->find()) {
            $this->baoError('等级权重重复，请调整后在提交');
        }
        return $data;
    }
    public function edit($grade_id = 0){
        if ($grade_id = (int) $grade_id) {
            $obj = D('Shopgrade');
            if (!($detail = $obj->find($grade_id))) {
                $this->baoError('请选择要编辑的商家等级');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['grade_id'] = $grade_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('Shopgrade/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商家等级');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['grade_name'] = htmlspecialchars($data['grade_name']);
        if (empty($data['grade_name'])) {
            $this->baoError('等级名称不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('等级图标不能为空');
        }
        $data['money'] = ((int) $data['money'])*100;
		if (empty($data['money'])) {
            $this->baoError('单独购买需要多少金额不能为空');
        }
        $data['gold'] = ((int) $data['gold'])*100;
		if (empty($data['gold'])) {
            $this->baoError('自动升级需要的商户资金不能为空');
        }
		$data['content'] = htmlspecialchars($data['content']);
		if (empty($data['content'])) {
            $this->baoError('还是写下等级介绍吧');
        }
		$data['orderby'] = (int) $data['orderby'];
		if (empty($data['orderby'])) {
            $this->baoError('等级权重不能为空');
        }
		if($detail = D('Shopgrade')->where(array('orderby'=>$data['orderby']))->find()) {
            $this->baoError('等级权重重复，请调整后在提交');
        }
        return $data;
    }
    public function delete($grade_id = 0){
        if (is_numeric($grade_id) && ($grade_id = (int) $grade_id)) {
            $obj = D('Shopgrade');
            $obj->save(array('grade_id' => $grade_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('Shopgrade/index'));
        } else {
            $grade_id = $this->_post('grade_id', false);
            if (is_array($grade_id)) {
                $obj = D('Shopgrade');
                foreach ($grade_id as $id) {
                    $obj->save(array('grade_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('Shopgrade/index'));
            }
            $this->baoError('请选择要删除的商家等级');
        }
    }
	
	public function edit_jurisdiction($grade_id = 0){
		if ($grade_id = (int) $grade_id) {
            $obj = D('Shopgrade');
            if (!($detail = $obj->find($grade_id))) {
                $this->baoError('请选择要编辑的商家等级');
            }
            if ($this->isPost()) {
                $data = $this->editCheck_jurisdiction();
                $data['grade_id'] = $grade_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('Shopgrade/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商家等级');
        }
    }
	
	 private function editCheck_jurisdiction(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields_jurisdiction);
        $data['is_mall'] = (int) $data['is_mall'];
		$data['is_tuan'] = (int) $data['is_tuan'];
		$data['is_ele'] = (int) $data['is_ele'];
		$data['is_news'] = (int) $data['is_news'];
		$data['is_hotel'] = (int) $data['is_hotel'];
		$data['is_booking'] = (int) $data['is_booking'];
		$data['is_farm'] = (int) $data['is_farm'];
		$data['is_appoint'] = (int) $data['is_appoint'];
		$data['is_huodong'] = (int) $data['is_huodong'];
		$data['is_coupon'] = (int) $data['is_coupon'];
		$data['is_life'] = (int) $data['is_life'];
		$data['is_jifen'] = (int) $data['is_jifen'];
		$data['is_cloud'] = (int) $data['is_cloud'];
		$data['is_mall_num'] = (int) $data['is_mall_num'];
		$data['is_tuan_num'] = (int) $data['is_tuan_num'];
		$data['is_ele_num'] = (int) $data['is_ele_num'];
		$data['is_news_num'] = (int) $data['is_news_num'];
		$data['is_hotel_num'] = (int) $data['is_hotel_num'];
		$data['is_booking_num'] = (int) $data['is_booking_num'];
		$data['is_farm_num'] = (int) $data['is_farm_num'];
		$data['is_appoint_num'] = (int) $data['is_appoint_num'];
		$data['is_huodong_num'] = (int) $data['is_huodong_num'];
		$data['is_coupon_num'] = (int) $data['is_coupon_num'];
		$data['is_life_num'] = (int) $data['is_life_num'];
		$data['is_jifen_num'] = (int) $data['is_jifen_num'];
		$data['is_cloud_num'] = (int) $data['is_cloud_num'];
        return $data;
    }
}
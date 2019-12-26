<?php
class BranchAction extends CommonAction{
    private $create_fields = array('name','cate_id', 'city_id', 'area_id', 'business_id', 'photo','addr','business_time','lng', 'lat', 'orderby', 'telephone','details');
    private $edit_fields = array('name','cate_id', 'city_id', 'area_id', 'business_id',  'photo','addr','business_time', 'lng', 'lat', 'orderby', 'telephone','details');
    public function _initialize(){
        parent::_initialize();
        $this->assign('city', D('City')->fetchAll());
        $this->assign('area', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
    }
    public function index(){
        $branch = D('Shopbranch');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('closed' => 0, 'shop_id' => $this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|addr'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $branch->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $branch->where($map)->order(array('orderby' => 'asc', 'branch_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Shopbranch');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('branch/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('分店名称不能为空');
        }
        $data['shop_id'] = $this->shop_id;
		$shops = D('Shop')->where(array('shop_id'=>$this->shop_id))->find();
		$data['cate_id'] = $shops['cate_id'];
		if (empty($data['cate_id'])) {
           $this->baoError('分类ID出现错误，请检查总店分类');
        }
		
		$data['photo'] = $this->_post('photo', 'htmlspecialchars');
        if (empty($data['photo'])) {
           $this->baoError('请上分店LOGO');
        }
        if (!isImage($data['photo'])) {
          $this->baoError('分店LOGO格式不正确');
        }
		
			
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('请选择城市');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('请选择地区');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->baoError('请选择商圈');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('分店地址不能为空');
        }
		$data['business_time'] = htmlspecialchars($data['business_time']);
       
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
            $this->baoError('分店坐标不能为空');
        }
        $data['telephone'] = htmlspecialchars($data['telephone']);
        if (empty($data['telephone'])) {
            $this->baoError('电话不能为空');
        }
        if (!isMobile($data['telephone']) && !isPhone($data['telephone'])) {
            $this->baoError('请输入正确的手机号码');
        }
        $data['orderby'] = (int) $data['orderby'];
		$data['details'] = $this->_post('details', 'SecurityEditorHtml');
        if ($words = D('Sensitive')->checkWords($data['details'])) {
           $this->baoError('商家介绍含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 1;
        return $data;
    }
    public function edit($branch_id = 0){
        if ($branch_id = (int) $branch_id) {
            $obj = D('Shopbranch');
            if (!($detail = $obj->find($branch_id))) {
                $this->error('请选择要编辑的分店');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('请不要试图越权操作其他人的内容');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['branch_id'] = $branch_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('branch/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的分店');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('分店名称不能为空');
        }
        $data['shop_id'] = $this->shop_id;
		//二开增加
		$shops = D('Shop')->where(array('shop_id'=>$this->shop_id))->find();
		$data['cate_id'] = $shops['cate_id'];
		if (empty($data['cate_id'])) {
           $this->baoError('分类ID出现错误，请检查总店分类');
        }
		
		$data['photo'] = $this->_post('photo', 'htmlspecialchars');
        if (empty($data['photo'])) {
           $this->baoError('请上分店LOGO');
        }
        if (!isImage($data['photo'])) {
          $this->baoError('分店LOGO格式不正确');
        }
		
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('请选择城市');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('请选择地区');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->baoError('请选择商圈');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('分店地址不能为空');
        }
		$data['business_time'] = htmlspecialchars($data['business_time']);
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
            $this->baoError('分店坐标不能为空');
        }
        $data['telephone'] = htmlspecialchars($data['telephone']);
        if (empty($data['telephone'])) {
            $this->baoError('电话不能为空');
        }
        if (!isMobile($data['telephone']) && !isPhone($data['telephone'])) {
            $this->baoError('请输入正确的手机号码');
        }
		$data['details'] = $this->_post('details', 'SecurityEditorHtml');
        if ($words = D('Sensitive')->checkWords($data['details'])) {
           $this->baoError('商家介绍含有敏感词：' . $words);
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function delete($branch_id = 0){
        if (is_numeric($branch_id) && ($branch_id = (int) $branch_id)) {
            $obj = D('Shopbranch');
            if (!($detail = $obj->find($branch_id))) {
                $this->error('请选择要删除的分店');
            }
            if ($detail['closed'] == 1) {
                $this->error('该分店不存在');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('请不要试图越权操作其他人的内容');
            }
            $obj->save(array('branch_id' => $branch_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('branch/index'));
        } else {
            $this->baoError('请选择要删除的分店');
        }
    }
    public function manage($branch_id = 0){
        if ($branch_id = (int) $branch_id) {
            $obj = D('Shopbranch');
            if (!($detail = $obj->find($branch_id))) {
                $this->error('请选择要设置的分店');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('请不要试图越权操作其他人的内容');
            }
            if ($this->isPost()) {
                $data['password'] = htmlspecialchars($_POST['password']);
                //$res = $obj->where(array('shop_id'=>$this->shop_id,'password'=>$data['password']))->find();
                //if(!empty($res)){
                //    $this->baoError('该账户已存在');
                //}
                if (empty($data['password'])) {
                    $this->baoError('口令不能为空');
                }
                $data['branch_id'] = $branch_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('branch/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要设置的分店');
        }
    }
}
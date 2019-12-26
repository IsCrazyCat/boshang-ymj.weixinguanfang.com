<?php
class ShopAction extends CommonAction{
    private $create_fields = array('user_id', 'cate_id', 'grade_id','city_id', 'area_id', 'business_id', 'shop_name', 'logo', 'mobile', 'photo', 'addr', 'tel', 'extension', 'contact', 'tags', 'near', 'is_pei', 'business_time', 'delivery_time', 'orderby', 'lng', 'lat', 'price', 'recognition','panorama_url');
    private $edit_fields = array('user_id', 'cate_id','grade_id','city_id', 'area_id', 'business_id', 'shop_name', 'mobile', 'logo', 'photo', 'addr', 'tel', 'extension', 'contact', 'tags', 'near', 'business_time', 'delivery_time', 'is_pei', 'orderby', 'lng', 'lat', 'price', 'is_ding', 'recognition','panorama_url', 'apiKey', 'mKey', 'partner', 'machine_code', 'service', 'service_audit', 'is_ele_print', 'is_tuan_print', 'is_goods_print', 'is_booking_print','is_appoint_print','service_audit');
	
	public function _initialize(){
        parent::_initialize();
        $this->assign('grades',$grades = D('Shopgrade')->where(array('closed'=>0))->select());//哈土豆二开增加商家等级
    }
    public function index(){
        $Shop = D('Shop');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Shopcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        $count = $Shop->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Shop->order(array('shop_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function apply()
    {
        $Shop = D('Shop');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'audit' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Shopcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        $count = $Shop->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Shop->order(array('shop_id' => 'asc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create()
    {
        if ($this->isPost()) {
            $data2 = $data = $this->createCheck();
            $obj = D('Shop');
            $details = $this->_post('details', 'SecurityEditorHtml');
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->baoError('商家介绍含有敏感词：' . $words);
            }
            $bank = $this->_post('bank', 'htmlspecialchars');
            unset($data['near'], $data['price'], $data['business_time'], $data['delivery_time']);
            if ($shop_id = $obj->add($data)) {
                $wei_pic = D('Weixin')->getCode($shop_id, 1);
                $ex = array('wei_pic' => $wei_pic, 'details' => $details, 'bank' => $bank, 'near' => $data2['near'], 'price' => $data2['price'], 'business_time' => $data2['business_time'], 'delivery_time' => $data2['delivery_time']);
                D('Shopdetails')->upDetails($shop_id, $ex);
                $this->baoSuccess('添加成功', U('shop/apply'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Shopcate')->fetchAll());
            $this->assign('business', D('Business')->fetchAll());
            $this->display();
        }
    }
    public function select()
    {
        $Shop = D('Shop');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0, 'audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Shopcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        $count = $Shop->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Shop->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('管理者不能为空');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
		$data['grade_id'] = (int) $data['grade_id'];
        if (empty($data['grade_id'])) {
            $this->baoError('商家等级不能为空');
        }
        $data['city_id'] = (int) $data['city_id'];
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('所在区域不能为空');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->baoError('所在商圈不能为空');
        }
        $data['shop_name'] = htmlspecialchars($data['shop_name']);
        if (empty($data['shop_name'])) {
            $this->baoError('商铺名称不能为空');
        }
        $data['logo'] = htmlspecialchars($data['logo']);
        if (empty($data['logo'])) {
            $this->baoError('请上传商铺LOGO');
        }
        if (!isImage($data['logo'])) {
            $this->baoError('商铺LOGO格式不正确');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传店铺缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('店铺缩略图格式不正确');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('店铺地址不能为空');
        }
        $data['tel'] = htmlspecialchars($data['tel']);
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['tel']) && empty($data['mobile'])) {
            $this->baoError('店铺电话不能为空');
        }
        $data['extension'] = htmlspecialchars($data['extension']);
        $data['contact'] = htmlspecialchars($data['contact']);
        $data['tags'] = str_replace(',', '，', htmlspecialchars($data['tags']));
        $data['near'] = htmlspecialchars($data['near']);
        $data['business_time'] = htmlspecialchars($data['business_time']);
        $data['orderby'] = (int) $data['orderby'];
		$data['panorama_url'] = htmlspecialchars($data['panorama_url']);
        $data['price'] = (int) $data['price'];
        $data['is_pei'] = 1; //写死为1 不开通配送功能
        $data['recognition'] = (int) $data['recognition'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['audit'] = 1;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($shop_id = 0)
    {
        if ($shop_id = (int) $shop_id) {
            $obj = D('Shop');
            if (!($detail = $obj->find($shop_id))) {
                $this->baoError('请选择要编辑的商家');
            }
            if ($this->isPost()) {
                $data = $this->editCheck($shop_id);
                $data['shop_id'] = $shop_id;
                $details = $this->_post('details', 'SecurityEditorHtml');
                if ($words = D('Sensitive')->checkWords($details)) {
                    $this->baoError('商家介绍含有敏感词：' . $words);
                }
                $bank = $this->_post('bank', 'htmlspecialchars');
                $shopdetails = D('Shopdetails')->find($shop_id);
                $ex = array('details' => $details, 'bank' => $bank, 'near' => $data['near'], 'price' => $data['price'], 'business_time' => $data['business_time']);
                if (!empty($shopdetails['wei_pic'])) {
                    if (true !== strpos($shopdetails['wei_pic'], 'https://mp.weixin.qq.com/')) {
                        $wei_pic = D('Weixin')->getCode($shop_id, 1);
                        $ex['wei_pic'] = $wei_pic;
                    }
                } else {
                    $wei_pic = D('Weixin')->getCode($shop_id, 1);
                    $ex['wei_pic'] = $wei_pic;
                }
                unset($data['near'], $data['price'], $data['business_time']);
                if (false !== $obj->save($data)) {
                    D('Shopdetails')->upDetails($shop_id, $ex);
                    $this->baoSuccess('操作成功', U('shop/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('areas', D('Area')->fetchAll());
                $this->assign('cates', D('Shopcate')->fetchAll());
                $this->assign('business', D('Business')->fetchAll());
                $this->assign('ex', D('Shopdetails')->find($shop_id));
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商家');
        }
    }
    private function editCheck($shop_id) {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('管理者不能为空');
        }
        $shop = D('Shop')->find(array('where' => array('user_id' => $data['user_id'])));
        if (!empty($shop) && $shop['shop_id'] != $shop_id) {
            $this->baoError('该管理者已经拥有商铺了');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
		$data['grade_id'] = (int) $data['grade_id'];
        if (empty($data['grade_id'])) {
            $this->baoError('商家等级不能为空');
        }
        $data['city_id'] = (int) $data['city_id'];
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('所在区域不能为空');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->baoError('所在商圈不能为空');
        }
        $data['shop_name'] = htmlspecialchars($data['shop_name']);
        if (empty($data['shop_name'])) {
            $this->baoError('商铺名称不能为空');
        }
        $data['logo'] = htmlspecialchars($data['logo']);
        if (empty($data['logo'])) {
            $this->baoError('请上传商铺LOGO');
        }
        if (!isImage($data['logo'])) {
            $this->baoError('商铺LOGO格式不正确');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传店铺缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('店铺缩略图格式不正确');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('店铺地址不能为空');
        }
        $data['tel'] = htmlspecialchars($data['tel']);
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['tel']) && empty($data['mobile'])) {
            $this->baoError('店铺电话不能为空');
        }
        $data['contact'] = htmlspecialchars($data['contact']);
        $data['tags'] = htmlspecialchars($data['tags']);
        $data['near'] = htmlspecialchars($data['near']);
        $data['business_time'] = htmlspecialchars($data['business_time']);
        $data['orderby'] = (int) $data['orderby'];
		$data['panorama_url'] = htmlspecialchars($data['panorama_url']);
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['price'] = (int) $data['price'];
        $data['is_pei'] = 1; //写死为1 不开通配送功能
        $data['apiKey'] = htmlspecialchars($data['apiKey']);
        $data['mKey'] = htmlspecialchars($data['mKey']);
        $data['partner'] = htmlspecialchars($data['partner']);
        $data['machine_code'] = htmlspecialchars($data['machine_code']);
        $data['service'] = $data['service'];
        $data['service_audit'] = (int) $data['service_audit'];
        $data['is_ele_print'] = (int) $data['is_ele_print'];
        $data['is_tuan_print'] = (int) $data['is_tuan_print'];
        $data['is_goods_print'] = (int) $data['is_goods_print'];
        $data['is_booking_print'] = (int) $data['is_booking_print'];
		$data['is_appoint_print'] = (int) $data['is_appoint_print'];
        return $data;
    }
    public function delete($shop_id = 0) {
        if (is_numeric($shop_id) && ($shop_id = (int) $shop_id)) {
            $obj = D('Shop');
            $obj->save(array('shop_id' => $shop_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('shop/index'));
        } else {
            $shop_id = $this->_post('shop_id', false);
            if (is_array($shop_id)) {
                $obj = D('Shop');
                foreach ($shop_id as $id) {
                    $obj->save(array('shop_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('shop/index'));
            }
            $this->baoError('请选择要删除的商家');
        }
    }
    public function audit($shop_id = 0){
        if (is_numeric($shop_id) && ($shop_id = (int) $shop_id)) {
            $obj = D('Shop');
            $obj->save(array('shop_id' => $shop_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('shop/apply'));
        } else {
            $shop_id = $this->_post('shop_id', false);
            if (is_array($shop_id)) {
                $obj = D('Shop');
                foreach ($shop_id as $id) {
                    $obj->save(array('shop_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('shop/apply'));
            }
            $this->baoError('请选择要审核的商家');
        }
    }
    public function login($shop_id){
        $obj = D('Shop');
        if (!($detail = $obj->find($shop_id))) {
            $this->error('请选择要编辑的商家');
        }
        if (empty($detail['user_id'])) {
            $this->error('该用户没有绑定管理者');
        }
        setUid($detail['user_id']);
        header('Location:' . U('Merchant/index/index'));
        die;
    }
    public function ding($shop_id){
        $obj = D('Shop');
        if (!($detail = $obj->find($shop_id))) {
            $this->error('请选择要编辑的商家');
        }
        $data = array('is_ding' => 0, 'shop_id' => $shop_id);
        if ($detail['is_ding'] == 0) {
            $data['is_ding'] = 1;
        }
        $obj->save($data);
        $this->baoSuccess('操作成功', U('shop/index'));
    }
    public function biz($shop_id){
        $obj = D('Shop');
        if (!($detail = $obj->find($shop_id))) {
            $this->error('请选择要编辑的商家');
        }
        $data = array('is_biz' => 0, 'shop_id' => $shop_id);
        if ($detail['is_biz'] == 0) {
            $data['is_biz'] = 1;
        }
        $obj->save($data);
        $this->baoSuccess('操作成功', U('shop/index'));
    }
    public function profit($shop_id){
        $obj = D('Shop');
        if (!($detail = $obj->find($shop_id))) {
            $this->error('请选择要编辑的商家');
        }
        $data = array('is_profit' => 0, 'shop_id' => $shop_id);
        if ($detail['is_profit'] == 0) {
            $data['is_profit'] = 1;
        }
        $obj->save($data);
        $this->baoSuccess('操作成功', U('shop/index'));
    }
    //改版
    public function pei($shop_id){
        $obj = D('Shop');
        if (!($detail = $obj->find($shop_id))) {
            $this->error('请选择要编辑的商家');
        }
        if ($detail['is_pei'] == 1) {
            //当前不是配送
            $DeliveryOrder = D('DeliveryOrder')->where(array('shop_id' => $detail['shop_id'], 'status' => array('NEQ', 8)))->select();
            if (!empty($DeliveryOrder)) {
                $this->baoError('还有未完成的订单');
            }
            $obj->save(array('shop_id' => $shop_id, 'is_pei' => 0));
        } else {
            if ($detail['is_pei'] == 0) {
                $Eleorder = D('Eleorder')->where(array('shop_id' => $detail['shop_id'], 'status' => array('NEQ', 8)))->find();
                $Order = D('Order')->where(array('shop_id' => $detail['shop_id'], 'status' => array('NEQ', 8)))->find();
                if (!empty($Eleorder) || !empty($Order)) {
                    $this->baoError('还有未完成的订单');
                }
                $obj->save(array('shop_id' => $shop_id, 'is_pei' => 1));
            }
        }
        $this->baoSuccess('操作成功', U('shop/index'));
    }
    public function recovery(){
        $Shop = D('Shop');
        import('ORG.Util.Page');
        $map = array('closed' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Shopcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        $count = $Shop->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Shop->order(array('shop_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //认领开始
    public function recognition(){
        $Shop = D('Shop');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'recognition' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Shopcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        $count = $Shop->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Shop->order(array('shop_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //认领结束
    public function recovery2($shop_id = 0){
        if (is_numeric($shop_id) && ($shop_id = (int) $shop_id)) {
            $obj = D('Shop');
            $obj->save(array('shop_id' => $shop_id, 'closed' => 0));
            $this->baoSuccess('恢复商家成功！', U('shop/index'));
        } else {
            $shop_id = $this->_post('shop_id', false);
            if (is_array($shop_id)) {
                $obj = D('Shop');
                foreach ($shop_id as $id) {
                    $obj->save(array('shop_id' => $id, 'closed' => 0));
                }
                $this->baoSuccess('恢复商家成功！', U('shop/index'));
            }
            $this->baoError('请选择要恢复的商家');
        }
    }
    public function delete2($shop_id = 0){
        $shop_id = (int) $shop_id;
        if (!empty($shop_id)) {
            $goods = D('Goods')->where(array('shop_id' => $shop_id))->select();
            foreach ($goods as $k => $value) {
                D('Goods')->save(array('goods_id' => $value['goods_id'], 'closed' => 1));
            }
            $coupon = D('Coupon')->where(array('shop_id' => $shop_id))->select();
            foreach ($coupon as $k => $value) {
                D('Tuan')->save(array('coupon_id' => $value['coupon_id'], 'closed' => 1));
            }
            $tuan = D('Tuan')->where(array('shop_id' => $shop_id))->select();
            foreach ($goods as $k => $value) {
                D('Tuan')->save(array('tuan_id' => $value['tuan_id'], 'closed' => 1));
            }
            D('Ele')->save(array('shop_id' => $value['shop_id'], 'audit' => 0));
            D('Shop')->delete($shop_id);
            $this->baoSuccess('彻底删除成功！', U('shop/recovery'));
        } else {
            $this->baoError('操作失败');
        }
    }
}
<?php
class PgoodsAction extends CommonAction {
private $create_fields = array('name', 'cate_id', 'shop_id', 'list_pic', 'pics1', 'pics2', 'pics3','describe','content', 'sales_num', 'virtual_sales_num', 'xiangou_num', 'market_price', 'one_price', 'tuanz_price', 'tuan_num', 'tuan_price', 'tuan_num2','tuan_price2', 'tuan_num3', 'tuan_price3', 'open_tuanj', 'tuan_status', 'tuan_hours', 'goods_unit', 'goods_pic', 'share_title', 'share_desc', 'is_show', 'paixu', 'end_time','zhongliang','kuaidi','is_yunfei','yunfei_ids');
private $edit_fields = array('name', 'cate_id', 'shop_id', 'list_pic', 'pics1', 'pics2', 'pics3','describe','content', 'sales_num', 'virtual_sales_num', 'xiangou_num', 'market_price', 'one_price', 'tuanz_price', 'tuan_num', 'tuan_price', 'tuan_num2','tuan_price2', 'tuan_num3', 'tuan_price3', 'open_tuanj', 'tuan_status', 'tuan_hours', 'goods_unit', 'goods_pic', 'share_title', 'share_desc', 'is_show', 'paixu', 'end_time','zhongliang','kuaidi','is_yunfei','yunfei_ids');
    public function index() {
        $Goods = D('pgoods');
        import('ORG.Util.Page'); // 导入分页类
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
			$pcate = D('Pcate')->find($cate_id);
			$this->assign('cate_name', $pcate['name']);
            $this->assign('cate_id', D('Pcate')->fetchAll());
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Pshop')->find($shop_id);
            $this->assign('shop_name', $shop['name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($show = (int) $this->_param('is_show')) {
            $map['is_show'] = ($show === 1 ? 1 : 0);
            $this->assign('is_show', $show);
        }
        $count = $Goods->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Goods->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$shop_ids = $cate_ids = array();
        foreach ($list as $key => $val) {
            $shop_ids[$val['id']] = $val['shop_id'];
			$cate_ids[$val['id']] = $val['cate_id']; 
        }
        $this->assign('shops', D('Pshop')->itemsByIds($shop_ids));
        $this->assign('cates', D('Pcate')->itemsByIds($cate_ids));
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Pgoods');
            if ($goods_id = $obj->add($data)) {
                $this->baoSuccess('添加成功', U('pgoods/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Pcate')->select());
			$this->assign('kuaidi', D('Pkuaidi')->where(array('type'=>pintuan))->select());
            $this->display();
        }
    }
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('产品名称不能为空');
        }
		$data['is_yunfei'] = (int) $data['is_yunfei'];
		$data['yunfei_ids'] = htmlspecialchars($data['yunfei_ids']);
		$data['zhongliang'] = (int) $data['zhongliang'];
        if (empty($data['zhongliang'])) {
            $this->baoError('重量不能为空');
        }
		$data['kuaidi'] = (int) $data['kuaidi'];
        if (empty($data['kuaidi'])) {
            $this->baoError('快递不能为空');
        }
		$data['goods_unit'] = htmlspecialchars($data['goods_unit']);
        if(empty($data['goods_unit'])){
            $this->baoError('商品规格不能为空');
        }
		$data['describe'] = htmlspecialchars($data['describe']);
        if (empty($data['describe'])) {
            $this->baoError('产品描述不能为空');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $shop = D('Pshop')->where(array('id' => $data['shop_id'])) ->find();
        if (empty($shop)) {
            $this->baoError('请选择正确的商家');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('请选择分类');
        }
        $data['list_pic'] = htmlspecialchars($data['list_pic']);
        if (empty($data['list_pic'])) {
            $this->baoError('列表图');
        }
        if (!isImage($data['list_pic'])) {
            $this->baoError('列表图格式不正确');
        } 
        $data['pics1'] = htmlspecialchars($data['pics1']);
        if (empty($data['pics1'])) {
            $this->baoError('幻灯片图1');
        }
        if (!isImage($data['pics1'])) {
            $this->baoError('幻灯片图1格式不正确');
        }
		$data['pics2'] = htmlspecialchars($data['pics2']);
		$data['pics3'] = htmlspecialchars($data['pics3']);
		$data['content'] = SecurityEditorHtml($data['content']);
        if (empty($data['content'])) {
            $this->baoError('商品详情不能为空');
        }
		$data['virtual_sales_num'] = (int) $data['virtual_sales_num'];
        $data['xiangou_num'] = (int) $data['xiangou_num'];
		if (empty($data['xiangou_num'])) {
            $this->baoError('库存不能为空');
        }
        $data['market_price'] = (int) ($data['market_price'] * 100);
        if (empty($data['market_price'])) {
            $this->baoError('市场价格不能为空');
        }
		$data['one_price'] = (int) ($data['one_price'] * 100);
        if (empty($data['one_price'])) {
            $this->baoError('单独购买价格不能为空');
        }  
        $data['tuanz_price'] = (int) ($data['tuanz_price'] * 100);
        if (empty($data['tuanz_price'])) {
            $this->baoError('团长价格不能为空');
        }
		$data['tuan_num'] = (int) $data['tuan_num'];
		if (empty($data['tuan_num'])) {
            $this->baoError('默认拼团人数不能为空');
        } 
        $data['tuan_price'] = (int) ($data['tuan_price'] * 100);
        if(empty($data['tuan_price'])){
            $this->baoError('默认拼团价格不能为空');
        }
		$data['open_tuanj'] = (int) $data['open_tuanj'];
		$data['tuan_num2'] = (int) $data['tuan_num2'];
        $data['tuan_price2'] = (int) ($data['tuan_price2'] * 100);
		$data['tuan_num3'] = (int) $data['tuan_num3'];
        $data['tuan_price3'] = (int) ($data['tuan_price3'] * 100);
        $data['tuan_status'] = strtotime($data['tuan_status']);
        if (empty($data['tuan_status'])) {
            $this->baoError('开启拼团时间不能为空');
        }
		$data['tuan_hours'] = (int) $data['tuan_hours'];
        if (empty($data['tuan_hours'])) {
            $this->baoError('开团后时间限制不能为空');
        }
		$data['end_time'] = strtotime($data['end_time']);
        if (empty($data['end_time'])) {
            $this->baoError('关闭拼团时间不能为空');
        }
        $data['goods_pic'] = htmlspecialchars($data['goods_pic']);
        if (empty($data['goods_pic'])) {
            $this->baoError('分享图不能为空');
        }
        if (!isImage($data['goods_pic'])) {
            $this->baoError('分享图格式不正确');
        }
        $data['add_time'] = NOW_TIME;
        $data['paixu'] = (int) $data['paixu'];
        $data['is_show'] = (int) $data['is_show'];
        return $data;
    }
    public function edit($goods_id = 0) {
        if ($goods_id = (int) $goods_id) {
            $obj = D('Pgoods');
            if (!$detail = $obj->find($goods_id)) {
                $this->baoError('请选择要编辑的商品');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['id'] = $goods_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('pgoods/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $obj->_format($detail));
                $this->assign('cates', D('Pcate')->select());
				$this->assign('kuaidi', D('Pkuaidi')->where(array('type'=>pintuan))->select());
                $this->assign('shop', D('Pshop')->where(array('id' => $detail['shop_id'])) ->find());
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商品');
        }
    }
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('产品名称不能为空');
        }
        $data['is_yunfei'] = (int) $data['is_yunfei'];
		$data['yunfei_ids'] = htmlspecialchars($data['yunfei_ids']);
		$data['zhongliang'] = (int) $data['zhongliang'];
        if (empty($data['zhongliang'])) {
            $this->baoError('重量不能为空');
        }
		$data['kuaidi'] = (int) $data['kuaidi'];
        if (empty($data['kuaidi'])) {
            $this->baoError('快递不能为空');
        }
		$data['goods_unit'] = htmlspecialchars($data['goods_unit']);
        if(empty($data['goods_unit'])){
            $this->baoError('商品规格不能为空');
        }
		$data['describe'] = htmlspecialchars($data['describe']);
        if (empty($data['describe'])) {
            $this->baoError('产品描述不能为空');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $shop = D('Pshop')->where(array('id' => $data['shop_id'])) ->find();
        if (empty($shop)) {
            $this->baoError('请选择正确的商家');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('请选择分类');
        }
        $data['list_pic'] = htmlspecialchars($data['list_pic']);
        if (empty($data['list_pic'])) {
            $this->baoError('列表图');
        }
        if (!isImage($data['list_pic'])) {
            $this->baoError('列表图格式不正确');
        } 
        $data['pics1'] = htmlspecialchars($data['pics1']);
        if (empty($data['pics1'])) {
            $this->baoError('幻灯片图1');
        }
        if (!isImage($data['pics1'])) {
            $this->baoError('幻灯片图1格式不正确');
        }
		$data['pics2'] = htmlspecialchars($data['pics2']);
		$data['pics3'] = htmlspecialchars($data['pics3']);
		$data['content'] = SecurityEditorHtml($data['content']);
        if (empty($data['content'])) {
            $this->baoError('商品详情不能为空');
        }
		$data['virtual_sales_num'] = (int) $data['virtual_sales_num'];
        $data['xiangou_num'] = (int) $data['xiangou_num'];
		if (empty($data['xiangou_num'])) {
            $this->baoError('库存不能为空');
        }
        $data['market_price'] = (int) ($data['market_price'] * 100);
        if (empty($data['market_price'])) {
            $this->baoError('市场价格不能为空');
        }
		$data['one_price'] = (int) ($data['one_price'] * 100);
        if (empty($data['one_price'])) {
            $this->baoError('单独购买价格不能为空');
        }  
        $data['tuanz_price'] = (int) ($data['tuanz_price'] * 100);
        if (empty($data['tuanz_price'])) {
            $this->baoError('团长价格不能为空');
        }
		$data['tuan_num'] = (int) $data['tuan_num'];
		if (empty($data['tuan_num'])) {
            $this->baoError('默认拼团人数不能为空');
        } 
        $data['tuan_price'] = (int) ($data['tuan_price'] * 100);
        if(empty($data['tuan_price'])){
            $this->baoError('默认拼团价格不能为空');
        }
		$data['open_tuanj'] = (int) $data['open_tuanj'];
		$data['tuan_num2'] = (int) $data['tuan_num2'];
        $data['tuan_price2'] = (int) ($data['tuan_price2'] * 100);
		$data['tuan_num3'] = (int) $data['tuan_num3'];
        $data['tuan_price3'] = (int) ($data['tuan_price3'] * 100);
        $data['tuan_status'] = strtotime($data['tuan_status']);
        if (empty($data['tuan_status'])) {
            $this->baoError('开启拼团时间不能为空');
        }
		$data['tuan_hours'] = (int) $data['tuan_hours'];
        if (empty($data['tuan_hours'])) {
            $this->baoError('开团后时间限制不能为空');
        }
		$data['end_time'] = strtotime($data['end_time']);
        if (empty($data['end_time'])) {
            $this->baoError('关闭拼团时间不能为空');
        }
        $data['goods_pic'] = htmlspecialchars($data['goods_pic']);
        if (empty($data['goods_pic'])) {
            $this->baoError('分享图不能为空');
        }
        if (!isImage($data['goods_pic'])) {
            $this->baoError('分享图格式不正确');
        }
        $data['add_time'] = NOW_TIME;
        $data['paixu'] = (int) $data['paixu'];
        $data['is_show'] = (int) $data['is_show'];
        return $data;
    }
    public function delete($goods_id = 0) {
        if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)) {
            $obj = D('Pgoods');
            $obj->delete($goods_id);
            $this->baoSuccess('删除成功！', U('pgoods/index'));
        } else {
            $goods_id = $this->_post('goods_id', false);
            if (is_array($goods_id)) {
                $obj = D('Pgoods');
                foreach ($goods_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('pgoods/index'));
            }
            $this->baoError('请选择要删除的商品');
        }
    }
    public function show($goods_id = 0) {
        if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)) {
            $obj = D('Pgoods');
            $r = $obj -> where('id ='.$goods_id) -> find();
            
            $obj->save(array('id' => $goods_id, 'is_show' => 1));
            $this->baoSuccess('上架成功！', U('pgoods/index'));
        } else {
            $goods_id = $this->_post('goods_id', false);
            if (is_array($goods_id)) {
                $obj = D('Goods');
                foreach ($goods_id as $id) {
                    $r = $obj -> where('goods_id ='.$id) -> find();		 
                    $obj->save(array('id' => $id, 'is_show' => 1));
                }
                $this->baoSuccess('上架成功！', U('pgoods/index'));
            }
            $this->baoError('请选择要上架的商品');
        }
    }
}

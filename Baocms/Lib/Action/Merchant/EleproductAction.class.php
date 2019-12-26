<?php

class EleproductAction extends CommonAction {
    private $create_fields = array('product_name','desc', 'cate_id', 'photo', 'price', 'is_new', 'is_hot', 'is_tuijian', 'create_time', 'create_ip');
    private $edit_fields = array('product_name', 'desc','cate_id', 'photo', 'price', 'is_new', 'is_hot', 'is_tuijian');
     public function _initialize() {
        parent::_initialize();
        $getEleCate = D('Ele')->getEleCate();
        $this->assign('getEleCate', $getEleCate);
        $this->ele = D('Ele')->find($this->shop_id);

        if (!empty($this->ele) && $this->ele['audit'] == 0) {

            $this->error("亲，您的申请正在审核中！");

        }

        if (empty($this->ele) && ACTION_NAME != 'apply') {

            $this->error('您还没有入住外卖频道', U('ele/apply'));

        }

        $this->assign('ele', $this->ele);

        $this->elecates = D('Elecate')->where(array('shop_id'=>$this->shop_id,'closed'=>0))->select();

        $this->assign('elecates', $this->elecates);

    }

   

    

    public function index() {

        $Eleproduct = D('Eleproduct');

        import('ORG.Util.Page'); // 导入分页类

        $map = array('closed'=>0);

        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {

            $map['product_name'] = array('LIKE', '%' . $keyword . '%');

            $this->assign('keyword', $keyword);

        }

        if ($shop_id = $this->shop_id) {

            $map['shop_id'] = $shop_id;

            $this->assign('shop_id', $shop_id);

        }

        $count = $Eleproduct->where($map)->count(); // 查询满足要求的总记录数 

        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数

        $show = $Page->show(); // 分页显示输出

        $list = $Eleproduct->where($map)->order(array('product_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $cate_ids= array();

        foreach ($list as $k => $val) {



            if($val['cate_id']){

                $cate_ids[$val['cate_id']] = $val['cate_id'];

            }

        }



        if($cate_ids){

            $this->assign('cates',D('Elecate')->itemsByIds($cate_ids));

        }

        $this->assign('list', $list); // 赋值数据集

        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板

    }



    public function create() {

        if ($this->isPost()) {

            $data = $this->createCheck();

            $obj = D('Eleproduct');

            if ($obj->add($data)) {

                D('Elecate')->updateNum($data['cate_id']);

                $this->baoSuccess('添加成功', U('eleproduct/index'));

            }

            $this->baoError('操作失败！');

        } else {

            $this->display();

        }

    }



    private function createCheck() {

        $data = $this->checkFields($this->_post('data', false), $this->create_fields);

        $data['product_name'] = htmlspecialchars($data['product_name']);

        if (empty($data['product_name'])) {

            $this->baoError('菜名不能为空');

        } 

		$data['desc'] = htmlspecialchars($data['desc']);

        if (empty($data['desc'])) {

            $this->baoError('菜单介绍不能为空');

        }

		

        $data['shop_id'] = $this->shop_id;
        $data['cate_id'] = (int) $data['cate_id'];

        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['price'] = (int) ($data['price']*100);
        if (empty($data['price'])) {
            $this->baoError('价格不能为空');

        } 

        $data['settlement_price'] = (int)( $data['price'] - ($data['price'] *  $this->ele['rate'] /1000));

        $data['is_new'] = (int) $data['is_new'];

        $data['is_hot'] = (int) $data['is_hot'];

        $data['is_tuijian'] = (int) $data['is_tuijian'];

        $data['create_time'] = NOW_TIME;

        $data['create_ip'] = get_client_ip();

        $data['audit'] = 0;

        return $data;

    }



    public function edit($product_id = 0) {

        if ($product_id = (int) $product_id) {

            $obj = D('Eleproduct');

            if (!$detail = $obj->find($product_id)) {

                $this->baoError('请选择要编辑的菜单管理');

            }

            if($detail['shop_id'] != $this->shop_id){

                $this->baoError('请不要操作其他商家的菜单管理');

            }

            if ($this->isPost()) {

                $data = $this->editCheck();

                $data['product_id'] = $product_id;

                if (false !== $obj->save($data)) {

                    D('Elecate')->updateNum($data['cate_id']);

                    $this->baoSuccess('操作成功', U('eleproduct/index'));

                }

                $this->baoError('操作失败');

            } else {

                $this->assign('detail', $detail);

                $this->display();

            }

        } else {

            $this->baoError('请选择要编辑的菜单管理');

        }

    }



    private function editCheck() {

        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);

        $data['product_name'] = htmlspecialchars($data['product_name']);

        if (empty($data['product_name'])) {

            $this->baoError('菜名不能为空');

        }

		$data['desc'] = htmlspecialchars($data['desc']);

        if (empty($data['desc'])) {

            $this->baoError('菜单介绍不能为空');

        }

		$data['cate_id'] = (int) $data['cate_id'];

        if (empty($data['cate_id'])) {

            $this->baoError('分类不能为空');

        } $data['photo'] = htmlspecialchars($data['photo']);

        if (empty($data['photo'])) {

            $this->baoError('请上传缩略图');

        }

        if (!isImage($data['photo'])) {

            $this->baoError('缩略图格式不正确');

        } $data['price'] = (int) ($data['price']*100);

        if (empty($data['price'])) {

            $this->baoError('价格不能为空');

        }

        $data['settlement_price'] = (int)( $data['price'] - ($data['price'] *  $this->ele['rate'] /1000));

        $data['is_new'] = (int) $data['is_new'];

        $data['is_hot'] = (int) $data['is_hot'];

        $data['is_tuijian'] = (int) $data['is_tuijian'];

        return $data;

    }



    public function delete($product_id = 0) {

        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {

            $obj = D('Eleproduct');

            if(!$detail = $obj->where(array('shop_id'=>$this->shop_id,'product_id'=>$product_id))->find()){

                $this->baoError('请选择要删除的菜单管理');

            }

               D('Elecate')->updateNum($detail['cate_id']);

             $obj->save(array('product_id' => $product_id, 'closed' => 1));

            $this->baoSuccess('删除成功！', U('eleproduct/index'));

        }

            $this->baoError('请选择要删除的菜单管理');

        

    }



}


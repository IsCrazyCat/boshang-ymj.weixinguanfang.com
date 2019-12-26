<?php
class CarAction extends CommonAction{
    private $create_fields = array('car_id','name','short_name','english_name','tags','is_open','parent_id','photo', 'first_letter', 'orderby');
    private $edit_fields = array('car_id','name','short_name','english_name','tags','is_open','parent_id','photo', 'first_letter', 'orderby');

    public function index(){
        $Car = D('Car');
        import('ORG.Util.Page'); // 导入分页类    www.blklube.com
        $map = array();
        $map['closed'] = 0;
        if($parent_id = $this->_param('parent_id','htmlspecialchars')){
            $map['parent_id'] = $parent_id;//二级分类的父级ID
            //获取所有一级分类
            $parent_car = $Car->where(array('car_id'=>$parent_id))->find();
            $this->assign('parent',$parent_car);
        }else{
            $map['parent_id'] = 0;//一级分类
        }
        $this->assign('parent_id',$parent_id);

        $keyword = $this->_param('keyword','htmlspecialchars');

        if($keyword){
            $map['name|english_name|short_name'] = array('LIKE', '%'.$keyword.'%');
        }
        $this->assign('keyword',$keyword);


        $count = $Car->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Car->where($map)->order(array('car_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $date['create_time'] = time();
            $obj = D('Car');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('car/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $parent_id = $this->_param('parent_id');
            if($parent_id){
                $parent = D('car')->find($parent_id);
                $this->assign('parent', $parent);
            }
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('品牌名称不能为空');
        }
        $data['short_name'] = htmlspecialchars($data['short_name']);
        if (empty($data['short_name'])) {
            $this->baoError('品牌简称不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!isImage($data['photo'])) {
            $this->baoError('请上传logo');
        }
        $data['first_letter'] = htmlspecialchars($data['first_letter']);
        if (empty($data['first_letter'])) {
            $this->baoError('首字母不能为空');
        }
        $data['parent_id'] = htmlspecialchars($data['parent_id']);
        $data['english_name'] = htmlspecialchars($data['english_name']);

        $data['tags'] = str_replace('；',';',htmlspecialchars($data['tags']));
        $data['is_open'] = (int)($data['is_open']);

        $data['orderby'] = (int)($data['orderby']);
        return $data;
    }

    public function edit($car_id = 0) {
        if ($car_id = (int) $car_id) {
            $obj = D('Car');
            if (!$detail = $obj->find($car_id)) {
                $this->baoError('请选择要编辑的车辆');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['car_id'] = $car_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('car/index'));
                }
                $this->baoError('操作失败');
            } else {
                $parent_id = $this->_param('parent_id');
                if($parent_id){
                    $parent = D('car')->find($parent_id);
                    $this->assign('parent', $parent);
                    $cars = D('car')->where(array('parent_id'=>0,'is_open'=>1))->select();
                    $this->assign('cars', $cars);
                }
                $this->assign('parent_id', $parent_id);
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的车辆品牌');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('车辆名称不能为空');
        }
        $data['short_name'] = htmlspecialchars($data['short_name']);
        if (empty($data['short_name'])) {
            $this->baoError('车辆简称不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!isImage($data['photo'])) {
            $this->baoError('请上传logo');
        }
        $data['first_letter'] = htmlspecialchars($data['first_letter']);
        if (empty($data['first_letter'])) {
            $this->baoError('首字母不能为空');
        }
        $data['parent_id'] = htmlspecialchars($data['parent_id']);
        $data['english_name'] = htmlspecialchars($data['english_name']);

        $data['tags'] = str_replace('；',';',htmlspecialchars($data['tags']));

        $data['is_open'] = (int)($data['is_open']);

        $data['first_letter'] = htmlspecialchars($data['first_letter']);
        $data['orderby'] = (int)($data['orderby']);
        return $data;
    }
    public function delete($car_id = 0) {
        $data['closed'] = 1;
        $obj = D('Car');
        if (is_numeric($car_id) && ($car_id = (int) $car_id)) {
            $data['car_id']=$car_id;
            $obj->save($data);
            //如果逻辑删除的是顶级分类 则二级分类均逻辑删除
            foreach (D('Car')->where(array('parent_id'=>$car_id))->select() as $key=>$val){
                $data['car_id']=$val['car_id'];
                $obj->save($data);
            }
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('car/index'));
        } else {
            $car_id = $this->_post('car_id', false);
            if (is_array($car_id)) {
                foreach ($car_id as $id){
                    $data['car_id']=$id;
                    $obj->save($data);
                    //如果逻辑删除的是顶级分类 则二级分类均逻辑删除
                    foreach (D('Car')->where(array('parent_id'=>$id))->select() as $key=>$val){
                        $data['car_id']=$val['car_id'];
                        $obj->save($data);
                    }
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('car/index'));
            }
            $this->baoError('请选择要删除的车辆品牌');
        }
    }
    public function select()
    {
        $Car = D('Car');
        import('ORG.Util.Page');
        // 导入分页类    www.blklube.com
        $map = array('closed' => 0, 'is_open' => 1);
        $map['parent_id'] = array('exp','<>0');
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|short_name|english_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }

        $count = $Car->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Car->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['parent_id']) {
                $parent = $Car->where(array('car_id'=>$val['parent_id']))->find();
                if(!empty($parent)){
                    $list[$k]['parent_name']=$parent['name'];
                }
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}
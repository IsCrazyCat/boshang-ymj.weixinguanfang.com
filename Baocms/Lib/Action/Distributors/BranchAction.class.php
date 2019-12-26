<?php



class BranchAction extends CommonAction {

    private $create_fields = array('name', 'city_id', 'area_id', 'business_id', 'addr', 'lng', 'lat', 'orderby','telephone');
    private $edit_fields = array('name', 'city_id', 'area_id', 'business_id', 'addr', 'lng', 'lat', 'orderby','telephone');

    public function _initialize() {
        parent::_initialize();
        $this->assign('city',D('City')->fetchAll());
        $this->assign('area', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
    }

    public function index() {
        
        $branch = D('Shopbranch');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0, 'shop_id' => $this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|addr'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $branch->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count,10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $branch->where($map)->order(array('orderby' => 'asc', 'branch_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }
    

    public function delete($branch_id = 0) {
        if (is_numeric($branch_id) && ($branch_id = (int) $branch_id)) {
            $obj = D('Shopbranch');
            if (!$detail = $obj->find($branch_id)) {
                $this->ajaxReturn(array('status'=>'error','message'=>'请选择要删除的分店！'));
            }
            if ($detail['closed'] == 1) {
				$this->ajaxReturn(array('status'=>'error','message'=>'该分店不存在！'));
            }
            if ($detail['shop_id'] != $this->shop_id) {
				$this->ajaxReturn(array('status'=>'error','message'=>'请不要试图越权操作其他人的内容！'));
            }
            $obj->save(array('branch_id' => $branch_id, 'closed' => 1));
			$this->ajaxReturn(array('status'=>'success','msg'=>'删除成功', U('branch/index')));
        } else {
			$this->ajaxReturn(array('status'=>'error','message'=>'请选择要删除的分店！'));
        }
    }
	
	public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Shopbranch');
            if ($obj->add($data)) {
                $this->fengmiMsg('添加成功', U('branch/index'));
            }
            $this->fengmiMsg('操作失败！');
        } else {
			$lat = cookie('lat');
            $lng = cookie('lng');
            if (empty($lat) || empty($lng)) {
                $lat = $this->_CONFIG['site']['lat'];
                $lng = $this->_CONFIG['site']['lng'];
            }
			$this->assign('lng', $lng);
            $this->assign('lat', $lat);
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->fengmiMsg('分店名称不能为空');
        }
        $data['shop_id'] = $this->shop_id;
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->fengmiMsg('请选择城市');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->fengmiMsgr('请选择地区');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->fengmiMsg('请选择商圈');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->fengmiMsg('分店地址不能为空');
        }
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
            $this->fengmiMsg('分店坐标不能为空');
        }
	 $data['telephone'] = htmlspecialchars($data['telephone']);	
	if (empty($data['telephone'])) {
            $this->fengmiMsg('电话不能为空');
        }
        if(!isMobile($data['telephone'])&&!isPhone($data['telephone'])){
            $this->fengmiMsg('请输入正确的手机号码');
        }	
		
        $data['orderby'] = (int) $data['orderby'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
	    $data['audit'] = 1;
        return $data;
    }

    public function edit($branch_id = 0) {
        if ($branch_id = (int) $branch_id) {
            $obj = D('Shopbranch');
            if (!$detail = $obj->find($branch_id)) {
                $this->error('请选择要编辑的分店');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('请不要试图越权操作其他人的内容');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['branch_id'] = $branch_id;
                if (false !== $obj->save($data)) {
                    $this->fengmiMsg('操作成功', U('branch/index'));
                }
                $this->fengmiMsg('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('请选择要编辑的分店');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->fengmiMsg('分店名称不能为空');
        }
        $data['shop_id'] = $this->shop_id;
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->fengmiMsg('请选择城市');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->fengmiMsg('请选择地区');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->fengmiMsg('请选择商圈');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->fengmiMsg('分店地址不能为空');
        }
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if (empty($data['lng']) || empty($data['lat'])) {
            $this->fengmiMsg('分店坐标不能为空');
        }
        $data['telephone'] = htmlspecialchars($data['telephone']);	
	if (empty($data['telephone'])) {
            $this->fengmiMsg('电话不能为空');
        }
        if(!isMobile($data['telephone'])&&!isPhone($data['telephone'])){
            $this->fengmiMsg('请输入正确的手机号码');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
	

    public function manage($branch_id=0) {
        if ($branch_id = (int) $branch_id) {
            $obj = D('Shopbranch');
            if (!$detail = $obj->find($branch_id)) {
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
                if(empty($data['password'])){
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
        }else{
            $this->baoError('请选择要设置的分店');
        }
    }

}

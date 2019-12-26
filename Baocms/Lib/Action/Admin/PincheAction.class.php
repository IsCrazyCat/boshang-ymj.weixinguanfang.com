<?php 
class PincheAction extends CommonAction{
	
	 private $create_fields = array('cate_id', 'city_id','area_id','community_id', 'user_id','photo', 'start_time', 'start_time_more', 'toplace', 'middleplace', 'num_1', 'num_2', 'num_3', 'num_4', 'mobile','lng','lat', 'details');
    private $edit_fields = array('cate_id', 'city_id','area_id','community_id', 'user_id','photo', 'start_time', 'start_time_more', 'toplace', 'middleplace', 'num', 'mobile','lng','lat', 'details');
	
	
	
   protected function _initialize() {
        parent::_initialize();
        $getPincheCate = D('Pinche')->getPincheCate();
        $this->assign('getPincheCate', $getPincheCate);
    }
	
    public function index(){
        $pinche = D('Pinche');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['toplace|middleplace'] = array('LIKE', '%' . $keyword . '%');
        }
        $area_id = (int) $this->_param('area_id');
        if ($area_id) {
            $map['area_id'] = $area_id;
        }
        $cate_id = (int) $this->_param('cate_id');
        if ($cate_id) {
            $map['cate_id'] = $cate_id;
        }
    

        $count = $pinche->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
		
        $list = $pinche->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
	
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('citys', D('City')->fetchAll());
		$this->assign('areas', D('Area')->fetchAll());
        $this->display();
    }
   
	//选择
	 public function child($area_id = 0) {
        $datas = D('Community')->select();
        $str = '<option value="0">请选择</option>';
        foreach ($datas as $val) {
            if ($val['area_id'] == $area_id) {
                $str.='<option value="' . $val['community_id'] . '">' . $val['name'] . '</option>';
            }
        }
        echo $str;
        die;
    }
	
	 public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            if ($pinche_id = D('Pinche')->add($data)) {
                $this->baoSuccess('发布成功', U('pinche/index'));
            }
            $this->baoError('发布失败');
        } else {
			$this->assign('areas', D('Area')->select());
            $this->assign('communitys', D('Community')->select());//不查询缓存
            $this->assign('business', D('Business')->fetchAll());
            $this->assign('user', D('Users')->find($detail['user_id']));
            $this->display();
        }
    }
    public function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('类型不能为空');
        }
		
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }

		
		$data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('区域不能为空');
        }
		
		$data['community_id'] = (int) $data['community_id'];
        if (empty($data['community_id'])) {
            $this->baoError('小区不能为空');
        }
		
		$data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('会员不能为空');
        }
		
		$data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
		
        $data['start_time'] = htmlspecialchars($data['start_time']);
        if (empty($data['start_time'])) {
            $this->baoError('出发时间不能为空');
        }
        $data['start_time_more'] = htmlspecialchars($data['start_time_more']);

        $data['toplace'] = htmlspecialchars($data['toplace']);
        if (empty($data['toplace'])) {
            $this->baoError('目的地不能为空');
        }
		$data['middleplace'] = htmlspecialchars($data['middleplace']);
		$data['num_1'] = htmlspecialchars($data['num_1']);
		$data['num_2'] = htmlspecialchars($data['num_2']);
		$data['num_3'] = htmlspecialchars($data['num_3']);
		$data['num_4'] = htmlspecialchars($data['num_4']);
        $data['mobile'] = htmlspecialchars($data['mobile']);
		$data['lng'] = htmlspecialchars(trim($data['lng']));
        $data['lat'] = htmlspecialchars(trim($data['lat']));
        if (empty($data['mobile'])) {
            $this->baoError('手机不能为空');
        }
        if (!ismobile($data['mobile'])) {
            $this->baoError('手机格式不正确');
        }
        $data['audit'] = 1;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($pinche_id){
        if ($pinche_id = (int) $pinche_id) {
            $obj = D('Pinche');
            if (!($detail = $obj->find($pinche_id))) {
                $this->error('请选择要编辑的拼车');
            }
            if ($detail['status'] != 0) {
                $this->error('该拼车状态不允许被编辑');
            }
            if ($detail['closed'] == 1) {
                $this->error('该拼车已被删除');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['pinche_id'] = $pinche_id;
                if (FALSE !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('pinche/index'));
                }
                $this->baoError('操作失败');
            } else {
				$this->assign('areas', D('Area')->select());
                $this->assign('communitys', D('Community')->select());//不查询缓存
                $this->assign('business', D('Business')->fetchAll());
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('请选择要编辑的拼车信息');
        }
    }
    public function editCheck(){
       $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
       $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('类型不能为空');
        }
		
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }

		
		$data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('区域不能为空');
        }
		
		$data['community_id'] = (int) $data['community_id'];
        if (empty($data['community_id'])) {
            $this->baoError('小区不能为空');
        }
		
		$data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('会员不能为空');
        }
		
		$data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
		
        $data['start_time'] = htmlspecialchars($data['start_time']);
        if (empty($data['start_time'])) {
            $this->baoError('出发时间不能为空');
        }
        $data['start_time_more'] = htmlspecialchars($data['start_time_more']);

        $data['toplace'] = htmlspecialchars($data['toplace']);
        if (empty($data['toplace'])) {
            $this->baoError('目的地不能为空');
        }
		$data['middleplace'] = htmlspecialchars($data['middleplace']);
			$data['num_1'] = htmlspecialchars($data['num_1']);
		$data['num_2'] = htmlspecialchars($data['num_2']);
		$data['num_3'] = htmlspecialchars($data['num_3']);
		$data['num_4'] = htmlspecialchars($data['num_4']);
        $data['mobile'] = htmlspecialchars($data['mobile']);
		$data['lng'] = htmlspecialchars(trim($data['lng']));
        $data['lat'] = htmlspecialchars(trim($data['lat']));
        if (empty($data['mobile'])) {
            $this->baoError('手机不能为空');
        }
        if (!ismobile($data['mobile'])) {
            $this->baoError('手机格式不正确');
        }
        $data['audit'] = 1;
        return $data;
    }
	//删除
	    public function delete($pinche_id = 0) {
        if (is_numeric($pinche_id) && ($pinche_id = (int) $pinche_id)) {
            $obj = D('Pinche');
            $obj->save(array('pinche_id' => $pinche_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('pinche/index'));
        } else {
            $pinche_id = $this->_post('pinche_id', false);
            if (is_array($pinche_id)) {
                $obj = D('Pinche');
                foreach ($pinche_id as $id) {
                    $obj->save(array('pinche_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('pinche/index'));
            }
            $this->baoError('请选择要删除的拼车');
        }
    }
}
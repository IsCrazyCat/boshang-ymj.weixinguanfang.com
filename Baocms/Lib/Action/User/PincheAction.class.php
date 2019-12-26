<?php 
class PincheAction extends CommonAction{

    private $edit_fields = array('cate_id', 'city_id','area_id','community_id', 'user_id','photo', 'start_time', 'start_time_more', 'toplace', 'middleplace', 'num_1', 'num_2', 'num_3', 'num_4', 'mobile','lng','lat', 'details');
	
	
	
   protected function _initialize() {
        parent::_initialize();
		 if ($this->_CONFIG['operation']['pinche'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $getPincheCate = D('Pinche')->getPincheCate();
        $this->assign('getPincheCate', $getPincheCate);
    }

    public function index(){
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $this->assign('nextpage', LinkTo('pinche/loaddata', array('t' => NOW_TIME,   'keyword' => $keyword, 'p' => '0000')));
	        $this->display(); // 输出模板
    }
	 public function end(){
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $this->assign('nextpage', LinkTo('pinche/load', array('t' => NOW_TIME,   'keyword' => $keyword, 'p' => '0000')));
	        $this->display(); // 输出模板
    }
	
	 public function del(){
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $this->assign('nextpage', LinkTo('pinche/del_load', array('t' => NOW_TIME,   'keyword' => $keyword, 'p' => '0000')));
	        $this->display(); // 输出模板
    }
	
    public function loaddata(){
        $pinche = D('Pinche');
        import('ORG.Util.Page');
        $map = array('audit' => 1,'user_id'=>$this->uid, 'closed' => 0, 'start_time' => array('EGT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['toplace'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $pinche->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $pinche->where($map)->order(array('create_time desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('citys', D('City')->fetchAll());
		$this->assign('areas', D('Area')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	 public function load(){
        $pinche = D('Pinche');
        import('ORG.Util.Page');
        $map = array('audit' => 1,'user_id'=>$this->uid, 'closed' => 0, 'start_time' => array('ELT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['toplace'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $pinche->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $pinche->where($map)->order(array('create_time desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('citys', D('City')->fetchAll());
		$this->assign('areas', D('Area')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	 public function del_load(){
        $pinche = D('Pinche');
        import('ORG.Util.Page');
        $map = array('user_id'=>$this->uid, 'closed' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['toplace'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $pinche->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $pinche->where($map)->order(array('create_time desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('citys', D('City')->fetchAll());
		$this->assign('areas', D('Area')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
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
                    $this->fengmiMsg('操作成功', U('pinche/index'));
                }
                $this->fengmiMsg('操作失败');
            } else {
			$lat = cookie('lat');
				$lng = cookie('lng');
				if (empty($lat) || empty($lng)) {
					$lat = $this->_CONFIG['site']['lat'];
					$lng = $this->_CONFIG['site']['lng'];
				}
				$this->assign('lng', $lng);
				$this->assign('lat', $lat);
                $this->assign('detail', $detail);
				$this->assign('communitys', D('Community')->select());
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
            $this->fengmiMsg('类型不能为空');
        }$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->fengmiMsg('城市不能为空');
        }$data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->fengmiMsg('区域不能为空');
        }$data['community_id'] = (int) $data['community_id'];
		$data['user_id'] = $this->uid;
        if (empty($data['user_id'])) {
            $this->fengmiMsg('您还没登录');
        }$data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->fengmiMsg('缩略图格式不正确');
        }$data['start_time'] = htmlspecialchars($data['start_time']);
        if (empty($data['start_time'])) {
            $this->fengmiMsg('出发时间不能为空');
        }$data['start_time_more'] = htmlspecialchars($data['start_time_more']);
        $data['toplace'] = htmlspecialchars($data['toplace']);
        if (empty($data['toplace'])) {
            $this->fengmiMsg('目的地不能为空');
        }$data['middleplace'] = htmlspecialchars($data['middleplace']);
		$data['num_1'] = htmlspecialchars($data['num_1']);
		$data['num_2'] = htmlspecialchars($data['num_2']);
		$data['num_3'] = htmlspecialchars($data['num_3']);
		$data['num_4'] = htmlspecialchars($data['num_4']);
        $data['mobile'] = htmlspecialchars($data['mobile']);
		$data['lng'] = htmlspecialchars(trim($data['lng']));
        $data['lat'] = htmlspecialchars(trim($data['lat']));
        if (empty($data['mobile'])) {
            $this->fengmiMsg('手机不能为空');
        }
        if (!ismobile($data['mobile'])) {
            $this->fengmiMsg('手机格式不正确');
        }
        $data['audit'] = 1;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function delete($pinche_id){
        if (is_numeric($pinche_id) && ($pinche_id = (int) $pinche_id)) {
            $obj = D('Pinche');
            if (!($detail = $obj->find($pinche_id))) {
                $this->fengmiMsg('拼车不存在');
            }
            if ($detail['closed'] == 1) {
                $this->fengmiMsg('该拼车状态不允许被删除');
            }
            $obj->save(array('pinche_id' => $pinche_id, 'closed' => 1));
            $this->fengmiMsg('删除成功！', U('pinche/index'));
        }
    }
	//选择小区
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
}
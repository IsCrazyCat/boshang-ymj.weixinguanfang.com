<?php 
class PincheAction extends CommonAction{
	
	 private $create_fields = array('cate_id', 'city_id','area_id','community_id', 'user_id','photo', 'start_time', 'start_time_more', 'toplace', 'middleplace', 'num_1' , 'num_2', 'num_3', 'num_4','mobile','lng','lat', 'details');

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
        $cate_id = (int) $this->_param('cate_id');
        $this->assign('cate_id', $cate_id);
        $order = (int) $this->_param('order');
        $areas = D('Area')->fetchAll();
        $area_id = (int) $this->_param('area_id');
        $this->assign('area_id', $area_id);
        $this->assign('areas', $areas);
        $this->assign('nextpage', LinkTo('pinche/loaddata', array('cate_id' => $cate_id, 't' => NOW_TIME, 'area_id' => $area_id, 'order' => $order,  'keyword' => $keyword, 'p' => '0000')));
		
		
		$bg_time = strtotime(TODAY);
		$counts['pinche_day'] = (int) D('Pinche')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'audit' => 1,'closed'=>0))->count();
		$this->assign('counts', $counts);
        $this->display(); // 输出模板
    }
    public function loaddata(){
        $pinche = D('Pinche');
        import('ORG.Util.Page');
        $map = array('audit' => 1,'city_id'=>$this->city_id, 'closed' => 0, 'start_time' => array('EGT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['toplace'] = array('LIKE', '%' . $keyword . '%');
        }
        $area_id = (int) $this->_param('area_id');
        if ($area_id) {
            $map['area_id'] = $area_id;
        }
        $cate_id = (int) $this->_param('cate_id');
        if ($cate_id) {
            $map['cate_id'] = $cate_id;
        }
        $order = $this->_param('order', 'htmlspecialchars');
		$lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $orderby = '';
        switch ($order) {
            case 3:
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";//距离
                break;
            case 2:
                $orderby = array('num' => 'asc', 'pinche_id' => 'desc');//人数
                break;
            default:
                $orderby = array('create_time' => 'desc');//发布时间
                break;
        }
     
        $this->assign('order', $order);
        $count = $pinche->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		
        $list = $pinche->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		//距离
		foreach ($list as $k => $val) {
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
		
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function detail($pinche_id){
        $pinche_id = (int) $pinche_id;
        if (empty($pinche_id) || !($detail = D('Pinche')->find($pinche_id))) {
            $this->error('该拼车不存在');
        }
     
		$this->assign('citys', D('City')->fetchAll());
		$communitys =  D('Community')->select();
		$this->assign('communitys', $communitys);
        $this->assign('detail', $detail);
        $this->display();
    }
    public function create(){
		 if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        if ($this->isPost()) {
            $data = $this->createCheck();
            if ($pinche_id = D('Pinche')->add($data)) {
                $this->fengmiMsg('发布成功', U('pinche/index'));
            }
            $this->fengmiMsg('发布失败');
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
    public function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->fengmiMsg('类型不能为空');
        }
		
		$data['city_id'] = $this->city_id;
        if (empty($data['city_id'])) {
            $this->fengmiMsg('参数错误');
        }
		
		$data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->fengmiMsg('区域不能为空');
        }
		
		$data['community_id'] = (int) $data['community_id'];
      
		
		$data['user_id'] = $this->uid;
        if (empty($data['user_id'])) {
            $this->fengmiMsg('您还没登录');
        }
		
		$data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->fengmiMsg('缩略图格式不正确');
        }
		
        $data['start_time'] = htmlspecialchars($data['start_time']);
        if (empty($data['start_time'])) {
            $this->fengmiMsg('出发时间不能为空');
        }
        $data['start_time_more'] = htmlspecialchars($data['start_time_more']);

        $data['toplace'] = htmlspecialchars($data['toplace']);
        if (empty($data['toplace'])) {
            $this->fengmiMsg('目的地不能为空');
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
<?php
class EleAction extends CommonAction
{
    private $create_fields = array('shop_id', 'distribution', 'is_open', 'is_pay', 'is_fan', 'fan_money', 'is_new', 'full_money', 'new_money', 'logistics', 'since_money', 'sold_num', 'month_num', 'intro', 'orderby');
    protected $ele;
    public function _initialize(){
        parent::_initialize();
        $getEleCate = D('Ele')->getEleCate();
        $this->assign('getEleCate', $getEleCate);
        $this->ele = D('Ele')->find($this->shop_id);
        if (empty($this->ele) && ACTION_NAME != 'apply') {
            $this->error('您还没有入住外卖频道', U('ele/apply'));
        }
        if (!empty($this->ele) && $this->ele['audit'] == 0) {
            $this->error('亲，您的申请正在审核中！');
        }
        $this->assign('ele', $this->ele);
    }
    public function index(){
		$file['small_file'] = D('Ele')->get_file_Code($this->shop_id,8);//生成二维码
		$file['middle_file'] = D('Ele')->get_file_Code($this->shop_id,15);//生成二维码
		$file['big_file'] = D('Ele')->get_file_Code($this->shop_id,100);//生成二维码
        $this->assign('file', $file);
        $this->display();
    }
    public function open(){
        $is_open = (int) $_POST['is_open'];
		$busihour = $_POST['busihour'];
        $is_radius = $_POST['is_radius'];
		$given_distribution = $_POST['given_distribution'];
		if($given_distribution !=0){
			if (!($Deliver = D('Delivery')->where(array('id'=>$given_distribution))->find())) {
				$this->baoError('不存在配送员ID');
			}
	    }
		$is_voice = (int) $_POST['is_voice'];
		$is_refresh = (int) $_POST['is_refresh'];
		$is_refresh_second = $_POST['is_refresh_second'];
		$tags = $_POST['tags'];
        D('Ele')->save(array(
			'shop_id' => $this->shop_id, 
			'is_open' => $is_open, 
			'busihour' => $busihour, 
			'is_radius' => $is_radius,
			'given_distribution' => $given_distribution,
			'is_print_deliver' => $is_print_deliver,
			'is_voice' => $is_voice,
			'is_refresh' => $is_refresh,
			'is_refresh_second' => $is_refresh_second,
			'tags' => $tags
		));
        $this->baoSuccess('操作成功！', U('ele/index'));
    }
    public function apply(){
        $this->assign('area', D('Area')->fetchAll());
        $this->assign('city', D('City')->fetchAll());
        if ($this->isPost()) {
            $data = $this->applyCheck();
            $obj = D('Ele');
            $cate = $this->_post('cate', false);
            $cate = implode(',', $cate);
            $data['cate'] = $cate;
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('ele/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function applyCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['shop_id'] = $this->shop_id;
        if (empty($data['shop_id'])) {
            $this->baoError('ID不能为空');
        }
        if (!($shop = D('Shop')->find($data['shop_id']))) {
            $this->baoError('商家不存在');
        }
        $data['shop_name'] = $shop['shop_name'];
        $data['lng'] = $shop['lng'];
        $data['lat'] = $shop['lat'];
        $data['area_id'] = $shop['area_id'];
        $data['city_id'] = $shop['city_id'];
        $data['is_open'] = (int) $data['is_open'];
        $data['is_pay'] = (int) $data['is_pay'];
        $data['is_fan'] = (int) $data['is_fan'];
        $data['fan_money'] = (int) ($data['fan_money'] * 100);
        $data['is_new'] = (int) $data['is_new'];
        $data['full_money'] = (int) ($data['full_money'] * 100);
        $data['new_money'] = (int) ($data['new_money'] * 100);
        $data['logistics'] = (int) ($data['logistics'] * 100);
        $data['since_money'] = (int) ($data['since_money'] * 100);
        $data['distribution'] = (int) $data['distribution'];
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('说明不能为空');
        }
        return $data;
    }
}
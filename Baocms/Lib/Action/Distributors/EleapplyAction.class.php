<?php





class EleapplyAction extends CommonAction {



   private $create_fields = array('shop_id', 'distribution', 'is_open', 'is_pay', 'is_fan', 'fan_money', 'is_new', 'full_money', 'new_money', 'logistics', 'since_money', 'sold_num', 'month_num', 'intro', 'orderby');

    protected $ele;

	

	public function _initialize() {

        parent::_initialize();

		

		$audit = D('Ele')->find($this->shop_id);

        $this->assign('audit',  $audit);

		

        $getEleCate = D('Ele')->getEleCate();

        $this->assign('getEleCate', $getEleCate);

        $this->ele = D('Ele')->find($this->shop_id);

        $this->assign('ele', $this->ele);

    }



	 public function apply() {

        $this->assign("area", D("Area")->fetchAll());

        $this->assign("city", D("City")->fetchAll());



        if ($this->isPost()) {

            $data = $this->applyCheck();

            $obj = D('Ele');

            $cate = $this->_post('cate', false);

            $cate = implode(',', $cate);

            $data['cate'] = $cate;

            if ($obj->add($data)) {

                $this->fengmiMsg('申请成功，请等待网站管理员审核', U('index/index'));

            }

            $this->error('操作失败！');

        } else {

			$lat = addslashes(cookie('lat'));

            $lng = addslashes(cookie('lng'));

            if (empty($lat) || empty($lng)) {

                $lat = $this->_CONFIG['site']['lat'];

                $lng = $this->_CONFIG['site']['lng'];

            }

            if ($business_id = (int) $this->_param('business_id')) {

                $map['business_id'] = $business_id;

                $this->assign('business_id', $business_id);

            }

			$this->assign('citys', D('City')->fetchAll());

			$this->assign('areas', D('Area')->fetchAll());

            $this->assign('business', D('Business')->fetchAll());

            $this->assign('lat', $lat);

            $this->assign('lng', $lng);

            $areas = D('Area')->fetchAll();

            $this->display();

        }

    }



    private function applyCheck() {

        $data = $this->checkFields($this->_post('data', false), $this->create_fields);

        $data['shop_id'] = $this->shop_id;

        if (empty($data['shop_id'])) {

            $this->fengmiMsg('ID不能为空');

        }

        if (!$shop = D('Shop')->find($data['shop_id'])) {

            $this->fengmiMsg('商家不存在');

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

            $this->fengmiMsg('说明不能为空');

        }

        return $data;

    }

}


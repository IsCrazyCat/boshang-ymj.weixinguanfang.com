<?php
class ZheAction extends CommonAction {

    private $create_fields = array('shop_id','city_id','area_id','zhe_name','cate_id', 'photo','bg_date','end_date','week_id','date_id', 'walkin', 'person', 'limit', 'description','credit','orderby', 'views','content');
    private $edit_fields = array('shop_id','city_id','area_id','zhe_name','cate_id', 'photo','bg_date','end_date','week_id','date_id', 'walkin', 'person', 'limit', 'description','credit','orderby', 'views','content');
    public function _initialize() {
        parent::_initialize();
        $this->getZheWeek = D('Zhe')->getZheWeek();
        $this->assign('weeks',  $this->getZheWeek);
        $this->getZheDate = D('Zhe')->getZheDate();
        $this->assign('dates',  $this->getZheDate);
		$this->assign('cates', D('Shopcate')->fetchAll());
    }

    
    public function index() {
        $Zhe = D('Zhe');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['hotel_name'] = array('LIKE', '%' . $keyword . '%');
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
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $Zhe->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Zhe->where($map)->order(array('zhe_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

    public function create() {
        $obj = D('Zhe');
        if ($this->isPost()) {
            $data = $this->createCheck();
			$week_id = $this->_post('week_id', false);
            $week_id = implode(',', $week_id);
            $data['week_id'] = $week_id;
			
			$date_id = $this->_post('date_id', false);
            $date_id = implode(',', $date_id);
            $data['date_id'] = $date_id;
			
            if ($Zhe_id = $obj->add($data)) {
                $this->baoSuccess('操作成功', U('zhe/index'));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
       
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
	    $data['shop_id'] = (int)$data['shop_id'];
        if(empty($data['shop_id'])){
            $this->baoError('商家不能为空');
        }elseif(!$shop = D('Shop')->find($data['shop_id'])){
            $this->baoError('商家不存在');
        }
		$data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        
        $data['zhe_name'] = htmlspecialchars($data['zhe_name']);
        if (empty($data['zhe_name'])) {
            $this->baoError('五折卡名称不能为空');
        }
		$data['cate_id'] = (int)$data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('五折卡分类没有选择');
        }
		$data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->baoError('开始时间不能为空');
        }
        if (!isDate($data['bg_date'])) {
            $this->baoError('开始时间格式不正确');
        } $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->baoError('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->baoError('结束时间格式不正确');
        }
		$data['walkin'] = (int)$data['walkin'];
		$data['person'] = htmlspecialchars($data['person']);
		$data['limit'] = (int)$data['limit'];
		$data['description'] = SecurityEditorHtml($data['description']);
        if (empty($data['description'])) {
            $this->baoError('五折卡说明不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['description'])) {
            $this->baoError('五折卡说明含有敏感词：' . $words);
        }
		$data['credit'] = (int)$data['credit'];
		$data['views'] = (int)$data['views'];
		$data['orderby'] = (int)$data['orderby'];
		$data['content'] = SecurityEditorHtml($data['content']);
        if (empty($data['content'])) {
            $this->baoError('五折卡详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['content'])) {
            $this->baoError('五折卡详情含有敏感词：' . $words);
        } 
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 1;
        return $data;
    }
    
    
    public function edit($zhe_id = 0) {

        if ($zhe_id = (int) $zhe_id) {
            $obj = D('Zhe');
            if (!$detail = $obj->find($zhe_id)) {
                $this->baoError('请选择要编辑的五折卡');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['zhe_id'] = $zhe_id;
				
				$week_id = $this->_post('week_id', false);
				$week_id = implode(',', $week_id);
				$data['week_id'] = $week_id;
				
				$date_id = $this->_post('date_id', false);
				$date_id = implode(',', $date_id);
				$data['date_id'] = $date_id;
			
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('zhe/index'));
                }
                $this->baoError('操作失败');
            } else {
				$this->assign('shop',D('Shop')->find($detail['shop_id']));
                $this->assign('week_ids', $week_ids = explode(',', $detail['week_id']));
				$this->assign('date_ids', $date_ids = explode(',', $detail['date_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的五折卡');
        }
    }
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['shop_id'] = (int)$data['shop_id'];
        if(empty($data['shop_id'])){
            $this->baoError('商家不能为空');
        }elseif(!$shop = D('Shop')->find($data['shop_id'])){
            $this->baoError('商家不存在');
        }
		$data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        
        $data['zhe_name'] = htmlspecialchars($data['zhe_name']);
        if (empty($data['zhe_name'])) {
            $this->baoError('五折卡名称不能为空');
        }
		$data['cate_id'] = (int)$data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('五折卡分类没有选择');
        }
		$data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->baoError('开始时间不能为空');
        }
        if (!isDate($data['bg_date'])) {
            $this->baoError('开始时间格式不正确');
        } $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->baoError('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->baoError('结束时间格式不正确');
        }
		$data['walkin'] = (int)$data['walkin'];
		$data['person'] = htmlspecialchars($data['person']);
		$data['limit'] = (int)$data['limit'];
		$data['description'] = SecurityEditorHtml($data['description']);
        if (empty($data['description'])) {
            $this->baoError('五折卡说明不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['description'])) {
            $this->baoError('五折卡说明含有敏感词：' . $words);
        }
		$data['credit'] = (int)$data['credit'];
		$data['views'] = (int)$data['views'];
		$data['orderby'] = (int)$data['orderby'];
		$data['content'] = SecurityEditorHtml($data['content']);
        if (empty($data['content'])) {
            $this->baoError('五折卡详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['content'])) {
            $this->baoError('五折卡详情含有敏感词：' . $words);
        } 
        return $data;
    }
    
    
    public function delete($Zhe_id = 0) {
        $obj = D('Zhe');
        if (is_numeric($Zhe_id) && ($Zhe_id = (int) $Zhe_id)) {
            $obj->save(array('zhe_id' => $Zhe_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('zhe/index'));
        } else {
            $Zhe_id = $this->_post('zhe_id', false);
            if (is_array($Zhe_id)) {
                foreach ($Zhe_id as $id) {
                    $obj->save(array('zhe_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('zhe/index'));
            }
            $this->baoError('请选择要删除的五折卡');
        }
    }

    public function audit($Zhe_id = 0) {
        $obj = D('Zhe');
        if (is_numeric($Zhe_id) && ($Zhe_id = (int) $Zhe_id)) {
            $obj->save(array('zhe_id' => $Zhe_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('zhe/index'));
        } else {
            $Zhe_id = $this->_post('zhe_id', false);
            if (is_array($Zhe_id)) {
                foreach ($Zhe_id as $id) {
                    $obj->save(array('zhe_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('zhe/index'));
            }
            $this->baoError('请选择要审核的五折卡');
        }
    }

    
}

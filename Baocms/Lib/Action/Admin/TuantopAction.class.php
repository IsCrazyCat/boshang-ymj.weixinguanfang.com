<?php


class TuantopAction extends CommonAction { //按逻辑  instructions  和  details 要分表出去
    private $edit_fields = array('shop_id', 'top_date', );
    public function _initialize() {
        parent::_initialize();
        $this->Tuancates = D('Tuancate')->fetchAll();
        $this->assign('cates', $this->Tuancates);
        $this->assign('ranks',D('Userrank')->fetchAll());
    }

    public function index() {
        $Tuan = D('Tuan');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0,'audit' => 1, 'end_date' => array('EGT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Tuancate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = ($audit === 1 ? 1 : 0);
            $this->assign('audit', $audit);
        }
        $count = $Tuan->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Tuan->where($map)->order(array('top_date' => 'desc', 'create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $val = $Tuan->_format($val);
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('cates', D('Tuancate')->fetchAll());
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }


	public function edit($tuan_id = 0) {
        $tuan_id = (int) $tuan_id;
        $detail = D('Tuan')->find($tuan_id);
        if (empty($detail)) {
            $this->baoError('没有该内容');
        }
        if ($this->isPost()) {
			
			
			
            if ($top_date = $this->_param('top_date', 'htmlspecialchars')) {
				
				if($top_date > $detail['end_date']){
           		  	 $this->baoError('置顶时间不得大于商品结束时间');
            	}
			
                $data = array('tuan_id' => $tuan_id, 'top_date' => $top_date);
                if (D('Tuan')->save($data)) {
                    $this->baoSuccess('操作成功', U('tuantop/index'));
                }
            }
            $this->baoError('请填写时间');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }

   
 public function top(){
        if(!$tuan_id = (int)  $this->_get('tuan_id')){
            $this->baoError('参数错误');
        }
        if(!$detail = D('Tuan')->find($tuan_id)){
            $this->baoError('参数错误');
        }
        $day = (int)$this->_get('day');
        $mday = 0;
        switch ($day){
            case 7:
               $mday = $day = 7;
                break;
            default:
                $day = 30;
                $mday = 30;
                break;
        }
        $top_date = date('Y-m-d',NOW_TIME + $day * 86400);
		
        if($detail['top_date'] > TODAY){
           $top_date = date('Y-m-d',strtotime($detail['top_date']) + $day*86400);
        }
		
		if($top_date > $detail['end_date']){
           $this->baoError('置顶时间不得大于商品结束时间');
        }else{
			D('Tuan')->save(array('top_date'=>$top_date,'tuan_id'=>$tuan_id));
			$this->baoSuccess('操作成功',U('tuantop/index'));	
		}
        
    }
    
   

   

}

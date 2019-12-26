<?php
class  AppointorderAction extends CommonAction{
	
    public function index(){
        $Appointorder = D('Appointorder');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array('closed'=>0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|tel|contents'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }  
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $Appointorder ->where($map)->count(); 
        $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $list = $Appointorder->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		$shop_ids = array();
        foreach ($list as $key => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }		
        $this->assign('list', $list); 
        $this->assign('page', $show); 
		$this->assign('cates', D('Appointcate')->fetchAll());
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->display(); 
    }
    
    public function edit($order_id){
        if ($order_id = (int) $order_id) {
            $obj = D('Appointorder');
            if (!$detail = $obj->find($order_id)) {
                $this->baoError('请选择要编辑的家政');
            }
            if ($this->isPost()) {
                $data['is_real'] = (int)$this->_post('is_real');
                $data['num']     = (int)  $this->_post('num');
                $data['money']    = (int) ($this->_post('money')*100);
                $data['order_id'] = $order_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('appointorder/index'));
                }
                $this->baoError('操作失败');
            } else {
    
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的活动');
        }
        
        
    }
    
     public function delete($order_id = 0) {
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Appointorder');
            $obj->delete($order_id);
            $this->baoSuccess('删除成功！', U('appointorder/index'));
        } else {
            $order_id = $this->_post('housework_id', false);
            if (is_array($order_id)) {
                $obj = D('Appointorder');
                foreach ($order_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('appointorder/index'));
            }
            $this->baoError('请选择要删除的预约');
        }
    }
	
	
    
}
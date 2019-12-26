<?php

class WorkAction extends CommonAction{
    
    public function index() {
        $Work = D('Work');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array();
         if($keyword = $this->_param('keyword','htmlspecialchars')){
            $map['title'] = array('LIKE','%'.$keyword.'%');
            $this->assign('keyword',$keyword);
        }
        if($shop_id = (int)$this->_param('shop_id')){
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name',$shop['shop_name']);
            $this->assign('shop_id',$shop_id);
        }
        if($audit = (int)$this->_param('audit')){
            $map['audit'] = ($audit === 1 ? 1:0);
            $this->assign('audit',$audit);
        }
        $count = $Work->where($map)->count(); // 查询满足要求的总记录数 

        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Work->where($map)->order(array('work_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        $shop_ids = array();
        foreach($list as $k=>$val){
            if($val['shop_id']){
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        if($shop_ids){
            $this->assign('shops',D('Shop')->itemsByIds($shop_ids));
        }
        
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
    
    
    public function audit($work_id = 0) {
        if (is_numeric($work_id) && ($work_id = (int) $work_id)) {
            $obj = D('Work');
            $obj->save(array('work_id' => $work_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('work/index'));
        } else {
            $work_id = $this->_post('work_id', false);
            if (is_array($work_id)) {
                $obj = D('Work');
                foreach ($work_id as $id) {
                    $obj->save(array('work_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('work/index'));
            }
            $this->baoError('请选择要审核的招聘信息');
        }
    }
    
    public function delete($work_id = 0) {
        if (is_numeric($work_id) && ($work_id = (int) $work_id)) {
            $obj = D('Work');
            $obj->delete($work_id);
            $this->baoSuccess('删除成功！', U('work/index'));
        } else {
            $work_id = $this->_post('work_id', false);
            if (is_array($work_id)) {
                $obj = D('Work');
                foreach ($work_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('work/index'));
            }
            $this->baoError('请选择要删除的招聘信息');
        }
    }

}
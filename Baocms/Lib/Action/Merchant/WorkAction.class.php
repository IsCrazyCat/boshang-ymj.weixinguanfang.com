<?php

/****************** 版权声明 ******************************
 *
 *----------------合肥生活宝网络科技有限公司-----------------
 *----------------      www.taobao.com    -----------------
 *QQ:800026911  
 *电话:0551-63641901  
 *EMAIL：youge@baocms.com
 * 
 ***************  未经许可不得用于商业用途  ****************/

class  WorkAction extends CommonAction{
    private $edit_fields = array('title', 'money1', 'money2','num','intro','work_time','expir_date');

    public function index() {
        $Work = D('Work');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array('shop_id' => $this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Work->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Work->where($map)->order(array('work_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->editCheck(); //这里和 编辑的字段差不多
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $obj = D('Work');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('work/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    
    public function edit($work_id = 0) {
        if (empty($work_id)) {
            $this->error('请选择需要编辑的内容操作');
        }
        $work_id = (int) $work_id;
        $obj = D('Work');
        $detail = $obj->find($work_id);
        if (empty($detail) || $detail['shop_id'] != $this->shop_id) {
            $this->error('请选择需要编辑的内容操作');
        }
        if ($this->isPost()) {

            $data = $this->editCheck();
            $data['work_id'] = $work_id;
            if (false !== $obj->save($data)) {
                $this->baoSuccess('操作成功', U('work/edit', array('work_id' => $work_id)));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['shop_id']        = $this->shop_id;
        $data['area_id']        = $this->shop['area_id'];
        $data['business_id']    = $this->shop['business_id'];
        $data['city_id']        = $this->shop['city_id'];
        $data['lng']            = $this->shop['lng'];
        $data['lat']            = $this->shop['lat'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        
        $data['num']        = (int)$data['num'];
        $data['money1']     =    (int)$data['money1'];
        $data['money2']     = (int)$data['money2'];
        $data['work_time']  = htmlspecialchars($data['work_time']);
        if(empty($data['work_time'])){
            $this->baoError('工作时间不能为空');            
        }        
        $data['expir_date'] = htmlspecialchars($data['expir_date']);
        if(!isDate($data['expir_date'])){
            $this->baoError('过期时间不能为空');
        }
        $data['intro'] = SecurityEditorHtml($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('职位描述不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->baoError('职位描述含有敏感词：' . $words);
        }
        return $data;
    }
    
    
   
    
}
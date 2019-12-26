<?php



class AwardAction extends CommonAction {
    private $create_fields = array('title', 'type', 'explain', 'expire_date');
    private $edit_fields = array('title', 'type', 'explain', 'expire_date');
    
    public function _initialize() {
        parent::_initialize();
       
        $this->assign('types', D('Award')->getCfg());
    }

    public function index() {
        $Award = D('Award');
        import('ORG.Util.Page'); // 导入分页类 
        $map = array('shop_id' => $this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Award->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Award->where($map)->order(array('award_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $url = U('Wap/award/index', array('award_id' => $val['award_id']));
            $url = __HOST__ . $url;
            $tooken = 'award_' . $val['award_id'];
            $file = baoQrCode($tooken, $url);
            $list[$k]['file'] = $file;
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Award');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('award/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('活动不能为空');
        }
        $data['shop_id'] = $this->shop_id;
        $data['type'] = htmlspecialchars($data['type']);
        if (empty($data['type'])) {
            $this->baoError('类型不能为空');
        }
        $data['explain'] = htmlspecialchars($data['explain']);
        if (empty($data['explain'])) {
            $this->baoError('说明不能为空');
        }
        $data['expire_date'] = htmlspecialchars($data['expire_date']);
        if (empty($data['expire_date'])) {
            $this->baoError('结束时间不能为空');
        }
        if (!isDate($data['expire_date'])) {
            $this->baoError('结束时间格式不正确');
        } 
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    
      public function edit($award_id = 0) {
        if ($award_id = (int) $award_id) {
            $obj = D('Award');
            if (!$detail = $obj->find($award_id)) {
                $this->baoError('请选择要编辑的抽奖管理');
            }
            if($detail['shop_id'] != $this->shop_id){
                $this->error('不可操作其他人的！');
            }
            
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['award_id'] = $award_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('award/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的抽奖管理');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('活动不能为空');
        }
        $data['type'] = htmlspecialchars($data['type']);
        if (empty($data['type'])) {
            $this->baoError('类型不能为空');
        } $data['explain'] = htmlspecialchars($data['explain']);
        if (empty($data['explain'])) {
            $this->baoError('说明不能为空');
        } $data['expire_date'] = htmlspecialchars($data['expire_date']);
        if (empty($data['expire_date'])) {
            $this->baoError('结束时间不能为空');
        }
        if (!isDate($data['expire_date'])) {
            $this->baoError('结束时间格式不正确');
        }
        return $data;
    }
    
    public function  audit($award_id){
        $award_id = (int)$award_id;
          $obj = D('Award');
        if (!$detail = $obj->find($award_id)) {
            $this->baoError('请选择要编辑的抽奖管理');
        }
        if($detail['shop_id'] != $this->shop_id){
            $this->error('不可操作其他人的！');
        }
        $data= array(
            'is_online' => 1,
            'award_id' => $award_id
        );
        if($obj->save($data)){
            $this->baoSuccess('启用成功！',U('award/index'));
        }else{
            $this->baoError('启用失败');
        }
        
    }
    
    
    
}
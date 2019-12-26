<?php
class MessageAction extends CommonAction {
    private $create_fields = array('msg_id', 'parent_id', 'send_id', 'content');
    private $edit_fields = array('msg_id', 'parent_id', 'send_id', 'content');

    public function index(){
	
		$Message = D('Message');
		import('ORG.Util.Page'); // 导入分页类
        $map = array('parent_id' => 0);
		
		 if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['content'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
        $count = $Message->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Message->where($map)->order('msg_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['send_id']) {
                $ids[$val['send_id']] = $val['send_id'];
            }
        }
		$users = D('Users')->itemsByIds($ids);
        $this->assign('users',$users );
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
	
	

 
	
	
	
	public function detail($msg_id){
		
		$Message = D('Message');
		$detail = $Message->find($msg_id);
		
		 if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['content'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
		$Message->save(array('read_time'=>time(),'msg_id'=>$msg_id));
		$this->assign('user', D('Users')->find($detail['send_id'])); // 赋值数据集
		$this->assign('detail', $detail); // 赋值数据集
		import('ORG.Util.Page'); // 导入分页类
        $map = array('parent_id'=>$msg_id);
        $count = $Message->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Message->where($map)->order('msg_id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['send_id']) {
                $ids[$val['send_id']] = $val['send_id'];
            }
        }
		$user = $this->uid;
		$users = D('Users')->itemsByIds($ids);
        $this->assign('users',$users);
		$this->assign('user',$user);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板		$this->display(); // 输出模板
	}
	
	
	 public function edit($msg_id = 0) {
        if ($msg_id = (int) $msg_id) {
            $obj = D('Message');
            if (!$detail = $obj->find($msg_id)) {
                $this->baoError('请选择要编辑的邻居回复');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['msg_id'] = $msg_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('message/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的邻居回复');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);

			$data['content'] = htmlspecialchars($data['content']);
			$data['create_time'] = time();
			
			if($data['content']==''){
				$this->baoError('信息内容不能为空1！');
				die;
			}
			
        return $data;
    }

	
	public function delete($msg_id = 0) {
        if (is_numeric($msg_id) && ($msg_id = (int) $msg_id)) {
            $obj = D('Message');
			$obj->where(array('parent_id'=>$msg_id))->delete();
			$obj->where(array('msg_id'=>$msg_id))->delete();
            $this->baoSuccess('删除成功！', U('message/index'));
        } else {
            $msg_id = $this->_post('msg_id', false);
            if (is_array($msg_id)) {
                $obj = D('Message');
                foreach ($msg_id as $id) {
					$obj->delete($id);
                    $obj->where(array('parent_id'=>$id))->delete();
                }
                $this->baoSuccess('删除成功！', U('message/index'));
            }
			
            $this->baoError('请选择要删除的邻居交友');
        }
    }
	

	
	
}

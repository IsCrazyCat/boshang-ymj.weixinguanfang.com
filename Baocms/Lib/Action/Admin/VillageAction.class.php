<?php
class VillageAction extends CommonAction{
    private $create_fields = array('cate','name', 'addr', 'tel', 'pic', 'user_id', 'city_id', 'area_id', 'lng', 'lat','profiles', 'orderby', 'info', 'is_bbs');
    private $create_worker_fields = array('name', 'photo', 'village_id', 'job','orderby');
    private $edit_fields = array('cate','name', 'addr', 'tel', 'pic', 'user_id', 'city_id', 'area_id', 'lng', 'lat', 'orderby', 'info', 'profiles','is_bbs');
    private $look = 0;
	
    protected function _initialize(){
		$getVillageCate = D('Village')->getVillageCate();//调用
        $this->assign('getVillageCate', $getVillageCate);
    }
	
	  public function index() {
        $Village = D('Village');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        //$users = $this->_param('data', false);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|addr'] = array('LIKE', '%' . $keyword . '%');
        }
        if ($this->look) {
            $map['user_id'] = $_SESSION['admin']['admin_id'];
        }
        $count = $Village->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Village->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $list[$k] = $Village->_format($val);
            $list[$k]['username'] = D('Admin')->where('admin_id=' . $val['user_id'])->getField('username');
        }
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Village');
			$cate = $this->_post('cate', false);
            $cate = implode(',', $cate);
            $data['cate'] = $cate;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('village/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('areas', D('Area')->fetchAll());
            $this->display();
        }
    }
	
	  private function createCheck($iswork = 0)
    {
        if ($iswork) {
            $data = $this->checkFields($this->_post('data', false), $this->create_worker_fields);
            $data['name'] = htmlspecialchars($data['name']);
            if (empty($data['name'])) {
                $this->baoError('姓名不能为空');
            }
            $data['job'] = htmlspecialchars($data['job']);
            if (empty($data['job'])) {
                $this->baoError('职务不能为空');
            }
        } else {
            $data = $this->checkFields($this->_post('data', false), $this->create_fields);
            $data['info'] = $data['info'];
            $data['name'] = htmlspecialchars($data['name']);
            if (empty($data['name'])) {
                $this->baoError('社区村名称不能为空');
            }
            $data['addr'] = htmlspecialchars($data['addr']);
            if (empty($data['addr'])) {
                $this->baoError('地址不能为空');
            }
            $data['city_id'] = (int) $data['city_id'];
            $data['area_id'] = (int) $data['area_id'];
            if (empty($data['area_id'])) {
                $this->baoError('所在区域不能为空');
            }
            $data['user_id'] = (int) $data['user_id'];
            if (empty($data['user_id'])) {
                $this->baoError('管理员不能为空');
            }
			$data['profiles'] = htmlspecialchars($data['profiles']);
            $data['orderby'] = (int) $data['orderby'];
            $data['lng'] = htmlspecialchars($data['lng']);
            $data['lat'] = htmlspecialchars($data['lat']);
            $data['is_bbs'] = (int) $data['is_bbs'];
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
        }
        return $data;
    }
	
	 public function edit($village_id = 0){
        if ($village_id = (int) $village_id) {
            $obj = D('Village');
            if (!($detail = $obj->find($village_id))) {
                $this->baoError('请选择要编辑的社区村');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['village_id'] = $village_id;
				$cate = $this->_post('cate', false);
                $cate = implode(',', $cate);
                $data['cate'] = $cate;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('Village/index'));
                }
                $this->baoError('操作失败');
            } else {
				$this->assign('users', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
				$cate = explode(',', $detail['cate']);
				$this->assign('cate', $cate);
                $this->assign('areas', D('Area')->fetchAll());
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的社区村');
        }
    }
	
	
	 private function editCheck($iswork = 0){
        if ($iswork) {
            $data = $this->checkFields($this->_post('data', false), $this->create_worker_fields);
            $data['name'] = htmlspecialchars($data['name']);
            if (empty($data['name'])) {
                $this->baoError('姓名不能为空');
            }
            $data['job'] = htmlspecialchars($data['job']);
            if (empty($data['job'])) {
                $this->baoError('职务不能为空');
            }
        } else {
            $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
            $data['name'] = htmlspecialchars($data['name']);
            if (empty($data['name'])) {
                $this->baoError('名称不能为空');
            }
            $data['addr'] = htmlspecialchars($data['addr']);
            if (empty($data['addr'])) {
                $this->baoError('地址不能为空');
            }
            $data['city_id'] = (int) $data['city_id'];
            $data['area_id'] = (int) $data['area_id'];
            if (empty($data['area_id'])) {
                $this->baoError('所在区域不能为空');
            }
            $data['user_id'] = (int) $data['user_id'];
            if (empty($data['user_id'])) {
                $this->baoError('管理员不能为空');
            }
			$data['profiles'] = htmlspecialchars($data['profiles']);
            $data['orderby'] = (int) $data['orderby'];
            $data['is_bbs'] = (int) $data['is_bbs'];
            $data['lng'] = htmlspecialchars($data['lng']);
            $data['lat'] = htmlspecialchars($data['lat']);
        }
        return $data;
    }
	
    public function suggestion(){
        $Village = D('Village_suggestion');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('village_id', $_GET['village_id']);
        $map['village_id'] = $_GET['village_id'];
        $count = $Village->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Village->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
    }
    public function bbs()
    {
        $Village = D('Village_bbs');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('village_id', $_GET['village_id']);
        $map['village_id'] = $_GET['village_id'];
        $count = $Village->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Village->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
    }
    public function bbs_delete($post_id = 0)
    {
        $village_id = $post_id;
        if (is_numeric($village_id) && ($village_id = (int) $village_id)) {
            $obj = D('Village_bbs');
            $obj->delete($village_id);
            D('Villagebbsreplys')->where('post_id=' . $village_id)->delete();
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('village/index'));
        } else {
            $village_id = $this->_post('post_id', false);
            if (is_array($village_id)) {
                $obj = D('Village_bbs');
                foreach ($village_id as $id) {
                    $obj->delete($id);
                    D('Villagebbsreplys')->where('post_id=' . $id)->delete();
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('village/index'));
            }
            $this->baoError('请选择要删除的帖子');
        }
    }
    public function bbs_view($post_id = 0)
    {
        if ($id = (int) $post_id) {
            $obj = D('Village_bbs');
            if (!($detail = $obj->find($id))) {
                $this->baoError('请选择要查看的帖子');
            }
            if ($this->isPost()) {
                $data = $this->_post('data', false);
                $data['post_id'] = $post_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('Village/bbs_view', array('post_id' => $data['post_id'])));
                }
                $this->baoError('操作失败');
            } else {
                import('ORG.Util.Page');
                // 导入分页类 
                $map = array('post_id' => $post_id);
                $replys = D('Villagebbsreplys');
                $count = $replys->where($map)->count();
                // 查询满足要求的总记录数
                $Page = new Page($count, 5);
                // 实例化分页类 传入总记录数和每页显示的记录数
                $show = $Page->show();
                // 分页显示输出
                $list = $replys->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
                foreach ($list as $l => $k) {
                    $list[$l]['user_name'] = D('users')->where('user_id = ' . $k['user_id'])->getField('nickname');
                }
                $this->assign('list', $list);
                // 赋值数据集
                $this->assign('page', $show);
                // 赋值分页输出
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要查看的帖子');
        }
    }
    public function bbs_audit($post_id = 0)
    {
        if (is_numeric($post_id) && ($post_id = (int) $post_id)) {
            $obj = D('Village_bbs');
            $detail = $obj->find($post_id);
            $obj->save(array('post_id' => $post_id, 'audit' => 1));
            $this->baoSuccess('审核社区帖子成功！', U('village/bbs', array('village_id' => $detail['village_id'])));
        } else {
            $post_id = $this->_post('post_id', false);
            if (is_array($post_id)) {
                $obj = D('Village_bbs');
                $detail = $obj->find($post_id);
                foreach ($post_id as $id) {
                    $obj->save(array('post_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('批量审核成功！', U('village/bbs', array('village_id' => $detail['village_id'])));
            }
            $this->baoError('请选择要审核社区的帖子');
        }
    }
    public function bbs_replys_audit($reply_id = 0)
    {
        if (is_numeric($reply_id) && ($reply_id = (int) $reply_id)) {
            $obj = D('Villagebbsreplys');
            $obj->save(array('reply_id' => $reply_id, 'audit' => 1));
            $this->baoSuccess('审回复成功！', U('village/bbs_view', array('post_id' => $reply_id)));
        } else {
            $reply_id = $this->_post('reply_id', false);
            if (is_array($reply_id)) {
                $obj = D('Villagebbsreplys');
                foreach ($reply_id as $id) {
                    $obj->save(array('reply_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('批量审核成功！', U('village/bbs_view', array('post_id' => $reply_id)));
            }
            $this->baoError('请选择要审核社区回复');
        }
    }
    public function notice()
    {
        $Village = D('Village_notice');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('village_id', $_GET['village_id']);
        if ($_GET['type']) {
            $map['type'] = $_GET['type'];
        }
        $map['village_id'] = $_GET['village_id'];
        $count = $Village->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Village->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
    }
    public function worker()
    {
        $Village = D('Village_worker');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|job'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('village_id', $_GET['village_id']);
        $map['village_id'] = $_GET['village_id'];
        $count = $Village->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Village->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
    }
  
    public function notice_create()
    {
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['title'] = htmlspecialchars($data['title']);
            if (empty($data['title'])) {
                $this->baoError('标题不能为空');
            }
            if (empty($data['context'])) {
                $this->baoError('内容不能为空');
            }
            $data['addtime'] = NOW_TIME;
            $obj = D('Village_notice');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('village/notice', array('village_id' => $data['village_id'])));
            }
            $this->baoError('操作失败！');
        } else {
            if ($_GET['village_id']) {
                $this->assign('village_id', $_GET['village_id']);
                $this->assign('type', $_GET['type']);
                $this->display();
            }
        }
    }
  
    public function worker_create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck(1);
            $obj = D('Village_worker');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('village/worker', array('village_id' => $data['village_id'])));
            }
            $this->baoError('操作失败！');
        } else {
            if ($_GET['village_id']) {
                $this->assign('village_id', $_GET['village_id']);
                $this->display();
            }
        }
    }
   
    public function suggestion_edit($id = 0)
    {
        if ($id = (int) $id) {
            $obj = D('Village_suggestion');
            if (!($detail = $obj->find($id))) {
                $this->baoError('请选择要编辑的意见');
            }
            if ($this->isPost()) {
                $data = $this->_post('data', false);
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('Village/suggestion', array('village_id' => $data['village_id'])));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的意见');
        }
    }
    public function worker_edit($id = 0)
    {
        if ($id = (int) $id) {
            $obj = D('Village_worker');
            if (!($detail = $obj->find($id))) {
                $this->baoError('请选择要编辑的工作人员');
            }
            if ($this->isPost()) {
                $data = $this->editCheck(1);
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('Village/worker', array('village_id' => $data['village_id'])));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的工作人员');
        }
    }
    public function reply($id = 0)
    {
        if ($id = (int) $id) {
            $obj = D('Village_suggestion');
            if (!($detail = $obj->find($id))) {
                $this->baoError('请选择要回复的意见');
            }
            if ($this->isPost()) {
                $data = $this->_post('data', false);
                $data['id'] = $id;
                $data['replytime'] = NOW_TIME;
                $data['type'] = 1;
                $data['user'] = $_SESSION['admin']['username'];
                if (false !== $obj->save($data)) {
                    //  $obj->cleanCache();
                    $this->baoSuccess('回复成功', U('Village/suggestion', array('village_id' => $data['village_id'])));
                }
                $this->baoError('回复成功');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要回复的意见');
        }
    }
    public function notice_edit($id = 0)
    {
        if ($id = (int) $id) {
            $obj = D('Village_notice');
            if (!($detail = $obj->find($id))) {
                $this->baoError('请选择要编辑的通知或是展示');
            }
            if ($this->isPost()) {
                $data = $this->_post('data', false);
                $data['title'] = htmlspecialchars($data['title']);
                if (empty($data['title'])) {
                    $this->baoError('标题不能为空');
                }
                if (empty($data['context'])) {
                    $this->baoError('内容不能为空');
                }
                $data['addtime'] = NOW_TIME;
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('Village/notice', array('village_id' => $data['village_id'])));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的通知或是展示');
        }
    }
    public function hots($business_id)
    {
        if ($business_id = (int) $business_id) {
            $obj = D('Business');
            if (!($detail = $obj->find($business_id))) {
                $this->baoError('请选择商圈');
            }
            $detail['is_hot'] = $detail['is_hot'] == 0 ? 1 : 0;
            $obj->save(array('business_id' => $business_id, 'is_hot' => $detail['is_hot']));
            $obj->cleanCache();
            $this->baoSuccess('操作成功', U('business/index'));
        } else {
            $this->baoError('请选择商圈');
        }
    }
   
    public function delete($village_id = 0)
    {
        if (is_numeric($village_id) && ($village_id = (int) $village_id)) {
            $obj = D('Village');
            $obj->delete($village_id);
            D('Village_worker')->where('village_id = ' . $village_id)->delete();
            D('Village_notice')->where('village_id = ' . $village_id)->delete();
            D('Village_suggestion')->where('village_id = ' . $village_id)->delete();
            // $obj->delete($village_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('village/index'));
        } else {
            $village_id = $this->_post('village_id', false);
            if (is_array($village_id)) {
                $obj = D('Village');
                foreach ($village_id as $id) {
                    $obj->delete($id);
                }
                D('Village_worker')->where('village_id = ' . $id)->delete();
                D('Village_notice')->where('village_id = ' . $id)->delete();
                D('Village_suggestion')->where('village_id = ' . $id)->delete();
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('village/index'));
            }
            $this->baoError('请选择要删除的社区村');
        }
    }
    public function worker_delete($id = 0)
    {
        $village_id = $id;
        if (is_numeric($village_id) && ($village_id = (int) $village_id)) {
            $obj = D('Village_worker');
            $obj->delete($village_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('village/index'));
        } else {
            $village_id = $this->_post('id', false);
            if (is_array($village_id)) {
                $obj = D('Village_worker');
                foreach ($village_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('village/index'));
            }
            $this->baoError('请选择要删除的工作人员');
        }
    }
    public function bbs_replys_delete($reply_id = 0, $post_id)
    {
        $village_id = $reply_id;
        if (is_numeric($village_id) && ($village_id = (int) $village_id)) {
            $obj = D('Villagebbsreplys');
            $obj->delete($village_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('village/bbs_view', array('post_id' => $post_id)));
        } else {
            echo 11111;
            $village_id = $this->_post('reply_id', false);
            if (is_array($village_id)) {
                $obj = D('Villagebbsreplys');
                foreach ($village_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('village/index'));
            }
            $this->baoError('请选择要删除的评论');
        }
    }
    public function notice_delete($id = 0)
    {
        $village_id = $id;
        if (is_numeric($village_id) && ($village_id = (int) $village_id)) {
            $obj = D('Village_notice');
            $obj->delete($village_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('village/index'));
        } else {
            $village_id = $this->_post('id', false);
            if (is_array($village_id)) {
                $obj = D('Village_notice');
                foreach ($village_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('village/index'));
            }
            $this->baoError('请选择要删除的工作人员');
        }
    }
    public function reply_delete($id = 0)
    {
        $village_id = $id;
        if (is_numeric($village_id) && ($village_id = (int) $village_id)) {
            $obj = D('Village_suggestion');
            $obj->delete($village_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('village/index'));
        } else {
            $village_id = $this->_post('id', false);
            if (is_array($village_id)) {
                $obj = D('Village_suggestion');
                foreach ($village_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('village/index'));
            }
            $this->baoError('请选择要删除的工作人员');
        }
    }
    public function child($area_id = 0)
    {
        $datas = D('Village')->fetchAll();
        $str = '<option value="0">请选择</option>';
        foreach ($datas as $val) {
            if ($val['area_id'] == $area_id) {
                $str .= '<option value="' . $val['village_id'] . '">' . $val['name'] . '</option>';
            }
        }
        echo $str;
        die;
    }
    // 新增选择村镇社区代理
    public function select()
    {
        $User = D('Village');
        import('ORG.Util.Page');
        // 导入分页类 
        //搜索
        $map = array('closed' => array('IN', '0,-1'));
        if ($account = $this->_param('name', 'htmlspecialchars')) {
            $map['name'] = array('LIKE', '%' . $account . '%');
            $this->assign('name', $name);
        }
        //搜索字段
        if ($nickname = $this->_param('addr', 'htmlspecialchars')) {
            $map['addr'] = array('LIKE', '%' . $addr . '%');
            $this->assign('addr', $addr);
        }
        //搜索字段
        $count = $User->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 8);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $pager = $Page->show();
        // 分页显示输出
        $list = $User->where($map)->order(array('village_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $pager);
        // 赋值分页输出
        $this->display();
    }
}
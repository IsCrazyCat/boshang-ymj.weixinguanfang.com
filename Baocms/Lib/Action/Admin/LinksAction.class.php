<?php
class LinksAction extends CommonAction{
    private $create_fields = array('city_id','link_name', 'link_url','link_email','link_intro','orderby');
    private $edit_fields = array('city_id','link_name', 'link_url','link_email','link_intro','orderby');
    public function index(){
        $Links = D('Links');
		import('ORG.Util.Page');
		$map = array('closed' => 0);
		if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['link_name|link_url'] = array('LIKE', '%' . $keyword . '%');
			$this->assign('keyword', $keyword);
        }
        $city_id = (int) $this->_param('city_id');
        if ($city_id) {
            $map['city_id'] = $city_id;
			$this->assign('city_id', $city_id);
        }
		$count = $Links->where($map)->count(); 
        $Page = new Page($count, 15);
        $show = $Page->show(); 
        $list = $Links->where($map)->order(array('orderby' => 'asc','create_time' => 'desc'))->select();
		$this->assign('citys', D('City')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Links');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('links/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
        $data['link_name'] = htmlspecialchars($data['link_name']);
        if (empty($data['link_name'])) {
            $this->baoError('链接名称不能为空');
        }
        $data['link_url'] = htmlspecialchars($data['link_url']);
        if (empty($data['link_url'])) {
            $this->baoError('链接地址不能为空');
        }
		$data['link_email'] = htmlspecialchars($data['link_email']);
		$data['link_intro'] = htmlspecialchars($data['link_intro']);
        $data['orderby'] = (int) $data['orderby'];
		$data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($link_id = 0) {
        if ($link_id = (int) $link_id) {
            $obj = D('Links');
            if (!($detail = $obj->find($link_id))) {
                $this->baoError('请选择要编辑的友情链接');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['link_id'] = $link_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('links/index'));
                }
                $this->baoError('操作失败');
            } else {
				$this->assign('areas', D('Area')->select());
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的友情链接');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
        $data['link_name'] = htmlspecialchars($data['link_name']);
        if (empty($data['link_name'])) {
            $this->baoError('链接名称不能为空');
        }
        $data['link_url'] = htmlspecialchars($data['link_url']);
        if (empty($data['link_url'])) {
            $this->baoError('链接地址不能为空');
        }
		$data['link_email'] = htmlspecialchars($data['link_email']);
		$data['link_intro'] = htmlspecialchars($data['link_intro']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function delete($link_id = 0){
        if (is_numeric($link_id) && ($link_id = (int) $link_id)) {
            $obj = D('Links');
            $obj->save(array('link_id' => $link_id, 'colsed' => 1));
            $this->baoSuccess('删除成功！', U('links/index'));
        } else {
            $link_id = $this->_post('link_id', false);
            if (is_array($link_id)) {
                $obj = D('Links');
                foreach ($link_id as $id) {
                    $obj->save(array('link_id' => $id, 'colsed' => 1));
                }
                $this->baoSuccess('删除成功！', U('links/index'));
            }
            $this->baoError('请选择要删除的友情链接');
        }
    }
	public function audit($link_id = 0){
        if (is_numeric($link_id) && ($link_id = (int) $link_id)) {
            $obj = D('Links');
			D('Email')->send_email_link_audit($link_id);
            $obj->save(array('link_id' => $link_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('links/index'));
        } else {
            $link_id = $this->_post('link_id', false);
            if (is_array($link_id)) {
                $obj = D('Links');
                foreach ($link_id as $id) {
                    $obj->save(array('link_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('links/index'));
            }
            $this->baoError('请选择要审核的友情链接');
        }
    }
}
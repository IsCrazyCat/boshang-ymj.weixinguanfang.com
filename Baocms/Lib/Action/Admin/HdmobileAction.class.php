<?php
class HdmobileAction extends CommonAction
{
    protected function _initialize()
    {
        parent::_initialize();
        $getHuoCate = D('Huodong')->getHuoCate();
        $this->assign('getHuoCate', $getHuoCate);
        $getPeopleCate = D('Huodong')->getPeopleCate();
        $this->assign('getPeopleCate', $getPeopleCate);
        $this->assign('traffic', D('Huodong')->getTraffic());
    }
    public function index()
    {
        $Huodong = D('Huodong');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('closed' => 0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Huodong->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Huodong->where($map)->order(array('huodong_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $huodong_ids = array();
        foreach ($list as $k => $val) {
            if ($val['huodong_id']) {
                $huodong_ids[$val['huodong_id']] = $val['huodong_id'];
            }
        }
        $this->assign('huodong', D('Huodong')->itemsByIds($huodong_ids));
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Huodong');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('hdmobile/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    public function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), array('title', 'addr', 'time', 'traffic', 'limit_num', 'user_id', 'intro', 'sex', 'photo', 'cate_id', 'shop_id', 'lat', 'lng'));
        $data['user_id'] = (int) $data['user_id'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['cate_id'] = (int) $data['cate_id'];
        $data['sex'] = (int) $data['sex'];
        $data['limit_num'] = (int) $data['limit_num'];
        $data['traffic'] = (int) $data['traffic'];
        $data['title'] = trim(htmlspecialchars($data['title']));
        if (empty($data['title'])) {
            $this->baoError('活动标题不能为空！');
        }
        $data['time'] = htmlspecialchars($data['time']);
        if (empty($data['time'])) {
            $this->baoError('活动时间不能为空！');
        }
        $data['intro'] = trim(htmlspecialchars($data['intro']));
        if (empty($data['intro'])) {
            $this->baoError('详情不能为空！');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function delete($huodong_id = 0)
    {
        if (is_numeric($huodong_id) && ($huodong_id = (int) $huodong_id)) {
            $obj = D('Huodong');
            $obj->save(array('huodong_id' => $huodong_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('hdmobile/index'));
        } else {
            $huodong_id = $this->_post('huodong_id', false);
            if (is_array($huodong_id)) {
                $obj = D('Huodong');
                foreach ($huodong_id as $id) {
                    $obj->save(array('huodong_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('hdmobile/index'));
            }
            $this->baoError('请选择要删除的活动');
        }
    }
    public function edit($huodong_id = 0)
    {
        $huodong_id = (int) $huodong_id;
        $obj = D('Huodong');
        $shop = D('Shop');
        $user = D('Users');
        if (!($detail = $obj->find($huodong_id))) {
            $this->baoError('请选择要编辑的活动');
        }
        if ($this->isPost()) {
            $data = $this->editCheck();
            $data['huodong_id'] = $huodong_id;
            if (false !== $obj->save($data)) {
                $this->baoSuccess('操作成功', U('hdmobile/index'));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $shop = $shop->find($datail['shop_id']);
            $this->assign('shop', $shop);
            $user = $user->find($datail['user_id']);
            $this->assign('user', $user);
            $this->display();
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), array('title', 'addr', 'time', 'traffic', 'limit_num', 'user_id', 'intro', 'sex', 'photo', 'cate_id', 'shop_id', 'lat', 'lng'));
        $data['user_id'] = (int) $data['user_id'];
        $data['cate_id'] = (int) $data['cate_id'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['sex'] = (int) $data['sex'];
        $data['limit_num'] = (int) $data['limit_num'];
        $data['traffic'] = (int) $data['traffic'];
        $data['title'] = trim(htmlspecialchars($data['title']));
        if (empty($data['title'])) {
            $this->baoError('活动标题不能为空！');
        }
        $data['intro'] = trim(htmlspecialchars($data['intro']));
        if (empty($data['intro'])) {
            $this->baoError('详情不能为空！');
        }
        $data['addr'] = trim(htmlspecialchars($data['addr']));
        if (empty($data['addr'])) {
            $this->baoError('活动地址不能为空！');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['time'] = $data['time'];
        if (empty($data['time'])) {
            $this->baoError('活动时间不能为空！');
        }
        return $data;
    }
    public function audit($huodong_id = 0)
    {
        if (is_numeric($huodong_id) && ($huodong_id = (int) $huodong_id)) {
            $obj = D('Huodong');
            $obj->save(array('huodong_id' => $huodong_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('hdmobile/index'));
        } else {
            $huodong_id = $this->_post('huodong_id', false);
            if (is_array($huodong_id)) {
                $obj = D('Huodong');
                foreach ($huodong_id as $id) {
                    $obj->save(array('huodong_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('hdmobile/index'));
            }
            $this->baoError('请选择要审核的活动');
        }
    }
}
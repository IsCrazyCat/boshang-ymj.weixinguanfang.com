<?php
class LifeAction extends CommonAction
{
    private $create_fields = array('title', 'city_id', 'cate_id', 'area_id', 'business_id', 'user_id', 'is_shop', 'text1', 'text2', 'text3', 'num1', 'num2', 'select1', 'select2', 'select3', 'select4', 'select5', 'urgent_date', 'top_date', 'photo', 'contact', 'mobile', 'qq', 'addr', 'views', 'lng', 'lat');
    private $edit_fields = array('title', 'city_id', 'cate_id', 'area_id', 'business_id', 'user_id', 'is_shop', 'text1', 'text2', 'text3', 'num1', 'num2', 'select1', 'select2', 'select3', 'select4', 'select5', 'urgent_date', 'top_date', 'photo', 'contact', 'mobile', 'qq', 'addr', 'views', 'lng', 'lat');
    public function index()
    {
        $Life = D('Life');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('city_id' => $this->city_id, 'closed' => 0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['qq|mobile|contact|title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $Life->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Life->where($map)->order(array('life_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('cates', D('Lifecate')->fetchAll());
        $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
        $this->display();
        // 输出模板
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Life');
            $details = $this->_post('details', 'SecurityEditorHtml');
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->baoError('商家介绍含有敏感词：' . $words);
            }
            if ($life_id = $obj->add($data)) {
                if ($details) {
                    D('Lifedetails')->updateDetails($life_id, $details);
                }
                $photos = $this->_post('photos', false);
                if (!empty($photos)) {
                    D('Lifephoto')->upload($life_id, $photos);
                }
                $this->baoSuccess('添加成功', U('life/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Lifecate')->fetchAll());
            $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
            $this->assign('areas', D('Area')->fetchAll());
            $this->assign('business', D('Business')->fetchAll());
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->baoError('商圈不能为空');
        }
        $data['user_id'] = htmlspecialchars($data['user_id']);
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['is_shop'] = (int) $data['is_shop'];
        $data['text1'] = htmlspecialchars($data['text1']);
        $data['text2'] = htmlspecialchars($data['text2']);
        $data['text3'] = htmlspecialchars($data['text3']);
        $data['num1'] = (int) $data['num1'];
        $data['num2'] = (int) $data['num2'];
        $data['select1'] = (int) $data['select1'];
        $data['select2'] = (int) $data['select2'];
        $data['select3'] = (int) $data['select3'];
        $data['select4'] = (int) $data['select4'];
        $data['select5'] = (int) $data['select5'];
        $data['urgent_date'] = htmlspecialchars($data['urgent_date']);
        $data['urgent_date'] = $data['urgent_date'] ? $data['urgent_date'] : TODAY;
        if (!empty($data['urgent_date']) && !isDate($data['urgent_date'])) {
            $this->baoError('火急日期格式不正确');
        }
        $data['top_date'] = htmlspecialchars($data['top_date']);
        $data['lng'] = htmlspecialchars(trim($data['lng']));
        $data['lat'] = htmlspecialchars(trim($data['lat']));
        $data['top_date'] = $data['top_date'] ? $data['top_date'] : TODAY;
        if (!empty($data['top_date']) && !isDate($data['top_date'])) {
            $this->baoError('置顶日期格式不正确');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['contact'] = htmlspecialchars($data['contact']);
        if (empty($data['contact'])) {
            $this->baoError('联系人不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('电话不能为空');
        }
        if (!isMobile($data['mobile']) && !isPhone($data['mobile'])) {
            $this->baoError('电话格式不正确');
        }
        $data['qq'] = htmlspecialchars($data['qq']);
        $data['addr'] = htmlspecialchars($data['addr']);
        $data['views'] = (int) $data['views'];
        $data['create_time'] = NOW_TIME;
        $data['last_time'] = NOW_TIME + 86400 * 30;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($life_id = 0)
    {
        if ($life_id = (int) $life_id) {
            //查询上级ID编辑处代码开始始
            $lifes = D('Life')->find($life_id);
            $city_id = $shop_ids['city_id'];
            if ($lifes['city_id'] != $this->city_id) {
                $this->error('非法操作', U('life/index'));
            }
            $obj = D('Life');
            if (!($detail = $obj->find($life_id))) {
                $this->baoError('请选择要编辑的生活信息');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['life_id'] = $life_id;
                $details = $this->_post('details', 'SecurityEditorHtml');
                if ($words = D('Sensitive')->checkWords($details)) {
                    $this->baoError('商家介绍含有敏感词：' . $words);
                }
                if (false !== $obj->save($data)) {
                    if ($details) {
                        D('Lifedetails')->updateDetails($life_id, $details);
                    }
                    $photos = $this->_post('photos', false);
                    if (!empty($photos)) {
                        D('Lifephoto')->upload($life_id, $photos);
                    }
                    $this->baoSuccess('操作成功', U('life/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('cates', D('Lifecate')->fetchAll());
                $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
                $this->assign('cate', D('Lifecate')->find($detail['cate_id']));
                $this->assign('areas', D('Area')->fetchAll());
                $this->assign('business', D('Business')->fetchAll());
                $this->assign('ex', D('Lifedetails')->find($life_id));
                $this->assign('attrs', D('Lifecateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $detail['cate_id']))->select());
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('photos', D('Lifephoto')->getPics($life_id));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的生活信息');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->baoError('商圈不能为空');
        }
        $data['user_id'] = htmlspecialchars($data['user_id']);
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['is_shop'] = (int) $data['is_shop'];
        $data['text1'] = htmlspecialchars($data['text1']);
        $data['text2'] = htmlspecialchars($data['text2']);
        $data['text3'] = htmlspecialchars($data['text3']);
        $data['num1'] = (int) $data['num1'];
        $data['num2'] = (int) $data['num2'];
        $data['select1'] = (int) $data['select1'];
        $data['select2'] = (int) $data['select2'];
        $data['select3'] = (int) $data['select3'];
        $data['select4'] = (int) $data['select4'];
        $data['select5'] = (int) $data['select5'];
        $data['urgent_date'] = htmlspecialchars($data['urgent_date']);
        $data['urgent_date'] = $data['urgent_date'] ? $data['urgent_date'] : TODAY;
        if (!empty($data['urgent_date']) && !isDate($data['urgent_date'])) {
            $this->baoError('火急日期格式不正确');
        }
        $data['top_date'] = htmlspecialchars($data['top_date']);
        $data['lng'] = htmlspecialchars(trim($data['lng']));
        $data['lat'] = htmlspecialchars(trim($data['lat']));
        $data['top_date'] = $data['top_date'] ? $data['top_date'] : TODAY;
        if (!empty($data['top_date']) && !isDate($data['top_date']) && $data['top_date'] != '0000-00-00') {
            $this->baoError('置顶日期格式不正确');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['contact'] = htmlspecialchars($data['contact']);
        if (empty($data['contact'])) {
            $this->baoError('联系人不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('电话不能为空');
        }
        if (!isMobile($data['mobile']) && !isPhone($data['mobile'])) {
            $this->baoError('电话格式不正确');
        }
        $data['qq'] = htmlspecialchars($data['qq']);
        $data['addr'] = htmlspecialchars($data['addr']);
        $data['views'] = (int) $data['views'];
        return $data;
    }
    public function delete($life_id = 0)
    {
        if (is_numeric($life_id) && ($life_id = (int) $life_id)) {
            $lifes = D('Life')->find($life_id);
            $city_id = $shop_ids['city_id'];
            if ($lifes['city_id'] != $this->city_id) {
                $this->baoError('非法操作', U('life/index'));
            }
            $obj = D('Life');
            $obj->save(array('life_id' => $life_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('life/index'));
        } else {
            $life_id = $this->_post('life_id', false);
            if (is_array($life_id)) {
                $obj = D('Life');
                foreach ($life_id as $id) {
                    $obj->save(array('life_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('life/index'));
            }
            $this->baoError('请选择要删除的生活信息');
        }
    }
    public function audit($life_id = 0)
    {
        if (is_numeric($life_id) && ($life_id = (int) $life_id)) {
            $lifes = D('Life')->find($life_id);
            $city_id = $shop_ids['city_id'];
            if ($lifes['city_id'] != $this->city_id) {
                $this->baoError('非法操作', U('life/index'));
            }
            $obj = D('Life');
            $obj->save(array('life_id' => $life_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('life/index'));
        } else {
            $life_id = $this->_post('life_id', false);
            if (is_array($life_id)) {
                $obj = D('Life');
                foreach ($life_id as $id) {
                    $obj->save(array('life_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('life/index'));
            }
            $this->baoError('请选择要审核的生活信息');
        }
    }
}
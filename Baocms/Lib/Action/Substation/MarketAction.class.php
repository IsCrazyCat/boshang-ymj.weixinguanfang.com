<?php
class MarketAction extends CommonAction
{
    private $create_fields = array('city_id', 'area_id', 'business_id', 'market_name', 'logo', 'photo', 'addr', 'summary', 'tel', 'contact', 'type1', 'type2', 'type3', 'type4', 'type5', 'type6', 'tags', 'business_time', 'near', 'orderby', 'lng', 'lat');
    private $edit_fields = array('city_id', 'area_id', 'business_id', 'market_name', 'logo', 'photo', 'addr', 'summary', 'tel', 'contact', 'type1', 'type2', 'type3', 'type4', 'type5', 'type6', 'tags', 'business_time', 'near', 'orderby', 'lng', 'lat');
    public function index()
    {
        $market = D('Market');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['market_name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        $count = $market->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $market->order(array('orderby' => 'asc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
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
            $obj = D('Market');
            $details = $this->_post('details', 'SecurityEditorHtml');
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->baoError('商家介绍含有敏感词：' . $words);
            }
            $ex = array('details' => $details, 'near' => $data['near'], 'business_time' => $data['business_time']);
            unset($data['near'], $data['business_time']);
            if ($market_id = $obj->add($data)) {
                D('Marketdetails')->upDetails($market_id, $ex);
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val)) {
                        $local[] = $val;
                    }
                }
                if (!empty($local)) {
                    D('Marketpic')->upload($market_id, $local);
                }
                $this->baoSuccess('添加成功', U('market/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('areas', D('Area')->fetchAll());
            $this->assign('types', D('Market')->getType());
            $this->assign('business', D('Business')->fetchAll());
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('所在区域不能为空');
        }
        $area = D('area')->find($data['area_id']);
        $data['city_id'] = $area['city_id'];
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->baoError('所在商圈不能为空');
        }
        $data['market_name'] = htmlspecialchars($data['market_name']);
        if (empty($data['market_name'])) {
            $this->baoError('商场名称不能为空');
        }
        $data['logo'] = htmlspecialchars($data['logo']);
        if (empty($data['logo'])) {
            $this->baoError('请上传商场LOGO');
        }
        if (!isImage($data['logo'])) {
            $this->baoError('商场LOGO格式不正确');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传商场缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('商场缩略图格式不正确');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('商场地址不能为空');
        }
        $data['summary'] = htmlspecialchars($data['summary']);
        if (empty($data['summary'])) {
            $this->baoError('小编总结不能为空');
        }
        $data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->baoError('商场电话不能为空');
        }
        if (!isPhone($data['tel']) && !isMobile($data['tel'])) {
            $this->baoError('商场电话格式不正确');
        }
        $data['contact'] = htmlspecialchars($data['contact']);
        $data['orderby'] = (int) $data['orderby'];
        $data['type1'] = (int) $data['type1'];
        $data['type2'] = (int) $data['type2'];
        $data['type3'] = (int) $data['type3'];
        $data['type4'] = (int) $data['type4'];
        $data['type5'] = (int) $data['type5'];
        $data['type6'] = (int) $data['type6'];
        $types = D('Market')->getType();
        $tags = array();
        foreach ($types as $k => $v) {
            if ($data['type' . $k] == 1) {
                $tags[] = $v;
            }
        }
        $data['tags'] = join(',', $tags);
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($market_id = 0)
    {
        if ($market_id = (int) $market_id) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Market')->find($market_id);
            $city_id = $shop_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('market/index'));
            }
            $obj = D('Market');
            if (!($detail = $obj->find($market_id))) {
                $this->baoError('请选择要编辑的商场');
            }
            if ($this->isPost()) {
                $data = $this->editCheck($market_id);
                $data['market_id'] = $market_id;
                $details = $this->_post('details', 'SecurityEditorHtml');
                if ($words = D('Sensitive')->checkWords($details)) {
                    $this->baoError('商场介绍含有敏感词：' . $words);
                }
                $ex = array('details' => $details, 'near' => $data['near'], 'business_time' => $data['business_time']);
                unset($data['near'], $data['business_time']);
                if (false !== $obj->save($data)) {
                    D('Marketdetails')->upDetails($market_id, $ex);
                    $photos = $this->_post('photos', false);
                    $local = array();
                    foreach ($photos as $val) {
                        if (isImage($val)) {
                            $local[] = $val;
                        }
                    }
                    if (!empty($local)) {
                        D('Marketpic')->upload($market_id, $local);
                    }
                    $this->baoSuccess('操作成功', U('market/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('areas', D('Area')->fetchAll());
                $this->assign('business', D('Business')->fetchAll());
                $this->assign('ex', D('Marketdetails')->find($market_id));
                $this->assign('photos', D('Marketpic')->getPics($market_id));
                $this->assign('types', D('Market')->getType());
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商场');
        }
    }
    private function editCheck($shop_id)
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('所在区域不能为空');
        }
        $area = D('area')->find($data['area_id']);
        $data['city_id'] = $area['city_id'];
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->baoError('所在商圈不能为空');
        }
        $data['market_name'] = htmlspecialchars($data['market_name']);
        if (empty($data['market_name'])) {
            $this->baoError('商场名称不能为空');
        }
        $data['logo'] = htmlspecialchars($data['logo']);
        if (empty($data['logo'])) {
            $this->baoError('请上传商场LOGO');
        }
        if (!isImage($data['logo'])) {
            $this->baoError('商场LOGO格式不正确');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传商场缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('商场缩略图格式不正确');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('商场地址不能为空');
        }
        $data['summary'] = htmlspecialchars($data['summary']);
        if (empty($data['summary'])) {
            $this->baoError('小编总结不能为空');
        }
        $data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel'])) {
            $this->baoError('商场电话不能为空');
        }
        if (!isPhone($data['tel']) && !isMobile($data['tel'])) {
            $this->baoError('商场电话格式不正确');
        }
        $data['contact'] = htmlspecialchars($data['contact']);
        $data['orderby'] = (int) $data['orderby'];
        $data['type1'] = (int) $data['type1'];
        $data['type2'] = (int) $data['type2'];
        $data['type3'] = (int) $data['type3'];
        $data['type4'] = (int) $data['type4'];
        $data['type5'] = (int) $data['type5'];
        $data['type6'] = (int) $data['type6'];
        $types = D('Market')->getType();
        $tags = array();
        foreach ($types as $k => $v) {
            if ($data['type' . $k] == 1) {
                $tags[] = $v;
            }
        }
        $data['tags'] = join(',', $tags);
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        return $data;
    }
    public function select()
    {
        $market = D('Market');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Shopcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        $count = $market->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 10);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $market->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function delete($market_id = 0)
    {
        if (is_numeric($market_id) && ($market_id = (int) $market_id)) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Market')->find($market_id);
            $city_id = $shop_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('market/index'));
            }
            $obj = D('Market');
            $obj->save(array('market_id' => $market_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('market/index'));
        } else {
            $market_id = $this->_post('market_id', false);
            if (is_array($market_id)) {
                $obj = D('Market');
                foreach ($market_id as $id) {
                    $obj->save(array('market_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('market/index'));
            }
            $this->baoError('请选择要删除的商场');
        }
    }
    public function floor($market_id)
    {
        $market_id = (int) $market_id;
        //查询上级ID编辑处代码开始
        $shop_ids = D('Market')->find($market_id);
        $city_id = $shop_ids['city_id'];
        if ($citys != $this->city_id) {
            $this->error('非法操作', U('market/index'));
        }
        if (!($detail = D('Market')->find($market_id))) {
            $this->baoError('您选择的商场不存在');
        }
        if ($detail['closed'] == 1) {
            $this->baoError('您选择的商场不存在');
        }
        $floor = D('Marketfloor')->where(array('market_id' => $market_id))->order('orderby asc')->select();
        $this->assign('detail', $detail);
        $this->assign('floor', $floor);
        $this->display();
    }
    public function flooradd($market_id)
    {
        if ($market_id = (int) $market_id) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Market')->find($market_id);
            $city_id = $shop_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('market/index'));
            }
            if (!($detail = D('Market')->find($market_id))) {
                $this->baoError('您选择的商场不存在');
            }
            if ($detail['closed'] == 1) {
                $this->baoError('您选择的商场不存在');
            }
            if ($this->isPost()) {
                $data['floor_name'] = $this->_post('floor_name', 'htmlspecialchars');
                if (empty($data['floor_name'])) {
                    $this->baoError('楼层不能为空');
                }
                if ($res = D('Marketfloor')->where(array('market_id' => $market_id, 'floor_name' => $data['floor_name']))->find()) {
                    $this->baoError('楼层名称已存在');
                }
                $data['market_id'] = $market_id;
                $data['orderby'] = (int) $this->_post('orderby');
                if (D('Marketfloor')->add($data)) {
                    $this->baoSuccess('添加楼层成功！', U('market/floor', array('market_id' => $market_id)));
                }
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要操作的商场');
        }
    }
    public function flooredit($market_id, $floor_id)
    {
        $market_id = (int) $market_id;
        //查询上级ID编辑处代码开始
        $shop_ids = D('Market')->find($market_id);
        $city_id = $shop_ids['city_id'];
        if ($citys != $this->city_id) {
            $this->error('非法操作', U('market/index'));
        }
        $floor_id = (int) $floor_id;
        if (!($detail = D('Market')->find($market_id))) {
            dump(D('Market')->getLastSql());
            $this->baoError('您选择的商场不存在2');
        }
        if ($detail['closed'] == 1) {
            $this->baoError('您选择的商场不存在3');
        }
        $floor = D('Marketfloor')->find($floor_id);
        if ($this->isPost()) {
            $data['floor_name'] = $this->_post('floor_name', 'htmlspecialchars');
            if (empty($data['floor_name'])) {
                $this->baoError('楼层不能为空');
            }
            if ($res = D('Marketfloor')->where(array('market_id' => $market_id, 'floor_name' => $data['floor_name']))->find()) {
                dump($res);
                if ($res['floor_id'] != $floor_id) {
                    $this->baoError('楼层名称已存在');
                }
            }
            $data['market_id'] = $market_id;
            $data['orderby'] = (int) $this->_post('orderby');
            $data['floor_id'] = $floor_id;
            if (false !== D('Marketfloor')->save($data)) {
                $this->baoSuccess('编辑楼层成功！', U('market/floor', array('market_id' => $market_id)));
            }
        } else {
            $this->assign('floor', $floor);
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    public function floordel($floor_id = 0, $market_id)
    {
        if (is_numeric($floor_id) && ($floor_id = (int) $floor_id)) {
            $obj = D('Marketfloor');
            $obj->save(array('floor_id' => $floor_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('market/floor', array('market_id' => $market_id)));
        } else {
            $floor_id = $this->_post('floor_id', false);
            if (is_array($floor_id)) {
                $obj = D('Marketfloor');
                foreach ($floor_id as $id) {
                    $obj->save(array('floor_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('market/floor', array('market_id' => $market_id)));
            }
            $this->baoError('请选择要删除的商家楼层');
        }
    }
    public function recovery($floor_id, $market_id)
    {
        if (is_numeric($floor_id) && ($floor_id = (int) $floor_id)) {
            $obj = D('Marketfloor');
            $obj->save(array('floor_id' => $floor_id, 'closed' => 0));
            $this->baoSuccess('恢复成功！', U('market/floor', array('market_id' => $market_id)));
        }
    }
    public function enter($market_id)
    {
        if ($market_id = (int) $market_id) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Market')->find($market_id);
            $city_id = $shop_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('market/index'));
            }
            if (!($detail = D('Market')->find($market_id))) {
                $this->baoError('您选择的商场不存在');
            }
            if ($detail['closed'] == 1) {
                $this->baoError('您选择的商场不存在');
            }
            $types = D('Market')->getType();
            for ($i = 1; $i <= 6; $i++) {
                if ($detail['type' . $i] == 0) {
                    unset($types[$i]);
                }
            }
            if ($this->isPost()) {
                $data['shop_id'] = (int) $this->_post('shop_id', false);
                $details = D('Marketenter')->where(array('market_id' => $market_id, 'shop_id' => $data['shop_id']))->find();
                if (!empty($details)) {
                    $this->baoError('该商家已经入驻');
                }
                $data['cate_id'] = (int) $this->_post('cate_id');
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                $data['market_id'] = $market_id;
                $data['floor'] = (int) $this->_post('floor');
                if ($enter_id = D('Marketenter')->add($data)) {
                    $this->baoSuccess('入驻成功！', U('market/enterlist', array('market_id' => $market_id)));
                }
            } else {
                $this->assign('floors', D('Marketfloor')->where(array('market_id' => $market_id))->select());
                $this->assign('types', $types);
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要入驻的商场');
        }
    }
    public function enterlist()
    {
        $marketenter = D('Marketenter');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0);
        if ($market_id = (int) $this->_param('market_id', false)) {
            if (!($detail = D('Market')->find($market_id))) {
                $this->baoError('查看的商场不存在');
            }
            $map['market_id'] = $market_id;
        }
        $count = $marketenter->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $marketenter->order(array('enter_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $market_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
            $market_ids[$val['market_id']] = $val['market_id'];
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('markets', D('Market')->itemsByIds($market_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('detail', $detail);
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function cancle($enter_id = 0)
    {
        if (is_numeric($enter_id) && ($enter_id = (int) $enter_id)) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Market')->find($market_id);
            $city_id = $shop_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('market/index'));
            }
            $obj = D('Marketenter');
            $obj->delete($enter_id);
            $this->baoSuccess('撤销成功！', U('market/enterlist'));
        } else {
            $enter_id = $this->_post('enter_id', false);
            if (is_array($enter_id)) {
                $obj = D('Marketenter');
                foreach ($enter_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('撤销成功！', U('market/enterlist'));
            }
            $this->baoError('请选择要撤销的商家入驻');
        }
    }
}
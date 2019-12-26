<?php
class ShopAction extends CommonAction
{
    public function myshop()
    {
        $shop = D('Shop');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('tui_uid' => $this->uid, 'closed' => 0);
        $count = $shop->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $shop->where($map)->order(array('shop_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function tongji()
    {
        $shopIds = D('Shop')->getShopIdsByTuiId($this->uid);
        if (empty($shopIds)) {
            $this->error('您还没有推广的商户', U('members/myshop'));
        }
        $bg_date = $this->_param('bg_date', 'htmlspecialchars');
        $end_date = $this->_param('end_date', 'htmlspecialchars');
        if (empty($bg_date) || empty($end_date)) {
            $bg_date = date('Y-m-d', NOW_TIME - 86400 * 30);
            $end_date = TODAY;
        }
        $this->assign('bg_date', $bg_date);
        $this->assign('end_date', $end_date);
        $this->assign('total', D('Shopmoney')->sumByIds($bg_date, $end_date, $shopIds));
        $shops = D('Shop')->itemsByIds($shopIds);
        $datas = D('Shopmoney')->sumByIdsTop10($bg_date, $end_date, $shopIds);
        $showdatas = array();
        foreach ($datas as $k => $val) {
            if (!empty($val['shop_id'])) {
                $showdatas['shop'][] = '"' . $shops[$val['shop_id']]['shop_name'] . '"';
                $showdatas['money'][] = round($val['money'] / 100, 2);
            }
        }
        $this->assign('shops', join(',', $showdatas['shop']));
        $this->assign('moneys', join(',', $showdatas['money']));
        $this->display();
    }
    public function shoplist()
    {
        $Shop = D('Shop');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Shop->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 10);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Shop->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function favorites()
    {
        $Shopfavorites = D('Shopfavorites');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Shopfavorites->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Shopfavorites->where($map)->order('favorites_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('prices', D('Shopdetails')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function deletefavo($favorites_id)
    {
        $favorites_id = (int) $favorites_id;
        if ($detial = D('Shopfavorites')->find($favorites_id)) {
            if ($detial['user_id'] == $this->uid) {
                D('Shopfavorites')->delete($favorites_id);

                $this->baoSuccess('取消收藏成功!', U('members/favorites'));
            }
        }
        $this->baoError('参数错误');
    }
    public function dianping($shop_id)
    {
        $shop_id = (int) $shop_id;
        if (!($detail = D('Shop')->find($shop_id))) {
            $this->error('该商家不存在');
        }
        if ($res = D('Shopdianping')->where(array('user_id' => $this->uid, 'shop_id' => $shop_id))->find()) {
            $this->error('您已经评价过了');
        }
        $cates = D('Shopcate')->fetchAll();
        $cate = $cates[$detail['cate_id']];
        $this->assign('cate', $cate);
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('score', 'd1', 'd2', 'd3', 'cost', 'contents'));
            $data['user_id'] = $this->uid;
            $data['shop_id'] = $shop_id;
            $data['score'] = (int) $data['score'];
            if ($data['score'] <= 0 || $data['score'] > 5) {
                $this->baoMsg('请选择评分');
            }
            $data['d1'] = (int) $data['d1'];
            if (empty($data['d1'])) {
                $this->baoMsg($cate['d1'] . '评分不能为空');
            }
            if ($data['d1'] > 5 || $data['d1'] < 1) {
                $this->baoMsg($cate['d1'] . '评分不能为空');
            }
            $data['d2'] = (int) $data['d2'];
            if (empty($data['d2'])) {
                $this->baoMsg($cate['d2'] . '评分不能为空');
            }
            if ($data['d2'] > 5 || $data['d2'] < 1) {
                $this->baoMsg($cate['d2'] . '评分不能为空');
            }
            $data['d3'] = (int) $data['d3'];
            if (empty($data['d3'])) {
                $this->baoMsg($cate['d3'] . '评分不能为空');
            }
            if ($data['d3'] > 5 || $data['d3'] < 1) {
                $this->baoMsg($cate['d3'] . '评分不能为空');
            }
            $data['cost'] = (int) $data['cost'];
            $data['contents'] = htmlspecialchars($data['contents']);
            if (empty($data['contents'])) {
                $this->baoMsg('不说点什么么');
            }
            $data['create_time'] = NOW_TIME;
            $data['show_date'] = date('Y-m-d', NOW_TIME);
            //15天后显示 --> 立刻显示
            $data['create_ip'] = get_client_ip();
            $obj = D('Shopdianping');
            if ($dianping_id = $obj->add($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val)) {
                        $local[] = $val;
                    }
                }
                if (!empty($local)) {
                    D('Shopdianpingpics')->upload($dianping_id, $data['shop_id'], $local);
                }
                D('Shop')->updateCount($shop_id, 'score_num');
                D('Users')->updateCount($this->uid, 'ping_num');
                D('Shopdianping')->updateScore($shop_id);
                $this->baoMsg('评价成功', U('shop/detail', array('shop_id' => $shop_id)));
            }
            $this->baoMsg('操作失败！');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
}
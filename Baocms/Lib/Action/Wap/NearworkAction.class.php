<?php
class NearworkAction extends CommonAction
{
    public function index()
    {
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $this->assign('nextpage', LinkTo('nearwork/loaddata', array('t' => NOW_TIME, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
        // 输出模板
    }
    public function loaddata()
    {
        $work = D('Work');
        import('ORG.Util.Page');
        // 导入分页类 
		//$map = array('audit' => 1, 'city_id' => $this->city_id, 'expir_date' => array('EGT', TODAY));
        $map = array('audit' => 1,  'expir_date' => array('EGT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $work->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 10);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
        $list = $work->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $work_id2[$val['shop_id']] = $val['shop_id'];
            }
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
            if (empty($val['money1']) && empty($val['money1'])) {
                $list[$k]['moeny'] = '面议';
            } elseif (empty($val['money1'])) {
                $list[$k]['money'] = $val['money2'] . '元/月';
            } elseif (empty($val['money2'])) {
                $list[$k]['money'] = $val['money1'] . '元/月';
            } else {
                $list[$k]['money'] = $val['money1'] . '-' . $val['money2'] . '元/月';
            }
        }
        if ($work_id2) {
            $this->assign('shops', D('Shop')->itemsByIds($work_id2));
        }
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function detail($work_id)
    {
        if (!($work_id = (int) $work_id)) {
            $this->error('参数错误');
        }
        if (!($detail = D('Work')->find($work_id))) {
            $this->error('参数错误');
        }
        $shop = D('Shop')->find($detail['shop_id']);
        $ex = D('Shopdetails')->find($detail['shop_id']);
        if (empty($detail['money1']) && empty($detail['money1'])) {
            $detail['moeny'] = '面议';
        } elseif (empty($detail['money1'])) {
            $detail['money'] = $detail['money2'] . '元/月';
        } elseif (empty($detail['money2'])) {
            $detail['money'] = $detail['money1'] . '元/月';
        } else {
            $detail['money'] = $detail['money1'] . '-' . $detail['money2'] . '元/月';
        }
        $this->assign('detail', $detail);
        $this->assign('shop', $shop);
        $this->assign('ex', $ex);
        $this->display();
    }
}
<?php
class LogsAction extends CommonAction
{
    public function index()
    {
        $logs = D('Communityorderlogs');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        if ($community_id = (int) $this->_param('community_id')) {
            $map['community_id'] = $community_id;
            $community = D('Community')->find($community_id);
            $this->assign('name', $community['name']);
            $this->assign('community_id', $community_id);
        }
        if ($number = $this->_param('number', 'htmlspecialchars')) {
            if (!empty($number)) {
                $owner = D('Communityowner')->where(array('number' => $number, 'community_id' => $community_id))->find();
                $map['user_id'] = $owner['user_id'];
                $this->assign('number', $number);
            }
        }
        if ($type = (int) $this->_param('type')) {
            if ($type != 999) {
                $map['type'] = $type;
                $this->assign('type', $type);
            } else {
                $this->assign('type', 999);
            }
        }
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        // var_dump($map);die();
        $count = $logs->where($map)->count();
        // 查询满足要求的总记录数
        $sum = $logs->where($map)->sum('money');
        $Page = new Page($count, 10);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $logs->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $community_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $community_ids[$val['community_id']] = $val['community_id'];
        }
        $this->assign('sum', $sum);
        $this->assign('communitys', D('Community')->itemsByIds($community_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('types', D('Communityorder')->getType());
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function select()
    {
        $Community = D('Community');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|addr|property'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        $count = $Community->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 10);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Community->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
}
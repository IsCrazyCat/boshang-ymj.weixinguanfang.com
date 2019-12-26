<?php
class IndexAction extends CommonAction
{
    public function index()
    {
        $menu = D('Menu')->fetchAll();
        if ($this->_admin['role_id'] != 1) {
            if ($this->_admin['menu_list']) {
                foreach ($menu as $k => $val) {
                    if (!empty($val['menu_action']) && !in_array($k, $this->_admin['menu_list'])) {
                        unset($menu[$k]);
                    }
                }
                foreach ($menu as $k1 => $v1) {
                    if ($v1['parent_id'] == 0) {
                        foreach ($menu as $k2 => $v2) {
                            if ($v2['parent_id'] == $v1['menu_id']) {
                                $unset = true;
                                foreach ($menu as $k3 => $v3) {
                                    if ($v3['parent_id'] == $v2['menu_id']) {
                                        $unset = false;
                                    }
                                }
                                if ($unset) {
                                    unset($menu[$k2]);
                                }
                            }
                        }
                    }
                }
                foreach ($menu as $k1 => $v1) {
                    if ($v1['parent_id'] == 0) {
                        $unset = true;
                        foreach ($menu as $k2 => $v2) {
                            if ($v2['parent_id'] == $v1['menu_id']) {
                                $unset = false;
                            }
                        }
                        if ($unset) {
                            unset($menu[$k1]);
                        }
                    }
                }
            } else {
                $menu = array();
            }
        }
        $this->assign('menuList', $menu);
        $this->display();
    }
    public function main(){
        $counts['users'] = (int) D('Users')->where(array('closed' => 0))->count();
        $counts['shops'] = (int) D('Shop')->where(array('closed' => 0, 'city_id' => $this->city_id))->count();
        $counts['article'] = (int) D('Article')->where(array('closed' => 0, 'city_id' => $this->city_id))->count();
        $counts['community'] = (int) D('Community')->where(array('closed' => 0, 'city_id' => $this->city_id))->count();
        $counts['coupon'] = (int) D('Coupon')->where(array('closed' => 0, 'city_id' => $this->city_id))->count();
        $counts['ele'] = (int) D('Ele')->where(array('closed' => 0, 'city_id' => $this->city_id))->count();
        $counts['express'] = (int) D('Express')->where(array('closed' => 0, 'city_id' => $this->city_id))->count();
        $counts['goods'] = (int) D('Goods')->where(array('closed' => 0, 'city_id' => $this->city_id))->count();
        $counts['life'] = (int) D('Life')->where(array('closed' => 0, 'city_id' => $this->city_id))->count();
        $counts['tuan'] = (int) D('Tuan')->where(array('closed' => 0, 'city_id' => $this->city_id))->count();
        $counts['village'] = (int) D('Village')->where(array('closed' => 0, 'city_id' => $this->city_id))->count();
		
		
		$counts['money'] = (int) D('Shopmoney')->where(array('city_id' => $this->city_id))->sum('money');
		$counts['money_goods'] = (int) D('Shopmoney')->where(array('type'=>goods,'city_id' => $this->city_id))->sum('money');
		$counts['money_tuan'] = (int) D('Shopmoney')->where(array('type'=>tuan,'city_id' => $this->city_id))->sum('money');
		$counts['money_ele'] = (int) D('Shopmoney')->where(array('type'=>ele,'city_id' => $this->city_id))->sum('money');
		$counts['money_ding'] = (int) D('Shopmoney')->where(array('type'=>ding,'city_id' => $this->city_id))->sum('money');
		
		$counts['money_day'] = (int) D('Shopmoney')->where(array(
				'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),
				'city_id' => $this->city_id
			))->sum('money');
			
		$counts['money_day_goods'] = (int) D('Shopmoney')->where(array(
				'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),
				'city_id' => $this->city_id,
				'type'=>goods,
			))->sum('money');
			
		$counts['money_day_tuan'] = (int) D('Shopmoney')->where(array(
				'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),
				'city_id' => $this->city_id,
				'type'=>tuan,
			))->sum('money');
		$counts['money_day_ele'] = (int) D('Shopmoney')->where(array(
				'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),
				'city_id' => $this->city_id,
				'type'=>ele,
			))->sum('money');
		$counts['money_day_ding'] = (int) D('Shopmoney')->where(array(
				'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),
				'city_id' => $this->city_id,
				'type'=>ding,
			))->sum('money');
	
		
        $v = (require BASE_PATH . '/version.php');
        $this->assign('v', $v);
        $this->assign('counts', $counts);
        $Msg = D('Msg');
        import('ORG.Util.Page');
		
		$map['cate_id'] = array('eq',3); 
		$map['closed'] = array('eq',0); 
		
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
		$lists = $Msg->where($map)->order(array('create_time' => 'desc'))->select();//时间降序排
        foreach ($lists as $k => $val) {
			 if (!empty($val['city_id'])) {
                $lists[$k]['city_id'] =  $val['city_id'];
                if ($lists[$k]['city_id'] != $this->city_id ) {
                    unset($lists[$k]);
                }
            }

        }
		$count = count($lists);// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);

		
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function check() {
        //后期获得通知使用！
        die('1');
    }
}
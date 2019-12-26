<?php


class ShopdingyuyueAction extends CommonAction {

    private $create_fields = array('user_id', 'shop_id', 'name', 'mobile','yuyue_date','yuyue_time','number', 'create_time', 'create_ip');
    private $edit_fields = array('user_id', 'shop_id', 'name', 'mobile','yuyue_date','yuyue_time','number');

    public function index() {	
	
	    $mapss = array('city_id' => $this->city_id);//查询城市ID为当前登录账户的ID
		$shop_city = D('Shop')->where($mapss)->order(array('shop_id' => 'desc'))->select();//查询所在城市的商家
		foreach ($shop_city as $val) {
			$cityids[$val['shop_id']] = $val['shop_id'];//对比shop_id
		}
		$maps['shop_id']  = array('in',$cityids);//取得当前商家ID，给下面的maps查询
		
			
        $Shopdingyuyue = D('Shopdingyuyue');
        import('ORG.Util.Page'); // 导入分页类 
        if($keyword = $this->_param('keyword','htmlspecialchars')){
            $maps['name|mobile'] = array('LIKE','%'.$keyword.'%');
            $this->assign('keyword',$keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $maps['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $maps['user_id'] = $user_id;
            $user = D('Users')->find($user_id);
            $this->assign('nickname', $user['nickname']);
            $this->assign('user_id', $user_id);
        }
		
		
        $count = $Shopdingyuyue->where($maps)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Shopdingyuyue->where($maps)->order(array('ding_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
        $user_ids = $shop_ids  = $ding_ids = array();		
        foreach($list  as $k=>$val){            
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;          
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$ding_ids[$val['ding_id']] = $val['ding_id'];
        }
				
        if(!empty($user_ids)){
            $this->assign('users',D('Users')->itemsByIds($user_ids));
        }
		
        if(!empty($shop_ids)){
            $this->assign('shops',D('Shop')->itemsByIds($shop_ids));
        }
		
		///查询订单
		if(!empty($ding_ids)){
			
			$where['ding_id'] = array('in',$ding_ids);
			
			 $count1 = $Shopdingyuyue->where($where)->count(); // 查询满足要求的总记录数 
			   $Page1 = new Page($count1, 25); // 实例化分页类 传入总记录数和每页显示的记录数
			   $show1 = $Page1->show(); // 分页显示输出
			
			$dings_info = D('Shopdingorder') -> where($where)->order(array('ding_id' => 'desc'))->limit($Page1->firstRow . ',' . $Page1->listRows)->select();
          
            $this->assign('dings',$dings_info);
        }
      //  p($list);die;
		
		
        $this->assign('list', $list); // 赋值数据集
		
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

   

	

   
    

    public function delete($ding_id = 0) {
        if (is_numeric($ding_id) && ($ding_id = (int) $ding_id)) {
			
			//查询上级ID编辑处代码开始
			$shop_ids = D('Shopdingyuyue')->find($ding_id);
			$shop_id = $shop_ids['shop_id'];
			$city_ids = D('Shop')->find($shop_id);
			$citys = $city_ids['city_id'];
			if ($citys != $this->city_id) {
			   $this->error('非法操作', U('Shopdingyuyue/index'));
			}
			
			
            $obj = D('Shopdingyuyue');
            $obj->delete($ding_id);
            $this->baoSuccess('删除成功！', U('Shopdingyuyue/index'));
        } else {
            $ding_id = $this->_post('ding_id', false);
            if (is_array($ding_id)) {
                $obj = D('Shopdingyuyue');
                foreach ($ding_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('Shopdingyuyue/index'));
            }
            $this->baoError('请选择要删除的商家订座预约2');
        }
    }

}

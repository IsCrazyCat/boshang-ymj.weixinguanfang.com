<?php


class ShopdingyueAction extends CommonAction { 

    private $create_fields = array('shop_id', 'orderby', 'use_integral', 'cate_id', 'intro', 'title', 'photo', 'thumb', 'price', 'tuan_price', 'settlement_price','mobile_fan', 'num', 'sold_num', 'bg_date', 'end_date', 'fail_date', 'is_hot', 'is_new', 'is_chose', 'freebook','xiadan','xiangou', 'activity_id', 'branch_id','profit_enable','profit_rate1','profit_rate2','profit_rate3','profit_rank_id','shangjing','sjusergroup','jiangjingrate','jjrate1','jjrate2','jjrate3');
    private $edit_fields = array('shop_id', 'orderby', 'use_integral', 'cate_id', 'intro', 'title', 'photo', 'thumb', 'price', 'tuan_price', 'settlement_price','mobile_fan', 'num', 'sold_num', 'bg_date', 'end_date', 'fail_date', 'is_hot', 'is_new', 'is_chose', 'freebook','xiadan','xiangou', 'activity_id', 'branch_id','profit_enable','profit_rate1','profit_rate2','profit_rate3','profit_rank_id','shangjing','sjusergroup','jiangjingrate','jjrate1','jjrate2','jjrate3');

    public function _initialize() {
        parent::_initialize();
        $this->Tuancates = D('Tuancate')->fetchAll();
        $this->assign('cates', $this->Tuancates);
        $this->assign('ranks',D('Userrank')->fetchAll());
    }

    public function index() {
         if ($this->isPost()) {  
         $data['city_id'] = $this->_post('city_id', false);         
         $data['area_id'] = $this->_post('area_id', false);
         $data['business_id'] = $this->_post('business_id', false);
         $data['one_cate'] = $this->_post('one_cate', false);
         $data['two_cate'] = $this->_post('two_cate', false);
         $data['count_list'] = $this->_post('count_list', false);
         $data['count_number'] = $this->_post('count_number', false);
         $data['sms_number'] = $this->_post('sms_number', false);
         $data['sms_open'] = $this->_post('sms_open', false);
        
         
        if (empty($data['business_id'])) {
            $data['business_id']='0';
        }
        if (empty($data['area_id'])) {
            $data['business_id']='0';
            $data['area_id']='0';
        }
        if (empty($data['two_cate'])) {
            $data['two_cate']='0';
        }
         $obj = M('shop_dingyue_set');
         $shop_dingyue_setid = $obj->getfield('dingyueset_id'); 
         $map['dingyueset_id']=$shop_dingyue_setid;
         $result=$obj->where($map)->save($data);
         if ($result) {
             $this->baoSuccess('设置成功！', U('Shopdingyue/index'));
         }else{
             $this->baoError('设置失败');
         }
         
        } else {
            $shop_dingyue_set = D('shop_dingyue_set')->find(1);            
            $this->assign('Shopdingyueset', $shop_dingyue_set); // 赋值数据集
            $this->display();
        }
    }
    public  function  shoplist(){
    	$shopdingyue=M('shop_dingyue');
    	import('ORG.Util.Page'); // 导入分页类 
    	$map = array();	
    	if($audit = $this->_param('audit')){
    		$map['audit'] = $audit;
    		$this->assign('audit_status',$audit);
    	}
    	
    	if ($user_id = (int) $this->_param('user_id')) {
    		$map['uid'] = $user_id;
    		$user = D('Users')->find($user_id);
    		$this->assign('nickname', $user['nickname']);
    		$this->assign('user_id', $user_id);
    	}
    	if ($shop_id = (int) $this->_param('shop_id')) {
    		$map['shop_id'] = $shop_id;
    		$shop = D('Shop')->find($shop_id);
    		$this->assign('shop_name', $shop['shop_name']);
    		$this->assign('shop_id', $shop_id);
    	}
    	$count = $shopdingyue->where($map)->count(); // 查询满足要求的总记录数
    	$Page = new Page($count, 20); // 实例化分页类 传入总记录数和每页显示的记录数
    	$show = $Page->show(); // 分页显示输出
    	$list = $shopdingyue->where($map)->order(array('dingyue_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
    	
    	
    	foreach($list  as &$val){
    		$mapu['user_id']=$val['uid'];
    		$val['uid'] = D('Users')->where($mapu)->getfield('nickname');
    		$maps['shop_id']=$val['shop_id'];
    		$val['shop_id'] = D('Shop')->where($maps)->getfield('shop_name');
    		$val['create_time']=date('Y-m-d',$val['create_time']);
    		
    		//地区
    		$sitelist=explode(",",$val['sitelist']);
    		$slist=array();
    		$clist=array();
    		foreach ($sitelist as $k => $v) {
    			switch ($k) {
    				case 0:
    					$model = M('city');
    					$map['city_id']=$v;
    					//得到答案的id
    					$slist[$k] = $model->where($map)->getField('name');
    					break;
    				case 1:
    					$model = M('area');
    					$map['area_id']=$v;
    					//得到答案的id
    					$slist[$k] = $model->where($map)->getField('area_name');
    					break;
    				case 2:
    					$model = M('business');
    					$map['business_id']=$v;
    					//得到答案的id
    					$slist[$k] = $model->where($map)->getField('business_name');
    					break;
    				default:
    					# code...
    					break;
    			}
    		}
    		
    		//分类
    		$catlist=explode(",",$val['catlist']);
    		$clist=array();
    		$chl = D('Lifecate')->getChannelMeans();
    		// var_dump($catlist);exit;
    		foreach ($catlist as $k1 => $v1) {
    			switch ($k1) {
    				case 0:
    					$clist[$k1] = $chl[$v1];
    					break;
    				case 1:
    					$model = M('life_cate');
    					$map['cate_id']=$v1;
    					//得到答案的id
    					$clist[$k1]=$model->where($map)->getField('cate_name');
    					break;
                    case 2:
                        $model = M('life_cate_attr');
                        $map['attr_id']=$v1;                                      
                        //得到答案的id
                        $clist[$k1]=$model->where($map)->getField('attr_name');
                        break;
    			}
    			 
    		}
    		if ($val['status']==1) {
    			$val['status']="";
    		}elseif($val['status']==-1){
    			$val['status']="已删除";
    		}else{
    			$val['status']=='无知';
    		}
    		
    		//拆分
    		$slist=implode(",",$slist);
    		$clist=implode(",",$clist);
    		$val['sitelist']=$slist;
    		$val['catlist']=$clist;    	
    	}
    	
    	$this->assign('list', $list); // 赋值数据集
    	$this->assign('page', $show); // 赋值分页输出
    	$this->display(); // 输出模板
    }
    public function  auditok($dingyue_id=0){
    	$dingyue_id = (int) $dingyue_id;
    	$obj = D('shop_dingyue');
    	$map['dingyue_id']=$dingyue_id;
    	$data['audit']=1;
    	$result=$obj->where($map)->save($data);
    	if ($result) {
    		echo"<script>alert('操作成功！');location.href=document.referrer;</script>";
    		 
    	}else{
    		echo"<script>alert('操作失败！');location.href=document.referrer;</script>";
    	}
    	
    }
    public function  auditno($dingyue_id=0){
    	$dingyue_id = (int) $dingyue_id;
    	$obj = D('shop_dingyue');
    	$map['dingyue_id']=$dingyue_id;
    	$data['audit']=-1;
    	$result=$obj->where($map)->save($data);
    	if ($result) {
    		echo"<script>alert('操作成功');location.href=document.referrer;</script>";
    		 
    	}else{
    		echo"<script>alert('操作失败！');location.href=document.referrer;</script>";
    	}
    }
    
}

<?php

class GoodsAction extends CommonAction {

    private $create_fields = array('discount','backcount','backinteval','title','intro','shoplx','guige', 'num','is_reight','weight','kuaidi_id','shop_id', 'photo', 'cate_id', 'price', 'mall_price','use_integral','mobile_fan', 'sold_num', 'orderby', 'views', 'instructions', 'details', 'end_date', 'orderby','is_vs1','is_vs2','is_vs3','is_vs4','is_vs5','is_vs6','profit_enable','profit_rate1','profit_rate2','profit_rate3','profit_rank_id','jiesuanfeilv','car_ids');
    private $edit_fields = array('discount','backcount','backinteval','title','intro','shoplx','guige','num', 'is_reight','weight','kuaidi_id','shop_id', 'photo', 'cate_id', 'price', 'mall_price','use_integral','mobile_fan', 'sold_num', 'orderby', 'views', 'instructions', 'details', 'end_date', 'orderby','is_vs1','is_vs2','is_vs3','is_vs4','is_vs5','is_vs6','profit_enable','profit_rate1','profit_rate2','profit_rate3','profit_rank_id','jiesuanfeilv','car_ids');


    public function _initialize() {
        parent::_initialize();
        $this->assign('ranks',D('Userrank')->fetchAll());
    }
    public function index() {
        $Goods = D('Goods');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'is_mall' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($parent_id = (int) $this->_param('parent_id')) {
            $this->assign('parent_id', $parent_id);
        }

        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = ($audit === 1 ? 1 : 0);
            $this->assign('audit', $audit);
        }
        $count = $Goods->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Goods->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val = $Goods->_format($val);
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('cates', D('Goodscate')->fetchAll());

        $this->assign('list', $list); 
        $this->assign('page', $show);
        $this->display(); 
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Goods');
            if ($goods_id = $obj->add($data)) {
                if(!empty($data['car_ids'])){
                    //关联车辆ID
                    $this->addCarGood($goods_id,$data['car_ids']);
                }
                $wei_pic = D('Weixin')->getCode($goods_id, 3); //购物类型是3
                $obj->save(array('goods_id'=>$goods_id,'wei_pic'=>$wei_pic));
                $photos = $this->_post('photos', false);
                if (!empty($photos)) {
                    D('Goodsphoto')->upload($goods_id, $photos);
                }
                //修改库存的东西
   			    $this->shuxin($goods_id);
                $this->baoSuccess('添加成功', U('goods/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Goodscate')->fetchAll());
			//多属性二开开始
			$this->assign('goodsInfo',D('Goods')->where('goods_id='.I('GET.id',0))->find());  // 商品详情   
			$this->assign('goodsType',M("TpGoodsType")->select());
			$this->assign('goodscategory', D('TpGoodsCategory')->select());
			//多属性二开结束
            $this->display();
        }
    }

/**
*添加和修改商品是刷新规格 private只能类内部访问，前台不能访问到
*/
     private function shuxin($goods_id){
                 // 商品规格价钱处理
         if($_POST['item'])
         {
             $spec = M('TpSpec')->getField('id,name'); // 规格表
             $specItem = M('TpSpecItem')->getField('id,item');//规格项
                          
             $specGoodsPrice = M("TpSpecGoodsPrice"); // 实例化 商品规格 价格对象
             $specGoodsPrice->where('goods_id = '.$goods_id)->delete(); // 删除原有的价格规格对象
             foreach($_POST['item'] as $k => $v)
             {
                   // 批量添加数据
                   $v['price'] = trim($v['price']);
                   $store_count = $v['store_count'] = trim($v['store_count']); // 记录商品总库存
                   $v['bar_code'] = trim($v['bar_code']);
                   $dataList[] = array('goods_id'=>$goods_id,'key'=>$k,'key_name'=>$v['key_name'],'price'=>$v['price'],'store_count'=>$v['store_count'],'bar_code'=>$v['bar_code']);                                      
             }             
             $specGoodsPrice->addAll($dataList);             
             //M('Goods')->where("goods_id = 1")->save(array('store_count'=>10)); // 修改总库存为各种规格的库存相加           
         }   
         

         refresh_stock($goods_id); // 刷新商品库存

    }


    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('产品名称不能为空');
        }
	
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('副标题不能为空');
        }
	
		$data['guige'] = htmlspecialchars($data['guige']);
        if (empty($data['guige'])) {
            $this->baoError('副标题不能为空');
        }

		$data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->baoError('库存不能为空');
        } 
		$data['is_reight'] = (int) $data['is_reight'];
		$data['weight'] = (int) $data['weight'];
		if ($data['is_reight'] == 1) {
//			if (empty($data['weight'])) {
//             	$this->baoError('重量不能为空');
//			}
//			if ($data['weight'] % 1 != 0) {
//				$this->baoError('重量必须为1的倍数');
//			}
        }
		$data['kuaidi_id'] = (int) $data['kuaidi_id'];
		if ($data['is_reight'] == 1) {
//			if (empty($data['kuaidi_id'])) {
//				$this->baoError('运费模板不能为空');
//			}
		}	
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $shop = D('Shop')->find($data['shop_id']);
        if (empty($shop)) {
            $this->baoError('请选择正确的商家');
        }
   
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('请选择分类');
        }
		 $Goodscate = D('Goodscate')->where(array('cate_id' => $data['cate_id']))->find();
		 $parent_id = $Goodscate['parent_id'];
		 if ($parent_id == 0) {
			$this->baoError('请选择二级分类');
		 }
		 
		
		$data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->baoError('市场价格不能为空');
        }
        $data['discount'] = htmlspecialchars($data['discount']);
        $data['backcount']= htmlspecialchars($data['backcount']);
        $data['backinteval']= htmlspecialchars($data['backinteval']);
        $data['mall_price'] = (int) ($data['mall_price'] * 100);
        if (empty($data['mall_price'])) {
            $this->baoError('商城价格不能为空');
        } 
        $data['mobile_fan'] = (int) ($data['mobile_fan'] * 100);
		
		$data['use_integral'] = (int) $data['use_integral'];
		//商城检测积分合法性开始
		if (!D('Goods')->check_add_use_integral($data['use_integral'],$data['mall_price'])) {//传2参数
            $this->baoError(D('Goods')->getError(), 3000, true);
        }
		//商城检测积分合法性结束
		$data['views'] = (int) $data['views'];
      	$data['instructions'] = SecurityEditorHtml($data['instructions']);
        if ($words = D('Sensitive')->checkWords($data['instructions'])) {
            $this->baoError('购买须知含有敏感词：' . $words);
        } $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('商品详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('商品详情含有敏感词：' . $words);
        } $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->baoError('过期时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->baoError('过期时间格式不正确');
        }
        $data['car_ids'] = htmlspecialchars($data['car_ids']);
		$data['is_vs1'] = (int) $data['is_vs1'];
		$data['is_vs2'] = (int) $data['is_vs2'];
		$data['is_vs3'] = (int) $data['is_vs3'];
		$data['is_vs4'] = (int) $data['is_vs4'];
		$data['is_vs5'] = (int) $data['is_vs5'];
		$data['is_vs6'] = (int) $data['is_vs6'];
        
        $data['sold_num'] = (int) $data['sold_num'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['orderby'] = (int) $data['orderby'];
        $data['is_mall'] = 1;
        $data['profit_enable'] = (int) $data['profit_enable'];
        $data['profit_rate1'] = (int) $data['profit_rate1'];
        $data['profit_rate2'] = (int) $data['profit_rate2'];
        $data['profit_rate3'] = (int) $data['profit_rate3'];
        $data['profit_prestige'] = (int) $data['profit_prestige'];
        return $data;
    }

    public function edit($goods_id = 0) {
        if ($goods_id = (int) $goods_id) {
            $obj = D('Goods');
            if (!$detail = $obj->find($goods_id)) {
                $this->baoError('请选择要编辑的商品');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['goods_id'] = $goods_id;
                if (!empty($detail['wei_pic'])) {
                    if (true !== strpos($detail['wei_pic'], "https://mp.weixin.qq.com/")) {
                        $wei_pic = D('Weixin')->getCode($goods_id, 3);
                        $data['wei_pic'] = $wei_pic;
                    }
                } else {
                    $wei_pic = D('Weixin')->getCode($goods_id, 3);
                    $data['wei_pic'] = $wei_pic;
                }
                if (false !== $obj->save($data)) {
                    if(!empty($data['car_ids'])){
                        //关联车辆ID
                        $this->addCarGood($goods_id,$data['car_ids']);
                    }
                    $photos = $this->_post('photos', false);
                    if (!empty($photos)) {
                        D('Goodsphoto')->upload($goods_id, $photos);
                    }
                    $this->shuxin($goods_id);
                    $this->baoSuccess('操作成功', U('goods/index'));
                }
                $this->baoError('操作失败');
            } else {
                $cars = D('CarGoods')->where(array('good_id'=>$goods_id))->select();
                foreach ($cars as $key=>$val){
                    $car = D('Car')->find($val['car_id']);
                    $cars[$key]['car'] = $car;
                }
                $this->assign('cars',$cars);
                $this->assign('detail', $obj->_format($detail));
				$this->assign('parent_id',D('Goodscate')->getParentsId($detail['cate_id']));
				$this->assign('attrs', D('Goodscateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $detail['cate_id']))->select());
                $this->assign('cates', D('Goodscate')->fetchAll());
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
				$this->assign('photos', D('Goodsphoto')->getPics($goods_id));
				$this->assign('kuaidi', D('Pkuaidi')->where(array('shop_id'=>$detail['shop_id'],'type'=>goods))->select());
				//二开开始
				$goodsInfo=D('Goods')->where('goods_id='.I('GET.goods_id',0))->find();
                $this->assign('goodsInfo',$goodsInfo);  // 商品详情   
        	 	$this->assign('goodsType',M("TpGoodsType")->select());
            	$this->assign('goodscategory', D('TpGoodsCategory')->select());

				//二开结束
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商品');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('产品名称不能为空');
		}
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('副标题不能为空');
        }
		$data['guige'] = htmlspecialchars($data['guige']);
        if (empty($data['guige'])) {
//            $this->baoError('规格不能为空');
       	}
		$data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->baoError('库存不能为空');
        } 
		$data['is_reight'] = (int) $data['is_reight'];
		$data['weight'] = (int) $data['weight'];
		if ($data['is_reight'] == 1) {
//			if (empty($data['weight'])) {
//             	$this->baoError('重量不能为空');
//			}
//			if ($data['weight'] % 1 != 0) {
//				$this->baoError('重量必须为1的倍数');
//			}
        }
		$data['kuaidi_id'] = (int) $data['kuaidi_id'];
		if ($data['is_reight'] == 1) {
//			if (empty($data['kuaidi_id'])) {
//				$this->baoError('运费模板不能为空');
//			}
		}
			
		$data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $shop = D('Shop')->find($data['shop_id']);
        if (empty($shop)) {
            $this->baoError('请选择正确的商家');
        }
    
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('请选择分类');
        }
		
		 $Goodscate = D('Goodscate')->where(array('cate_id' => $data['cate_id']))->find();
		 $parent_id = $Goodscate['parent_id'];
		 if ($parent_id == 0) {
			$this->baoError('请选择二级分类');
		 }

		$data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        } 
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->baoError('市场价格不能为空');
        }
        $data['discount'] = htmlspecialchars($data['discount']);
        $data['backcount']= htmlspecialchars($data['backcount']);
        $data['mall_price'] = (int) ($data['mall_price'] * 100);
        if (empty($data['mall_price'])) {
            $this->baoError('商城价格不能为空');
        }
        $data['mobile_fan'] = (int) ($data['mobile_fan'] * 100);
		$data['use_integral'] = (int) $data['use_integral'];
		//商城检测积分合法性开始
		if (!D('Goods')->check_add_use_integral($data['use_integral'],$data['mall_price'])) {//传2参数
            $this->baoError(D('Goods')->getError(), 3000, true);
        }
		//商城检测积分合法性结束
        $data['views'] = (int) $data['views'];
		$data['instructions'] = SecurityEditorHtml($data['instructions']);
      
        if ($words = D('Sensitive')->checkWords($data['instructions'])) {
            $this->baoError('购买须知含有敏感词：' . $words);
        } $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('商品详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('商品详情含有敏感词：' . $words);
        } $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->baoError('过期时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->baoError('过期时间格式不正确');
        }
        $data['car_ids'] = htmlspecialchars($data['car_ids']);
		$data['is_vs1'] = (int) $data['is_vs1'];
		$data['is_vs2'] = (int) $data['is_vs2'];
		$data['is_vs3'] = (int) $data['is_vs3'];
		$data['is_vs4'] = (int) $data['is_vs4'];
		$data['is_vs5'] = (int) $data['is_vs5'];
		$data['is_vs6'] = (int) $data['is_vs6'];
        
        $data['sold_num'] = (int) $data['sold_num'];
        $data['orderby'] = (int) $data['orderby'];
        $data['profit_enable'] = (int) $data['profit_enable'];
        $data['profit_rate1'] = (int) $data['profit_rate1'];
        $data['profit_rate2'] = (int) $data['profit_rate2'];
        $data['profit_rate3'] = (int) $data['profit_rate3'];
        $data['profit_prestige'] = (int) $data['profit_prestige'];
        return $data;
    }

    public function delete($goods_id = 0) {
        if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)) {
            $obj = D('Goods');
            $obj->save(array('goods_id' => $goods_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('goods/index'));
        } else {
            $goods_id = $this->_post('goods_id', false);
            if (is_array($goods_id)) {
                $obj = D('Goods');
                foreach ($goods_id as $id) {
                    $obj->save(array('goods_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('goods/index'));
            }
            $this->baoError('请选择要删除的商家');
        }
    }

    public function audit($goods_id = 0) {
        if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)) {
            $obj = D('Goods');
            $obj->save(array('goods_id' => $goods_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('goods/index'));
        } else {
            $goods_id = $this->_post('goods_id', false);
            if (is_array($goods_id)) {
                $obj = D('Goods');
                foreach ($goods_id as $id) {
                    $obj->save(array('goods_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！'.$error.'条失败', U('goods/index'));
            }
            $this->baoError('请选择要审核的商品');
        }
    }
	
	 /**
     * 动态获取商品规格选择框 根据不同的数据返回不同的选择框
     */
    public function ajaxGetSpecSelect(){
        $goods_id = $_GET['goods_id'] ? $_GET['goods_id'] : 0;
        $specList = D('TpSpec')->where("type_id = ".$_GET['spec_type'])->order('`order` desc')->select();
        foreach($specList as $k => $v)        
            $specList[$k]['spec_item'] = D('TpSpecItem')->where("spec_id = ".$v['id'])->getField('id,item'); // 获取规格项                
        $items_id = M('TpSpecGoodsPrice')->where('goods_id = '.$goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
        $items_ids = explode('_', $items_id);       
        // 获取商品规格图片                
        if($goods_id){
           $specImageList = M('TpSpecImage')->where("goods_id = $goods_id")->getField('spec_image_id,src');                 
        }        
        $this->assign('specImageList',$specImageList);
        
        $this->assign('items_ids',$items_ids);
        $this->assign('specList',$specList);
        $this->display('ajax_spec_select');        
    }    

     /**
     * 动态获取商品规格输入框 根据不同的数据返回不同的输入框
     */    
    public function ajaxGetSpecInput(){     
         $goods_id = $_REQUEST['goods_id'] ? $_REQUEST['goods_id'] : 0;
         $str = $this->getSpecInput($goods_id ,$_POST['spec_arr']);
         exit($str);   
    }

     /**
     * 获取 规格的 笛卡尔积
     * @param $goods_id 商品 id     
     * @param $spec_arr 笛卡尔积
     * @return string 返回表格字符串
     */
    public function getSpecInput($goods_id, $spec_arr){
        // 排序
        foreach ($spec_arr as $k => $v)
        {
            $spec_arr_sort[$k] = count($v);
        }
        asort($spec_arr_sort);        
        foreach ($spec_arr_sort as $key =>$val){
            $spec_arr2[$key] = $spec_arr[$key];
        }
     
         $clo_name = array_keys($spec_arr2);         
         $spec_arr2 = combineDika($spec_arr2); //  获取 规格的 笛卡尔积                 
                       
         $spec = M('TpSpec')->getField('id,name'); // 规格表
         $specItem = M('TpSpecItem')->getField('id,item,spec_id');//规格项
         $keySpecGoodsPrice = M('TpSpecGoodsPrice')->where('goods_id = '.$goods_id)->getField('key,key_name,price,store_count,bar_code');//规格项
                          
       $str = "<table class='table table-bordered' id='spec_input_tab'>";
       $str .="<tr>";       
       // 显示第一行的数据
       foreach ($clo_name as $k => $v) {
           $str .=" <td><b>{$spec[$v]}</b></td>";
       }    
        $str .="<td><b>价格</b></td>
               <td><b>库存</b></td>
               <td><b>条码</b></td>
             </tr>";
       // 显示第二行开始 
       foreach ($spec_arr2 as $k => $v) {
            $str .="<tr>";
            $item_key_name = array();
            foreach($v as $k2 => $v2)
            {
                $str .="<td>{$specItem[$v2][item]}</td>";
                $item_key_name[$v2] = $spec[$specItem[$v2]['spec_id']].':'.$specItem[$v2]['item'];
            }   
            ksort($item_key_name);            
            $item_key = implode('_', array_keys($item_key_name));
            $item_name = implode(' ', $item_key_name);
            
            $keySpecGoodsPrice[$item_key][price] ? false : $keySpecGoodsPrice[$item_key][price] = 0; // 价格默认为0
            $keySpecGoodsPrice[$item_key][store_count] ? false : $keySpecGoodsPrice[$item_key][store_count] = 0; //库存默认为0
            $str .="<td><input name='item[$item_key][price]' value='{$keySpecGoodsPrice[$item_key][price]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' /></td>";
            $str .="<td><input name='item[$item_key][store_count]' value='{$keySpecGoodsPrice[$item_key][store_count]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";            
            $str .="<td><input name='item[$item_key][bar_code]' value='{$keySpecGoodsPrice[$item_key][bar_code]}' />
                <input type='hidden' name='item[$item_key][key_name]' value='$item_name' /></td>";
            $str .="</tr>";           
       }
        $str .= "</table>";
       return $str;   
    }
    public function addCarGood($good_id,$car_ids){
        $car_ids = explode(",",$car_ids);
        $data['good_id'] = $good_id;
        $data['create_time']=time();
        D('Cargoods')->where(array('good_id'=>$good_id))->delete();
        foreach ($car_ids as $key=>$id){
            $data['car_id'] = $id;
            D('Cargoods')->add($data);
        }
    }
}

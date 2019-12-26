<?php
class GoodsAction extends CommonAction {

    private $create_fields = array('title', 'photo', 'shoplx','cate_id', 'intro','guige', 'num','is_reight','weight','kuaidi_id','select1', 'select2', 'select3', 'select4', 'select5','price', 'shopcate_id', 'mall_price','use_integral','instructions', 'details', 'end_date','is_vs1','is_vs2','is_vs3','is_vs4','is_vs5','is_vs6','profit_enable','profit_rate1','profit_rate2','profit_rate3','profit_rank_id');
    private $edit_fields = array('title', 'photo','shoplx', 'cate_id', 'intro','guige', 'num','is_reight','weight','kuaidi_id','select1', 'select2', 'select3', 'select4', 'select5','price', 'shopcate_id', 'mall_price','use_integral', 'instructions', 'details', 'end_date','is_vs1','is_vs2','is_vs3','is_vs4','is_vs5','is_vs6','profit_enable','profit_rate1','profit_rate2','profit_rate3','profit_rank_id','audit');

    public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['mall'] == 0) {
				$this->error('此功能已关闭');die;
		}
        $this->autocates = D('Goodsshopcate')->where(array('shop_id' => $this->shop_id))->select();
        $this->assign('autocates', $this->autocates);
		$this->GoodsCates = D('Goodscate')->fetchAll();
        $this->assign('GoodsCates', $this->GoodsCates);
    }

    private function check_weidian() {
        $wd = D('WeidianDetails');
        $wd_res = $wd->where('shop_id =' . ($this->shop_id))->find();
        if (!$wd_res) {
            $this->error('请先完善微店资料！', U('goods/weidian'));
        } elseif ($wd_res['audit'] == 0) {
            $this->error('您的微店正在审核中，请耐心等待！', U('index/index'));
        } elseif ($wd_res['audit'] == 2) {
            $this->error('您的微店未通过审核！', U('index/index'));
        }
    }

    public function index() {
//        $this->check_weidian();
        $Goods = D('Goods');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'shop_id' => $this->shop_id, 'is_mall' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Goodscate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
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

    public function get_select() {

        if (IS_AJAX) {

            $pid = I('pid', 0, 'intval,trim');
            $gc = D('GoodsCate');
            $list = $gc->where('parent_id =' . $pid)->select();

            if ($pid == 0) {
                $this->ajaxReturn(array('status' => 'success', 'list' => ''));
            }

            if ($list) {
                $l = '';
                foreach ($list as $k => $v) {
                    $l = $l . '<option value=' . $v['cate_id'] . ' style="color:#333333;">' . $v['cate_name'] . '</option>';
                }

                $this->ajaxReturn(array('status' => 'success', 'list' => $l));
            }
        }
    }

    public function weidian() {
       
        $gc = D('GoodsCate');
        $select = $gc->where('parent_id =0')->select();
        $this->assign('select', $select);

        $wd = D('WeidianDetails');
        $weidian = $wd->where('shop_id =' . ($this->shop_id))->find();
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('weidian_name', 'addr', 'city_id', 'area_id', 'cate_id', 'business_time', 'details', 'pic', 'logo', 'lng', 'lat', 'reg_time'));
            if (empty($weidian)) {
                $data['weidian_name'] = htmlspecialchars($data['weidian_name']);
                if (empty($data['weidian_name'])) {
                    $this->baoError('店铺名称不能为空');
                }
                $data['addr'] = htmlspecialchars($data['addr']);
                if (empty($data['addr'])) {
                    $this->baoError('店铺地址不能为空');
                }
                $data['cate_id'] = (int)$data['cate_id'];
                if (empty($data['cate_id'])) {
                    $this->baoError('店铺分类没有选择');
                }
                $data['city_id'] = intval($data['city_id']);
                $data['area_id'] = intval($data['area_id']);
                if (empty($data['city_id']) || empty($data['area_id'])) {
                    $this->baoError('城市或地区没有选择');
                }
                $data['reg_time'] = NOW_TIME;
            }else{
                $data['update_time'] = NOW_TIME;
            }
            $data['business_time'] = htmlspecialchars($data['business_time']);

            $data['shop_id'] = $this->shop_id;

            if (empty($data['pic'])) {
                $this->baoError('店铺图标没有上传');
            }
            if (empty($data['logo'])) {
                $this->baoError('店铺logo没有上传');
            }
            if (empty($data['lng']) || empty($data['lat'])) {
                $this->baoError('店铺坐标没有选择');
            }
            $data['details'] = $this->_post('details', 'SecurityEditorHtml');
            if (empty($data['details']) || $data['details'] == null) {
                $this->baoError('详情没有填写');
            }
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->baoError('商家介绍含有敏感词：' . $words);
            }
            if (!$weidian) { 
                $add = $wd->add($data);
                if (!$add) {
                    $this->baoError('设置失败');
                } else {
                    $this->baoSuccess('设置成功', U('goods/weidian'));
                }
            } else {  
                $up = $wd->where('shop_id =' . ($this->shop_id))->save($data);
                if (!$up) {
                    $this->baoError('修改失败');
                } else {
                    $this->baoSuccess('修改成功', U('goods/weidian'));
                }
            }
        } else {
            $this->assign('the_shop', D('Shop')->where('shop_id =' . ($this->shop_id))->find());
            $cates = D('Weidiancate')->fetchAll();
			$this->assign('cates', $cates); 
		
            $this->assign('weidian', $weidian);

            $this->display();
        }
    }

    public function create() {
        $this->check_weidian();
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Goods');
            if ($goods_id = $obj->add($data)) {
                $wei_pic = D('Weixin')->getCode($goods_id, 3); 
                $obj->save(array('goods_id' => $goods_id, 'wei_pic' => $wei_pic));
				$photos = $this->_post('photos', false);
                if (!empty($photos)) {
                    D('Goodsphoto')->upload($goods_id, $photos);
                }
                //添加商品
  		     $this->shuxin($goods_id);
                $this->baoSuccess('添加成功', U('goods/index'));
            }
            $this->baoError('操作失败！');
        } else {
        	  $this->assign('goodsInfo',D('Goods')->where('goods_id='.I('GET.id',0))->find());  // 商品详情   
             $this->assign('goodsType',M("TpGoodsType")->select());
		  $this->assign('kuaidi', D('Pkuaidi')->where(array('shop_id'=>$this->shop_id,'type'=>goods))->select());
            $this->assign('cates', D('Goodscate')->fetchAll());
            $this->display();
        }
    }
	public function child($parent_id=0){
        $datas = D('Goodscate')->fetchAll();
        $str = '';

        foreach($datas as $var){
            if($var['parent_id'] == 0 && $var['cate_id'] == $parent_id){
         
                foreach($datas as $var2){

                    if($var2['parent_id'] == $var['cate_id']){
                        $str.='<option value="'.$var2['cate_id'].'">'.$var2['cate_name'].'</option>'."\n\r";
           
                        foreach($datas as $var3){
                            if($var3['parent_id'] == $var2['cate_id']){
                                
                               $str.='<option value="'.$var3['cate_id'].'">&nbsp;&nbsp;--'.$var3['cate_name'].'</option>'."\n\r"; 
                                
                            }
                            
                        }
                    }  
                }
                             
              
            }           
        }
        echo $str;die;
    }
	


/*********商城多属性商城规格********/


 public function shuxin($goods_id){
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
     /**
     * 动态获取商品规格选择框 根据不同的数据返回不同的选择框
     */
    public function ajaxGetSpecSelect(){
        $goods_id = $_GET['goods_id'] ? $_GET['goods_id'] : 0;
      
       
        $specList = D('TpSpec')->where("type_id = ".$_GET['spec_type'])->order('`order` desc')->select();


        foreach($specList as $k => $v)        
            $specList[$k]['spec_item'] = D('TpSpecItem')->where("spec_id = ".$v['id'])->getField('id,item'); // 获取规格项                
        

         //dump($specList);;die;
        $items_id = M('TpSpecGoodsPrice')->where('goods_id = '.$goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
        $items_ids = explode('_', $items_id);       
        
        // 获取商品规格图片                
        if($goods_id)
        {
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
    public function getSpecInput($goods_id, $spec_arr)
    {
        
        // 排序
        foreach ($spec_arr as $k => $v)
        {
            $spec_arr_sort[$k] = count($v);
        }
        asort($spec_arr_sort);        
        foreach ($spec_arr_sort as $key =>$val)
        {
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
       foreach ($clo_name as $k => $v) 
       {
           $str .=" <td><b>{$spec[$v]}</b></td>";
       }    
        $str .="<td><b>价格</b></td>
               <td><b>库存</b></td>
               <td><b>条码</b></td>
             </tr>";
       // 显示第二行开始 
       foreach ($spec_arr2 as $k => $v) 
       {
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
/********规格多属性 end*********/

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
			if (empty($data['weight'])) {
             	$this->baoError('重量不能为空');
			}
			if ($data['weight'] % 1 != 0) {
				$this->baoError('重量必须为1的倍数');
			}
        }
		$data['kuaidi_id'] = (int) $data['kuaidi_id'];
		if ($data['is_reight'] == 1) {
			if (empty($data['kuaidi_id'])) {
				$this->baoError('运费模板不能为空');
			}
		}	
        $data['shop_id'] = $this->shop_id;
        $shopdetail = D('Shop')->find($this->shop_id);

        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('请选择分类');
        }
		 $Goodscate = D('Goodscate')->where(array('cate_id' => $data['cate_id']))->find();
		 $parent_id = $Goodscate['parent_id'];
		 if ($parent_id == 0) {
			$this->baoError('请选择二级分类');
		 }
        $data['shopcate_id'] = (int) $data['shopcate_id'];
		
		//$this->baoError($data['shopcate_id']);
		
		
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
        $data['city_id'] = $this->shop['city_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        } $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->baoError('市场价格不能为空');
        } 
		$data['mall_price'] = (int) ($data['mall_price'] * 100);
        if (empty($data['mall_price'])) {
            $this->baoError('商城价格不能为空');
        }
		$data['select5'] = (int) $data['select5'];
        $data['use_integral'] = (int) $data['use_integral'];
		//商城检测积分合法性开始
		if (!D('Goods')->check_add_use_integral($data['use_integral'],$data['mall_price'])) {//传2参数
            $this->baoError(D('Goods')->getError(), 3000, true);
        }
		//商城检测积分合法性结束	
        $data['instructions'] = SecurityEditorHtml($data['instructions']);
        if (empty($data['instructions'])) {
            $this->baoError('购买须知不能为空');
        }
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
		$data['is_vs1'] = (int) $data['is_vs1'];
		$data['is_vs2'] = (int) $data['is_vs2'];
		$data['is_vs3'] = (int) $data['is_vs3'];
		$data['is_vs4'] = (int) $data['is_vs4'];
		$data['is_vs5'] = (int) $data['is_vs5'];
		$data['is_vs6'] = (int) $data['is_vs6'];
		$data['select1'] = (int) $data['select1'];
        $data['select2'] = (int) $data['select2'];
        $data['select3'] = (int) $data['select3'];
        $data['select4'] = (int) $data['select4'];
        
		$data['profit_enable'] = (int) $data['profit_enable'];
        $data['profit_rate1'] = (int) $data['profit_rate1'];
        $data['profit_rate2'] = (int) $data['profit_rate2'];
        $data['profit_rate3'] = (int) $data['profit_rate3'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['sold_num'] = 0;
        $data['view'] = 0;
        $data['is_mall'] = 1;
        return $data;
    }

    public function edit($goods_id = 0) {
        $this->check_weidian();
        if ($goods_id = (int) $goods_id) {
            $obj = D('Goods');
            if (!$detail = $obj->find($goods_id)) {
                $this->error('请选择要编辑的商品');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('请不要试图越权操作其他人的内容');
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
					$photos = $this->_post('photos', false);
                    if (!empty($photos)) {
                        D('Goodsphoto')->upload($goods_id, $photos);
                    }					
                    $this->shuxin($goods_id);
                    $this->baoSuccess('操作成功', U('goods/index'));
                }
                $this->baoError('操作失败');
            } else {
          
             $goodsInfo=D('Goods')->where('goods_id='.I('GET.goods_id',0))->find();
             $this->assign('goodsInfo',$goodsInfo);  // 商品详情   
             $this->assign('goodsType',M("TpGoodsType")->select());
          
          
                $this->assign('detail', $obj->_format($detail));
				$this->assign('parent_id',D('Goodscate')->getParentsId($detail['cate_id']));
				$this->assign('attrs', D('Goodscateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $detail['cate_id']))->select());
                $this->assign('cates', D('Goodscate')->fetchAll());
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
				$this->assign('photos', D('Goodsphoto')->getPics($goods_id));
				$this->assign('kuaidi', D('Pkuaidi')->where(array('shop_id'=>$this->shop_id,'type'=>goods))->select());
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
        } $data['shop_id'] = (int) $this->shop_id;
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }

		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('副标题不能为空');
        }

		$data['guige'] = htmlspecialchars($data['guige']);
        if (empty($data['guige'])) {
            $this->baoError('规格不能为空');
        }

		$data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->baoError('库存不能为空');
        } 
		$data['is_reight'] = (int) $data['is_reight'];

		$data['weight'] = (int) $data['weight'];
		if ($data['is_reight'] == 1) {
			if (empty($data['weight'])) {
             	$this->baoError('重量不能为空');
			}
			if ($data['weight'] % 1 != 0) {
				$this->baoError('重量必须为1的倍数');
			}
        }
		$data['kuaidi_id'] = (int) $data['kuaidi_id'];
		if ($data['is_reight'] == 1) {
			if (empty($data['kuaidi_id'])) {
				$this->baoError('运费模板不能为空');
			}
		}	
        $shopdetail = D('Shop')->find($this->shop_id);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('请选择分类');
        }

        $data['shopcate_id'] = (int) $data['shopcate_id'];
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
        $data['city_id'] = $this->shop['city_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        } $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->baoError('市场价格不能为空');
        } 
        $data['mall_price'] = (int) ($data['mall_price'] * 100);
        if (empty($data['mall_price'])) {
            $this->baoError('商城价格不能为空');
        }
		//商城检测积分合法性开始
		if (!D('Goods')->check_add_use_integral($data['use_integral'],$data['mall_price'])) {//传2参数
            $this->baoError(D('Goods')->getError(), 3000, true);
        }
		//商城检测积分合法性结束
        $data['instructions'] = SecurityEditorHtml($data['instructions']);
        if (empty($data['instructions'])) {
            $this->baoError('购买须知不能为空');
        }
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
		$data['is_vs1'] = (int) $data['is_vs1'];
		$data['is_vs2'] = (int) $data['is_vs2'];
		$data['is_vs3'] = (int) $data['is_vs3'];
		$data['is_vs4'] = (int) $data['is_vs4'];
		$data['is_vs5'] = (int) $data['is_vs5'];
		$data['is_vs6'] = (int) $data['is_vs6'];
		$data['select1'] = (int) $data['select1'];
        $data['select2'] = (int) $data['select2'];
        $data['select3'] = (int) $data['select3'];
        $data['select4'] = (int) $data['select4'];
        $data['select5'] = (int) $data['select5'];
		$data['profit_enable'] = (int) $data['profit_enable'];
        $data['profit_rate1'] = (int) $data['profit_rate1'];
        $data['profit_rate2'] = (int) $data['profit_rate2'];
        $data['profit_rate3'] = (int) $data['profit_rate3'];
        $data['orderby'] = (int) $data['orderby'];
		$data['audit'] = 0;
        return $data;
    }
	  public function ajax($cate_id,$goods_id=0){
        if(!$cate_id = (int)$cate_id){
            $this->error('请选择正确的分类');
        }
        if(!$detail = D('Goodscate')->find($cate_id)){
            $this->error('请选择正确的分类');
        }
        $this->assign('cate',$detail);
        $this->assign('attrs',D('Goodscateattr')->order(array('orderby'=>'asc'))->where(array('cate_id'=>$cate_id))->select());
        if($goods_id){
            $this->assign('detail',D('Goods')->find($goods_id));
            $this->assign('maps',D('GoodsCateattr')->getAttrs($goods_id));
        }
        $this->display();
    }



}

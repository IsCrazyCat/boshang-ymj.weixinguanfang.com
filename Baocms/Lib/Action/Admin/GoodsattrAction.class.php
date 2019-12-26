<?php

class GoodsattrAction extends CommonAction {

    //规格列表
    public function index(){       
        $goodsTypeList = M("TpGoodsType")->select();
		$type_id = I('audit');
		if(!empty($type_id)){
			$where = array('type_id'=>$type_id);  
		}else{
			$where = ' 1 = 1 '; // 搜索条件      		
		}
        $model = D('TpSpec');
        $count = $model->where($where)->count();
        import('ORG.Util.Page');
        $Page = new Page($count,13);
        $show = $Page->show();
        $specList = $model->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();        
       
        foreach($specList as $k => $v){      
           $model = M('TpSpecItem');        
           $arr = $model->where("spec_id =$v[id]")->select(); 
           $arr = get_id_val($arr, 'id','item'); 
           $specList[$k]['spec_item'] = implode(' , ', $arr);
          
        }
        $this->assign('specList',$specList);
        $this->assign('page',$show);
        $goodsTypeList = M("TpGoodsType")->select(); 
        $goodsTypeList = convert_arr_key($goodsTypeList, 'id');
        $this->assign('goodsTypeList',$goodsTypeList);
		$this->assign('type_id',$type_id);
        $this->display();
    }
    

    //修改编辑
    public  function addEditSpec(){
            $model = D("TpSpec");                      
            $type = $_POST['id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新        
            if(IS_POST){                
                if(!$model->create(NULL,$type)){
                    $this->baoError('类型错误');
                }else {                   
                    if ($type == 2){
                        $model->save(); //更新数据库                   
                        $this->afterSave($_POST['id']);
						$this->baoSuccess('编辑成功！', U('goodsattr/index'));
                    }
                    else{
                        $insert_id = $model->add(); // 写入数据到数据库        
                        $this->afterSave($insert_id);
						$this->baoSuccess('添加成功！', U('goodsattr/index'));
                    }                    
					
                }  
            }                
           // 点击过来编辑时                 
           $id = $_GET['id'] ? $_GET['id'] : 0;       
           $spec = $model->find($id);
           $items = $this->getSpecItem($id);
           $spec[items] = implode(PHP_EOL, $items); 
           $this->assign('spec',$spec);
           $goodsTypeList = M("TpGoodsType")->select();   
           $this->assign('goodsTypeList',$goodsTypeList);           
           $this->display('spec');           
    }  



	
	//删除规格项目
    public function delGoodsSpec($id = 0) {
        if (is_numeric($id) && ($id = (int) $id)) {
			$obj = D('TpSpec');
			$count = $obj->judge_goods_item($id);//判断规格
			if($count > 0){
				$this->baoError('该类型下有规格不得删除');
			}
            $obj->delete($id);
            $this->baoSuccess('删除成功！', U('goodsattr/index'));
        } else {
            $attr_id = $this->_post('id', false);
            if (is_array($attr_id)) {
				$count = $obj->judge_goods_item($id);//判断规格
				if($count > 0){
					$this->baoError('该类型下有规格不得删除');
				}
                foreach ($id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('goodsattr/index'));
            }
            $this->baoError('请选择要删除的规格');
        }
    }
	
	

	//编辑的时候执行
    public function getSpecItem($spec_id) { 
        $model = M('TpSpecItem');        
        $arr = $model->where("spec_id = $spec_id")->select(); 
        $arr = get_id_val($arr, 'id','item');        
        return $arr;
    }   

	 //后置操作方法
     public function afterSave($id){
        $model = M("TpSpecItem"); 
        $post_items = explode(PHP_EOL, $_POST['items']);        
        foreach ($post_items as $key => $val)  {
            $val = str_replace('_', '', $val); // 替换特殊字符
            $val = str_replace('@', '', $val); // 替换特殊字符
            $val = trim($val);
            if(empty($val)) 
                unset($post_items[$key]);
            else                     
                $post_items[$key] = $val;
        }
        $db_items = $model->where("spec_id = $id")->getField('id,item');
        
        // 提交过来的 跟数据库中比较 不存在插
        foreach($post_items as $key => $val){
            if(!in_array($val, $db_items))            
                $dataList[] = array('spec_id'=>$id,'item'=>$val);            
        }
        $dataList && $model->addAll($dataList);
        /* 数据库中的 跟提交过来的比较 不存在删除*/
        foreach($db_items as $key => $val){
            if(!in_array($val, $post_items))       {       
               M("TpSpecGoodsPrice")->where("`key` REGEXP '^{$key}_' OR `key` REGEXP '_{$key}_' OR `key` REGEXP '_{$key}$'")->delete(); // 删除规格项价格表
               $model->where('id='.$key)->delete(); // 删除规格项
            }
        }        
    }     
}

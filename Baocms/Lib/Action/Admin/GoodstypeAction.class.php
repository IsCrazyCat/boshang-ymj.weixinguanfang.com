<?php

class GoodstypeAction extends CommonAction {

        public function index(){
        $model = D("TpGoodsType");     
        $count = $model->count(); 
        import('ORG.Util.Page'); 
        $Page  = new Page($count,100);
        $show  = $Page->show();
        $goodsTypeList = $model->order("id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('show',$show);
       
        $this->assign('goodsTypeList',$goodsTypeList);
        $this->display();
    }
    

    

    //编辑商品类型
    public  function addEditGoodsType(){        
            $_GET['id'] = $_GET['id'] ? $_GET['id'] : 0;     
            $model = M("TpGoodsType");
            if(IS_POST){                           
                    $model->create();
					$id = $_POST['id'];
                    if(!empty($id)){
                        $data['name']=I('name');
                        $data['id']=$id;   
                        $model->save($data);
						$this->baoSuccess("编辑成功!",U('index'));    
                    }else{
						$model->add();
                   		$this->baoSuccess("添加成功!",U('index'));    
					}
            }           
           $goodsType = $model->find($_GET['id']);
           $this->assign('goodsType',$goodsType);
           $this->display('goodsType');           
    }


    //商品属性列表
    public function goodsAttributeList(){

        $where = ' 1 = 1 '; // 搜索条件                        
        I('type_id')   && $where = "$where and type_id = ".I('type_id') ;                
        // 关键词搜索               
        $model = M('TpGoodsAttribute');
        $count = $model->where($where)->count();
                import('ORG.Util.Page'); // 导入分页类       
        $Page       = new Page($count,13);
        $show = $Page->show();
        $goodsAttributeList = $model->where($where)->order('`order` desc,attr_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $goodsTypeList = M("TpGoodsType")->select();
        foreach ($goodsTypeList as $k => $v) {
           $ss[$v[id]]=$v[name];
        }
        $attr_input_type = array(0=>'手工录入',1=>' 从列表中选择',2=>' 多行文本框');
        $this->assign('attr_input_type',$attr_input_type);
        $this->assign('goodsTypeLists',$ss);        
        $this->assign('goodsAttributeList',$goodsAttributeList);
        $this->assign('page',$show);// 赋值分页输出
        $goodsTypeList = M("TpGoodsType")->select();
        $this->assign('goodsTypeList',$goodsTypeList);
        $this->display();
    } 


    //添加修改商品属性
    public  function addEditGoodsAttribute(){
                        
            $model = D("TpGoodsAttribute");                      
            $type = $_POST['attr_id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新         
            $_POST['attr_values'] = str_replace('_', '', $_POST['attr_values']); // 替换特殊字符
            $_POST['attr_values'] = str_replace('@', '', $_POST['attr_values']); // 替换特殊字符            
            $_POST['attr_values'] = trim($_POST['attr_values']);

            if(IS_POST){                
                  $model->create();
                  $attr_id=I('attr_id');
                  $type_id=I('type_id');
                   
                    if ($attr_id)                        { 
                        $model->save(); // 写入数据到数据库  
                        $this->baoSuccess("更新成功!!!",U('goodsAttributeList',array("type_id"=>$type_id)));                         
                    }
                    else{   
                        $insert_id = $model->add(); // 写入数据到数据库  
                        $this->baoSuccess("添加成功!!!",U('goodsAttributeList',array("type_id"=>$type_id)));                          
                    }
                    

                 
            }                
           // 点击过来编辑时                 
           $_GET['attr_id'] = $_GET['attr_id'] ? $_GET['attr_id'] : 0;       
           $goodsTypeList = M("TpGoodsType")->select();           
           $goodsAttribute = $model->find($_GET['attr_id']);           
           $this->assign('goodsTypeList',$goodsTypeList);                   
           $this->assign('goodsAttribute',$goodsAttribute);
           $this->display('goodsAttribute');           
    }  


	
	//删除商品属性
    public function delGoodsAttribute($attr_id = 0) {
        if (is_numeric($attr_id) && ($attr_id = (int) $attr_id)) {
			
			//D('TpGoodsAttr')->judge_goods_attr($attr_id);//判断商品属性暂时取消
			
            $obj = D('TpGoodsAttribute');
            $obj->delete($attr_id);
            $this->baoSuccess('删除成功！', U('goodsAttributeList',array('type_id'=>$_GET['oo'])));
        } else {
            $attr_id = $this->_post('id', false);
            if (is_array($attr_id)) {
				//D('TpGoodsAttr')->judge_goods_attr($attr_id);//判断商品属性暂时取消
                $obj = D('TpGoodsAttribute');
                foreach ($id as $attr_id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('goodsAttributeList',array('type_id'=>$_GET['oo'])));
            }
            $this->baoError('请选择要删除的规格');
        }
    }
	
	    


    //删除商品类型
    public function delGoodsType($id = 0) {
        if (is_numeric($id) && ($id = (int) $id)) {
			$count = D('TpGoodsAttribute')->where(array('type_id'=>$id))->count();   
			if($count > 0){
				$this->baoError('该类型下有商品属性不得删除');
			}
            $obj = M('TpGoodsType');
            $obj->delete($id);
            $this->baoSuccess('删除成功！', U('goodstype/index'));
        } else {
            $id = $this->_post('id', false);
            if (is_array($id)) {
				$count = D('TpGoodsAttribute')->where(array('type_id'=>$id))->count();   
				if($count > 0){
					$this->baoError('该类型下有商品属性不得删除');
				}
                $obj = M('TpGoodsType');
                foreach ($id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('goodstype/index'));
            }
            $this->baoError('请选择要删除的规格');
        }
    }
   

}

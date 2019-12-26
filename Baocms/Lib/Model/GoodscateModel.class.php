<?php

class GoodscateModel extends CommonModel {
    protected $pk = 'cate_id';
    protected $tableName = 'goods_cate';
    protected $token = 'goods_cate';
    protected $orderby = array('orderby' => 'asc');

     public function  getParentsId($id){
        $data = $this->fetchAll();
        $parent_id = $data[$id]['parent_id'];
        $parent_id2 = $data[$parent_id]['parent_id'];
        if($parent_id2 == 0) return $parent_id;
        return  $parent_id2;
    }

      public function getChildren($id){
        $local = array();
        //循环两层即可了 最高3级分类
        $data = $this->fetchAll();
        foreach($data  as $val){
            if($val['parent_id'] == $id){
                $child = true;
                foreach($data as  $val1){
                    if($val1['parent_id'] == $val['cate_id']){
                        $child = FALSE;
                        $local[]=$val1['cate_id'];
                    }
                }
                if($child){
                    $local[]=$val['cate_id'];
                }
            }         
        }
        return $local;
    }
	
	public function check_parent_id($cate_id){
		$obj = D('Goodscate');
		$detail = $obj->where(array('cate_id'=>$cate_id))->find();
		if($detail['parent_id'] == 0){  //如果等0，检测二级分类
			$count_parent_id = $obj->where(array('parent_id'=>$cate_id))->count();
			if($count_parent_id >= 1){
		 		return false;
			}else{
				return true;
			}
		}else{
			return true;
		}  
    }
	
	public function check_cate_id_goods($cate_id){
		$obj = D('Goods');
        $count = $obj->where(array('cate_id'=>$cate_id))->count();
		if($count > 0){
		 	return false;
		}
        return true;
    }
}
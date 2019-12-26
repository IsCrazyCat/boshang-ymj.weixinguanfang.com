<?php
class AppointcateModel extends CommonModel{
    protected $pk = 'cate_id';
    protected $tableName = 'appoint_cate';
    protected $token = 'appoint_cate';
    protected $orderby = array('orderby' => 'asc');
    public function getParentsId($id){
        $data = $this->fetchAll();
        $parent_id = $data[$id]['parent_id'];
        return $parent_id;
    }
    public function getChildren($id, $ty = true){
        $local = array();//暂时 只支持 2级分类
        $data = $this->fetchAll();
        if ($ty) {
            $local[] = $id;
        }
        foreach ($data as $val) {
            if ($val['parent_id'] == $id) {
                $local[] = $val['cate_id'];
            }
        }
        return $local;
    }
	
	public function check_parent_id($cate_id){
		$obj = D('Appointcate');
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
	
	public function check_cate_id_appoint($cate_id){
		$obj = D('Appoint');
        $count = $obj->where(array('cate_id'=>$cate_id))->count();
		if($count > 0){
		 	return false;
		}
        return true;
    }
}
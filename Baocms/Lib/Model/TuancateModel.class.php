<?php
class TuancateModel extends CommonModel
{
    protected $pk = 'cate_id';
    protected $tableName = 'tuan_cate';
    protected $token = 'tuan_cate';
    protected $orderby = array('orderby' => 'asc');
    public function getParentsId($id)
    {
        $data = $this->fetchAll();
        $parent_id = $data[$id]['parent_id'];
        return $parent_id;
    }
    public function getChildren($id)
    {
        $local = array();
        //暂时 只支持 2级分类
        $data = $this->fetchAll();
        $local[] = $id;
        foreach ($data as $val) {
            if ($val['parent_id'] == $id) {
                $local[] = $val['cate_id'];
            }
        }
        return $local;
    }
    public function check_parent_id($cate_id){
        $obj = D('Tuancate');
        $detail = $obj->where(array('cate_id' => $cate_id))->find();
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
    public function check_cate_id_tuan($cate_id)
    {
        $obj = D('Tuan');
        $count = $obj->where(array('cate_id' => $cate_id))->count();
        if ($count > 0) {
            return false;
        }
        return true;
    }
	//返回3个变量
	public function return_column_value($cate_id){
		$tuancates = D('Tuancate')->fetchAll();
        if ($tuancates[$cate_id]['parent_id'] == 0) {
            $catstr =  $tuancates[$cate_id]['cate_name'];
			return array('catstr' => $catstr);
        } else {
            $catstr =  $tuancates[$tuancates[$cate_id]['parent_id']]['cate_name'];
            $cat = $tuancates[$cate_id]['parent_id'];
            $catestr = $tuancates[$cate_id]['cate_name'];
			return array('catstr' => $catstr, 'cat' => $cat,'catestr' => $catestr);
        }
    }
}
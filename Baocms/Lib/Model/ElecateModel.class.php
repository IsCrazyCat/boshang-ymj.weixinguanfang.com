<?php
class ElecateModel extends CommonModel{
    protected $pk   = 'cate_id';
    protected $tableName =  'ele_cate';
    
    public function updateNum($cate_id){
        $cate_id = (int) $cate_id;
        $count = D('Eleproduct')->where(array('cate_id'=>$cate_id))->count();
        return $this->save(array(
            'cate_id' => $cate_id,
            'num'     => (int)$count
        ));
    }
    
}
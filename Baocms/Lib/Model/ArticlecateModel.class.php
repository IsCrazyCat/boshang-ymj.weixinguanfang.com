<?php
class ArticlecateModel extends CommonModel{
    protected $pk   = 'cate_id';
    protected $tableName =  'article_cate';
    protected $token = 'article_cate';
    protected $orderby = array('orderby'=>'asc');
    
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
  
   
}
<?php
class WeidiancateModel extends CommonModel
{
    protected $pk = 'cate_id';
    protected $tableName = 'weidian_cate';
    protected $token = 'weidian_cate';
    protected $orderby = array('orderby' => 'asc');
    public function getParentsId($id)
    {
        $data = $this->fetchAll();
        $parent_id = $data[$id]['parent_id'];
        return $parent_id;
    }
    public function getChildren($id, $ty = true)
    {
        $local = array();
        //暂时 只支持 2级分类
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
}
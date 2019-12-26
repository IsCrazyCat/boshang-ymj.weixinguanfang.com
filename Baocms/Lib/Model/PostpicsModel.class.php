<?php
class PostpicsModel extends CommonModel
{
    protected $pk = 'pic_id';
    protected $tableName = 'post_pics';
    public function upload($post_id, $photos)
    {
        $post_id = (int) $post_id;
        $this->delete(array("where" => array('post_id' => $post_id)));
        foreach ($photos as $val) {
            $this->add(array('pic' => $val, 'post_id' => $post_id));
        }
        return true;
    }
    public function getPics($post_id)
    {
        $post_id = (int) $post_id;
        return $this->where(array('post_id' => $post_id))->select();
    }
}
<?php
class PostzanModel extends CommonModel
{
    protected $pk = 'zan_id';
    protected $tableName = 'post_zan';
    public function checkIsZan($post_id, $ip)
    {
        return $this->find(array('where' => array('post_id' => $post_id, 'create_ip' => $ip)));
    }
}
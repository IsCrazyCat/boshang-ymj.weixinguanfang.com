<?php

class ThreadpostphotoModel extends CommonModel{
    protected $pk   = 'photo_id';
    protected $tableName =  'thread_post_photo';
    
    public function upload($post_id,$photos){
        $post_id = (int)$post_id;
        $this->delete(array("where"=>array('post_id'=>$post_id)));
        foreach($photos as $val){
            $this->add(array('photo'=>$val,'post_id'=>$post_id));
        }
        return true;
    }
    public function getbypost_ids($post_ids){
		$post_ids = $post_ids;
		$sql = "SELECT * FROM `bao_thread_post_photo` WHERE ( `post_id` IN (". $post_ids . " ) )" ;
		$ret = $this->query($sql);
		return $ret;
	}
}
<?php
class ArticledonateModel extends CommonModel{
    protected $pk   = 'donate_id';
    protected $tableName =  'article_donate';
	
	public function checkIsZan($comment_id,$ip){
        return $this->find(array('where'=>array('comment_id'=>$comment_id,'create_ip'=>$ip)));        
    }
    
}
<?php
class ArticlecommentModel extends CommonModel{
    protected $pk   = 'comment_id';
    protected $tableName =  'article_comment';
	
	public function checkIsZan($comment_id,$ip){
        return $this->find(array('where'=>array('comment_id'=>$comment_id,'create_ip'=>$ip)));        
    }
    
}
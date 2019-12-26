<?php
class ArticlephotoModel extends CommonModel{
    protected $pk   = 'pic_id';
    protected $tableName =  'article_photos';
	
	  public  function upload_comment($comment_id,$photos){
        $this->delete(array("where"=>array('comment_id'=>$comment_id)));
        foreach($photos as $val){
            $this->add(array(
                'comment_id' => $comment_id,
                'photo' => htmlspecialchars($val)
            ));
        }
        return true;
    }
	
	 public  function upload_article($article_id,$photos){
        $this->delete(array("where"=>array('article_id'=>$article_id)));
        foreach($photos as $val){
            $this->add(array(
                'article_id' => $article_id,
                'photo' => htmlspecialchars($val)
            ));
        }
        return true;
    }
	
	
	public function getPics_comment($comment_id){
        $comment_id = (int) $comment_id;
        return $this->where(array('comment_id'=>$comment_id))->select();
    }
	
	public function getPics_article($article_id){
        $article_id = (int) $article_id;
        return $this->where(array('article_id'=>$article_id))->select();
    }
}
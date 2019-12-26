<?php
class ArticleModel extends CommonModel{
    protected $pk   = 'article_id';
    protected $tableName =  'article';
    
	//返回统计结果
	public function get_article_pic_count($article_id){
	        $count_photo_list = $this->get_article_photo_list_array($article_id);
			if (!empty($count_photo_list)) {
				return count($count_photo_list);
			}
			
	}
	
	//返回数组
	public function get_article_photo($article_id){
	        $get_photo_list = $this->get_article_photo_list_array($article_id);
			if (!empty($get_photo_list)) {
				return $get_photo_list;
			}
			
	}
	
	//获取图片
	public function get_article_photo_list_array($article_id){
	        $get_article_photo = D('Article')->find($article_id);
			if($get_article_photo['photo']){
                $get_article_photo_array[]=array('pic_id'=>$get_article_photo['photo']);
			}
			$get_details_pic = getImgs($get_article_photo['details']);
			if(!empty($get_details_pic)){
                  $get_details_pic_array = array();
                  foreach ($get_details_pic as $key => $value) {
                      $get_details_pic_array[]=array('pic_id'=>$value);
                   }
            }

			$photo_list = array();
            if(!empty($get_article_photo_array)){
               $photo_list = array_merge($photo_list,$get_article_photo_array);
            }
			if(!empty($get_details_pic_array)){
               $photo_list = array_merge($photo_list,$get_details_pic_array);
            }
			
			$photo_list = array_slice($photo_list,$Page->firstRow,$Page->listRows);	

			if (!empty($photo_list)) {
				return $photo_list;
			}
	}
			
			
	public function rands(){
		$news = $this->order(array('article_id' => 'desc','closed'=>0,'audit'=>0))->limit(0, 45)->select();
		shuffle($news);
		if (empty($news)) {
			return array();
		}
		$num = (3 < count($news) ? 3 : count($news));
		$keys = array_rand($news, $num);
		$return = array();
		foreach ($news as $k => $val ) {
			if (in_array($k, $keys)) {
				$return[] = $val;
			}
		}
		return $return;
	}
}
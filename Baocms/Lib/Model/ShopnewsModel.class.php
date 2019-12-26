<?php
class ShopnewsModel extends CommonModel{
    protected $pk   = 'news_id';
    protected $tableName =  'shop_news';
	//去更新数据
	public function update_shop_news($news_id, $data){
		if (false == D('Shopnews')->where(array('news_id' => $news_id))->save(array(
				'shop_id' => $data['shop_id'],
				'cate_id' => $data['cate_id'],
				'title' => $data['title'],
				'source' => $data['source'],
				'profiles' => $data['profiles'],
				'keywords' => $data['keywords'],
				'photo' => $data['photo'],
				'details' => $data['details']
			))){
                return false;
            }
			return true;   
		}
    
			

}
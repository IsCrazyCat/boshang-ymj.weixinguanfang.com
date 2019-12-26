<?php
class ArticleAction extends CommonAction {



    public function index() {
        $content_id = (int) $this->_get('content_id');
        if (empty($content_id)) {
            $content_id = 1;
        }
        $contents = D('Systemcontent')->fetchAll();
        if (!$contents[$content_id]) {
            $this->error('该内容不存在');
            die;
        }
        $this->assign('detail', $contents[$content_id]);
        $this->assign('contents', $contents);
        $this->assign('content_id', $content_id);
        $this->seodatas['title'] = $contents[$content_id]['title'];
        $this->display();
    }

    public function detail($article_id = 0) {

        if ($article_id = (int) $article_id) {
			
	
	    define('__HOST__', 'http://' . $_SERVER['HTTP_HOST']);
		define('BAO_DOMAIN', $this->_CONFIG['site']['hostdo']); //设置根域名

		preg_match("#http://(.*?)\.#i",__HOST__,$match);  //匹配当前二级域名前缀
        $nowhost = $match[1];
        $oneT = D('Article')->where(array('article_id' => $article_id))->Field('city_id')->select();   //查询当前信息对应的城市ID

		$cityid = D('city')->where(array('city_id' => $oneT['0']['city_id']))->Field('pinyin,domain')->select();  //通过城市ID 查询对应的 二级域名前缀

	    if($cityid[0]['domain'] == '1' && $nowhost != $cityid[0]['pinyin'] ){ //判断是否开启二级域名  以及当前 域名前缀是否等于拼音
		    header("Location: http://".$cityid[0]['pinyin'] . '.'. BAO_DOMAIN.$_SERVER['REQUEST_URI']);die;			 
		}
        //end
		
		
		
            $obj = D('Article');
            if (!$detail = $obj->find($article_id)) {
                $this->error('没有该文章');
            }
            $cates = D('Articlecate')->fetchAll();
            $obj->updateCount($article_id, 'views');
            $this->assign('detail', $detail);

            $this->assign('parent_id', D('Articlecate')->getParentsId($detail['cate_id']));
            $this->assign('cates', $cates);
            $this->assign('cate',$cates[$detail['cate_id']]);
            $this->seodatas['title'] = $detail['title'];
            $this->seodatas['cate_name'] = $cates[$detail['cate_id']];
            $this->seodatas['keywords'] = $detail['keywords'];
            $this->seodatas['desc'] = $detail['desc'];

            $this->display();
        } else {
            $this->error('没有该文章');
        }
    }

    public function system() {
        $content_id = (int) $this->_get('content_id');
        if (empty($content_id)) {
            $this->error('该内容不存在');
            die;
        }
        $contents = D('Systemcontent')->fetchAll();
        if (!$contents[$content_id]) {
            $this->error('该内容不存在');
            die;
        }
        $this->assign('detail', $contents[$content_id]);
        $this->assign('contents', $contents);
        $this->assign('content_id', $content_id);
        $this->seodatas['title'] = $contents[$content_id]['title'];
        $this->display();
    }

}
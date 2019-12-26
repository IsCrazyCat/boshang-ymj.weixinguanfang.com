<?php

class VillageAction extends CommonAction {
	
		
	protected function _initialize(){
       parent::_initialize();
        if ($this->_CONFIG['operation']['village'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
	
	
	
  public function index() {
        $this->display(); // 输出模板;
    }



    public function village_load() {
		
        $map = array('user_id' => $this->uid);
		$joined = D('Villagejoin')->where($map)->order(array('join_id' => 'desc'))->limit(0,2)-> select();	
		foreach ($joined as $val) {
			$cmm_ids[$val['village_id']] = $val['village_id'];
		}
		$this->assign('list', D('Village')->itemsByIds($cmm_ids));		
        $this->display();
    }


	public function tongzhi(){
        
         $this->display(); // 输出模板;
    }
	
	public function tongzhi_load() {
		$village_id = (int) $this->_param('village_id');
		$noticenew = D('Villagenotice');
        $maps['community_id']  = array('in',$cmm_ids);
		$news = $noticenew->where($maps)->order(array('news_id' => 'desc'))->limit(0,10)->select();
		$this->assign('news', $news);
        $this->display();
    }
   
}
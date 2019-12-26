<?php
class  AdAction extends CommonAction{
	public function click(){
		$ad_id = (int) $this->_param('ad_id');
		$aready = (int) $this->_param('aready');
		$obj = D('Ad');
		if(!$detail = $obj->find($ad_id)){
            $this->error('没有该商家信息');
        }
		if($detail['closed'] ==1){
            $this->error('广告已关闭');
        }
		if ($detail['end_date'] < TODAY) {
            $this->error('广告已过期');
        }
		$obj->click_number($ad_id);
		if(!empty($detail['link_url'])){
            header("Location:" . $detail['link_url']);
        }else{
			if($aready ==1){
				header('Location:' . U('wap/index/index'));
			}else{
				header('Location:' . U('home/index/index'));	
			}
		}
	}
	
	public function community_click(){
		$ad_id = (int) $this->_param('ad_id');
		$aready = (int) $this->_param('aready');
		$obj = D('Communityad');
		if(!$detail = $obj->find($ad_id)){
            $this->error('没有该小区信息');
        }
		$obj->click_community_number($ad_id);
		if(!empty($detail['link_url'])){
            header("Location:" . $detail['link_url']);
        }else{
			if($aready ==1){
				header('Location:' . U('wap/index/index'));
			}else{
				header('Location:' . U('home/index/index'));	
			}
		}
	}
}
<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class CardAction extends CommonAction {

	public function index($shop_id)
    {
		if(!$shop_id){
			$this->Error('参数错误');
		}elseif(!$weixin =D('User_weixin')->where(array('shop_id',$shop_id))->select()){
			$this->Error('该商家还未绑定公众号');
		}else{
			$client = $this->wechat_client();
			$cardlist = $client->getcardlist($weixin);
			$res = json_decode($cardlist, true);
			$arr1 = $card = array();
			if($res['errcode'] == 0  && $res['total_num']>0){
				foreach($res['card_id_list'] as $k => $v){
					$arr = array();
					$arr = json_decode($client->getcarddetail($weixin,$v), true);
					$arr1[] = $arr['card'];
				}
				
				foreach($arr1 as $k => $v){
					
					$card[$k] = $v[strtolower($v['card_type'])]['base_info'];
					$card[$k]['card_type'] = $v['card_type'];
					$card[$k]['iid'] = base64_encode($v[strtolower($v['card_type'])]['base_info']['id']);
				}
				include_once "Baocms/Lib/Action/weixin/jssdk.php";
				//Import::L('weixin/jssdk.php');
				$jsSdk = new WeixinJSSDK($weixin['appid'], $weixin['secret']);
				$jsSdk1 = $jsSdk->getSignPackage();
				$this->assign('wxjscfg',$jsSdk1);
				//$this->pagedata['wxjscfg'] = $jsSdk->getSignPackage();
				
				foreach($card as $k => $v){
					$card[$k]['wxjscfg'] = $jsSdk->getCardSignPackage($v['id']);
				}
				$this->assign('detail',$detail);
				$this->assign('codeurl',$codeurl);
				//$this->pagedata['shop_id'] = $shop_id;
				//$this->pagedata['card'] = $card;
				$this->display();
			}else{
				$this->Error('您没有卡劵,请先在微信中添加');
				 //$this->err->add('您没有卡劵,请先在微信中添加', 213);
			}
		}
    }

	public function get_card($shop_id,$id)
	{
		if(!$shop_id){
			$this->err->add('参数错误', 215);
		}elseif(!$weixin = D('User_weixin')->where(array('shp_id'=>$shop_id))->select()){
			$this->Error('该商家还未绑定公众号');
		}else{
			$client = $this->wechat_client();
			$id = base64_decode($id);
			$res = $client->get_card($weixin,$id);
			$cart = json_decode($res, true);
			$url = $client->getQrcodeImgUrlByTicket($cart['ticket']);
			$qrurl =  U('card/wxqrcode', array('shop_id'=>$shop_id,'id'=>$id));
			//$qrurl = $this->mklink('weixin/card:wxqrcode', array(), array('shop_id'=>$shop_id,'id'=>$id));
			header("Location:{$qrurl}");
		}
	}

	

	public function wxqrcode($id,$shop_id)

    {
		if(!$id = (int)$id){
            exit('params error');
        }
		if(!$shop_id = (int)$shop_id){
            exit('params error');
        }
		if(!$shop_id){
			$this->Error('参数错误');
		}elseif(!$weixin = D('User_weixin')->where(array('shp_id'=>$shop_id))->select()){
			$this->Error('该商家还未绑定公众号');
		}else{
			$client = $this->wechat_client();
			$cardlist = $client->getcardlist($weixin);
			$detail = json_decode($client->getcarddetail($weixin,$id), true);
			$card = $detail['card'][strtolower($detail['card']['card_type'])]['base_info'];
			$card['card_type'] = $detail['card']['card_type'];
			$card['iid'] = base64_encode($detail['card'][strtolower($detail['card']['card_type'])]['base_info']['id']);
			$res = $client->get_card($weixin,$id);
			$cart = json_decode($res, true);
			$url = $client->getQrcodeImgUrlByTicket($cart['ticket']);
			$this->assign('id',$id);
			//$this->pagedata['id'] = $id;
			if(strpos($_SERVER["HTTP_USER_AGENT"], 'MicroMessenger')){
				$this->assign('isling',1);
				//$this->pagedata['isling'] = 1;
			}
			Import::L('weixin/jssdk.php');
			$jsSdk = new WeixinJSSDK($weixin['appid'], $weixin['secret']);
			$jsSdk1 = $jsSdk->getSignPackage();
			$this->assign('wxjscfg',$jsSdk1);
			//$this->pagedata['wxjscfg'] = $jsSdk->getSignPackage();
			
			$card['wxjscfg'] = $jsSdk->getCardSignPackage($card['id']);

			$card['description'] = str_replace('；','<br>',$card['description']);
			if($card['date_info']['fixed_term'] >0){
				$card['stime'] = date('Y-m-d',$card['wxjscfg']['timestamp']);
				$card['etime'] = date('Y-m-d',$card['wxjscfg']['timestamp']+($card['date_info']['fixed_term']-1)*24*3600);
			}else{
				$card['stime'] = date('Y-m-d',$card['date_info']['begin_timestamp']);
				$card['etime'] = date('Y-m-d',$card['date_info']['end_timestamp']);
			}
			$this->assign('detail',$detail);
			$this->assign('codeurl',$codeurl);
			$this->display();
			//$this->pagedata['detail'] = $card;
			//$this->pagedata['codeurl'] = $url;
		    //$this->tmpl = 'weixin/card/wxqrcode.html';
		}
    }
}
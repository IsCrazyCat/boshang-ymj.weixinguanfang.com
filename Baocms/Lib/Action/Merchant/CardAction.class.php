<?php

/**

 * Copy Right IJH.CC

 * Each engineer has a duty to keep the code elegant

 * $Id$

 */

class CardAction extends CommonAction {
    public function _initialize() {
        parent::_initialize();
    }
    public function index($page=1)

    {
        if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
		$obj = D('user_weixin');
		$map['shop_id'] = $shop_id;
		$weixin=$obj->where($map)->getField('shop_id');
		$client = $this->wechat_client($shop_id);
		import('ORG.Util.Page'); // 导入分页类
		$cardlist = $client->getcardlist($shop_id);
		$res = json_decode($cardlist, true);
		$arr1 = $card = array();
		if($res['errcode'] == 0  && $res['total_num']>0){
			foreach($res['card_id_list'] as $k => $v){
				$arr = array();
				$arr = json_decode($client->getcarddetail($shop_id,$v), true);
				$arr1[] = $arr['card'];
			}
			foreach($arr1 as $k => $v){				
				$card[$k] = $v[strtolower($v['card_type'])]['base_info'];
				$card[$k]['card_type'] = $v['card_type'];
				$card[$k]['iid'] = base64_encode($v[strtolower($v['card_type'])]['base_info']['id']);
			}
			$this->assign('card', $card);
			$this->assign('shop_id', $shop_id);
			$this->display();
		}else{
			$this->baoError('您没有卡劵,请先在微信中添加');
		}
    }

	public function get_card($shop_id,$iid)
	{
		if(!$shop_id = (int)$shop_id){
			 $this->baoError('商家不能为空');
		}
        $detail = D('shop')->find($shop_id);
        $shop = D('Shopdetails')->find($shop_id);
		$id = $iid;
		$client = $this->wechat_client();
		$id = base64_decode($id);
		$res = $client->get_card($shop_id,$id);
		$cart = json_decode($res, true);
		$url = $client->getQrcodeImgUrlByTicket($cart['ticket']);
        $jsSdk = $this->weixin_jssdk($shop['app_id'], $shop['app_key']);
		$jsSdk1 = $jsSdk->getSignPackage($id);
		$this->assign('wxjscfg',$jsSdk1);
				//$this->pagedata['wxjscfg'] = $jsSdk->getSignPackage();
				
		//$qrurl = $this->mklink('ucenter/weixin/addon/card:wxqrcode', array(), array('id'=>$id));
        $this->assign('id', $id);
		$url2 = U('weixin/voucher/index', array('shop_id' => $shop_id));
        $url3 = __HOST__ . $url2;
        $tooken = 'shop_' . $shop_id;
        $codeurl = baoQrCode($tooken, $url3);
        if(!$codeurl){
			$this->baoError('二维码链接不存在');
			
        }
		$this->assign('codeurl', $codeurl);
        $this->assign('shop', $shop);
        $this->assign('detail', $detail);
		$this->display('wxqrcode');
		//header("Location:{$qrurl}");
	}
	
	/*public function wxqrcode($id)
	{
		if($id = (int) $id){
			$this->assign('id', $id);
		}
		$this->display();
	}*/


	public function show()

	{

		if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
		$obj = D('user_weixin');
		$map['shop_id'] = $shop_id;
		$weixin=$obj->where($map)->getField('weixin_id');
		//$site = K::M('system/config')->get('site');
		//$url = $site['siteurl'].'/'.K::M('helper/link')->mklink('weixin/voucher:index', array($shop_id));
		$url =  U('voucher/index', array('shop_id' => $shop_id));
		$qrurl =  U('card/wxqrcode2', array('codeurl' => $url));
		//$qrurl = $this->mklink('ucenter/weixin/addon/card:wxqrcode2', array(), array('codeurl'=>$url));
		header("Location:{$qrurl}");

	}



	public function consume($id)

	{
		if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
        $shop = D('Shopdetails')->find($shop_id);
		if($code = $this->_post('code')){
			$client = $this->wechat_client();
			$id = base64_decode($id);
			$res = $client->get_code($shop,$id,$code);
			$cart = json_decode($res, true);
			if($cart['errcode'] == 0){
				if($cart['card']['begin_time']<time() && $cart['card']['end_time']>time() && $cart['can_consume'] === true){
					$res2 = $client->consume($shop,$id,$code);
					$res3 = json_decode($res2, true);
					if($res3['errcode'] == 0 || $res3['errmsg'] == 'ok'){
						$this->baoError('核销成功');
					}else{
						$this->baoError('核销失败');
					}
				}else{
                    $this->baoError('该卡劵已过期 或已被核销');
				}			
			}else{
				$this->baoError('该卡劵不存在 请查证卡劵ID');
			}
		}else{
			$this->assign('id', $id);
			//$this->pagedata['id'] = $id;
			$this->display('form');
			//$this->tmpl = 'ucenter/weixin/addon/card/form.html';

		}

	}



	public function delete_card($iid)

	{
		if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
		$client = $this->wechat_client($shop_id);
		$id = base64_decode($iid);
        $shop = D('Shopdetails')->find($shop_id);
		$res = $client->delete_card($shop,$id);
		$cart = json_decode($res, true);
		if($cart['errcode'] == 0 || $cart['errmsg'] == 'ok'){
			  $this->baoError('删除成功');
		}else{
             $this->baoError('删除失败');
		}
	}

	public function wxqrcode2()
    {
		if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
        $url = U('weixin/voucher/index', array('shop_id' => $shop_id));
        $url = __HOST__ . $url;
        $tooken = 'shop_' . $shop_id;
        $codeurl = baoQrCode($tooken, $url);
        if(!$codeurl){
			$this->baoError('二维码链接不存在');
			
        }
		$this->assign('codeurl', $codeurl);
        //$this->pagedata['codeurl'] = $codeurl;
		$this->display();
        //$this->tmpl = 'ucenter/weixin/addon/card/wxqrcode2.html';
    }



	public function wxqrcode($id,$shop_id)

    {
		if(!$id = $id){
            exit('params error');
        }
		if(!$shop_id = $shop_id){
            exit('params error');
        }
		if(!$shop_id){
			$this->Error('参数错误');
		}elseif(!$weixin =D('Shopdetails')->where(array('shop_id'=>$shop_id))->find()){
			$this->Error('该商家还未绑定公众号');
		}else{
			$client = $this->wechat_client($shop_id);
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
			$jsSdk = $this->weixin_jssdk($weixin['app_id'], $weixin['app_key']);
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
			$this->assign('card',$card);
			$this->assign('detail',$detail);
			$this->assign('codeurl',$url);
			$this->display();
			//$this->pagedata['detail'] = $card;
			//$this->pagedata['codeurl'] = $url;
		    //$this->tmpl = 'weixin/card/wxqrcode.html';
		}
    }

	protected function wechat_client($shop_id)
    {
        static $client = null;
		$obj = D('Weixin');
        if($client === null){
            if(!$client = $obj->admin_wechat_client($shop_id)){
                exit('网站公众号设置错误');
            }
        }
        return $client;
    }



    protected function access_openid($force = false)

    {

        static $openid = null;

        if($force || $openid === null){

            if($code = $this->GP('code')){

                $client = $this->wechat_client();

                $ret = $client->getAccessTokenByCode($code);

                $openid = $ret['openid'];

            }else{

                if(!$openid = $this->cookie->get('wx_openid')){

                    $client = $this->wechat_client();

                    $url = $this->request['url'].'/'.$this->request['uri'];

                    $authurl = $client->getOAuthConnectUri($url, $state, 'snsapi_userinfo');

                    header('Location:'.$authurl);

                    exit();

                }

            }

            $this->cookie->set('wx_openid', $openid);

        }

        if(empty($openid)){

            exit('获取授权失败');

        }

        return $openid;

    }

}
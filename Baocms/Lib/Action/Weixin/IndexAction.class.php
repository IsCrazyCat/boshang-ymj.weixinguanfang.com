<?php
class IndexAction extends CommonAction
{
    public function index()
    {
        $data = $this->weixin->request();
        switch ($data['MsgType']) {
            //
            case 'event':
                if ($data['Event'] == 'subscribe') {
                    if (isset($data['EventKey']) && !empty($data['EventKey'])) {
                        $this->events();
                    } else {
                        $this->event();
                    }
                }
                if ($data['Event'] == 'SCAN') {
                    $this->scan();
                }
                break;
            case 'location':
                $this->location($data);
                break;
            default:
                //其余的类型都算关键词
                $this->keyword($data);
                break;
        }
    }
    private function location($data)
    {
        $lat = addcslashes($data['Location_X']);
        $lng = addcslashes($data['Location_Y']);
        $list = D('Shop')->where(array('audit' => 1, 'closed' => 0))->order(" (ABS(lng - '{$lng}') +  ABS(lat - '" . $lat . '\') )  asc ')->limit(0, 10)->select();
        if (!empty($list)) {
            $content = array();
            foreach ($list as $item) {
                $content[] = array($item['shop_name'], $item['addr'], $this->getImage($item['photo']), __HOST__ . '/wap/shop/detail/shop_id/' . $item['shop_id'] . '.html');
            }
            $this->weixin->response($content, 'news');
        } else {
            $this->weixin->response('很抱歉没有合适的商家推荐给您', 'text');
        }
    }
    private function keyword($data){
        if (empty($data['Content'])) {
            return;
        }
		
		
        if ($this->shop_id == 0) {
            $key = explode(' ', $data['Content']);
            $keyword = D('Weixinkeyword')->checkKeyword($key[0]);
            if ($keyword) {
			 switch ($keyword['type']) {
                    case 'text':
                        $this->weixin->response($keyword['contents'], 'text');
                        break;
                    case 'news':
                        $content = array();
                        $content[] = array(
                            $keyword['title'],
                            $keyword['contents'],
                            $this->getImage($keyword['photo']),
                            $keyword['url'],
                        );
                        $this->weixin->response($content, 'news');
                        break;
                }
			
            } else {
                // 没有特定关键词则查询POIS信息
                $openid = $data['FromUserName'];
                $con = D('Connect')->getConnectByOpenid('weixin', $openid);
                $usr = D('Users')->where(array('user_id' => $con['uid']))->find();
                $map = array();
                $map['name|tag'] = array('LIKE', array('%' . $key[0] . '%', '%' . $key[0], $key[0] . '%', 'OR'));
                $lat = $usr['lat'];
                $lng = $usr['lng'];
                if (empty($lat) || empty($lng)) {
                    $lat = $this->_CONFIG['site']['lat'];
                    $lng = $this->_CONFIG['site']['lng'];
                }
                $squares = returnSquarePoint($lng, $lat, 2);
                $map['lat'] > $squares['right-bottom']['lat'];
                $map['lat'] < $squares['left-top']['lat'];
                $map['lng'] > $squares['left-top']['lng'];
                $map['lng'] > $squares['right-bottom']['lng'];
                $orderby = 'orderby asc';
                //查询包年固顶
                $word = D('Nearword')->where(array('text' => $key[0]))->find();
                $word_pois = $word['pois_id'];
                if ($word_pois) {
                    $ding = D('Near')->find($word_pois);
                }
                if ($ding) {
                    $map['pois_id'] != $word_pois;
                    if ($ding['shop_id']) {
                        $url = $this->_CONFIG['site']['host'] . '/wap/shop/detail/shop_id/' . $ding['shop_id'] . '.html';
                    } else {
                        $url = $this->_CONFIG['site']['host'] . '/wap/biz/detail/pois_id/' . $ding['pois_id'] . '.html';
                    }
                    $text = '<a href="' . $url . '">' . $ding['name'] . '</a> ★★★★★ /:strong
' . $ding['address'] . '
' . $ding['telephone'] . '

';
                }
                $list = D('Near')->where($map)->order($orderby)->limit(0, 9)->select();
                //判断是否从POIS中获取到信息
                if (count($list) > 0) {
                    foreach ($list as $val) {
                        if (intval($val['pois_id']) != intval($word_pois)) {
                            if (intval($val['shop_id']) > 0) {
                                $url = $this->_CONFIG['site']['host'] . '/wap/shop/detail/shop_id/' . $val['shop_id'] . '.html';
                            } else {
                                $url = $this->_CONFIG['site']['host'] . '/wap/biz/detail/pois_id/' . $val['pois_id'] . '.html';
                            }
                            $distance = getDistanceCN($val['lat'], $val['lng'], $lat, $lng);
                            if (!empty($val['telephone'])) {
                                $text .= '<a href="' . $url . '">' . $val['name'] . '</a>
' . $val['address'] . ' (' . $distance . ')
' . $val['telephone'] . '

';
                            } else {
                                $text .= '<a href="' . $url . '">' . $val['name'] . '</a>
' . $val['address'] . ' (' . $distance . ')

';
                            }
                        }
                    }
                }
                if (empty($ding) && count($list) == 0) {
                    $text = '回禀圣上，臣翻阅了整个新华字典也没找到你要的东东。依臣所见，还是点击下面菜单试试吧！';
                }
                //发送信息到客户
                $this->weixin->response($text, 'text');
            }
        } else {
           $keyword = D('Shopweixinkeyword')->checkKeyword($this->shop_id, $data['Content']);
            if ($keyword) {
                switch ($keyword['type']) {
                    case 'text':
                        $this->weixin->response($keyword['contents'], 'text');
                        break;
                    case 'news':
                        $content = array();
                        $content[] = array(
                            $keyword['title'],
                            $keyword['contents'],
                            $this->getImage($keyword['photo']),
                            $keyword['url'],
                        );
                        $this->weixin->response($content, 'news');
                        break;
                }
            } else {
                $this->event();
            }
        }
    }
	
	
	
    //响应用户的事件
    private function event(){
        if ($this->shop_id == 0) {
            if ($this->_CONFIG['weixin']['type'] == 1) {
                $this->weixin->response($this->_CONFIG['weixin']['description'], 'text');
            } else {
                $content[] = array($this->_CONFIG['weixin']['title'], $this->_CONFIG['weixin']['description'], $this->getImage($this->_CONFIG['weixin']['photo']), $this->_CONFIG['weixin']['linkurl']);
                $this->weixin->response($content, 'news');
            }
        } else {
            //
            $data['get'] = $_GET;
            $data['post'] = $_POST;
            $data['data'] = $this->weixin->request();
            //file_put_contents('/www/web/bao_baocms_cn/public_html/Baocms/Lib/Action/Weixin/aaa.txt', var_export($data, true));
            $weixin_msg = unserialize($this->shopdetails['weixin_msg']);
            if ($weixin_msg['type'] == 1) {
                $this->weixin->response($weixin_msg['description'], 'text');
            } else {
                $content[] = array($weixin_msg['title'], $weixin_msg['description'], $this->getImage($weixin_msg['photo']), $this->_CONFIG['weixin']['linkurl']);
                $this->weixin->response($content, 'news');
            }
        }
    }
    private function events()
    {
        $data['get'] = $_GET;
        $data['post'] = $_POST;
        $data['data'] = $this->weixin->request();
        //file_put_contents('/www/web/bao_baocms_cn/public_html/Baocms/Lib/Action/Weixin/aaa.txt', var_export($data['data'], true));
        if (!empty($data['data'])) {
            $datas = explode('_', $data['data']['EventKey']);
            $id = $datas[1];
            if (!($detail = D('Weixinqrcode')->find($id))) {
                die;
            }
            $type = $detail['type'];
            if ($type == 1) {
                $shop_id = $detail['soure_id'];
                $shop = D('Shop')->find($shop_id);
                $content[] = array($shop['shop_name'], $shop['addr'], $this->getImage($shop['photo']), __HOST__ . '/mobile/shop/detail/shop_id/' . $shop_id . '.html');
                //file_put_contents('/www/web/bao_baocms_cn/public_html/Baocms/Lib/Action/Weixin/bbb.txt', var_export($content, true));
                $result = D('Connect')->getConnectByOpenid('weixin', $data['data']['FromUserName']);
                if (!empty($result)) {
                    $user_id = $result['uid'];
                    $ymd = date('Y-m-d', NOW_TIME);
                    $ymdarr = explode('-', $ymd);
                    if (!($de = D('Census')->where(array('user_id' => $user_id))->find())) {
                        $datac = array('user_id' => $user_id, 'year' => $ymdarr[0], 'month' => $ymdarr[1], 'day' => $ymdarr[2]);
                        D('Census')->add($datac);
                    }
                    if (!($fans = D('Shopfavorites')->where(array('user_id' => $user_id, 'shop_id' => $shop_id))->find())) {
                        $dataf = array('user_id' => $user_id, 'shop_id' => $shop_id, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
                        D('Shopfavorites')->add($dataf);
                        D('Shop')->updateCount($shop_id, 'fans_num');
                    } else {
                        if ($fans['closed'] == 1) {
                            D('Shopfavorites')->save(array('favorites_id' => $fans['favorites_id'], 'closed' => 0));
                        }
                    }
                }
                $this->weixin->response($content, 'news');
            } elseif ($type == 2) {
                //抢购
                $tuan_id = $detail['soure_id'];
                $tuan = D('Tuan')->find($tuan_id);
                $content[] = array($tuan['title'], $tuan['intro'], $this->getImage($tuan['photo']), __HOST__ . '/mobile/tuan/detail/tuan_id/' . $tuan_id . '.html');
                file_put_contents('/www/web/bao_baocms_cn/public_html/Baocms/Lib/Action/Weixin/bbb.txt', var_export($content, true));
                $result = D('Connect')->getConnectByOpenid('weixin', $data['data']['FromUserName']);
                if (!empty($result)) {
                    $user_id = $result['uid'];
                    $ymd = date('Y-m-d', NOW_TIME);
                    $ymdarr = explode('-', $ymd);
                    if (!($de = D('Census')->where(array('user_id' => $user_id))->find())) {
                        $datac = array('user_id' => $user_id, 'year' => $ymdarr[0], 'month' => $ymdarr[1], 'day' => $ymdarr[2]);
                        D('Census')->add($datac);
                    }
                    if (!($fans = D('Shopfavorites')->where(array('user_id' => $user_id, 'shop_id' => $tuan['shop_id']))->find())) {
                        $dataf = array('user_id' => $user_id, 'shop_id' => $tuan['shop_id'], 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
                        D('Shopfavorites')->add($dataf);
                        D('Shop')->updateCount($tuan['shop_id'], 'fans_num');
                    } else {
                        if ($fans['closed'] == 1) {
                            D('Shopfavorites')->save(array('favorites_id' => $fans['favorites_id'], 'closed' => 0));
                        }
                    }
                }
                $this->weixin->response($content, 'news');
            } elseif ($type == 3) {
                //购物
                $goods_id = $detail['soure_id'];
                $goods = D('Goods')->find($goods_id);
                $shops = D('Shop')->find($goods['shop_id']);
                $content[] = array($goods['title'], $shops['shop_name'], $this->getImage($goods['photo']), __HOST__ . '/wap/mall/detail/goods_id/' . $goods_id . '.html');
                //file_put_contents('/www/web/bao_baocms_cn/public_html/Baocms/Lib/Action/Weixin/bbb.txt', var_export($content, true));
                $result = D('Connect')->getConnectByOpenid('weixin', $data['data']['FromUserName']);
                if (!empty($result)) {
                    $user_id = $result['uid'];
                    $ymd = date('Y-m-d', NOW_TIME);
                    $ymdarr = explode('-', $ymd);
                    if (!($de = D('Census')->where(array('user_id' => $user_id))->find())) {
                        $datac = array('user_id' => $user_id, 'year' => $ymdarr[0], 'month' => $ymdarr[1], 'day' => $ymdarr[2]);
                        D('Census')->add($datac);
                    }
                    if (!($fans = D('Shopfavorites')->where(array('user_id' => $user_id, 'shop_id' => $goods['shop_id']))->find())) {
                        $dataf = array('user_id' => $user_id, 'shop_id' => $goods['shop_id'], 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
                        D('Shopfavorites')->add($dataf);
                        D('Shop')->updateCount($goods['shop_id'], 'fans_num');
                    } else {
                        if ($fans['closed'] == 1) {
                            D('Shopfavorites')->save(array('favorites_id' => $fans['favorites_id'], 'closed' => 0));
                        }
                    }
                }
                $this->weixin->response($content, 'news');
            }elseif($type == 4){

                //分销
                $fuid = $detail['soure_id'];
                //存入cookie 然后跳转到首页 走自动注册流程，然后注册时取出fuid，创建会员

                $client = D('Weixin')->wechat_client();
                $access_token = $client->getAccessToken();

                $wx_info = $client->getUserInfoById($data['data']['FromUserName']);

                //检查用户是否注册，如果已经注册则不做任何处理
                $data = array(
                    'type' => 'weixin',
                    'open_id' => $data['data']['FromUserName'],
                    'nickname' => $wx_info['nickname'],
                    'headimgurl' => $wx_info['headimgurl'],
                    'fuid'=>$fuid
                );

                $test = $this->wxconn($data,$data['open_id']);
                if($data['data']['FromUserName']=='oz6Qc6OtmkWc-wM2NVd_4weH79oY'){
                    $this->weixin->response($data['data']['FromUserName'], 'text');
                }
            }
        }
    }
    public function scan()
    {
        $data['data'] = $this->weixin->request();
        //file_put_contents('/www/web/bao_baocms_cn/public_html/Baocms/Lib/Action/Weixin/ccc.txt', var_export($data['data'], true));
        if (!empty($data['data'])) {
            $id = $data['data']['EventKey'];
            if (!($detail = D('Weixinqrcode')->find($id))) {
                die;
            }
            $type = $detail['type'];
            if ($type == 1) {
                $shop_id = $detail['soure_id'];
                $shop = D('Shop')->find($shop_id);
				//扫码回复是这里
                $content[] = array($shop['shop_name'], $shop['addr'], $this->getImage($shop['photo']), __HOST__ . '/wap/shop/detail/shop_id/' . $shop_id . '.html');
                //file_put_contents('/www/web/bao_baocms_cn/public_html/Baocms/Lib/Action/Weixin/bbb.txt', var_export($content, true));
                $result = D('Connect')->getConnectByOpenid('weixin', $data['data']['FromUserName']);
                if (!empty($result)) {
                    $user_id = $result['uid'];
                    $ymd = date('Y-m-d', NOW_TIME);
                    $ymdarr = explode('-', $ymd);
                    if (!($fans = D('Shopfavorites')->where(array('user_id' => $user_id, 'shop_id' => $shop_id))->find())) {
                        $dataf = array('user_id' => $user_id, 'shop_id' => $shop_id, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
                        D('Shopfavorites')->add($dataf);
                        D('Shop')->updateCount($shop_id, 'fans_num');
                    } else {
                        if ($fans['closed'] == 1) {
                            D('Shopfavorites')->save(array('favorites_id' => $fans['favorites_id'], 'closed' => 0));
                        }
                    }
                }
                $this->weixin->response($content, 'news');
            } elseif ($type == 2) {
                //抢购
                $tuan_id = $detail['soure_id'];
                $tuan = D('Tuan')->find($tuan_id);
                $content[] = array($tuan['title'], $tuan['intro'], $this->getImage($tuan['photo']), __HOST__ . '/wap/tuan/detail/tuan_id/' . $tuan_id . '.html');
                //file_put_contents('/www/web/bao_baocms_cn/public_html/Baocms/Lib/Action/Weixin/aaa.txt', var_export($content, true));
                $result = D('Connect')->getConnectByOpenid('weixin', $data['data']['FromUserName']);
                if (!empty($result)) {
                    $user_id = $result['uid'];
                    if (!($fans = D('Shopfavorites')->where(array('user_id' => $user_id, 'shop_id' => $tuan['shop_id']))->find())) {
                        $dataf = array('user_id' => $user_id, 'shop_id' => $tuan['shop_id'], 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
                        D('Shopfavorites')->add($dataf);
                        D('Shop')->updateCount($tuan['shop_id'], 'fans_num');
                    } else {
                        if ($fans['closed'] == 1) {
                            D('Shopfavorites')->save(array('favorites_id' => $fans['favorites_id'], 'closed' => 0));
                        }
                    }
                }
                $this->weixin->response($content, 'news');
            } elseif ($type == 3) {
                //购物
                $goods_id = $detail['soure_id'];
                $goods = D('Goods')->find($goods_id);
                $shops = D('Shop')->find($goods['shop_id']);
                $content[] = array($goods['title'], $shops['shop_name'], $this->getImage($goods['photo']), __HOST__ . '/wap/mall/detail/goods_id/' . $goods_id . '.html');
                //file_put_contents('/www/web/bao_baocms_cn/public_html/Baocms/Lib/Action/Weixin/aaa.txt', var_export($content, true));
                $result = D('Connect')->getConnectByOpenid('weixin', $data['data']['FromUserName']);
                if (!empty($result)) {
                    $user_id = $result['uid'];
                    if (!($fans = D('Shopfavorites')->where(array('user_id' => $user_id, 'shop_id' => $goods['shop_id']))->find())) {
                        $dataf = array('user_id' => $user_id, 'shop_id' => $goods['shop_id'], 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
                        D('Shopfavorites')->add($dataf);
                        D('Shop')->updateCount($goods['shop_id'], 'fans_num');
                    } else {
                        if ($fans['closed'] == 1) {
                            D('Shopfavorites')->save(array('favorites_id' => $fans['favorites_id'], 'closed' => 0));
                        }
                    }
                }
                $this->weixin->response($content, 'news');
            }
        }
    }
    private function getImage($img){
		return config_weixin_img($img);
        //return __HOST__ . '/attachs/' . $img;
    }
    //微信自动注册为用户
    public function wxconn($data,$openid) {

        $connect = D('Connect')->getConnectByOpenid($data['type'], $data['open_id']);

        if (empty($connect)) {
            $connect = $data;
            $connect['connect_id'] = D('Connect')->add($data);
        } else {
            D('Connect')->save(array('connect_id' => $connect['connect_id'], 'token' => $data['token'], 'nickname' => $data['nickname']));
        }
        if (empty($connect['uid'])) {
            session('connect', $connect['connect_id']);
            // 用户数据整理
            $host = explode('.', $this->_CONFIG['site']['host']);
            $account = uniqid() . '@' .'ymj.com';
            if ($data['nickname'] == '') {
                $nickname = $data['type'] . $connect['connect_id'];
            } else {
                $nickname = $data['nickname'];
            }
            $user = array(
                'fuid' => $data['fuid'],
                'account' => $account,
                'password' => rand(10000000, 999999999),
                'nickname' => $nickname,
                'ext0' => $account,
                'face' => $data['headimgurl'],
                'token' => $data['token'],
                'reg_time' => NOW_TIME,
                'reg_ip' => get_client_ip()
            );
            //注册用户资料
            if (!D('Passport')->register($user)) {
                $this->error('创建帐号失败');
            }

            // 注册第三方接口
            $token = D('Passport')->getToken();
            $connect['uid'] = $token['uid'];
            D('Connect')->save(array('connect_id' => $connect['connect_id'], 'uid' => $connect['uid']));// 注册成功智能跳转
            $backurl = session('backurl');
//            if (!empty($backurl)) {
//                header("Location:{$backurl}");
//            } else {
//                header('Location:' . U('user/member/index'));
//            }
        } else {
            setuid($connect['uid']);
            session('access', $connect['connect_id']);
            // 注册成功智能跳转
            $backurl = session('backurl');
//            if (!empty($backurl)) {
//                header("Location:{$backurl}");
//            } else {
//                header('Location:' . U('user/member/index'));
//            }
        }
        die;
    }
}
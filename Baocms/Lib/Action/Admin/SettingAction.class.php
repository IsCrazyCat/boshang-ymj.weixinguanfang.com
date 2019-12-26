<?php
class SettingAction extends CommonAction{
    public function site(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'site', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('站点设置成功', U('setting/site'));
        } else {
            $this->assign('citys', D('City')->fetchAll());
            $this->assign('ranks', D('Userrank')->fetchAll());
            //增加分销
            $this->display();
        }
    }
    public function config(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'config', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('全局设置成功', U('setting/config'));
        } else {
            $this->display();
        }
    }
    public function attachs(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'attachs', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('附件设置成功', U('setting/attachs'));
        } else {
            $this->display();
        }
    }
    public function mall(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'mall', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('商城设置成功', U('setting/mall'));
        } else {
            $this->display();
        }
    }
    public function ucenter(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'ucenter', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('设置成功', U('setting/ucenter'));
        } else {
            $this->display();
        }
    }
   public function sms(){
		$config = D('Setting')->fetchAll();
		if(!empty($config['sms']['sms_bao_account'])){
			$http = tmplToStr('http://www.smsbao.com/query?u='.$config["sms"]["sms_bao_account"].'&p='.md5($config["sms"]["sms_bao_password"]), $local);
			$res = file_get_contents($http);
			$res1 = explode(",", $res);
			if($res1[1] > 0){
				$number = $res1[1];
			}else{
				$number = '短信宝账户或者密码错了';
			}
		}else{
			$number = '短信宝账或户密码未设置';
		}
		$this->assign('number', $number);
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'sms', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('短信配置成功', U('setting/sms'));
        } else {
            $this->display();
        }
    }
	
	public function pay(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'pay', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('支付设置成功', U('setting/pay'));
        } else {
            $this->display();
        }
    }
    public function weixin(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'weixin', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('微信设置成功', U('setting/weixin'));
        } else {
            $this->display();
        }
    }
  
    public function weixinmenu(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            D('Weixin')->weixinmenu($data);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'weixinmenu', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('菜单设置成功', U('setting/weixinmenu'));
        } else {
            $this->display();
        }
    }
    public function connect(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'connect', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('设置成功', U('setting/connect'));
        } else {
            $this->display();
        }
    }
    public function integral(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'integral', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('积分设置成功', U('setting/integral'));
        } else {
            $this->display();
        }
    }
    public function weidian(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'weidian', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('微店设置成功', U('setting/weidian'));
        } else {
            $this->display();
        }
    }
    public function prestige(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'prestige', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('设置成功', U('setting/prestige'));
        } else {
            $this->display();
        }
    }
    public function mail(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'mail', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('邮箱设置成功', U('setting/mail'));
        } else {
            $this->display();
        }
    }
    public function mobile(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'mobile', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('手机功能设置成功', U('setting/mobile'));
        } else {
            $this->display();
        }
    }
   
    public function other(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'other', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('设置成功', U('setting/other'));
        } else {
            $this->display();
        }
    }
	
	public function profit(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'profit', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('分销设置成功', U('setting/profit'));
        } else {
		
			$this->assign('ranks', D('Userrank')->fetchAll());
            $this->display();
        }
    }
    public function operation(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'operation', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('站点功能成功', U('setting/operation'));
        } else {
            $this->display();
        }
    }
    public function register(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'register', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('注册成功', U('setting/register'));
        } else {
            $this->display();
        }
    }
    public function share(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'share', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('分享设置成功', U('setting/share'));
        } else {
            $this->display();
        }
    }
    public function cash() {
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'cash', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('提现设置成功', U('setting/cash'));
        } else {
			$this->assign('ranks', D('Userrank')->fetchAll());
            $this->display();
        }
    }
    public function collects(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'collects', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('采集设置成功', U('setting/collects'));
        } else {
            $this->display();
        }
    }
    public function search(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'search', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('搜索设置成功', U('setting/search'));
        } else {
            $this->display();
        }
    }
    public function sms_shop(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'sms_shop', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('购买短信设置成功', U('setting/sms_shop'));
        } else {
            $this->display();
        }
    }
	public function running(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'running', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('跑腿设置成功', U('setting/running'));
        } else {
            $this->display();
        }
    }
	public function community(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'community', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('小区设置成功', U('setting/community'));
        } else {
            $this->display();
        }
    }
	 public function appoint(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data = serialize($data);
            D('Setting')->save(array('k' => 'appoint', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('家政设置成功', U('setting/appoint'));
        } else {
            $this->display();
        }
    }
    public function ele(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'ele', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('外卖更新设置成功', U('setting/ele'));
        } else {
            $this->display();
        }
    }
	public function goods(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'goods', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('商城更新设置成功', U('setting/goods'));
        } else {
            $this->display();
        }
    }
	public function zhe(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['time'] = time();
            $data = serialize($data);
            D('Setting')->save(array('k' => 'zhe', 'v' => $data));
            D('Setting')->cleanCache();
            $this->baoSuccess('五折卡设置成功', U('setting/zhe'));
        } else {
            $this->display();
        }
    }
}
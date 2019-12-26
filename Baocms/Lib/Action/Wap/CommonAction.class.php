<?php
class CommonAction extends Action
{
    protected $uid = 0;
    protected $member = array();
    protected $seodatas = array();
    protected $_CONFIG = array();
    protected $citys = array();
    protected $areas = array();
    protected $bizs = array();
    protected $city_id = 0;
    protected $city = array();
    protected function _initialize()
    {
        define('__HOST__', 'http://' . $_SERVER['HTTP_HOST']);
        $this->_CONFIG = D('Setting')->fetchAll();
        $this->citys = D('City')->fetchAll();
        define('BAO_DOMAIN', $this->_CONFIG['site']['hostdo']);
        //设置根域名
        $this->assign('citys', $this->citys);
        $this->city_id = cookie('city_id');
        //p($this->uid);die;
//		if (is_mobile()) {
//           if ($_SERVER['HTTP_HOST'] == BAO_DOMAIN || $_SERVER['HTTP_HOST'] != 'www.' . BAO_DOMAIN) {
//               header("Location: http://www." . BAO_DOMAIN . $_SERVER['REQUEST_URI']);
//               die;
//           }
//        }
       
        if (empty($this->city_id)) {
            import('ORG/Net/IpLocation');
            $IpLocation = new IpLocation('UTFWry.dat');// 实例化类 参数表示IP地址库文件
            $result = $IpLocation->getlocation($_SERVER['REMOTE_ADDR']);
            foreach ($this->citys as $val) {
                if (strstr($result['country'], $val['name'])) {
                    $city = $val;
                    $this->city_id = $val['city_id'];
                    break;
                }
            }
            if (empty($city)) {
                $this->city_id = $this->_CONFIG['site']['city_id'];
                $city = $this->citys[$this->_CONFIG['site']['city_id']];
            }
        } else {
            $city = $this->citys[$this->city_id];
        }
        $this->city = $city;
        define('IN_MOBILE', true);
        $ctl = strtolower(MODULE_NAME);
        $act = strtolower(ACTION_NAME);
        $is_weixin = is_weixin();
        $is_weixin = $is_weixin == false ? false : true;
        define('IS_WEIXIN', $is_weixin);
        searchWordFrom();
        $this->uid = getUid();

        if (!empty($this->uid)) {
            $member = $MEMBER = $this->member = D('Users')->find($this->uid);//客户端缓存会员数据
            $member['password'] = '';
            $member['token'] = '';
            cookie('member', $member, 86000);//cookie保存时间，建议后台设置，暂时这样修改
        }
        if ($ctl == 'member') {
            if (empty($this->uid)) {
                header('Location: ' . U('passport/login'));
                die;
            }
        }
        //这里主要处理推广链接入口！如果是用户推广进来的会增加到COOKIE
        $invite = (int) $this->_get('invite');
        if (!empty($invite)) {//这里改成了获得积分
            cookie('invite_id', $invite);
        }
        //三级分销开始
        $fuid = (int) $this->_get('fuid');
        if (!empty($fuid)) {
            session('fuid', $fuid);
            $profit_expire = (int) $this->_CONFIG['profit']['profit_expire'];
            if ($profit_expire) {
                cookie('fuid', $fuid, $profit_expire * 60 * 60);
            } else {
                cookie('fuid', $fuid);
            }
        }
        $tui_uid = (int) $this->_get('tui_uid');
        if ($tui_uid) {
            if ($goods_id = (int) $this->_get('goods_id')) {
                //两个条件都满足的情况下 COOKIE 存一个月
                cookie('tui', $tui_uid . '_' . $goods_id, 30 * 86400);//一个月有效果
            }
        }
        $tui_uids = (int) $this->_get('tui_uids');
        if ($tui_uids) {
            cookie('tuis', $tui_uids, 15 * 86400); //半个月有效果
        }
        $this->_CONFIG = D('Setting')->fetchAll();
        define('__HOST__', $this->_CONFIG['site']['host']);
        if (!empty($city['name'])) {
            $this->_CONFIG['site']['cityname'] = $city['name'];
        }
		
		//统计PC导航
		if ($nav_id = (int) $this->_param('nav_id')) {
            D('Navigation')->navigation_click($nav_id);
        }
		if (empty($this->uid)) {
            //对微信用户是否登录进行判断
            wx_auto_login($this->_CONFIG,$act);
		}

        $local = D('Near')->GetLocation();
        $this->assign('local', $local);
        $this->assign('CONFIG', $this->_CONFIG);
        $this->assign('MEMBER', $this->member);
        $this->areas = D('Area')->fetchAll();
        $areass = array();
        foreach ($this->areas as $key => $v) {
            if ($v['city_id'] == $this->city_id) {
                $areass[] = $this->areas[$key];
            }
        }
        $this->assign('areas', $areass);
        $limit_area = array();
        foreach ($this->areas as $k => $val) {
            if ($val['city_id'] == $this->city_id) {
                $limit_area[] = $val['area_id'];
            }
        }
        $this->bizs = D('Business')->fetchAll();
        $this->assign('bizs', $this->bizs);
        $this->assign('areass', $this->areas);
        $this->assign('today', TODAY);//兼容模版的其他写法
        $this->assign('nowtime', NOW_TIME);
        $this->assign('ctl', strtolower(MODULE_NAME));//主要方便调用
        $this->assign('act', ACTION_NAME);
        $this->assign('is_weixin', IS_WEIXIN);
        $this->assign('city_name', $city['name']);//您当前可能在的城市
        $this->assign('city_id', $this->city_id);
        $city_ids = array('0', $this->city_id);
        $city_ids = join(',', $city_ids);
        $this->assign('city_ids', $city_ids);
        $this->msg();
        $goods = session('goods');
        $this->assign('cartnum', (int) array_sum($goods));
        $footer = $this->_CONFIG['other']['footer'];
        $this->assign('footer', $footer);//获取微信的PID全局
        $appid = $this->_CONFIG['weixin']["appid"];
        $appsecret = $this->_CONFIG['weixin']["appsecret"];
        import("@/Net.Jssdk");
        $jssdk = new JSSDK("{$appid}", "{$appsecret}");
        $signPackage = $jssdk->GetSignPackage();
        $this->signPackage = $signPackage;//检测是否有店铺
        $this->assign('is_shop', $is_shop = D('Shop')->find(array('where' => array('user_id' => $this->uid))));
        $web_close = $this->_CONFIG['site']['web_close'];
        $web_close_title = $this->_CONFIG['site']['web_close_title'];

        if ($web_close == 0) {
            $this->display('public:web_close');
            die;
        }
    }
    private function seo()
    {
        $this->assign('mobile_title', $this->mobile_title);
        $this->assign('mobile_keywords', $this->mobile_keywords);
        $this->assign('mobile_description', $this->mobile_description);
    }
    protected function check_mobile()
    {
        //检测用户是否绑定手机
        $u = D('Users');
        $m = $u->where('user_id =' . $this->uid)->getField('mobile');
        if ($m == null || !isMobile($m)) {
            $mobile_open = 0;
        } else {
            $mobile_open = 1;
        }
        $this->assign('mobile_open', $mobile_open);
    }
    protected function mobile()
    {
        //绑定手机
        if ($this->isPost()) {
            $mobile = $this->_post('mobile');
            $yzm = $this->_post('yzm');
            if (empty($mobile) || empty($yzm)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '请填写正确的手机及手机收到的验证码！'));
            }
            $s_mobile = session('mobile');
            $s_code = session('code');
            if ($mobile != $s_mobile) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机号码和收取验证码的手机号不一致！'));
            }
            if ($yzm != $s_code) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '验证码不正确！'));
            }

            if (empty($this->uid)) {
                $this->error('登录状态失效!', U('Wap/passport/login'));
            }
            $data = array('user_id' => $this->uid, 'mobile' => $mobile);
            if (D('Users')->save($data)) {
                D('Users')->integral($this->uid, 'mobile');
                D('Users')->prestige($this->uid, 'mobile');
                $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您通过手机认证！'));
            }
            $this->ajaxReturn(array('status' => 'error', 'msg' => '更新失败！'));
        } else {
            $this->display();
        }
    }
    //删除分类信息获取验证码
    protected function life_yzm()
    {
        if (!($mobile = $this->_post('mobile'))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请输入正确的手机号码！'));
        }
        if (!isMobile($mobile)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请输入正确的手机号码！'));
        }
        session('mobile', $mobile);
        $randstring = session('life_code', 100);
        if (empty($randstring)) {
            $randstring = rand_string(6, 1);
            session('life_code', $randstring);
        }
        //如果开启大鱼
        if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
            D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_yzm', $mobile, array(
				'sitename' => $this->_CONFIG['site']['sitename'], 
				'code' => $randstring
			));
        } else {
            D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
        }
        $this->ajaxReturn(array('status' => 'success', 'msg' => '短信发送成功，请留意收到的手机短信！', 'code' => session('code')));
    }
    protected function mobile2()
    {
        //绑定手机
        if ($this->isPost()) {
            $mobile = $this->_post('mobile');
            $yzm = $this->_post('yzm');
            if (empty($mobile) || empty($yzm)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '请填写正确的手机及手机收到的验证码！'));
            }
            $s_mobile = session('mobile');
            $s_code = session('code');
            if ($mobile != $s_mobile) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机号码和收取验证码的手机号不一致！'));
            }
            if ($yzm != $s_code) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '验证码不正确！'));
            }
            $data = array('user_id' => $this->uid, 'mobile' => $mobile);
            if (D('Users')->save($data)) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您成功更换绑定手机号！'));
            }
            $this->ajaxReturn(array('status' => 'error', 'msg' => '更新失败！'));
        }
    }
    protected function sendsms()
    {
        if (!($mobile = $this->_post('mobile'))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请输入正确的手机号码！'));
        }
        if (!isMobile($mobile)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请输入正确的手机号码！'));
        }
        if ($user = D('Users')->where(array('mobile' => $mobile))->find()) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '手机号码已经存在！'));
        }
        session('mobile', $mobile);
        $randstring = session('code');
        if (empty($randstring)) {
            $randstring = rand_string(6, 1);
            session('code', $randstring);
        }
        //如果开启大鱼
        if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
            D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_yzm', $mobile, array(
				'sitename' => $this->_CONFIG['site']['sitename'], 
				'code' => $randstring));
        } else {
            D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
        }
        $this->ajaxReturn(array('status' => 'success', 'msg' => '短信发送成功，请留意收到的手机短信！', 'code' => session('code')));
    }
    protected function msg()
    {
        $msgs = D('Msg')->where(array('user_id' => array('IN', array(0, $this->uid))))->limit(0, 20)->select();
        $this->assign('msgs', $msgs);
        $msg_ids = array();
        foreach ($msgs as $val) {
            $msg_ids[] = $val['msg_id'];
        }
        if (!empty($this->uid)) {
            $reads = D('Msgread')->where(array('user_id' => $this->uid, 'msg_id' => array('IN', $msg_ids)))->select();
            $messagenum = count($msgs) - count($reads);
            $messagenum = $messagenum > 9 ? 9 : $messagenum;
            $readids = array();
            foreach ($reads as $val) {
                $readids[$val['msg_id']] = $val['msg_id'];
            }
            $this->assign('readids', $readids);
            $this->assign('messagenum', $messagenum);
        } else {
            $this->assign('messagenum', 0);
        }
    }
    private function tmplToStr($str, $datas)
    {
        preg_match_all('/{(.*?)}/', $str, $arr);
        foreach ($arr[1] as $k => $val) {
            $v = isset($datas[$val]) ? $datas[$val] : '';
            $str = str_replace($arr[0][$k], $v, $str);
        }
        return $str;
    }
    public function show($templateFile = '')
    {
        $this->seo();
        parent::display($templateFile);
    }
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '')
    {
        $this->seo();
        parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');
    }
    private function parseTemplate($template = '')
    {
        $depr = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);
        $theme = $this->getTemplateTheme();
        define('NOW_PATH', BASE_PATH . '/themes/' . $theme . 'Wap/');
        define('THEME_PATH', BASE_PATH . '/themes/default/Wap/');
        define('APP_TMPL_PATH', __ROOT__ . '/themes/default/Wap/');
        if ('' == $template) {
            $template = strtolower(MODULE_NAME) . $depr . strtolower(ACTION_NAME);
        } elseif (false === strpos($template, '/')) {
            $template = strtolower(MODULE_NAME) . $depr . strtolower($template);
        }
        $file = NOW_PATH . $template . C('TMPL_TEMPLATE_SUFFIX');
        if (file_exists($file)) {
            return $file;
        }
        return THEME_PATH . $template . C('TMPL_TEMPLATE_SUFFIX');
    }
    private function getTemplateTheme()
    {
        define('THEME_NAME', 'default');
        if ($this->theme) {
            $theme = $this->theme;
        } else {
            $theme = D('Template')->getDefaultTheme();
            if (C('TMPL_DETECT_THEME')) {
                $t = C('VAR_TEMPLATE');
                if (isset($_GET[$t])) {
                    $theme = $_GET[$t];
                } elseif (cookie('think_template')) {
                    $theme = cookie('think_template');
                }
                if (!in_array($theme, explode(',', C('THEME_LIST')))) {
                    $theme = C('DEFAULT_THEME');
                }
                cookie('think_template', $theme, 864000);
            }
        }
        return $theme ? $theme . '/' : '';
    }
    protected function ajaxLogin()
    {
        $str = '<script>';
        $str .= 'parent.ajaxLogin();';
        $str .= '</script>';
        die($str);
    }
    protected function checkFields($data = array(), $fields = array())
    {
        foreach ($data as $k => $val) {
            if (!in_array($k, $fields)) {
                unset($data[$k]);
            }
        }
        return $data;
    }
    protected function ipToArea($_ip)
    {
        return IpToArea($_ip);
    }
    protected function fengmiMsg($message, $jumpUrl = '', $time = 3000)
    {
        $str = '<script>';
        $str .= 'parent.boxmsg("' . $message . '","' . $jumpUrl . '","' . $time . '");';
        $str .= '</script>';
        die($str);
    }
    protected function fengmiErrorJump($message, $jumpUrl = '', $time = 3000)
    {
        $str = '<script>';
        $str .= 'parent.error("' . $message . '",' . $time . ',\'jumpUrl("' . $jumpUrl . '")\');';
        $str .= '</script>';
        die($str);
    }
    protected function fengmiAlert($message, $url = '')
    {
        $str = '<script>';
        $str .= 'parent.alert("' . $message . '");';
        if (!empty($url)) {
            $str .= 'parent.location.href="' . $url . '";';
        }
        $str .= '</script>';
        die($str);
    }
    protected function fengmiLoginSuccess()
    {
        //异步登录
        $str = '<script>';
        $str .= 'parent.parent.LoginSuccess();';
        $str .= '</script>';
        die($str);
    }
    protected function fengmiError($message, $time = 2000, $yzm = false, $parent = true)
    {
        $parent = $parent ? "parent." : "";
        $str = "<script>";
        if ($yzm) {
            $str .= $parent . "error(\"" . $message . "\"," . $time . ",\"verify()\");";
        } else {
            $str .= $parent . "error(\"" . $message . "\"," . $time . ");";
        }
        $str .= "</script>";
        exit($str);
    }
    protected function fengmiSuccess($message, $jumpUrl = "", $time = 2000, $parent = true)
    {
        $parent = $parent ? "parent." : "";
        $str = "<script>";
        $str .= $parent . "success(\"" . $message . "\"," . $time . ",'jump(\"" . $jumpUrl . "\")');";
        $str .= "</script>";
        exit($str);
    }
}
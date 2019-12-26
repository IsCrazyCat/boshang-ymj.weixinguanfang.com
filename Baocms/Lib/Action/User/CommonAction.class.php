<?php
class CommonAction extends Action{
    protected $uid = 0;
    protected $member = array();
    protected $_CONFIG = array();
    protected $citys = array();
    protected $areas = array();
    protected $bizs = array();
    protected $city_id = 0;
    protected $city = array();
    protected function _initialize(){
        define("__HOST__", "http://" . $_SERVER['HTTP_HOST']);
        define("IN_MOBILE", TRUE);
        $is_weixin = is_weixin();
        $is_weixin = !$is_weixin ? FALSE : TRUE;
        define("IS_WEIXIN", $is_weixin);
        searchwordfrom();
        $this->uid = (int) getuid();
        if (empty($this->uid)) {
            header("Location: " . U("wap/passport/login"));
            exit;
        }
        $this->member = D("Users")->find($this->uid);
		
        if (empty($this->member)) {
            setuid(0);
            header("Location: " . U("wap/passport/login"));
            exit;
        }
        $this->ex = d("Usersex")->find($this->uid);
        $this->checkFzmoney();
        $this->_CONFIG = d("Setting")->fetchAll();
        $this->citys = d("City")->fetchAll();
        $this->city_id = cookie("city_id");
        if (empty($this->city_id)) {
            import("ORG/Net/IpLocation");
            "UTFWry.dat";
            $IpLocation = new IpLocation();
            $result = $IpLocation->getlocation($_SERVER['REMOTE_ADDR']);
            foreach ($this->citys as $val) {
                if (!strstr($result['country'], $val['name'])) {
                    continue;
                }
                $city = $val;
                $this->city_id = $val['city_id'];
                break;
            }
            if (empty($city)) {
                $this->city_id = $this->_CONFIG['site']['city_id'];
                $city = $this->citys[$this->_CONFIG['site']['city_id']];
            }
        } else {
            $city = $this->citys[$this->city_id];
        }
        $this->assign("city", $city);
        $this->assign("citys", $this->citys);
        $this->assign('city_id', $this->city_id);
        $this->areas = d("Area")->fetchAll();
        $this->assign("areas", $this->areas);
        $this->bizs = d("Business")->fetchAll();
        $this->assign("bizs", $this->bizs);
        $invite = (int) $this->_get("invite");
        if (!empty($invite)) {
            cookie("invite_id", $invite);
        }
        $tui_uid = (int) $this->_get("tui_uid");
        if ($tui_uid && ($goods_id = (int) $this->_get("goods_id"))) {
            cookie("tui", $tui_uid . "_" . $goods_id, 2592000);
        }
        $this->assign("CONFIG", $this->_CONFIG);
        $this->assign("MEMBER", $this->member);
        $this->assign("MEMBER_EX", $this->ex);
		$this->assign('ranks', D('Userrank')->fetchAll());
        $this->assign("shopcates", d("Shopcate")->fetchAll());
        $this->assign("tuancates", d("Tuancate")->fetchAll());
        $this->assign("goodscates", d("Goodscate")->fetchAll());
        $this->assign("today", TODAY);
        $this->assign("nowtime", NOW_TIME);
        $this->assign("ctl", strtolower(MODULE_NAME));
        $this->assign("act", ACTION_NAME);
        $this->assign("is_weixin", IS_WEIXIN);
		
		D('Tuanorder')->chenk_guoqi_time();//检测套餐过期时间
		
        $this->msg();
        $this->assign('profit', $profit = $this->_CONFIG['profit']['profit']);
        $this->assign('open_appoint', $open_appoint = $this->_CONFIG['operation']['appoint']);
		$this->assign('open_crowd', $open_crowd = $this->_CONFIG['operation']['crowd']);
		$this->assign('open_booking', $open_bookingt = $this->_CONFIG['operation']['booking']);
		$this->assign('open_hotels', $open_appoint = $this->_CONFIG['operation']['hotels']);
        $this->assign('open_thread', $open_tieba = $this->_CONFIG['operation']['thread']);
		$this->assign('open_pintuan', $open_tieba = $this->_CONFIG['operation']['pintuan']);
		$this->assign('open_pinche', $open_tieba = $this->_CONFIG['operation']['pinche']);
        $this->assign('open_news', $open_news = $this->_CONFIG['operation']['news']);
        $this->assign('open_life', $open_life = $this->_CONFIG['operation']['life']);
        $this->assign('open_jifen', $open_jifen = $this->_CONFIG['operation']['jifen']);
        $this->assign('open_market', $open_market = $this->_CONFIG['operation']['market']);
        $this->assign('open_running', $open_express = $this->_CONFIG['operation']['running']);
        $this->assign('open_mall', $open_mall = $this->_CONFIG['operation']['mall']);
        $this->assign('open_cloud', $open_cloud = $this->_CONFIG['operation']['cloud']);
        $this->assign('open_huodong', $open_huodong = $this->_CONFIG['operation']['huodong']);
        $this->assign('open_community', $open_community = $this->_CONFIG['operation']['community']);
        $this->assign('open_village', $open_village = $this->_CONFIG['operation']['village']);
        //查询用户通知
        $bg_time = strtotime(TODAY);
        $this->assign('msg_day', $counts['msg_day'] = (int) D('Msg')->where(array('cate_id' => 2, 'views' => 0, 'shop_id' => $this->shop_id))->count());
        $this->assign('sign_day', $sign_day = D('Usersign')->where(array('user_id' => $this->uid, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->select());
        //查询通知结束
		$this->assign('check_connect_uid', $check_connect_uid = $this->check_connect_uid($this->uid));
        $web_close = $this->_CONFIG['site']['web_close'];
        $web_close_title = $this->_CONFIG['site']['web_close_title'];
        if ($web_close == 0) {
            $this->display("public:web_close");
            die;
        }
    }
	//检测用户有没有绑定微信
    private function check_connect_uid($uid){
        $connect = D('Connect')->where(array('uid' => $uid))->find();
		if(!empty($connect)){
			return $connect;
		}else{
			return false;
		}
    }
	//自动解冻冻结金，不过用不上
	private function checkFzmoney(){
        if ($this->ex['frozen_money'] && $this->ex['is_no_frozen'] != 1 && $this->ex['frozen_date'] < NOW_TIME) {
            $this->ex['is_no_frozen'] = 1;
            if (D("Usersex")->save(array("user_id" => $this->uid, "is_no_frozen" => 1))) {
                D("Users")->addMoney($this->uid, $this->ex['frozen_money'], "冻结金额系统自动解冻了");
            }
        }
        return TRUE;
    }
    //获取通知
    protected function msg(){
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
    private function seo(){
        $seo = D('Seo')->fetchAll();
        $this->assign('seo_title', $this->_CONFIG['site']['title']);
        $this->assign('seo_keywords', $this->_CONFIG['site']['keyword']);
        $this->assign('seo_description', $this->_CONFIG['site']['description']);
    }
    private function tmplToStr($str, $datas){
        preg_match_all('/{(.*?)}/', $str, $arr);
        foreach ($arr[1] as $k => $val) {
            $v = isset($datas[$val]) ? $datas[$val] : '';
            $str = str_replace($arr[0][$k], $v, $str);
        }
        return $str;
    }
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = ''){
        $this->seo();
        parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');
    }
    private function parseTemplate($template = ''){
        $depr = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);
        $theme = $this->getTemplateTheme();
        define('NOW_PATH', BASE_PATH . '/themes/' . $theme . 'User/');
        define('THEME_PATH', BASE_PATH . '/themes/default/User/');
        define('APP_TMPL_PATH', __ROOT__ . '/themes/default/User/');
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
    private function getTemplateTheme(){
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
    protected function fengmiSuccess($message, $jumpUrl = '', $time = 3000){
        $str = '<script>';
        $str .= 'parent.success("' . $message . '",' . $time . ',\'jumpUrl("' . $jumpUrl . '")\');';
        $str .= '</script>';
        exit($str);
    }
    protected function fengmiMsg($message, $jumpUrl = '', $time = 3000){
        $str = '<script>';
        $str .= 'parent.boxmsg("' . $message . '","' . $jumpUrl . '","' . $time . '");';
        $str .= '</script>';
        exit($str);
    }
    protected function fengmiErrorJump($message, $jumpUrl = '', $time = 3000){
        $str = '<script>';
        $str .= 'parent.error("' . $message . '",' . $time . ',\'jumpUrl("' . $jumpUrl . '")\');';
        $str .= '</script>';
        exit($str);
    }
    protected function fengmiError($message, $time = 3000, $yzm = false){
        $str = '<script>';
        if ($yzm) {
            $str .= 'parent.error("' . $message . '",' . $time . ',"yzmCode()");';
        } else {
            $str .= 'parent.error("' . $message . '",' . $time . ');';
        }
        $str .= '</script>';
        exit($str);
    }
    protected function fengmiAlert($message, $url = ''){
        $str = '<script>';
        $str .= 'parent.alert("' . $message . '");';
        if (!empty($url)) {
            $str .= 'parent.location.href="' . $url . '";';
        }
        $str .= '</script>';
        exit($str);
    }
    protected function fengmiLoginSuccess(){
        //异步登录
        $str = '<script>';
        $str .= 'parent.parent.LoginSuccess();';
        $str .= '</script>';
        exit($str);
    }
    protected function ajaxLogin(){
        $str = '<script>';
        $str .= 'parent.ajaxLogin();';
        $str .= '</script>';
        exit($str);
    }
    protected function checkFields($data = array(), $fields = array()){
        foreach ($data as $k => $val) {
            if (!in_array($k, $fields)) {
                unset($data[$k]);
            }
        }
        return $data;
    }
    protected function ipToArea($_ip){
        return IpToArea($_ip);
    }
}
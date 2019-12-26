<?php
class CommonAction extends Action{
    protected $uid = 0;
    protected $member = array();
    protected $_CONFIG = array();
    protected $shop_id = 0;
    protected $shop = array();
    protected $shopcates = array();
    protected function _initialize(){

        $this->uid = getUid();
		$passport_login = U('passport/login');
        if (!empty($this->uid)) {
            $this->member = D('Users')->find($this->uid);
        }
        if (strtolower(MODULE_NAME) != 'passport' && strtolower(MODULE_NAME) != 'public') {
            if (empty($this->uid)) {
                header("Location: " . $passport_login );
                die;
            }else{
				$shopworker = D('Shopworker')->find(array("where" => array('user_id' => $this->uid)));
                $scan_shop_id = $shopworker['shop_id'];
				if (empty($shopworker)) {
                    if(!$scan_shop = D('Shop')->where(array('user_id'=>$this->uid))->find()){
                        $this->error('您还不属于任何商家的员工哦~', $passport_login );
                    }
                    $scan_shop_id = $scan_shop['shop_id'];
				}else{
                    if (empty($shopworker['status']) || $shopworker['status'] != 1) {
                        $this->error('您的员工信息还处于待通过状态，无权进行操作！', $passport_login );
                    }
                }

				$this->shop = D('Shop')->find(array("where" => array('shop_id' => $scan_shop_id, 'closed' => 0, 'audit' => 1)));

				$this->shop_id = $this->shop['shop_id'];//为了程序调用的时候
				$this->assign('SHOP', $this->shop);
				$this->assign('worker', $shopworker);//首页调用
				$this->workers = D('Shopworker')->find(array("where" => array('user_id' => $this->uid)));//为了程序调用的时候方便
				$this->assign('workers', $this->workers);//全局调用	
			}          
        }
        $this->_CONFIG = D('Setting')->fetchAll();
        define('__HOST__', 'http://' . $_SERVER['HTTP_HOST']);
        $this->assign('CONFIG', $this->_CONFIG);
        $this->assign('MEMBER', $this->member);
        $this->shopcates = D('Shopcate')->fetchAll();
        $this->assign('shopcates', $this->shopcates);
        $this->assign('ctl', strtolower(MODULE_NAME));//主要方便调用
        $this->assign('act', ACTION_NAME);
        $this->assign('today', TODAY); //兼容模版的其他写法
        $bg_time = strtotime(TODAY);
        $this->assign('msg_day', $counts['msg_day'] = (int) D('Msg')->where(array('cate_id' => 6, 'views' => 0, 'worker_id' => $this->uid, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count());
        $this->assign('nowtime', NOW_TIME);
        $is_weixin = is_weixin();
        $this->assign('is_weixin', $is_weixin);
    }
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = ''){
        parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');
    }
    private function parseTemplate($template = ''){
        $depr = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);
        $theme = $this->getTemplateTheme();
        define('NOW_PATH', BASE_PATH . '/themes/' . $theme . 'Worker/');
        define('THEME_PATH', BASE_PATH . '/themes/default/Worker/');
        define('APP_TMPL_PATH', __ROOT__ . '/themes/default/Worker/');
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
            $themes = D('Template')->fetchAll();
            if (C('TMPL_DETECT_THEME')) {
                $t = C('VAR_TEMPLATE');
                if (isset($_GET[$t])) {
                    $theme = $_GET[$t];
                } elseif (cookie('think_template')) {
                    $theme = cookie('think_template');
                }
                if (!isset($themes[$theme])) {
                    $theme = C('DEFAULT_THEME');
                }
                cookie('think_template', $theme, 864000);
            }
        }
        return $theme ? $theme . '/' : '';
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
    protected function fengmiAlert($message, $url = ''){
        $str = '<script>';
        $str .= 'parent.alert("' . $message . '");';
        if (!empty($url)) {
            $str .= 'parent.location.href="' . $url . '";';
        }
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
    protected function fengmiLoginSuccess(){
        $str = '<script>';
        $str .= 'parent.parent.LoginSuccess();';
        $str .= '</script>';
        exit($str);
    }
}
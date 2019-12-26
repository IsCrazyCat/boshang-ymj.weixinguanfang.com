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
        if (!empty($this->uid)) {
            $this->member = D('Users')->find($this->uid);
        }
        if (strtolower(MODULE_NAME) != 'passport' && strtolower(MODULE_NAME) != 'public') {
            if (empty($this->uid)) {
                header("Location: " . U('passport/login'));
                die;
            }
            $this->shop = D('Shop')->find(array("where" => array('user_id' => $this->uid, 'closed' => 0, 'audit' => 1)));
            if (empty($this->shop)) {
                $this->error('该用户没有开通商户', U('passport/login'));
            }
            $this->shop_id = $this->shop['shop_id'];
            $this->assign('SHOP', $this->shop);
        }
        $this->_CONFIG = D('Setting')->fetchAll();
        define('__HOST__', 'http://' . $_SERVER['HTTP_HOST']);
        $this->assign('CONFIG', $this->_CONFIG);
        $this->assign('MEMBER', $this->member);
        $this->shopcates = D('Shopcate')->fetchAll();
        $this->assign('shopcates', $this->shopcates);
        $this->assign('ctl', strtolower(MODULE_NAME));
        //主要方便调用
		
		$Shopgrade = D('Shopgrade')->where(array('grade_id'=>$this->shop['grade_id']))->find();
        $this->assign('SHOPGRADE', $Shopgrade);
		$this->grade_id = $Shopgrade['shop_id'];//方便程序调用这段可以不要
				
        $this->assign('act', ACTION_NAME);
        $this->assign('open_lifeservice', $open_lifeservice = $this->_CONFIG['operation']['lifeservice']);
        $this->assign('open_life', $open_life = $this->_CONFIG['operation']['life']);
        $this->assign('open_ding', $open_ding = $this->_CONFIG['operation']['ding']);
        $this->assign('open_mall', $open_mall = $this->_CONFIG['operation']['mall']);
        $this->assign('today', TODAY);
        //兼容模版的其他写法
        $this->assign('nowtime', NOW_TIME);
        $bg_time = strtotime(TODAY);
        $this->assign('msg_day', $counts['msg_day'] = (int) D('Msg')->where(array('cate_id' => 2, 'views' => 0, 'shop_id' => $this->shop_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count());
    }
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = ''){
        parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');
    }
    private function parseTemplate($template = ''){
        $depr = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);
        $theme = $this->getTemplateTheme();
        define('NOW_PATH', BASE_PATH . '/themes/' . $theme . 'Distributors/');
        define('THEME_PATH', BASE_PATH . '/themes/default/Distributors/');
        define('APP_TMPL_PATH', __ROOT__ . '/themes/default/Distributors/');
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
    ///开始
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
        //异步登录
        $str = '<script>';
        $str .= 'parent.parent.LoginSuccess();';
        $str .= '</script>';
        exit($str);
    }
}
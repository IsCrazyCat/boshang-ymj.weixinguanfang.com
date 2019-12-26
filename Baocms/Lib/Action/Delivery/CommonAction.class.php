<?php
class CommonAction extends Action{
    protected $uid = 0;
    protected $member = array();
    protected $_CONFIG = array();
	protected $citys = array();
	protected $city_id = 0;
    protected $city = array();
    protected $shop_id = 0;
    protected $shop = array();
    protected $shopcates = array();
    protected function _initialize() {
        $this->citys = D('City')->fetchAll();
        $this->assign('citys', $this->citys);
        $this->city_id = cookie('city_id');
        $this->assign('city_id', $this->city_id);
		
		
        $this->uid = getUid();
        if (!empty($this->uid)) {
            $this->member = D('Users')->find($this->uid);
        }
        if (strtolower(MODULE_NAME) != 'passport' && strtolower(MODULE_NAME) != 'public') {
            if (empty($this->uid)) {
                header("Location: " . U('passport/login'));
                die;
            }
            $user_delivery = D('Delivery')->where(array('user_id' => $this->uid))->find();
			$login_url = U('passport/login');
            if (empty($user_delivery)) {
                $this->error('您无权管理', $login_url);
            }
            if ($user_delivery['audit'] != 1) {
                $this->error('还没有审核', $login_url);
            }elseif($user_delivery['closed'] != 0){
				$this->error('账户不存在', $login_url);
			}
			$users = D('Users')->where(array('user_id' => $this->uid))->find();
			if ($users['is_lock'] == 1) {
                $this->error('您的账户不安全', $login_url);
            }
            $this->delivery_id = $user_delivery['user_id'];//PHP调用
            $this->assign('user_delivery', $user_delivery);//模板调用
        }
        $this->_CONFIG = D('Setting')->fetchAll();
        define('__HOST__', 'http://' . $_SERVER['HTTP_HOST']);
        $this->assign('CONFIG', $this->_CONFIG);
        $this->assign('MEMBER', $this->member);
        $this->assign('ctl', strtolower(MODULE_NAME));
        $this->assign('act', ACTION_NAME);
        $this->assign('today', TODAY);
		
		$bg_time = strtotime(TODAY);
        $this->assign('msg_day', $counts['msg_day'] = (int) D('Msg')->where(array('cate_id' => 5, 'views' => 0, 'worker_id' => $this->uid, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count());
		
		
		$this->assign('open_running',$open_running = $this->_CONFIG['operation']['running']); //快递
        $this->assign('nowtime', NOW_TIME);
        $is_weixin = is_weixin();
        $this->assign('is_weixin', $is_weixin);
    }
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '')
    {
        parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');
    }
    private function parseTemplate($template = ''){
        $depr = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);
        $theme = $this->getTemplateTheme();
        define('NOW_PATH', BASE_PATH . '/themes/' . $theme . 'Delivery/');
        define('THEME_PATH', BASE_PATH . '/themes/default/Delivery/');
        define('APP_TMPL_PATH', __ROOT__ . '/themes/default/Delivery/');
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
<?php
class CommonAction extends Action
{
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
        if (strtolower(MODULE_NAME) != 'login' && strtolower(MODULE_NAME) != 'public') {
            //public 不受权限控制
            if (empty($this->uid)) {
                header("Location: " . U('login/index'));
                die;
            }
            $this->shop = D('Shop')->find(array("where" => array('user_id' => $this->uid, 'closed' => 0, 'audit' => 1)));
            if (empty($this->shop)) {
                $this->error('该用户没有开通商户', U('login/index'));
            }
            $this->shop_id = $this->shop['shop_id'];
            //为了程序调用的时候方便
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
        $this->assign('act', ACTION_NAME);
        $this->assign('today', TODAY);
        $this->assign('nowtime', NOW_TIME);
        $waimai = $this->ele = D('Ele')->find($this->shop_id);
        $this->assign('waimai', $waimai);
        $ding = D('Shop')->find($this->shop_id);
        $this->assign('ding', $ding);
        $wd = D('WeidianDetails')->where('shop_id =' . $this->shop_id)->find();
        $this->assign('wd', $wd);
		
		$Shopgrade = D('Shopgrade')->where(array('grade_id'=>$this->shop['grade_id'],'closed'=>0))->find();
        $this->assign('SHOPGRADE', $Shopgrade);
		$this->grade_id = $Shopgrade['shop_id'];//方便程序调用这段可以不要
		
		
        $this->assign('open_appoint', $open_appoint = $this->_CONFIG['operation']['appoint']);
        $this->assign('open_life', $open_life = $this->_CONFIG['operation']['life']);
        $this->assign('open_booking', $open_booking = $this->_CONFIG['operation']['booking']);
		$this->assign('open_appoint', $open_appoint = $this->_CONFIG['operation']['appoint']);
		$this->assign('open_hotels', $open_appoint = $this->_CONFIG['operation']['hotels']);
		$this->assign('open_farm', $open_appoint = $this->_CONFIG['operation']['farm']);
        $this->assign('open_mall', $open_mall = $this->_CONFIG['operation']['mall']);
		$this->assign('open_huodong', $open_huodong = $this->_CONFIG['operation']['huodong']);
		$this->assign('open_cloud', $open_cloud = $this->_CONFIG['operation']['cloud']);
		
        $this->assign('msg_day', $counts['msg_day'] = (int) D('Msg')->where(array('cate_id' => 2, 'views' => 0, 'shop_id' => $this->shop_id))->count());
    }
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = ''){
        parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');
    }
    private function parseTemplate($template = ''){
        $depr = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);
        $theme = $this->getTemplateTheme();
        define('NOW_PATH', BASE_PATH . '/themes/' . $theme . 'Merchant/');
        define('THEME_PATH', BASE_PATH . '/themes/default/Merchant/');
        define('APP_TMPL_PATH', __ROOT__ . '/themes/default/Merchant/');
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
    protected function baoMsg($message, $jumpUrl = '', $time = 3000, $callback = '', $parent = true)
    {
        $parents = $parent ? 'parent.' : '';
        $str = '<script>';
        $str .= $parents . 'bmsg("' . $message . '","' . $jumpUrl . '","' . $time . '","' . $callback . '");';
        $str .= '</script>';
        exit($str);
    }
    protected function baoOpen($message, $close = true, $style)
    {
        $str = '<script>';
        $str .= 'parent.bopen("' . $message . '","' . $close . '","' . $style . '");';
        $str .= '</script>';
        exit($str);
    }
    protected function baoSuccess($message, $jumpUrl = '', $time = 3000, $parent = true)
    {
        $this->baoMsg($message, $jumpUrl, $time, '', $parent);
    }
    protected function baoErrorJump($message, $jumpUrl = '', $time = 3000)
    {
        $this->baoMsg($message, $jumpUrl, $time);
    }
    protected function baoError($message, $time = 3000, $yzm = false, $parent = true)
    {
        $parent = $parent ? 'parent.' : '';
        $str = '<script>';
        if ($yzm) {
            $str .= $parent . 'bmsg("' . $message . '","",' . $time . ',"yzmCode()");';
        } else {
            $str .= $parent . 'bmsg("' . $message . '","",' . $time . ');';
        }
        $str .= '</script>';
        exit($str);
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
}
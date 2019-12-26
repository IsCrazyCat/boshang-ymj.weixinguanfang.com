<?php
class CommonAction extends BaseAction{
    protected $uid = 0;
    protected $token = '';
    protected $_CONFIG = array();
    protected $member = array();//缓存用
    protected $citys = array();
    protected $areas = array();
    protected $bizs = array();
    protected $shopcates = array();
    protected $tuancates = array();
    protected $goodscates = array();
    protected $session = '';
    protected $city_id = 0;
    protected $city = array();
    protected $lat = 0;
    protected $lng = 0;
    public function _initialize(){
        $this->_CONFIG = D('Setting')->fetchAll();
        $this->lat = addslashes($this->_param('lat'));
        $this->lng = addslashes($this->_param('lng'));
        $this->uid = $this->_get('uid');
        if ($this->uid > 0) {
            $this->member = D('Users')->find($this->uid);
        }
        //客户端TOKEN比对
        $token = $this->_get('user_token');
        if (!empty($token)) {
            if ($token != $this->member['token']) {
                $this->token = '';
                $this->uid = '';
            } else {
                $this->token = $token;
            }
        } else {
            $this->uid = '';
        }
        $this->city_id = $this->_get('city_id');
        if (empty($this->city_id)) {
            import('ORG/Net/IpLocation');
            $IpLocation = new IpLocation('UTFWry.dat');
            // 实例化类 参数表示IP地址库文件
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
        $this->shopcates = D('Shopcate')->fetchAll();
        $this->tuancates = D('Tuancate')->fetchAll();
        $this->goodscates = D('Goodscate')->fetchAll();
        $this->areas = D('Area')->fetchAll();
        $this->bizs = D('Business')->fetchAll();
    }
    //增加模板开始的
    private function seo()
    {
        $seo = D('Seo')->fetchAll();
        $this->assign('seo_title', $this->_CONFIG['site']['title']);
        $this->assign('seo_keywords', $this->_CONFIG['site']['keyword']);
        $this->assign('seo_description', $this->_CONFIG['site']['description']);
    }
    public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '')
    {
        $this->seo();
        parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');
    }
    private function parseTemplate($template = ''){
        $depr = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);
        $theme = $this->getTemplateTheme();
        define('NOW_PATH', BASE_PATH . '/themes/' . $theme . 'App/');
        define('THEME_PATH', BASE_PATH . '/themes/default/App/');
        define('APP_TMPL_PATH', __ROOT__ . '/themes/default/App/');
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
    public function verify(){
        import('ORG.Util.Image');
        Image::buildImageVerify(4, 2, 'png', 60, 30);
    }
    public function gps($shop_id){
        $shop_id = (int) $shop_id;
        if (empty($shop_id)) {
            $this->error('该商家不存在');
            $this->stringify(array('status' => self::BAO_DETAIL_NO_EXSITS, 'msg' => '数据不存在！'));
        }
        $shop = D('Shop')->find($shop_id);
        $this->stringify(array('status' => self::BAO_REQUEST_SUCCESS, 'shop' => $shop));
    }
    protected function checkFields($data = array(), $fields = array()){
        foreach ($data as $k => $val) {
            if (!in_array($k, $fields)) {
                unset($data[$k]);
            }
        }
        return $data;
    }
    //开始
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
}
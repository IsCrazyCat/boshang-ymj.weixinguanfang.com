<?php
class CommonAction extends Action
{
    protected $_admin = array();
    protected $_CONFIG = array();
    protected function _initialize()
    {
        $this->_admin = session('admin');
        if (strtolower(MODULE_NAME) != 'login' && strtolower(MODULE_NAME) != 'public') {
            if (empty($this->_admin)) {
                header('Location: ' . u('login/index'));
                die;
            }
            if (strstr($this->_admin['username'], 'demo')) {
                if ($this->isPost()) {
                    $this->baoError('演示站不提供数据操作!');
                }
                if (strtolower(ACTION_NAME) == 'delete') {
                    $this->baoError('演示站不能删除数据！');
                }
                if (strtolower(ACTION_NAME) == 'audit') {
                    $this->baoError('演示站不能审核数据');
                }
                if (strtolower(ACTION_NAME) == 'uninstall') {
                    $this->baoError('演示站不能卸载数据');
                }
            }
            //演示账号不能操作结束
            if ($this->_admin['role_id'] != 1) {
                $this->_admin['menu_list'] = d('RoleMaps')->getMenuIdsByRoleId($this->_admin['role_id']);
                if (strtolower(MODULE_NAME) != 'index') {
                    $menu_action = strtolower(MODULE_NAME . '/' . ACTION_NAME);
                    $menu = d('Menu')->fetchAll();
                    $menu_id = 0;
                    foreach ($menu as $k => $v) {
                        if ($v['menu_action'] == $menu_action) {
                            $menu_id = (int) $k;
                            break;
                        }
                    }
                    if (empty($menu_id) || !isset($this->_admin['menu_list'][$menu_id])) {
                        $this->error('很抱歉您没有权限操作模块:' . $menu[$menu_id]['menu_name']);
                    }
                }
            }
            $admin_user_name = D('Admin')->find($this->_admin['admin_id']);
            if ($admin_user_name['role_id'] == 2) {
                session('admin', null);
                $this->error('请不要非法操作', U('login/index'));
            } elseif ($admin_user_name['is_username_lock'] == 1) {
                session('admin', null);
                $this->error('您的账户已被冻结', U('login/index'));
            }
        }
        $this->_CONFIG = d('Setting')->fetchAll();
        define('__HOST__', 'http://' . $_SERVER['HTTP_HOST']);
        $this->assign('CONFIG', $this->_CONFIG);
        $this->assign('admin', $this->_admin);
        $this->assign('today', TODAY);
        $this->assign('nowtime', NOW_TIME);
		$this->assign("ctl", strtolower(MODULE_NAME));
        $this->assign("act", ACTION_NAME);
    }
    protected function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '')
    {
        parent::display($this->parseTemplate($templateFile), $charset, $contentType, $content = '', $prefix = '');
    }
	
	
	 private function parseTemplate($template = ''){
        $depr = C('TMPL_FILE_DEPR');
        $template = str_replace(':', $depr, $template);
        $theme = $this->getTemplateTheme();
        define('NOW_PATH', BASE_PATH . '/themes/' . $theme . 'Admin/');
        define('THEME_PATH', BASE_PATH . '/themes/default/Admin/');
        define('APP_TMPL_PATH', __ROOT__ . '/themes/default/Admin/');
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
            $default = D('Template')->getDefaultTheme();
            $themes = D('Template')->fetchAll();
            if (C('TMPL_DETECT_THEME')) {
                $t = C('VAR_TEMPLATE');
                if (isset($_GET[$t])) {
                    $theme = $_GET[$t];
                    cookie('think_template', $theme, 864000);
                } else {
                    if (!empty($this->city['theme'])) {
                        $theme = $this->city['theme'];
                    } else {
                        if (cookie('think_template')) {
                            $theme = cookie('think_template');
                        }
                    }
                }
                if (!isset($themes[$theme])) {
                    $theme = $default;
                }
            } else {
                $theme = $default;
            }
        }
        return $theme ? $theme . '/' : '';
    }
	

    protected function baoSuccess($message, $jumpUrl = '', $time = 3000){
        $str = '<script>';
        $str .= 'parent.success("' . $message . '",' . $time . ',\'jumpUrl("' . $jumpUrl . '")\');';
        $str .= '</script>';
        die($str);
    }
    protected function baoError($message, $time = 3000, $yzm = false)
    {
        $str = '<script>';
        if ($yzm) {
            $str .= 'parent.error("' . $message . '",' . $time . ',"yzmCode()");';
        } else {
            $str .= 'parent.error("' . $message . '",' . $time . ');';
        }
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
        return iptoarea($_ip);
    }
}
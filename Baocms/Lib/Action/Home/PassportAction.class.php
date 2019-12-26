<?php
class PassportAction extends CommonAction
{
    private $create_fields = array('account', 'password', 'nickname');
    public function _initialize()
    {
        parent::_initialize();
        $this->register_ip = get_client_ip();
        $this->assign('Registerip', $Registerip = D('Registerip')->where(array('ip' => $this->register_ip))->count());
    }
    public function register(){
        if ($this->isPost()) {
            if (isMobile(htmlspecialchars($_POST['data']['account']))) {
                if (!($scode = trim($_POST['scode']))) {
                    $this->baoError('请输入短信验证码！');
                }
                $scode2 = session('scode');
                if (empty($scode2)) {
                    $this->baoError('请获取短信验证码！');
                }
                if ($scode != $scode2) {
                    $this->baoError('请输入正确的短信验证码！');
                }
            } else {
                $yzm = $this->_post('yzm');
                //是否开启显示验证码1，开启，0关闭
                $register_yzm = $this->_CONFIG['register']['register_yzm'];
                if ($register_yzm == 1) {
                    if (strtolower($yzm) != strtolower(session('verify'))) {
                        session('verify', null);
                        $this->baoError('验证码不正确!', 2000, true);
                    }
                }
            }
            $data = $this->createCheck();
            $is_agree = $this->_post('is_agree');
            if (!$is_agree) {
                $this->baoError('请同意注册条约!', 2000, true);
            }
            $password2 = $this->_post('password2');
            if ($password2 !== $data['password']) {
                session('verify', null);
                $this->baoError('两次密码不一致', 3000, true);
            }
            //开始其他的判断了
            if (D('Passport')->register($data)) {
                D('Registerip')->delete_register_ip($this->register_ip);
                //删除IP
                $this->baoSuccess('恭喜您，注册成功！', U('index/index'));
            }
            $this->baoError(D('Passport')->getError(), 3000, true);
        } else {
            //判断是不是注册会员获取cookie开启
            $fuid = (int) cookie('fuid');
            if ($fuid) {
                $profit_min_rank_id = (int) $this->_CONFIG['profit']['profit_min_rank_id'];
                $fuser = D('Users')->find($fuid);
                if ($fuser) {
                    $flag = false;
                    if ($profit_min_rank_id) {
                        $modelRank = D('Userrank');
                        $rank = $modelRank->find($profit_min_rank_id);
                        $userRank = $modelRank->find($fuser['rank_id']);
                        if ($rank) {
                            if ($userRank && $userRank['prestige'] >= $rank['prestige']) {
                                $flag = true;
                            } else {
                                $flag = false;
                            }
                        } else {
                            $flag = false;
                        }
                    } else {
                        $flag = true;
                    }
                    $fuser['nickname'] = empty($fuser['nickname']) ? $fuser['account'] : $fuser['nickname'];
                    if ($flag) {
                        $this->assign('fuser', $fuser);
                    }
                }
            }
            //结束
            if (!empty($this->uid)) {
                $this->success('您已经是我们的会员，不需要重复注册！', U('members/index'));
            } else {
                $this->display();
            }
        }
    }
    public function sendsms()
    {
        $obj = D('Registerip');
        $obj->add_register_ip($this->register_ip);
        //写入数据库
        if (!($mobile = htmlspecialchars($_POST['mobile']))) {
            die('请输入正确的手机号码');
        }
        if (!isMobile($mobile)) {
            die('请输入正确的手机号码');
        }
        if ($user = D('Users')->getUserByMobile($mobile)) {
            die('手机号码已经存在！');

        }
        $register = $obj->where(array('ip' => $this->register_ip))->order(array('create_time' => 'desc'))->find();
        if ($register['is_lock'] == 1) {
            $var = D('Registerip')->check_register_time($this->register_ip);
            //检测IP
            if (empty($var)) {
                $this->baoSuccess('请新休息一下再注册', U('passport/register'));
            } else {
                $sms_yzm = htmlspecialchars($_POST['sms_yzm']);
                if (strtolower($sms_yzm) != strtolower(session('verify'))) {
                    session('verify', null);
                    $this->baoSuccess('请输入正确的验证码后获取手机短信，正在为您跳转', U('passport/register'));
                }
            }
        }
        $randstring = session('scode');
        if (empty($randstring)) {
            $randstring = rand_string(6, 1);
            session('scode', $randstring);
        }
        if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
            D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_yzm', $mobile, array('sitename' => $this->_CONFIG['site']['sitename'], 'code' => $randstring));
        } else {
            D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
        }
        die('1');
    }
    public function logout()
    {
        D('Passport')->logout();
        $this->success('退出登录成功！', U('index/index'));
    }
    public function login()
    {
        if ($this->isPost()) {
            $yzm = $this->_post('yzm');
            if (strtolower($yzm) != strtolower(session('verify'))) {
                session('verify', null);
                $this->baoError('验证码不正确!', 2000, true);
            }
            $account = $this->_post('account');
            if (empty($account)) {
                session('verify', null);
                $this->baoError('请输入用户名!', 2000, true);
            }
            $password = $this->_post('password');
            if (empty($password)) {
                session('verify', null);
                $this->baoError('请输入登录密码!', 2000, true);
            }
            $backurl = $this->_post('backurl', 'htmlspecialchars');
            if (empty($backurl)) {
                $backurl = U('members/index');
            }
            if (true == D('Passport')->login($account, $password)) {
                $this->baoSuccess('恭喜您登录成功！', $backurl);
            }
            $this->baoError(D('Passport')->getError(), 3000, true);
        } else {
            if (!empty($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) && !strstr($_SERVER['HTTP_REFERER'], 'passport')) {
                $backurl = $_SERVER['HTTP_REFERER'];
            } else {
                $backurl = U('members/index');
            }
            $this->assign('backurl', $backurl);
            if (!empty($this->uid)) {
                $this->success('您已经是我们的会员，不需要重复注册！', U('members/index'));
            } else {
                echo session('verify', null);
                $this->display();
            }
        }
    }
    public function bind()
    {
        $connect = session('connect');
        $this->assign('connect', D('Connect')->find($connect));
        $this->assign('types', array('qq' => '腾讯QQ', 'weixin' => '微信', 'weibo' => '微博'));
        $this->display();
    }
    public function login2()
    {
        //这里修改一下
        if ($this->isPost()) {
            $yzm = $this->_post('yzm');
            if (strtolower($yzm) != strtolower(session('verify'))) {
                session('verify', null);
                $this->baoError('验证码不正确!', 2000, true);
            }
            $account = $this->_post('account');
            if (empty($account)) {
                session('verify', null);
                $this->baoError('请输入用户名!', 2000, true);
            }
            $password = $this->_post('password');
            if (empty($password)) {
                session('verify', null);
                $this->baoError('请输入登录密码!', 2000, true);
            }
            if (true == D('Passport')->login($account, $password)) {
                $this->baoLoginSuccess();
            }
            $this->baoError(D('Passport')->getError(), 3000, true);
        } else {
            $this->display();
        }
    }
    public function login3()
    {
        //这里修改一下
        if ($this->isPost()) {
            $yzm = $this->_post('yzm');
            if (strtolower($yzm) != strtolower(session('verify'))) {
                session('verify', null);
                $this->fengmiError('验证码不正确!', 2000, true);
            }
            $account = $this->_post('account');
            if (empty($account)) {
                session('verify', null);
                $this->fengmiError('请输入用户名!', 2000, true);
            }
            $password = $this->_post('password');
            if (empty($password)) {
                session('verify', null);
                $this->fengmiError('请输入登录密码!', 2000, true);
            }
            if (true == D('Passport')->login($account, $password)) {
                $this->fengmiLoginSuccess();
            }
            $this->fengmiError(D('Passport')->getError(), 3000, true);
        } else {
            $this->display();
        }
    }
    public function login4()
    {
        if ($this->isPost()) {
            $account = $this->_post('account');
            if (empty($account)) {
                session('verify', null);
                $this->fengmiError('请输入用户名!', 2000, true);
            }
            $password = $this->_post('password');
            if (empty($password)) {
                session('verify', null);
                $this->fengmiError('请输入登录密码!', 2000, true);
            }
            if (true == D('Passport')->login($account, $password)) {
                $this->ajaxLoginSuccess();
            }
            $this->fengmiError(D('Passport')->getError(), 3000, true);
        } else {
            $this->display();
        }
    }
    public function check(){
        $this->display();
    }
    public function ajaxloging(){
        $this->display();
    }
    public function ajaxloging1(){
        $this->display();
    }
    public function ajaxloging2(){
        $this->display();
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['account'] = htmlspecialchars($_POST['data']['account']);
        if (!isMobile($data['account']) && !isEmail($data['account'])) {
            session('verify', null);
            $this->baoError('用户名只允许手机号码或者邮件2!', 2000, true);
        }
        $data['password'] = htmlspecialchars($data['password']);
        //整合UC的时候需要
        $register_password = $this->_CONFIG['register']['register_password'];
        if (empty($data['password']) || strlen($data['password']) < $register_password) {
            session('verify', null);
            $this->baoError('请输入正确的密码!密码长度必须要在' . $register_password . '个字符以上', 2000, true);
        }
        $data['nickname'] = $data['account'];
        if (isEmail($data['account'])) { //如果邮件的@前面超过15就不好了
            $local = explode('@', $data['account']);
            $data['ext0'] = $local[0];
        } else {
            $data['mobile'] = $data['account'];//绑定手机号
            $data['ext0'] = $data['account'];
        }
        $data['reg_ip'] = get_client_ip();
        $data['reg_time'] = NOW_TIME;
        return $data;
    }
    //两种找回密码的方式 1个是通过邮件 //填写了2个就改密码相对来说是不太合理，但是毕竟逻辑和操作相对简单一些！
    public function forget(){
        $way = (int) $this->_param('way');
        $this->assign('way', $way);
        $this->display();
    }
    public function findsms(){
        if (!($mobile = htmlspecialchars($_POST['mobile']))) {
            die('请输入正确的手机号码');
        }
        if (!isMobile($mobile)) {
            die('请输入正确的手机号码');
        }
        if (!($account = htmlspecialchars($_POST['account']))) {
            die('请填写账号');
        }
        if ($user = D('Users')->getUserByAccount($account)) {
            if (empty($user['mobile'])) {
                die('你还未绑定手机号，请选择其他方式！');
            } else {
                if ($user['mobile'] != $mobile) {
                    die('您输入的手机号有误，请重新填写');
                }
            }
        }
        if (empty($user)) {
            die('没有找到此账户');
        }
        $randstring = session('scode');
        if (empty($randstring)) {
            $randstring = rand_string(6, 1);
            session('scode', $randstring);
        }
        //大鱼短信
        if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
            D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_yzm', $mobile, array(
				'sitename' => $this->_CONFIG['site']['sitename'], 
				'code' => $randstring
			));
        } else {
            D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
        }
        die('1');
    }
    public function newpwd()
    {
        $account = $this->_post('account');
        if (empty($account)) {
            session('verify', null);
            $this->baoError('请输入用户名!');
        }
        $user = D('Users')->getUserByAccount($account);
        if (empty($user)) {
            session('verify', null);
            $this->baoError('用户不存在!');
        }
        $way = (int) $this->_post('way');
        $password = rand_string(8, 1);
        if ($way == 1) {
            $yzm = $this->_post('yzm');
            if (strtolower($yzm) != strtolower(session('verify'))) {
                session('verify', null);
                $this->baoError('验证码不正确!');
            }
            $email = $this->_post('email');
            if (empty($email) || $email != $user['email']) {
                $this->baoError('邮件不正确');
            }
            D('Passport')->uppwd($user['account'], '', $password);
            D('Email')->sendMail('email_newpwd', $email, '重置密码', array('newpwd' => $password));
        } elseif ($way == 2) {
            $mobile = htmlspecialchars($_POST['mobile']);
            if (!($scode = trim($_POST['scode']))) {
                $this->baoError('请输入短信验证码！');
            }
            $scode2 = session('scode');
            if (empty($scode2)) {
                $this->baoError('请获取短信验证码！');
            }
            if ($scode != $scode2) {
                $this->baoError('请输入正确的短信验证码！');
            }
            D('Passport')->uppwd($user['account'], '', $password);
            if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_user_newpwd', $mobile, array(
					'sitename' => $this->_CONFIG['site']['sitename'], 
					'newpwd' => $password
				));
            } else {
                D('Sms')->sendSms('sms_newpwd', $mobile, array(
					'newpwd' => $password
				));
            }
        }
        $this->baoSuccess('重置密码成功！', U('passport/suc', array('way' => $way)));
    }
    public function suc(){
        $way = (int) $this->_get('way');
        switch ($way) {
            case 1:
                $this->success('密码重置成功！请登录邮箱查看！', U('passport/login'));
                break;
            default:
                $this->success('密码重置成功！请查看验证手机！', U('passport/login'));
                break;
        }
    }
    public function wblogin()
    {
        $login_url = 'https://api.weibo.com/oauth2/authorize?client_id=' . $this->_CONFIG['connect']['wb_app_id'] . '&response_type=code&redirect_uri=' . urlencode(__HOST__ . U('passport/wbcallback'));
        header("Location:{$login_url}");
        die;
    }
    public function qqlogin()
    {
        $state = md5(uniqid(rand(), TRUE));
        session('state', $state);
        $login_url = 'https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=' . $this->_CONFIG['connect']['qq_app_id'] . '&redirect_uri=' . urlencode(__HOST__ . U('passport/qqcallback')) . '&state=' . $state . '&scope=';
        header("Location:{$login_url}");
        die;
    }
    public function wxlogin()
    {
        $state = md5(uniqid(rand(), TRUE));
        cookie('wx_state', $state);
        $data = array();
        $data['state'] = $state;
        $data['status'] = 0;
        $data['user_id'] = 0;
        $data['create_time'] = NOW_TIME;
        $send = D('Weixinconn')->add($data);
        $url = $this->_CONFIG['site']['host'] . '/wap/passport/weixincheck/state/' . $state . '.html';
        $this->assign('url', $url);
        $this->assign('state', $state);
        $this->display();
    }
    public function weixincheck()
    {
        $state = $this->_param('state');
        $wxconn = D('Weixinconn')->where(array('state' => $state))->find();
        if ($wxconn) {
            if (intval($wxconn['user_id']) > 0) {
                echo $wxconn['user_id'];
            } else {
                echo '0';
            }
        } else {
            echo '0';
        }
    }
    public function wxscaned()
    {
        $state = $this->_param('state');
        $user_id = $this->_param('user');
        setuid($user_id);
        header('Location:' . U('members/index'));
    }
    public function wbcallback()
    {
        import('@/Net.Curl');
        $curl = new Curl();
        $params = array('grant_type' => 'authorization_code', 'code' => $_REQUEST['code'], 'client_id' => $this->_CONFIG['connect']['wb_app_id'], 'client_secret' => $this->_CONFIG['connect']['wb_app_key'], 'redirect_uri' => __HOST__ . U('passport/qqcallback'));
        $url = 'https://api.weibo.com/oauth2/access_token';
        $response = $curl->post($url, http_build_query($params));
        $params = json_decode($response, true);
        if (isset($params['error'])) {
            echo '<h3>error:</h3>' . $params['error'];
            echo '<h3>msg  :</h3>' . $params['error_code'];
            die;
        }
        $url = 'https://api.weibo.com/2/account/get_uid.json?source=' . $this->_CONFIG['connect']['wb_app_key'] . '&access_token=' . $params['access_token'];
        $result = $curl->get($url);
        $user = json_decode($result, true);
        if (isset($user['error'])) {
            echo '<h3>error:</h3>' . $user['error'];
            echo '<h3>msg  :</h3>' . $user['error_code'];
            die;
        }
        $data = array('type' => 'weibo', 'open_id' => $user['uid'], 'token' => $params['access_token']);
        $this->thirdlogin($data);
    }
    public function wxcallback()
    {
        if ($_REQUEST['state'] == session('state')) {
            import('@/Net.Curl');
            $curl = new Curl();
            if (empty($_REQUEST['code'])) {
                $this->error('授权后才能登陆！', U('passport/login'));
            }
            $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->_CONFIG['connect']['wx_app_id'] . '&secret=' . $this->_CONFIG['connect']['wx_app_key'] . '&code=' . $_REQUEST['code'] . '&grant_type=authorization_code';
            $str = $curl->get($token_url);
            $params = json_decode($str, true);
            if (!empty($params['errcode'])) {
                echo '<h3>error:</h3>' . $params['errcode'];
                echo '<h3>msg  :</h3>' . $params['errmsg'];
                die;
            }
            if (empty($params['openid'])) {
                $this->error('登录失败', U('passport/login'));
            }
            $data = array('type' => 'weixin', 'open_id' => $params['openid'], 'token' => $params['refresh_token']);
            $this->thirdlogin($data);
        }
    }
    public function qqcallback()
    {
        import('@/Net.Curl');
        $curl = new Curl();
        if ($_REQUEST['state'] == session('state')) {
            $token_url = 'https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&' . 'client_id=' . $this->_CONFIG['connect']['qq_app_id'] . '&redirect_uri=' . urlencode(__HOST__ . U('passport/qqcallback')) . '&client_secret=' . $this->_CONFIG['connect']['qq_app_key'] . '&code=' . $_REQUEST['code'];
            $response = $curl->get($token_url);
            if (strpos($response, 'callback') !== false) {
                $lpos = strpos($response, '(');
                $rpos = strrpos($response, ')');
                $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
                $msg = json_decode($response);
                echo '<h3>error:</h3>' . $msg->error;
                echo '<h3>msg  :</h3>' . $msg->error_description;
                die;
            }
            $params = array();
            parse_str($response, $params);
            if (empty($params)) {
                die;
            }
            $graph_url = 'https://graph.qq.com/oauth2.0/me?access_token=' . $params['access_token'];
            $str = $curl->get($graph_url);
            if (strpos($str, 'callback') !== false) {
                $lpos = strpos($str, '(');
                $rpos = strrpos($str, ')');
                $str = substr($str, $lpos + 1, $rpos - $lpos - 1);
            }
            $user = json_decode($str, true);
            if (isset($user['error'])) {
                echo '<h3>error:</h3>' . $user['error'];
                echo '<h3>msg  :</h3>' . $user['error_description'];
                die;
            }
            if (empty($user['openid'])) {
                die;
            }
            $data = array('type' => 'qq', 'open_id' => $user['openid'], 'client_id' => $user['client_id'], 'token' => $params['access_token']);
            $this->thirdlogin($data);
        }
    }
    private function thirdlogin($data)
    {
        if ($this->_CONFIG['connect']['debug']) {
            $connect = D('Connect')->getConnectByOpenid($data['type'], $data['open_id']);
            if (empty($connect)) {
                $connect = $data;
                $connect['connect_id'] = D('Connect')->add($data);
            } else {
                D('Connect')->save(array('connect_id' => $connect['connect_id'], 'token' => $data['token']));
            }
            if ($data['type'] == 'qq') {
                $user_info = D('Connect')->user_info($data['client_id'], $data['open_id'], $data['token']);//这里可能有问题，缺少user_info函数
                $nickname = $user_info['nickname'];//获取昵称
                $face = $user_info['figureurl_qq_2'] == '' ? $user_info['figureurl_qq_1'] : $user_info['figureurl_qq_2'];
            } else {
                $nickname = 'cdaxue' . rand(100000, 999999) . $connect['connect_id'];
            }
            //二开结束
            if (empty($connect['uid'])) {
                $account = $data['type'] . rand(100000, 999999) . '@qq.com';
                //p($face);die;
                $user = array(
					'account' => $account, 
					'password' => rand(100000, 999999), 
					'nickname' => $nickname, 
					'ext0' => $account, 
					'face' => $face, 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip()
				);
                if (!D('Passport')->register($user)) {
                    $this->error('创建帐号失败');
                }
                $token = D('Passport')->getToken();
                $connect['uid'] = $token['uid'];
                D('Connect')->save(array('connect_id' => $connect['connect_id'], 'uid' => $connect['uid']));
            }
            setUid($connect['uid']);
            session('access', $connect['connect_id']);
            header('Location:' . U('members/index'));
            die;
        } else {
            $connect = D('Connect')->getConnectByOpenid($data['type'], $data['open_id']);
            if (empty($connect)) {
                $connect = $data;
                $connect['connect_id'] = D('Connect')->add($data);
            } else {
                D('Connect')->save(array('connect_id' => $connect['connect_id'], 'token' => $data['token']));
            }
            if (empty($connect['uid'])) {
                if ($this->uid) {
                    D('Connect')->save(array('connect_id' => $connect['connect_id'], 'uid' => $this->uid));
                    $this->success('绑定第三方登录成功', U('user/information/index'));
                } else {
                    session('connect', $connect['connect_id']);
                    header('Location: ' . U('passport/bind'));
                }
            } else {
                setUid($connect['uid']);
                session('access', $connect['connect_id']);
                header('Location:' . U('members/index'));
            }
            die;
        }
    }
}
<?php



class PublicAction extends CommonAction {


 public function shopcate($parent_id=0){
        $datas = D('Shopcate')->fetchAll();
        $str = '';

        foreach($datas as $var){
            if($var['parent_id'] == 0 && $var['cate_id'] == $parent_id){
         
                foreach($datas as $var2){

                    if($var2['parent_id'] == $var['cate_id']){
                        $str.='<option value="'.$var2['cate_id'].'">'.$var2['cate_name'].'</option>'."\n\r";
                    }  
                }
                             
              
            }           
        }
        echo $str;die;
    }
    
        public function child($parent_id=0){
        $datas = D('Activitytype')->fetchAll();
        $str = '';

        foreach($datas as $var){
            if($var['parent_id'] == 0 && $var['type_id'] == $parent_id){
         
                foreach($datas as $var2){

                    if($var2['parent_id'] == $var['type_id']){
                        $str.='<option value="'.$var2['type_id'].'">'.$var2['type_name'].'</option>'."\n\r";
                    }  
                }
            }           
        }
        echo $str;die;
    }
    
       
 
    public function upload() {

        $model = $this->_get('model');
        import('ORG.Net.UploadFile');
        $upload = new UploadFile(); // 
        $upload->maxSize = 3145728; // 设置附件上传大小
        $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
        $name = date('Y/m/d', NOW_TIME);
        $dir = BASE_PATH . '/attachs/' . $name . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $upload->savePath = $dir; // 设置附件上传目录
        if (isset($this->_CONFIG['attachs'][$model]['thumb'])) {
            $upload->thumb = true;
            if (is_array($this->_CONFIG['attachs'][$model]['thumb'])) {
                $prefix = $w = $h = array();
                foreach ($this->_CONFIG['attachs'][$model]['thumb'] as $k => $v) {
                    $prefix[] = $k . '_';
                    list($w1, $h1) = explode('X', $v);
                    $w[] = $w1;
                    $h[] = $h1;
                }
                $upload->thumbPrefix = join(',', $prefix);
                $upload->thumbMaxWidth = join(',', $w);
                $upload->thumbMaxHeight = join(',', $h);
            } else {
                $upload->thumbPrefix = 'thumb_';
                list($w, $h) = explode('X', $this->_CONFIG['attachs'][$model]['thumb']);
                $upload->thumbMaxWidth = $w;
                $upload->thumbMaxHeight = $h;
            }
        }
        if (!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        } else {// 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            if (!empty($this->_CONFIG['attachs'][$model]['water'])) {
                import('ORG.Util.Image');
                $Image = new Image();
                $Image->water(BASE_PATH . '/attachs/' . $name . '/thumb_' . $info[0]['savename'], BASE_PATH . '/attachs/' . $this->_CONFIG['attachs']['water']);
            }
            if ($upload->thumb) {
                echo $name . '/thumb_' . $info[0]['savename'];
            } else {
                echo $name . '/' . $info[0]['savename'];
            }
        }
    }
    
    public function payyes(){
        $this->success('支付成功！',U('members/index'));
    }

    public function email() { //email验证接口
        $email = $this->_get('email');
        if (!isEmail($email)) {
            $this->error('EMAIL地址不正确', U('index/index'));
        }
        $uid = (int) $this->_get('uid');
        $time = (int) $this->_get('time');
        $sig = $this->_get('sig');
        if (empty($uid) || empty($time) || empty($sig)) {
            $this->error('参数不能为空', U('index/index'));
        }
        if (NOW_TIME - $time > 3600) {
            $this->error('验证链接已经超时了！', U('index/index'));
        }
        $sign = md5($uid . $email . $time . C('AUTH_KEY'));
        if ($sig != $sign) {
            $this->error('签名失败', U('index/index'));
        }
        $user = D('Users')->find($uid);
        if (empty($user))
            $this->error('用户不存在！', U('index/index'));
        if (!empty($user['email']))
            $this->error('用户已经通过邮件认证的！', U('index/index'));
        $data = array(
            'user_id' => $uid,
            'email' => $email
        );
        D('Users')->save($data);
        $this->success('恭喜您邮件认证成功！', U('index/index'));
    }

}
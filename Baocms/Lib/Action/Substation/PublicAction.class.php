<?php
class PublicAction extends CommonAction
{
    //根据后面实际需要 调整缩略图大小
    public function uploadify()
    {
        $model = $this->_get('model');
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();
        //
        $upload->maxSize = 3145728;
        // 设置附件上传大小
        $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg');
        // 设置附件上传类型
        $name = date('Y/m/d', NOW_TIME);
        $dir = BASE_PATH . '/attachs/' . $name . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $upload->savePath = $dir;
        // 设置附件上传目录
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
        if (!$upload->upload()) {
            // 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        } else {
            // 上传成功 获取上传文件信息
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
    public function editor()
    {
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();
        //
        $upload->maxSize = 3145728;
        // 设置附件上传大小
        $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg');
        // 设置附件上传类型
        $name = date('Y/m/d', NOW_TIME);
        $dir = BASE_PATH . '/attachs/editor/' . $name . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $upload->savePath = $dir;
        // 设置附件上传目录
        if (isset($this->_CONFIG['attachs']['editor']['thumb'])) {
            $upload->thumb = true;
            $upload->thumbType = 0;
            //不自动裁剪
            $upload->thumbPrefix = 'thumb_';
            list($w, $h) = explode('X', $this->_CONFIG['attachs']['editor']['thumb']);
            $upload->thumbMaxWidth = $w;
            $upload->thumbMaxHeight = $h;
        }
        if (!$upload->upload()) {
            // 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        } else {
            // 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            if (!empty($this->_CONFIG['attachs']['editor']['water'])) {
                import('ORG.Util.Image');
                $Image = new Image();
                $Image->water(BASE_PATH . '/attachs/editor/' . $name . '/thumb_' . $info[0]['savename'], BASE_PATH . '/attachs/' . $this->_CONFIG['attachs']['water']);
            }
            $return = array('url' => $name . '/thumb_' . $info[0]['savename'], 'originalName' => $name . '/thumb_' . $info[0]['savename'], 'name' => $name . '/thumb_' . $info[0]['savename'], 'state' => 'SUCCESS', 'size' => $info['size'], 'type' => $info['extension']);
            echo json_encode($return);
        }
    }
    //升级之前需要请求BAOCMS看看是否是授权用户，BAOCMS会进行回调验证TOKEN授权的
    public function token()
    {
        $url = 'http://www.taobao.com/update/token.html?host=' . urlencode($_SERVER['HTTP_HOST']) . '&bao_key=' . C('BAO_KEY');
        $token = file_get_contents($url);
        if (empty($token)) {
            $this->error('获取TOKEN失败，请稍后再试！');
        }
        file_put_contents(BASE_PATH . '/token_' . md5(date('Y-m-d', NOW_TIME)) . '.php', $token);
        header("Location:" . U('update/check'));
        //获取TOKEN后需要保存，服务器会请求checktoken来判断是否是正版用户的请求！
        die;
    }
    //用于BAOCMS回调请求TOKEN是否正确
    public function checktoken()
    {
        $file = BASE_PATH . '/token_' . md5(date('Y-m-d', NOW_TIME)) . '.php';
        echo file_get_contents($file);
        unlink($file);
        //TOKEN 只能被使用一次，如果使用过了
        die;
    }
    public function maps()
    {
        $lat = $this->_get('lat', 'htmlspecialchars');
        $lng = $this->_get('lng', 'htmlspecialchars');
        $this->assign('lat', $lat ? $lat : $this->_CONFIG['site']['lat']);
        $this->assign('lng', $lng ? $lng : $this->_CONFIG['site']['lng']);
        $this->display();
    }
}
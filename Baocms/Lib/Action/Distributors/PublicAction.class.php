<?php



class PublicAction extends CommonAction {

    public function verify() {
        import('ORG.Util.Image');
        Image::buildImageVerify(4,2,'png',60,30);
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
    
}
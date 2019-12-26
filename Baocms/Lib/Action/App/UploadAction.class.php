<?php

class  UploadAction extends  CommonAction{

    //调用云存储
    public function superUpload($model){
        import('ORG.Net.Upload');
        $upinfo = M("uploadset")->where("status = 1")->find();

        if(!empty($upinfo) && $upinfo['type'] != 'Local') {

            $conf = json_decode($upinfo['para'], true);
            $superup = new Upload(array('exts'=>'jpeg,jpg,gif,png'), $upinfo['type'], $conf);
            $upres = $superup->upload();

            /*if (isset($this->_CONFIG['attachs'][$model]['thumb'])) {
                if (is_array($this->_CONFIG['attachs'][$model]['thumb'])) {
                    //$prefix = $w = $h = array();
                    foreach($this->_CONFIG['attachs'][$model]['thumb'] as $k=>$v){
                        $prefix[] = $k.'_';
                        list($w1,$h1) = explode('X', $v);
                        $w=$w1;
                        $h=$h1;
                    }
                } else {
                    list($w, $h) = explode('X', $this->_CONFIG['attachs'][$model]['thumb']);
                }
                foreach ($upres as $pk => $pv){
                    $upres[$pk]['url'] = $pv['url']."?imageView2/1/w/".$w."/h/".$h;
                }
            }*/
            return  $upres;
        }else{
            return false;
        }
    }

    public function upload() {

        $model = $this->_get('model');
        $yun = $this->superUpload($model);
        if($yun){
            foreach ($yun as $pk => $pv){
                $picurl = $pv['url'];
            }
            echo json_encode(array('url'=>$picurl));
        }else{
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
                if (!empty($this->_CONFIG['attachs']['water'])) {
                    import('ORG.Util.Image');
                    $Image = new Image();
                    $Image->water(BASE_PATH . '/attachs/' . $name . '/thumb_' . $info[0]['savename'], BASE_PATH . '/attachs/' . $this->_CONFIG['attachs']['water']);
                }
                if ($upload->thumb) {
                    $picurl =  '/attachs/'.$name . '/thumb_' . $info[0]['savename'];
                    echo json_encode(array('url'=>$picurl));
                } else {
                    $picurl = '/attachs/'.$name . '/' . $info[0]['savename'];
                    echo json_encode(array('url'=>$picurl));
                }
            }
        }
        die;
    }

    public function uploadify() {
        $model = $this->_get('model');
		$yun = $this->superUpload($model);
        if($yun){
            foreach ($yun as $pk => $pv){
                $picurl = $pv['url'];
            }
            echo json_encode(array('url'=>$picurl));
        }else{
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
					foreach($this->_CONFIG['attachs'][$model]['thumb'] as $k=>$v){
						$prefix[] = $k.'_';
						list($w1,$h1) = explode('X', $v);
						$w[]=$w1;
						$h[]=$h1;
					}
					$upload->thumbPrefix = join(',',$prefix);
					$upload->thumbMaxWidth =join(',',$w);
					$upload->thumbMaxHeight =join(',',$h);
				} else {
					$upload->thumbPrefix = 'thumb_';
					list($w, $h) = explode('X', $this->_CONFIG['attachs'][$model]['thumb']);
					$upload->thumbMaxWidth = $w;
					$upload->thumbMaxHeight = $h;
				}
			}
			if (!$upload->upload()) {// 上传错误提示错误信息
				var_dump($upload->getErrorMsg());
			} else {// 上传成功 获取上传文件信息
				$info = $upload->getUploadFileInfo();
				if(!empty($this->_CONFIG['attachs']['water'])){
					import('ORG.Util.Image');
					$Image = new Image();
					$Image->water(BASE_PATH . '/attachs/'. $name . '/thumb_' . $info[0]['savename'],BASE_PATH . '/attachs/'.$this->_CONFIG['attachs']['water']);
				}
				if ($upload->thumb) {
                    $picurl =  '/attachs/'.$name . '/thumb_' . $info[0]['savename'];
                    echo json_encode(array('url'=>$picurl));
                } else {
                    $picurl = '/attachs/'.$name . '/' . $info[0]['savename'];
                    echo json_encode(array('url'=>$picurl));
                }
			}
		}
    }

    public function editor() {
        $yun = $this->superUpload('editor');
        if($yun){
            foreach ($yun as $pk => $pv){
                $picurl = $pv['url'];
                $picsize = $pv['size'];
                $pictype = $pv['ext'];
            }
            $return = array(
                'url' => $picurl,
                'originalName' => $picurl,
                'name' => $picurl,
                'state' => 'SUCCESS',
                'size' => $picsize,
                'type' => $pictype,
            );
            echo json_encode($return);exit;
        }else{
            import('ORG.Net.UploadFile');
            $upload = new UploadFile(); //
            $upload->maxSize = 3145728; // 设置附件上传大小
            $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
            $name = date('Y/m/d', NOW_TIME);
            $dir = BASE_PATH . '/attachs/editor/' . $name . '/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $upload->savePath = $dir; // 设置附件上传目录

            if (isset($this->_CONFIG['attachs']['editor']['thumb'])) {
                $upload->thumb = true;
                $upload->thumbPrefix = 'thumb_';
                $upload->thumbType = 0; //不自动裁剪
                list($w, $h) = explode('X', $this->_CONFIG['attachs']['editor']['thumb']);
                $upload->thumbMaxWidth = $w;
                $upload->thumbMaxHeight = $h;
            }
            if (!$upload->upload()) {// 上传错误提示错误信息
                var_dump($upload->getErrorMsg());
            } else {// 上传成功 获取上传文件信息
                $info = $upload->getUploadFileInfo();

                 if(!empty($this->_CONFIG['attachs']['editor']['water'])){
                    import('ORG.Util.Image');
                    $Image = new Image();

                     $Image->water(BASE_PATH . '/attachs/editor/'. $name . '/thumb_' . $info[0]['savename'],BASE_PATH . '/attachs/'.$this->_CONFIG['attachs']['water']);
                }
                $return = array(
                    'url' => '/attachs/'.$name . '/thumb_' . $info[0]['savename'],
                    'originalName' => '/attachs/'.$name . '/thumb_' . $info[0]['savename'],
                    'name' => '/attachs/'.$name . '/thumb_' . $info[0]['savename'],
                    'state' => 'SUCCESS',
                    'size' => $info['size'],
                    'type' => $info['extension'],
                );
                echo json_encode($return);
            }
        }
    }

    
    public function shangjia() {
        $shop_id = (int)$this->_get('shop_id');
        $sig  = $this->_get('sig');
        if(empty($shop_id) || empty($sig)) die;
        $sign = md5($shop_id.C('AUTH_KEY'));
        if($sign != $sig) die;
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
        
        if (isset($this->_CONFIG['attachs']['shopphoto']['thumb'])) {      
            $upload->thumb = true;
            $upload->thumbPrefix = 'thumb_';
            list($w, $h) = explode('X', $this->_CONFIG['attachs']['shopphoto']['thumb']);
            $upload->thumbMaxWidth = $w;
            $upload->thumbMaxHeight = $h;
        }
        if (!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        } else {// 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            if(!empty($this->_CONFIG['attachs']['shopphoto']['water'])){
               import('ORG.Util.Image');
               $Image = new Image();
               $Image->water(BASE_PATH . '/attachs/'. $name . '/thumb_' . $info[0]['savename'],BASE_PATH . '/'.$this->_CONFIG['attachs']['water']);
           }
            if($upload->thumb){
               $photo = '/attachs/'.$name . '/thumb_' . $info[0]['savename'];
            }else{
               $photo =  '/attachs/'.$name . '/' . $info[0]['savename'];
            }
            $data = array(
                'shop_id' => $shop_id,
                'photo' => $photo,
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
            );
            D('Shoppic')->add($data);
        }
        echo 1;
    }
     public function shopbanner() {
        $shop_id = (int)$this->_get('shop_id');
        $sig  = $this->_get('sig');
        if(empty($shop_id) || empty($sig)) die;
        $sign = md5($shop_id.C('AUTH_KEY'));
        if($sign != $sig) die;
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
        
        if (isset($this->_CONFIG['attachs']['shopbanner']['thumb'])) {      
            $upload->thumb = true;
            $upload->thumbPrefix = 'thumb_';
            list($w, $h) = explode('X', $this->_CONFIG['attachs']['shopbanner']['thumb']);
            $upload->thumbMaxWidth = $w;
            $upload->thumbMaxHeight = $h;
        }
        if (!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        } else {// 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            if(!empty($this->_CONFIG['attachs']['shopbanner']['water'])){
               import('ORG.Util.Image');
               $Image = new Image();
               $Image->water(BASE_PATH . '/attachs/'. $name . '/thumb_' . $info[0]['savename'],BASE_PATH . '/'.$this->_CONFIG['attachs']['water']);
           }
            if($upload->thumb){
               $photo = '/attachs/'.$name . '/thumb_' . $info[0]['savename'];
            }else{
               $photo =  '/attachs/'.$name . '/' . $info[0]['savename'];
            }
            $data = array(
                'shop_id' => $shop_id,
                'photo' => $photo,
                 'is_mobile'=>1,
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
            );
            D('Shopbanner')->add($data);
        }
        echo 1;
    }
    public function shopbanner1() {
        $shop_id = (int)$this->_get('shop_id');
        $sig  = $this->_get('sig');
        if(empty($shop_id) || empty($sig)) die;
        $sign = md5($shop_id.C('AUTH_KEY'));
        if($sign != $sig) die;
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
        
        if (isset($this->_CONFIG['attachs']['shopbanner1']['thumb'])) {      
            $upload->thumb = true;
            $upload->thumbPrefix = 'thumb_';
            list($w, $h) = explode('X', $this->_CONFIG['attachs']['shopbanner1']['thumb']);
            $upload->thumbMaxWidth = $w;
            $upload->thumbMaxHeight = $h;
        }
        if (!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        } else {// 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            if(!empty($this->_CONFIG['attachs']['shopbanner1']['water'])){
               import('ORG.Util.Image');
               $Image = new Image();
               $Image->water(BASE_PATH . '/attachs/'. $name . '/thumb_' . $info[0]['savename'],BASE_PATH . '/'.$this->_CONFIG['attachs']['water']);
           }
            if($upload->thumb){
               $photo = '/attachs/'.$name . '/thumb_' . $info[0]['savename'];
            }else{
               $photo =  '/attachs/'.$name . '/' . $info[0]['savename'];
            }
            $data = array(
                'shop_id' => $shop_id,
                'photo' => $photo,
                'is_mobile'=>0,
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
            );
            D('Shopbanner')->add($data);
        }
        echo 1;
    }
    
    
    
    
}
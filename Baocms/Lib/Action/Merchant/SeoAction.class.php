<?php



class SeoAction extends CommonAction {

    public function index() {
        $shop_id = (int) $this->shop_id;
        if ($this->isPost()) {

            $data = $this->_post('data',true);
            
            $update['seo_title'] = htmlspecialchars($data['seo_title']);
            
            $update['seo_keywords'] = htmlspecialchars($data['seo_keywords']);
            
            $update['seo_description'] = htmlspecialchars($data['seo_description']);
            $update['icp'] = htmlspecialchars($data['icp']);
            $update['sitelogo'] = htmlspecialchars($data['sitelogo']);
            if (!isImage($update['sitelogo'])) {
                $this->baoError('网站logo格式不正确');
            }
            if (false !== D('Shopdetails')->upDetails($this->shop_id, $update)) {
                $this->baoSuccess('操作成功', U('seo/index'));
            }
        } else {
            $detail = D('Shopdetails')->find($shop_id);
            $this->assign('detail', $detail);
            $this->display();
        }
    }

}

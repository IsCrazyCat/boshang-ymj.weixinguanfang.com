<?php
class channelWidget extends Widget {

    public function render($data) {
        $datas['setting_id'] = $data['setting_id'];
        $datas['channel_name'] = $data['channel_name'];
        $datas['channel_link'] = $data['channel_link'];
        $datas['help'] = $data['help'];
        $datas['helplink'] = $data['helplink'];
        $datas['keyword'] = $data['keyword'];
        $datas['keywordlink'] = $data['keywordlink'];
        $cats = D('Shopcate')->getChildren($data['cate_id']);
        $datas['shops'] = D('Shop')->where(array('closed'=>0,'cate_id'=>array('IN',$cats)))->order(' shop_id desc ')->limit(0,9)->select();
        $datas['ads'] = D('Ad')->where(array('closed'=>0,'site_id'=>$data['adsite_id'],'bg_date'=>array('ELT',TODAY),'end_date'=>array('EGT',TODAY)))->limit(0,5)->order('orderby asc')->select();
        $datas['blocks'] = D('Recommend')->where(array('group_id'=>$data['site_id'],'bg_date'=>array('ELT',TODAY),'end_date'=>array('EGT',TODAY)))->limit(0,4)->order('orderby asc')->select();
        $content = $this->renderFile('display', $datas);
        return $content;
    }

    public function setting($data) {
        $datas = array();
        if (!empty($data['setting_id'])) {
            $settings = D('Templatesetting')->detail($data['setting_id']);
            $datas = $settings['setting'];
        }
        $content = $this->renderFile('setting', $datas);
        return $content;
    }

    public function cfg() {
        return array(
            'name' => '首页频道挂件',
            'content' => '这个是一款在首页调用不同频道的插件！',
        );
    }
}
<?php
class LifecateAction extends CommonAction
{
    public function ajax($cate_id, $life_id = 0)
    {
        if (!($cate_id = (int) $cate_id)) {
            $this->error('请选择正确的分类');
        }
        if (!($detail = D('Lifecate')->find($cate_id))) {
            $this->error('请选择正确的分类');
        }
        $this->assign('cate', $detail);
        $this->assign('attrs', D('Lifecateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
        if ($life_id) {
            $this->assign('detail', D('Life')->find($life_id));
            $this->assign('maps', D('Lifecateattr')->getAttrs($life_id));
        }
        $this->display();
    }
}
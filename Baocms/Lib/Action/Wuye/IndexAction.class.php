<?php
class IndexAction extends CommonAction
{
    public function index()
    {
        $bg_time = strtotime(TODAY);
        //小区广告
        $counts['ad'] = (int) D('Communityad')->where(array('community_id' => $this->community_id))->count();
        //小区通知
        $counts['feedback'] = (int) D('Feedback')->where(array('community_id' => $this->community_id, 'closed' => 0))->count();
        $counts['feedback_audit'] = (int) D('Feedback')->where(array('community_id' => $this->community_id, 'audit' => 0, 'closed' => 0))->count();
        $counts['feedback_day'] = (int) D('Feedback')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'community_id' => $this->community_id, 'closed' => 0))->count();
        //小区通知
        $counts['news'] = (int) D('Communitynews')->where(array('community_id' => $this->community_id, 'closed' => 0))->count();
        $counts['news_audit'] = (int) D('Communitynews')->where(array('community_id' => $this->community_id, 'audit' => 0, 'closed' => 0))->count();
        $counts['news_day'] = (int) D('Communitynews')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'community_id' => $this->community_id, 'closed' => 0))->count();
        //便民电话
        $counts['phone'] = (int) D('Convenientphone')->where(array('community_id' => $this->community_id))->count();
        //小区邻居
        $counts['neighbor'] = (int) D('Communityusers')->where(array('community_id' => $this->community_id))->count();
        //业主管理
        $counts['owner'] = (int) D('Communityowner')->where(array('community_id' => $this->community_id))->count();
        $counts['owner_audit'] = (int) D('Communityowner')->where(array('community_id' => $this->community_id, 'audit' => 0))->count();
        $counts['owner_day'] = (int) D('Communityowner')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'community_id' => $this->community_id, 'closed' => 0))->count();
        //小区账单
        $counts['order_type_1'] = (int) D('Communityorderproducts')->where(array('type' => 1, 'community_id' => $this->community_id))->sum('money');
        $counts['order_type_1_is_pay'] = (int) D('Communityorderproducts')->where(array('type' => 1, 'community_id' => $this->community_id, 'is_pay' => 0))->sum('money');
        $counts['order_type_2'] = (int) D('Communityorderproducts')->where(array('type' => 2, 'community_id' => $this->community_id))->sum('money');
        $counts['order_type_2_is_pay'] = (int) D('Communityorderproducts')->where(array('type' => 2, 'community_id' => $this->community_id, 'is_pay' => 0))->sum('money');
        $counts['order_type_3'] = (int) D('Communityorderproducts')->where(array('type' => 3, 'community_id' => $this->community_id))->sum('money');
        $counts['order_type_3_is_pay'] = (int) D('Communityorderproducts')->where(array('type' => 3, 'community_id' => $this->community_id, 'is_pay' => 0))->sum('money');
        $counts['order_type_4'] = (int) D('Communityorderproducts')->where(array('type' => 4, 'community_id' => $this->community_id))->sum('money');
        $counts['order_type_4_is_pay'] = (int) D('Communityorderproducts')->where(array('type' => 4, 'community_id' => $this->community_id, 'is_pay' => 0))->sum('money');
        $counts['order_type_5'] = (int) D('Communityorderproducts')->where(array('type' => 5, 'community_id' => $this->community_id))->sum('money');
        $counts['order_type_5_is_pay'] = (int) D('Communityorderproducts')->where(array('type' => 5, 'community_id' => $this->community_id, 'is_pay' => 0))->sum('money');
        //财务管理
        $counts['order'] = (int) D('Communityorderproducts')->where(array('community_id' => $this->community_id))->sum('money');
        $counts['order_0'] = (int) D('Communityorderproducts')->where(array('community_id' => $this->community_id, 'is_pay' => 0))->sum('money');
        //未交费
        $counts['order_1'] = (int) D('Communityorderproducts')->where(array('community_id' => $this->community_id, 'is_pay' => 1))->sum('money');
        //已交费
        //论坛
        $counts['bbs'] = (int) D('Communityposts')->where(array('community_id' => $this->community_id))->count();
        $counts['bbs_audit'] = (int) D('Communityposts')->where(array('community_id' => $this->community_id, 'audit' => 0, 'closed' => 0))->count();
        $counts['bbs_day'] = (int) D('Communityposts')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'community_id' => $this->community_id, 'closed' => 0))->count();
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('counts', $counts);
        $this->display();
    }
    public function order()
    {
        $bg_time = strtotime(TODAY);
        //小区账单
        $counts['order_type_1'] = (int) D('Communityorderproducts')->where(array('type' => 1, 'community_id' => $this->community_id))->sum('money');
        $counts['order_type_1_is_pay'] = (int) D('Communityorderproducts')->where(array('type' => 1, 'community_id' => $this->community_id, 'is_pay' => 0))->sum('money');
        $counts['order_type_1_no_pay'] = (int) D('Communityorderproducts')->where(array('type' => 1, 'community_id' => $this->community_id, 'is_pay' => 1))->sum('money');
        $counts['order_type_2'] = (int) D('Communityorderproducts')->where(array('type' => 2, 'community_id' => $this->community_id))->sum('money');
        $counts['order_type_2_is_pay'] = (int) D('Communityorderproducts')->where(array('type' => 2, 'community_id' => $this->community_id, 'is_pay' => 0))->sum('money');
        $counts['order_type_2_no_pay'] = (int) D('Communityorderproducts')->where(array('type' => 2, 'community_id' => $this->community_id, 'is_pay' => 1))->sum('money');
        $counts['order_type_3'] = (int) D('Communityorderproducts')->where(array('type' => 3, 'community_id' => $this->community_id))->sum('money');
        $counts['order_type_3_is_pay'] = (int) D('Communityorderproducts')->where(array('type' => 3, 'community_id' => $this->community_id, 'is_pay' => 0))->sum('money');
        $counts['order_type_3_no_pay'] = (int) D('Communityorderproducts')->where(array('type' => 3, 'community_id' => $this->community_id, 'is_pay' => 1))->sum('money');
        $counts['order_type_4'] = (int) D('Communityorderproducts')->where(array('type' => 4, 'community_id' => $this->community_id))->sum('money');
        $counts['order_type_4_is_pay'] = (int) D('Communityorderproducts')->where(array('type' => 4, 'community_id' => $this->community_id, 'is_pay' => 0))->sum('money');
        $counts['order_type_4_no_pay'] = (int) D('Communityorderproducts')->where(array('type' => 4, 'community_id' => $this->community_id, 'is_pay' => 1))->sum('money');
        $counts['order_type_5'] = (int) D('Communityorderproducts')->where(array('type' => 5, 'community_id' => $this->community_id))->sum('money');
        $counts['order_type_5_is_pay'] = (int) D('Communityorderproducts')->where(array('type' => 5, 'community_id' => $this->community_id, 'is_pay' => 0))->sum('money');
        $counts['order_type_5_no_pay'] = (int) D('Communityorderproducts')->where(array('type' => 5, 'community_id' => $this->community_id, 'is_pay' => 1))->sum('money');
        //财务管理
        $counts['order'] = (int) D('Communityorderproducts')->where(array('community_id' => $this->community_id))->sum('money');
        $counts['order_0'] = (int) D('Communityorderproducts')->where(array('community_id' => $this->community_id, 'is_pay' => 0))->sum('money');
        //未交费
        $counts['order_1'] = (int) D('Communityorderproducts')->where(array('community_id' => $this->community_id, 'is_pay' => 1))->sum('money');
        //已交费
        $this->assign('counts', $counts);
        $this->display();
    }
}
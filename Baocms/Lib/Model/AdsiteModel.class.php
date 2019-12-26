<?php
class AdsiteModel extends CommonModel {
    protected $pk = 'site_id';
    protected $tableName = 'ad_site';
    protected $token = 'ad_site';
    public function getType() {
        return array(1 => '文字广告', 2 => '图片广告', 3 => '代码广告');
    }

    public function getPlace() {
        return array(
            1 => 'PC首页',
            2 => 'PC套餐',
            3 => 'PC活动',
            4 => 'PC新版家政/文章资讯',
            5 => 'PC商城',
            6 => 'PC外卖',
            7 => 'PC订座',
            8 => 'PC分类信息',
            9 => 'PC优惠券',
            10 => 'PC商家',
            11 => 'PC积分商城',
            12 => 'PC一元云购',
            13 => 'PC贴吧',
            14 => '手机首页',
            15 => '手机套餐',
            16 => '手机商家',
            17 => '手机活动',
            18 => '手机同城优购',
            19 => '手机家政',
            20 => '手机外卖',
            21 => '手机订座',
            22 => '手机酒店',
            23 => '手机贴吧',
            24 => '手机酒店',
            25 => '手机众筹',
            26 => '手机积分商城',
            27 => '手机生活信息',
            28 => '手机黄页',
            29 => '手机小区详细页',
            30 => '手机会员卡',
            31 => '手机榜单',
            32 => '手机附近工作',
            33 => 'PC登录注册页面所有广告',
            34 => '拼团',
        );

    }



}


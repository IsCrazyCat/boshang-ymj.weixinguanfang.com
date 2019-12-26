<?php
class KeywordModel extends CommonModel {

    protected $pk = 'key_id';
    protected $tableName = 'keyword';

    public function getKeyType() {
        $res = array(
            '0' => '不限',
            '1' => '商家',
            '2' => '套餐',
            '3' => '生活信息',
            '4' => '商品',
            '5' => '分享',
			'6' => '订座',
        );
        return $res;
    }
}

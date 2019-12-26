<?php
class WeixinkeywordModel extends CommonModel
{
    protected $pk = 'keyword_id';
    protected $tableName = 'weixin_keyword';
    protected $token = 'weixin_keyword';
    public function checkKeyword($keyword)
    {
        $words = $this->fetchAll();
        foreach ($words as $val) {
            if ($val['keyword'] == $keyword) {
                return $val;
            }
        }
        return false;
    }
}
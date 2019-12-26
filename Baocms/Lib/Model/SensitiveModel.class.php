<?php
class SensitiveModel extends CommonModel
{
    protected $pk = 'words_id';
    protected $tableName = 'sensitive_words';
    protected $token = 'sensitive_words';
    protected $cacheTime = 8640000;
    //100天
    //return false  表示正常，否则会返回对应的敏感词
    public function checkWords($content)
    {
        $words = $this->fetchAll();
        foreach ($words as $val) {
            if (strstr($content, $val['words'])) {
                return $val['words'];
            }
        }
        return false;
    }
}
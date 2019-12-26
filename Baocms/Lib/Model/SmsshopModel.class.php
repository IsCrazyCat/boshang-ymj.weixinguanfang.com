<?php
class SmsshopModel extends CommonModel
{
    protected $pk = 'log_id';
    protected $tableName = 'sms_shop';
    protected $token = 'bao_sms_shop';
    public function fetchAll()
    {
        $cache = cache(array('type' => 'File', 'expire' => $this->cacheTime));
        if (!($data = $cache->get($this->token))) {
            $result = $this->order($this->orderby)->select();
            $data = array();
            foreach ($result as $row) {
                $data[$row['sms_key']] = $row;
            }
            $cache->set($this->token, $data);
        }
        return $data;
    }
}
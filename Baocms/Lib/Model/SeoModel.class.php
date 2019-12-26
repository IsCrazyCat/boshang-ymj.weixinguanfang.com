<?php
class SeoModel extends CommonModel
{
    protected $pk = 'seo_id';
    protected $tableName = 'seo';
    protected $token = 'bao_seo';
    public function fetchAll()
    {
        $cache = cache(array('type' => 'File', 'expire' => $this->cacheTime));
        if (!($data = $cache->get($this->token))) {
            $result = $this->order($this->orderby)->select();
            $data = array();
            foreach ($result as $row) {
                $data[$row['seo_key']] = $row;
            }
            $cache->set($this->token, $data);
        }
        return $data;
    }
}
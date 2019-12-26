<?php
class SettingModel extends CommonModel
{
    protected $pk = 'k';
    protected $tableName = 'setting';
    protected $token = 'setting';
    protected $settings = null;
    public function fetchAll()
    {
        $cache = cache(array('type' => 'File', 'expire' => $this->cacheTime));
        if (!($data = $cache->get($this->token))) {
            $result = $this->select();
            foreach ($result as $row) {
                $row['v']= preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $row['v'] );
                $row['v']= str_replace("\r", "", $row['v']);
                $row['v'] = unserialize($row['v']);
                $data[$row[$this->pk]] = $row['v'];
            }
            $cache->set($this->token, $data);
        }
        $this->settings = $data;
        return $this->settings;
    }
    public function save($arr)
    {
        if ($this->find(array("where" => array('k' => $arr['k'])))) {
            return parent::save($arr);
        } else {
            return $this->add($arr);
        }
    }
}
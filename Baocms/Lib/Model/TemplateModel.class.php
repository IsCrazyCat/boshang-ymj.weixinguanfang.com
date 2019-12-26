<?php
class TemplateModel extends CommonModel
{
    protected $pk = 'template_id';
    protected $tableName = 'template';
    protected $token = 'template';
    public function fetchAll()
    {
        $cache = cache(array('type' => 'File', 'expire' => $this->cacheTime));
        if (!($data = $cache->get($this->token))) {
            $result = $this->order($this->orderby)->select();
            $data = array();
            foreach ($result as $row) {
                $data[$row['theme']] = $row;
            }
            $cache->set($this->token, $data);
        }
        return $data;
    }
    public function getDefaultTheme()
    {
        $data = $this->fetchAll();
        foreach ($data as $k => $v) {
            if ($v['is_default']) {
                return $v['theme'];
            }
        }
        return C('DEFAULT_THEME');
    }
}
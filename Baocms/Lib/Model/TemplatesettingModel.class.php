<?php
class TemplatesettingModel extends CommonModel
{
    protected $pk = 'theme';
    protected $tableName = 'template_setting';
    protected $token = 'template_setting';
    public function detail($theme)
    {
        $data = $this->fetchAll();
        return $data[$theme];
    }
    public function _format($data)
    {
        $data['setting'] = unserialize($data['setting']);
        return $data;
    }
}
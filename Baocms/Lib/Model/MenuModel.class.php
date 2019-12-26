<?php
class MenuModel extends CommonModel
{
    protected $pk = 'menu_id';
    protected $tableName = 'menu';
    protected $token = 'bao_menu';
    protected $orderby = array('orderby' => 'asc');
    public function checkAuth($auth)
    {
        $data = $this->fetchAll();
        foreach ($data as $row) {
            if ($auth == $row['menu_action']) {
                return true;
            }
        }
        return false;
    }
}
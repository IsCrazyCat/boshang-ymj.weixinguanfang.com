<?php
class RoleMapsModel extends CommonModel
{
    protected $tableName = 'role_maps';
    public function getMenuIdsByRoleId($role_id)
    {
        $role_id = (int) $role_id;
        $datas = $this->where(" role_id = '{$role_id}' ")->select();
        $return = array();
        foreach ($datas as $val) {
            $return[$val['menu_id']] = $val['menu_id'];
        }
        return $return;
    }
}
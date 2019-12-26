<?php
class PcfunctionModel extends CommonModel
{
    protected $pk = 'function_id';
    protected $tableName = 'pc_function';
    protected $token = 'pcfunction';
    protected $orderby = array('orderby' => 'asc');
}
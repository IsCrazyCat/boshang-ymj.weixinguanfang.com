<?php
class Village_bbsModel extends CommonModel
{
    protected $pk = 'post_id';
    protected $tableName = 'village_bbs';
    protected $token = 'village_bbs';
    protected $orderby = array('hot' => 'desc');
}
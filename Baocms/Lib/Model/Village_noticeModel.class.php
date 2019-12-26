<?php
class Village_noticeModel extends CommonModel
{
    protected $pk = 'id';
    protected $tableName = 'village_notice';
    protected $token = 'village_notice';
    protected $orderby = array('addtime' => 'desc');
}
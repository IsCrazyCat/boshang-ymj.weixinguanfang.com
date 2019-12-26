<?php
class SystemcontentModel extends CommonModel
{
    protected $pk = 'content_id';
    protected $tableName = 'system_content';
    protected $token = 'system_content';
    protected $orderby = array('orderby' => 'asc');
}
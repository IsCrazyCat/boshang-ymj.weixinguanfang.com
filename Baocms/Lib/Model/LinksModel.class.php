<?php
class LinksModel extends CommonModel
{
    protected $pk = 'link_id';
    protected $tableName = 'links';
    protected $token = 'links';
    protected $orderby = array('orderby' => 'asc');
}
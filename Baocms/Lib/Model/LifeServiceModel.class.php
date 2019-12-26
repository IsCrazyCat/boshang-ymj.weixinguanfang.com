<?php
class LifeServiceModel extends RelationModel
{
    protected $_link = array('LifeServiceCate' => array('mapping_type' => BELONGS_TO, 'class_name' => 'LifeServiceCate', 'foreign_key' => 'cate_id', 'mapping_fields' => 'cate_name', 'as_fields' => 'cate_name,cate_name'));
}
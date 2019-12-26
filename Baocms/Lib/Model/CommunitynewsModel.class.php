<?php
class CommunitynewsModel extends CommonModel
{
    protected $pk = 'news_id';
    protected $tableName = 'community_news';
    protected $token = 'community_news';
    protected $orderby = array('orderby' => 'asc');
}
<?php


class HouseworkModel extends CommonModel {

    protected $pk = 'housework_id';
    protected $tableName = 'housework';
    private $svcCfg = array(
        1 => '家庭保洁',
        2 => '新居开荒',
        3 => '深度保洁',
        4 => '洗衣洗鞋',
        5 => '洗窗帘',
        6 => '洗地毯',
        7 => '油烟机清洗',
        8 => '空调清洗',
        9 => '冰箱除臭',
        10 => '微波炉清洗',
        11 => '电烤箱清洗',
        12 => '饮水机清洗',
        13 => '洗衣机清洗',
        14 => '擦玻璃',
        15 => '厨房保养',
        16 => '卫生间保养',
        17 => '地板打蜡',
        18 => '皮质沙发保养',
        19 => '管道疏通',
        20 => '家电维修',
        21 => '开锁换锁',
        22 => '家具维修'
    );

    public function getCfg() {
        return $this->svcCfg;
    }

}

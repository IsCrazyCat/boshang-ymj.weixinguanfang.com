<?php

class VillageModel extends CommonModel {

    protected $pk = 'village_id';
    protected $tableName = 'village';
    protected $token = 'village';
    protected $orderby = array('orderby' => 'asc');



    public function _format($data) {
        static $area = null;
        if ($area == null) {
            $area = D('Area')->fetchAll();
        }
        $data['area_name'] = $area[$data['area_id']]['area_name'];
        return $data;

    }

	 public function getVillageCate() {
			return array(
				'1' => '休闲娱乐',
				'2' => '旅游胜地',
				'3' => '养殖乡村',
				'4' => '水果之乡',
				'5' => '鱼米之乡',
			);
		}

    

}


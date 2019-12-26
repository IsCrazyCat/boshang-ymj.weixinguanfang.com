<?php



class  HouseworksettingModel  extends  CommonModel{
    protected $pk   = 'id';
    protected $tableName =  'housework_setting';
    
    
    public function detail($id){
        $id = (int)$id;
        $data = $this->find($id);
        if(empty($data)){
            $data = array('id'=>$id);
            $this->add($data);
        }
        return $data;
    }
    
}
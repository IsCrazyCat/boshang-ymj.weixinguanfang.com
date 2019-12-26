<?php



class PublicAction extends CommonAction {
    //根据后面实际需要 调整缩略图大小
    
    
    
    
    public function maps(){
        $lat = $this->_get('lat',  'htmlspecialchars');
        $lng = $this->_get('lng','htmlspecialchars');
        
        $this->assign('lat' , $lat ? $lat : $this->_CONFIG['site']['lat']);
        $this->assign('lng' , $lng ? $lng : $this->_CONFIG['site']['lng']);
        $this->display();
    }
    

    
}
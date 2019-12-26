<?php

class  VerifyAction extends CommonAction{
    
    public function index(){
        import('ORG.Util.Image');
        Image::buildImageVerify(4,2,'png',60,30);
    }
    
    
    
}
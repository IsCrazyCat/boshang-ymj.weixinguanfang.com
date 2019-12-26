<?php

class IndexAction extends CommonAction {

   public function index(){

		if(empty($this->uid)){

			redirect('wap/passport/login');

		}else{
			redirect("http://" . $_SERVER['HTTP_HOST'] . "/user/member/index.html");

		}

   }

}




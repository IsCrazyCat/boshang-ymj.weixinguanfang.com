<?php
class Express
{
	public $keys = '';
	public $company = 'suer';
	public $num = '0';

	public function getContent()
	{
		$json_str = file_get_contents("http://api.kuaidi100.com/api?id={$this->keys}&com={$this->company}&nu={$this->num}&valicode=&show=0&muti=1&order=asc");
		return json_decode($json_str, true);
	}

}
<?php

class IndexController extends Yaf_Controller_Abstract
{
	public function indexAction(){
		echo 'ok?';
		var_dump(phpinfo());
	}	
}
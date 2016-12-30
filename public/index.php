<?php
	
	define("APP_PATH",  realpath(dirname(__FILE__) . '/../')); 
	date_default_timezone_set('PRC');

	$app  = new \Yaf_Application(APP_PATH . "/conf/application.ini");

	// 执行入口文件
	try {
		$app->bootstrap();
		$app->run();
	} catch (\InvalidArgumentException $e) {
		$data = array(
			'status' => 1001,
			'data'   => array(),
			'msg'    => $e->getMessage(),		
		);
		header('Content-Type:application/json; charset=utf-8');
		echo json_encode($data);
	} catch (\Exception $e) {
		$data = array(
			'status' => 1000,
			'data'   => array(),
			'msg'    => 'Bad Request',		
		);
		header('Content-Type:application/json; charset=utf-8');
		echo json_encode($data);
		var_dump($e->getMessage());
}

<?php

namespace Service\Lib\Controller;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Base extends \Yaf_Controller_Abstract
{
	private $logger = null;
	private $redis = null;
	public function logger() {
		if (is_null($this->logger)) {
			$this->logger = new Logger('uc');
			$filename = APP_PATH .'/log/' . date('Y.m.d', time()) . '.log';
			$output = "[%datetime%] %channel%.%level_name%: %message% %context% \r\n";
			$formatter = new LineFormatter($output);
			$stream = new StreamHandler($filename, Logger::DEBUG);
			$stream->setFormatter($formatter);
			$this->logger->pushHandler($stream);
		}
		return $this->logger;
	}
	
	public function getRedis(){
		
		if($this->redis == null){
			$redis = new \Redis();
			$ip = \Yaf_Registry::get('config')->redis->ip;
			$port = \Yaf_Registry::get('config')->redis->port;
			$redis->connect($ip,$port);
		}
		return $redis;
	}
	
	public function jsonRender($data)
	{
		header('Content-Type:application/json; charset=utf-8');
        echo json_encode($data);
	}
	
	public function ajaxRender($data = array(), $msg = null)
	{
		$data = array('ret' => 0, 'data' => $data, 'msg' => $msg);
		return $this->jsonRender($data);
	}
	
	public function errorAjaxRender($msg = null)
	{
		$data = array('ret' => 1, 'msg' => $msg);
		return $this->jsonRender($data);
	}
	
	public function  getPost($name, $if_not_exist = null, $security = true)
	{
		if (!isset($_POST[$name])) {
			return $if_not_exist;
		}
		
		$result = $_POST[$name];
		if (is_array($result) && $security) {
			foreach ($result as $key => $value) {
				$result[$key] = htmlSpecialChars($value); 
			}
		}
		
		if (!is_array($result) && $security) {
			$result = htmlSpecialChars($result);
		}
		
		return $result;
	}
	
	public function getQuery($name, $if_not_exist = null, $security = true)
	{
		$string = isset($_GET[$name]) ? urldecode($_GET[$name]) : $if_not_exist;
		if($security){
			$string = htmlSpecialChars($string,ENT_QUOTES);
		}
		return $string;
	}
	
	public function formatLog($class, $func, $line) {
		return array('class:'. $class , 'function:'. $func,'line:'. $line);
	}
	
}
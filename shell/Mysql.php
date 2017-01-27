<?php
class GetDb{
		private $db = null;
		private static $instance = null;
		private function __construct(){
			$database_info = parse_ini_file('./shell.ini');
			// nmred
			// 使用 sprintf 拼接多个变量到字符串中
			$dsn = sprintf('mysql:dbname=%s;host=%s;port=%s', $database_info['dbDatabase'],
				$database_info['dbHostname'], $database_info['dbPort']);
			//$database = $database_info['dbDatabase'];
			//$hostname = $database_info['dbHostname'];
			//$password = $database_info['dbPassword'];
			//$username = $database_info['dbUsername'];
			//$port = $database_info['dbPort'];
			//$dsn = 'mysql:dbname='.$database.';host='.$hostname.';port='.$port;
			//$this->db = new \PDO($dsn, $username, $password);
			$this->db = new \PDO($dsn, $database_info['dbUsername'], $database_info['dbPassword']);
		}
		public static function getInstance(){
			if(self::$instance == null){
				self::$instance = new self();
			}
			return self::$instance;
		}
		
		public function getDb(){
			return $this->db;
		}
}

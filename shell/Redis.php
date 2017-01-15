<?php
class GetRedis{
		private static $instance = null;
		private $redis = null;
		private function __construct(){
			$reids_info = parse_ini_file('./shell.ini');
			$this->redis = new \Redis();
			$ip = $reids_info['redisIp'];
			$port = $reids_info['redisPort'];
			$this->redis->connect($ip,$port);
		}
		public static function getInstance(){
			if(self::$instance == null){	
				self::$instance = new self();
			}
			return self::$instance;
		}
		
		public function getRedis(){
			return $this->redis;
		}
		
	}
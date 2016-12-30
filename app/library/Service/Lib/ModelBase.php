<?php 
namespace Service\Lib;

class ModelBase
{
	static protected $db = null;
	public function getDb()
	{
		if(self::$db == null) {
			$database = \Yaf_Registry::get('config')->db->database;
			$hostname = \Yaf_Registry::get('config')->db->hostname;
			$password = \Yaf_Registry::get('config')->db->password;
			$username = \Yaf_Registry::get('config')->db->username;
			$port = \Yaf_Registry::get('config')->db->port;
			$dsn = 'mysql:dbname='.$database.';host='.$hostname.';port='.$port;
			self::$db = new \PDO($dsn, $username, $password);	
		}
		return self::$db;
	}	
}


	
<?php
use Service\Lib\ModelBase;
class LoginModel extends ModelBase
{
	public function userInfoByUsr($username)
	{
		$db = $this->getDb();
		$sql = 'select * from '.UC_TABLE_USER_INFO .' where name=?';
		$stm = $db->prepare($sql);
		if(!$stm->execute(array($username))){
			throw new \Exception('fail to get userInfoByUsr!');
		}
		return $stm->fetch(PDO::FETCH_ASSOC);
	}
	
	
	public function userInfobyUsrPasswd($username,$password)
	{
		$db = $this->getDb();
		$sql = 'select * from '.UC_TABLE_USER_INFO .' where name=? and password=?';
		$stm = $db->prepare($sql);
		if(!$stm->execute(array($username,$password))){
			throw new \Exception('fail to get userInfobyUsrPasswd!');
		}
		return $stm->fetch(PDO::FETCH_ASSOC);
	}
}
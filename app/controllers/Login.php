<?php 
use Wp\Demo\Test;
use Service\Lib\Controller\Base;
class LoginController extends Base
{
	
	public function indexAction()
	{
		$this->getView()->display('login.html');
	}
	
	public function verifyAction()
	{
		$username = trim($this->getPost('username',''));
		$password = trim($this->getPost('password',''));
		$login = new LoginModel;
		if(empty($username)){
			return $this->errorAjaxRender('Username can not be empty!');
		}
		if(!$login->userInfoByUsr($username)){
			return $this->errorAjaxRender('Username does not exist!');
		}
		if(empty($password)){
			return $this->errorAjaxRender('Password can not be empty!');
		}
		
		if(!$login->userInfobyUsrPasswd($username,md5($password.'123456'))){
			return $this->errorAjaxRender('Password is not correct!');
		}
				
		$_SESSION['is_login'] = 1;
		$_SESSION['username'] = $username;	
		//获取一开始想要访问的URL
		$refers = parse_url($_SERVER['HTTP_REFERER']);
		$refer = '/domain/list';

		if (isset($refers['query']) && strstr($refers['query'], 'refer=')  !== false) {
			$params = explode('&', $refers['query']);
						
			foreach ($params as $val) {
				list($name, $value) = explode('=', $val);
				if ($name == 'refer') {
					$refer = $value;
					break;
				}
			}
		}
		$isLocation = $this->getPost('isLocation');
		if ($isLocation == 1) {
			$this->redirect($refer); 
		} else {
			return $this->ajaxRender($refer, 'login sucess.');
		}
		
	}
}
<?php 
class CheckLogonPlugin extends Yaf_Plugin_Abstract
{
	public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
	{
		$controller = $request->getControllerName();
		if(stristr($controller,'api') === false && stristr($controller,'login') === false){
			if(isset($_SESSION['is_login']) && $_SESSION['is_login'] == 1){
				return true;
			}else{
				header('location:/login?refer=' . $this->getCurrentUrl());
			}
		}
		
	}
	
	protected function getCurrentUrl()
    {
        if (!isset($_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $_SERVER['REQUEST_URI'])) {
            return false;
        }

        $url = 'http';
        if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') {
            $url .= 's';
        }
        $url .= '://';

        if ((int)$_SERVER['SERVER_PORT'] !== 80) {
            $url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else {
            $url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        }

        return $url;
    }
}

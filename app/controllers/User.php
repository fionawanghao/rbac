<?php
use Service\Lib\Controller\Base;

class UserController extends Base
{
	private $allow_type = array(1,2);
	private $allow_status = array(1,2);
	
	public function addAction()
	{
		
		$name = trim($this->getPost('name',''));
		$image = trim($this->getPost('image',''));
		$email = trim($this->getPost('email',''));
		$network_type = trim($this->getPost('network_type',1));
		$status = trim($this->getPost('status',1));
		$user = new UserModel;
	
		if(empty($name)){
			$error = '用户名不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$user_info_by_name = $user->UserInfoByName($name);
		if($user_info_by_name){
			$error = '用户名已存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		if(empty($image)){
			$error = '头像地址不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(empty($email)){
			$error = '电子邮箱不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$pattern = '/^[0-9a-z\._-]+@[0-9a-z\._-]+$/i';
		if(!preg_match($pattern,$email)){
			$error = '请输入正确的电子邮箱格式';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(!in_array($network_type,$this->allow_type)){
			$error = '网络类型只能是数字1表示内网，或者数字2表示外网';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(!in_array($status,$this->allow_status)){
			$error = '用户状态必须是数字1表示可用，或者数字2表示禁用';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$data = array($network_type,$name,$image,$email,$status);
		
		try{
			$ret = $user->add($data);
		}catch(\Exception $e){
			$this->logger()->error($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($e->getMessage());
		}
		$error = '用户添加成功';
		$arr = array('network_type: '.$network_type,'name: '.$name,'image: '.$image,'email: '.$email,'status: '.$status);
		$this->logger()->info($error,$arr);
		return $this->ajaxRender(array(),$error);
	}
	
	public function listAction()
	{
		$user = new UserModel;
		$conditionIsTotal = array('istotal' => true);
		$condition = array(
			'start' => $this->getPost('start',0),
			'limit' => $this->getPost('limit',10),
		);
		
		try{
			$total = $user->getList($conditionIsTotal);
			$result = $user->getlist($condition);
		}catch(\Exception $e){
			 return $this->errorAjaxRender($e->getMessage());
		}
		$data = array(
			'data' => $result,
			'recordsTotal' => $total,
		);
		
		return $this->jsonRender($data);
	}
	
	public function updateAction()
	{
		$user = new UserModel;
		$id = trim($this->getPost('id',''));
		$network_type = $this->getPost('network_type');
		$name = $this->getPost('name');
		$image = $this->getPost('image');
		$email = $this->getPost('email');
		$status = $this->getPost('status');
		$data = array();
		
		if(empty($id)){
			$error = '用户ID不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		$userInfo = $user->userInfo($id);
		if(empty($userInfo)){
			$error = '该记录不存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		if($userInfo['is_delete'] == 1){
			$error = '该记录已经删除，不能更新';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(!is_null($network_type)){
			$network_type = trim($network_type);
			if(empty($network_type)){
				$error = '网络类型不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			if(!in_array($network_type,$this->allow_type)){
				$error = '网络类型只能是数字1表示内网，或者数字2表示外网';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			$data['network_type']=$network_type;
		}
		
		if(!is_null($name)){
			$name = trim($name);
			if(empty($name)){
				$error = '用户名不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			$data['name']=$name;
		}
		$user_info_by_name = $user->UserInfoByName($name);
		if($user_info_by_name){
			$error = '该用户名已存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		  
		if(!is_null($image)){
			$image = trim($image);
			if(empty($image)){
				$error = '用户头像地址不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			$data['image'] = $image;
		}
		
		if(!is_null($email)){
			$email = trim($email);
			if(empty($email)){
				$error = '电子邮箱不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			$pattern = '/^[0-9a-z\._-]+@[0-9a-z\._-]+$/i';
			if(!preg_match($pattern,$email)){
				$error = '电子邮箱格式错误';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			$data['email'] = $email;
		}
		
		if(!is_null($status)){
			$status = trim($status);
			if(empty($status)){
				$error = '用户状态不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			if(!in_array($status,$this->allow_status)){
				$error = '用户状态必须是数字1表示可用，或者数字2表示禁用';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			$data['status'] = $status;
		}
		
		if(empty($data)){
			$error = '没有添加更新信息';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		try{
			$ret = $user->update($id,$data);
		}catch(\Exception $e){
			$this->logger()->error($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($e->getMessage());
		}
		$error = 'id为'.$id.'的记录更新成功';
		$this->logger()->info($error,$data);
		return $this->ajaxRender(array(),$error);
	}	
	
	public function deleteAction()
	{
		
		$user = new UserModel;
		$id = explode(',',$this->getPost('id',''));
		
		/*if(!is_array($id)){
			$id = array($id);
		}*/
	
		foreach($id as $val){
			$val = trim($val);
			if(empty($val)){
				$error = '删除的记录ID不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
		}	
		try{
			$user_batch_info = $user->userBatchInfo($id);
		}catch(\Exception $e){
			$this->logger()->error($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			$this->errorAjaxRender($e->getMessage());
		}
		if(!$user_batch_info){
			$error = '删除的记录不存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		foreach($user_batch_info as $v){
			if(isset($v['is_delete']) && $v['is_delete'] == 1 ){
				$error = 'id是'.$v['id'].'的记录已删除，不能再次删除';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
		}	

		try{
			$ret = $user->del($id);	
		}catch(\Exception $e){
			$this->logger()->error($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($e->getMessage());
		}
		foreach($id as $a){
			$this->logger()->info('ID为'.$a.'的记录删除记录成功');
		}	
		return $this->ajaxRender(array(),'删除记录成功');
	}
}
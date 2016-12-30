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
			return $this->errorAjaxRender('用户名不能为空');
		}
		$user_info_by_name = $user->UserInfoByName($name);
		if($user_info_by_name){
			return $this->errorAjaxRender('用户名已存在');
		}
		if(empty($image)){
			return $this->errorAjaxRender('头像地址不能为空');
		}
		
		if(empty($email)){
			return $this->errorAjaxRender('电子邮箱不能为空');
		}
		$pattern = '/^[0-9a-z\._-]+@[0-9a-z\._-]+$/i';
		if(!preg_match($pattern,$email)){
			return $this->errorAjaxRender('请输入正确的电子邮箱格式');
		}
		
		if(!in_array($network_type,$this->allow_type)){
			return $this->errorAjaxRender('网络类型只能是数字1表示内网，或者数字2表示外网');
		}
		
		if(!in_array($status,$this->allow_status)){
			return $this->errorAjaxRender('用户状态必须是数字1表示可用，或者数字2表示禁用');
		}
		$data = array($network_type,$name,$image,$email,$status);
		
		try{
			$ret = $user->add($data);
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		
		return $this->ajaxRender(array(),'用户添加成功');
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
			return $this->errorAjaxRender('用户ID不能为空');
		}
		
		$userInfo = $user->userInfo($id);
		if(empty($userInfo)){
			return $this->errorAjaxRender('该记录不存在');
		}
		if($userInfo['is_delete'] == 1){
			return $this->errorAjaxRender('该记录已经删除，不能更新');
		}
		
		if(!is_null($network_type)){
			$network_type = trim($network_type);
			if(empty($network_type)){
				return $this->errorAjaxRender('网络类型不能为空');
			}
			if(!in_array($network_type,$this->allow_type)){
				return $this->errorAjaxRender('网络类型只能是数字1表示内网，或者数字2表示外网');
			}
			$data['network_type']=$network_type;
		}
		
		if(!is_null($name)){
			$name = trim($name);
			if(empty($name)){
				return $this->errorAjaxRender('用户名字不能为空');
			}
			$data['name']=$name;
		}
		$user_info_by_name = $user->UserInfoByName($name);
		if($user_info_by_name){
			return $this->errorAjaxRender('该用户名已存在');
		}
		
		if(!is_null($image)){
			$image = trim($image);
			if(empty($image)){
				return $this->errorAjaxRender('用户头像地址不能为空');
			}
			$data['image'] = $image;
		}
		
		if(!is_null($email)){
			$email = trim($email);
			if(empty($email)){
				return $this->errorAjaxRender('电子邮箱不能为空');
			}
			$pattern = '/^[0-9a-z\._-]+@[0-9a-z\._-]+$/i';
			if(!preg_match($pattern,$email)){
				return $this->errorAjaxRender('电子邮箱格式错误');
			}
			$data['email'] = $email;
		}
		
		if(!is_null($status)){
			$status = trim($status);
			if(empty($status)){
				return $this->errorAjaxRender('用户状态不能为空');
			}
			if(!in_array($status,$this->allow_status)){
				return $this->errorAjaxRender('用户状态必须是数字1表示可用，或者数字2表示禁用');
			}
			$data['status'] = $status;
		}
		
		if(empty($data)){
			return $this->errorAjaxRender('没有添加更新信息');
		}
		try{
			$ret = $user->update($id,$data);
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		
		return $this->ajaxRender(array(),'更新成功');
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
				return $this->errorAjaxRender('删除的记录ID不能为空');
			}
		}	
		try{
			$user_batch_info = $user->userBatchInfo($id);
		}catch(\Exception $e){
			$this->errorAjaxRender($e->getMessage());
		}
		if(!$user_batch_info){
			return $this->errorAjaxRender('删除的记录不存在');
		}
		foreach($user_batch_info as $v){
			if(isset($v['is_delete']) && $v['is_delete'] == 1 ){
				return $this->errorAjaxRender('id是'.$v['id'].'的记录已删除，不能再次删除');
			}
		}	

		try{
			$ret = $user->del($id);	
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		
		return $this->ajaxRender(array(),'删除记录成功');
	}
}
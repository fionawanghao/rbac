<?php 
use Service\Lib\Controller\Base;
class RoleController extends Base
{
	private $allow_type = array(1,2);
	private	$allow_status = array(1,2);
	public function addAction()
	{
		$domain_id = trim($this->getPost('domain_id',''));
		$role_name = trim($this->getPost('role_name',''));
		$role_desc = trim($this->getPost('role_desc',''));
		$role_type = trim($this->getPost('role_type',1));
		$status = trim($this->getPost('status',1));
		$role = new RoleModel;
		$domain = new DomainModel;
		
		if(empty($domain_id)){
			return $this->errorAjaxRender('产品线ID不能为空');
		}
		$domain_info = $domain->domainInfo($domain_id);
		if(!$domain_info){
			return $this->errorAjaxRender('输入的产品ID不存在');
		}
		
		$role_info_by_name = $role->roleInfoByNameDomainId($role_name,$domain_id);
		if(!empty($role_info_by_name)){
			return $this->errorAjaxRender('该产品线的该角色已经存在');
		}
		if(empty($role_name)){
			return $this->errorAjaxRender('角色名称不能为空');
		}
		
		if(empty($role_desc)){
			return $this->errorAjaxRender('角色描述不能为空');
		}
		
		if(!in_array($role_type,$this->allow_type)){
			return $this->errorAjaxRender('网络类型只能是数字1表示内网，或者数字2表示表示外网，不能是其他的值');
		}
		
		if(!in_array($status,$this->allow_status)){
			return $this->errorAjaxRender('角色状态只能是数字1表示可用，或者数字2表示禁用，不能是其他的值');
		}
		
		$data = array($domain_id,$role_name,$role_desc,$role_type,$status,time(),time());
		try{
			$ret = $role->insert($data);
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		
		return $this->ajaxRender(array(),'角色添加成功');
	}
	
	public function listAction()
	{
		$role = new RoleModel;
		$conditionTotal = array('is_total'=>true);
		$condition = array(
			'start' => $this->getPost('start',0),
			'limit' => $this->getPost('limit',10)
		);
		
		try{
			$total = $role->getList($conditionTotal);
			$result = $role->getList($condition);
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage);
		}
		
		$data = array(
			'data' => $result,
			'recordsTotal' => $total,
		);
		
		return $this->jsonRender($data);
		
	}
	
	public function updateAction()
	{
		$role = new RoleModel;
		$domain = new DomainModel;
		$id = $this->getPost('id',14);
		$role_name = $this->getPost('role_name');
		$role_desc = $this->getPost('role_desc');
		$role_type = $this->getPost('role_type');
		$status = $this->getPost('status');
		$update_time = time();
		$data = array();
		
		if(is_null($id) || empty(trim($id))){
			return $this->errorAjaxRender('ID不能为空');
		}
		
		$info = $role->roleInfo($id);
		if(empty($info)){
			return $this->errorAjaxRender('更新的记录不存在');
		}elseif($info['is_delete'] == 1){
			return $this->errorAjaxRender('更新的记录已经删除');
		}
		
		if(!is_null($role_name)){
			$role_name = trim($role_name);
			if(empty($role_name)){
				return $this->errorAjaxRender('角色名称不能为空');
			}
			$roles = $role->rolesOfOneDomain($info['domain_id']);
			foreach($roles as $v){
				if($role_name == $v['role_name']){
					return $this->errorAjaxRender('不能修改为已存在的角色名称');
				}
			}
			$data['role_name'] = $role_name;
		}
		
		if(!is_null($role_desc)){
			$role_desc = trim($role_desc);
			if(empty($role_desc)){
				return $this->errorAjaxRender('角色描述不能为空');
			}
			$data['role_desc'] = $role_desc;
		}
		
		if(!is_null($role_type)){
			$role_type = trim($role_type);
			if(empty($role_type)){
				return $this->errorAjaxRender('网络类型不能为空');
			}
		
			if(!in_array($role_type,$this->allow_type)){
				return $this->errorAjaxRender('网络类型只能是数字1表示内网，或者数字2表示表示外网，不能是其他的值');
			}
			
			$data['role_type'] = $role_type;
		}
		
		if(!is_null($status)){
			$status = trim($status);
			if(empty($status)){
				return $this->errorAjaxRender('角色状态不能为空');
			}
			if(!in_array($status,$this->allow_status)){
				return $this->errorAjaxRender('角色状态只能是数字1表示可用，或者数字2表示禁用，不能是其他的值');
			}
			$data['status'] = $status;
		}
		
		if(empty($data)){
			return $this->errorAjaxRender('没有添加更新内容');
		}
		$data['update_time'] = $update_time;
		
		try{
			$ret = $role->update($id,$data);
			
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		return $this->ajaxRender(array(), '更新角色信息成功');
	}
	
	public function deleteAction()
	{
		$role = new RoleModel;
		$id = explode(',',$this->getPost('id',''));
		
		/*if(!is_array($id)){
			$id = array($id);
		}*/
		foreach($id as $val){
			$val = trim($val);
			if(empty($val)){
				return $this->errorAjaxRender('删除的记录ID不能为空');
			}	
			$roleInfo = $role->roleInfo($val);
			if(isset($roleInfo['is_delete']) && $roleInfo['is_delete'] == 1 ){
				return $this->errorAjaxRender('id是'.$val.'的记录已删除，不能再次删除');
			}
		}
		try{
			$role_batch_info = $role->roleBatchInfo($id);
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		
		foreach($role_batch_info as $val){
			if(isset($val['is_delete']) && $val['is_delete'] == 1 ){
				return $this->errorAjaxRender('id是'.$val['id'].'的记录已删除，不能再次删除');
			}
		}
		
		try{
			$ret = $role->del($id);	
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
	
		return $this->ajaxRender(array(),'删除记录成功');
	}
}

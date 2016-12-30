<?php 
use Service\Lib\Controller\Base;

class DomainController extends Base
{
	private $allowType = array(1, 2);
	private $allowStatus = array(1, 2);
	
	public function addAction(){
		$name = trim($this->getPost('domain_name',''));
		$desc = trim($this->getPost('domain_desc','')); 
		$type = trim($this->getPost('domain_type',1)); 
		$default_role_id = $this->getPost('default_role_id',1);
		$status = $this->getPost('status',1);
		$domain = new DomainModel;
		
		if(empty($name)){
			return $this->errorAjaxRender('产品线名称不能为空');
		}
		$domain_info = $domain->domainInfoByName($name);
		if(!empty($domain_info)){
			return $this->errorAjaxRender('该产品线已经存在，不能重复添加');
		}
		if(empty($desc)){
			return $this->errorAjaxRender('产品线描述不能为空');
		}
	
		if(!in_array($type, $this->allowType)){
			return $this->errorAjaxRender('产品线url类型只能是数字1表示产品线提供URL，或者数字2表示产品线自行控制，不提供URL，不能是其他的值');
		}
		
		if(!in_array($status, $this->allowStatus)){
			return $this->errorAjaxRender('产品线状态只能是数字1表示可用，或者数字2表示禁用，不能是其他的值');
		}
		$salt = md5($name.'12345');
		$data = array($name,$desc,$type,$salt,$default_role_id,$status,time(),time());
	
		try{
			$ret = $domain->insert($data);	
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		
		return $this->ajaxRender(array(), '添加产品线成功');	
    }
	
	public function listAction()
	{
		$domain = new DomainModel;
		$conditionIsTotal = array( 'istotal' => true);
		$condition = array(
			'start' => $this->getPost('start',0),
			'limit' => $this->getPost('limit',10),
		);
		
		try{
			$total = $domain->getList($conditionIsTotal);
			$result = $domain->getlist($condition);
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
		$domain = new DomainModel;
		$id = $this->getPost('id');
		$name = $this->getPost('domain_name'); 
		$desc = $this->getPost('domain_desc'); 
		$type = $this->getPost('domain_type'); 
		$default_role_id = $this->getPost('default_role_id');
		$status = $this->getPost('status');
		$data = array();
		
		$info = $domain->domainInfo($id);
		if(is_null($id) || empty(trim($id))){
			return $this->errorAjaxRender('ID不能为空');
		}
		if(empty($info)){
			return $this->errorAjaxRender('更新的记录不存在');
		} elseif($info['is_delete'] == 1){
			return $this->errorAjaxRender('更新的记录已经删除');
		}
		
		if (!is_null($name)) {
			if (empty(trim($name))) {
				return $this->errorAjaxRender('产品线名称不能为空');
			} else {
				$data['domain_name'] = trim($name);
			}
		}
		
		if(!is_null($desc)){
			if(empty(trim($desc))){
				return $this->errorAjaxRender('产品线描述不能为空');	
			} else {
				$data['domain_desc'] = $desc;
			}
		}
		
		if (!is_null($type)) {
			$type = trim($type);
			if(empty($type)){
				return $this->errorAjaxRender('产品线url类型不能为空');
			}
			if(!in_array($type, $this->allowType)){
				return $this->errorAjaxRender('产品线url类型只能是数字1表示产品线提供URL，或者数字2表示产品线自行控制，不提供URL，不能是其他的值');
			}
			$data['domain_type'] = $type;
		}
		
		if(!is_null($default_role_id)){
			$default_role_id = trim($default_role_id);
			if(empty($default_role_id)){
				return $this->errorAjaxRender('default_role_id不能为空');
			}
			$data['default_role_id'] = $default_role_id;
		}
		
		if(!is_null($status)){
			$status = trim($status);
			if(empty($status)){
				return $this->errorAjaxRender('产品线状态不能为空');
			}
			if(!in_array($status, $this->allowStatus)){
				return $this->errorAjaxRender('产品线状态只能是数字1表示可用，或者数字2表示禁用，不能是其他的值');
			}
			$data['status'] = $status;
		}
		
		if (empty($data)) {
			return $this->errorAjaxRender('产品线没有更新内容');
		}
		$data['update_time'] = time();
		try{
			$ret = $domain->update($id,$data);
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		
		return $this->ajaxRender(array(), '更新产品线成功');
	}
	
	public function deleteAction()
	{
		$domain = new DomainModel;
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
			$domain_batch_info = $domain->domainBatchInfo($id);
		}catch(\Exception $e){
			return errorAjaxRender($e->getMessage());
		}
		foreach($domain_batch_info as $val){
			if(isset($val['is_delete']) && $val['is_delete'] == 1 ){
				return $this->errorAjaxRender('id是'.$val['id'].'的记录已删除，不能再次删除');
			}
		}
		
		try{
			$ret = $domain->del($id);	
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		
		return $this->ajaxRender(array(),'删除记录成功');	
	}
	
	
}
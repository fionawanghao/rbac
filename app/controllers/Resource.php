<?php 
use Service\Lib\Controller\Base;

class ResourceController extends Base
{
	private $allow_status = array(1,2);
	public function addAction()
	{
		
		$domain = new DomainModel;
		$resource = new ResourceModel;
		$domain_id = trim($this->getPost('domain_id',''));
		$resource_url = trim($this->getPost('resource_url',''));
		$resource_name = trim($this->getPost('resource_name',''));
		$resource_desc = trim($this->getPost('resource_desc',''));
		$status = trim($this->getPost('status',1));
		$data = array();
		
		if(empty($domain_id)){
			$error = '添加的产品线ID为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$ret = $domain->domainInfo($domain_id);
		if(!$ret){
			$error = '输入的产品线ID不存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(empty($resource_url)){
			$error = '权限URL不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$resource_info_of_domain = $resource->resourceInfoOfDomain($domain_id,$resource_url);
		
		if($resource_info_of_domain){
			$error = '该生产线的该权限已经存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		if(empty($resource_name)){
			$error = '权限名称不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(empty($resource_desc)){
			$error = '权限描述不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(!in_array($status,$this->allow_status)){
			$error = '权限状态只能是数字1表示可用，或者数字2表示禁用，不能是其他的值';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		$data = array($domain_id,$resource_url,$resource_name,$resource_desc,$status,time(),time());
		
		try{
			$ret = $resource->add($data);
		}catch(\Exception $e){
			$this->logger()->debug($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($e->getMessage());
		}
		$info = '添加权限成功';
		$arr = array('domain_id:'.$domain_id,'resource_url:'.$resource_url,'resource_name:'.$resource_name,'resource_desc:'.$resource_desc,'status:'.$status,'create_time:'.time(),'update_time:'.time());
		$this->logger()->info($info,$arr);
		return $this->ajaxRender(array(),$info);
	}
	
	public function listAction()
	{
		$resource = new ResourceModel;
		$conditionTotal = array('is_total' => true);
		$condition = array(
			'start' => $this->getPost('start',0),
			'limit' => $this->getPost('limit',10),
		);
		
		try{
			$total = $resource->getList($conditionTotal);
			$result = $resource->getList($condition);
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
		
		$resource = new ResourceModel;
		$id = $this->getPost('id')  ;
		$resource_url = $this->getPost('resource_url');
		$resource_name = $this->getPost('resource_name');
		$resource_desc = $this->getPost('resource_desc');
		$status = $this->getPost('status');
		$data = array();
		
		if(is_null($id) || empty(trim($id))){
			$error = 'ID不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$info = $resource->resourceInfo($id);
		
		if(empty($info)){
			$error = '更新的记录不存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}elseif($info['is_delete'] == 1){
			$error = '更新的记录已经删除';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(!is_null($resource_url)){
			$resource_url = trim($resource_url);
			if(empty($resource_url)){
				$error = '权限URL不能为空';
				$this->logger()->error($error ,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error );
			}
			$data['resource_url'] = $resource_url;
		}
		$resource_info_of_domain = $resource->resourceInfoOfDomain($info['domain_id'],$resource_url);
		if($resource_info_of_domain){
			$error = '该生产线的该权限已经存在，请重新填写';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		if(!is_null($resource_name)){
			$resource_name = trim($resource_name);
			if(empty($resource_name)){
				$error = '权限名称不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error );
			}
			$data['resource_name'] = $resource_name;
		}
		
		if(!is_null($resource_desc)){
			$resource_desc = trim($resource_desc);
			if(empty($resource_desc)){
				$error = '权限描述不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			$data['resource_desc'] = $resource_desc;
		}
		
		if(!is_null($status)){
			$status = trim($status);
			if(empty($status)){
				$error = '权限状态不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			if(!in_array($status,$this->allow_status)){
				$error = '权限状态只能是数字1表示可用，或者数字2表示禁用，不能是其他的值';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			$data['status'] = $status;
		}
		if(empty($data)){
			$error = '没有添加更新内容';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$data['update_time'] = time();
		try{
			$ret = $resource->update($id,$data);
		}catch(\Exception $e){
			$this->logger()->error($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($e->getMessage());
		}
		$error = '修改ID为'.$id.'的权限成功';
		$this->logger()->info($error,$data);
		return $this->ajaxRender(array(),$error);	
	}
	
	public function deleteAction()
	{
		
		$resource = new ResourceModel;
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
			$role_batch_info = $resource->roleBatchInfo($id);
		}catch(\Exception $e){
			$this->logger()->debug($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($e->getMessage());
		}
		
		foreach($role_batch_info as $val){
			if(isset($val['is_delete']) && $val['is_delete'] == 1 ){
				$error = 'id是'.$val['id'].'的记录已删除，不能再次删除';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
		}
		
		try{
			$ret = $resource->del($id);	
		}catch(\Exception $e){
			$this->logger()->debug($e->getMessage(),$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($e->getMessage());
		}
		foreach($id as $a){
			$this->logger()->info('id是'.$a.'的记录删除成功');
		}
		return $this->ajaxRender(array(),'删除记录成功');
			
	}
}
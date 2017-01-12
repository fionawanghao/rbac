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
			$error = '产品线名称不能为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$domain_info = $domain->domainInfoByName($name);
		if(!empty($domain_info)){
			$error = '添加的产品线名称重复添加';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		if(empty($desc)){
			$error = '添加的产品线描述为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
	
		if(!in_array($type, $this->allowType)){
			$error = '产品线url类型只能是数字1表示产品线提供URL，或者数字2表示产品线自行控制，不提供URL，不能是其他的值';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if(!in_array($status, $this->allowStatus)){
			$error = '产品线状态只能是数字1表示可用，或者数字2表示禁用，不能是其他的值';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$salt = md5($name.'12345');
		$data = array($name,$desc,$type,$salt,$default_role_id,$status,time(),time());
	
		try{
			$ret = $domain->insert($data);	
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		$info = '添加产品线'.$name.'成功';
		$this->logger()->info($info,$data);
		return $this->ajaxRender(array(), $info);	
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
			$error = '更新的产品线id为空';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		if(empty($info)){
			$error = '更新的产品线记录不存在';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		} elseif($info['is_delete'] == 1){
			$error = '更新的产品线记录已经删除';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		
		if (!is_null($name)) {
			if (empty(trim($name))) {
				$error = '更新的产品线名称为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			} else {
				$data['domain_name'] = trim($name);
			}
		}
		
		if(!is_null($desc)){
			if(empty(trim($desc))){
				$error = '更新的产品线描述为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);	
			} else {
				$data['domain_desc'] = $desc;
			}
		}
		
		if (!is_null($type)) {
			$type = trim($type);
			if(empty($type)){
				$error = '更新的产品线url类型不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			if(!in_array($type, $this->allowType)){
				$error = '产品线url类型只能是数字1表示产品线提供URL，或者数字2表示产品线自行控制，不提供URL，不能是其他的值';
				$this->logger()->error($error ,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error );
			}
			$data['domain_type'] = $type;
		}
		
		if(!is_null($default_role_id)){
			$default_role_id = trim($default_role_id);
			if(empty($default_role_id)){
				$error = '更新的产品线default_role_id不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			$data['default_role_id'] = $default_role_id;
		}
		
		if(!is_null($status)){
			$status = trim($status);
			if(empty($status)){
				$error = '更新的产品线状态不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			if(!in_array($status, $this->allowStatus)){
				$error = '更新的产品线状态有误，只能是1或者2';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
			$data['status'] = $status;
		}
		
		if (empty($data)) {
			$error = '产品线没有更新内容';
			$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
			return $this->errorAjaxRender($error);
		}
		$data['update_time'] = time();
		try{
			$ret = $domain->update($id,$data);
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		$error = '更新ID为'.$id.'产品线成功';
		$this->logger()->info($error,$data);
		return $this->ajaxRender(array(), $error);
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
				$error = '删除的记录ID不能为空';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
		}
		
		try{
			$domain_batch_info = $domain->domainBatchInfo($id);
		}catch(\Exception $e){
			return errorAjaxRender($e->getMessage());
		}
		foreach($domain_batch_info as $val){
			if(isset($val['is_delete']) && $val['is_delete'] == 1 ){
				$error = 'id是'.$val['id'].'的记录已删除，不能再次删除';
				$this->logger()->error($error,$this->formatLog(__CLASS__ ,__FUNCTION__,__LINE__));
				return $this->errorAjaxRender($error);
			}
		}
		
		try{
			$ret = $domain->del($id);	
		}catch(\Exception $e){
			return $this->errorAjaxRender($e->getMessage());
		}
		foreach($id as $a){
			$this->logger()->info('删除ID为'.$a.'的记录成功');
		}
		
		return $this->ajaxRender(array(),'删除记录成功');	
	}
	
	
}
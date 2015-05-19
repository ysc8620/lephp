<?php
/**
 * 控制器基类
 * User: ShengYue
 * Email: ysc8620@163.com
 * QQ: 372613912
 */
abstract class core_controller {
	
	function run($action=null,$controller=null) {
		$this -> exec_before();
		$method = $action . "Action";
		if(!method_exists ( $this, $method )){
			throw new core_exception_404(907001,'controller no this method:'.$method);
		}
		try{
			$this->{$method} ();
		}catch (core_exception_assert $e){
			throw $e;
			//@todo
		}catch (core_exception_program $e){
			throw $e;
			//@todo
		}catch (core_exception_Unexpected $e){
			throw $e;
			//@todo
		}
		$this -> exec_after();
	}
	
	
	//前置执行
	function exec_before(){
		//@todo
		//something as check permission 
	}
	
	
	//后置执行
	function exec_after(){
		//@todo
		//something as clear pools
	}
}
?>
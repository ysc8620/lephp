<?php
/**
 * 500拒绝服务类
 * 当系统发现攻击及其他异常时执行
 */
 
class core_exception_500 extends core_exception {
    public function __construct($message=null) {
    	//@todo
    	$assignList = array(
    		'msg'	=>	'您的本次请求非由本站发起，请注意检查来源连接安全'
    	);
    	core_comm::tpl('comm') -> show_result($assignList,'500.html');
        parent::__construct($message);
    }
}

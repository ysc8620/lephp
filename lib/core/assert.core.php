<?php
/**
 * 
 * 断言
 * 
 * 断言用于验证程序中不可能出现的情况。可以通过 core_assert::as_* 系列方法调整断言的行为。
 * 默认行为为不输出任何内容。
 */
class core_assert{
	static protected $assert_type = 0;
	
	/**
	 * 设置assert行为为不输出任何内容
	 */
	static public function as_dumb(){
		self::$assert_type = 0;
	}
	
	/**
	 * 
	 * 设置assert行为为触发warning
	 */
	static public function as_warning(){
		self::$assert_type = 1;
	}
	
	/**
	 * 设置assert行为为抛出exception
	 */
	static public function as_exception(){
		self::$assert_type = 3;
	}
	
	/**
	 * 
	 * 设置assert行为为触发error
	 */
	static public function as_error(){
		self::$assert_type = 2;
	}
	
	/**
	 * 验证条件是否为成立，如果不成立，则提示指定的message
	 * 
	 * @param bool $condition
	 * @param string $message
	 */
	static public function true($condition,$msg_no,$message=null){
		if(!$condition){
			self::act($msg_no,$message);
		}
	}
	
	/**
	 * 验证条件是否不成立，如果为成立，则提示指定的message
	 * @param bool $condition
	 * @param string $message
	 */
	static public function false($condition,$msg_no,$message=null){
		if($condition){
			self::act($msg_no,$message);
		}
	}
	
	/**
	 * 错误处理
	 * @param string $message
	 * @throws comm_exception_assert
	 */
	static protected function act($msg_no=null,$message=null){
	    throw new core_exception_assert($msg_no,$message);
	    exit;
	/* 	$msg_no = isset($msg_no) ? $msg_no : 900001;
	//	$message = core_Msg::message($msg_no,LANGUAGE);
		switch (self::$assert_type){
			case 2:
				trigger_error($msg_no, E_USER_ERROR);
				break;
			case 3:
				//断言异常均为调试级异常
				throw new core_exception_program($msg_no,$message);
				break;
			case 1:
				trigger_error($msg_no, E_USER_WARNING);
				break;
			default:
		} */
	} 
}
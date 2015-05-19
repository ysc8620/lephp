<?php
/**
 * 消息类(支持多语言)
 * @author aloner <qiang_killer@126.com>
 * @version 1.1
 */ 
class core_msg{
	static $message;	
	static $language = 'chinese';	//默认语言(chinese/english/and so on)
	
	
	/**
	 * 根据信息编号获取信息内容
	 * @param int $msg_no
	 * @param str $lang 语言
	 * @return
	 */
	public static function message($msg_no,$lang=LANGUAGE){
		if(!self::$message){
			self::load($lang);
		}
		if(!isset(self::$message[$msg_no])){
		    throw new core_exception_program(900002,$msg_no);
		    exit;
		}
		return self::$message[$msg_no];
	}
	
	/**
	 * 加载语言库
	 * @param str $lang
	 * @return
	 */
	private static function load($lang=LANGUAGE){
		if($lang){
			self::$language = $lang;
		}
		$file_path = LANGUAGE_PATH . core_comm::parse_path(self::$language) . ".msg.php";
        self::$message = core_comm::load($file_path);
	}
}
?>
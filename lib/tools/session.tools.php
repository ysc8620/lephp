<?php 
/**
 * session 方法封装
 * @author aloner
 *
 */
class tools_session {
	
	/**
	 * session模式，默认为服务器存储
	 * @var str
	 */
	static $session_set = 'by_file';
	
	
	/**
	 * 设置session存储方式，默认为缓存存储
	 */
	static function set_cache_by($session_by='by_file'){
		self::$session_by = $session_by;
	}
	
	/**
	 * 设置session
	 * @param str $name COOKIE名称
	 * @param str $value cookie值 只能是字符串类型
	 * @param int $expire 过期时间，以秒为单位（不是时间戳）
	 */
	static function set_session($session_name,$session_value,$expire=0){
		if(self::$session_set == 'by_file'){
			$_SESSION[$session_name] = $session_value;
		}else{
			$session_name = self::get_session_name($session_name);
			core_cache::pools() -> set($session_name,$session_value,$expire);
		}
	}
	
	
	/**
	 * 取回session
	 * @param str $session_name session名
	 */
	static function get_session($session_name){
		if(self::$session_set == 'by_file'){
			return $_SESSION[$session_name];
		}else{
			$session_name = self::get_session_name($session_name);
			return core_cache::pools() -> get($session_name);
		}
	}
	
	/**
	 * 删除session
	 * @param unknown_type $session_name
	 */
	static function del_session($session_name){
		if(self::$session_set == 'by_file'){
			unset($_SESSION[$session_name]);
		}else{
			$session_name = self::get_session_name($session_name);
			core_cache::pools() -> del($session_name);
		}
	}
	
	static function get_session_name($session_name){
		//$ip = core_context::get_client_ip(true);
		return $session_name . "_" .  session_id();
	}
}
?>
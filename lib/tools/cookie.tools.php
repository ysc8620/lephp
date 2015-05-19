<?php 
/**
 * cookie信息封装
 * 
 * 写入cookie时将数据进行加密保存
 * 
 * @author aloner
 */
class tools_cookie{

	/**
	 * 添加一个COOKIE
	 * @param str $name COOKIE名称
	 * @param str $value cookie值 只能是字符串类型
	 * @param int $expire 过期时间，以秒为单位（不是时间戳）
	 * @param unknown_type $path
	 * @param unknown_type $domain
	 * @param unknown_type $secure
	 * @param unknown_type $httponly
	 */
	static function set_cookie($name,$value,$expire=0,$path='/',$domain='',$secure=false,$httponly=false){
		$name = md5($name);
		//将数据加密
		$value = base64_encode($value);
		//获取随机混淆码
		$crypt_key = substr($value,2,4);
		//获取md5校验码
		$cookie_token = md5($value . $crypt_key);
		//设置值
		$cookie_value = $cookie_token . $value;
		if($expire != 0){
    		$expire = time() + $expire;
		}
		setcookie($name,$cookie_value,$expire,$path,$domain,$secure,$httponly);
	}
	
	
	/**
	 * 获取一个COOKIE
	 */
	static function get_cookie($name){
		$name = md5($name);
		if(isset($_COOKIE[$name])){
			$cookie_value = $_COOKIE[$name];
			$cookie_token = substr($cookie_value,0,32);
			$value = substr($cookie_value,32);
			$crypt_key = substr($value,2,4);
			//验证数据完整性
			if($cookie_token != md5($value . $crypt_key)){
				//cookie被篡改
				return null;
			}
			return base64_decode($value);
		}
		return null;
	}
	
	/**
	 * 删除一个COOKIE
	 * @param unknown_type $name
	 */
	static function del_cookie($name,$path='/',$domain='',$secure=false,$httponly=false){
		$name = md5($name);
		//设置值
		$cookie_value = null;
		$expire = time() - 3600;
		setcookie($name,$cookie_value,$expire,$path,$domain,$secure,$httponly);
	}
}
?>
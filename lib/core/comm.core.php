<?php
/**
 * 公共类
 * User: ShengYue
 * Email: ysc8620@163.com
 * QQ: 372613912
 */
class core_comm{
	static function auto_load($class_name){
		$path = explode ( '_', $class_name );
        $real_path = ROOT_PATH;
		switch (strtolower ( $path [0] )) {
            case "controller" :
                $real_path = APP_PATH;
                $ext = ".controller.php";
                break;
			case "table" :
                $real_path = APP_PATH;
				$ext = ".table.php";
				break;
			case "dao" :
                $real_path = APP_PATH;
				$ext = ".dao.php";
				break;
            case "vo" :
                $ext = ".vo.php";
                break;
			case "tools" :
				$ext = ".tools.php";
				break;
			case "core" :
				$ext = ".core.php";
				break;
			case "interface" :
				$ext = ".interface.php";
				break;
            case "thirdpart" :
                $ext = ".thirdpart.php";
                break;
		}
		
		$filePath = $real_path . self::parse_path($class_name) . $ext;
		self::load ($filePath);
	}
	
	/**
	 * 将文件名解析成为可用路径
	 * @param $file
	 * @return
	 */
	static function parse_path($file,$split="_"){
		return str_replace ( $split, '/', strtolower ( $file ));
	}
	
	
	/**
	 * 加载文件
	 * @param unknown_type $file
	 * @throws core_exception_program
	 * @return
	 */
	public static function load($file) {
		if (! file_exists ( $file )) {
            if(core_config::get('base.debug')){
			    throw new core_exception_program(900003,'file:' . $file);
            }else{
                core_notice::notice('访问出错','/');
            }
		}
		return include_once($file) ;
    }
	
    
    /**
     * 启动数据操作对象
     * @param unknown_type $class_name
     * @return
     */
	static function dao($dao_name){
		core_assert::true(isset($dao_name),904001,'dao:'.$dao_name);
		global $data_pools;
		$dao_name = "dao_" . $dao_name;
		$key = strtolower($dao_name);
		if(!isset($data_pools[$dao_name])){
			$dao = new $dao_name();
			$data_pools[$key] = & $dao;
		}
		return $data_pools[$key];
	}
	
	
	static function table($table){
		core_assert::true(isset($table),903001);
		global $data_pools;
		$table = 'table_' . $table;
		if(!isset($data_pools[$table])){
			$table_obj = new $table();
			$data_pools[$table] = & $table_obj;
		}
		return $data_pools[$table];
	}
	
	static function vo($name,$id){
		core_assert::true(isset($name),903001);
		core_assert::true($id, 903003);
		global $data_pools;
		$vo_name = "vo_" . $name;
		$key = strtolower($vo_name . '_' . $id);
		if(!isset($data_pools[$key])){
			$vo = new core_vo($name,$id);
			$data_pools[$key] = & $vo;
		}
		return $data_pools[$key];
	}

	static function clear_vo($name,$id){
		core_assert::true(isset($name),903001);
		global $data_pools;
		$vo_name = "vo_" . $name;
		$key = strtolower($vo_name . '_' . $id);
		$data_pools[$key] = null;
		return true;
	}
	
	/**
	 * 启动模板引擎
	 * @param unknown_type $template_dir
	 * @throws core_exception_program
	 * @return
	 */
	static function tpl($template_dir=null){
		static $smarty_pools = array ();
		$identify = !empty ( $template_dir ) ? $template_dir : self::to_guid_string ('');
		if (!isset($smarty_pools[$identify])) {
			try{
				$smarty_pools[$identify] = new core_tpl($template_dir);
			}catch(exception $e){
				throw new core_exception_program(901001,'smarty init error,error message:'.$e->getMessage());
			}
		}
		return $smarty_pools[$identify];
	}

	
	/**
	 * 启动外部工具
	 * @param unknown_type $class_name
	 * @throws core_exception_program
	 * @return
	 */
	static function tools($class_name){
		static $toolArray = array ();
		$class_name = "tool_" . $class_name;
		if (! isset ( $toolArray [$class_name] )) {
			try{
				$toolArray [$class_name] = new $class_name ();
			}catch (exception $e){
				throw new core_exception_program(902001,'tools:'.$class_name);
			}catch (core_exception_program $e){
				throw $e;
			}
		}
		return $toolArray [$class_name];
	}
	
	static function get_instance_of($name, $method = '', $args = array()) {
		static $_instance = array ();
		$identify = empty ( $args ) ? $name . $method : $name . $method . self::to_guid_string ( $args );
		if (! isset ( $_instance [$identify] )) {
			if (class_exists ( $name )) {
				$o = new $name ();
				if (method_exists ( $o, $method )) {
					if (! empty ( $args )) {
						$_instance [$identify] = call_user_func_array ( array (&$o, $method ), $args );
					} else {
						$_instance [$identify] = $o -> $method ();
					}
				} else
					$_instance [$identify] = $o;
			}
		}
		return $_instance [$identify];
	}
	
	// 根据PHP各种类型变量生成唯一标识号
	static function to_guid_string($mix) {
		if (is_object ( $mix ) && function_exists ( 'spl_object_hash' )) {
			return spl_object_hash ( $mix );
		} elseif (is_resource ( $mix )) {
			$mix = get_resource_type ( $mix ) . strval ( $mix );
		} else {
			$mix = serialize ( $mix );
		}
		return md5 ( $mix );
	}
	
	/**
	 * 获取图片缩略图
	 * 该方法自动根据pic_rule生成缩略图
	 * 如果pic_rule为空则返回来源图片路径
	 * @param unknown_type $pic_from
	 * @param unknown_type $pic_type
	 * @param unknown_type $pic_rule
	 */
	static function get_pic($pic_from,$pic_type,$pic_rule=null){
		if(!$pic_from){
			return null;
		}
		if($pic_rule){
    		$pic_name = tools_image::get_thumb_name($pic_from,$pic_rule);
    		if(!file_exists(UPLOAD_PATH . $pic_name)){
    			tools_image::make_thumb($pic_from, $pic_type,$pic_rule);
    		}
		}else{
		    $pic_name = $pic_from;
		}
		
		//多个图片处理服务器
		$img_server = tools_image::get_server($pic_from);
		$pic_url = $img_server . '/' . $pic_name;
		return $pic_url;
	}
	
	/**
	 * 检查是否通过JS提交
	 * @return
	 */
	static function by_js(){
		return tools_request::by_js();
	}
	
	
	/**
	 * 检查是否通过POST提交
	 * @return
	 */
	static function by_post($to_long = false){
		return strtolower ( $_SERVER ['REQUEST_METHOD'] ) == 'post';
	}
	
	/**
	 * 检查是否通过手机访问
	 */
	static function by_mobile(){
		$agent = $_SERVER['HTTP_USER_AGENT'];
		if(strpos($agent,"NetFront") || strpos($agent,"iPhone") || strpos($agent,"MIDP-2.0") || strpos($agent,"Opera Mini") || strpos($agent,"UCWEB") || strpos($agent,"Android") || strpos($agent,"Windows CE") || strpos($agent,"SymbianOS")){
			return true;
		}
		return false;
	}
	
	
	static function get_mobile(){
		$agent = $_SERVER['HTTP_USER_AGENT'];
		if(strpos($agent,"iPhone")){
			$mobile = 'iPhone';
		}elseif(strpos($agent,"Android")){
			$mobile = 'Android';
		}elseif(strpos($agent,"SymbianOS")){
			$mobile = 'Windows CE';
		}elseif(strpos($agent,"Windows CE")){
			
		}elseif(strpos($agent,"MIDP-2.0")){
			
		}elseif(strpos($agent,"Opera Mini")){
			
		}elseif(strpos($agent,"UCWEB")){

		}elseif(strpos($agent,"NetFront")){
			
		}
		return $mobile;
	}
	
	/**
	 * 获取客户端ip地址
	 * @param boolean $to_long	可选。是否返回一个unsigned int表示的ip地址
	 * @return string|float		客户端ip。如果to_long为真，则返回一个unsigned int表示的ip地址；否则，返回字符串表示。
	 */
	static function get_ip($to_long=false){
		$forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'];
		if($forwarded){
			$ip_chains = explode(',', $forwarded);
			$proxied_client_ip = $ip_chains ? trim(array_pop($ip_chains)) : '';
		}
		
		if(core_Util::is_private_ip($_SERVER['REMOTE_ADDR']) && isset($proxied_client_ip)){
			$real_ip = $proxied_client_ip;
		}else{
			$real_ip = $_SERVER['REMOTE_ADDR'];
		}
		
		return $to_long ? core_Util::ip2long($real_ip) : $real_ip;
	}
	
    /**
     * 移除文件名中 application, system, modpath, or docroot 的 绝对地址，并用字符串取代他们
     *
     * echo Swift_core::debug_path(Swift_core::find_file('classes', 'swift'));
     *
     * @param string path to debug
     * @return string
     */
    public static function debug_path($file) {
        if (strpos($file, TEMPLATES_PATH) === 0) {
            $file = 'TEMPLATES_PATH' . substr($file, strlen(TEMPLATES_PATH));
        } elseif (strpos($file, CONFIG_PATH) === 0) {
            $file = 'CONFIG_PATH' . substr($file, strlen(CONFIG_PATH));
        } elseif (strpos($file, CORE_PATH) === 0) {
            $file = 'CORE_PATH' . substr($file, strlen(CORE_PATH));
        } elseif (defined('ROOT_PATH') && strpos($file, ROOT_PATH) === 0) {
            $file = 'ROOT_PATH' . substr($file, strlen(ROOT_PATH));
        }elseif (defined('APP_PATH') && strpos($file, APP_PATH) === 0) {
            $file = 'APP_PATH' . substr($file, strlen(APP_PATH));
        }
        return $file;
    }
}
spl_autoload_register('core_comm::auto_load');
?>
<?php
/**
 * 配置类
 * Created by ShengYue
 * User: ShengYue
 * Email: ysc8620@163.com
 * QQ: 372613912
 */
class core_config{

    static $config = array();
    /**
     * 加载指定的配置文件
     *
     * @param string 映射configuration文件名
     * @return array
     */
    public static function load($config_name) {
        $file_path = CONFIG_PATH . core_comm::parse_path($config_name) . '.config.php' ;

        $config = core_comm::load($file_path);
        $config = array_change_key_case($config);
        return $config;
    }
    
    /**
     * 获取指定的配置项，如果$key不存在将报错
     * 进程内缓存，避免重复加载
     * 
     * @param string $key 支持dot path方式获取
     */
    public static function get($key) {
        $key = strtolower($key);
        if (strpos($key, '.') !== false) {
            list($file, $path , $path2) = explode('.', $key, 3);
        }else{
            $file = $key;
        }
        if (!isset(self::$config[$file])) {
            self::$config[$file] = self::load($file);
        }
        
        if (isset($path)) {
        	if(isset($path2)){
        		if(!isset(self::$config[$file][$path][$path2])){
        			throw new core_exception_program(906001,"key:" . $key);
        		}
        		return self::$config[$file][$path][$path2];
        	}
           	if(!isset(self::$config[$file][$path])){
				throw new core_exception_program(906001,"key:" . $key);
           	}
            return self::$config[$file][$path];
        }else{
            // 获取整个配置
            return self::$config[$file];
        }
    }

    /**
     * @param $name
     * @param null $value
     */
    public static function set($key, $value=null){
        $key = strtolower($key);
        if (strpos($key, '.') !== false) {
            list($file, $path , $path2) = explode('.', $key, 3);
        }else{
            $file = $key;
        }

        if (!isset(self::$config[$file])) {
            self::$config[$file] = self::load($file);
        }

        if (isset($path)) {
            if(isset($path2)){
                self::$config[$file][$path][$path2] = $value;
                return true;
            }
            self::$config[$file][$path] = $value;
            return true;
        }else{
            // 获取整个配置
            self::$config[$file] = $value;
        }

    }
}

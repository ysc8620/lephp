<?php 
/**
 * 缓存类
 * Created by ShengYue
 * User: ShengYue
 * Email: ysc8620@163.com
 * QQ: 372613912
 */
class core_cache{
	static $server_key;				//服务器标志
	static $pools = array();		//服务器资源池
	
	/**
	 * 从缓存资源池中获取服务器
	 */
	static function pools($type='memcache',$server_key=null){
		//如果资源服务器不存在则配置服务器
		if(!isset(self::$pools[$type])){
			switch ($type){
				case "memcached":	//memcached
                    if(core_config::get('base.use_cache')){
                        $cache = new core_cache_memcached();
                        $cache -> init();
                    }else{
                        $cache = new core_cache_empty();
                        $cache -> init();
                    }
					break;
				case "memcache":	//memcache
                    if(core_config::get('base.use_cache')){
                        $cache = new core_cache_memcache();
                        $cache -> init();
                    }else{
                        $cache = new core_cache_empty();
                        $cache -> init();
                    }
					break;
				case "file":
					$cache = new core_cache_file();
					$cache -> init();
			}
			self::$pools[$type] = $cache;
		}
		return self::$pools[$type];
	}
}
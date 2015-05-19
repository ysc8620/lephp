<?php
class tools_pin_store {
    const MC_LIVETIME = 300;		//验证码过期时间为5分钟
    private static $MC_KEY_PINCODE = '%s_1_%s';
    
    function __construct() {
    	$this -> CACHE_KEY_PINCODE = core_config::get('cachekey.pincode');
    }
    
    /**
     * 添加验证码到缓存
     * @param string $key
     * @param string $code
     */
    public function add_pin($key, $code) {
    	$mc_key = $this -> get_mc_key($key);
    	$re = core_cache::pools ()-> set($mc_key, $code, self::MC_LIVETIME);
        return $re;
    }
    
    /**
     * 从缓存中获取验证码
     * @param string $key
     */
    public function get_pin($key) {
        $mc_key = $this -> get_mc_key($key);
        $code = core_cache::pools()->get($mc_key, self::MC_LIVETIME);
        return $code;
    }
    
    /**
     * 从缓存中删除验证码
     * @param string $key
     */
    public function del_pin($key) {
        $mc_key = $this -> get_mc_key($key);
        $re = core_cache::pools()->del($mc_key);
        return $re;
    }
    
    
    function get_mc_key($key){
    	return sprintf ( self::$MC_KEY_PINCODE, $this->CACHE_KEY_PINCODE, $key);
    }
    
}
?>
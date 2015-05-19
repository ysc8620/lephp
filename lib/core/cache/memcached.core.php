<?php 
class core_cache_memcached extends Memcached implements core_cache_interface{
	
	function init(){
		$memcacheServers = core_config::get('cache.memcache');
		parent::addServers($memcacheServers);
	}
	
	/**
	 * 获取缓存
	 * @see core_cache_Interface::get()
	 */
	public function get($key){
		$key = $this -> made_key($key);
		return parent::get($key);
	}
	
	/**
	 * 添加缓存
	 * @see core_cache_Interface::set()
	 */
	public function set($key, $value, $expire = 60){
		$key = $this -> made_key($key);
		return parent::set($key, $value, $expire);
	}
	
	
	/**
	 * 设置缓存数据$timeout秒后删除
	 * @see core_cache_Interface::del()
	 */
	public function del($key){
		$key = $this -> made_key($key);
		return parent::delete($key);
	}
	
	
	/**
	 * 批量获取缓存
	 * @see core_cache_Interface::mget()
	 */
	public function mget(array $keys){
		if(empty($keys)){
			return null;
		}
		foreach($keys as $k){
			$k = $this -> made_key($k);
			$_keys[] = $k;
		}
		$ret = parent::getMulti($_keys);
		foreach ($_keys as $k){
			if(!isset($ret[$k])){
				$ret[$k] = false;
			}
		}
		return $ret;
	}
	
	
	/**
	 * 批量设置缓存
	 * @see core_cache_Interface::mset()
	 */
	public function mset(array $values, $expire = 60){
		if(empty($values)){
			return null;
		}
		foreach ($values as $k=>$v){
			$k = $this -> made_key($k);
			$rs[$k] = $v;
		}
		parent::setMulti($rs, $expire);
	}
	
	/**
	 * 批量删除缓存（60秒后删除）
	 * @see core_cache_Interface::mdel()
	 */
	public function mdel(array $keys){
		if(empty($keys)){
			return null;
		}
		foreach ($keys as $key){
			$key = $this -> made_key($key);
			parent::delete($key);
		}
	}
	
	/**
	 * 生成缓存名
	 * @param unknown_type $key
	 */
	function made_key($key){
		//每个站点可设置独立的后缀，以防止多站点使用同一名称的缓存名造成覆盖
		$site_suffix = core_config::get('base.site_suffix');
		$key = $site_suffix . $key;
		return key;
	}
	
	
	/**
	 * 清空缓存
	 * @see core_cache_Interface::flush()
	 */
	public function flush(){
		parent::flush();
	}
}
?>
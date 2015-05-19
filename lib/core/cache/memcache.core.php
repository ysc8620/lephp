<?php 
class core_cache_memcache extends Memcache implements core_cache_interface{
	function init(){
		$memcacheServers = core_config::get('cache.memcache');
		foreach($memcacheServers as $server){
			$this -> addServer($server[0], $server[1],$server[2]);
		}
	}
	
	/**
	 * 获取缓存
	 * @see core_cache_Interface::get()
	 * @return false/value
	 */
	public function get($key){
		$key = $this -> made_key($key);
		return parent::get($key);
	}
	
	/**
	 * 添加缓存
	 * //第三个参数0表示不压缩
	 * @see core_cache_Interface::set()
	 */
	public function set($key, $value, $expire = 60){
		$key = $this -> made_key($key);
		return parent::set($key, $value,0,$expire);
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
		foreach ($keys as $k){
			$_k = $this -> made_key($k);
			$data[$k] = parent::get($_k);
		}
		return $data;
	}
	
	
	/**
	 * 批量设置缓存
	 * @see core_cache_Interface::mset()
	 */
	public function mset(array $values, $expire = 60){
		foreach($values as $k=>$v){
			$k = $this -> made_key($k);
			parent::set($k,$v,0,$expire);
		}
	}
	
	/**
	 * 批量删除缓存（60秒后删除）
	 * @see core_cache_Interface::mdel()
	 */
	public function mdel(array $keys){
		foreach($keys as $k){
			$k = $this -> made_key($k);
			parent::delete($k);
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
		return $key;
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
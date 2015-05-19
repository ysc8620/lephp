<?php 

interface core_cache_interface{
//	public function configure($config);
	
	/**
	 * 获取单条缓存数据
	 * @param str $key
	 * @return false/value
	 */
	public function get($key);
	
	public function set($key, $value, $expire = 60);
	
	public function del($key);
	
	public function mget(array $keys);
	
	public function mset(array $values, $expire = 60);
	
	public function mdel(array $keys);
	
	public function flush();
}
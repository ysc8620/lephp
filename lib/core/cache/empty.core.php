<?php 
class core_cache_empty implements core_cache_interface{
	
	function init(){

	}
	
	/**
	 * 获取缓存
	 * @see core_cache_Interface::get()
	 */
	public function get($key){
		return '';
	}
	
	/**
	 * 添加缓存
	 * @see core_cache_Interface::set()
	 */
	public function set($key, $value, $expire = 60){

	}
	
	
	/**
	 * 设置缓存数据$timeout秒后删除
	 * @see core_cache_Interface::del()
	 */
	public function del($key){

	}
	
	
	/**
	 * 批量获取缓存
	 * @see core_cache_Interface::mget()
	 */
	public function mget(array $keys){
        return array();
	}
	
	
	/**
	 * 批量设置缓存
	 * @see core_cache_Interface::mset()
	 */
	public function mset(array $values, $expire = 60){

	}
	
	/**
	 * 批量删除缓存（60秒后删除）
	 * @see core_cache_Interface::mdel()
	 */
	public function mdel(array $keys){

	}
	
	/**
	 * 生成缓存名
	 * @param unknown_type $key
	 */
	function made_key($key){

	}
	
	
	/**
	 * 清空缓存
	 * @see core_cache_Interface::flush()
	 */
	public function flush(){

	}
}
?>
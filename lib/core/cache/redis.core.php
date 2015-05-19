<?php 
class core_cache_redis implements core_cache_interface{
	
	public function __construct(){
		
	}
	
	public function init(){
		$this -> cache_path = TMP_CACHE_PATH;
	}
	
	public function get($key){
	
	}
	
	
	/**
	 * 缓存文件名
	 * @param str $key
	 */
	protected function get_file_name($key){
	}
	
	/**
	 * 缓存文件
	 * @see core_cache_Interface::set()
	 */
	function set($key,$data,$expire=0){
		$fname = $this -> get_file_name($key);
		if($expire){
			$expire = time() + $expire;
		}
		$c = base64_encode(serialize(array('e' => $expire, 'c' => $data)));
		$content = "<?php \r\n return \"" . $c . "\"\r\n ?>";
		tools_file::to_file($fname, $content);
		return true;
	}


	/**
	 * 删除缓存文件
	 * @see Comm_cache_Interface::del()
	 */
	public function del($key){
		$fname = $this -> get_file_name($key);
		if(file_exists($fname)){
			unlink($fname);
		}
		return TRUE;
	}

	/* 批量获取数据
	 * @see Comm_cache_Interface::mget()
	 */
	public function mget(array $keys){
		$return = array();
		foreach ($keys as $key){
			$return[$key] = $this -> get($key);
		}
		return $return;
	}

	/* 批量缓存数据
	 * @see Comm_cache_Interface::mset()
	 */
	public function mset(array $values, $expire = 60){
		foreach ($values as $k => $v){
			$this->set($k, $v, $expire);
		}
	}

	/* 批量删除数据
	 * @see Comm_cache_Interface::mdel()
	 */
	public function mdel(array $keys){
		foreach ($keys as $k){
			$this->del($k);
		}
	}
	
	
	//清空缓存数据
	public function flush(){
		//删除tmp/cache目录
		//重建tmp/cache目录
	}
}
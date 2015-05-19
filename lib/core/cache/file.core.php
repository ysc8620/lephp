<?php 
/**
 * 文本缓存
 * 数据将以文本文件格式存放在服务器。
 * 通常适用于某些不经常变动，但却非常频繁调用的表结构。如配置信息表，全国城市列表，商品分类表等。
 * 另外，也适用于缓存某些不经常变动的组合数据，比如某些初始化信息（用户，商铺，商品等）。
 * @author aloner
 */
class core_cache_file implements core_cache_interface{
	
	protected $cache_path;	//缓存目录
	
	public function __construct(){
		
	}
	
	public function init(){
		$this -> cache_path = TMP_CACHE_PATH;
	}
	
	public function get($key){
		$fname = $this->get_file_name($key);
		if(!file_exists($fname)){
			return false;
		}
		$c = include_once($fname);
		$content = unserialize(base64_decode($c));
		if(!is_array($content)){
			return false;
		}
		if($content['e']){
			if($content['e'] != 0 && $content['e'] < time()){
				$this -> del($key);
				return false;
			}
		}
		return $content['c'];
	}
	
	
	/**
	 * 缓存文件名
	 * @param str $key
	 */
	protected function get_file_name($key){
		return $this -> cache_path . preg_replace("'(\w{3})'i",'$1/',md5($key)). ".php";
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
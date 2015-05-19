<?php 
/**
 * 值对象
 * @author aloner
 *
 */
class core_vo {
	public $base_db;						//数据库
	public $base_table;						//基表名称（有分表则实际操作表名为“基表名_序号”）
	public $fields = array();				//表内字段设置，包括字段约束及缓存设置等
	public $create_sql = '';				//----自动建表语句，必须与最终表结构完全一致，否则会发生致命错误
	
	
	public $id;
	public $vo_name;
	public $fields_list;					//所有字段列表
	public $is_cache;						//是否设置了缓存
	
	//分库分表设置
	public $hash_type;						//分库分表类型
	public $hash_key;						//分库分表hash字段
	public $hash_value;					//分库分表hash值
	
	protected $cache_fields;				//待缓存字段
	protected $cache_time;					//缓存周期
	protected $values = array();			//记录的值

	
	/**
	 * 构造函数
	 * 实例化值对象时必须为该对象设置ID，系统将自动通过dao获取值
	 * @param int $id
	 * @return
	 */
	function __construct($vo_name,$id=null){
		$this -> vo_name = $vo_name;
		$this -> fields = core_comm::table($this -> vo_name) -> fields;
		$this -> cache_time = core_comm::table($this -> vo_name) -> cache_time;
		$this -> cache_fields = core_comm::table($this -> vo_name) -> cache_fields;
		$this -> fields_list = @array_keys($this -> fields);	//表字段设置
		//是否分表分库存储
	//	$this -> hash_type = core_comm::table($this -> vo_name) -> hash_type;
	//	$this -> hash_key = core_comm::table($this -> vo_name) -> hash_key;
		if($id !== null){
			$this -> id = $id;
			$this -> values['id'] = $id;
		}
		
		if($this -> cache_fields){
			$this -> is_cache = true;						//是否需要缓存
		}
	}
	

	/**
	 * 批量赋值。该方法不进行变量检测
	 */
	function set($key,$val=null){
		if(is_array($key)){
			//用现有数据覆盖来源数据，防止已赋值数据被重置
			$this -> values = $key + $this -> values;
		}else{
			$this -> values[$key] = $val;
		}
		return $this;
	}

    function get_value($key){
        if(@array_key_exists($key,$this -> values)){
            return $this -> values[$key];
        }
        return ;
    }

	/**
	 * 魔术方法：获取字段值
	 */
	function __get($key){
		core_assert::true(in_array($key, $this -> fields_list),903004);

		if(@array_key_exists($key,$this -> values)){
			return $this -> values[$key];
		}elseif($this -> id){
			if(in_array($key,$this -> cache_fields)){  //从缓存中获取数据
				$this -> dao() -> get_vo_from_cache($this -> id);
			}
			//如果缓存中的数据仍然不能满足需要，则连接数据库获取数据
			if(!@array_key_exists($key,$this -> values)){
				$this -> dao() -> get_vo_from_db($this -> id);
			}
			return $this -> values[$key];
		}else{
			//字段尚未被赋值，该对象没有关联数据库。
			throw new core_exception_assert(903005);
		}
	}
	
	/**
	 * 为字段赋值
	 */
	function __set($key,$val=null){
		//变量检测
		$this -> values[$key] = $val;
	}
	
	
	/****************************************************************************************
	 * 以下通过值对象直接同步操作数据库及缓存												*
	 ****************************************************************************************/
	
	/**
	 * 将该对象插入到数据库中
	 * @throws core_exception_program
	 */
	public function insert(){
		$id = $this -> dao() -> add($this -> values);
		if($id){
			return core_comm::vo($this -> vo_name,$id) -> set($this -> values);
		}
	}
	
	/**
	 *  将该对象替换插入到数据库中
	 */
	public function replace_insert(){
		$id = $this -> dao() -> replace_add($this -> values);
		if($id){
			return core_comm::vo($this -> vo_name,$id) -> set($this -> values);
		}
	}
	
	/**
	 * 编辑该对象对应数据库记录
	 */
	public function update($update_field='*'){
		core_assert::true($this -> id,903002);
		if($update_field=='*'){
			$update_value = $this -> values;
		}else{ 
			$update_field = @ explode(",",$update_field);
			if($update_field){
				foreach($update_field as $_field){
					if(@ array_key_exists($_field,$this -> values)){
						$update_value[$_field] = $this -> values[$_field];
					}
				}
			}
			$update_value['id'] = $this -> id;
		}
		core_assert::true(!empty($update_value),903006);
		$rs = $this -> dao() -> edit_by_id($update_value,$this -> id);
		if($rs){
			$this -> change_cache();
			return $this;
		}
		throw new core_exception_program(903007);		//值对象更新失败
	}

	
	/**
	 * 删除值对象
	 */
	public function delete(){
		core_assert::true($this -> id,903002);
		return $this -> dao() -> del_by_ids(array($this -> id));
	}
	
	
	/**
	 * 将值对象输出成为数组
	 */
	public function to_array(){
		return $this -> values;
	}
	
	/**
	 * 根据ID判断vo对应记录是否存在
	 * 先从缓存中取值，未获取到值则从数据库中取
	 */
	public function exists(){
		if(!$this -> id){
			return false;
		}
		if(count($this -> values) > 1){
			return true;
		}
		$this -> dao() -> get_vo_from_cache($this -> id);
		if(count($this -> values) > 1){
			return true;
		}
		$this -> dao() -> get_vo_from_db($this -> id);
		if(count($this -> values) > 1){
			return true;
		}
		return false;
	}
	
	/**
	 * 获取值操作对象
	 */
	function dao(){
		if($this -> hash_type){
			if(!$this -> hash_value && $this -> values[$this -> hash_key]){
				$this -> set_hash_value($this -> values[$this -> hash_key]);
			}
			core_assert::true($this -> hash_value, 904105);
			return core_comm::dao($this -> vo_name) -> hash($this -> hash_value);
		}else{
			return core_comm::dao($this -> vo_name);
		}
	}
	
	
	/**
	 * 设置散列信息
	 */
	function set_hash_value($hash_value){
		$this -> hash_value = $hash_value;
	}
	
	
	/**
	 * 更新缓存信息
	 */
	function change_cache(){
		$this -> dao() -> set_caches(array($this -> values));
	}
}
?>
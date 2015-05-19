<?php 
class core_table_range extends core_table{
	public $row_num_per_table = 1000000;	//每一百万条数据分一张表，上线后该数字不能修改。
	public $table_num_per_db = 10;			//每个数据库中存放10张数据表。
	public $create_id_auto = true;			//使用发号器自动发号
	public $hash_key = 'id';
	public $hash_type = 'range';
	
	protected $hash_value;		//散列参考值
	
	
	/**
	 * 设置散列信息
	 */
	function set_hash_value($hash_value){
		$this -> hash_value = $hash_value;
	}
	
	/**
	 * 获取数据操作对象
	 * 散列表需要同时分配散列表信息
	 * 
	 * 该对象重写父对象方法
	 */
	function dao(){
		if(!$this -> hash_value && $this -> values[$this -> hash_key]){
			$this -> set_hash_value($this -> values[$this -> hash_key]);
		}
		core_assert::true($this -> hash_value, 904105);
		return core_comm::dao($this -> get_called_class()) -> hash($this -> hash_value);
	}
}
?>